<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Users";
require_once '../config/db.php';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Check if email already exists
    $check_email = "SELECT user_id FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_email);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error_msg'] = "Email already exists!";
    } else {
        $query = "INSERT INTO users (full_name, email, password, phone, address, registered_at) 
                  VALUES ('$full_name', '$email', '$password', '$phone', '$address', NOW())";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success_msg'] = "User added successfully!";
        } else {
            $_SESSION['error_msg'] = "Error: " . mysqli_error($conn);
        }
    }
    header("Location: manage_users.php");
    exit();
}

// Handle Update User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Check if email already exists for other users
    $check_email = "SELECT user_id FROM users WHERE email = '$email' AND user_id != $user_id";
    $check_result = mysqli_query($conn, $check_email);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error_msg'] = "Email already exists!";
    } else {
        $password_update = "";
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $password_update = ", password = '$password'";
        }
        
        $query = "UPDATE users SET 
                  full_name = '$full_name', 
                  email = '$email', 
                  phone = '$phone', 
                  address = '$address'
                  $password_update
                  WHERE user_id = $user_id";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success_msg'] = "User updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Error: " . mysqli_error($conn);
        }
    }
    header("Location: manage_users.php");
    exit();
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Delete user's orders first (if cascade is not set)
    $delete_orders = "DELETE FROM orders WHERE user_id = $user_id";
    mysqli_query($conn, $delete_orders);
    
    // Delete user's submissions
    $delete_submissions = "DELETE FROM submissions WHERE user_id = $user_id";
    mysqli_query($conn, $delete_submissions);
    
    // Delete user
    $delete_user = "DELETE FROM users WHERE user_id = $user_id";
    
    if (mysqli_query($conn, $delete_user)) {
        $_SESSION['success_msg'] = "User deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting user: " . mysqli_error($conn);
    }
    header("Location: manage_users.php");
    exit();
}

// Filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';
$sort_by = isset($_GET['sort_by']) ? mysqli_real_escape_string($conn, $_GET['sort_by']) : 'registered_at';
$sort_order = isset($_GET['sort_order']) ? mysqli_real_escape_string($conn, $_GET['sort_order']) : 'DESC';

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["1=1"];

