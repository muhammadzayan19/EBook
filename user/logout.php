<?php
/**
 * User Logout
 * Destroys session and redirects to login page
 */

// Start session
session_start();

// Store user name for goodbye message (optional)
$user_name = $_SESSION['user_name'] ?? 'User';

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Start new session for logout message
session_start();
$_SESSION['logout_message'] = "You have been successfully logged out. See you again soon!";

// Redirect to login page
header('Location: login.php');
exit();
?>