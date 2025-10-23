<?php
// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default active page name
$current_page = basename($_SERVER['PHP_SELF']);

?>

<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="admin-brand">
            <i class="bi bi-shield-check"></i>
            <span>Admin Panel</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item <?php echo isActive('index.php'); ?>">
                <a href="index.php" class="nav-link">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActive('manage_books.php'); ?>">
                <a href="manage_books.php" class="nav-link">
                    <i class="bi bi-book"></i>
                    <span>Manage Books</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActive('manage_users.php'); ?>">
                <a href="manage_users.php" class="nav-link">
                    <i class="bi bi-people"></i>
                    <span>Manage Users</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActive('manage_orders.php'); ?>">
                <a href="manage_orders.php" class="nav-link">
                    <i class="bi bi-bag-check"></i>
                    <span>Manage Orders</span>
                    <?php if (isset($stats['pending_orders']) && $stats['pending_orders'] > 0): ?>
                        <span class="badge-notify"><?php echo $stats['pending_orders']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item <?php echo isActive('manage_competitions.php'); ?>">
                <a href="manage_competitions.php" class="nav-link">
                    <i class="bi bi-trophy"></i>
                    <span>Competitions</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActive('winners.php'); ?>">
                <a href="winners.php" class="nav-link">
                    <i class="bi bi-award"></i>
                    <span>Winners</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActive('payments.php'); ?>">
                <a href="payments.php" class="nav-link">
                    <i class="bi bi-credit-card"></i>
                    <span>Payments</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActive('settings.php'); ?>">
                <a href="settings.php" class="nav-link">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-profile">
            <div class="profile-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="profile-info">
                <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></strong>
                <small>Administrator</small>
            </div>
        </div>
        <a href="logout.php" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
