<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Admin Settings";

require_once '../config/db.php';

// Helper function to get settings
function getSetting($conn, $key, $default = '') {
    $key = mysqli_real_escape_string($conn, $key);
    $query = "SELECT setting_value FROM settings WHERE setting_key = '$key'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['setting_value'];
    }
    return $default;
}

// Get current admin info
$current_username = $_SESSION['admin_username'];
$query = "SELECT * FROM admin_users WHERE username = '$current_username'";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

// Get statistics
$stats = [];
$query = "SELECT COUNT(*) as total FROM books";
$result = mysqli_query($conn, $query);
$stats['total_books'] = mysqli_fetch_assoc($result)['total'];

$query = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($conn, $query);
$stats['total_users'] = mysqli_fetch_assoc($result)['total'];

$query = "SELECT COUNT(*) as total FROM orders";
$result = mysqli_query($conn, $query);
$stats['total_orders'] = mysqli_fetch_assoc($result)['total'];

$query = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
$result = mysqli_query($conn, $query);
$stats['pending_orders'] = mysqli_fetch_assoc($result)['total'];

// Get all settings
$settings = [
    'site_name' => getSetting($conn, 'site_name', 'Online E-Book System'),
    'site_email' => getSetting($conn, 'site_email', 'info@ebookstore.com'),
    'site_phone' => getSetting($conn, 'site_phone', '+1 234 567 8900'),
    'site_address' => getSetting($conn, 'site_address', '123 Book Street, City, Country'),
    'site_description' => getSetting($conn, 'site_description', 'Welcome to our online e-book store.'),
    'admin_email' => getSetting($conn, 'admin_email', 'admin@ebookstore.com'),
    'smtp_host' => getSetting($conn, 'smtp_host', 'smtp.gmail.com'),
    'smtp_port' => getSetting($conn, 'smtp_port', '587'),
    'smtp_username' => getSetting($conn, 'smtp_username', 'noreply@ebookstore.com'),
    'email_notifications' => getSetting($conn, 'email_notifications', '1'),
    'email_users' => getSetting($conn, 'email_users', '1'),
    'payment_mode' => getSetting($conn, 'payment_mode', 'test'),
    'payment_gateway' => getSetting($conn, 'payment_gateway', 'stripe'),
    'stripe_publishable' => getSetting($conn, 'stripe_publishable', ''),
    'stripe_secret' => getSetting($conn, 'stripe_secret', ''),
    'auto_approve' => getSetting($conn, 'auto_approve', '1')
];

include '../includes/admin_header.php';
?>

