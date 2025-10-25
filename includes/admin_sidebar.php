<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

function isActiveSidebar($page) {
    global $current_page;
    return ($current_page === $page) ? 'active' : '';
}
?>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="admin-brand">
            <i class="bi bi-shield-check"></i>
            <span>Admin Panel</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item <?php echo isActiveSidebar('index.php'); ?>">
                <a href="index.php" class="nav-link">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActiveSidebar('manage_books.php'); ?>">
                <a href="manage_books.php" class="nav-link">
                    <i class="bi bi-book"></i>
                    <span>Manage Books</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActiveSidebar('manage_users.php'); ?>">
                <a href="manage_users.php" class="nav-link">
                    <i class="bi bi-people"></i>
                    <span>Manage Users</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActiveSidebar('manage_subscriptions.php'); ?>">
                <a href="manage_subscriptions.php" class="nav-link">
                    <i class="bi bi-star-fill"></i>
                    <span>Manage Subscriptions</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActiveSidebar('manage_orders.php'); ?>">
                <a href="manage_orders.php" class="nav-link">
                    <i class="bi bi-bag-check"></i>
                    <span>Manage Orders</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActiveSidebar('manage_competitions.php'); ?>">
                <a href="manage_competitions.php" class="nav-link">
                    <i class="bi bi-trophy"></i>
                    <span>Manage Competitions</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActiveSidebar('winners.php'); ?>">
                <a href="winners.php" class="nav-link">
                    <i class="bi bi-award"></i>
                    <span>Manage Winners</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActiveSidebar('payments.php'); ?>">
                <a href="payments.php" class="nav-link">
                    <i class="bi bi-credit-card"></i>
                    <span>Payments</span>
                </a>
            </li>
            <li class="nav-item <?php echo isActiveSidebar('settings.php'); ?>">
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

<div class="sidebar-overlay" id="sidebarOverlay"></div>