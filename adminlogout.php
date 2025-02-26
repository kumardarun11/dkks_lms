<?php
// Start session
session_start();

// Destroy session
session_unset();
session_destroy();

// Redirect to the login page
header("Location: adminsigninup.php");
exit;
?>
