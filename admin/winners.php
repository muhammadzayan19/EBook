<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "Winners Management";
require_once '../config/db.php';

// Get competition ID from URL
$comp_id = isset($_GET['comp_id']) ? intval($_GET['comp_id']) : 0;

// Fetch competition details
$comp_query = "SELECT * FROM competitions WHERE comp_id = $comp_id";
$comp_result = mysqli_query($conn, $comp_query);
$competition = mysqli_fetch_assoc($comp_result);

if (!$competition) {
    $_SESSION['error_msg'] = "Competition not found!";
    header("Location: manage_competitions.php");
    exit();
}

// Handle Add Winner
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_winner'])) {
    $user_id = intval($_POST['user_id']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $prize_amount = mysqli_real_escape_string($conn, $_POST['prize_amount']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    
    $check_query = "SELECT * FROM winners WHERE comp_id = $comp_id AND user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error_msg'] = "This user is already declared as a winner for this competition!";
    } else {
        $query = "INSERT INTO winners (comp_id, user_id, position, prize, remarks) 
                  VALUES ($comp_id, $user_id, '$position', '$prize_amount', '$remarks')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success_msg'] = "Winner added successfully!";
            header("Location: winners.php?comp_id=$comp_id");
            exit();
        } else {
            $_SESSION['error_msg'] = "Error: " . mysqli_error($conn);
        }
    }
}

// Handle Update Winner
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_winner'])) {
    $winner_id = intval($_POST['winner_id']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $prize_amount = mysqli_real_escape_string($conn, $_POST['prize_amount']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    
    $query = "UPDATE winners SET position='$position', prize='$prize_amount', remarks='$remarks' 
              WHERE winner_id=$winner_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success_msg'] = "Winner updated successfully!";
        header("Location: winners.php?comp_id=$comp_id");
        exit();
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($conn);
    }
}

// Handle Delete Winner
if (isset($_GET['delete_winner'])) {
    $winner_id = intval($_GET['delete_winner']);
    $query = "DELETE FROM winners WHERE winner_id = $winner_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success_msg'] = "Winner removed successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($conn);
    }
    header("Location: winners.php?comp_id=$comp_id");
    exit();
}

// Fetch all submissions for this competition
$submissions_query = "SELECT s.*, u.full_name, u.email 
                      FROM submissions s
                      JOIN users u ON s.user_id = u.user_id
                      WHERE s.comp_id = $comp_id
                      ORDER BY s.submitted_at DESC";
$submissions_result = mysqli_query($conn, $submissions_query);
$submissions = [];
while ($row = mysqli_fetch_assoc($submissions_result)) {
    $submissions[] = $row;
}

// Fetch declared winners
$winners_query = "SELECT w.*, u.full_name, u.email 
                  FROM winners w
                  JOIN users u ON w.user_id = u.user_id
                  WHERE w.comp_id = $comp_id
                  ORDER BY 
                  CASE 
                    WHEN w.position = '1st' THEN 1
                    WHEN w.position = '2nd' THEN 2
                    WHEN w.position = '3rd' THEN 3
                    ELSE 4
                  END,
                  w.announced_at DESC";
$winners_result = mysqli_query($conn, $winners_query);
$winners = [];
while ($row = mysqli_fetch_assoc($winners_result)) {
    $winners[] = $row;
}

