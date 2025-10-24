<?php
$page_title = "Home";
include 'includes/header.php';
include 'config/db.php'; // <-- Make sure this file connects to your database

// Fetch featured books (limit 3)
$books_sql = "SELECT * FROM books ORDER BY created_at DESC LIMIT 3";
$books_result = $conn->query($books_sql);

// Fetch active competitions (limit 2)
$comps_sql = "SELECT * FROM competitions WHERE status = 'active' ORDER BY end_date ASC LIMIT 2";
$comps_result = $conn->query($comps_sql);

function getBookImage($book) {
    if (!empty($book['image_path']) && file_exists('./' . $book['image_path'])) {
        return './' . $book['image_path'];
    } elseif (!empty($book['image_path']) && file_exists($book['image_path'])) {
        return $book['image_path'];
    } else {
        return './assets/images/books/default.jpg';
    }
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold text-white mb-4">Welcome to the Online E-Book System</h1>
                <p class="lead text-white mb-4">
                    Discover thousands of digital books, join writing competitions, and expand your knowledge with our comprehensive e-learning platform.
                </p>
                <div class="hero-buttons">
                    <a href="user/books.php" class="btn btn-light btn-lg me-3">
                        <i class="bi bi-book me-2"></i>Browse Books
                    </a>
                    <a href="user/register.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-person-plus me-2"></i>Get Started
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="assets/images/hero-books.webp" alt="E-Books Collection" class="img-fluid hero-image">
            </div>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section class="about-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="assets/images/about-illustration.webp" alt="About Our Platform" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <h2 class="section-title mb-4">About Our Platform</h2>
                <p class="text-muted mb-3">
                    Our Online E-Book System is a comprehensive digital platform designed to revolutionize the way readers access and engage with educational content.
                </p>
                <p class="text-muted mb-4">
                    Beyond just books, we host regular writing competitions where aspiring authors can showcase their talent, compete for prizes, and gain recognition.
                </p>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="stat-box">
                            <h3 class="stat-number">
                                <?php
                                $count_books = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc();
                                echo $count_books['total'] ?? 0;
                                ?>+
                            </h3>
                            <p class="stat-label">Books</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <h3 class="stat-number">
                                <?php
                                $count_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc();
                                echo $count_users['total'] ?? 0;
                                ?>+
                            </h3>
                            <p class="stat-label">Users</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <h3 class="stat-number">
                                <?php
                                $count_comps = $conn->query("SELECT COUNT(*) AS total FROM competitions")->fetch_assoc();
                                echo $count_comps['total'] ?? 0;
                                ?>+
                            </h3>
                            <p class="stat-label">Competitions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Books Section -->
<section class="featured-books-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Featured Books</h2>
            <p class="text-muted">Explore our handpicked collection of bestsellers and educational resources</p>
        </div>
        <div class="row">
            <?php if ($books_result->num_rows > 0): ?>
                <?php while ($book = $books_result->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="book-card">
                            <div class="book-image">
                                <img src="<?php echo getBookImage($book); ?>" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                     class="img-fluid"
                                     style="width: 100%; height: 100%; object-fit: cover;"
                                     onerror="this.src='../assets/images/books/default.jpg'">
                                <div class="book-badge"><?= $book['category'] ?></div>
                            </div>
                            <div class="book-content">
                                <span class="book-category"><?= htmlspecialchars($book['category']) ?></span>
                                <h5 class="book-title"><?= htmlspecialchars($book['title']) ?></h5>
                                <p class="book-author">By <?= htmlspecialchars($book['author']) ?></p>
                                <p class="book-description"><?= htmlspecialchars(substr($book['description'], 0, 100)) ?>...</p>
                                <div class="book-footer">
                                    <span class="book-price">$<?= htmlspecialchars($book['price']) ?></span>
                                    <a href="user/book_details.php?id=<?= $book['book_id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted">No books available at the moment.</p>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="user/books.php" class="btn btn-outline-primary btn-lg">
                View All Books <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Competitions Section -->
<section class="competitions-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Ongoing Competitions</h2>
            <p class="text-muted">Showcase your writing talent and win exciting prizes</p>
        </div>
        <div class="row">
            <?php if ($comps_result->num_rows > 0): ?>
                <?php while ($comp = $comps_result->fetch_assoc()): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="competition-card">
                            <div class="competition-header">
                                <span class="competition-type"><?= ucfirst($comp['type']) ?> Competition</span>
                                <span class="competition-status"><?= ucfirst($comp['status']) ?></span>
                            </div>
                            <h4 class="competition-title"><?= htmlspecialchars($comp['title']) ?></h4>
                            <p class="competition-description">
                                <?= htmlspecialchars(substr($comp['description'], 0, 150)) ?>...
                            </p>
                            <div class="competition-details">
                                <div class="detail-item">
                                    <i class="bi bi-calendar-event text-primary"></i>
                                    <span>Ends: <?= date('F j, Y', strtotime($comp['end_date'])) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="bi bi-trophy text-warning"></i>
                                    <span>Prize: <?= htmlspecialchars($comp['prize']) ?></span>
                                </div>
                            </div>
                            <a href="user/competition.php?id=<?= $comp['comp_id'] ?>" class="btn btn-primary w-100 mt-3">
                                <i class="bi bi-pencil-square me-2"></i>Join Competition
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted">No active competitions right now.</p>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="user/competition.php" class="btn btn-outline-primary btn-lg">
                View All Competitions <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
