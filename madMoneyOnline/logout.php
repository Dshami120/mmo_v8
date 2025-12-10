<?php
// 1. Start the session
session_start();

// 2. Unset all session variables
$_SESSION = array();

// 3. Destroy the session
session_destroy();

// 4. Delete the session cookie (if used)
// This code snippet assumes the default session cookie name 'PHPSESSID'
// and default parameters for path and domain. Adjust if necessary.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Redirect the user to the login page or homepage
header("Location: index.html");
exit(); // Important to call exit() after header redirect
?>