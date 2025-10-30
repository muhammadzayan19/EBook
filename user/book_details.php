<?php
session_start();

require_once '../config/db.php';
require_once '../includes/subscription_helper.php';

$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($book_id === 0) {
    header('Location: books.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();

if (!$book) {
    header('Location: books.php');
    exit();
}

$page_title = $book['title'];

function getBookImage($book) {
    $paths = [
        '../' . $book['image_path'],
        $book['image_path'],
        '../uploads/book_covers/' . basename($book['image_path'])
    ];
    
    foreach ($paths as $path) {
        if (!empty($book['image_path']) && file_exists($path)) {
            return $path;
        }
    }
    
    return '../assets/images/books/default.jpg';
}

include '../includes/header.php';

$has_purchased = false;
$can_download = false;
$has_subscription = false;
$access_method = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $subscription = hasActiveSubscription($conn, $user_id);
    $has_subscription = ($subscription !== false);
    
    $purchase = hasPurchasedBook($conn, $user_id, $book_id);
    if ($purchase) {
        $has_purchased = true;
        $can_download = ($purchase['status'] === 'paid' || $purchase['payment_status'] === 'completed');
        $access_method = 'purchase';
    }
    
    if ($has_subscription && $book['is_subscription_allowed']) {
        $can_download = true;
        if (!$has_purchased) {
            $access_method = 'subscription';
        }
    }
}

$related_stmt = $conn->prepare("SELECT * FROM books WHERE category = ? AND book_id != ? LIMIT 3");
$related_stmt->bind_param("si", $book['category'], $book_id);
$related_stmt->execute();
$related_books = $related_stmt->get_result();
$related_stmt->close();

if (isset($_SESSION['payment_success'])) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-success alert-dismissible fade show">';
    echo '<i class="bi bi-check-circle me-2"></i>' . $_SESSION['payment_success'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    echo '</div>';
    unset($_SESSION['payment_success']);
}

$reviews_stmt = $conn->prepare("
    SELECT br.review_id, br.rating, br.review_text, br.created_at, u.full_name, br.helpful_count
    FROM book_reviews br
    JOIN users u ON br.user_id = u.user_id
    WHERE br.book_id = ? AND br.status = 'approved'
    ORDER BY br.created_at DESC
    LIMIT 10
");
$reviews_stmt->bind_param("i", $book_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
$reviews_stmt->close();

// Get or create review summary
$summary_stmt = $conn->prepare("
    SELECT total_reviews, average_rating, five_star, four_star, three_star, two_star, one_star
    FROM review_ratings_summary
    WHERE book_id = ?
");
$summary_stmt->bind_param("i", $book_id);
$summary_stmt->execute();
$summary_result = $summary_stmt->get_result();
$review_summary = $summary_result->fetch_assoc();
$summary_stmt->close();

$stats_stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_reviews,
        COALESCE(AVG(rating), 0) as average_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
    FROM book_reviews
    WHERE book_id = ? AND status = 'approved'
");
$stats_stmt->bind_param("i", $book_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$review_summary = $stats_result->fetch_assoc();
$stats_stmt->close();

// Ensure all keys exist with default values
$review_summary = array_merge([
    'total_reviews' => 0,
    'average_rating' => 0,
    'five_star' => 0,
    'four_star' => 0,
    'three_star' => 0,
    'two_star' => 0,
    'one_star' => 0
], $review_summary ?? []);

// Check if user has already reviewed - also check status
$user_review = null;
if (isset($_SESSION['user_id'])) {
    $check_stmt = $conn->prepare("
        SELECT review_id, rating, review_text, status
        FROM book_reviews
        WHERE book_id = ? AND user_id = ?
    ");
    $check_stmt->bind_param("ii", $book_id, $_SESSION['user_id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $user_review = $check_result->fetch_assoc();
    }
    $check_stmt->close();
}
?>

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="books.php">Books</a></li>
                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($book['title']); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="book-details-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="book-details-image-wrapper">
                    <div class="book-details-image" style="position: relative; overflow: hidden;">
                        <img src="<?php echo getBookImage($book); ?>" 
                             alt="<?php echo htmlspecialchars($book['title']); ?>" 
                             class="img-fluid"
                             style="width: 100%; height: auto; max-height: 500px; object-fit: cover; border-radius: 8px;"
                             onerror="this.src='../assets/images/books/default.jpg'">
                        
                        <?php if ($book['is_free']): ?>
                            <div class="book-details-badge badge-free">FREE</div>
                        <?php elseif ($has_subscription && $book['is_subscription_allowed']): ?>
                            <div class="book-details-badge" style="background: #28a745;">
                                <i class="bi bi-check-circle me-1"></i>Included in Subscription
                            </div>
                        <?php elseif ($book['stock'] > 0 && $book['stock'] < 10): ?>
                            <div class="book-details-badge badge-limited">Limited Stock</div>
                        <?php elseif ($book['stock'] == 0): ?>
                            <div class="book-details-badge badge-outstock">Out of Stock</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="quick-actions-card">
                        <?php if ($access_method === 'subscription'): ?>
                            <div class="alert alert-success mb-3">
                                <i class="bi bi-star-fill me-2"></i>
                                <strong>Included with your subscription!</strong>
                            </div>
                        <?php elseif ($has_purchased): ?>
                            <div class="alert alert-info mb-3">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>You own this book</strong>
                            </div>
                        <?php endif; ?>
                        
                        <div class="price-section">
                            <?php if ($book['is_free']): ?>
                                <div class="price-main price-free">FREE</div>
                            <?php elseif ($has_subscription && $book['is_subscription_allowed']): ?>
                                <div class="price-main text-success">
                                    <i class="bi bi-check-circle-fill"></i> Included
                                </div>
                                <div class="price-alternative text-muted">
                                    Or buy for $<?php echo number_format($book['price'], 2); ?>
                                </div>
                            <?php else: ?>
                                <div class="price-main">$<?php echo number_format($book['price'], 2); ?></div>
                                <?php if ($book['is_subscription_allowed']): ?>
                                    <div class="price-subscription">
                                        <i class="bi bi-star-fill me-1"></i>
                                        Available with subscription
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="stock-info">
                            <?php if ($book['stock'] > 0): ?>
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span class="text-success fw-bold"><?php echo $book['stock']; ?> in stock</span>
                            <?php else: ?>
                                <i class="bi bi-x-circle-fill text-danger"></i>
                                <span class="text-danger fw-bold">Out of Stock</span>
                            <?php endif; ?>
                        </div>

                        <div class="action-buttons">
                            <?php if ($can_download && $book['type'] === 'pdf'): ?>
                                <a href="../get_book.php?id=<?php echo $book_id; ?>" 
                                class="btn btn-success btn-lg w-100 mb-2"
                                target="_blank">
                                    <i class="bi bi-download me-2"></i>Download PDF
                                </a>
                                <?php if ($access_method === 'subscription'): ?>
                                    <small class="text-muted d-block mb-2">
                                        <i class="bi bi-info-circle me-1"></i>Access via subscription
                                    </small>
                                <?php endif; ?>
                                
                            <?php elseif ($has_purchased && $book['type'] !== 'pdf'): ?>
                                <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                    <i class="bi bi-check-circle me-2"></i>Already Purchased
                                </button>
                                <small class="text-muted d-block mb-2">
                                    <?php echo strtoupper($book['type']); ?> will be shipped to you
                                </small>
                                
                            <?php else: ?>
                                <?php if (!isset($_SESSION['user_id'])): ?>
                                    <a href="login.php" class="btn btn-primary btn-lg w-100 mb-2">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Login to Access
                                    </a>
                                    
                                <?php elseif ($has_subscription && $book['is_subscription_allowed']): ?>
                                    <?php if ($book['type'] === 'pdf'): ?>
                                        <a href="../get_book.php?id=<?php echo $book_id; ?>" 
                                        class="btn btn-success btn-lg w-100 mb-2"
                                        target="_blank">
                                            <i class="bi bi-download me-2"></i>Download (Subscription)
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                            <i class="bi bi-info-circle me-2"></i>Physical copies not included
                                        </button>
                                        <a href="order.php?id=<?php echo $book_id; ?>" 
                                        class="btn btn-outline-primary w-100 mb-2">
                                            <i class="bi bi-cart-plus me-2"></i>Buy Physical Copy
                                        </a>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <?php if ($book['stock'] > 0 || $book['type'] === 'pdf'): ?>
                                        <a href="order.php?id=<?php echo $book_id; ?>" 
                                        class="btn btn-primary btn-lg w-100 mb-2">
                                            <i class="bi bi-cart-plus me-2"></i>Buy Now
                                        </a>
                                        
                                        <?php if ($book['is_subscription_allowed'] && !$has_subscription): ?>
                                            <a href="subscribe.php" 
                                            class="btn btn-outline-primary w-100 mb-2">
                                                <i class="bi bi-star me-2"></i>Subscribe for Access
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                            <i class="bi bi-x-circle me-2"></i>Out of Stock
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline-primary w-100" onclick="shareBook()">
                                <i class="bi bi-share me-2"></i>Share
                            </button>
                        </div>
                    </div>
                    
                    <div class="book-features-card">
                        <h6 class="features-title">
                            <i class="bi bi-star-fill me-2"></i>Features
                        </h6>
                        <ul class="features-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Format: <?php echo strtoupper($book['type']); ?></span>
                            </li>
                            <?php if ($book['type'] === 'pdf'): ?>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Instant Download</span>
                            </li>
                            <?php endif; ?>
                            <?php if ($book['is_subscription_allowed']): ?>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Subscription Available</span>
                            </li>
                            <?php endif; ?>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Lifetime Access</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>24/7 Support</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="book-details-content">
                    <span class="book-details-category"><?php echo htmlspecialchars($book['category']); ?></span>
                    <h1 class="book-details-title"><?php echo htmlspecialchars($book['title']); ?></h1>
                    <div class="book-details-author">
                        <i class="bi bi-person-fill me-2"></i>
                        By &nbsp;<strong><?php echo htmlspecialchars($book['author']); ?></strong>
                    </div>
                    
                    <div class="book-details-meta">
                        <div class="meta-item">
                            <i class="bi bi-calendar3"></i>
                            <div>
                                <small>Published</small>
                                <strong><?php echo date('M j, Y', strtotime($book['created_at'])); ?></strong>
                            </div>
                        </div>
                        <div class="meta-item">
                            <i class="bi bi-file-earmark"></i>
                            <div>
                                <small>Format</small>
                                <strong><?php echo strtoupper($book['type']); ?></strong>
                            </div>
                        </div>
                        <div class="meta-item">
                            <i class="bi bi-tag"></i>
                            <div>
                                <small>Category</small>
                                <strong><?php echo htmlspecialchars($book['category']); ?></strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="book-details-tabs">
                        <ul class="nav nav-tabs" id="bookTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                                        data-bs-target="#description" type="button" role="tab">
                                    <i class="bi bi-book me-2"></i>Description
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="details-tab" data-bs-toggle="tab" 
                                        data-bs-target="#details" type="button" role="tab">
                                    <i class="bi bi-info-circle me-2"></i>Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                                        data-bs-target="#reviews" type="button" role="tab">
                                    <i class="bi bi-star me-2"></i>Reviews
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="bookTabsContent">
                            <div class="tab-pane fade show active" id="description" role="tabpanel">
                                <div class="tab-content-wrapper">
                                    <h4 class="content-subtitle">About This Book</h4>
                                    <p class="content-text">
                                        <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                                    </p>
                                    
                                    <h5 class="content-subtitle mt-4">What You'll Learn</h5>
                                    <ul class="content-list">
                                        <li>Comprehensive coverage of all key topics</li>
                                        <li>Practical examples and real-world applications</li>
                                        <li>Step-by-step guidance for beginners</li>
                                        <li>Advanced techniques for experienced readers</li>
                                        <li>Tips and best practices from industry experts</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="details" role="tabpanel">
                                <div class="tab-content-wrapper">
                                    <h4 class="content-subtitle">Book Specifications</h4>
                                    <table class="details-table">
                                        <tr>
                                            <td class="detail-label">Title:</td>
                                            <td class="detail-value"><?php echo htmlspecialchars($book['title']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="detail-label">Author:</td>
                                            <td class="detail-value"><?php echo htmlspecialchars($book['author']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="detail-label">Category:</td>
                                            <td class="detail-value"><?php echo htmlspecialchars($book['category']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="detail-label">Format:</td>
                                            <td class="detail-value"><?php echo strtoupper($book['type']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="detail-label">Price:</td>
                                            <td class="detail-value">
                                                <?php echo $book['is_free'] ? 'FREE' : '$' . number_format($book['price'], 2); ?>
                                            </td>
                                        </tr>
                                        <?php if ($book['is_subscription_allowed']): ?>
                                        <tr>
                                            <td class="detail-label">Subscription:</td>
                                            <td class="detail-value">
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-star-fill me-1"></i>Available with subscription
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td class="detail-label">Stock:</td>
                                            <td class="detail-value"><?php echo $book['stock']; ?> available</td>
                                        </tr>
                                        <tr>
                                            <td class="detail-label">Published:</td>
                                            <td class="detail-value"><?php echo date('F j, Y', strtotime($book['created_at'])); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="reviews" role="tabpanel">
                                <div class="tab-content-wrapper">
                                    <h4 class="content-subtitle">Customer Reviews</h4>
                                    
                                    <!-- Review Summary -->
                                    <div class="reviews-summary mb-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-3 text-center">
                                                <div class="rating-overview">
                                                    <div class="rating-score display-4">
                                                        <?php 
                                                        $avg = (float)($review_summary['average_rating'] ?? 0);
                                                        echo number_format($avg, 1); 
                                                        ?>
                                                    </div>
                                                    <div class="rating-stars">
                                                        <?php 
                                                        $rating = round((float)($review_summary['average_rating'] ?? 0));
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            if ($i <= $rating) {
                                                                echo '<i class="bi bi-star-fill text-warning"></i>';
                                                            } elseif ($i - $rating < 1) {
                                                                echo '<i class="bi bi-star-half text-warning"></i>';
                                                            } else {
                                                                echo '<i class="bi bi-star text-warning"></i>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <p class="rating-count">Based on <?php echo (int)($review_summary['total_reviews'] ?? 0); ?> reviews</p>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="rating-breakdown">
                                                    <?php 
                                                    $stars = [5, 4, 3, 2, 1];
                                                    foreach ($stars as $star) {
                                                        $star_key = $star . '_star';
                                                        $count = (int)($review_summary[$star_key] ?? 0);
                                                        $total = (int)($review_summary['total_reviews'] ?? 1);
                                                        $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                                                        ?>
                                                        <div class="rating-bar-item d-flex align-items-center gap-2 mb-2">
                                                            <span class="rating-label"><?php echo $star; ?> <i class="bi bi-star-fill text-warning"></i></span>
                                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                                <div class="progress-bar bg-warning" style="width: <?php echo round($percentage); ?>%"></div>
                                                            </div>
                                                            <span class="rating-count-small text-muted"><?php echo $count; ?></span>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Write Review Button/Form -->
                                    <?php 
                                    // Check if user can review: either purchased OR has active subscription with book allowed
                                    $can_review = (isset($_SESSION['user_id']) && ($has_purchased || ($has_subscription && $book['is_subscription_allowed'])));
                                    ?>
                                    
                                    <?php if ($can_review): ?>
                                        <?php if (!$user_review): ?>
                                        <div class="write-review-section mb-4">
                                            <button class="btn btn-primary" onclick="toggleReviewForm()">
                                                <i class="bi bi-pencil-square me-2"></i>Write a Review
                                            </button>
                                        </div>

                                        <div id="reviewFormContainer" class="review-form-container mb-4" style="display: none;">
                                            <div class="card border-light">
                                                <div class="card-body">
                                                    <h5 class="card-title mb-3">Share Your Review</h5>
                                                    <form id="reviewForm">
                                                        <div class="mb-3">
                                                            <label class="form-label">Rating *</label>
                                                            <div class="star-rating" id="starRating">
                                                                <i class="bi bi-star" data-rating="1" style="cursor: pointer; font-size: 1.5rem; color: #ffc107;"></i>
                                                                <i class="bi bi-star" data-rating="2" style="cursor: pointer; font-size: 1.5rem; color: #ffc107;"></i>
                                                                <i class="bi bi-star" data-rating="3" style="cursor: pointer; font-size: 1.5rem; color: #ffc107;"></i>
                                                                <i class="bi bi-star" data-rating="4" style="cursor: pointer; font-size: 1.5rem; color: #ffc107;"></i>
                                                                <i class="bi bi-star" data-rating="5" style="cursor: pointer; font-size: 1.5rem; color: #ffc107;"></i>
                                                            </div>
                                                            <input type="hidden" id="ratingInput" name="rating" value="0">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="reviewText" class="form-label">Your Review (Optional)</label>
                                                            <textarea class="form-control" id="reviewText" name="review_text" rows="4" placeholder="Share your thoughts about this book..."></textarea>
                                                            <small class="text-muted">Max 500 characters</small>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button type="submit" class="btn btn-primary" id="submitReviewBtn">
                                                                <i class="bi bi-check-circle me-2"></i>Submit Review
                                                            </button>
                                                            <button type="button" class="btn btn-secondary" onclick="toggleReviewForm()">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <?php elseif ($user_review['status'] === 'pending'): ?>
                                        <div class="alert alert-warning mb-4">
                                            <i class="bi bi-clock-history me-2"></i>
                                            Your review is pending approval by the administrator.
                                        </div>

                                        <?php elseif ($user_review['status'] === 'approved'): ?>
                                        <div class="alert alert-success mb-4">
                                            <i class="bi bi-check-circle me-2"></i>
                                            Thank you for your review! It has been published.
                                        </div>
                                        <?php else: ?>
                                        <div class="alert alert-info mb-4">
                                            <i class="bi bi-info-circle me-2"></i>
                                            You have already reviewed this book.
                                        </div>
                                        <?php endif; ?>
                                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                                        <div class="alert alert-warning mb-4">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <a href="login.php">Sign in</a> to write a review
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-4">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            You must purchase this book or subscribe to write a review
                                        </div>
                                    <?php endif; ?>

                                    <!-- Reviews List -->
                                    <div class="reviews-list">
                                        <?php if ($reviews_result && $reviews_result->num_rows > 0): ?>
                                            <?php while ($review = $reviews_result->fetch_assoc()): ?>
                                            <div class="review-item card mb-3 border-light">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($review['full_name']); ?></h6>
                                                            <div class="review-stars mb-2">
                                                                <?php 
                                                                for ($i = 1; $i <= 5; $i++) {
                                                                    if ($i <= (int)$review['rating']) {
                                                                        echo '<i class="bi bi-star-fill text-warning"></i>';
                                                                    } else {
                                                                        echo '<i class="bi bi-star text-warning"></i>';
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                                    </div>
                                                    <p class="review-text mb-2"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                                </div>
                                            </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <div class="no-reviews-message text-center py-4">
                                                <i class="bi bi-chat-left-text" style="font-size: 2rem;"></i>
                                                <p class="mt-3">No reviews yet. Be the first to review this book!</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($related_books->num_rows > 0): ?>
<section class="related-books-section py-5 bg-light">
    <div class="container">
        <div class="section-header mb-4">
            <h3 class="section-title">
                <i class="bi bi-bookmark-star me-2"></i>Related Books
            </h3>
            <p class="text-muted">More books from the <?php echo htmlspecialchars($book['category']); ?> category</p>
        </div>
        
        <div class="row">
            <?php while ($related = $related_books->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="book-card">
                    <div class="book-image" style="position: relative; overflow: hidden; height: 300px;">
                        <img src="<?php echo getBookImage($related); ?>" 
                             alt="<?php echo htmlspecialchars($related['title']); ?>" 
                             class="img-fluid"
                             style="width: 100%; height: 100%; object-fit: cover;"
                             onerror="this.src='../assets/images/books/default.jpg'">
                    </div>
                    <div class="book-content">
                        <span class="book-category"><?php echo htmlspecialchars($related['category']); ?></span>
                        <h5 class="book-title"><?php echo htmlspecialchars($related['title']); ?></h5>
                        <p class="book-author">By <?php echo htmlspecialchars($related['author']); ?></p>
                        <div class="book-footer">
                            <span class="book-price">
                                <?php echo $related['is_free'] ? 'FREE' : '$' . number_format($related['price'], 2); ?>
                            </span>
                            <a href="book_details.php?id=<?php echo $related['book_id']; ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
function shareBook() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes($book['title']); ?>',
            text: 'Check out this book: <?php echo addslashes($book['title']); ?> by <?php echo addslashes($book['author']); ?>',
            url: window.location.href
        }).catch(console.error);
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link copied to clipboard!');
        });
    }
}
</script>
<script>
    let selectedRating = 0;

// Only initialize if starRating element exists
const starRatingElement = document.getElementById('starRating');
if (starRatingElement) {
    starRatingElement.addEventListener('click', function(e) {
        if (e.target.classList.contains('bi')) {
            selectedRating = parseInt(e.target.dataset.rating);
            document.getElementById('ratingInput').value = selectedRating;
            updateStarDisplay();
        }
    });
}

function updateStarDisplay() {
    const stars = document.querySelectorAll('#starRating i');
    stars.forEach((star, index) => {
        if (index < selectedRating) {
            star.classList.remove('bi-star');
            star.classList.add('bi-star-fill', 'text-warning');
        } else {
            star.classList.remove('bi-star-fill', 'text-warning');
            star.classList.add('bi-star');
        }
    });
}

function toggleReviewForm() {
    const container = document.getElementById('reviewFormContainer');
    if (container) {
        container.style.display = container.style.display === 'none' ? 'block' : 'none';
    }
}

// Review form submission
const reviewForm = document.getElementById('reviewForm');
if (reviewForm) {
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (selectedRating === 0) {
            alert('Please select a rating');
            return;
        }

        const reviewText = document.getElementById('reviewText').value.trim();
        if (reviewText.length > 500) {
            alert('Review must be 500 characters or less');
            return;
        }

        const bookId = new URLSearchParams(window.location.search).get('id');
        
        const formData = new FormData();
        formData.append('action', 'submit_review');
        formData.append('book_id', bookId);
        formData.append('rating', selectedRating);
        formData.append('review_text', reviewText);

        const submitBtn = document.getElementById('submitReviewBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting...';

        fetch('../includes/handle_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Submit Review';

            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Submit Review';
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        });
    });
}

// Helpful button functionality
document.querySelectorAll('.helpful-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const reviewId = this.dataset.reviewId;
        const formData = new FormData();
        formData.append('action', 'mark_helpful');
        formData.append('review_id', reviewId);

        fetch('../includes/handle_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.querySelector('span').textContent = data.helpful_count;
                this.disabled = true;
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>
<?php
include '../includes/footer.php';
?>