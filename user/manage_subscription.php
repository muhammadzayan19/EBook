<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';
require_once '../includes/subscription_helper.php';

$user_id = $_SESSION['user_id'];
$page_title = "Manage Subscription";

$error_message = '';
$success_message = '';

// Handle subscription cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_subscription'])) {
    $subscription_id = intval($_POST['subscription_id']);
    
    $verify = $conn->prepare("SELECT subscription_id FROM subscriptions WHERE subscription_id = ? AND user_id = ?");
    $verify->bind_param("ii", $subscription_id, $user_id);
    $verify->execute();
    
    if ($verify->get_result()->num_rows > 0) {
        $cancel = $conn->prepare("UPDATE subscriptions SET status = 'cancelled', auto_renew = 0 WHERE subscription_id = ?");
        $cancel->bind_param("i", $subscription_id);
        
        if ($cancel->execute()) {
            $success_message = 'Subscription cancelled successfully. You can continue using it until the end date.';
        } else {
            $error_message = 'Failed to cancel subscription.';
        }
        $cancel->close();
    }
    $verify->close();
}

// Handle auto-renew toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_autorenew'])) {
    $subscription_id = intval($_POST['subscription_id']);
    $auto_renew = intval($_POST['auto_renew']);
    
    $verify = $conn->prepare("SELECT subscription_id FROM subscriptions WHERE subscription_id = ? AND user_id = ?");
    $verify->bind_param("ii", $subscription_id, $user_id);
    $verify->execute();
    
    if ($verify->get_result()->num_rows > 0) {
        $update = $conn->prepare("UPDATE subscriptions SET auto_renew = ? WHERE subscription_id = ?");
        $update->bind_param("ii", $auto_renew, $subscription_id);
        
        if ($update->execute()) {
            $success_message = $auto_renew ? 'Auto-renew enabled successfully.' : 'Auto-renew disabled successfully.';
        } else {
            $error_message = 'Failed to update auto-renew settings.';
        }
        $update->close();
    }
    $verify->close();
}

// Get current subscription
$current_subscription = hasActiveSubscription($conn, $user_id);

