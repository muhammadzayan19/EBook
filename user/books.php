<?php
session_start();

require_once '../config/db.php';

$page_title = "Browse Books";

include '../includes/header.php';

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';

$query = "SELECT * FROM books WHERE 1=1";
$params = [];
$types = "";

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR author LIKE ? OR description LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if (!empty($type)) {
    $query .= " AND type = ?";
    $params[] = $type;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

$categories_result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="display-4 fw-bold mb-3">Browse Our Collection</h1>
                <p class="lead mb-4">Discover thousands of books across various categories and formats</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item active">Books</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Books Section -->
<section class="books-listing-section py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="filters-sidebar">
                    <div class="filter-card">
                        <h5 class="filter-title">
                            <i class="bi bi-funnel me-2"></i>Filters
                        </h5>
                        
                        <!-- Search Form -->
                        <form method="GET" action="" class="mb-4">
                            <div class="search-box">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search books..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="search-btn">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>

                        <!-- Category Filter -->
                        <div class="filter-group">
                            <h6 class="filter-group-title">Category</h6>
                            <div class="filter-options">
                                <a href="books.php" class="filter-option <?php echo empty($category) ? 'active' : ''; ?>">
                                    All Categories
                                </a>
                                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                <a href="?category=<?php echo urlencode($cat['category']); ?>" 
                                   class="filter-option <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </a>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <!-- Type Filter -->
                        <div class="filter-group">
                            <h6 class="filter-group-title">Format</h6>
                            <div class="filter-options">
                                <a href="?<?php echo http_build_query(array_filter(['category' => $category, 'search' => $search])); ?>" 
                                   class="filter-option <?php echo empty($type) ? 'active' : ''; ?>">
                                    All Formats
                                </a>
                                <a href="?<?php echo http_build_query(array_filter(['category' => $category, 'search' => $search, 'type' => 'pdf'])); ?>" 
                                   class="filter-option <?php echo $type === 'pdf' ? 'active' : ''; ?>">
                                    <i class="bi bi-file-pdf me-2"></i>PDF
                                </a>
                                <a href="?<?php echo http_build_query(array_filter(['category' => $category, 'search' => $search, 'type' => 'cd'])); ?>" 
                                   class="filter-option <?php echo $type === 'cd' ? 'active' : ''; ?>">
                                    <i class="bi bi-disc me-2"></i>CD
                                </a>
                                <a href="?<?php echo http_build_query(array_filter(['category' => $category, 'search' => $search, 'type' => 'hardcopy'])); ?>" 
                                   class="filter-option <?php echo $type === 'hardcopy' ? 'active' : ''; ?>">
                                    <i class="bi bi-book me-2"></i>Hard Copy
                                </a>
                            </div>
                        </div>

                        <!-- Clear Filters -->
                        <?php if (!empty($category) || !empty($search) || !empty($type)): ?>
                        <div class="mt-4">
                            <a href="books.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-x-circle me-2"></i>Clear Filters
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Books Grid -->
            <div class="col-lg-9">
                <div class="books-header mb-4">
                    <div class="results-info">
                        <h5 class="mb-0">
                            <?php echo $result->num_rows; ?> Books Found
                        </h5>
                    </div>
                </div>

                <?php if ($result->num_rows > 0): ?>
                <div class="row">
                    <?php while ($book = $result->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="book-card">
                            <div class="book-image">
                                <img src="../assets/images/books/default.jpg" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                     class="img-fluid">
                                
                                <?php if ($book['is_free']): ?>
                                <div class="book-badge badge-free">Free</div>
                                <?php elseif ($book['stock'] > 0 && $book['stock'] < 10): ?>
                                <div class="book-badge badge-limited">Limited Stock</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="book-content">
                                <span class="book-category"><?php echo htmlspecialchars($book['category']); ?></span>
                                <h5 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="book-author">By <?php echo htmlspecialchars($book['author']); ?></p>
                                <p class="book-description">
                                    <?php echo htmlspecialchars(substr($book['description'], 0, 100)) . '...'; ?>
                                </p>
                                
                                <div class="book-meta">
                                    <span class="book-type">
                                        <i class="bi bi-<?php echo $book['type'] === 'pdf' ? 'file-pdf' : ($book['type'] === 'cd' ? 'disc' : 'book'); ?>"></i>
                                        <?php echo strtoupper($book['type']); ?>
                                    </span>
                                    <span class="book-stock">
                                        <i class="bi bi-box-seam"></i>
                                        <?php echo $book['stock']; ?> in stock
                                    </span>
                                </div>
                                
                                <div class="book-footer">
                                    <div class="book-price-info">
                                        <?php if ($book['is_free']): ?>
                                            <span class="book-price">FREE</span>
                                        <?php else: ?>
                                            <span class="book-price">$<?php echo number_format($book['price'], 2); ?></span>
                                            <?php if ($book['subscription_price'] > 0): ?>
                                                <small class="subscription-price">
                                                    or $<?php echo number_format($book['subscription_price'], 2); ?>/year
                                                </small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <a href="book_details.php?id=<?php echo $book['book_id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div class="no-results">
                    <div class="text-center py-5">
                        <i class="bi bi-search" style="font-size: 4rem; opacity: 0.3;"></i>
                        <h4 class="mt-3 mb-2">No Books Found</h4>
                        <p class="text-muted mb-4">Try adjusting your filters or search terms</p>
                        <a href="books.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>View All Books
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
include '../includes/footer.php';
?>