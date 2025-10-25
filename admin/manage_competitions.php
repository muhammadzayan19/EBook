<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Competitions";
$success_msg = '';
if (isset($_GET['success'])) $success_msg = "Competition added successfully!";
if (isset($_GET['updated'])) $success_msg = "Competition updated successfully!";
require_once '../config/db.php';

$conn->query("UPDATE competitions SET status = 'closed' WHERE end_date < NOW() AND status = 'active'");

// Handle Add Competition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_competition'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $topic = mysqli_real_escape_string($conn, $_POST['topic']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $prize = mysqli_real_escape_string($conn, $_POST['prize']);
    $status = isset($_POST['status']) && !empty($_POST['status']) 
    ? mysqli_real_escape_string($conn, $_POST['status']) 
    : 'active';
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $query = "INSERT INTO competitions (title, type, topic, start_date, end_date, prize, status, description) 
              VALUES ('$title', '$type', '$topic', '$start_date', '$end_date', '$prize', '$status', '$description')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: manage_competitions.php?success=1");
        exit();
    } else {
        $error_msg = "Error: " . mysqli_error($conn);
    }
}

// Handle Update Competition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_competition'])) {
    $comp_id = intval($_POST['comp_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $topic = mysqli_real_escape_string($conn, $_POST['topic']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $prize = mysqli_real_escape_string($conn, $_POST['prize']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $query = "UPDATE competitions SET title='$title', type='$type', topic='$topic', start_date='$start_date', 
              end_date='$end_date', prize='$prize', status='$status', description='$description' 
              WHERE comp_id=$comp_id";
    
    if (mysqli_query($conn, $query)) {
        header("Location: manage_competitions.php?updated=1");
        exit();
    } else {
        $error_msg = "Error: " . mysqli_error($conn);
    }
}

// Handle Delete Competition
if (isset($_GET['delete'])) {
    $comp_id = intval($_GET['delete']);
    $query = "DELETE FROM competitions WHERE comp_id = $comp_id";
    
    if (mysqli_query($conn, $query)) {
        $success_msg = "Competition deleted successfully!";
    } else {
        $error_msg = "Error: " . mysqli_error($conn);
    }
}

// Fetch Competitions
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$type_filter = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM submissions WHERE comp_id = c.comp_id) as submission_count,
          (SELECT COUNT(*) FROM winners WHERE comp_id = c.comp_id) as winner_count
          FROM competitions c WHERE 1=1";

if ($search) {
    $query .= " AND (title LIKE '%$search%' OR topic LIKE '%$search%')";
}
if ($type_filter) {
    $query .= " AND type = '$type_filter'";
}
if ($status_filter) {
    $query .= " AND status = '$status_filter'";
}
$query .= " ORDER BY start_date DESC";

$result = mysqli_query($conn, $query);
$competitions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $competitions[] = $row;
}

// Fetch competition for editing
$edit_competition = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "SELECT * FROM competitions WHERE comp_id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_competition = mysqli_fetch_assoc($edit_result);
}

