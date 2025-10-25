<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';

$subscription_id = isset($_GET['subscription_id']) ? intval($_GET['subscription_id']) : 0;
$method = isset($_GET['method']) ? $_GET['method'] : 'credit_card';

if ($subscription_id === 0) {
    header('Location: subscription.php');
    exit();
}

$stmt = $conn->prepare("SELECT s.*, u.full_name, u.email 
                        FROM user_subscriptions s 
                        JOIN users u ON s.user_id = u.user_id 
                        WHERE s.subscription_id = ? AND s.user_id = ?");
$stmt->bind_param("ii", $subscription_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$subscription = $result->fetch_assoc();
$stmt->close();

if (!$subscription) {
    header('Location: subscription.php');
    exit();
}

$page_title = "Processing Subscription";
include '../includes/header.php';
?>

<section class="payment-processing">
    <div class="container">
        <div class="payment-card">
            <div class="payment-icon" id="paymentIcon">
                <i class="bi bi-star-fill"></i>
            </div>
            
            <h2 class="payment-title" id="paymentTitle">Processing Subscription...</h2>
            <p class="payment-message" id="paymentMessage">Please wait while we activate your premium membership</p>
            
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
                         ($method === 'paypal' ? 'paypal' : 'bank'); 
                ?>"></i>
                <span><?php echo ucwords(str_replace('_', ' ', $method)); ?></span>
            </div>
            
            <div class="subscription-summary-box">
                <div class="summary-row">
                    <span>Subscription:</span>
                    <strong>Monthly Premium</strong>
                </div>
                <div class="summary-row">
                    <span>Duration:</span>
                    <strong>1 Month</strong>
                </div>
                <div class="summary-row">
                    <span>Valid Until:</span>
                    <strong><?php echo date('M j, Y', strtotime($subscription['end_date'])); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Total Amount:</span>
                    <strong>$<?php echo number_format($subscription['amount'], 2); ?></strong>
                </div>
            </div>
            
            <div id="returnButtons" style="display: none;">
                <a href="books.php?subscription=1" class="btn-return">
                    <i class="bi bi-book me-2"></i>Browse Subscription Books
                </a>
                <a href="profile.php" class="btn-return ms-2">
                    <i class="bi bi-person me-2"></i>My Profile
                </a>
            </div>
        </div>
    </div>
</section>

<script>
setTimeout(function() {
    document.getElementById('paymentTitle').textContent = 'Subscription Activated!';
    document.getElementById('paymentMessage').textContent = 'Welcome to Premium! You now have unlimited access to all subscription books';
    document.getElementById('spinner').style.display = 'none';
    document.getElementById('successCheck').classList.add('show');
    document.getElementById('paymentIcon').style.display = 'none';
    document.getElementById('returnButtons').style.display = 'block';
    
    if (typeof confetti !== 'undefined') {
        confetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    }
}, 3000);
</script>

<?php include '../includes/footer.php'; ?>