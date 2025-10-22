<?php
session_start();

$user_name = $_SESSION['user_name'] ?? 'User';

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

session_start();
$_SESSION['logout_message'] = "You have been successfully logged out. See you again soon!";

header('Location: login.php');
exit();
?>