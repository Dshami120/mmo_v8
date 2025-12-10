<?php
   ob_start();
   session_start();
   // If the user is logged in, redirect to the home page
    if (isset($_SESSION['account_loggedin'])) {
        header('Location: dashboard.php');
        exit;
    }
?>
<html lang = "en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>

   <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php
        $msg = '';

        if (isset($_POST['login']) && !empty($_POST['username']) && !empty($_POST['password'])) {
            $DATABASE_HOST = 'localhost';
            $DATABASE_USER = 'root';
            $DATABASE_PASS = '';
            $DATABASE_NAME = 'madmoneyonline';
            // Try and connect using the info above
            $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
            // Check for connection errors
            if (mysqli_connect_errno()) {
                // If there is an error with the connection, stop the script and display the error
                exit('Failed to connect to MySQL!');
            }

            if ($stmt = $con->prepare('SELECT sys_user_id, user_password FROM money_user WHERE user_email = ?')) {
                // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
                $stmt->bind_param('s', $_POST['username']);
                $stmt->execute();
                // Store the result so we can check if the account exists in the database
                $stmt->store_result();

                // Check if account exists with the input username
                if ($stmt->num_rows > 0) {
                    // Account exists, so bind the results to variables
                    $stmt->bind_result($sys_user_id, $user_password);
                    $stmt->fetch();
                    // Note: remember to use password_hash in your registration file to store the hashed passwords
                    // if (password_verify($_POST['password'], $user_password)) {
                    if ($_POST['password'] === $user_password) {    
                        // Password is correct! User has logged in!
                        // Regenerate the session ID to prevent session fixation attacks
                        session_regenerate_id();
                        // Declare session variables (they basically act like cookies but the data is remembered on the server)
                        $_SESSION['account_loggedin'] = TRUE;
                        $_SESSION['account_name'] = $_POST['username'];
                        $_SESSION['account_id'] = $sys_user_id;
                        // Output success message
                        echo 'Welcome back, ' . htmlspecialchars($_SESSION['account_name'], ENT_QUOTES) . '!';
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        // Incorrect password
                        $msg = 'Incorrect password!';
                    }
                } else {
                    // Incorrect username
                    $msg = 'Incorrect username!';
                }

                // Close the prepared statement
                $stmt->close();
            }
      }
   ?>

 
<!-- Centering container -->
<div class="container d-flex justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">

        <h1 class="text-center mt-5 mb-4 fw-bold">Login</h1>
        <h4 style="margin-left:10rem; color:red;"><?php echo $msg; ?></h4>
        <br/><br/>

        <form class="text-start" action="login.php" method="POST">

            <label class="form-label">Email</label>
            <input type="email" class="form-control mb-3" name="username" id="name" placeholder="Enter your email">

            <label class="form-label">Password</label>
            <input type="password" class="form-control mb-4" name="password" id="password" placeholder="Enter your password">

            <button type="submit" class="btn btn-primary w-100 py-2 mb-3" name="login">
                Login
            </button>

            <p class="text-center mb-4">
                <a href="password_reset.html">Forgot your password?</a>
            </p>

            <p class="text-center">
                Don't have an account?
                <a href="signup.php">Sign Up</a>
            </p>

            <p class="text-center mt-4">
                <a href="index.html" class="btn btn-outline-secondary">
                    ← Back to Home
                </a>
            </p>

        </form>

    </div>
</div>

</body>
</html>