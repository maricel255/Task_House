<?php
session_start(); // Start the session
session_destroy(); // Destroy the session to log out the user
header("Location: faci_log_in.html"); // Redirect to the login page
exit();
?>