<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Subscriptions";
require_once '../config/db.php';

// Handle Cancel Subscription
if (isset($_GET['cancel'])) {
    $subscription_id = intval($_GET['cancel']);
    $query = "UPDATE subscriptions SET status = 'cancelled', auto_renew = 0 WHERE subscription_id = $subscription_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success_msg'] = "Subscription cancelled successfully!";
    } else {
        $_SESSION['error_msg'] = "Error cancelling subscription: " . mysqli_error($conn);
    }
    header("Location: manage_subscriptions.php");
    exit();
}

// Handle Renew/Activate Subscription
if (isset($_GET['renew'])) {
    $subscription_id = intval($_GET['renew']);
    $new_end_date = date('Y-m-d H:i:s', strtotime('+30 days'));
    $query = "UPDATE subscriptions SET status = 'active', end_date = '$new_end_date', auto_renew = 1 WHERE subscription_id = $subscription_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success_msg'] = "Subscription renewed successfully!";
    } else {
        $_SESSION['error_msg'] = "Error renewing subscription: " . mysqli_error($conn);
    }
    header("Location: manage_subscriptions.php");
    exit();
}

// Handle Delete Subscription
if (isset($_GET['delete'])) {
    $subscription_id = intval($_GET['delete']);
    
    // Delete subscription access records first
    $delete_access = "DELETE FROM subscription_access WHERE subscription_id = $subscription_id";
    mysqli_query($conn, $delete_access);
    
    // Delete subscription
    $delete_query = "DELETE FROM subscriptions WHERE subscription_id = $subscription_id";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success_msg'] = "Subscription deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting subscription: " . mysqli_error($conn);
    }
    header("Location: manage_subscriptions.php");
    exit();
}

// Filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$plan_filter = isset($_GET['plan']) ? mysqli_real_escape_string($conn, $_GET['plan']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["1=1"];

if ($search) {
    $where_conditions[] = "(u.full_name LIKE '%$search%' OR u.email LIKE '%$search%')";
}

if ($status_filter) {
    $where_conditions[] = "s.status = '$status_filter'";
}

if ($plan_filter) {
    $where_conditions[] = "s.plan_type = '$plan_filter'";
}

if ($date_from) {
    $where_conditions[] = "DATE(s.start_date) >= '$date_from'";
}

if ($date_to) {
    $where_conditions[] = "DATE(s.start_date) <= '$date_to'";
}

$where_clause = implode(" AND ", $where_conditions);

// Count total records
$count_query = "SELECT COUNT(*) as total 
                FROM subscriptions s 
                LEFT JOIN users u ON s.user_id = u.user_id
                WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Fetch subscriptions
$query = "SELECT s.*, u.full_name, u.email, u.phone,
          DATEDIFF(s.end_date, NOW()) as days_remaining,
          (SELECT COUNT(*) FROM subscription_access WHERE subscription_id = s.subscription_id) as books_accessed
          FROM subscriptions s 
          LEFT JOIN users u ON s.user_id = u.user_id
          WHERE $where_clause 
          ORDER BY s.start_date DESC 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
$subscriptions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $subscriptions[] = $row;
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_subscriptions,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_subscriptions,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_subscriptions,
    COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired_subscriptions,
    SUM(CASE WHEN status = 'active' THEN amount ELSE 0 END) as active_revenue,
    SUM(amount) as total_revenue
    FROM subscriptions";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

include '../includes/admin_header.php';
?>

