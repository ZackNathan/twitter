<!--
    Enlighten: login.php
    Use this page to login to Enlighten to view the feed
    Created by Zack Nathan, Denis Khavin, Surya Pandiaraju, Michael McGovern, and Mark Hoel
    Created and last edited in May 2016
-->

<?php

    // First we execute our common code to connection to the database and start the session
    require("common.php");

    // This variable will be used to re-display the user's username to them in the
    // login form if they fail to enter the correct password.  It is initialized here
    // to an empty value, which will be shown if the user has not submitted the form.
    $submitted_username = '';

    // This if statement checks to determine whether the login form has been submitted
    // If it has, then the login code is run, otherwise the form is displayed
    if(!empty($_POST))
    {
        // This query retreives the user's information from the database using
        // their username.
        $query = "
            SELECT
                id,
                username,
                password,
                salt,
                email
            FROM users
            WHERE
                username = :username
        ";

        // The parameter values
        $query_params = array(
            ':username' => $_POST['username']
        );

        try {
            // Execute the query against the database
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            // Note: On a production website, you should not output $ex->getMessage().
            // It may provide an attacker with helpful information about your code.
            die("Failed to run query: " . $ex->getMessage());
        }

        // This variable tells us whether the user has successfully logged in or not.
        // We initialize it to false, assuming they have not.
        // If we determine that they have entered the right details, then we switch it to true.
        $login_ok = false;

        // Retrieve the user data from the database.  If $row is false, then the username
        // they entered is not registered.
        $row = $stmt->fetch();
        if ($row) {
            // Using the password submitted by the user and the salt stored in the database,
            // we now check to see whether the passwords match by hashing the submitted password
            // and comparing it to the hashed version already stored in the database.
            $check_password = hash('sha256', $_POST['password'] . $row['salt']);
            for ($round = 0; $round < 65536; $round++) {
                $check_password = hash('sha256', $check_password . $row['salt']);
            }

            if($check_password === $row['password']) {
                // If they do, then we flip this to true
                $login_ok = true;
            }
        }

        // If the user logged in successfully, then we send them to the private members-only page
        // Otherwise, we display a login failed message and show the login form again
        if($login_ok) {

            // Here I am preparing to store the $row array into the $_SESSION by
            // removing the salt and password values from it.  Although $_SESSION is
            // stored on the server-side, there is no reason to store sensitive values
            // in it unless you have to.  Thus, it is best practice to remove these
            // sensitive values first.
            unset($row['salt']);
            unset($row['password']);

            // This stores the user's data into the session at the index 'user'.
            // We will check this index on the private members-only page to determine whether
            // or not the user is logged in.  We can also use it to retrieve
            // the user's details.
            $_SESSION['user'] = $row;

            // Redirect the user to the private members-only page.
            $location = "http://".$_SERVER['HTTP_HOST']."/twitter/feed.php";
            echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
        }

        else {
            // Tell the user they failed
            // This is done by passing a url parameter
            $location = "http://".$_SERVER['HTTP_HOST']."/twitter/login.php?errors=loginfailed";
            echo '<META HTTP-EQUIV="refresh" CONTENT="0;URL='.$location.'">';
        }
    }

?>

<html>
    <head>
        <title>Login</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        <link href="dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="enlighten.css">

        <meta charset="UTF-8">
        <meta name="description" content="Login to Enlighten">
        <meta name="author" content="Zack, Denis, Surya, and Michael">
    </head>

    <body background="resources/tower.jpg">
        <div class="container">
            <img width=250px height=150px src="resources/phoenix.png">

            <div>
                <h1><font size"500" color ="red">Enlighten!</font></h1>
            </div>

            <br>
            <div class="redbox">

                <font color ="white"><h2>Login</h2></font>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">

                    <br />
                    <input type="text" name="username" value="<?php echo $submitted_username; ?>" class="form-control" placeholder="Username" aria-describedby="sizing-addon1" required/>
                    <br />
                    <input type="password" name="password" value="" class="form-control" placeholder="Password" aria-describedby="sizing-addon1" required/>
                    <br />
                    <?php
                    // Display the message if the url contains the loginfailed error
                    // This means the user entered incorrect login credentials
                    if (strpos($_GET["errors"], "loginfailed") !== false) {
                        // convenient link to the register page
                        echo "<span style='color: white;'>Invalid username/password combination, try again or <a href='register.php'><strong><span style='color:white'>register</span></a></span><br>";
                    }
                    ?>
                    <br />

                   <center> <button class="button" style="vertical-align:middle"><span>Enter the Firepit</span> </button></center>

                </form>

                <a href="register.php"><strong><span style="color:white">Don't have an account? Register here!</span><br /></a>
                <a href="about.php"><strong><span style="color:white">About Enlighten and FAQ</span></a>
            </div>
        </div>
    </body>
</html>
