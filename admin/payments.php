<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Payment Management";

require_once '../config/db.php';

// Handle Payment Status Update
if (isset($_POST['update_payment_status'])) {
    $payment_id = mysqli_real_escape_string($conn, $_POST['payment_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['payment_status']);
    
    $query = "UPDATE payments SET payment_status = '$new_status' WHERE payment_id = '$payment_id'";
    
    if (mysqli_query($conn, $query)) {
        // If payment is completed, update order status
        if ($new_status === 'completed') {
            $get_order = "SELECT order_id FROM payments WHERE payment_id = '$payment_id'";
            $result = mysqli_query($conn, $get_order);
            $payment = mysqli_fetch_assoc($result);
            
            mysqli_query($conn, "UPDATE orders SET status = 'paid' WHERE order_id = '" . $payment['order_id'] . "'");
        }
        
        $success_message = "Payment status updated successfully!";
    } else {
        $error_message = "Failed to update payment status.";
    }
}

// Handle Payment Deletion
if (isset($_POST['delete_payment'])) {
    $payment_id = mysqli_real_escape_string($conn, $_POST['payment_id']);
    
    $query = "DELETE FROM payments WHERE payment_id = '$payment_id'";
    
    if (mysqli_query($conn, $query)) {
        $success_message = "Payment record deleted successfully!";
    } else {
        $error_message = "Failed to delete payment record.";
    }
}

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$method_filter = isset($_GET['method']) ? mysqli_real_escape_string($conn, $_GET['method']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query with filters
$where_conditions = [];
if ($status_filter) {
    $where_conditions[] = "p.payment_status = '$status_filter'";
}
if ($method_filter) {
    $where_conditions[] = "p.payment_method = '$method_filter'";
}
if ($search) {
    $where_conditions[] = "(u.full_name LIKE '%$search%' OR u.email LIKE '%$search%' OR p.payment_id LIKE '%$search%')";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM payments p
                JOIN orders o ON p.order_id = o.order_id
                JOIN users u ON o.user_id = u.user_id
                $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Fetch payments with details
$query = "SELECT p.*, o.order_id, o.user_id, o.total_amount as order_total, o.status as order_status,
          u.full_name, u.email, u.phone,
          b.title as book_title
          FROM payments p
          JOIN orders o ON p.order_id = o.order_id
          JOIN users u ON o.user_id = u.user_id
          JOIN books b ON o.book_id = b.book_id
          $where_clause
          ORDER BY p.payment_date DESC
          LIMIT $limit OFFSET $offset";

$payments_result = mysqli_query($conn, $query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_payments,
    SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_revenue,
    SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END) as pending_amount,
    COUNT(CASE WHEN payment_status = 'completed' THEN 1 END) as completed_count,
    COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN payment_status = 'failed' THEN 1 END) as failed_count
    FROM payments";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Monthly revenue
$monthly_query = "SELECT 
    DATE_FORMAT(payment_date, '%Y-%m') as month,
    SUM(amount) as revenue
    FROM payments
    WHERE payment_status = 'completed'
    GROUP BY month
    ORDER BY month DESC
    LIMIT 6";

$monthly_result = mysqli_query($conn, $monthly_query);

include '../includes/admin_header.php';
?>

<link rel="stylesheet" href="../assets/css/admin_payments.css">

<!-- Admin Payments Page -->
<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <!-- Admin Main Content -->
    <main class="admin-main">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="header-left">
                <button class="btn-toggle-sidebar" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="header-title">Payment Management</h1>
            </div>
            <div class="header-right">
                <div class="header-date">
                    <i class="bi bi-calendar-event"></i>
                    <span><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>
        </header>
        
        <!-- Payments Content -->
        <div class="admin-content">
            <!-- Alert Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <div class="stat-details">
                        <h4>Total Payments</h4>
                        <h2><?php echo number_format($stats['total_payments']); ?></h2>
                        <p class="stat-subtitle">All time transactions</p>
                    </div>
                </div>
                
                <div class="stat-card stat-primary">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="stat-details">
                        <h4>Total Revenue</h4>
                        <h2>$<?php echo number_format($stats['total_revenue'], 2); ?></h2>
                        <p class="stat-subtitle"><?php echo $stats['completed_count']; ?> completed</p>
                    </div>
                </div>
                
                <div class="stat-card stat-primary">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-details">
                        <h4>Pending Payments</h4>
                        <h2>$<?php echo number_format($stats['pending_amount'], 2); ?></h2>
                        <p class="stat-subtitle"><?php echo $stats['pending_count']; ?> pending</p>
                    </div>
                </div>
                
                <div class="stat-card stat-primary">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h4>Failed Payments</h4>
                        <h2><?php echo $stats['failed_count']; ?></h2>
                        <p class="stat-subtitle">Require attention</p>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Revenue Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="bi bi-graph-up"></i> Monthly Revenue Trend</h3>
                </div>
                <div class="chart-body">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            
            <!-- Filters and Search -->
            <div class="payments-toolbar">
                <div class="toolbar-left">
                    <h3>Payment Records</h3>
                    <span class="record-count"><?php echo number_format($total_records); ?> total</span>
                </div>
                <div class="toolbar-right">
                    <form method="GET" class="filter-form">
                        <div class="form-group-inline">
                            <select name="status" class="filter-select" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            </select>
                            
                            <select name="method" class="filter-select" onchange="this.form.submit()">
                                <option value="">All Methods</option>
                                <option value="credit_card" <?php echo $method_filter === 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                                <option value="dd" <?php echo $method_filter === 'dd' ? 'selected' : ''; ?>>DD</option>
                                <option value="cheque" <?php echo $method_filter === 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                                <option value="vpp" <?php echo $method_filter === 'vpp' ? 'selected' : ''; ?>>VPP</option>
                            </select>
                            
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" placeholder="Search by name, email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            
                            <?php if ($status_filter || $method_filter || $search): ?>
                                <a href="payments.php" class="btn-clear-filter">
                                    <i class="bi bi-x-circle"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Payments Table -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="payments-table">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Customer</th>
                                <th>Book</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($payments_result) > 0): ?>
                                <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                                    <tr>
                                        <td>
                                            <span class="payment-id">#<?php echo $payment['payment_id']; ?></span>
                                        </td>
                                        <td>
                                            <div class="customer-info">
                                                <strong><?php echo htmlspecialchars($payment['full_name']); ?></strong>
                                                <small><?php echo htmlspecialchars($payment['email']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="book-title"><?php echo htmlspecialchars($payment['book_title']); ?></span>
                                        </td>
                                        <td>
                                            <span class="amount">$<?php echo number_format($payment['amount'], 2); ?></span>
                                        </td>
                                        <td>
                                            <span class="payment-method method-<?php echo $payment['payment_method']; ?>">
                                                <?php echo strtoupper(str_replace('_', ' ', $payment['payment_method'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="date-time">
                                                <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>
                                                <small><?php echo date('h:i A', strtotime($payment['payment_date'])); ?></small>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $payment['payment_status']; ?>">
                                                <?php echo ucfirst($payment['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-view" onclick="viewPayment(<?php echo $payment['payment_id']; ?>)" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn-action btn-edit" onclick="openUpdateModal(<?php echo $payment['payment_id']; ?>, '<?php echo $payment['payment_status']; ?>')" title="Update Status">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this payment record?');">
                                                    <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                                    <button type="submit" name="delete_payment" class="btn-action btn-delete" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-data">
                                        <i class="bi bi-inbox"></i>
                                        <p>No payment records found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $method_filter ? '&method=' . $method_filter : ''; ?><?php echo $search ? '&search=' . $search : ''; ?>" class="page-link">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $method_filter ? '&method=' . $method_filter : ''; ?><?php echo $search ? '&search=' . $search : ''; ?>" 
                           class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $method_filter ? '&method=' . $method_filter : ''; ?><?php echo $search ? '&search=' . $search : ''; ?>" class="page-link">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Update Payment Status Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="bi bi-pencil-square"></i> Update Payment Status</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="payment_id" id="modal_payment_id">
                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" id="modal_payment_status" class="form-select" required>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" name="update_payment_status" class="btn-submit">
                    <i class="bi bi-check-circle"></i> Update Status
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Payment Details Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3><i class="bi bi-receipt"></i> Payment Details</h3>
            <button class="modal-close" onclick="closeViewModal()">&times;</button>
        </div>
        <div class="modal-body" id="payment-details-content">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Toggle sidebar
document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
});

// Modal functions
function openUpdateModal(paymentId, currentStatus) {
    document.getElementById('modal_payment_id').value = paymentId;
    document.getElementById('modal_payment_status').value = currentStatus;
    document.getElementById('updateModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('updateModal').style.display = 'none';
}

function viewPayment(paymentId) {
    document.getElementById('viewModal').style.display = 'flex';
    // In a real application, load payment details via AJAX
    document.getElementById('payment-details-content').innerHTML = '<p style="text-align:center; padding:2rem;">Loading payment details...</p>';
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
    const updateModal = document.getElementById('updateModal');
    const viewModal = document.getElementById('viewModal');
    if (event.target === updateModal) {
        closeModal();
    }
    if (event.target === viewModal) {
        closeViewModal();
    }
}

// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const monthlyData = <?php 
    $months = [];
    $revenues = [];
    mysqli_data_seek($monthly_result, 0);
    while ($row = mysqli_fetch_assoc($monthly_result)) {
        $months[] = date('M Y', strtotime($row['month'] . '-01'));
        $revenues[] = floatval($row['revenue']);
    }
    echo json_encode(['months' => array_reverse($months), 'revenues' => array_reverse($revenues)]);
?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthlyData.months,
        datasets: [{
            label: 'Revenue ($)',
            data: monthlyData.revenues,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 6,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(0);
                    }
                }
            }
        }
    }
});
</script>

<?php

mysqli_close($conn);
include '../includes/admin_footer.php';
?>