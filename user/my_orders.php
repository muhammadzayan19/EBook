<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$page_title = "My Orders";

$stmt = $conn->prepare("
    SELECT 
        o.order_id,
        o.quantity,
        o.order_type,
        o.total_amount,
        o.status,
        o.order_date,
        b.title,
        b.author,
        b.category,
        b.image_path,
        b.file_path,
        b.is_free,
        p.payment_method,
        p.payment_status,
        p.payment_date
    FROM orders o
    INNER JOIN books b ON o.book_id = b.book_id
    LEFT JOIN payments p ON o.order_id = p.order_id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_orders = count($orders);
$total_spent = 0;
$pending_orders = 0;
$completed_orders = 0;

foreach ($orders as $order) {
    $total_spent += $order['total_amount'];
    if ($order['status'] === 'pending') {
        $pending_orders++;
    } else {
        $completed_orders++;
    }
}

include '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="display-5 fw-bold mb-3">My Orders</h1>
                <p class="lead mb-4">Track and manage all your book orders</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="profile.php">Profile</a></li>
                        <li class="breadcrumb-item active">My Orders</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="my-orders-section py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="orders-stat-card">
                    <div class="stat-icon-wrapper">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $total_orders; ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="orders-stat-card">
                    <div class="stat-icon-wrapper stat-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $pending_orders; ?></div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="orders-stat-card">
                    <div class="stat-icon-wrapper stat-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $completed_orders; ?></div>
                        <div class="stat-label">Completed Orders</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="orders-filters-bar">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="search-box-orders">
                        <input type="text" id="searchOrders" class="form-control" placeholder="Search by book title or order ID...">
                        <i class="bi bi-search search-icon"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">
                            <i class="bi bi-grid"></i> All
                        </button>
                        <button class="filter-btn" data-filter="pending">
                            <i class="bi bi-clock"></i> Pending
                        </button>
                        <button class="filter-btn" data-filter="paid">
                            <i class="bi bi-check-circle"></i> Completed
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($orders)): ?>
        <div class="no-orders-section">
            <div class="no-orders-icon">
                <i class="bi bi-cart-x"></i>
            </div>
            <h3 class="no-orders-title">No Orders Yet</h3>
            <p class="no-orders-text">You haven't placed any orders yet. Start exploring our collection!</p>
            <a href="books.php" class="btn btn-primary btn-lg">
                <i class="bi bi-book me-2"></i>Browse Books
            </a>
        </div>
        <?php else: ?>
        <div class="orders-list" id="ordersList">
            <?php foreach ($orders as $order): ?>
            <div class="order-item" data-status="<?php echo strtolower($order['status']); ?>" data-order-id="<?php echo $order['order_id']; ?>" data-title="<?php echo strtolower($order['title']); ?>">
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-id-section">
                            <span class="order-id-label">Order ID:</span>
                            <span class="order-id-value">#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="order-status-badge status-<?php echo $order['status']; ?>">
                            <?php if ($order['status'] === 'pending'): ?>
                                <i class="bi bi-clock-history"></i> Pending
                            <?php else: ?>
                                <i class="bi bi-check-circle"></i> Paid
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-card-body">
                        <div class="row">
                            <div class="col-md-2 col-sm-3 mb-3 mb-md-0">
                                <div class="order-book-image">
                                    <img src="../assets/images/books/default.jpg" 
                                         alt="<?php echo htmlspecialchars($order['title']); ?>" 
                                         class="img-fluid">
                                    <?php if ($order['is_free']): ?>
                                        <span class="book-free-badge">FREE</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-7 col-sm-9">
                                <div class="order-book-details">
                                    <span class="order-book-category"><?php echo htmlspecialchars($order['category']); ?></span>
                                    <h5 class="order-book-title"><?php echo htmlspecialchars($order['title']); ?></h5>
                                    <p class="order-book-author">
                                        <i class="bi bi-person"></i>
                                        <?php echo htmlspecialchars($order['author']); ?>
                                    </p>
                                    
                                    <div class="order-info-grid">
                                        <div class="order-info-item">
                                            <i class="bi bi-file-earmark"></i>
                                            <span>Type: <strong><?php echo ucfirst($order['order_type']); ?></strong></span>
                                        </div>
                                        <div class="order-info-item">
                                            <i class="bi bi-box"></i>
                                            <span>Quantity: <strong><?php echo $order['quantity']; ?></strong></span>
                                        </div>
                                        <div class="order-info-item">
                                            <i class="bi bi-calendar"></i>
                                            <span><?php echo date('M j, Y', strtotime($order['order_date'])); ?></span>
                                        </div>
                                        <?php if ($order['payment_method']): ?>
                                        <div class="order-info-item">
                                            <i class="bi bi-credit-card"></i>
                                            <span><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 col-12">
                                <div class="order-actions-section">
                                    <div class="order-total-amount">
                                        <span class="amount-label">Total Amount</span>
                                        <span class="amount-value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                    
                                    <div class="order-action-buttons">
                                        <?php if ($order['status'] === 'paid' && $order['order_type'] === 'pdf' && !empty($order['file_path'])): ?>
                                            <a href="../<?php echo htmlspecialchars($order['file_path']); ?>" 
                                               class="btn btn-success btn-sm w-100 mb-2" 
                                               download>
                                                <i class="bi bi-download me-2"></i>Download PDF
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-outline-primary btn-sm w-100 view-details-btn" 
                                                data-order-id="<?php echo $order['order_id']; ?>">
                                            <i class="bi bi-eye me-2"></i>View Details
                                        </button>
                                        
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <button class="btn btn-outline-danger btn-sm w-100 mt-2" 
                                                    onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                                <i class="bi bi-x-circle me-2"></i>Cancel Order
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($order['status'] === 'paid'): ?>
                    <div class="order-card-footer">
                        <div class="delivery-status">
                            <i class="bi bi-truck"></i>
                            <?php if ($order['order_type'] === 'pdf'): ?>
                                <span>Digital delivery completed</span>
                            <?php else: ?>
                                <span>Physical delivery in progress</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($order['payment_date']): ?>
                        <div class="payment-date">
                            <i class="bi bi-calendar-check"></i>
                            <span>Paid on <?php echo date('M j, Y', strtotime($order['payment_date'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-receipt me-2"></i>Order Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('searchOrders').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const orders = document.querySelectorAll('.order-item');
    
    orders.forEach(order => {
        const title = order.dataset.title;
        const orderId = order.dataset.orderId;
        
        if (title.includes(searchTerm) || orderId.includes(searchTerm)) {
            order.style.display = '';
        } else {
            order.style.display = 'none';
        }
    });
});

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const orders = document.querySelectorAll('.order-item');
        
        orders.forEach(order => {
            if (filter === 'all') {
                order.style.display = '';
            } else {
                if (order.dataset.status === filter) {
                    order.style.display = '';
                } else {
                    order.style.display = 'none';
                }
            }
        });
    });
});

document.querySelectorAll('.view-details-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const orderId = this.dataset.orderId;
        document.getElementById('orderDetailsContent').innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-receipt" style="font-size: 3rem; color: var(--primary-color);"></i>
                <h5 class="mt-3">Order #${orderId.padStart(6, '0')}</h5>
                <p class="text-muted">Detailed order information will be displayed here.</p>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        modal.show();
    });
});

function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        alert('Order cancellation functionality will be implemented here.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const orders = document.querySelectorAll('.order-item');
    orders.forEach((order, index) => {
        setTimeout(() => {
            order.style.opacity = '0';
            order.style.transform = 'translateY(20px)';
            order.style.transition = 'all 0.4s ease-out';
            
            setTimeout(() => {
                order.style.opacity = '1';
                order.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
});
</script>

<?php
include '../includes/footer.php';
?>