include '../includes/admin_header.php';
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
                <h1 class="header-title">Manage Competitions</h1>
            </div>
            <div class="header-right">
                <div class="header-date">
                    <i class="bi bi-calendar-event"></i>
                    <span><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>
        </header>
        
        <div class="admin-content">
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>
            
            <!-- Page Header -->
            <div class="page-header-admin">
                <h1><i class="bi bi-trophy-fill"></i> Competitions Management</h1>
                <div class="header-actions">
                    <button class="btn-filter" onclick="toggleAddForm()">
                        <i class="bi bi-plus-circle"></i> Add New Competition
                    </button>
                </div>
            </div>
            
            <!-- Add/Edit Competition Form -->
            <div id="competitionForm" style="display: <?php echo $edit_competition ? 'block' : 'none'; ?>;" class="form-card">
                <div class="form-card-header">
                    <h3><i class="bi bi-pencil-square"></i> <?php echo $edit_competition ? 'Edit Competition' : 'Add New Competition'; ?></h3>
                    <button class="btn-close-form" onclick="toggleAddForm()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="form-card-body">
                    <form method="POST">
                        <?php if ($edit_competition): ?>
                            <input type="hidden" name="comp_id" value="<?php echo $edit_competition['comp_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Competition Title *</label>
                                <input type="text" name="title" class="form-input" 
                                       value="<?php echo $edit_competition ? htmlspecialchars($edit_competition['title']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Type *</label>
                                <select name="type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="essay" <?php echo ($edit_competition && $edit_competition['type'] == 'essay') ? 'selected' : ''; ?>>Essay</option>
                                    <option value="story" <?php echo ($edit_competition && $edit_competition['type'] == 'story') ? 'selected' : ''; ?>>Story</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Start Date *</label>
                                <input type="datetime-local" name="start_date" class="form-input" 
                                       value="<?php echo $edit_competition ? date('Y-m-d\TH:i', strtotime($edit_competition['start_date'])) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">End Date *</label>
                                <input type="datetime-local" name="end_date" class="form-input" 
                                       value="<?php echo $edit_competition ? date('Y-m-d\TH:i', strtotime($edit_competition['end_date'])) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Prize *</label>
                                <input type="text" name="prize" class="form-input" 
                                       value="<?php echo $edit_competition ? htmlspecialchars($edit_competition['prize']) : ''; ?>" 
                                       placeholder="e.g., $500 Cash Prize" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="active" <?php echo ($edit_competition && $edit_competition['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="upcoming" <?php echo ($edit_competition && $edit_competition['status'] == 'upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                                    <option value="closed" <?php echo ($edit_competition && $edit_competition['status'] == 'closed') ? 'selected' : ''; ?>>Closed</option>
                                    <option value="completed" <?php echo ($edit_competition && $edit_competition['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Topic/Description *</label>
                            <textarea name="topic" class="form-textarea" rows="3" required><?php echo $edit_competition ? htmlspecialchars($edit_competition['topic']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Additional Description</label>
                            <textarea name="description" class="form-textarea" rows="4"><?php echo $edit_competition ? htmlspecialchars($edit_competition['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <?php if ($edit_competition): ?>
                                <button type="submit" name="update_competition" class="btn-submit">
                                    <i class="bi bi-check-circle"></i> Update Competition
                                </button>
                                <a href="manage_competitions.php" class="btn-cancel">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_competition" class="btn-submit">
                                    <i class="bi bi-plus-circle"></i> Add Competition
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
                        <input type="text" name="search" class="filter-input" placeholder="Search by title or topic..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Type</label>
                        <select name="type" class="filter-select">
                            <option value="">All Types</option>
                            <option value="essay" <?php echo $type_filter == 'essay' ? 'selected' : ''; ?>>Essay</option>
                            <option value="story" <?php echo $type_filter == 'story' ? 'selected' : ''; ?>>Story</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select name="status" class="filter-select">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="upcoming" <?php echo $status_filter == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn-filter">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Competitions Grid -->
            <div class="competitions-management-grid">
                <?php if (empty($competitions)): ?>
                    <div class="no-data">
                        <i class="bi bi-inbox"></i>
                        <p>No competitions found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($competitions as $comp): ?>
                        <div class="competition-card-admin">
                            <div class="competition-card-header-admin">
                                <span class="comp-type-badge comp-type-<?php echo $comp['type']; ?>">
                                    <i class="bi bi-<?php echo $comp['type'] == 'essay' ? 'file-text' : 'book'; ?>"></i>
                                    <?php echo ucfirst($comp['type']); ?>
                                </span>
                                <span class="comp-status-badge status-<?php echo $comp['status']; ?>">
                                    <?php echo ucfirst($comp['status']); ?>
                                </span>
                            </div>
                            
                            <div class="competition-card-body-admin">
                                <h3 class="competition-card-title-admin"><?php echo htmlspecialchars($comp['title']); ?></h3>
                                <p class="competition-card-topic"><?php echo htmlspecialchars(substr($comp['topic'], 0, 120)); ?>...</p>
                                
                                <div class="competition-card-meta-admin">
                                    <div class="meta-item-comp">
                                        <i class="bi bi-calendar-event"></i>
                                        <div>
                                            <small>Start Date</small>
                                            <strong><?php echo date('M j, Y', strtotime($comp['start_date'])); ?></strong>
                                        </div>
                                    </div>
                                    <div class="meta-item-comp">
                                        <i class="bi bi-calendar-check"></i>
                                        <div>
                                            <small>End Date</small>
                                            <strong><?php echo date('M j, Y', strtotime($comp['end_date'])); ?></strong>
                                        </div>
                                    </div>
                                    <div class="meta-item-comp">
                                        <i class="bi bi-trophy"></i>
                                        <div>
                                            <small>Prize</small>
                                            <strong><?php echo htmlspecialchars($comp['prize']); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="competition-stats">
                                    <div class="stat-item-comp">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <span><?php echo $comp['submission_count']; ?> Submissions</span>
                                    </div>
                                    <div class="stat-item-comp">
                                        <i class="bi bi-award"></i>
                                        <span><?php echo $comp['winner_count']; ?> Winners</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="competition-card-footer-admin">
                                <a href="?edit=<?php echo $comp['comp_id']; ?>" class="btn-action btn-edit">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="winners.php?comp_id=<?php echo $comp['comp_id']; ?>" class="btn-action btn-view">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="?delete=<?php echo $comp['comp_id']; ?>" class="btn-action btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this competition?')">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
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
    const form = document.getElementById('competitionForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
    if (form.style.display === 'block') {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<?php include '../includes/admin_footer.php'; ?>