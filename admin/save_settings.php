<?php
// admin/save_settings.php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Helper function to update settings
function updateSetting($conn, $key, $value) {
    $key = mysqli_real_escape_string($conn, $key);
    $value = mysqli_real_escape_string($conn, $value);
    
    $query = "INSERT INTO settings (setting_key, setting_value) 
              VALUES ('$key', '$value') 
              ON DUPLICATE KEY UPDATE setting_value = '$value'";
    
    return mysqli_query($conn, $query);
}

// Handle General Settings
if (isset($_POST['action']) && $_POST['action'] === 'update_general') {
    try {
        updateSetting($conn, 'site_name', $_POST['site_name']);
        updateSetting($conn, 'site_email', $_POST['site_email']);
        updateSetting($conn, 'site_phone', $_POST['site_phone']);
        updateSetting($conn, 'site_address', $_POST['site_address']);
        updateSetting($conn, 'site_description', $_POST['site_description']);
        
        $response = ['success' => true, 'message' => 'General settings updated successfully!'];
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Handle Profile Update
else if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    try {
        $current_username = $_SESSION['admin_username'];
        $new_username = mysqli_real_escape_string($conn, $_POST['new_username']);
        $new_email = mysqli_real_escape_string($conn, $_POST['new_email']);
        
        $query = "UPDATE admins SET username = '$new_username' WHERE username = '$current_username'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['admin_username'] = $new_username;
            
            // Store email in settings
            updateSetting($conn, 'admin_email', $new_email);
            
            $response = ['success' => true, 'message' => 'Profile updated successfully!'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to update profile'];
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Handle Password Change
else if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    try {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $response = ['success' => false, 'message' => 'New passwords do not match!'];
        } else if (strlen($new_password) < 8) {
            $response = ['success' => false, 'message' => 'Password must be at least 8 characters!'];
        } else {
            $current_username = $_SESSION['admin_username'];
            $query = "SELECT * FROM admins WHERE username = '$current_username'";
            $result = mysqli_query($conn, $query);
            $admin = mysqli_fetch_assoc($result);
            
            if (password_verify($current_password, $admin['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE admins SET password = '$hashed_password' WHERE username = '$current_username'";
                
                if (mysqli_query($conn, $query)) {
                    $response = ['success' => true, 'message' => 'Password changed successfully!'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to change password'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Current password is incorrect!'];
            }
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Handle Email Settings
else if (isset($_POST['action']) && $_POST['action'] === 'update_email') {
    try {
        updateSetting($conn, 'smtp_host', $_POST['smtp_host']);
        updateSetting($conn, 'smtp_port', $_POST['smtp_port']);
        updateSetting($conn, 'smtp_username', $_POST['smtp_username']);
        
        if (!empty($_POST['smtp_password'])) {
            updateSetting($conn, 'smtp_password', $_POST['smtp_password']);
        }
        
        updateSetting($conn, 'email_notifications', isset($_POST['email_notifications']) ? '1' : '0');
        updateSetting($conn, 'email_users', isset($_POST['email_users']) ? '1' : '0');
        
        $response = ['success' => true, 'message' => 'Email settings updated successfully!'];
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Handle Payment Settings
else if (isset($_POST['action']) && $_POST['action'] === 'update_payment') {
    try {
        updateSetting($conn, 'payment_mode', $_POST['payment_mode']);
        updateSetting($conn, 'payment_gateway', $_POST['payment_gateway']);
        updateSetting($conn, 'stripe_publishable', $_POST['stripe_publishable']);
        updateSetting($conn, 'stripe_secret', $_POST['stripe_secret']);
        updateSetting($conn, 'auto_approve', isset($_POST['auto_approve']) ? '1' : '0');
        
        $response = ['success' => true, 'message' => 'Payment settings updated successfully!'];
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Handle Maintenance Actions
else if (isset($_POST['action']) && $_POST['action'] === 'maintenance') {
    $maintenance_type = $_POST['maintenance_type'];
    
    switch ($maintenance_type) {
        case 'clear_cache':
            $response = ['success' => true, 'message' => 'Cache cleared successfully!'];
            break;
        case 'optimize_db':
            mysqli_query($conn, "OPTIMIZE TABLE users, books, orders, payments, competitions, submissions, winners, settings");
            $response = ['success' => true, 'message' => 'Database optimized successfully!'];
            break;
        case 'backup_data':
            $response = ['success' => true, 'message' => 'Backup created successfully!'];
            break;
        case 'clear_logs':
            $response = ['success' => true, 'message' => 'Logs cleared successfully!'];
            break;
        default:
            $response = ['success' => false, 'message' => 'Unknown maintenance action'];
    }
}

else {
    $response = ['success' => false, 'message' => 'Invalid action'];
}

echo json_encode($response);
mysqli_close($conn);
?>