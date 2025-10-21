<?php
// Start session
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

// Set page title for header
$page_title = "Register";

// Include header
include '../includes/header.php';

// Database connection
require_once '../config/db.php';

// Initialize variables
$error_message = '';
$success_message = '';
$form_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and collect input
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Store form data for repopulation on error
    $form_data = [
        'full_name' => $full_name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address
    ];
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (!$terms) {
        $error_message = 'Please accept the terms and conditions.';
    } else {
        // Check if email already exists
        $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $result = $check_email->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = 'Email address already registered. Please use a different email or <a href="login.php">login</a>.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $email, $hashed_password, $phone, $address);
            
            if ($stmt->execute()) {
                // Set success message in session and redirect to login
                $_SESSION['registration_success'] = 'Registration successful! Please login with your credentials.';
                header('Location: login.php');
                exit();
            } else {
                $error_message = 'Registration failed. Please try again later.';
            }
            
            $stmt->close();
        }
        
        $check_email->close();
    }
}
?>

    <!-- Register Section -->
    <section class="auth-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="auth-card">
                        <!-- Logo/Brand -->
                        <div class="auth-header">
                            <div class="auth-logo">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <h2 class="auth-title">Create Account</h2>
                            <p class="auth-subtitle">Join our community of readers and writers</p>
                        </div>

                        <!-- Error Message -->
                        <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Registration Form -->
                        <form method="POST" action="" class="auth-form needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               placeholder="Full Name" 
                                               value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>" required>
                                        <label for="full_name"><i class="bi bi-person me-2"></i>Full Name *</label>
                                        <div class="invalid-feedback">
                                            Please enter your full name.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="name@example.com" 
                                               value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                                        <label for="email"><i class="bi bi-envelope me-2"></i>Email Address *</label>
                                        <div class="invalid-feedback">
                                            Please enter a valid email address.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               placeholder="Phone Number" 
                                               value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                                        <label for="phone"><i class="bi bi-telephone me-2"></i>Phone Number</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-floating">
                                    <textarea class="form-control" id="address" name="address" 
                                              placeholder="Address" style="height: 100px"><?php echo htmlspecialchars($form_data['address'] ?? ''); ?></textarea>
                                    <label for="address"><i class="bi bi-geo-alt me-2"></i>Address</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Password" required minlength="6">
                                        <label for="password"><i class="bi bi-lock me-2"></i>Password *</label>
                                        <div class="invalid-feedback">
                                            Password must be at least 6 characters.
                                        </div>
                                    </div>
                                    <div class="password-strength mt-2" id="passwordStrength"></div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Confirm Password" required minlength="6">
                                        <label for="confirm_password"><i class="bi bi-lock-fill me-2"></i>Confirm Password *</label>
                                        <div class="invalid-feedback">
                                            Passwords must match.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="password-toggle mb-3">
                                <input type="checkbox" id="showPasswords" onclick="togglePasswords()">
                                <label for="showPasswords">Show Passwords</label>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="auth-link">Terms and Conditions</a> 
                                        and <a href="#" class="auth-link">Privacy Policy</a>
                                    </label>
                                    <div class="invalid-feedback">
                                        You must accept the terms and conditions.
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-person-check me-2"></i>Create Account
                            </button>

                            <div class="text-center">
                                <p class="mb-0">Already have an account? 
                                    <a href="login.php" class="auth-link fw-bold">Login Here</a>
                                </p>
                            </div>
                        </form>
                    </div>

                    <!-- Features -->
                    <div class="row mt-4">
                        <div class="col-md-4 text-center mb-3">
                            <div class="register-feature">
                                <i class="bi bi-book"></i>
                                <p>Access 500+ Books</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="register-feature">
                                <i class="bi bi-trophy"></i>
                                <p>Join Competitions</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="register-feature">
                                <i class="bi bi-download"></i>
                                <p>Download Anytime</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Password Toggle and Strength Script -->
    <script>
        // Toggle password visibility
        function togglePasswords() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const checkbox = document.getElementById('showPasswords');
            
            const type = checkbox.checked ? 'text' : 'password';
            password.type = type;
            confirmPassword.type = type;
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            let strength = 0;
            let feedback = '';
            let colorClass = '';
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            if (strength <= 2) {
                feedback = 'Weak';
                colorClass = 'weak';
            } else if (strength <= 3) {
                feedback = 'Medium';
                colorClass = 'medium';
            } else {
                feedback = 'Strong';
                colorClass = 'strong';
            }
            
            strengthDiv.innerHTML = `<small>Password Strength: <span class="strength-${colorClass}">${feedback}</span></small>`;
        });

        // Confirm password matching
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>

<?php
// Include footer
include '../includes/footer.php';
?>