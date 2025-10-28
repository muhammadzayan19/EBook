<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'subscribe.php';
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';
require_once '../includes/subscription_helper.php';

$user_id = $_SESSION['user_id'];
$page_title = "Subscribe";

$user_check = $conn->query("SELECT user_id FROM users WHERE user_id = $user_id");
if (!$user_check || $user_check->num_rows === 0) {
    session_destroy();
    header('Location: login.php');
    exit();
}

$active_subscription = hasActiveSubscription($conn, $user_id);

$pricing = getSubscriptionPricing($conn);

$count_query = "SELECT COUNT(*) as count FROM books WHERE is_subscription_allowed = 1";
$count_result = $conn->query($count_query);
$subscription_books_count = $count_result->fetch_assoc()['count'];

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_type = $_POST['plan_type'] ?? 'monthly';
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($payment_method)) {
        $error_message = 'Please select a payment method.';
    } elseif ($active_subscription) {
        $error_message = 'You already have an active subscription.';
    } else {
        $user_verify = $conn->query("SELECT user_id FROM users WHERE user_id = $user_id LIMIT 1");
        
        if (!$user_verify || $user_verify->num_rows === 0) {
            $error_message = 'User verification failed. Please log in again.';
        } else {
            $amount = ($plan_type === 'yearly') ? $pricing['yearly'] : $pricing['monthly'];
            $duration = ($plan_type === 'yearly') ? '+1 year' : '+1 month';
            $end_date = date('Y-m-d H:i:s', strtotime($duration));
            
            $conn->begin_transaction();
            
            try {
                $plan_type_escaped = $conn->real_escape_string($plan_type);
                $end_date_escaped = $conn->real_escape_string($end_date);
                $payment_method_escaped = $conn->real_escape_string($payment_method);
                
                $sub_query = "
                    INSERT INTO subscriptions (user_id, plan_type, end_date, amount, payment_method, status) 
                    VALUES ($user_id, '$plan_type_escaped', '$end_date_escaped', $amount, '$payment_method_escaped', 'active')
                ";
                
                if (!$conn->query($sub_query)) {
                    throw new Exception('Failed to create subscription: ' . $conn->error);
                }
                
                $subscription_id = $conn->insert_id;
                
                $books_query = "SELECT book_id FROM books WHERE is_subscription_allowed = 1";
                $books_result = $conn->query($books_query);
                
                if ($books_result && $books_result->num_rows > 0) {
                    $access_insert = "INSERT INTO subscription_access (subscription_id, book_id) VALUES ";
                    $values = [];
                    
                    while ($book = $books_result->fetch_assoc()) {
                        $values[] = "($subscription_id, {$book['book_id']})";
                    }
                    
                    if (!empty($values)) {
                        $access_insert .= implode(", ", $values);
                        $conn->query($access_insert);
                    }
                }
                
                $conn->commit();
                
                header("Location: subscription_process.php?subscription_id=$subscription_id&method=$payment_method_escaped");
                exit();
                
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = 'Subscription failed: ' . $e->getMessage();
            }
        }
    }
}

include '../includes/header.php';
?>

<section class="subscription-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <div class="hero-badge">
                    <i class="bi bi-star-fill"></i>
                    <span>Premium Membership</span>
                </div>
                <h1 class="display-3 fw-bold mb-4">Unlimited Access to <?php echo $subscription_books_count; ?>+ Books</h1>
                <p class="lead mb-0">Subscribe now and get instant access to our entire premium collection</p>
            </div>
        </div>
    </div>
</section>

