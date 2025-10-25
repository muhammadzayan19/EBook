<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION[$type . '_message'] = $message;
    }
    header("Location: $url");
    exit();
}

function format_price($amount) {
    return '$' . number_format($amount, 2);
}

function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) return 'just now';
    if ($difference < 3600) return floor($difference / 60) . ' minutes ago';
    if ($difference < 86400) return floor($difference / 3600) . ' hours ago';
    if ($difference < 604800) return floor($difference / 86400) . ' days ago';
    
    return date('M j, Y', $timestamp);
}
?>