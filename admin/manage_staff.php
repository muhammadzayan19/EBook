<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Staff Management";
require_once '../config/db.php';

// Check if user is super admin
$admin_id = $_SESSION['admin_id'];
$admin_query = "SELECT role FROM admin_users WHERE admin_id = $admin_id";
$admin_result = mysqli_query($conn, $admin_query);
$admin_data = mysqli_fetch_assoc($admin_result);
$is_super_admin = ($admin_data['role'] === 'super_admin');

if (!$is_super_admin) {
    $_SESSION['error_msg'] = "You don't have permission to access this page!";
    header("Location: index.php");
    exit();
}

$success_msg = '';
$error_msg = '';

// Handle Add Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Validation
    if (empty($name) || empty($email) || empty($username) || empty($password)) {
        $error_msg = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters long!";
    } else {
        // Check if email or username already exists
        $check_query = "SELECT * FROM admin_users WHERE email = '$email' OR username = '$username'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_msg = "Email or username already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO admin_users (name, email, username, password, role, created_at) 
                      VALUES ('$name', '$email', '$username', '$hashed_password', '$role', NOW())";
            
            if (mysqli_query($conn, $query)) {
                $success_msg = "Staff member added successfully!";
                header("Location: manage_staff.php?success=1");
                exit();
            } else {
                $error_msg = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Handle Update Admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_admin'])) {
    $admin_user_id = intval($_POST['admin_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = $_POST['password'];
    
    // Check if email/username is unique (excluding current admin)
    $check_query = "SELECT * FROM admin_users WHERE (email = '$email' OR username = '$username') AND admin_id != $admin_user_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error_msg = "Email or username already exists!";
    } else {
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $error_msg = "Password must be at least 6 characters long!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $query = "UPDATE admin_users SET name='$name', email='$email', username='$username', role='$role', password='$hashed_password' 
                          WHERE admin_id=$admin_user_id";
            }
        } else {
            $query = "UPDATE admin_users SET name='$name', email='$email', username='$username', role='$role' 
                      WHERE admin_id=$admin_user_id";
        }
        
        if (isset($query) && mysqli_query($conn, $query)) {
            $success_msg = "Staff member updated successfully!";
            header("Location: manage_staff.php?updated=1");
            exit();
        } else {
            if (!isset($error_msg)) {
                $error_msg = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Handle Delete Admin
if (isset($_GET['delete'])) {
    $admin_user_id = intval($_GET['delete']);
    
    // Prevent super admin from deleting themselves
    if ($admin_user_id == $admin_id) {
        $error_msg = "You cannot delete your own admin account!";
    } else {
        $query = "DELETE FROM admin_users WHERE admin_id = $admin_user_id";
        
        if (mysqli_query($conn, $query)) {
            $success_msg = "Staff member deleted successfully!";
            header("Location: manage_staff.php?deleted=1");
            exit();
        } else {
            $error_msg = "Error: " . mysqli_error($conn);
        }
    }
}

// Fetch all admins
$admins_query = "SELECT * FROM admin_users ORDER BY created_at DESC";
$admins_result = mysqli_query($conn, $admins_query);
$admins = [];
while ($row = mysqli_fetch_assoc($admins_result)) {
    $admins[] = $row;
}

// Fetch admin for editing
$edit_admin = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM admin_users WHERE admin_id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_admin = mysqli_fetch_assoc($edit_result);
}

if (isset($_GET['success'])) $success_msg = "Staff member added successfully!";
if (isset($_GET['updated'])) $success_msg = "Staff member updated successfully!";
if (isset($_GET['deleted'])) $success_msg = "Staff member deleted successfully!";

include '../includes/admin_header.php';
?>

<div class="admin-wrapper">
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <button class="btn-toggle-sidebar" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="header-title">Staff Management</h1>
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
                <h1><i class="bi bi-shield-lock"></i> Staff Management</h1>
                <div class="header-actions">
                    <button class="btn-filter" onclick="toggleAddForm()">
                        <i class="bi bi-plus-circle"></i> Add New Staff Member
                    </button>
                </div>
            </div>
            
            <!-- Add/Edit Admin Form -->
            <div id="adminForm" style="display: <?php echo $edit_admin ? 'block' : 'none'; ?>;" class="form-card">
                <div class="form-card-header">
                    <h3><i class="bi bi-person-check"></i> <?php echo $edit_admin ? 'Edit Staff Member' : 'Add New Staff Member'; ?></h3>
                    <button class="btn-close-form" onclick="toggleAddForm()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="form-card-body">
                    <form method="POST">
                        <?php if ($edit_admin): ?>
                            <input type="hidden" name="admin_id" value="<?php echo $edit_admin['admin_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-person"></i> Full Name *</label>
                                <input type="text" name="name" class="form-input" 
                                       value="<?php echo $edit_admin ? htmlspecialchars($edit_admin['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-envelope"></i> Email Address *</label>
                                <input type="email" name="email" class="form-input" 
                                       value="<?php echo $edit_admin ? htmlspecialchars($edit_admin['email']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-person-badge"></i> Username *</label>
                                <input type="text" name="username" class="form-input" 
                                       value="<?php echo $edit_admin ? htmlspecialchars($edit_admin['username']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-shield"></i> Staff Role *</label>
                                <select name="role" class="form-select" required>
                                    <option value="">Select Role</option>
                                    <option value="admin" <?php echo ($edit_admin && $edit_admin['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="moderator" <?php echo ($edit_admin && $edit_admin['role'] == 'moderator') ? 'selected' : ''; ?>>Moderator</option>
                                    <option value="editor" <?php echo ($edit_admin && $edit_admin['role'] == 'editor') ? 'selected' : ''; ?>>Editor</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-lock"></i> Password <?php echo $edit_admin ? '(Leave blank to keep current)' : '*'; ?></label>
                                <input type="password" name="password" class="form-input" 
                                       <?php echo !$edit_admin ? 'required' : ''; ?> 
                                       placeholder="<?php echo $edit_admin ? 'Leave blank to keep current password' : 'Enter password'; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-lock-check"></i> Confirm Password <?php echo $edit_admin ? '' : '*'; ?></label>
                                <input type="password" name="confirm_password" class="form-input" 
                                       <?php echo !$edit_admin ? 'required' : ''; ?>
                                       placeholder="<?php echo $edit_admin ? 'Leave blank to keep current password' : 'Confirm password'; ?>">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <?php if ($edit_admin): ?>
                                <button type="submit" name="update_admin" class="btn-submit">
                                    <i class="bi bi-check-circle"></i> Update Staff Member
                                </button>
                                <a href="manage_staff.php" class="btn-cancel">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_admin" class="btn-submit">
                                    <i class="bi bi-plus-circle"></i> Add Staff Member
                                </button>
                                <button type="button" class="btn-cancel" onclick="toggleAddForm()">Cancel</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Admin Statistics -->
            <div class="orders-stats-grid">
                <div class="order-stat-card stat-primary">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($admins); ?></h3>
                        <p>Total Staff Members</p>
                    </div>
                </div>
                
                <div class="order-stat-card stat-success">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($admins, function($a) { return $a['role'] === 'admin'; })); ?></h3>
                        <p>Admin Users</p>
                    </div>
                </div>
                
                <div class="order-stat-card stat-warning">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($admins, function($a) { return $a['role'] === 'moderator'; })); ?></h3>
                        <p>Moderators</p>
                    </div>
                </div>
                
                <div class="order-stat-card stat-info">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($admins, function($a) { return $a['role'] === 'editor'; })); ?></h3>
                        <p>Editors</p>
                    </div>
                </div>
            </div>
            
            <!-- Admins List -->
            <div class="orders-table-card">
                <div class="table-header">
                    <h3><i class="bi bi-list-check"></i> All Staff Members</h3>
                    <span class="record-count"><?php echo count($admins); ?> total records</span>
                </div>
                
                <div class="table-responsive">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($admins)): ?>
                                <tr>
                                    <td colspan="9" class="no-data-row">
                                        <div class="no-data">
                                            <i class="bi bi-inbox"></i>
                                            <p>No staff members found</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $counter = 1; ?>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td>
                                            <div class="customer-cell">
                                                <strong><?php echo htmlspecialchars($admin['name']); ?></strong>
                                                <?php if ($admin['admin_id'] == $admin_id): ?>
                                                    <small class="badge badge-current">Current User</small>
                                                <?php endif; ?>
                                                <?php if ($admin['role'] === 'super_admin'): ?>
                                                    <small class="badge badge-super">Super Admin</small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                        <td>
                                            <span class="order-type-badge type-<?php echo $admin['role']; ?>">
                                                <i class="bi bi-<?php 
                                                    if ($admin['role'] === 'super_admin') echo 'shield-lock';
                                                    elseif ($admin['role'] === 'admin') echo 'shield-check';
                                                    elseif ($admin['role'] === 'moderator') echo 'person-badge';
                                                    else echo 'pencil-square';
                                                ?>"></i>
                                                <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="order-status-badge status-paid">
                                                <i class="bi bi-circle-fill"></i> Active
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-cell">
                                                <strong><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></strong>
                                                <small><?php echo date('h:i A', strtotime($admin['created_at'])); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo !empty($admin['last_login']) ? date('M j, Y H:i', strtotime($admin['last_login'])) : 'Never'; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($admin['role'] !== 'super_admin'): ?>
                                                    <a href="?edit=<?php echo $admin['admin_id']; ?>" 
                                                       class="btn-table-action btn-edit" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if ($admin['admin_id'] != $admin_id): ?>
                                                        <a href="?delete=<?php echo $admin['admin_id']; ?>" 
                                                           class="btn-table-action btn-delete" title="Delete"
                                                           onclick="return confirm('Are you sure you want to delete this staff member?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-super">Protected</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
});

function toggleAddForm() {
    const form = document.getElementById('adminForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        form.style.display = 'none';
    }
}
</script>

<?php include '../includes/admin_footer.php'; ?>