<!-- Admin Settings -->
<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <!-- Admin Main Content -->
    <main class="admin-main">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="header-left">
                <button class="btn-toggle-sidebar" id="toggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="header-title">Settings</h1>
            </div>
            <div class="header-right">
                <div class="header-date">
                    <i class="bi bi-calendar-event"></i>
                    <span><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>
        </header>
        
        <!-- Settings Content -->
        <div class="admin-content">
            <!-- Alert Container (for dynamic messages) -->
            <div id="alert-container"></div>
            
            <!-- Settings Navigation Tabs -->
            <div class="settings-nav">
                <button class="settings-nav-btn active" data-tab="general">
                    <i class="bi bi-gear-fill"></i>
                    <span>General</span>
                </button>
                <button class="settings-nav-btn" data-tab="profile">
                    <i class="bi bi-person-circle"></i>
                    <span>Profile</span>
                </button>
                <button class="settings-nav-btn" data-tab="security">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Security</span>
                </button>
                <button class="settings-nav-btn" data-tab="email">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Email</span>
                </button>
                <button class="settings-nav-btn" data-tab="payment">
                    <i class="bi bi-credit-card-fill"></i>
                    <span>Payment</span>
                </button>
                <button class="settings-nav-btn" data-tab="system">
                    <i class="bi bi-cpu-fill"></i>
                    <span>System</span>
                </button>
            </div>
            
            <!-- General Settings Tab -->
            <div class="settings-tab active" id="general-tab">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h3><i class="bi bi-gear-fill"></i> General Settings</h3>
                        <p>Manage your site's general information and configuration</p>
                    </div>
                    <div class="settings-card-body">
                        <form id="general-form" class="settings-form">
                            <input type="hidden" name="action" value="update_general">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-building"></i>
                                        Site Name
                                    </label>
                                    <input type="text" name="site_name" class="form-input" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                                    <small class="text-muted">This will appear as your website name</small>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-envelope"></i>
                                        Site Email
                                    </label>
                                    <input type="email" name="site_email" class="form-input" value="<?php echo htmlspecialchars($settings['site_email']); ?>" required>
                                    <small class="text-muted">Primary contact email address</small>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-telephone"></i>
                                        Site Phone
                                    </label>
                                    <input type="tel" name="site_phone" class="form-input" value="<?php echo htmlspecialchars($settings['site_phone']); ?>" required>
                                    <small class="text-muted">Contact phone number</small>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-geo-alt"></i>
                                        Site Address
                                    </label>
                                    <input type="text" name="site_address" class="form-input" value="<?php echo htmlspecialchars($settings['site_address']); ?>" required>
                                    <small class="text-muted">Physical business address</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-file-text"></i>
                                    Site Description
                                </label>
                                <textarea name="site_description" class="form-textarea" rows="4"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                                <small class="text-muted">Brief description of your website</small>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-save"></i>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Profile Settings Tab -->
            <div class="settings-tab" id="profile-tab">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h3><i class="bi bi-person-circle"></i> Profile Settings</h3>
                        <p>Update your admin profile information</p>
                    </div>
                    <div class="settings-card-body">
                        <div class="profile-section">
                            <div class="profile-avatar-large">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="profile-info-section">
                                <h4><?php echo htmlspecialchars($admin['username']); ?></h4>
                                <p>Administrator</p>
                                <span class="profile-badge">
                                    <i class="bi bi-shield-check"></i>
                                    Super Admin
                                </span>
                            </div>
                        </div>
                        
                        <form id="profile-form" class="settings-form">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-person"></i>
                                        Username
                                    </label>
                                    <input type="text" name="new_username" class="form-input" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-envelope"></i>
                                        Email Address
                                    </label>
                                    <input type="email" name="new_email" class="form-input" value="<?php echo htmlspecialchars($settings['admin_email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-save"></i>
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Security Settings Tab -->
            <div class="settings-tab" id="security-tab">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h3><i class="bi bi-shield-lock-fill"></i> Security Settings</h3>
                        <p>Manage your password and security preferences</p>
                    </div>
                    <div class="settings-card-body">
                        <form id="security-form" class="settings-form">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-lock"></i>
                                    Current Password
                                </label>
                                <input type="password" name="current_password" class="form-input" required>
                            </div>
                            
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-key"></i>
                                        New Password
                                    </label>
                                    <input type="password" name="new_password" class="form-input" minlength="8" required>
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-key-fill"></i>
                                        Confirm New Password
                                    </label>
                                    <input type="password" name="confirm_password" class="form-input" minlength="8" required>
                                </div>
                            </div>
                            
                            <div class="security-tips">
                                <h5><i class="bi bi-lightbulb"></i> Password Tips</h5>
                                <ul>
                                    <li><i class="bi bi-check-circle"></i> Use at least 8 characters</li>
                                    <li><i class="bi bi-check-circle"></i> Include uppercase and lowercase letters</li>
                                    <li><i class="bi bi-check-circle"></i> Add numbers and special characters</li>
                                    <li><i class="bi bi-check-circle"></i> Avoid common words or patterns</li>
                                </ul>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-shield-check"></i>
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Email Settings Tab -->
            <div class="settings-tab" id="email-tab">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h3><i class="bi bi-envelope-fill"></i> Email Settings</h3>
                        <p>Configure email notifications and SMTP settings</p>
                    </div>
                    <div class="settings-card-body">
                        <form id="email-form" class="settings-form">
                            <input type="hidden" name="action" value="update_email">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-server"></i>
                                        SMTP Host
                                    </label>
                                    <input type="text" name="smtp_host" class="form-input" value="<?php echo htmlspecialchars($settings['smtp_host']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-hdd-network"></i>
                                        SMTP Port
                                    </label>
                                    <input type="number" name="smtp_port" class="form-input" value="<?php echo htmlspecialchars($settings['smtp_port']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-person"></i>
                                        SMTP Username
                                    </label>
                                    <input type="text" name="smtp_username" class="form-input" value="<?php echo htmlspecialchars($settings['smtp_username']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-key"></i>
                                        SMTP Password
                                    </label>
                                    <input type="password" name="smtp_password" class="form-input" placeholder="Leave blank to keep current">
                                </div>
                            </div>
                            
                            <div class="form-check-group">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="email_notifications" <?php echo $settings['email_notifications'] == '1' ? 'checked' : ''; ?>>
                                    <span>Enable email notifications for new orders</span>
                                </label>
                            </div>
                            
                            <div class="form-check-group">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="email_users" <?php echo $settings['email_users'] == '1' ? 'checked' : ''; ?>>
                                    <span>Send welcome emails to new users</span>
                                </label>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-save"></i>
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Payment Settings Tab -->
            <div class="settings-tab" id="payment-tab">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h3><i class="bi bi-credit-card-fill"></i> Payment Settings</h3>
                        <p>Configure payment gateway and transaction settings</p>
                    </div>
                    <div class="settings-card-body">
                        <form id="payment-form" class="settings-form">
                            <input type="hidden" name="action" value="update_payment">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-toggles"></i>
                                        Payment Mode
                                    </label>
                                    <select name="payment_mode" class="form-select">
                                        <option value="test" <?php echo $settings['payment_mode'] == 'test' ? 'selected' : ''; ?>>Test Mode</option>
                                        <option value="live" <?php echo $settings['payment_mode'] == 'live' ? 'selected' : ''; ?>>Live Mode</option>
                                    </select>
                                    <small class="text-muted">Switch between test and live payment modes</small>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-wallet2"></i>
                                        Payment Gateway
                                    </label>
                                    <select name="payment_gateway" class="form-select">
                                        <option value="stripe" <?php echo $settings['payment_gateway'] == 'stripe' ? 'selected' : ''; ?>>Stripe</option>
                                        <option value="paypal" <?php echo $settings['payment_gateway'] == 'paypal' ? 'selected' : ''; ?>>PayPal</option>
                                        <option value="razorpay" <?php echo $settings['payment_gateway'] == 'razorpay' ? 'selected' : ''; ?>>Razorpay</option>
                                        <option value="manual" <?php echo $settings['payment_gateway'] == 'manual' ? 'selected' : ''; ?>>Manual Payment</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="payment-gateway-section">
                                <h5><i class="bi bi-stripe"></i> Stripe Settings</h5>
                                <div class="form-grid-2">
                                    <div class="form-group">
                                        <label class="form-label">Publishable Key</label>
                                        <input type="text" name="stripe_publishable" class="form-input" value="<?php echo htmlspecialchars($settings['stripe_publishable']); ?>" placeholder="pk_test_...">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Secret Key</label>
                                        <input type="password" name="stripe_secret" class="form-input" placeholder="sk_test_... (Leave blank to keep current)">
                                    </div>
                                </div>
                            </div>

                            <div class="payment-gateway-section" style="margin-top: 1.5rem;">
                                <h5><i class="bi bi-star-fill"></i> Subscription Settings</h5>
                                <div class="form-grid-2">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-cash-stack"></i>
                                            Monthly Subscription Price ($)
                                        </label>
                                        <input type="number" step="0.01" name="subscription_price" class="form-input" 
                                            value="<?php echo htmlspecialchars(getSetting($conn, 'monthly_subscription_price', '9.99')); ?>" required>
                                        <small class="text-muted">Monthly fee for subscription access to all eligible books</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check-group">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="auto_approve" <?php echo $settings['auto_approve'] == '1' ? 'checked' : ''; ?>>
                                    <span>Automatically approve paid orders</span>
                                </label>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-save"></i>
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- System Settings Tab -->
            <div class="settings-tab" id="system-tab">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h3><i class="bi bi-cpu-fill"></i> System Information</h3>
                        <p>View system status and maintenance options</p>
                    </div>
                    <div class="settings-card-body">
                        <div class="system-info-grid">
                            <div class="system-info-card">
                                <div class="system-info-icon">
                                    <i class="bi bi-server"></i>
                                </div>
                                <div class="system-info-details">
                                    <h5>Server Info</h5>
                                    <p>PHP Version: <strong><?php echo phpversion(); ?></strong></p>
                                    <p>Server: <strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?></strong></p>
                                </div>
                            </div>
                            
                            <div class="system-info-card">
                                <div class="system-info-icon">
                                    <i class="bi bi-database"></i>
                                </div>
                                <div class="system-info-details">
                                    <h5>Database</h5>
                                    <p>MySQL Version: <strong><?php echo mysqli_get_server_info($conn); ?></strong></p>
                                    <p>Status: <strong class="status-success">Connected</strong></p>
                                </div>
                            </div>
                            
                            <div class="system-info-card">
                                <div class="system-info-icon">
                                    <i class="bi bi-hdd"></i>
                                </div>
                                <div class="system-info-details">
                                    <h5>Storage</h5>
                                    <p>Total Books: <strong><?php echo $stats['total_books']; ?></strong></p>
                                    <p>Total Users: <strong><?php echo $stats['total_users']; ?></strong></p>
                                </div>
                            </div>
                            
                            <div class="system-info-card">
                                <div class="system-info-icon">
                                    <i class="bi bi-speedometer"></i>
                                </div>
                                <div class="system-info-details">
                                    <h5>Performance</h5>
                                    <p>Total Orders: <strong><?php echo $stats['total_orders']; ?></strong></p>
                                    <p>Pending: <strong><?php echo $stats['pending_orders']; ?></strong></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="maintenance-section">
                            <h5><i class="bi bi-tools"></i> Maintenance Actions</h5>
                            <div class="maintenance-actions">
                                <button class="btn-maintenance" data-action="clear_cache">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    Clear Cache
                                </button>
                                <button class="btn-maintenance" data-action="optimize_db">
                                    <i class="bi bi-database-check"></i>
                                    Optimize Database
                                </button>
                                <button class="btn-maintenance" data-action="backup_data">
                                    <i class="bi bi-file-earmark-zip"></i>
                                    Backup Data
                                </button>
                                <button class="btn-maintenance danger" data-action="clear_logs">
                                    <i class="bi bi-trash"></i>
                                    Clear Logs
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- AJAX JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab Navigation
    const navButtons = document.querySelectorAll('.settings-nav-btn');
    const tabs = document.querySelectorAll('.settings-tab');
    
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            navButtons.forEach(btn => btn.classList.remove('active'));
            tabs.forEach(tab => tab.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
    
    // Toggle sidebar
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
    });
    
    // Alert Helper Function
    function showAlert(message, type = 'success') {
        const alertContainer = document.getElementById('alert-container');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'}"></i>
            <span>${message}</span>
        `;
        
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alertDiv);
        
        // Scroll to top to show alert
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }
    
    // Form Submission Handler
    function handleFormSubmit(formId) {
        const form = document.getElementById(formId);
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
            
            // Get form data
            const formData = new FormData(form);
            
            // Send AJAX request
            fetch('save_settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    
                    // If password was changed, clear the form
                    if (formId === 'security-form') {
                        form.reset();
                    }
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', 'danger');
                console.error('Error:', error);
            })
            .finally(() => {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // Initialize all forms
    handleFormSubmit('general-form');
    handleFormSubmit('profile-form');
    handleFormSubmit('security-form');
    handleFormSubmit('email-form');
    handleFormSubmit('payment-form');
    
    // Maintenance Actions
    const maintenanceButtons = document.querySelectorAll('.btn-maintenance');
    maintenanceButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const originalText = this.innerHTML;
            
            // Confirm for destructive actions
            if (action === 'clear_logs' && !confirm('Are you sure you want to clear all logs?')) {
                return;
            }
            
            this.disabled = true;
            this.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing...';
            
            const formData = new FormData();
            formData.append('action', 'maintenance');
            formData.append('maintenance_type', action);
            
            fetch('save_settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', 'danger');
                console.error('Error:', error);
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    });
});
</script>

<!-- Alert Styles -->
<style>
#alert-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 400px;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    font-weight: 600;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    animation: slideIn 0.3s ease-out;
    transition: opacity 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.alert-success {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
}

.alert-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #ffffff;
}

.alert i {
    font-size: 1.25rem;
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<?php include '../includes/admin_footer.php'; ?>