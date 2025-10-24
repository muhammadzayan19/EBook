<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Books";
require_once '../config/db.php';

// Handle Add Book
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $subscription_price = floatval($_POST['subscription_price']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $stock = intval($_POST['stock']);
    $is_free = isset($_POST['is_free']) ? 1 : 0;

    // Base directories
    $book_dir = __DIR__ . '/../uploads/books/';
    $cover_dir = __DIR__ . '/../uploads/book_covers/';
    if (!file_exists($book_dir)) mkdir($book_dir, 0777, true);
    if (!file_exists($cover_dir)) mkdir($cover_dir, 0777, true);

    // Handle PDF file upload
    $file_path = '';
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        $allowed_pdf_types = ['application/pdf'];
        if (!in_array($_FILES['pdf_file']['type'], $allowed_pdf_types)) {
            $_SESSION['error_msg'] = "Invalid file format. Only PDF allowed.";
            header("Location: manage_books.php");
            exit();
        }
        $file_name = uniqid() . '_' . basename($_FILES['pdf_file']['name']);
        move_uploaded_file($_FILES['pdf_file']['tmp_name'], $book_dir . $file_name);
        $file_path = 'uploads/books/' . $file_name; // stored relative in DB
    }

    // Handle book cover image upload
    $image_path = '';
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $file_type = $_FILES['book_image']['type'];
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error_msg'] = "Invalid image format. Only JPG, PNG, and WEBP allowed.";
            header("Location: manage_books.php");
            exit();
        }
        if ($_FILES['book_image']['size'] > 2 * 1024 * 1024) { // 2MB limit
            $_SESSION['error_msg'] = "Image too large. Max 2MB allowed.";
            header("Location: manage_books.php");
            exit();
        }
        $image_name = uniqid() . '_' . basename($_FILES['book_image']['name']);
        move_uploaded_file($_FILES['book_image']['tmp_name'], $cover_dir . $image_name);
        $image_path = 'uploads/book_covers/' . $image_name; // relative path
    }

    $query = "INSERT INTO books 
        (title, author, category, description, price, subscription_price, type, file_path, image_path, stock, is_free)
        VALUES 
        ('$title', '$author', '$category', '$description', $price, $subscription_price, '$type', '$file_path', '$image_path', $stock, $is_free)";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success_msg'] = "Book added successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($conn);
    }
    header("Location: manage_books.php");
    exit();
}

// Handle Update Book
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_book'])) {
    $book_id = intval($_POST['book_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $subscription_price = floatval($_POST['subscription_price']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $stock = intval($_POST['stock']);
    $is_free = isset($_POST['is_free']) ? 1 : 0;

    $file_path_update = "";
    $image_path_update = "";

    // Handle PDF file update
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        $allowed_pdf_types = ['application/pdf'];
        if (!in_array($_FILES['pdf_file']['type'], $allowed_pdf_types)) {
            $_SESSION['error_msg'] = "Invalid file format. Only PDF allowed.";
            header("Location: manage_books.php");
            exit();
        }

        // Delete old PDF
        $old_file_query = "SELECT file_path FROM books WHERE book_id=$book_id";
        $old_file_result = mysqli_query($conn, $old_file_query);
        $old_file_data = mysqli_fetch_assoc($old_file_result);
        if ($old_file_data && $old_file_data['file_path'] && file_exists(__DIR__ . '/../' . $old_file_data['file_path'])) {
            unlink(__DIR__ . '/../' . $old_file_data['file_path']);
        }

        $file_name = uniqid() . '_' . basename($_FILES['pdf_file']['name']);
        move_uploaded_file($_FILES['pdf_file']['tmp_name'], $book_dir . $file_name);
        $file_path_update = ", file_path='uploads/books/$file_name'";
    }

    // Handle book cover image update
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $file_type = $_FILES['book_image']['type'];
        if (in_array($file_type, $allowed_types)) {
            // Delete old image
            $old_image_query = "SELECT image_path FROM books WHERE book_id=$book_id";
            $old_image_result = mysqli_query($conn, $old_image_query);
            $old_image_data = mysqli_fetch_assoc($old_image_result);
            if ($old_image_data && $old_image_data['image_path'] && file_exists(__DIR__ . '/../' . $old_image_data['image_path'])) {
                unlink(__DIR__ . '/../' . $old_image_data['image_path']);
            }

            $image_name = uniqid() . '_' . basename($_FILES['book_image']['name']);
            move_uploaded_file($_FILES['book_image']['tmp_name'], $cover_dir . $image_name);
            $image_path_update = ", image_path='uploads/book_covers/$image_name'";
        }
    }

    $query = "UPDATE books SET 
        title='$title', 
        author='$author', 
        category='$category', 
        description='$description',
        price=$price, 
        subscription_price=$subscription_price, 
        type='$type', 
        stock=$stock, 
        is_free=$is_free
        $file_path_update 
        $image_path_update
        WHERE book_id=$book_id";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success_msg'] = "Book updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($conn);
    }
    header("Location: manage_books.php");
    exit();
}

