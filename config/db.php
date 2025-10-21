<?php

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ebook');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Error</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: #f9fafb;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }
            .error-box {
                background: white;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                max-width: 500px;
                text-align: center;
            }
            .error-icon {
                font-size: 64px;
                color: #ef4444;
                margin-bottom: 20px;
            }
            h2 {
                color: #1f2937;
                margin-bottom: 15px;
            }
            p {
                color: #6b7280;
                line-height: 1.6;
            }
            .btn {
                display: inline-block;
                margin-top: 20px;
                padding: 12px 24px;
                background: #2563eb;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <div class='error-icon'>⚠️</div>
            <h2>Database Connection Error</h2>
            <p>Unable to connect to the database. Please check your configuration and try again.</p>
            <a href='javascript:history.back()' class='btn'>Go Back</a>
        </div>
    </body>
    </html>
    ");
}

$conn->set_charset("utf8mb4");

date_default_timezone_set('Asia/Karachi');

function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $conn->real_escape_string($data);
}

function execute_query($query, $params = [], $types = "") {
    global $conn;
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Query Preparation Failed: " . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Query Execution Failed: " . $stmt->error);
    }
    
    return $stmt;
}
?>