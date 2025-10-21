<?php
// Set page title for header
$page_title = "Home";

// Include header
include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">Welcome to the Online E-Book System</h1>
                    <p class="lead text-white mb-4">Discover thousands of digital books, join writing competitions, and expand your knowledge with our comprehensive e-learning platform.</p>
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
                    <img src="assets/images/hero-books.png" alt="E-Books Collection" class="img-fluid hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="about-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="assets/images/about-illustration.jpg" alt="About Our Platform" class="img-fluid rounded shadow">
                </div>
                <div class="col-lg-6">
                    <h2 class="section-title mb-4">About Our Platform</h2>
                    <p class="text-muted mb-3">
                        Our Online E-Book System is a comprehensive digital platform designed to revolutionize the way readers access and engage with educational content. We provide a seamless experience for purchasing, downloading, and reading e-books across multiple formats.
                    </p>
                    <p class="text-muted mb-4">
                        Beyond just books, we host regular writing competitions where aspiring authors can showcase their talent, compete for prizes, and gain recognition in the literary community.
                    </p>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-box">
                                <h3 class="stat-number">500+</h3>
                                <p class="stat-label">Books</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box">
                                <h3 class="stat-number">1000+</h3>
                                <p class="stat-label">Users</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box">
                                <h3 class="stat-number">50+</h3>
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
                <!-- Book 1 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="book-card">
                        <div class="book-image">
                            <img src="assets/images/books/book1.jpg" alt="Advanced PHP Programming" class="img-fluid"
                                 onerror="this.src='https://via.placeholder.com/300x400/004aad/ffffff?text=Advanced+PHP'">
                            <div class="book-badge">Bestseller</div>
                        </div>
                        <div class="book-content">
                            <span class="book-category">Programming</span>
                            <h5 class="book-title">Advanced PHP Programming</h5>
                            <p class="book-author">By John Smith</p>
                            <p class="book-description">Master modern PHP development with practical examples and best practices.</p>
                            <div class="book-footer">
                                <span class="book-price">$29.99</span>
                                <a href="user/book_details.php?id=1" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Book 2 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="book-card">
                        <div class="book-image">
                            <img src="assets/images/books/book2.jpg" alt="Database Design Fundamentals" class="img-fluid"
                                 onerror="this.src='https://via.placeholder.com/300x400/007bff/ffffff?text=Database+Design'">
                            <div class="book-badge badge-new">New Release</div>
                        </div>
                        <div class="book-content">
                            <span class="book-category">Database</span>
                            <h5 class="book-title">Database Design Fundamentals</h5>
                            <p class="book-author">By Sarah Johnson</p>
                            <p class="book-description">Learn to design efficient and scalable database systems from scratch.</p>
                            <div class="book-footer">
                                <span class="book-price">$34.99</span>
                                <a href="user/book_details.php?id=2" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Book 3 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="book-card">
                        <div class="book-image">
                            <img src="assets/images/books/book3.jpg" alt="Web Development Complete Guide" class="img-fluid"
                                 onerror="this.src='https://via.placeholder.com/300x400/ffc107/333333?text=Web+Development'">
                            <div class="book-badge badge-featured">Featured</div>
                        </div>
                        <div class="book-content">
                            <span class="book-category">Web Design</span>
                            <h5 class="book-title">Web Development Complete Guide</h5>
                            <p class="book-author">By Michael Brown</p>
                            <p class="book-description">A comprehensive guide to full-stack web development for beginners.</p>
                            <div class="book-footer">
                                <span class="book-price">$39.99</span>
                                <a href="user/book_details.php?id=3" class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
                <!-- Competition 1 -->
                <div class="col-lg-6 mb-4">
                    <div class="competition-card">
                        <div class="competition-header">
                            <span class="competition-type">Essay Competition</span>
                            <span class="competition-status">Active</span>
                        </div>
                        <h4 class="competition-title">The Future of Technology</h4>
                        <p class="competition-description">
                            Write a compelling essay about how technology will shape our future. Express your thoughts on AI, automation, and digital transformation.
                        </p>
                        <div class="competition-details">
                            <div class="detail-item">
                                <i class="bi bi-calendar-event text-primary"></i>
                                <span>Ends: December 31, 2025</span>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-trophy text-warning"></i>
                                <span>Prize: $500</span>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-clock text-info"></i>
                                <span>Duration: 3 Hours</span>
                            </div>
                        </div>
                        <a href="user/competition.php?id=1" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-pencil-square me-2"></i>Join Competition
                        </a>
                    </div>
                </div>

                <!-- Competition 2 -->
                <div class="col-lg-6 mb-4">
                    <div class="competition-card">
                        <div class="competition-header">
                            <span class="competition-type competition-type-story">Story Competition</span>
                            <span class="competition-status">Active</span>
                        </div>
                        <h4 class="competition-title">Tales of Adventure</h4>
                        <p class="competition-description">
                            Craft an exciting short story about an unforgettable adventure. Let your imagination run wild and create memorable characters.
                        </p>
                        <div class="competition-details">
                            <div class="detail-item">
                                <i class="bi bi-calendar-event text-primary"></i>
                                <span>Ends: January 15, 2026</span>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-trophy text-warning"></i>
                                <span>Prize: $750</span>
                            </div>
                            <div class="detail-item">
                                <i class="bi bi-clock text-info"></i>
                                <span>Duration: 3 Hours</span>
                            </div>
                        </div>
                        <a href="user/competition.php?id=2" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-pencil-square me-2"></i>Join Competition
                        </a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="user/competition.php" class="btn btn-outline-primary btn-lg">
                    View All Competitions <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

<?php
// Include footer
include 'includes/footer.php';
?>