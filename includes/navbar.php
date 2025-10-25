<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

function isActive($page) {
    global $current_page;
    return ($current_page === $page) ? 'active' : '';
}

$path_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$in_subdirectory = (in_array($current_page, [
    'login.php', 'register.php', 'books.php', 'my_orders.php', 'my_submissions.php', 'my_books.php',
    'book_details.php', 'order.php', 'competition.php',
    'upload_essay.php', 'profile.php'
]) || strpos($_SERVER['REQUEST_URI'], '/user/') !== false) ? '../' : '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $in_subdirectory; ?>index.php">
            <i class="bi bi-book-half me-2"></i>E-Book System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('index.php'); ?>" href="<?php echo $in_subdirectory; ?>index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('about.php'); ?>" href="<?php echo $in_subdirectory; ?>about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('books.php'); ?>" href="<?php echo $in_subdirectory; ?>user/books.php">Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('competition.php'); ?>" href="<?php echo $in_subdirectory; ?>user/competition.php">Competitions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActive('contact.php'); ?>" href="<?php echo $in_subdirectory; ?>contact.php">Contact</a>
                </li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo isActive('profile.php'); ?>" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> 
                            <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Profile'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo $in_subdirectory; ?>user/profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo $in_subdirectory; ?>user/my_books.php"><i class="bi bi-book me-2"></i>My Books</a></li>
                            <li><a class="dropdown-item" href="<?php echo $in_subdirectory; ?>user/my_orders.php"><i class="bi bi-bag me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item" href="<?php echo $in_subdirectory; ?>user/my_submissions.php"><i class="bi bi-file-earmark-text me-2"></i>My Submissions</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $in_subdirectory; ?>user/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link btn-login <?php echo isActive('login.php'); ?>" href="<?php echo $in_subdirectory; ?>user/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-register <?php echo isActive('register.php'); ?>" href="<?php echo $in_subdirectory; ?>user/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>