// Get subscription history
$history_stmt = $conn->prepare("
    SELECT * FROM subscriptions 
    WHERE user_id = ? 
    ORDER BY start_date DESC
");
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$subscription_history = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$history_stmt->close();

// Get subscription access count
$access_count = 0;
if ($current_subscription) {
    $access_stmt = $conn->prepare("SELECT COUNT(*) as count FROM subscription_access WHERE subscription_id = ?");
    $access_stmt->bind_param("i", $current_subscription['subscription_id']);
    $access_stmt->execute();
    $access_count = $access_stmt->get_result()->fetch_assoc()['count'];
    $access_stmt->close();
    
    // Get accessed books
    $books_stmt = $conn->prepare("
        SELECT b.book_id, b.title, b.author, b.category, sa.granted_at
        FROM subscription_access sa
        JOIN books b ON sa.book_id = b.book_id
        WHERE sa.subscription_id = ?
        ORDER BY sa.granted_at DESC
        LIMIT 10
    ");
    $books_stmt->bind_param("i", $current_subscription['subscription_id']);
    $books_stmt->execute();
    $accessed_books = $books_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $books_stmt->close();
}

// Get pricing
$pricing = getSubscriptionPricing($conn);

include '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="display-5 fw-bold mb-3">Manage Subscription</h1>
                <p class="lead mb-4">View and manage your subscription details</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="profile.php">Profile</a></li>
                        <li class="breadcrumb-item active">Manage Subscription</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="manage-subscription-section py-5">
    <div class="container">
        <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($current_subscription): ?>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="subscription-card active">
                    <div class="subscription-header">
                        <div class="subscription-badge">
                            <i class="bi bi-star-fill"></i>
                            <span>Active Subscription</span>
                        </div>
                        <div class="subscription-status">
                            <span class="status-badge status-<?php echo $current_subscription['status']; ?>">
                                <?php echo ucfirst($current_subscription['status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="subscription-body">
                        <h3 class="subscription-plan">
                            <?php echo ucfirst($current_subscription['plan_type']); ?> Plan
                        </h3>
                        <div class="subscription-price">
                            $<?php echo number_format($current_subscription['amount'], 2); ?>
                            <span class="price-period">/<?php echo $current_subscription['plan_type'] === 'yearly' ? 'year' : 'month'; ?></span>
                        </div>

                        <div class="subscription-details">
                            <div class="detail-item">
                                <i class="bi bi-calendar-check"></i>
                                <div>
                                    <strong>Start Date</strong>
                                    <span><?php echo date('F j, Y', strtotime($current_subscription['start_date'])); ?></span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-calendar-x"></i>
                                <div>
                                    <strong>Renewal Date</strong>
                                    <span><?php echo date('F j, Y', strtotime($current_subscription['end_date'])); ?></span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-credit-card"></i>
                                <div>
                                    <strong>Payment Method</strong>
                                    <span><?php echo ucwords(str_replace('_', ' ', $current_subscription['payment_method'])); ?></span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-arrow-repeat"></i>
                                <div>
                                    <strong>Auto-Renew</strong>
                                    <span>
                                        <?php if ($current_subscription['auto_renew']): ?>
                                            <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Enabled</span>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="bi bi-x-circle-fill me-1"></i>Disabled</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="subscription-progress">
                            <div class="progress-info">
                                <span>Subscription Period</span>
                                <span>
                                    <?php
                                    $start = strtotime($current_subscription['start_date']);
                                    $end = strtotime($current_subscription['end_date']);
                                    $now = time();
                                    $total = $end - $start;
                                    $elapsed = $now - $start;
                                    $percentage = min(100, max(0, ($elapsed / $total) * 100));
                                    $days_left = max(0, ceil(($end - $now) / 86400));
                                    echo $days_left . ' days remaining';
                                    ?>
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>

                        <div class="subscription-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="subscription_id" value="<?php echo $current_subscription['subscription_id']; ?>">
                                <input type="hidden" name="auto_renew" value="<?php echo $current_subscription['auto_renew'] ? 0 : 1; ?>">
                                <button type="submit" name="toggle_autorenew" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-repeat me-2"></i>
                                    <?php echo $current_subscription['auto_renew'] ? 'Disable' : 'Enable'; ?> Auto-Renew
                                </button>
                            </form>
                            
                            <?php if ($current_subscription['status'] === 'active'): ?>
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-x-circle me-2"></i>Cancel Subscription
                            </button>
                            <?php endif; ?>
                            
                            <a href="books.php?subscription=1" class="btn btn-success">
                                <i class="bi bi-book me-2"></i>Browse Books
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (!empty($accessed_books)): ?>
                <div class="subscription-card mt-4">
                    <div class="card-header">
                        <h5><i class="bi bi-book-half me-2"></i>Your Subscription Books</h5>
                        <p class="text-muted mb-0">Books you've accessed with this subscription</p>
                    </div>
                    <div class="books-list">
                        <?php foreach ($accessed_books as $book): ?>
                        <div class="book-item">
                            <div class="book-info">
                                <i class="bi bi-book-fill text-primary"></i>
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($book['title']); ?></h6>
                                    <small class="text-muted">
                                        by <?php echo htmlspecialchars($book['author']); ?> • 
                                        <?php echo htmlspecialchars($book['category']); ?>
                                    </small>
                                </div>
                            </div>
                            <a href="book_details.php?id=<?php echo $book['book_id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($access_count > 10): ?>
                    <div class="text-center mt-3">
                        <a href="books.php?subscription=1" class="btn btn-outline-primary">
                            View All <?php echo $access_count; ?> Books
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="subscription-card">
                    <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Subscription Benefits</h5>
                    <ul class="benefits-list">
                        <li>
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>Unlimited access to <?php echo $access_count; ?>+ books</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>Download PDFs anytime</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>New releases included</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>Ad-free reading experience</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>Cancel anytime</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>Priority customer support</span>
                        </li>
                    </ul>
                </div>

                <div class="subscription-card mt-4">
                    <h5 class="mb-3"><i class="bi bi-question-circle me-2"></i>Need Help?</h5>
                    <p class="text-muted small">Have questions about your subscription?</p>
                    <div class="d-grid gap-2">
                        <a href="../contact.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-envelope me-2"></i>Contact Support
                        </a>
                        <a href="#faq" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-question-circle me-2"></i>View FAQ
                        </a>
                    </div>
                </div>

                <div class="subscription-card mt-4 bg-light">
                    <h6 class="mb-2"><i class="bi bi-shield-check me-2"></i>Billing Information</h6>
                    <p class="small text-muted mb-2">
                        Your subscription will <?php echo $current_subscription['auto_renew'] ? 'automatically renew' : 'expire'; ?> on 
                        <strong><?php echo date('F j, Y', strtotime($current_subscription['end_date'])); ?></strong>
                    </p>
                    <?php if ($current_subscription['auto_renew']): ?>
                    <p class="small text-muted mb-0">
                        Next payment: <strong>$<?php echo number_format($current_subscription['amount'], 2); ?></strong>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Cancel Subscription Modal -->
        <div class="modal fade" id="cancelModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-exclamation-triangle me-2"></i>Cancel Subscription
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <p class="mb-3">Are you sure you want to cancel your subscription?</p>
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Please note:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>You'll continue to have access until <?php echo date('F j, Y', strtotime($current_subscription['end_date'])); ?></li>
                                    <li>You won't be charged again</li>
                                    <li>You can resubscribe anytime</li>
                                    <li>Downloaded books will remain accessible</li>
                                </ul>
                            </div>
                            <input type="hidden" name="subscription_id" value="<?php echo $current_subscription['subscription_id']; ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Subscription</button>
                            <button type="submit" name="cancel_subscription" class="btn btn-danger">
                                <i class="bi bi-x-circle me-2"></i>Yes, Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- No Active Subscription -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="no-subscription-card">
                    <div class="text-center">
                        <i class="bi bi-star" style="font-size: 4rem; color: #d1d5db;"></i>
                        <h3 class="mt-4 mb-3">No Active Subscription</h3>
                        <p class="text-muted mb-4">You don't have an active subscription. Subscribe now to get unlimited access to our premium collection!</p>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <div class="plan-card">
                                    <h5>Monthly Plan</h5>
                                    <div class="plan-price">$<?php echo number_format($pricing['monthly'], 2); ?></div>
                                    <p class="text-muted">per month</p>
                                    <a href="subscription.php" class="btn btn-primary w-100">Subscribe</a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="plan-card featured">
                                    <div class="badge bg-success mb-2">Best Value</div>
                                    <h5>Yearly Plan</h5>
                                    <div class="plan-price">$<?php echo number_format($pricing['yearly'], 2); ?></div>
                                    <p class="text-muted">per year</p>
                                    <a href="subscription.php" class="btn btn-success w-100">Subscribe</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($subscription_history)): ?>
        <div class="subscription-card mt-5">
            <h4 class="mb-4"><i class="bi bi-clock-history me-2"></i>Subscription History</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscription_history as $sub): ?>
                        <tr>
                            <td><?php echo ucfirst($sub['plan_type']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($sub['start_date'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($sub['end_date'])); ?></td>
                            <td>$<?php echo number_format($sub['amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $sub['status'] === 'active' ? 'success' : 
                                        ($sub['status'] === 'cancelled' ? 'warning' : 'secondary'); 
                                ?>">
                                    <?php echo ucfirst($sub['status']); ?>
                                </span>
                            </td>
                            <td><?php echo ucwords(str_replace('_', ' ', $sub['payment_method'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.subscription-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    color: black;
}

.subscription-card.active {
    border: 2px solid #10b981;
}

.subscription-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.subscription-badge {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-badge.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.status-cancelled {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.status-expired {
    background: #e5e7eb;
    color: #4b5563;
}

.subscription-plan {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.subscription-price {
    font-size: 2.5rem;
    font-weight: 700;
    color: #10b981;
    margin-bottom: 2rem;
}

.price-period {
    font-size: 1.25rem;
    color: #6b7280;
}

.subscription-details {
    display: grid;
    gap: 1rem;
    margin-bottom: 2rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
}

.detail-item i {
    font-size: 1.5rem;
    color: #10b981;
}

.detail-item strong {
    display: block;
    font-size: 0.875rem;
    color: #6b7280;
}

.subscription-progress {
    margin-bottom: 2rem;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.subscription-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.books-list {
    max-height: 400px;
    overflow-y: auto;
}

.book-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.book-item:last-child {
    border-bottom: none;
}

.book-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.book-info i {
    font-size: 1.5rem;
}

.benefits-list {
    list-style: none;
    padding: 0;
}

.benefits-list li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.benefits-list li:last-child {
    border-bottom: none;
}

.no-subscription-card {
    background: white;
    border-radius: 12px;
    padding: 3rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.plan-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
}

.plan-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.plan-card.featured {
    border-color: #10b981;
    background: linear-gradient(135deg, #f0fdf4, #d1fae5);
}

.plan-price {
    font-size: 2.5rem;
    font-weight: 700;
    color: #10b981;
    margin: 1rem 0;
}
</style>

<?php include '../includes/footer.php'; ?>