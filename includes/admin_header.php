<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$in_subdirectory = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Online E-Book System - Admin Dashboard">
    <meta name="keywords" content="ebooks, online books, competitions, essay writing, digital learning">
    <meta name="author" content="Zayan - Prime Creators">
    <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?>Admin - E-Book System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo $in_subdirectory; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $in_subdirectory; ?>assets/css/admin.css">
</head>
<body class="admin-body">