<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Admin Dashboard";

require_once '../config/db.php';

$stats = [];

$query = "SELECT COUNT(*) as total FROM books";
$result = mysqli_query($conn, $query);
$stats['total_books'] = mysqli_fetch_assoc($result)['total'];

$query = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($conn, $query);
$stats['total_users'] = mysqli_fetch_assoc($result)['total'];

$query = "SELECT COUNT(*) as total FROM orders";
$result = mysqli_query($conn, $query);
$stats['total_orders'] = mysqli_fetch_assoc($result)['total'];

$query = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
$result = mysqli_query($conn, $query);
$stats['pending_orders'] = mysqli_fetch_assoc($result)['total'];

$query = "SELECT COUNT(*) as total FROM competitions WHERE status = 'active'";
$result = mysqli_query($conn, $query);
$stats['active_competitions'] = mysqli_fetch_assoc($result)['total'];

$query = "SELECT SUM(total_amount) as total FROM orders WHERE status = 'paid'";
$result = mysqli_query($conn, $query);
$stats['total_revenue'] = mysqli_fetch_assoc($result)['total'] ?? 0;

$recent_orders = [];
$query = "SELECT o.*, u.full_name, b.title as book_title 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.user_id 
          LEFT JOIN books b ON o.book_id = b.book_id 
          ORDER BY o.order_date DESC 
          LIMIT 5";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $recent_orders[] = $row;
}

$recent_users = [];
$query = "SELECT * FROM users ORDER BY registered_at DESC LIMIT 5";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $recent_users[] = $row;
}

$popular_books = [];
$query = "SELECT b.*, COUNT(o.order_id) as order_count 
          FROM books b 
          LEFT JOIN orders o ON b.book_id = o.book_id 
          GROUP BY b.book_id 
          ORDER BY order_count DESC 
          LIMIT 5";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $popular_books[] = $row;
}

include '../includes/header.php';
?>

<!-- Admin Dashboard -->
<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <div class="admin-brand">
                <i class="bi bi-shield-check"></i>
                <span>Admin Panel</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item active">
                    <a href="index.php" class="nav-link">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_books.php" class="nav-link">
                        <i class="bi bi-book"></i>
                        <span>Manage Books</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_users.php" class="nav-link">
                        <i class="bi bi-people"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_orders.php" class="nav-link">
                        <i class="bi bi-bag-check"></i>
                        <span>Manage Orders</span>
                        <?php if ($stats['pending_orders'] > 0): ?>
                            <span class="badge-notify"><?php echo $stats['pending_orders']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_competitions.php" class="nav-link">
                        <i class="bi bi-trophy"></i>
                        <span>Competitions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="winners.php" class="nav-link">
                        <i class="bi bi-award"></i>
                        <span>Winners</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="payments.php" class="nav-link">
                        <i class="bi bi-credit-card"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li class="nav-item">
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
                    <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
                    <small>Administrator</small>
                </div>
            </div>
            <a href="logout.php" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>
    
    <!-- Admin Main Content -->
    <main class="admin-main">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="header-left">
                <button class="btn-toggle-sidebar" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="header-title">Dashboard Overview</h1>
            </div>
            <div class="header-right">
                <div class="header-date">
                    <i class="bi bi-calendar-event"></i>
                    <span><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>
        </header>
        
        <!-- Dashboard Content -->
        <div class="admin-content">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <!-- Total Books -->
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="bi bi-book-fill"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value"><?php echo number_format($stats['total_books']); ?></h3>
                        <p class="stat-label">Total Books</p>
                    </div>
                    <div class="stat-trend">
                        <i class="bi bi-arrow-up"></i>
                        <span>12% this month</span>
                    </div>
                </div>
                
                <!-- Total Users -->
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value"><?php echo number_format($stats['total_users']); ?></h3>
                        <p class="stat-label">Total Users</p>
                    </div>
                    <div class="stat-trend">
                        <i class="bi bi-arrow-up"></i>
                        <span>8% this month</span>
                    </div>
                </div>
                
                <!-- Total Orders -->
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="bi bi-bag-fill"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value"><?php echo number_format($stats['total_orders']); ?></h3>
                        <p class="stat-label">Total Orders</p>
                    </div>
                    <div class="stat-trend">
                        <i class="bi bi-arrow-up"></i>
                        <span>15% this month</span>
                    </div>
                </div>
                
                <!-- Total Revenue -->
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-details">
                        <h3 class="stat-value">$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p class="stat-label">Total Revenue</p>
                    </div>
                    <div class="stat-trend">
                        <i class="bi bi-arrow-up"></i>
                        <span>20% this month</span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2 class="section-title">
                    <i class="bi bi-lightning-charge-fill me-2"></i>Quick Actions
                </h2>
                <div class="actions-grid">
                    <a href="manage_books.php?action=add" class="action-card">
                        <i class="bi bi-plus-circle"></i>
                        <span>Add New Book</span>
                    </a>
                    <a href="manage_orders.php" class="action-card">
                        <i class="bi bi-eye"></i>
                        <span>View Orders</span>
                    </a>
                    <a href="manage_competitions.php?action=add" class="action-card">
                        <i class="bi bi-trophy"></i>
                        <span>Create Competition</span>
                    </a>
                    <a href="manage_users.php" class="action-card">
                        <i class="bi bi-person-plus"></i>
                        <span>Manage Users</span>
                    </a>
                </div>
            </div>
            
            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Orders -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-clock-history me-2"></i>Recent Orders
                        </h3>
                        <a href="manage_orders.php" class="btn-link">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Book</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_orders)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No orders found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($order['book_title'], 0, 30)); ?>...</td>
                                                <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-person-check me-2"></i>Recent Users
                        </h3>
                        <a href="manage_users.php" class="btn-link">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="user-list">
                            <?php if (empty($recent_users)): ?>
                                <p class="text-center text-muted">No users found</p>
                            <?php else: ?>
                                <?php foreach ($recent_users as $user): ?>
                                    <div class="user-item">
                                        <div class="user-avatar">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <div class="user-info">
                                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                            <small><?php echo htmlspecialchars($user['email']); ?></small>
                                        </div>
                                        <div class="user-date">
                                            <small><?php echo date('M j, Y', strtotime($user['registered_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Popular Books -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-star-fill me-2"></i>Popular Books
                    </h3>
                    <a href="manage_books.php" class="btn-link">View All</a>
                </div>
                <div class="card-body">
                    <div class="books-grid">
                        <?php if (empty($popular_books)): ?>
                            <p class="text-center text-muted">No books found</p>
                        <?php else: ?>
                            <?php foreach ($popular_books as $book): ?>
                                <div class="book-item">
                                    <div class="book-cover">
                                        <i class="bi bi-book"></i>
                                    </div>
                                    <div class="book-info">
                                        <strong><?php echo htmlspecialchars(substr($book['title'], 0, 30)); ?>...</strong>
                                        <small><?php echo htmlspecialchars($book['author']); ?></small>
                                        <div class="book-stats">
                                            <span class="badge-count"><?php echo $book['order_count']; ?> orders</span>
                                            <span class="badge-price">$<?php echo number_format($book['price'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Toggle Sidebar Script -->
<script>
document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
});
</script>

<?php
include '../includes/footer.php';
?>
