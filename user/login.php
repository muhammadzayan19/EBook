<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

require_once '../config/db.php';

$page_title = "Login";

include '../includes/header.php';

$error_message = '';
$success_message = '';

if (isset($_SESSION['registration_success'])) {
    $success_message = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

if (isset($_SESSION['logout_message'])) {
    $success_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['login_time'] = time();
                
                if ($remember) {
                    setcookie('remember_user', $email, time() + (86400 * 30), "/");
                } else {
                    if (isset($_COOKIE['remember_user'])) {
                        setcookie('remember_user', '', time() - 3600, "/");
                    }
                }
                
                $update_stmt = $conn->prepare("UPDATE users SET registered_at = registered_at WHERE user_id = ?");
                $update_stmt->bind_param("i", $user['user_id']);
                $update_stmt->execute();
                $update_stmt->close();
                
                header('Location: profile.php');
                exit();
            } else {
                $error_message = 'Invalid email or password. Please try again.';
            }
        } else {
            $error_message = 'Invalid email or password. Please try again.';
        }
        
        $stmt->close();
    }
}

$remembered_email = $_COOKIE['remember_user'] ?? '';
?>

    <!-- Login Section -->
    <section class="auth-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="auth-card">
                        <!-- Logo/Brand -->
                        <div class="auth-header">
                            <div class="auth-logo">
                                <i class="bi bi-book-half"></i>
                            </div>
                            <h2 class="auth-title">Welcome Back!</h2>
                            <p class="auth-subtitle">Login to access your account</p>
                        </div>

                        <!-- Success Message -->
                        <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Error Message -->
                        <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form method="POST" action="" class="auth-form needs-validation" novalidate>
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="name@example.com" 
                                           value="<?php echo htmlspecialchars($remembered_email); ?>" required>
                                    <label for="email"><i class="bi bi-envelope me-2"></i>Email Address</label>
                                    <div class="invalid-feedback">
                                        Please enter a valid email address.
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Password" required>
                                    <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                                    <div class="invalid-feedback">
                                        Please enter your password.
                                    </div>
                                </div>
                                <div class="password-toggle">
                                    <input type="checkbox" id="showPassword" onclick="togglePassword()">
                                    <label for="showPassword">Show Password</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember" 
                                           <?php echo $remembered_email ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                <a href="forgot_password.php" class="auth-link">Forgot Password?</a>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>

                            <div class="text-center">
                                <p class="mb-0">Don't have an account? 
                                    <a href="register.php" class="auth-link fw-bold">Register Now</a>
                                </p>
                            </div>
                        </form>
                    </div>

                    <!-- Additional Links -->
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            <i class="bi bi-shield-check me-1"></i>
                            Your data is secure and encrypted
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Password Toggle Script -->
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const checkbox = document.getElementById('showPassword');
            passwordField.type = checkbox.checked ? 'text' : 'password';
        }

        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>

<?php
include '../includes/footer.php';
?>