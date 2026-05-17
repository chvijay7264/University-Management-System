<?php
// Start the session
session_start();

// Remove all session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect back to login page
header("Location: login.php");
exit();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logout</title>
</head>
<body>
    <h2>You have been logged out successfully.</h2>
    <p><a href="login.php">Click here to login again</a></p>
</body>
</html>