<section class="subscription-section">
    <div class="container">
        <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($active_subscription): ?>
        <div class="active-subscription-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-3"><i class="bi bi-star-fill me-2"></i>Your Premium Subscription is Active!</h3>
                    <p class="mb-2">Plan: <strong><?php echo ucfirst($active_subscription['plan_type']); ?></strong></p>
                    <p class="mb-0">Valid until <strong><?php echo date('F j, Y', strtotime($active_subscription['end_date'])); ?></strong></p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="books.php?subscription=1" class="btn btn-light btn-lg">
                        <i class="bi bi-book me-2"></i>Browse Books
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>

        <div class="benefits-grid">
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="bi bi-infinity"></i>
                </div>
                <h4>Unlimited Access</h4>
                <p>Read as many books as you want from our premium collection</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="bi bi-download"></i>
                </div>
                <h4>Instant Downloads</h4>
                <p>Download PDFs instantly and read offline anytime</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="bi bi-arrow-clockwise"></i>
                </div>
                <h4>Cancel Anytime</h4>
                <p>No contracts, cancel your subscription whenever you want</p>
            </div>
            
            <div class="benefit-card">
                <div class="benefit-icon">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <h4>New Releases</h4>
                <p>Get access to new books as soon as they're released</p>
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-lg-5 mb-4">
                <div class="pricing-card">
                    <div class="pricing-header">
                        <h2 class="pricing-title">Monthly Plan</h2>
                        <div class="pricing-price">$<?php echo number_format($pricing['monthly'], 2); ?></div>
                        <p class="pricing-period">per month</p>
                    </div>
                    
                    <ul class="pricing-features">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Access to <?php echo $subscription_books_count; ?>+ premium books</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Unlimited downloads</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>New books added regularly</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Cancel anytime</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Ad-free experience</span>
                        </li>
                    </ul>
                    
                    <button type="button" class="btn btn-primary btn-lg w-100" 
                            data-bs-toggle="modal" data-bs-target="#subscribeModal"
                            data-plan="monthly">
                        <i class="bi bi-star me-2"></i>Subscribe Monthly
                    </button>
                </div>
            </div>
            
            <div class="col-lg-5 mb-4">
                <div class="pricing-card featured">
                    <div class="pricing-header">
                        <h2 class="pricing-title">Yearly Plan</h2>
                        <div class="pricing-price">$<?php echo number_format($pricing['yearly'], 2); ?></div>
                        <p class="pricing-period">per year</p>
                        <span class="pricing-save">
                            Save $<?php echo number_format(($pricing['monthly'] * 12) - $pricing['yearly'], 2); ?>
                        </span>
                    </div>
                    
                    <ul class="pricing-features">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>All Monthly features</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>2 months FREE</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Early access to new books</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Exclusive content</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Priority support</span>
                        </li>
                    </ul>
                    
                    <button type="button" class="btn btn-success btn-lg w-100" 
                            data-bs-toggle="modal" data-bs-target="#subscribeModal"
                            data-plan="yearly">
                        <i class="bi bi-star me-2"></i>Subscribe Yearly
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="faq-section">
    <div class="container">
        <h2 class="text-center mb-5">Frequently Asked Questions</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="faq-item">
                    <div class="faq-question">How does the subscription work?</div>
                    <div class="faq-answer">Pay a monthly or yearly fee and get unlimited access to all books marked as subscription-eligible. You can read and download as many as you want during your subscription period.</div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">Can I cancel anytime?</div>
                    <div class="faq-answer">Yes! There's no long-term commitment. You can cancel your subscription at any time from your profile settings. Your access continues until the end of your current billing period.</div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">What happens when I cancel?</div>
                    <div class="faq-answer">You'll continue to have access until the end of your current billing period. After that, you'll need to purchase books individually or renew your subscription.</div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">Are all books included in subscription?</div>
                    <div class="faq-answer">Not all books are included. Currently, <?php echo $subscription_books_count; ?> books are available with the subscription. Books with subscription access are marked with a star badge.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="subscribeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #2563eb; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-credit-card me-2"></i>Complete Your Subscription
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="subscribeForm">
                <input type="hidden" name="plan_type" id="planType" value="monthly">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 id="modalPrice">$<?php echo number_format($pricing['monthly'], 2); ?></h3>
                        <p class="text-muted" id="modalPeriod">Billed monthly, cancel anytime</p>
                    </div>
                    
                    <h6 class="mb-3">Select Payment Method</h6>
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="credit_card" checked required>
                            <div class="payment-content">
                                <i class="bi bi-credit-card"></i>
                                <span>Credit Card</span>
                            </div>
                        </label>
                        
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="paypal">
                            <div class="payment-content">
                                <i class="bi bi-paypal"></i>
                                <span>PayPal</span>
                            </div>
                        </label>
                        
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="bank_transfer">
                            <div class="payment-content">
                                <i class="bi bi-bank"></i>
                                <span>Bank Transfer</span>
                            </div>
                        </label>
                        
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="debit_card">
                            <div class="payment-content">
                                <i class="bi bi-credit-card-2-front"></i>
                                <span>Debit Card</span>
                            </div>
                        </label>
                    </div>
                    
                    <div class="alert alert-info mt-3" style="border-left: 4px solid #2563eb;">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Your subscription will auto-renew. You can cancel anytime from your profile.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Confirm & Subscribe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const subscribeModal = document.getElementById('subscribeModal');
const monthlyPrice = <?php echo $pricing['monthly']; ?>;
const yearlyPrice = <?php echo $pricing['yearly']; ?>;

subscribeModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const plan = button.getAttribute('data-plan');
    
    const planType = document.getElementById('planType');
    const modalPrice = document.getElementById('modalPrice');
    const modalPeriod = document.getElementById('modalPeriod');
    
    planType.value = plan;
    
    if (plan === 'yearly') {
        modalPrice.textContent = '$' + yearlyPrice.toFixed(2);
        modalPeriod.textContent = 'Billed yearly, cancel anytime';
    } else {
        modalPrice.textContent = '$' + monthlyPrice.toFixed(2);
        modalPeriod.textContent = 'Billed monthly, cancel anytime';
    }
});
</script>

<?php include '../includes/footer.php'; ?>