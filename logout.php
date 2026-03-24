<?php
/**
 * User Logout Handler
 * Safely terminates user session and clears all authentication data
 * Redirects to home page after successful logout
 */

// Initialize session to access session variables and perform cleanup
session_start();

// Include authentication helper functions from includes folder
include "includes/auth.php";

// Call the logout helper function which safely destroys the session
logout_user();

// Redirect user to home page after successful logout
header("location:Home.php");

// Exit to prevent further PHP code execution after redirect
exit();

?>
