<?php
session_start();

require_once '../config/db.php';
require_once '../includes/subscription_helper.php';

// Check if this is an AJAX request
$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

if ($is_ajax) {
    // Handle AJAX request - return JSON
    header('Content-Type: application/json');
    
    try {
        $category = $_GET['category'] ?? '';
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $subscription_filter = isset($_GET['subscription']) ? intval($_GET['subscription']) : 0;

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

        if ($subscription_filter) {
            $query .= " AND is_subscription_allowed = 1";
        }

        $query .= " ORDER BY created_at DESC";

        if (!empty($params)) {
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $conn->error);
            }
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($query);
            if (!$result) {
                throw new Exception("Database query error: " . $conn->error);
            }
        }

        $user_has_subscription = false;
        if (isset($_SESSION['user_id'])) {
            $user_has_subscription = hasActiveSubscription($conn, $_SESSION['user_id']) !== false;
        }

        function getBookImageAjax($book) {
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

        $books = [];
        while ($book = $result->fetch_assoc()) {
            $book['processed_image_path'] = getBookImageAjax($book);
            $books[] = $book;
        }

        echo json_encode([
            'success' => true,
            'count' => count($books),
            'books' => $books,
            'has_subscription' => $user_has_subscription
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'count' => 0,
            'books' => [],
            'has_subscription' => false
        ]);
    }
    exit;
}

// Regular page load
$page_title = "Browse Books";
include '../includes/header.php';

$categories_result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");

$user_has_subscription = false;
if (isset($_SESSION['user_id'])) {
    $user_has_subscription = hasActiveSubscription($conn, $_SESSION['user_id']) !== false;
}
?>

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="display-4 fw-bold mb-3">Browse Our Collection</h1>
                <p class="lead mb-4">Discover thousands of books across various categories and formats</p>
                <?php if ($user_has_subscription): ?>
                    <div class="alert alert-success d-inline-block">
                        <i class="bi bi-star-fill me-2"></i>You have an active subscription!
                    </div>
                <?php endif; ?>
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

<section class="books-listing-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="filters-sidebar">
                    <div class="filter-card">
                        <h5 class="filter-title">
                            <i class="bi bi-funnel me-2"></i>Filters
                        </h5>
                        
                        <form id="searchForm" class="mb-4">
                            <div class="search-box">
                                <input type="text" id="searchInput" name="search" class="form-control" 
                                       placeholder="Search books...">
                                <button type="submit" class="search-btn">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>

                        <div class="filter-group">
                            <h6 class="filter-group-title">Category</h6>
                            <div class="filter-options">
                                <a href="#" class="filter-option active" data-filter="category" data-value="">
                                    All Categories
                                </a>
                                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                <a href="#" class="filter-option" data-filter="category" data-value="<?php echo htmlspecialchars($cat['category']); ?>">
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </a>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h6 class="filter-group-title">Format</h6>
                            <div class="filter-options">
                                <a href="#" class="filter-option active" data-filter="type" data-value="">
                                    All Formats
                                </a>
                                <a href="#" class="filter-option" data-filter="type" data-value="pdf">
                                    <i class="bi bi-file-pdf me-2"></i>PDF
                                </a>
                                <a href="#" class="filter-option" data-filter="type" data-value="cd">
                                    <i class="bi bi-disc me-2"></i>CD
                                </a>
                                <a href="#" class="filter-option" data-filter="type" data-value="hardcopy">
                                    <i class="bi bi-book me-2"></i>Hard Copy
                                </a>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h6 class="filter-group-title">Subscription</h6>
                            <div class="filter-options">
                                <a href="#" class="filter-option active" data-filter="subscription" data-value="0">
                                    All Books
                                </a>
                                <a href="#" class="filter-option" data-filter="subscription" data-value="1">
                                    <i class="bi bi-star-fill me-2"></i>Subscription Books Only
                                </a>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button id="clearFilters" class="btn btn-outline-primary w-100" style="display: none;">
                                <i class="bi bi-x-circle me-2"></i>Clear Filters
                            </button>
                        </div>
                        
                        <?php if (!$user_has_subscription): ?>
                        <div class="mt-4 p-3 bg-primary text-white rounded">
                            <h6 class="mb-2"><i class="bi bi-star-fill me-2"></i>Get Unlimited Access</h6>
                            <p class="small mb-2">Subscribe for unlimited access to all subscription books!</p>
                            <a href="subscribe.php" class="btn btn-light btn-sm w-100">
                                <i class="bi bi-arrow-repeat me-1"></i>Subscribe Now
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="books-header mb-4">
                    <div class="results-info">
                        <h5 class="mb-0">
                            <span id="booksCount">Loading...</span> Books Found
                        </h5>
                        <p id="filterMessage" class="text-muted mb-0" style="display: none;">Showing subscription-enabled books only</p>
                    </div>
                </div>

                <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div id="booksContainer" class="row">
                    <!-- Books will be loaded here via AJAX -->
                </div>

                <div id="noResults" class="no-results" style="display: none;">
                    <div class="text-center py-5">
                        <i class="bi bi-search" style="font-size: 4rem; opacity: 0.3;"></i>
                        <h4 class="mt-3 mb-2">No Books Found</h4>
                        <p class="text-muted mb-4">Try adjusting your filters or search terms</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Filter state
