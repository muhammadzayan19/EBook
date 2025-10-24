<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Orders";
require_once '../config/db.php';

// Handle Update Order Status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE orders SET status = '$new_status' WHERE order_id = $order_id";
    
    if (mysqli_query($conn, $query)) {
        // Update payment status if order is marked as paid
        if ($new_status === 'paid') {
            $payment_query = "UPDATE payments SET payment_status = 'completed' WHERE order_id = $order_id";
            mysqli_query($conn, $payment_query);
        }
        $_SESSION['success_msg'] = "Order status updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error updating order: " . mysqli_error($conn);
    }
    header("Location: manage_orders.php");
    exit();
}

// Handle Delete Order
if (isset($_GET['delete'])) {
    $order_id = intval($_GET['delete']);
    
    // Delete associated payment first
    $delete_payment = "DELETE FROM payments WHERE order_id = $order_id";
    mysqli_query($conn, $delete_payment);
    
    // Delete order
    $delete_order = "DELETE FROM orders WHERE order_id = $order_id";
    
    if (mysqli_query($conn, $delete_order)) {
        $_SESSION['success_msg'] = "Order deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting order: " . mysqli_error($conn);
    }
    header("Location: manage_orders.php");
    exit();
}

// Filters
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';
$order_type_filter = isset($_GET['order_type']) ? mysqli_real_escape_string($conn, $_GET['order_type']) : '';

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["1=1"];

if ($status_filter) {
    $where_conditions[] = "o.status = '$status_filter'";
}

if ($search) {
    $where_conditions[] = "(u.full_name LIKE '%$search%' OR u.email LIKE '%$search%' OR b.title LIKE '%$search%' OR o.order_id LIKE '%$search%')";
}

if ($date_from) {
    $where_conditions[] = "DATE(o.order_date) >= '$date_from'";
}

if ($date_to) {
    $where_conditions[] = "DATE(o.order_date) <= '$date_to'";
}

if ($order_type_filter) {
    $where_conditions[] = "o.order_type = '$order_type_filter'";
}

$where_clause = implode(" AND ", $where_conditions);

// Count total records
$count_query = "SELECT COUNT(*) as total FROM orders o 
                LEFT JOIN users u ON o.user_id = u.user_id 
                LEFT JOIN books b ON o.book_id = b.book_id 
                WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Fetch orders
$query = "SELECT o.*, u.full_name, u.email, u.phone, b.title as book_title, b.type as book_type, 
          p.payment_method, p.payment_status, p.payment_date
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.user_id 
          LEFT JOIN books b ON o.book_id = b.book_id 
          LEFT JOIN payments p ON o.order_id = p.order_id
          WHERE $where_clause 
          ORDER BY o.order_date DESC 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_orders,
    SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as total_revenue
    FROM orders";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Fetch order for editing
$edit_order = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "SELECT o.*, u.full_name, u.email, b.title as book_title 
                   FROM orders o 
                   LEFT JOIN users u ON o.user_id = u.user_id 
                   LEFT JOIN books b ON o.book_id = b.book_id 
                   WHERE o.order_id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_order = mysqli_fetch_assoc($edit_result);
}