// Handle Delete Book
if (isset($_GET['delete'])) {
    $book_id = intval($_GET['delete']);

    // Fetch file paths
    $file_query = "SELECT file_path, image_path FROM books WHERE book_id = $book_id";
    $file_result = mysqli_query($conn, $file_query);
    $file_data = mysqli_fetch_assoc($file_result);

    // Delete from database first
    $query = "DELETE FROM books WHERE book_id = $book_id";
    if (mysqli_query($conn, $query)) {

        // Base absolute path (one level up from /admin)
        $base_path = realpath(__DIR__ . '/..') . '/';

        // Delete PDF file
        if (!empty($file_data['file_path'])) {
            $pdf_full = $base_path . $file_data['file_path'];
            if (file_exists($pdf_full)) {
                unlink($pdf_full);
            } else {
                error_log("PDF not found: " . $pdf_full);
            }
        }

        // Delete Image file
        if (!empty($file_data['image_path'])) {
            $img_full = $base_path . $file_data['image_path'];
            if (file_exists($img_full)) {
                unlink($img_full);
            } else {
                error_log("Image not found: " . $img_full);
            }
        }

        $_SESSION['success_msg'] = "Book deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting book: " . mysqli_error($conn);
    }

    header("Location: manage_books.php");
    exit();
}

// Fetch Books
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$type_filter = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';

$query = "SELECT * FROM books WHERE 1=1";
if ($search) {
    $query .= " AND (title LIKE '%$search%' OR author LIKE '%$search%')";
}
if ($category_filter) {
    $query .= " AND category = '$category_filter'";
}
if ($type_filter) {
    $query .= " AND type = '$type_filter'";
}
$query .= " ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);
$books = [];
while ($row = mysqli_fetch_assoc($result)) {
    $books[] = $row;
}

// Get categories for filter
$categories = ['Fiction', 'Non-Fiction', 'Science', 'Technology', 'Business', 'History', 'Biography', 'Other'];

// Fetch book for editing
$edit_book = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM books WHERE book_id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_book = mysqli_fetch_assoc($edit_result);
}

include '../includes/header.php';
?>

