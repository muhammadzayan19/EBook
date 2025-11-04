<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

function isActiveSidebar($page) {
    global $current_page;
    return ($current_page === $page) ? 'active' : '';
}
$is_super_admin = false;
if (isset($_SESSION['admin_id'])) {
    require_once __DIR__ . '/../config/db.php';
    $admin_id = $_SESSION['admin_id'];
    $check_role = mysqli_query($conn, "SELECT role FROM admin_users WHERE admin_id = $admin_id");
    if ($check_role && $role_data = mysqli_fetch_assoc($check_role)) {
        $is_super_admin = ($role_data['role'] === 'super_admin');
    }
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
            <?php if ($is_super_admin): ?>
                <li class="nav-item <?php echo isActiveSidebar('manage_staff.php'); ?>">
                    <a href="manage_staff.php" class="nav-link">
                        <i class="bi bi-shield-lock"></i>
                        <span>Manage Staff</span>
                    </a>
                </li>
            <?php endif; ?>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.querySelector('.btn-toggle-sidebar');
    const body = document.body;
    
    // Toggle sidebar on button click
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('sidebar-open');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });
    }
    
    // Close sidebar when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            body.classList.remove('sidebar-open');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }
    
    // Close sidebar when clicking a nav link on mobile
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                body.classList.remove('sidebar-open');
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    });
    
    // Handle window resize - close sidebar if opened on mobile and resized to desktop
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 992) {
                body.classList.remove('sidebar-open');
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        }, 250);
    });
    
    // Prevent body scroll when sidebar is open on mobile
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                if (body.classList.contains('sidebar-open')) {
                    body.style.overflow = 'hidden';
                } else {
                    body.style.overflow = '';
                }
            }
        });
    });
    
    observer.observe(body, {
        attributes: true,
        attributeFilter: ['class']
    });
});
</script>

<div class="sidebar-overlay" id="sidebarOverlay"></div>