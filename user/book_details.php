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
                        By <strong><?php echo htmlspecialchars($book['author']); ?></strong>
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
                                    <div class="reviews-summary">
                                        <div class="rating-overview">
                                            <div class="rating-score">4.5</div>
                                            <div class="rating-stars">
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-half"></i>
                                            </div>
                                            <p class="rating-count">Based on 128 reviews</p>
                                        </div>
                                    </div>
                                    
                                    <div class="no-reviews-message">
                                        <i class="bi bi-chat-left-text"></i>
                                        <p>Be the first to review this book!</p>
                                        <?php if ($has_purchased): ?>
                                            <button class="btn btn-primary btn-sm">Write a Review</button>
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

<?php
include '../includes/footer.php';
?>