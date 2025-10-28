<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$page_title = "My Books";

// Fetch user's books with proper table names
$stmt = $conn->prepare("
    SELECT DISTINCT
        b.book_id,
        b.title,
        b.author,
        b.category,
        b.description,
        b.price,
        b.type,
        b.file_path,
        b.image_path,
        b.is_free,
        o.order_id,
        o.order_date,
        o.order_type,
        o.status as order_status,
        p.payment_status
    FROM orders o
    INNER JOIN books b ON o.book_id = b.book_id
    LEFT JOIN payments p ON o.order_id = p.order_id
    WHERE o.user_id = ? AND p.payment_status = 'completed'

    UNION

    SELECT DISTINCT
        b.book_id,
        b.title,
        b.author,
        b.category,
        b.description,
        b.price,
        b.type,
        b.file_path,
        b.image_path,
        b.is_free,
        NULL as order_id,
        s.start_date as order_date,
        'subscription' as order_type,
        s.status as order_status,
        'active' as payment_status
    FROM subscriptions s
    INNER JOIN subscription_access sa ON s.subscription_id = sa.subscription_id
    INNER JOIN books b ON sa.book_id = b.book_id
    WHERE s.user_id = ? AND s.status = 'active' AND (s.end_date IS NULL OR s.end_date > NOW())

    ORDER BY order_date DESC
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_books = count($books);
$pdf_count = 0;
$physical_count = 0;
$subscription_count = 0;
$categories = [];

foreach ($books as $book) {
    if ($book['order_type'] === 'pdf') {
        $pdf_count++;
    } elseif ($book['order_type'] === 'subscription') {
        $subscription_count++;
    } else {
        $physical_count++;
    }
    
    if (!in_array($book['category'], $categories)) {
        $categories[] = $book['category'];
    }
}

$categories_count = count($categories);

// Helper function to get book image
function getBookImage($book) {
    if (empty($book['image_path'])) {
        return '../assets/images/books/default.jpg';
    }
    
    $paths = [
        '../' . $book['image_path'],
        $book['image_path'],
        '../uploads/book_covers/' . basename($book['image_path'])
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return '../assets/images/books/default.jpg';
}

include '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="display-5 fw-bold mb-3">My Library</h1>
                <p class="lead mb-4">Your personal collection of purchased books</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="profile.php">Profile</a></li>
                        <li class="breadcrumb-item active">My Books</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="my-books-section py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="library-stat-card">
                    <div class="stat-icon-wrapper stat-books">
                        <i class="bi bi-book-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $total_books; ?></div>
                        <div class="stat-label">Total Books</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="library-stat-card">
                    <div class="stat-icon-wrapper stat-digital">
                        <i class="bi bi-file-pdf"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $pdf_count; ?></div>
                        <div class="stat-label">Digital Books</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="library-stat-card">
                    <div class="stat-icon-wrapper stat-physical">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $physical_count; ?></div>
                        <div class="stat-label">Physical Books</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="library-stat-card">
                    <div class="stat-icon-wrapper stat-success">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $subscription_count; ?></div>
                        <div class="stat-label">Subscription <br>Books</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="library-filters-bar">
            <div class="row align-items-center">
                <div class="col-md-5 mb-3 mb-md-0">
                    <div class="search-box-library">
                        <input type="text" id="searchBooks" class="form-control" 
                               placeholder="Search your library...">
                        <i class="bi bi-search search-icon"></i>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="filter-buttons">
                            <button class="filter-btn active" data-filter="all">
                                <i class="bi bi-grid"></i> <span>All</span>
                            </button>
                            <button class="filter-btn" data-filter="pdf">
                                <i class="bi bi-file-pdf"></i> <span>PDF</span>
                            </button>
                            <button class="filter-btn" data-filter="subscription">
                                <i class="bi bi-star"></i> <span>Subscription</span>
                            </button>
                            <button class="filter-btn" data-filter="physical">
                                <i class="bi bi-box"></i> <span>Physical</span>
                            </button>
                        </div>
                        <div class="view-toggle">
                            <button class="view-btn active" data-view="grid" title="Grid View">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </button>
                            <button class="view-btn" data-view="list" title="List View">
                                <i class="bi bi-list"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($books)): ?>
        <div class="no-books-section">
            <div class="no-books-icon">
                <i class="bi bi-book"></i>
            </div>
            <h3 class="no-books-title">Your Library is Empty</h3>
            <p class="no-books-text">Start building your collection by purchasing books from our store!</p>
            <a href="books.php" class="btn btn-primary btn-lg">
                <i class="bi bi-shop me-2"></i>Browse Books
            </a>
        </div>
        <?php else: ?>
        <div class="books-grid" id="booksGrid">
            <?php foreach ($books as $book): 
                $imagePath = getBookImage($book);
            ?>
            <div class="library-book-item" 
                 data-type="<?php echo htmlspecialchars($book['order_type']); ?>" 
                 data-title="<?php echo strtolower(htmlspecialchars($book['title'])); ?>"
                 data-author="<?php echo strtolower(htmlspecialchars($book['author'])); ?>"
                 data-category="<?php echo strtolower(htmlspecialchars($book['category'])); ?>">
                <div class="library-book-card">
                    <div class="book-card-image">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             alt="<?php echo htmlspecialchars($book['title']); ?>"
                             onerror="this.src='../assets/images/books/default.jpg'">
                        <div class="book-type-overlay">
                            <?php if ($book['order_type'] === 'subscription'): ?>
                                <span class="type-badge badge-subscription">
                                    <i class="bi bi-star"></i> Subscription
                                </span>
                            <?php elseif ($book['order_type'] === 'pdf'): ?>
                                <span class="type-badge badge-pdf">
                                    <i class="bi bi-file-pdf"></i> PDF
                                </span>
                            <?php elseif ($book['order_type'] === 'cd'): ?>
                                <span class="type-badge badge-cd">
                                    <i class="bi bi-disc"></i> CD
                                </span>
                            <?php else: ?>
                                <span class="type-badge badge-hardcopy">
                                    <i class="bi bi-book"></i> Hardcopy
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($book['is_free']): ?>
                        <div class="free-badge">FREE</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="book-card-body">
                        <span class="book-category-tag"><?php echo htmlspecialchars($book['category']); ?></span>
                        <h5 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                        <p class="book-author">
                            <i class="bi bi-person"></i>
                            <?php echo htmlspecialchars($book['author']); ?>
                        </p>
                        <p class="book-description">
                            <?php echo htmlspecialchars(substr($book['description'], 0, 100)) . (strlen($book['description']) > 100 ? '...' : ''); ?>
                        </p>
                        
                        <div class="book-purchase-info">
                            <div class="purchase-date">
                                <i class="bi bi-calendar-check"></i>
                                <span><?php echo $book['order_type'] === 'subscription' ? 'Subscription Access' : 'Purchased ' . date('M j, Y', strtotime($book['order_date'])); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="book-card-actions">
                        <?php if (($book['order_type'] === 'pdf' || $book['order_type'] === 'subscription') && !empty($book['file_path'])): ?>
                            <a href="download.php?book_id=<?php echo $book['book_id']; ?>" 
                               class="btn btn-primary btn-action">
                                <i class="bi bi-download"></i>
                                <span>Download PDF</span>
                            </a>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline-secondary btn-action read-online-btn" 
                                data-book-id="<?php echo $book['book_id']; ?>"
                                data-book-title="<?php echo htmlspecialchars($book['title']); ?>"
                                data-file-path="<?php echo htmlspecialchars($book['file_path']); ?>">
                            <i class="bi bi-book-half"></i>
                            <span>Read Online</span>
                        </button>

                        
                        <a href="book_details.php?id=<?php echo $book['book_id']; ?>" 
                           class="btn btn-outline-primary btn-action">
                            <i class="bi bi-info-circle"></i>
                            <span>View Details</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="modal fade" id="readingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content reading-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="readingModalTitle">
                    <i class="bi bi-book-half"></i>Reading Mode
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="reading-container">
                    <div class="reading-placeholder">
                        <i class="bi bi-book"></i>
                        <h4>PDF Reader</h4>
                        <p>Your book will be displayed here.</p>
                        <small class="text-muted">Click "Read Online" on any book to start reading</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($books)): ?>
