<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';
require_once '../includes/subscription_helper.php';

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($full_name)) {
        $error_message = 'Full name is required.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $full_name, $phone, $address, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $full_name;
            $success_message = 'Profile updated successfully!';
        } else {
            $error_message = 'Failed to update profile.';
        }
        $stmt->close();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'All password fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();
        
        if (password_verify($current_password, $user_data['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = 'Password changed successfully!';
            } else {
                $error_message = 'Failed to update password.';
            }
            $update_stmt->close();
        } else {
            $error_message = 'Current password is incorrect.';
        }
    }
}

// Get user data
$stmt = $conn->prepare("SELECT user_id, full_name, email, phone, address, registered_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get statistics
$order_stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_count = $order_stmt->get_result()->fetch_assoc()['order_count'];
$order_stmt->close();

$submission_stmt = $conn->prepare("SELECT COUNT(*) as submission_count FROM submissions WHERE user_id = ?");
$submission_stmt->bind_param("i", $user_id);
$submission_stmt->execute();
$submission_count = $submission_stmt->get_result()->fetch_assoc()['submission_count'];
$submission_stmt->close();

$download_stmt = $conn->prepare("SELECT COUNT(*) as download_count FROM download_logs WHERE user_id = ?");
$download_stmt->bind_param("i", $user_id);
$download_stmt->execute();
$download_count = $download_stmt->get_result()->fetch_assoc()['download_count'];
$download_stmt->close();

$win_stmt = $conn->prepare("SELECT COUNT(*) as win_count FROM winners WHERE user_id = ?");
$win_stmt->bind_param("i", $user_id);
$win_stmt->execute();
$win_count = $win_stmt->get_result()->fetch_assoc()['win_count'];
$win_stmt->close();

// Get subscription info
$subscription = hasActiveSubscription($conn, $user_id);
$sub_access_count = 0;

if ($subscription) {
    $access_stmt = $conn->prepare("SELECT COUNT(*) as count FROM subscription_access WHERE subscription_id = ?");
    $access_stmt->bind_param("i", $subscription['subscription_id']);
    $access_stmt->execute();
    $sub_access_count = $access_stmt->get_result()->fetch_assoc()['count'];
    $access_stmt->close();
}

// Get recent activity
$recent_activity = [];

// Recent orders
$recent_orders = $conn->prepare("
    SELECT 'order' as type, o.order_date as activity_date, b.title as description, o.status
    FROM orders o
    JOIN books b ON o.book_id = b.book_id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
    LIMIT 3
");
$recent_orders->bind_param("i", $user_id);
$recent_orders->execute();
$orders_result = $recent_orders->get_result();
while ($row = $orders_result->fetch_assoc()) {
    $recent_activity[] = $row;
}
$recent_orders->close();

// Recent submissions
$recent_subs = $conn->prepare("
    SELECT 'submission' as type, s.submitted_at as activity_date, c.title as description, 'submitted' as status
    FROM submissions s
    JOIN competitions c ON s.comp_id = c.comp_id
    WHERE s.user_id = ?
    ORDER BY s.submitted_at DESC
    LIMIT 2
");
$recent_subs->bind_param("i", $user_id);
$recent_subs->execute();
$subs_result = $recent_subs->get_result();
while ($row = $subs_result->fetch_assoc()) {
    $recent_activity[] = $row;
}
$recent_subs->close();

// Sort by date
usort($recent_activity, function($a, $b) {
    return strtotime($b['activity_date']) - strtotime($a['activity_date']);
});

$page_title = "My Profile";
include '../includes/header.php';
?>

<section class="profile-header">
    <div class="container">
        <div class="text-center text-white">
            <h1 class="display-5 fw-bold mb-2">Welcome Back, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
            <p class="lead mb-0">Manage your account and explore your reading journey</p>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="text-center">
                        <h3 class="mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="text-muted mb-3">
                            <i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?>
                        </p>
                        <p class="text-muted small">
                            <i class="bi bi-calendar-check me-2"></i>Member since 
                            <?php echo date('F Y', strtotime($user['registered_at'])); ?>
                        </p>
                    </div>
                    <hr class="my-3">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="bi bi-pencil me-2"></i>Edit Profile
                        </button>
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="bi bi-key me-2"></i>Change Password
                        </button>
                        <a href="books.php" class="btn btn-outline-primary">
                            <i class="bi bi-book me-2"></i>Browse Books
                        </a>
                        <a href="competition.php" class="btn btn-outline-success">
                            <i class="bi bi-trophy me-2"></i>Competitions
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $order_count; ?></div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $submission_count; ?></div>
                            <div class="stat-label">Submissions</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $download_count; ?></div>
                            <div class="stat-label">Downloads</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-box <?php echo $subscription ? 'stat-success' : ''; ?>">
                            <div class="stat-number">
                                <?php echo $subscription ? '<i class="bi bi-check-circle"></i>' : '<i class="bi bi-x-circle"></i>'; ?>
                            </div>
                            <div class="stat-label">Subscription</div>
                        </div>
                    </div>
                </div>

                <?php if ($win_count > 0): ?>
                <div class="alert alert-success mb-4" style="border-left: 4px solid #10b981;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-trophy-fill me-3" style="font-size: 2rem;"></i>
                        <div>
                            <h5 class="mb-1">Competition Winner!</h5>
                            <p class="mb-0">Congratulations! You have won <?php echo $win_count; ?> competition<?php echo $win_count > 1 ? 's' : ''; ?>! 🎉</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($subscription): ?>
                <div class="alert alert-success mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1"><i class="bi bi-star-fill me-2"></i>Active Subscription</h5>
                            <p class="mb-0">
                                <strong><?php echo ucfirst($subscription['plan_type']); ?> Plan</strong> • 
                                Expires: <?php echo date('F j, Y', strtotime($subscription['end_date'])); ?> •
                                Access to <?php echo $sub_access_count; ?> books
                            </p>
                        </div>
                        <div>
                            <a href="manage_subscription.php" class="btn btn-outline-success">
                                Manage
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1"><i class="bi bi-info-circle me-2"></i>No Active Subscription</h5>
                            <p class="mb-0">Subscribe now for unlimited access to our entire book collection</p>
                        </div>
                        <a href="subscription.php" class="btn btn-primary">
                            Subscribe Now
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <div class="profile-card">
                    <h4 class="mb-4">
                        <i class="bi bi-person-badge me-2"></i>Account Information
                    </h4>
                    
                    <div class="info-group">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">
                            <?php echo $user['phone'] ? htmlspecialchars($user['phone']) : '<span class="text-muted">Not provided</span>'; ?>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Address</div>
                        <div class="info-value">
                            <?php echo $user['address'] ? nl2br(htmlspecialchars($user['address'])) : '<span class="text-muted">Not provided</span>'; ?>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Member Since</div>
                        <div class="info-value"><?php echo date('F j, Y', strtotime($user['registered_at'])); ?></div>
                    </div>
                </div>

                <div class="profile-card mt-4">
                    <h4 class="mb-4">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </h4>
                    <?php if (!empty($recent_activity)): ?>
                        <div class="activity-timeline">
                            <?php foreach ($recent_activity as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <?php if ($activity['type'] === 'order'): ?>
                                        <i class="bi bi-cart-check"></i>
                                    <?php else: ?>
                                        <i class="bi bi-pencil-square"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-content">
                                    <h6 class="mb-1">
                                        <?php if ($activity['type'] === 'order'): ?>
                                            Ordered: <?php echo htmlspecialchars($activity['description']); ?>
                                        <?php else: ?>
                                            Submitted entry for: <?php echo htmlspecialchars($activity['description']); ?>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?php echo date('F j, Y', strtotime($activity['activity_date'])); ?>
                                    </small>
                                    <?php if ($activity['type'] === 'order'): ?>
                                        <span class="badge bg-<?php echo $activity['status'] === 'paid' ? 'success' : 'warning'; ?> ms-2">
                                            <?php echo ucfirst($activity['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0">No recent activity yet</p>
                            <small>Start exploring books and competitions!</small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <a href="my_orders.php" class="quick-link-card">
                            <i class="bi bi-cart"></i>
                            <h6>My Orders</h6>
                            <span class="badge"><?php echo $order_count; ?></span>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="my_books.php" class="quick-link-card">
                            <i class="bi bi-book"></i>
                            <h6>My Library</h6>
                            <span class="badge"><?php echo $order_count; ?></span>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="my_submissions.php" class="quick-link-card">
                            <i class="bi bi-file-text"></i>
                            <h6>My Submissions</h6>
                            <span class="badge"><?php echo $submission_count; ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Edit Profile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email_display" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email_display" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small class="text-muted">Email cannot be changed</small>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-key me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="passwordForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password *</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               required minlength="6">
                        <small class="text-muted">Must be at least 6 characters</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               required minlength="6">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="showPasswords" onclick="togglePasswordVisibility()">
                        <label class="form-check-label" for="showPasswords">Show passwords</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Subscription Modal -->
<?php if ($subscription): ?>
<div class="modal fade" id="manageSubscriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-star-fill me-2"></i>Manage Subscription
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="subscription-details">
                    <div class="detail-row">
                        <span>Plan Type:</span>
                        <strong><?php echo ucfirst($subscription['plan_type']); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Status:</span>
                        <span class="badge bg-success"><?php echo ucfirst($subscription['status']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Start Date:</span>
                        <strong><?php echo date('F j, Y', strtotime($subscription['start_date'])); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>End Date:</span>
                        <strong><?php echo date('F j, Y', strtotime($subscription['end_date'])); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Amount:</span>
                        <strong>$<?php echo number_format($subscription['amount'], 2); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Books Accessed:</span>
                        <strong><?php echo $sub_access_count; ?> books</strong>
                    </div>
                    <div class="detail-row">
                        <span>Auto-Renew:</span>
                        <strong><?php echo $subscription['auto_renew'] ? 'Enabled' : 'Disabled'; ?></strong>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>Your subscription gives you unlimited access to all subscription-eligible books.</small>
                </div>
            </div>
            <div class="modal-footer">
                <a href="books.php?subscription=1" class="btn btn-success">
                    <i class="bi bi-book me-2"></i>Browse Books
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function togglePasswordVisibility() {
    const currentPass = document.getElementById('current_password');
    const newPass = document.getElementById('new_password');
    const confirmPass = document.getElementById('confirm_password');
    const checkbox = document.getElementById('showPasswords');
    
    const type = checkbox.checked ? 'text' : 'password';
    currentPass.type = type;
    newPass.type = type;
    confirmPass.type = type;
}

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass !== confirmPass) {
        e.preventDefault();
        alert('New passwords do not match!');
        return false;
    }
});
</script>

<style>
.activity-timeline {
    position: relative;
    padding-left: 40px;
}

.activity-item {
    position: relative;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.activity-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.activity-icon {
    position: absolute;
    left: -40px;
    top: 0;
    width: 32px;
    height: 32px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.activity-content h6 {
    margin-bottom: 0.5rem;
}

.quick-link-card {
    display: block;
    padding: 1.5rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
}

.quick-link-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.quick-link-card i {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.quick-link-card h6 {
    margin-bottom: 0.5rem;
}

.quick-link-card .badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--primary-color);
}

.subscription-details {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 8px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
    border-bottom: none;
}
</style>

<?php
include '../includes/footer.php';
?>