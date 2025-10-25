<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$method = isset($_GET['method']) ? $_GET['method'] : 'credit_card';

if ($order_id === 0) {
    header('Location: books.php');
    exit();
}

$stmt = $conn->prepare("SELECT o.*, b.title, b.author, u.full_name, u.email 
                        FROM orders o 
                        JOIN books b ON o.book_id = b.book_id 
                        JOIN users u ON o.user_id = u.user_id 
                        WHERE o.order_id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: books.php');
    exit();
}

$page_title = "Payment Processing";
include '../includes/header.php';
?>

<section class="payment-processing">
    <div class="container">
        <div class="payment-card">
            <div class="payment-icon" id="paymentIcon">
                <i class="bi bi-credit-card"></i>
            </div>
            
            <h2 class="payment-title" id="paymentTitle">Processing Payment...</h2>
            <p class="payment-message" id="paymentMessage">Please wait while we securely process your payment</p>
            
            <div class="payment-spinner" id="spinner"></div>
            
            <div class="success-checkmark" id="successCheck">
                <svg viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="45" fill="#10b981" />
                    <path d="M30 50 L45 65 L70 35" stroke="white" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <div class="payment-method-badge">
                <i class="bi bi-<?php 
                    echo $method === 'credit_card' ? 'credit-card' : 
                         ($method === 'paypal' ? 'paypal' : 
                         ($method === 'bank_transfer' ? 'bank' : 'cash')); 
                ?>"></i>
                <span><?php echo ucwords(str_replace('_', ' ', $method)); ?></span>
            </div>
            
            <div class="order-summary-box">
                <div class="summary-row">
                    <span>Order ID:</span>
                    <strong>#<?php echo $order['order_id']; ?></strong>
                </div>
                <div class="summary-row">
                    <span>Book:</span>
                    <strong><?php echo htmlspecialchars($order['title']); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Quantity:</span>
                    <strong><?php echo $order['quantity']; ?></strong>
                </div>
                <div class="summary-row">
                    <span>Total Amount:</span>
                    <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                </div>
            </div>
            
            <div id="returnButtons" style="display: none;">
                <a href="my_orders.php" class="btn-return">
                    <i class="bi bi-receipt me-2"></i>View My Orders
                </a>
                <a href="books.php" class="btn-return ms-2">
                    <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
</section>

<script>
setTimeout(function() {
    document.getElementById('paymentTitle').textContent = 'Payment Successful!';
    document.getElementById('paymentMessage').textContent = 'Your order has been confirmed and will be processed shortly';
    document.getElementById('spinner').style.display = 'none';
    document.getElementById('successCheck').classList.add('show');
    document.getElementById('paymentIcon').style.display = 'none';
    document.getElementById('returnButtons').style.display = 'block';
    
    fetch('update_payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=<?php echo $order_id; ?>&status=paid'
    });
}, 3000)
</script>

<?php include '../includes/footer.php'; ?>