const filters = {
    category: '',
    type: '',
    search: '',
    subscription: 0
};

const userHasSubscription = <?php echo $user_has_subscription ? 'true' : 'false'; ?>;

// Load books function
function loadBooks() {
    const container = document.getElementById('booksContainer');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const noResults = document.getElementById('noResults');
    const booksCount = document.getElementById('booksCount');
    const filterMessage = document.getElementById('filterMessage');
    
    // Show loading
    loadingSpinner.style.display = 'block';
    container.style.display = 'none';
    noResults.style.display = 'none';
    
    // Build query string
    const params = new URLSearchParams({
        ajax: '1',
        ...filters
    });
    
    // Fetch books
    fetch(`?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            loadingSpinner.style.display = 'none';
            
            if (!data.success) {
                throw new Error(data.error || 'Unknown error occurred');
            }
            
            booksCount.textContent = data.count;
            
            // Show/hide filter message
            if (filters.subscription == 1) {
                filterMessage.style.display = 'block';
            } else {
                filterMessage.style.display = 'none';
            }
            
            if (data.count === 0) {
                noResults.style.display = 'block';
            } else {
                container.innerHTML = renderBooks(data.books, data.has_subscription);
                container.style.display = 'flex';
            }
            
            // Update URL without reload
            updateURL();
        })
        .catch(error => {
            console.error('Error loading books:', error);
            loadingSpinner.style.display = 'none';
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Error loading books: ${error.message}. Please refresh the page and try again.
                    </div>
                </div>
            `;
            container.style.display = 'block';
        });
}