if ($search) {
    $where_conditions[] = "(full_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}

if ($date_from) {
    $where_conditions[] = "DATE(registered_at) >= '$date_from'";
}

if ($date_to) {
    $where_conditions[] = "DATE(registered_at) <= '$date_to'";
}

$where_clause = implode(" AND ", $where_conditions);

// Validate sort column
$allowed_sort = ['full_name', 'email', 'registered_at'];
if (!in_array($sort_by, $allowed_sort)) {
    $sort_by = 'registered_at';
}

// Validate sort order
if (!in_array($sort_order, ['ASC', 'DESC'])) {
    $sort_order = 'DESC';
}

// Count total records
$count_query = "SELECT COUNT(*) as total FROM users WHERE $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Fetch users with order count
$query = "SELECT u.*, 
          COUNT(DISTINCT o.order_id) as order_count,
          SUM(CASE WHEN o.status = 'paid' THEN o.total_amount ELSE 0 END) as total_spent
          FROM users u 
          LEFT JOIN orders o ON u.user_id = o.user_id
          WHERE $where_clause 
          GROUP BY u.user_id
          ORDER BY $sort_by $sort_order 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN DATE(registered_at) = CURDATE() THEN 1 END) as today_registrations,
    COUNT(CASE WHEN DATE(registered_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as week_registrations,
    COUNT(CASE WHEN DATE(registered_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as month_registrations
    FROM users";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Fetch user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM users WHERE user_id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_user = mysqli_fetch_assoc($edit_result);
}

include '../includes/header.php';
?>

<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <button class="btn-toggle-sidebar" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="header-title">Manage Users</h1>
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
            
            <!-- Statistics Cards -->
            <div class="users-stats-grid">
                <div class="user-stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="user-stat-card stat-success">
                    <div class="stat-icon">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['today_registrations']); ?></h3>
                        <p>Today's Registrations</p>
                    </div>
                </div>
                
                <div class="user-stat-card stat-info">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['week_registrations']); ?></h3>
                        <p>This Week</p>
                    </div>
                </div>
                
                <div class="user-stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['month_registrations']); ?></h3>
                        <p>This Month</p>
                    </div>
                </div>
            </div>
            
            <!-- Page Header -->
            <div class="page-header-admin">
                <h1><i class="bi bi-people"></i> Users Management</h1>
                <div class="header-actions">
                    <button class="btn-filter" onclick="toggleAddForm()">
                        <i class="bi bi-person-plus"></i> Add New User
                    </button>
                </div>
            </div>
            
            <!-- Add/Edit User Form -->
            <div id="userForm" style="display: <?php echo $edit_user ? 'block' : 'none'; ?>;" class="form-card">
                <div class="form-card-header">
                    <h3><i class="bi bi-person-badge"></i> <?php echo $edit_user ? 'Edit User' : 'Add New User'; ?></h3>
                    <button class="btn-close-form" onclick="toggleAddForm()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="form-card-body">
                    <form method="POST">
                        <?php if ($edit_user): ?>
                            <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-person"></i>
                                    Full Name *
                                </label>
                                <input type="text" name="full_name" class="form-input" 
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['full_name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-envelope"></i>
                                    Email Address *
                                </label>
                                <input type="email" name="email" class="form-input" 
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-telephone"></i>
                                    Phone Number
                                </label>
                                <input type="tel" name="phone" class="form-input" 
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['phone']) : ''; ?>" 
                                       placeholder="+1 (555) 000-0000">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-lock"></i>
                                    Password <?php echo $edit_user ? '(Leave blank to keep current)' : '*'; ?>
                                </label>
                                <input type="password" name="password" class="form-input" 
                                       placeholder="Enter password" <?php echo !$edit_user ? 'required' : ''; ?>>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-geo-alt"></i>
                                Address
                            </label>
                            <textarea name="address" class="form-textarea" rows="3" 
                                      placeholder="Enter full address"><?php echo $edit_user ? htmlspecialchars($edit_user['address']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <?php if ($edit_user): ?>
                                <button type="submit" name="update_user" class="btn-submit">
                                    <i class="bi bi-check-circle"></i> Update User
                                </button>
                                <a href="manage_users.php" class="btn-cancel">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_user" class="btn-submit">
                                    <i class="bi bi-person-plus"></i> Add User
                                </button>
                                <button type="button" class="btn-cancel" onclick="toggleAddForm()">Cancel</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div class="users-filters-section">
                <div class="filters-header">
                    <h3><i class="bi bi-funnel"></i> Filter & Sort Users</h3>
                    <?php if ($search || $date_from || $date_to || $sort_by != 'registered_at' || $sort_order != 'DESC'): ?>
                        <a href="manage_users.php" class="btn-clear-filters">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
                
                <form method="GET" class="users-filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label class="filter-label">Search</label>
                            <div class="search-input-wrapper">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="filter-input" 
                                       placeholder="Name, Email, Phone..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Date From</label>
                            <input type="date" name="date_from" class="filter-input" 
                                   value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Date To</label>
                            <input type="date" name="date_to" class="filter-input" 
                                   value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Sort By</label>
                            <select name="sort_by" class="filter-select">
                                <option value="registered_at" <?php echo $sort_by == 'registered_at' ? 'selected' : ''; ?>>Registration Date</option>
                                <option value="full_name" <?php echo $sort_by == 'full_name' ? 'selected' : ''; ?>>Name</option>
                                <option value="email" <?php echo $sort_by == 'email' ? 'selected' : ''; ?>>Email</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Order</label>
                            <select name="sort_order" class="filter-select">
                                <option value="DESC" <?php echo $sort_order == 'DESC' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="ASC" <?php echo $sort_order == 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" class="btn-filter-submit">
                                <i class="bi bi-search"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="users-table-card">
                <div class="table-header">
                    <h3><i class="bi bi-table"></i> Users List</h3>
                    <span class="record-count"><?php echo number_format($total_records); ?> total records</span>
                </div>
                
                <div class="table-responsive">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Info</th>
                                <th>Contact</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="no-data-row">
                                        <div class="no-data">
                                            <i class="bi bi-inbox"></i>
                                            <p>No users found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <strong class="user-id">#<?php echo $user['user_id']; ?></strong>
                                        </td>
                                        <td>
                                            <div class="user-info-cell">
                                                <div class="user-avatar-small">
                                                    <i class="bi bi-person-circle"></i>
                                                </div>
                                                <div class="user-details">
                                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                    <small><?php echo htmlspecialchars($user['email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-cell">
                                                <?php if ($user['phone']): ?>
                                                    <div class="contact-item">
                                                        <i class="bi bi-telephone"></i>
                                                        <span><?php echo htmlspecialchars($user['phone']); ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No phone</span>
                                                <?php endif; ?>
                                                <?php if ($user['address']): ?>
                                                    <div class="contact-item">
                                                        <i class="bi bi-geo-alt"></i>
                                                        <span><?php echo htmlspecialchars(substr($user['address'], 0, 30)); ?><?php echo strlen($user['address']) > 30 ? '...' : ''; ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="orders-badge">
                                                <i class="bi bi-bag"></i>
                                                <strong><?php echo $user['order_count']; ?></strong>
                                                <?php echo $user['order_count'] == 1 ? 'order' : 'orders'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="spent-amount">$<?php echo number_format($user['total_spent'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <div class="date-cell">
                                                <strong><?php echo date('M j, Y', strtotime($user['registered_at'])); ?></strong>
                                                <small><?php echo date('g:i A', strtotime($user['registered_at'])); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-table-action btn-view" onclick="viewUser(<?php echo $user['user_id']; ?>)" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <a href="?edit=<?php echo $user['user_id']; ?>" class="btn-table-action btn-edit" title="Edit User">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?delete=<?php echo $user['user_id']; ?>" class="btn-table-action btn-delete" 
                                                   onclick="return confirm('Are you sure you want to delete this user? All associated orders and submissions will be deleted.')" title="Delete User">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-wrapper">
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>" class="page-link">
                                    <i class="bi bi-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>" 
                                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>" class="page-link">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- View User Modal -->
<div id="viewUserModal" class="user-modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3><i class="bi bi-person-badge"></i> User Details</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="userDetailsContent">
            <!-- Content loaded via JavaScript -->
        </div>
    </div>
</div>

<script>
document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
});

function toggleAddForm() {
    const form = document.getElementById('userForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
    }
}

function viewUser(userId) {
    const modal = document.getElementById('viewUserModal');
    const content = document.getElementById('userDetailsContent');
    
    // Show modal
    modal.style.display = 'flex';
    content.innerHTML = '<div class="loading"><i class="bi bi-hourglass-split"></i> Loading...</div>';
    
    // Fetch user details
    fetch(`get_user_details.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
            } else {
                content.innerHTML = '<div class="error"><i class="bi bi-exclamation-triangle"></i> Failed to load user details</div>';
            }
        })
        .catch(error => {
            content.innerHTML = '<div class="error"><i class="bi bi-exclamation-triangle"></i> Error loading user details</div>';
        });
}

function closeModal() {
    document.getElementById('viewUserModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('viewUserModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include '../includes/footer.php'; ?>