<div class="admin-wrapper">
    <!-- Admin Sidebar (same as index.php) -->
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <button class="btn-toggle-sidebar" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="header-title">Manage Books</h1>
            </div>
            <div class="header-right">
                <div class="header-date">
                    <i class="bi bi-calendar-event"></i>
                    <span><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>
        </header>
        
        <div class="admin-content">
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success_msg']; ?>
                </div>
                <?php unset($_SESSION['success_msg']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error_msg']; ?>
                </div>
                <?php unset($_SESSION['error_msg']); ?>
            <?php endif; ?>
            
            <!-- Page Header -->
            <div class="page-header-admin">
                <h1><i class="bi bi-book-fill"></i> Books Management</h1>
                <div class="header-actions">
                    <button class="btn-filter" onclick="toggleAddForm()">
                        <i class="bi bi-plus-circle"></i> Add New Book
                    </button>
                </div>
            </div>
            
            <!-- Add/Edit Book Form -->
            <div id="bookForm" style="display: <?php echo $edit_book ? 'block' : 'none'; ?>;" class="form-card">
                <div class="form-card-header">
                    <h3><i class="bi bi-pencil-square"></i> <?php echo $edit_book ? 'Edit Book' : 'Add New Book'; ?></h3>
                    <button class="btn-close-form" onclick="toggleAddForm()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="form-card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php if ($edit_book): ?>
                            <input type="hidden" name="book_id" value="<?php echo $edit_book['book_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Book Title *</label>
                                <input type="text" name="title" class="form-input" 
                                       value="<?php echo $edit_book ? htmlspecialchars($edit_book['title']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Author *</label>
                                <input type="text" name="author" class="form-input" 
                                       value="<?php echo $edit_book ? htmlspecialchars($edit_book['author']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" <?php echo ($edit_book && $edit_book['category'] == $cat) ? 'selected' : ''; ?>>
                                            <?php echo $cat; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Type *</label>
                                <select name="type" class="form-select" required>
                                    <option value="pdf" <?php echo ($edit_book && $edit_book['type'] == 'pdf') ? 'selected' : ''; ?>>PDF</option>
                                    <option value="cd" <?php echo ($edit_book && $edit_book['type'] == 'cd') ? 'selected' : ''; ?>>CD</option>
                                    <option value="hardcopy" <?php echo ($edit_book && $edit_book['type'] == 'hardcopy') ? 'selected' : ''; ?>>Hard Copy</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Price ($) *</label>
                                <input type="number" step="0.01" name="price" class="form-input" 
                                       value="<?php echo $edit_book ? $edit_book['price'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Subscription Price ($)</label>
                                <input type="number" step="0.01" name="subscription_price" class="form-input" 
                                       value="<?php echo $edit_book ? $edit_book['subscription_price'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Stock *</label>
                                <input type="number" name="stock" class="form-input" 
                                       value="<?php echo $edit_book ? $edit_book['stock'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Book Cover Image</label>
                                <input type="file" name="book_image" class="form-input" accept="image/jpeg,image/jpg,image/png,image/webp" onchange="previewImage(event)">
                                <?php if ($edit_book && $edit_book['image_path']): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Current image:</small>
                                        <img src="<?php echo htmlspecialchars($edit_book['image_path']); ?>" 
                                             alt="Current book cover" 
                                             style="max-width: 150px; display: block; margin-top: 8px; border-radius: 4px; border: 1px solid #ddd;">
                                    </div>
                                <?php endif; ?>
                                <div id="imagePreview" style="margin-top: 10px;"></div>
                                <small class="text-muted">Accepted formats: JPG, PNG, WEBP (Max 5MB)</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">PDF File</label>
                                <input type="file" name="pdf_file" class="form-input" accept=".pdf">
                                <?php if ($edit_book && $edit_book['file_path']): ?>
                                    <small class="text-muted">Current file: <?php echo basename($edit_book['file_path']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-textarea" rows="4" required><?php echo $edit_book ? htmlspecialchars($edit_book['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-check-group">
                            <label class="form-check-label">
                                <input type="checkbox" name="is_free" class="form-check-input" 
                                       <?php echo ($edit_book && $edit_book['is_free']) ? 'checked' : ''; ?>>
                                <span>Make this book free</span>
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <?php if ($edit_book): ?>
                                <button type="submit" name="update_book" class="btn-submit">
                                    <i class="bi bi-check-circle"></i> Update Book
                                </button>
                                <a href="manage_books.php" class="btn-cancel">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_book" class="btn-submit">
                                    <i class="bi bi-plus-circle"></i> Add Book
                                </button>
                                <button type="button" class="btn-cancel" onclick="toggleAddForm()">Cancel</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div class="filters-section">
                <form method="GET" class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <input type="text" name="search" class="filter-input" placeholder="Search by title or author..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Category</label>
                        <select name="category" class="filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo $category_filter == $cat ? 'selected' : ''; ?>>
                                    <?php echo $cat; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Type</label>
                        <select name="type" class="filter-select">
                            <option value="">All Types</option>
                            <option value="pdf" <?php echo $type_filter == 'pdf' ? 'selected' : ''; ?>>PDF</option>
                            <option value="cd" <?php echo $type_filter == 'cd' ? 'selected' : ''; ?>>CD</option>
                            <option value="hardcopy" <?php echo $type_filter == 'hardcopy' ? 'selected' : ''; ?>>Hard Copy</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn-filter">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Books Grid -->
            <div class="books-management-grid">
                <?php if (empty($books)): ?>
                    <div class="no-data">
                        <i class="bi bi-inbox"></i>
                        <p>No books found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <div class="book-card-admin">
                            <div class="book-card-image">
                                <?php if (!empty($book['image_path'])): ?>
                                    <img src="../<?php echo htmlspecialchars($book['image_path']); ?>" 
                                        alt="<?php echo htmlspecialchars($book['title']); ?>"
                                        style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0;">
                                <?php else: ?>
                                    <i class="bi bi-book"></i>
                                <?php endif; ?>
                                <?php if ($book['is_free']): ?>
                                    <span class="book-badge-admin badge-free">FREE</span>
                                <?php elseif ($book['stock'] == 0): ?>
                                    <span class="book-badge-admin badge-outofstock">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="book-card-body">
                                <span class="book-category-tag"><?php echo htmlspecialchars($book['category']); ?></span>
                                <h4 class="book-card-title"><?php echo htmlspecialchars($book['title']); ?></h4>
                                <p class="book-card-author">
                                    <i class="bi bi-person"></i>
                                    <?php echo htmlspecialchars($book['author']); ?>
                                </p>
                                <div class="book-card-meta">
                                    <div class="meta-item-book">
                                        <span class="meta-label">Price</span>
                                        <span class="meta-value price">$<?php echo number_format($book['price'], 2); ?></span>
                                    </div>
                                    <div class="meta-item-book">
                                        <span class="meta-label">Stock</span>
                                        <span class="meta-value <?php echo $book['stock'] < 10 ? 'stock-low' : ''; ?>">
                                            <?php echo $book['stock']; ?>
                                        </span>
                                    </div>
                                    <div class="meta-item-book">
                                        <span class="meta-label">Type</span>
                                        <span class="meta-value"><?php echo strtoupper($book['type']); ?></span>
                                    </div>
                                </div>
                                <div class="book-card-footer">
                                    <a href="?edit=<?php echo $book['book_id']; ?>" class="btn-action btn-edit">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="?delete=<?php echo $book['book_id']; ?>" class="btn-action btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this book?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
});

function toggleAddForm() {
    const form = document.getElementById('bookForm');
    const imagePreview = document.getElementById('imagePreview');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
        imagePreview.innerHTML = '';
    }
}

function previewImage(event) {
    const preview = document.getElementById('imagePreview');
    const file = event.target.files[0];
    
    if (file) {
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            event.target.value = '';
            preview.innerHTML = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div style="margin-top: 10px;">
                    <p style="margin-bottom: 5px; font-size: 14px; color: #666;">Preview:</p>
                    <img src="${e.target.result}" 
                         alt="Preview" 
                         style="max-width: 200px; border-radius: 4px; border: 1px solid #ddd;">
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}
</script>

<?php include '../includes/footer.php'; ?>