// Render books HTML
function renderBooks(books, hasSubscription) {
    return books.map(book => {
        const imagePath = book.processed_image_path || '../assets/images/books/default.jpg';
        const badges = [];
        
        if (book.is_free == 1) {
            badges.push('<div class="book-badge badge-free">Free</div>');
        } else if (book.is_subscription_allowed == 1 && hasSubscription) {
            badges.push('<div class="book-badge" style="background: #28a745;"><i class="bi bi-check-circle me-1"></i>Included</div>');
        } else if (book.stock > 0 && book.stock < 10) {
            badges.push('<div class="book-badge badge-limited">Limited Stock</div>');
        } else if (book.stock == 0) {
            badges.push('<div class="book-badge badge-outstock">Out of Stock</div>');
        }
        
        if (book.is_subscription_allowed == 1) {
            badges.push('<div class="book-badge" style="top: auto; bottom: 10px; left: 10px; background: rgba(255, 193, 7, 0.9);"><i class="bi bi-star-fill"></i></div>');
        }
        
        const typeIcon = book.type === 'pdf' ? 'file-pdf' : (book.type === 'cd' ? 'disc' : 'book');
        
        let priceHTML = '';
        if (book.is_free == 1) {
            priceHTML = '<span class="book-price">FREE</span>';
        } else if (hasSubscription && book.is_subscription_allowed == 1) {
            priceHTML = '<span class="book-price text-success"><i class="bi bi-check-circle-fill me-1"></i>Included</span>';
        } else {
            priceHTML = `<span class="book-price">$${parseFloat(book.price).toFixed(2)}</span>`;
            if (book.is_subscription_allowed == 1 && parseFloat(book.subscription_price) > 0) {
                priceHTML += '<small class="subscription-price"><i class="bi bi-star-fill"></i> Subscription available</small>';
            }
        }
        
        return `
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="book-card">
                    <div class="book-image" style="position: relative; overflow: hidden; height: 300px;">
                        <img src="${imagePath}" 
                             alt="${escapeHtml(book.title)}" 
                             class="img-fluid"
                             style="width: 100%; height: 100%; object-fit: cover;"
                             onerror="this.src='../assets/images/books/default.jpg'">
                        ${badges.join('')}
                    </div>
                    
                    <div class="book-content">
                        <span class="book-category">${escapeHtml(book.category)}</span>
                        <h5 class="book-title">${escapeHtml(book.title)}</h5>
                        <p class="book-author">By ${escapeHtml(book.author)}</p>
                        <p class="book-description">
                            ${escapeHtml((book.description || '').substring(0, 100))}...
                        </p>
                        
                        <div class="book-meta">
                            <span class="book-type">
                                <i class="bi bi-${typeIcon}"></i>
                                ${book.type.toUpperCase()}
                            </span>
                            <span class="book-stock">
                                <i class="bi bi-box-seam"></i>
                                ${book.stock} in stock
                            </span>
                        </div>
                        
                        <div class="book-footer">
                            <div class="book-price-info">
                                ${priceHTML}
                            </div>
                            <a href="book_details.php?id=${book.book_id}" 
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Helper functions
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updateURL() {
    const params = new URLSearchParams();
    if (filters.category) params.set('category', filters.category);
    if (filters.type) params.set('type', filters.type);
    if (filters.search) params.set('search', filters.search);
    if (filters.subscription) params.set('subscription', filters.subscription);
    
    const newURL = params.toString() ? `?${params.toString()}` : 'books.php';
    window.history.replaceState({}, '', newURL);
    
    // Show/hide clear filters button
    const hasFilters = filters.category || filters.type || filters.search || filters.subscription;
    document.getElementById('clearFilters').style.display = hasFilters ? 'block' : 'none';
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Parse URL parameters on load first
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('category')) {
        filters.category = urlParams.get('category');
        const elem = document.querySelector(`[data-filter="category"][data-value="${CSS.escape(filters.category)}"]`);
        if (elem) {
            document.querySelectorAll('[data-filter="category"]').forEach(e => e.classList.remove('active'));
            elem.classList.add('active');
        }
    }
    if (urlParams.has('type')) {
        filters.type = urlParams.get('type');
        const elem = document.querySelector(`[data-filter="type"][data-value="${filters.type}"]`);
        if (elem) {
            document.querySelectorAll('[data-filter="type"]').forEach(e => e.classList.remove('active'));
            elem.classList.add('active');
        }
    }
    if (urlParams.has('search')) {
        filters.search = urlParams.get('search');
        document.getElementById('searchInput').value = filters.search;
    }
    if (urlParams.has('subscription')) {
        filters.subscription = parseInt(urlParams.get('subscription'));
        const elem = document.querySelector(`[data-filter="subscription"][data-value="${filters.subscription}"]`);
        if (elem) {
            document.querySelectorAll('[data-filter="subscription"]').forEach(e => e.classList.remove('active'));
            elem.classList.add('active');
        }
    }
    
    // Load initial books
    loadBooks();
    
    // Search form
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        filters.search = document.getElementById('searchInput').value;
        loadBooks();
    });
    
    // Filter options
    document.querySelectorAll('.filter-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            
            const filterType = this.dataset.filter;
            const filterValue = this.dataset.value;
            
            // Update filter state
            if (filterType === 'subscription') {
                filters[filterType] = parseInt(filterValue);
            } else {
                filters[filterType] = filterValue;
            }
            
            // Update active class
            const group = this.closest('.filter-group');
            group.querySelectorAll('.filter-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            // Load books
            loadBooks();
        });
    });
    
    // Clear filters
    document.getElementById('clearFilters').addEventListener('click', function() {
        filters.category = '';
        filters.type = '';
        filters.search = '';
        filters.subscription = 0;
        
        document.getElementById('searchInput').value = '';
        
        // Reset active states
        document.querySelectorAll('.filter-option').forEach(opt => opt.classList.remove('active'));
        document.querySelectorAll('.filter-group').forEach(group => {
            group.querySelector('.filter-option').classList.add('active');
        });
        
        loadBooks();
    });
});
</script>

<?php
include '../includes/footer.php';
?>