// Fetch winner for editing
$edit_winner = null;
if (isset($_GET['edit_winner'])) {
    $edit_id = intval($_GET['edit_winner']);
    $edit_query = "SELECT * FROM winners WHERE winner_id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_winner = mysqli_fetch_assoc($edit_result);
}

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
                <h1 class="header-title">Winners Management</h1>
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
                <div>
                    <h1><i class="bi bi-trophy-fill"></i> Winners Management</h1>
                    <p style="color: var(--admin-secondary); margin-top: 0.5rem; font-size: 0.95rem;">
                        <i class="bi bi-info-circle"></i> Managing winners for: <strong><?php echo htmlspecialchars($competition['title']); ?></strong>
                    </p>
                </div>
                <div class="header-actions">
                    <a href="manage_competitions.php" class="btn-filter" style="background: #f8fafc; color: var(--admin-primary); border: 2px solid #e2e8f0;">
                        <i class="bi bi-arrow-left"></i> Back to Competitions
                    </a>
                </div>
            </div>
            
            <!-- Competition Info Card -->
            <div class="competition-info-card">
                <div class="comp-info-header">
                    <div class="comp-info-title">
                        <i class="bi bi-trophy"></i>
                        <h3><?php echo htmlspecialchars($competition['title']); ?></h3>
                    </div>
                    <span class="comp-status-badge status-<?php echo $competition['status']; ?>">
                        <?php echo ucfirst($competition['status']); ?>
                    </span>
                </div>
                <div class="comp-info-grid">
                    <div class="comp-info-item">
                        <i class="bi bi-file-text"></i>
                        <div>
                            <small>Type</small>
                            <strong><?php echo ucfirst($competition['type']); ?></strong>
                        </div>
                    </div>
                    <div class="comp-info-item">
                        <i class="bi bi-calendar-range"></i>
                        <div>
                            <small>Duration</small>
                            <strong><?php echo date('M j', strtotime($competition['start_date'])); ?> - <?php echo date('M j, Y', strtotime($competition['end_date'])); ?></strong>
                        </div>
                    </div>
                    <div class="comp-info-item">
                        <i class="bi bi-award"></i>
                        <div>
                            <small>Prize</small>
                            <strong><?php echo htmlspecialchars($competition['prize']); ?></strong>
                        </div>
                    </div>
                    <div class="comp-info-item">
                        <i class="bi bi-people"></i>
                        <div>
                            <small>Total Submissions</small>
                            <strong><?php echo count($submissions); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="winners-stats-grid">
                <div class="winner-stat-card stat-primary">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($winners); ?></h3>
                        <p>Total Winners</p>
                    </div>
                </div>
                
                <div class="winner-stat-card stat-success">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($submissions); ?></h3>
                        <p>Total Submissions</p>
                    </div>
                </div>
                
                <div class="winner-stat-card stat-warning">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($submissions) - count($winners); ?></h3>
                        <p>Pending Review</p>
                    </div>
                </div>
                
                <div class="winner-stat-card stat-info">
                    <div class="stat-icon admin-stat-icon">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo htmlspecialchars($competition['prize']); ?></h3>
                        <p>Prize Pool</p>
                    </div>
                </div>
            </div>
            
            <!-- Add/Edit Winner Form -->
            <div id="winnerForm" style="display: <?php echo $edit_winner ? 'block' : 'none'; ?>;" class="form-card">
                <div class="form-card-header">
                    <h3><i class="bi bi-award"></i> <?php echo $edit_winner ? 'Edit Winner' : 'Declare New Winner'; ?></h3>
                    <button class="btn-close-form" onclick="toggleWinnerForm()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="form-card-body">
                    <form method="POST">
                        <?php if ($edit_winner): ?>
                            <input type="hidden" name="winner_id" value="<?php echo $edit_winner['winner_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-person"></i> Select Participant *</label>
                                <select name="user_id" class="form-select" required <?php echo $edit_winner ? 'disabled' : ''; ?>>
                                    <option value="">Choose a participant...</option>
                                    <?php foreach ($submissions as $sub): ?>
                                        <option value="<?php echo $sub['user_id']; ?>" 
                                                <?php echo ($edit_winner && $edit_winner['user_id'] == $sub['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sub['full_name']); ?> (<?php echo htmlspecialchars($sub['email']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($edit_winner): ?>
                                    <input type="hidden" name="user_id" value="<?php echo $edit_winner['user_id']; ?>">
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-trophy"></i> Position *</label>
                                <select name="position" class="form-select" required>
                                    <option value="">Select Position</option>
                                    <option value="1st" <?php echo ($edit_winner && $edit_winner['position'] == '1st') ? 'selected' : ''; ?>>1st Place</option>
                                    <option value="2nd" <?php echo ($edit_winner && $edit_winner['position'] == '2nd') ? 'selected' : ''; ?>>2nd Place</option>
                                    <option value="3rd" <?php echo ($edit_winner && $edit_winner['position'] == '3rd') ? 'selected' : ''; ?>>3rd Place</option>
                                    <option value="Honorable Mention" <?php echo ($edit_winner && $edit_winner['position'] == 'Honorable Mention') ? 'selected' : ''; ?>>Honorable Mention</option>
                                    <option value="Participation" <?php echo ($edit_winner && $edit_winner['position'] == 'Participation') ? 'selected' : ''; ?>>Participation Prize</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label"><i class="bi bi-currency-dollar"></i> Prize Amount *</label>
                                <input type="text" name="prize_amount" class="form-input" 
                                       placeholder="e.g., $500, Book Voucher, Certificate"
                                       value="<?php echo $edit_winner ? htmlspecialchars($edit_winner['prize']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="bi bi-chat-left-text"></i> Remarks / Comments</label>
                            <textarea name="remarks" class="form-textarea" rows="3" 
                                      placeholder="Optional remarks or feedback for the winner..."><?php echo $edit_winner ? htmlspecialchars($edit_winner['remarks']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <?php if ($edit_winner): ?>
                                <button type="submit" name="update_winner" class="btn-submit">
                                    <i class="bi bi-check-circle"></i> Update Winner
                                </button>
                                <a href="winners.php?comp_id=<?php echo $comp_id; ?>" class="btn-cancel">Cancel</a>
                            <?php else: ?>
                                <button type="submit" name="add_winner" class="btn-submit">
                                    <i class="bi bi-award"></i> Declare Winner
                                </button>
                                <button type="button" class="btn-cancel" onclick="toggleWinnerForm()">Cancel</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Winners Section -->
            <div class="winners-section">
                <div class="section-header">
                    <h2><i class="bi bi-trophy-fill"></i> Declared Winners</h2>
                    <button class="btn-filter" onclick="toggleWinnerForm()">
                        <i class="bi bi-plus-circle"></i> Declare Winner
                    </button>
                </div>
                
                <?php if (empty($winners)): ?>
                    <div class="empty-state">
                        <i class="bi bi-trophy"></i>
                        <h3>No Winners Declared Yet</h3>
                        <p>Click "Declare Winner" to announce the competition winners</p>
                    </div>
                <?php else: ?>
                    <div class="winners-grid">
                        <?php foreach ($winners as $winner): ?>
                            <div class="winner-card position-<?php echo strtolower(str_replace(' ', '-', $winner['position'])); ?>">
                                <div class="winner-card-header">
                                    <div class="winner-position-badge">
                                        <i class="bi bi-trophy-fill"></i>
                                        <span><?php echo htmlspecialchars($winner['position']); ?></span>
                                    </div>
                                    <div class="winner-actions">
                                        <a href="?comp_id=<?php echo $comp_id; ?>&edit_winner=<?php echo $winner['winner_id']; ?>" 
                                           class="btn-action-mini btn-edit" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?comp_id=<?php echo $comp_id; ?>&delete_winner=<?php echo $winner['winner_id']; ?>" 
                                           class="btn-action-mini btn-delete" title="Remove"
                                           onclick="return confirm('Are you sure you want to remove this winner?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="winner-card-body">
                                    <div class="winner-avatar">
                                        <i class="bi bi-person-circle"></i>
                                    </div>
                                    <h4 class="winner-name"><?php echo htmlspecialchars($winner['full_name']); ?></h4>
                                    <p class="winner-email"><?php echo htmlspecialchars($winner['email']); ?></p>
                                    
                                    <div class="winner-prize-info">
                                        <i class="bi bi-award"></i>
                                        <span><?php echo htmlspecialchars($winner['prize']); ?></span>
                                    </div>
                                    
                                    <?php if ($winner['remarks']): ?>
                                        <div class="winner-remarks">
                                            <i class="bi bi-chat-quote"></i>
                                            <p><?php echo htmlspecialchars($winner['remarks']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="winner-meta">
                                        <i class="bi bi-calendar-check"></i>
                                        <small>Announced on <?php echo date('M j, Y', strtotime($winner['announced_at'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Submissions Table -->
            <div class="submissions-section">
                <div class="section-header">
                    <h2><i class="bi bi-file-earmark-text"></i> All Submissions</h2>
                </div>
                
                <div class="table-card">
                    <div class="table-responsive">
                        <table class="submissions-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Participant</th>
                                    <th>Email</th>
                                    <th>Submission File</th>
                                    <th>Submitted On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($submissions)): ?>
                                    <tr>
                                        <td colspan="7" class="no-data-row">
                                            <div class="no-data">
                                                <i class="bi bi-inbox"></i>
                                                <p>No submissions yet</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($submissions as $sub): ?>
                                        <?php
                                        $is_winner = false;
                                        foreach ($winners as $w) {
                                            if ($w['user_id'] == $sub['user_id']) {
                                                $is_winner = true;
                                                break;
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $counter++; ?></td>
                                            <td>
                                                <div class="participant-cell">
                                                    <strong><?php echo htmlspecialchars($sub['full_name']); ?></strong>
                                                    <?php if ($is_winner): ?>
                                                        <span class="winner-badge-small"><i class="bi bi-trophy-fill"></i> Winner</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($sub['email']); ?></td>
                                            <td>
                                                <a href="../<?php echo htmlspecialchars($sub['file_path']); ?>" 
                                                   class="file-link" target="_blank">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                    <?php echo basename($sub['file_path']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="date-cell">
                                                    <strong><?php echo date('M j, Y', strtotime($sub['submitted_at'])); ?></strong>
                                                    <small><?php echo date('h:i A', strtotime($sub['submitted_at'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($is_winner): ?>
                                                    <span class="submission-status status-winner">
                                                        <i class="bi bi-trophy-fill"></i> Winner
                                                    </span>
                                                <?php else: ?>
                                                    <span class="submission-status status-pending">
                                                        <i class="bi bi-hourglass-split"></i> Under Review
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="../<?php echo htmlspecialchars($sub['file_path']); ?>" 
                                                       class="btn-table-action btn-view" target="_blank" title="View Submission">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
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
        </div>
    </main>
</div>

<script>
document.getElementById('toggleSidebar').addEventListener('click', function() {
    document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
});

function toggleWinnerForm() {
    const form = document.getElementById('winnerForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
    }
}
</script>

<?php include '../includes/admin_footer.php';?>