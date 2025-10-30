<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$page_title = "Admin Login";

require_once '../config/db.php';

$error = '';
$success = '';

if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success = "You have been successfully logged out.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Try admin_users table first (new structure)
        $query = "SELECT * FROM admin_users WHERE username = '$username' OR email = '$username' LIMIT 1";
        $result = mysqli_query($conn, $query);
        
        // Fallback to admin_users table if admin_users doesn't exist or no result
        if (!$result || mysqli_num_rows($result) == 0) {
            $query = "SELECT * FROM admin_users WHERE username = '$username' LIMIT 1";
            $result = mysqli_query($conn, $query);
        }
        
        if ($result && mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = isset($admin['name']) ? $admin['name'] : $admin['username'];
                $_SESSION['admin_role'] = isset($admin['role']) ? $admin['role'] : 'admin';
                $_SESSION['admin_email'] = isset($admin['email']) ? $admin['email'] : '';
                
                // Update last login if admin_users table
                if (isset($admin['last_login'])) {
                    mysqli_query($conn, "UPDATE admin_users SET last_login = NOW() WHERE admin_id = {$admin['admin_id']}");
                }
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}

include '../includes/header.php';
?>

<!-- Admin-Specific Styles -->
<link rel="stylesheet" href="../assets/css/admin.css">

    <!-- Admin Login Section -->
    <section class="admin-auth-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <!-- Admin Login Card -->
                    <div class="admin-login-card">
                        <!-- Card Header -->
                        <div class="admin-login-header">
                            <div class="admin-logo">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <h2 class="admin-title">Admin Portal</h2>
                            <p class="admin-subtitle">Sign in to access the dashboard</p>
                        </div>
                        
                        <!-- Alert Messages -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Login Form -->
                        <form method="POST" action="" class="admin-login-form">
                            <div class="form-floating mb-3">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="username" 
                                    name="username" 
                                    placeholder="Username"
                                    required
                                    autofocus
                                >
                                <label for="username">
                                    <i class="bi bi-person-fill me-2"></i>Username or Email
                                </label>
                            </div>
                            
                            <div class="form-floating mb-3">
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Password"
                                    required
                                >
                                <label for="password">
                                    <i class="bi bi-lock-fill me-2"></i>Password
                                </label>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="remember" 
                                    name="remember"
                                >
                                <label class="form-check-label" for="remember">
                                    Remember me for 30 days
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-admin-primary w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Sign In to Dashboard
                            </button>
                            
                            <div class="text-center">
                                <a href="../index.php" class="admin-link">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Back to Main Site
                                </a>
                            </div>
                        </form>
                        
                        <!-- Security Badge -->
                        <div class="admin-security-badge">
                            <i class="bi bi-shield-check"></i>
                            <span>Secured by 256-bit SSL Encryption</span>
                        </div>
                    </div>
                    
                    <!-- Footer Note -->
                    <div class="admin-footer-note">
                        <p>
                            <i class="bi bi-info-circle me-1"></i>
                            For security reasons, admin sessions expire after 30 minutes of inactivity.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
include '../includes/footer.php';
?>