<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <button class="btn-toggle-sidebar" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="header-title">Manage Subscriptions</h1>
            </div>
            <div class="header-right">
                <div class="header-date">
                    <i class="bi bi-calendar-event"></i>
                    <span><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>
        </header>
        
        <div class="admin-content">
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success_msg']; ?>
                </div>
                <?php unset($_SESSION['success_msg']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error_msg']; ?>
                </div>
                <?php unset($_SESSION['error_msg']); ?>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="subscriptions-stats-grid">
                <div class="subscription-stat-card stat-primary">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_subscriptions']); ?></h3>
                        <p>Total Subscriptions</p>
                    </div>
                </div>
                
                <div class="subscription-stat-card stat-primary">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['active_subscriptions']); ?></h3>
                        <p>Active Subscriptions</p>
                    </div>
                </div>
                
                <div class="subscription-stat-card stat-primary">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['cancelled_subscriptions']); ?></h3>
                        <p>Cancelled</p>
                    </div>
                </div>
                
                <div class="subscription-stat-card stat-primary">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <!-- Page Header -->
            <div class="page-header-admin">
                <div>
                    <h1><i class="bi bi-arrow-repeat"></i> Subscriptions Management</h1>
                    <p class="text-muted">Monitor and manage all user subscriptions</p>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div class="subscriptions-filters-section">
                <div class="filters-header">
                    <h3><i class="bi bi-funnel"></i> Filter Subscriptions</h3>
                    <?php if ($search || $status_filter || $plan_filter || $date_from || $date_to): ?>
                        <a href="manage_subscriptions.php" class="btn-clear-filters">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
                
                <form method="GET" class="subscriptions-filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Search User</label>
                            <div class="search-input-wrapper">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="filter-input" 
                                       placeholder="Name or Email..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Status</label>
                            <select name="status" class="filter-select">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="expired" <?php echo $status_filter == 'expired' ? 'selected' : ''; ?>>Expired</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Plan Type</label>
                            <select name="plan" class="filter-select">
                                <option value="">All Plans</option>
                                <option value="monthly" <?php echo $plan_filter == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                <option value="yearly" <?php echo $plan_filter == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Date From</label>
                            <input type="date" name="date_from" class="filter-input" 
                                   value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Date To</label>
                            <input type="date" name="date_to" class="filter-input" 
                                   value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" class="btn-filter-submit">
                                <i class="bi bi-search"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Subscriptions Table -->
            <div class="subscriptions-table-card">
                <div class="table-header">
                    <h3><i class="bi bi-table"></i> Subscriptions List</h3>
                    <span class="record-count"><?php echo number_format($total_records); ?> total records</span>
                </div>
                
                <div class="table-responsive">
                    <table class="subscriptions-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subscriber</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Auto-Renew</th>
                                <th>Books Accessed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subscriptions)): ?>
                                <tr>
                                    <td colspan="10" class="no-data-row">
                                        <div class="no-data">
                                            <i class="bi bi-inbox"></i>
                                            <p>No subscriptions found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($subscriptions as $sub): ?>
                                    <tr>
                                        <td>
                                            <strong class="subscription-id">#<?php echo $sub['subscription_id']; ?></strong>
                                        </td>
                                        <td>
                                            <div class="subscriber-cell">
                                                <div class="subscriber-avatar">
                                                    <i class="bi bi-person-circle"></i>
                                                </div>
                                                <div class="subscriber-details">
                                                    <strong><?php echo htmlspecialchars($sub['full_name']); ?></strong>
                                                    <small><?php echo htmlspecialchars($sub['email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="plan-badge plan-<?php echo $sub['plan_type']; ?>">
                                                <i class="bi bi-<?php echo $sub['plan_type'] == 'monthly' ? 'calendar-month' : 'calendar-range'; ?>"></i>
                                                <?php echo ucfirst($sub['plan_type']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="amount-value">$<?php echo number_format($sub['amount'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <div class="date-cell">
                                                <strong><?php echo date('M j, Y', strtotime($sub['start_date'])); ?></strong>
                                                <small><?php echo date('g:i A', strtotime($sub['start_date'])); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="date-cell">
                                                <strong><?php echo date('M j, Y', strtotime($sub['end_date'])); ?></strong>
                                                <?php if ($sub['status'] == 'active' && $sub['days_remaining'] <= 7): ?>
                                                    <small class="text-warning">
                                                        <i class="bi bi-exclamation-triangle"></i> 
                                                        <?php echo $sub['days_remaining']; ?> days left
                                                    </small>
                                                <?php else: ?>
                                                    <small><?php echo date('g:i A', strtotime($sub['end_date'])); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="subscription-status-badge status-<?php echo $sub['status']; ?>">
                                                <i class="bi bi-<?php 
                                                    echo $sub['status'] == 'active' ? 'check-circle' : 
                                                        ($sub['status'] == 'cancelled' ? 'x-circle' : 'clock-history'); 
                                                ?>"></i>
                                                <?php echo ucfirst($sub['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($sub['auto_renew']): ?>
                                                <span class="auto-renew-badge enabled">
                                                    <i class="bi bi-arrow-repeat"></i> Enabled
                                                </span>
                                            <?php else: ?>
                                                <span class="auto-renew-badge disabled">
                                                    <i class="bi bi-x-lg"></i> Disabled
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="books-accessed-badge">
                                                <i class="bi bi-book"></i>
                                                <strong><?php echo $sub['books_accessed']; ?></strong>
                                                <?php echo $sub['books_accessed'] == 1 ? 'book' : 'books'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-table-action btn-view" 
                                                        onclick="viewSubscription(<?php echo $sub['subscription_id']; ?>)" 
                                                        title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                
                                                <?php if ($sub['status'] == 'cancelled' || $sub['status'] == 'expired'): ?>
                                                    <a href="?renew=<?php echo $sub['subscription_id']; ?>" 
                                                       class="btn-table-action btn-renew" 
                                                       onclick="return confirm('Renew this subscription for 30 more days?')" 
                                                       title="Renew Subscription">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($sub['status'] == 'active'): ?>
                                                    <a href="?cancel=<?php echo $sub['subscription_id']; ?>" 
                                                       class="btn-table-action btn-cancel" 
                                                       onclick="return confirm('Cancel this subscription?')" 
                                                       title="Cancel Subscription">
                                                        <i class="bi bi-x-circle"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="?delete=<?php echo $sub['subscription_id']; ?>" 
                                                   class="btn-table-action btn-delete" 
                                                   onclick="return confirm('Are you sure you want to delete this subscription? This action cannot be undone.')" 
                                                   title="Delete Subscription">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-wrapper">
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>&status=<?php echo $status_filter; ?>&plan=<?php echo $plan_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" class="page-link">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status=<?php echo $status_filter; ?>&plan=<?php echo $plan_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" 
                                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>&status=<?php echo $status_filter; ?>&plan=<?php echo $plan_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" class="page-link">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- View Subscription Modal -->
<div id="viewSubscriptionModal" class="subscription-modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3><i class="bi bi-info-circle"></i> Subscription Details</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="subscriptionDetailsContent">
            <!-- Content loaded via JavaScript -->
        </div>
    </div>
</div>

<script>
document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
});

function viewSubscription(subscriptionId) {
    const modal = document.getElementById('viewSubscriptionModal');
    const content = document.getElementById('subscriptionDetailsContent');
    
    // Show modal
    modal.style.display = 'flex';
    content.innerHTML = '<div class="loading"><i class="bi bi-hourglass-split"></i> Loading...</div>';
    
    // Fetch subscription details
    fetch(`get_subscription_details.php?id=${subscriptionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
            } else {
                content.innerHTML = '<div class="error"><i class="bi bi-exclamation-triangle"></i> Failed to load subscription details</div>';
            }
        })
        .catch(error => {
            content.innerHTML = '<div class="error"><i class="bi bi-exclamation-triangle"></i> Error loading subscription details</div>';
        });
}

function closeModal() {
    document.getElementById('viewSubscriptionModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('viewSubscriptionModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include '../includes/admin_footer.php'; ?>