include '../includes/header.php';
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
                <h1 class="header-title">Manage Orders</h1>
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
            <div class="orders-stats-grid">
                <div class="order-stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="bi bi-basket"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_orders']); ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="order-stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['pending_orders']); ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
                
                <div class="order-stat-card stat-success">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['paid_orders']); ?></h3>
                        <p>Paid Orders</p>
                    </div>
                </div>
                
                <div class="order-stat-card stat-info">
                    <div class="stat-icon">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-content">
                        <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <!-- Edit Order Form (Modal Style) -->
            <?php if ($edit_order): ?>
            <div class="order-edit-form-container">
                <div class="form-card">
                    <div class="form-card-header">
                        <h3><i class="bi bi-pencil-square"></i> Edit Order #<?php echo $edit_order['order_id']; ?></h3>
                        <a href="manage_orders.php" class="btn-close-form">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                    <div class="form-card-body">
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $edit_order['order_id']; ?>">
                            
                            <div class="order-details-grid">
                                <div class="detail-group">
                                    <label><i class="bi bi-person"></i> Customer</label>
                                    <p class="detail-value"><?php echo htmlspecialchars($edit_order['full_name']); ?></p>
                                    <small><?php echo htmlspecialchars($edit_order['email']); ?></small>
                                </div>
                                
                                <div class="detail-group">
                                    <label><i class="bi bi-book"></i> Book</label>
                                    <p class="detail-value"><?php echo htmlspecialchars($edit_order['book_title']); ?></p>
                                </div>
                                
                                <div class="detail-group">
                                    <label><i class="bi bi-currency-dollar"></i> Amount</label>
                                    <p class="detail-value">$<?php echo number_format($edit_order['total_amount'], 2); ?></p>
                                </div>
                                
                                <div class="detail-group">
                                    <label><i class="bi bi-calendar"></i> Order Date</label>
                                    <p class="detail-value"><?php echo date('M j, Y', strtotime($edit_order['order_date'])); ?></p>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-toggle-on"></i>
                                    Order Status *
                                </label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" <?php echo $edit_order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="paid" <?php echo $edit_order['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="cancelled" <?php echo $edit_order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="refunded" <?php echo $edit_order['status'] == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                </select>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="update_status" class="btn-submit">
                                    <i class="bi bi-check-circle"></i> Update Order
                                </button>
                                <a href="manage_orders.php" class="btn-cancel">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Filters Section -->
            <div class="orders-filters-section">
                <div class="filters-header">
                    <h3><i class="bi bi-funnel"></i> Filter Orders</h3>
                    <?php if ($status_filter || $search || $date_from || $date_to || $order_type_filter): ?>
                        <a href="manage_orders.php" class="btn-clear-filters">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
                
                <form method="GET" class="orders-filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Search</label>
                            <div class="search-input-wrapper">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="filter-input" 
                                       placeholder="Order ID, Customer, Book..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Status</label>
                            <select name="status" class="filter-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="paid" <?php echo $status_filter == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="refunded" <?php echo $status_filter == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Order Type</label>
                            <select name="order_type" class="filter-select">
                                <option value="">All Types</option>
                                <option value="purchase" <?php echo $order_type_filter == 'purchase' ? 'selected' : ''; ?>>Purchase</option>
                                <option value="subscription" <?php echo $order_type_filter == 'subscription' ? 'selected' : ''; ?>>Subscription</option>
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
            
            <!-- Orders Table -->
            <div class="orders-table-card">
                <div class="table-header">
                    <h3><i class="bi bi-table"></i> Orders List</h3>
                    <span class="record-count"><?php echo number_format($total_records); ?> total records</span>
                </div>
                
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Book</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="9" class="no-data-row">
                                        <div class="no-data">
                                            <i class="bi bi-inbox"></i>
                                            <p>No orders found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong class="order-id">#<?php echo $order['order_id']; ?></strong>
                                        </td>
                                        <td>
                                            <div class="customer-cell">
                                                <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                                <small><?php echo htmlspecialchars($order['email']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="book-cell">
                                                <span class="book-title"><?php echo htmlspecialchars(substr($order['book_title'], 0, 40)); ?><?php echo strlen($order['book_title']) > 40 ? '...' : ''; ?></span>
                                                <small class="book-type-badge badge-<?php echo $order['book_type']; ?>">
                                                    <?php echo strtoupper($order['book_type']); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="order-type-badge type-<?php echo $order['order_type']; ?>">
                                                <?php echo ucfirst($order['order_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="amount-value">$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($order['payment_method']): ?>
                                                <div class="payment-cell">
                                                    <span class="payment-method-badge">
                                                        <i class="bi bi-credit-card"></i>
                                                        <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?>
                                                    </span>
                                                    <small class="payment-status status-<?php echo $order['payment_status']; ?>">
                                                        <?php echo ucfirst($order['payment_status']); ?>
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="order-status-badge status-<?php echo $order['status']; ?>">
                                                <?php 
                                                $status_icons = [
                                                    'pending' => 'hourglass-split',
                                                    'paid' => 'check-circle',
                                                    'cancelled' => 'x-circle',
                                                    'refunded' => 'arrow-counterclockwise'
                                                ];
                                                ?>
                                                <i class="bi bi-<?php echo $status_icons[$order['status']] ?? 'circle'; ?>"></i>
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-cell">
                                                <strong><?php echo date('M j, Y', strtotime($order['order_date'])); ?></strong>
                                                <small><?php echo date('g:i A', strtotime($order['order_date'])); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-table-action btn-view" onclick="viewOrder(<?php echo $order['order_id']; ?>)" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <a href="?edit=<?php echo $order['order_id']; ?>" class="btn-table-action btn-edit" title="Edit Order">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?delete=<?php echo $order['order_id']; ?>" class="btn-table-action btn-delete" 
                                                   onclick="return confirm('Are you sure you want to delete this order?')" title="Delete Order">
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
                                <a href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo $search; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&order_type=<?php echo $order_type_filter; ?>" class="page-link">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo $search; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&order_type=<?php echo $order_type_filter; ?>" 
                                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo $search; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&order_type=<?php echo $order_type_filter; ?>" class="page-link">
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

<!-- View Order Modal -->
<div id="viewOrderModal" class="order-modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3><i class="bi bi-receipt"></i> Order Details</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="orderDetailsContent">
            <!-- Content loaded via JavaScript -->
        </div>
    </div>
</div>

<script>
document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
});

function viewOrder(orderId) {
    const modal = document.getElementById('viewOrderModal');
    const content = document.getElementById('orderDetailsContent');
    
    // Show modal
    modal.style.display = 'flex';
    content.innerHTML = '<div class="loading"><i class="bi bi-hourglass-split"></i> Loading...</div>';
    
    // Fetch order details (you can create a separate PHP endpoint for this)
    fetch(`get_order_details.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
            } else {
                content.innerHTML = '<div class="error">Failed to load order details</div>';
            }
        })
        .catch(error => {
            content.innerHTML = '<div class="error">Error loading order details</div>';
        });
}

function closeModal() {
    document.getElementById('viewOrderModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('viewOrderModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include '../includes/footer.php'; ?>