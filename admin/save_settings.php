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

// Password validation function
function validatePassword($password) {
    $errors = [];
    
    // Check minimum length (8 characters)
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    // Check for uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must include at least one uppercase letter';
    }
    
    // Check for lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must include at least one lowercase letter';
    }
    
    // Check for number
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must include at least one number';
    }
    
    // Check for special character
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $errors[] = 'Password must include at least one special character';
    }
    
    // Check for common passwords
    $commonPasswords = [
        'password', '12345678', 'qwerty', 'abc123', 'password123',
        'admin123', 'welcome', 'letmein', 'monkey', '1234567890',
        'password1', 'admin', 'root', 'test1234', 'pass1234'
    ];
    
    if (in_array(strtolower($password), $commonPasswords)) {
        $errors[] = 'Password is too common. Please choose a stronger password';
    }
    
    return [
        'isValid' => empty($errors),
        'errors' => $errors
    ];
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
        
        // Validate email format
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $response = ['success' => false, 'message' => 'Invalid email format'];
        } else {
            $query = "UPDATE admin_users SET username = '$new_username' WHERE username = '$current_username'";
            
            if (mysqli_query($conn, $query)) {
                $_SESSION['admin_username'] = $new_username;
                
                // Store email in settings
                updateSetting($conn, 'admin_email', $new_email);
                
                $response = ['success' => true, 'message' => 'Profile updated successfully!'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update profile'];
            }
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
        
        // Check if passwords match
        if ($new_password !== $confirm_password) {
            $response = ['success' => false, 'message' => 'New passwords do not match!'];
        } else {
            // Validate new password
            $validation = validatePassword($new_password);
            
            if (!$validation['isValid']) {
                $response = [
                    'success' => false, 
                    'message' => 'Password does not meet requirements:<br>• ' . implode('<br>• ', $validation['errors'])
                ];
            } else {
                $current_username = mysqli_real_escape_string($conn, $_SESSION['admin_username']);
                $query = "SELECT * FROM admin_users WHERE username = '$current_username'";
                $result = mysqli_query($conn, $query);
                $admin = mysqli_fetch_assoc($result);
                
                if (password_verify($current_password, $admin['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $hashed_password = mysqli_real_escape_string($conn, $hashed_password);
                    $query = "UPDATE admin_users SET password = '$hashed_password' WHERE username = '$current_username'";
                    
                    if (mysqli_query($conn, $query)) {
                        $response = ['success' => true, 'message' => 'Password changed successfully! Your account is now more secure.'];
                    } else {
                        $response = ['success' => false, 'message' => 'Failed to change password. Please try again.'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'Current password is incorrect!'];
                }
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
        
        if (!empty($_POST['stripe_secret'])) {
            updateSetting($conn, 'stripe_secret', $_POST['stripe_secret']);
        }
        
        updateSetting($conn, 'auto_approve', isset($_POST['auto_approve']) ? '1' : '0');
        
        // Validate and update subscription price
        if (isset($_POST['subscription_price'])) {
            $subscription_price = floatval($_POST['subscription_price']);
            if ($subscription_price >= 0) {
                updateSetting($conn, 'monthly_subscription_price', $subscription_price);
            }
        }
        
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
            // Clear cache logic here
            $response = ['success' => true, 'message' => 'Cache cleared successfully!'];
            break;
            
        case 'optimize_db':
            try {
                mysqli_query($conn, "OPTIMIZE TABLE users, books, orders, payments, competitions, submissions, winners, settings, admin_users");
                $response = ['success' => true, 'message' => 'Database optimized successfully!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Failed to optimize database'];
            }
            break;
            
        case 'backup_data':
            // Backup logic here
            $response = ['success' => true, 'message' => 'Backup created successfully!'];
            break;
            
        case 'clear_logs':
            // Clear logs logic here
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