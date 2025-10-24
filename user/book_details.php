<?php
session_start();

require_once '../config/db.php';

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

// Function to get book image path
function getBookImage($book) {
    if (!empty($book['image_path']) && file_exists('../' . $book['image_path'])) {
        return '../' . $book['image_path'];
    } elseif (!empty($book['image_path']) && file_exists($book['image_path'])) {
        return $book['image_path'];
    } else {
        return '../assets/images/books/default.jpg';
    }
}

include '../includes/header.php';

$has_purchased = false;
$can_download = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $purchase_check = $conn->prepare("
        SELECT o.order_id, o.status, p.payment_status 
        FROM orders o 
        LEFT JOIN payments p ON o.order_id = p.order_id 
        WHERE o.user_id = ? AND o.book_id = ?
    ");
    $purchase_check->bind_param("ii", $user_id, $book_id);
    $purchase_check->execute();
    $purchase_result = $purchase_check->get_result();
    if ($purchase_result->num_rows > 0) {
        $order = $purchase_result->fetch_assoc();
        $has_purchased = true;
        $can_download = ($order['status'] === 'paid' || $order['payment_status'] === 'completed');
    }
    $purchase_check->close();
}

$related_stmt = $conn->prepare("SELECT * FROM books WHERE category = ? AND book_id != ? LIMIT 3");
$related_stmt->bind_param("si", $book['category'], $book_id);
$related_stmt->execute();
$related_books = $related_stmt->get_result();
$related_stmt->close();
?>

<!-- Page Header -->
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

<!-- Book Details Section -->
<section class="book-details-section py-5">
    <div class="container">
        <div class="row">
            <!-- Book Image & Quick Actions -->
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
                        <?php elseif ($book['stock'] > 0 && $book['stock'] < 10): ?>
                            <div class="book-details-badge badge-limited">Limited Stock</div>
                        <?php elseif ($book['stock'] == 0): ?>
                            <div class="book-details-badge badge-outstock">Out of Stock</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Actions Card -->
                    <div class="quick-actions-card">
                        <div class="price-section">
                            <?php if ($book['is_free']): ?>
                                <div class="price-main price-free">FREE</div>
                            <?php else: ?>
                                <div class="price-main">$<?php echo number_format($book['price'], 2); ?></div>
                                <?php if ($book['subscription_price'] > 0): ?>
                                    <div class="price-subscription">
                                        <i class="bi bi-arrow-repeat me-1"></i>
                                        $<?php echo number_format($book['subscription_price'], 2); ?>/year subscription
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
                            <?php if ($has_purchased): ?>
                                <?php if ($can_download && $book['type'] === 'pdf'): ?>
                                    <a href="download.php?book_id=<?php echo $book_id; ?>" class="btn btn-success btn-lg w-100 mb-2">
                                        <i class="bi bi-download me-2"></i>Download PDF
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                        <i class="bi bi-check-circle me-2"></i>Already Purchased
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($book['stock'] > 0): ?>
                                    <a href="order.php?id=<?php echo $book_id; ?>" class="btn btn-primary btn-lg w-100 mb-2">
                                        <i class="bi bi-cart-plus me-2"></i>Buy Now
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>
                                        <i class="bi bi-x-circle me-2"></i>Out of Stock
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline-primary w-100" onclick="shareBook()">
                                <i class="bi bi-share me-2"></i>Share
                            </button>
                        </div>
                    </div>
                    
                    <!-- Features List -->
                    <div class="book-features-card">
                        <h6 class="features-title">
                            <i class="bi bi-star-fill me-2"></i>Features
                        </h6>
                        <ul class="features-list">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Format: <?php echo strtoupper($book['type']); ?></span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Instant Access</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Lifetime Updates</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                <span>24/7 Support</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Book Information -->
            <div class="col-lg-8">
                <div class="book-details-content">
                    <!-- Category Badge -->
                    <span class="book-details-category"><?php echo htmlspecialchars($book['category']); ?></span>
                    
                    <!-- Title -->
                    <h1 class="book-details-title"><?php echo htmlspecialchars($book['title']); ?></h1>
                    
                    <!-- Author -->
                    <div class="book-details-author">
                        <i class="bi bi-person-fill me-2"></i>
                        By <strong><?php echo htmlspecialchars($book['author']); ?></strong>
                    </div>
                    
                    <!-- Meta Information -->
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
                    
                    <!-- Tabs -->
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
                            <!-- Description Tab -->
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
                            
                            <!-- Details Tab -->
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
                            
                            <!-- Reviews Tab -->
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

<!-- Related Books Section -->
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