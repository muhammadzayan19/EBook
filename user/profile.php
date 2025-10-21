<?php
/**
 * User Profile Dashboard
 * Protected page - requires authentication
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once '../config/db.php';

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT user_id, full_name, email, phone, address, registered_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get user's order count
$order_stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order_data = $order_result->fetch_assoc();
$order_count = $order_data['order_count'];
$order_stmt->close();

// Set page title
$page_title = "My Profile";

// Include header
include '../includes/header.php';
?>

<style>
.profile-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    padding: 60px 0;
    margin-bottom: 40px;
}

.profile-card {
    background: var(--white);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    margin-bottom: 2rem;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: var(--white);
    margin: 0 auto 1.5rem;
    box-shadow: var(--shadow-lg);
}

.stat-box {
    background: var(--bg-color);
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    border: 1px solid var(--border-color);
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
}

.info-group {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.info-group:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: var(--text-muted);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.info-value {
    font-size: 1.05rem;
    color: var(--text-color);
    font-weight: 500;
}
</style>

<!-- Profile Header -->
<section class="profile-header">
    <div class="container">
        <div class="text-center text-white">
            <h1 class="display-5 fw-bold mb-2">Welcome Back, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
            <p class="lead mb-0">Manage your account and explore your reading journey</p>
        </div>
    </div>
</section>

<!-- Profile Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- User Info Card -->
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
                        <a href="books.php" class="btn btn-primary">
                            <i class="bi bi-book me-2"></i>Browse Books
                        </a>
                        <a href="competition.php" class="btn btn-outline-primary">
                            <i class="bi bi-trophy me-2"></i>Competitions
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Stats Row -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $order_count; ?></div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-box">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Competitions</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-box">
                            <div class="stat-number">0</div>
                            <div class="stat-label">Downloads</div>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
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
                            <?php echo $user['phone'] ? htmlspecialchars($user['phone']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Address</div>
                        <div class="info-value">
                            <?php echo $user['address'] ? htmlspecialchars($user['address']) : 'Not provided'; ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" disabled>
                            <i class="bi bi-pencil me-2"></i>Edit Profile
                        </button>
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="bi bi-key me-2"></i>Change Password
                        </button>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="profile-card mt-4">
                    <h4 class="mb-4">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </h4>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0">No recent activity yet</p>
                        <small>Start exploring books and competitions!</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include '../includes/footer.php';
?>