<section class="recommendations-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <span class="section-label">Based on Your Library</span>
            <h2 class="section-title">You Might Also Like</h2>
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <a href="books.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-compass me-2"></i>Discover More Books
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
// Search functionality
document.getElementById('searchBooks').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const books = document.querySelectorAll('.library-book-item');
    
    books.forEach(book => {
        const title = book.dataset.title;
        const author = book.dataset.author;
        const category = book.dataset.category;
        
        if (title.includes(searchTerm) || author.includes(searchTerm) || category.includes(searchTerm)) {
            book.style.display = '';
        } else {
            book.style.display = 'none';
        }
    });
});

// Filter functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const books = document.querySelectorAll('.library-book-item');
        
        books.forEach(book => {
            const type = book.dataset.type;
            
            if (filter === 'all') {
                book.style.display = '';
            } else if (filter === 'physical') {
                book.style.display = (type === 'cd' || type === 'hardcopy') ? '' : 'none';
            } else {
                book.style.display = type === filter ? '' : 'none';
            }
        });
    });
});

// View toggle
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const view = this.dataset.view;
        const grid = document.getElementById('booksGrid');
        
        if (view === 'list') {
            grid.classList.add('books-list-view');
            grid.classList.remove('books-grid');
        } else {
            grid.classList.add('books-grid');
            grid.classList.remove('books-list-view');
        }
    });
});

// Reading modal
document.querySelectorAll('.read-online-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const bookTitle = this.dataset.bookTitle;
        const filePath = this.dataset.filePath;

        const modalTitle = document.getElementById('readingModalTitle');
        const readingContainer = document.querySelector('.reading-container');

        modalTitle.innerHTML = `<i class="bi bi-book-half me-2"></i>${bookTitle}`;

        // If no file path
        if (!filePath) {
            readingContainer.innerHTML = `
                <div class="reading-placeholder">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h4>File Not Available</h4>
                    <p>Sorry, this book doesn't have an online version yet.</p>
                </div>
            `;
        } else {
            // Show PDF inside iframe
            readingContainer.innerHTML = `
                <embed src="../<?php echo $book['file_path']; ?>#view=FitH" type="application/pdf" width="90%" height="500px" />
            `;
        }

        // Open modal
        const modal = new bootstrap.Modal(document.getElementById('readingModal'));
        modal.show();
    });
});


// Animate book cards on load
document.addEventListener('DOMContentLoaded', function() {
    const books = document.querySelectorAll('.library-book-item');
    books.forEach((book, index) => {
        setTimeout(() => {
            book.style.opacity = '0';
            book.style.transform = 'translateY(20px)';
            book.style.transition = 'all 0.4s ease-out';
            
            setTimeout(() => {
                book.style.opacity = '1';
                book.style.transform = 'translateY(0)';
            }, 50);
        }, index * 80);
    });
});
</script>

<?php
include '../includes/footer.php';
?>