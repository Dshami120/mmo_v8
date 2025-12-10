<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Your Account</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
   <?php
        $msg = '';
        $error = false;
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
        // Check if form data was submitted via POST
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            //get data out
               $username = $_POST['username'];
               $password = $_POST['password'];
               $fname    = $_POST['fname'];
               $lname    = $_POST['lname'];
               $gender   = $_POST['gender'];
            // Make sure that email address does not exists in DB.
            if ($stmt = $con->prepare('SELECT sys_user_id FROM money_user WHERE user_email = ?')) {
                // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
                $stmt->bind_param('s', $_POST['username']);
                $stmt->execute();
                // Store the result so we can check if the account exists in the database
                $stmt->store_result();

                // Check if account exists with the input username
                if ($stmt->num_rows > 0) {
                    // Account exists, so bind the results to variables
                    $msg ='This email address has already been registered. Please use another email address';
                    $error = true;
                }
                // Close the prepared statement
                $stmt->close();
            }
            if(!$error){
                // lets add user to DB
                            if ($stmt = $con->prepare('INSERT INTO money_user (USER_EMAIL, USER_PASSWORD, USER_FIRST_NAME, USER_LAST_NAME, USER_GENDER) values(?, ?, ?, ?, ? )' ) ) {
                                // Bind parameters (s = string, i = int, b = blob, etc), in our case the username is a string so we use "s"
                                $stmt->bind_param('sssss', $username, $password, $fname, $lname, $gender);
                               // $stmt->bind_param('s', $password);
                               // $stmt->bind_param('s', $fname);
                               // $stmt->bind_param('s', $lname);
                               // $stmt->bind_param('s', $gender );
                                
                                if ($stmt->execute()) {
                                    // Account created, we are good, send to login page
                                    header('Location: login.php');
                                    //exit;
                                } else {
                                    $msg="User Creation Failed. Please contact Admin.";
                                }
                                // Close the prepared statement
                                $stmt->close();
                            }

            }
            $con->close();
        }    

   ?>
<!-- Bootstrap centering container -->
<div class="container d-flex justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">

        <h1 class="text-center mt-5 mb-4 fw-bold">Create Your Account</h1>
       <h4 style="margin-left:10rem; color:red;"><?php echo $msg; ?></h4>
        <br/><br/>
        <form class="text-start" action="signup.php" method="post">

            <label class="form-label">First Name</label>
            <input type="text" class="form-control mb-3" name="fname" placeholder="Your first name">

            <label class="form-label">Last Name</label>
            <input type="text" class="form-control mb-3" name="lname" placeholder="Your last name">

            <label class="form-label">Email Address</label>
            <input type="email" class="form-control mb-3" name="username" id="username" placeholder="Your email">
            

            <label class="form-label">Password</label>
            <input type="password" class="form-control mb-3" name="password" id="password" placeholder="Create a password">

            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control mb-4" name="password1" id="password1" placeholder="Re-enter password">

            <label class="form-label">Gender</label>
            <div class="form-check">
                <input type="radio" class="form-check-input" name="gender" value="Male" checked>Male
                <label class="form-check-label" for="radio1"></label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" name="gender" value="Female">Female
                <label class="form-check-label" for="radio2"></label>
            </div>
            <br>
            <br>


            <button type="button" class="btn btn-primary w-100 py-2 mb-3"  onClick="myFunction(this);">
                Sign Up
            </button>

            <p class="text-center">
                Already have an account?
                <a href="login.php">Log In</a>
            </p>

            <p class="text-center mt-4">
                <a href="landingPage.html" class="btn btn-outline-secondary">
                    ← Back to Home
                </a>
            </p>
    <script>
    function myFunction(butt) {
        var mil = $("#username").val();
        if($("#username").val() =="") {
            alert("Email is a required field");
            return;
        }
        if(mil.indexOf("@") == -1) {
            alert("Email is not Valid");
            return;
        }
        
        if($("#password").val() =="") {
            alert("Password is a required field");
            return;
        }
        if($("#password").val() != $("#password1").val()) {
            alert("Password do not match");
            return;
        }
        butt.form.submit();
    }
    </script>
        </form>

    </div>
</div>
<!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</body>
</html>




















<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>

    <h1> Sign Up</h1>

    <form>
        <label for="fname">First Name</label>
        <input type="text" id="fname" name="firstname" placeholder="Your name..">
        <br>

        <label for="lname">Last Name</label>
        <input type="text" id="lname" name="lastname" placeholder="Your last name..">
        <br>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Your email..">
        <br>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Your password..">
        <br>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password..">
        <br><br>

        <input type="submit" value="Sign Up">
        <label>Already have an account?</label>
    </form>


-->

<!--
     add javascript if/else func -- if new user, go to sign up page, else stay here and prompt log in
     what is label element?
    <a href="log_in.html"> Already have an account?</a>
    <label>Already have an account?</label>
    <p> are you a new user?</p>
    <input type="radio" id="newUser" name="userType" value="yes">
    <label for="newUser">yes</label>
    <input type="radio" id="notNew" name="userType" value="no">
    <label for="notNew">no</label>


    <br><br><br>
    <button> <a href="landingPage.html"> Go Back! </a></button>
</body>
</html>
-->