<?php
session_start();

require_once '../config/db.php';
$conn->query("UPDATE competitions SET status = 'closed' WHERE end_date < NOW() AND status = 'active'");

$page_title = "Writing Competitions";

include '../includes/header.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

$comp_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($comp_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM competitions WHERE comp_id = ?");
    $stmt->bind_param("i", $comp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $single_competition = $result->fetch_assoc();
    $stmt->close();
    
    $has_submitted = false;
    if ($is_logged_in && $single_competition) {
        $check_stmt = $conn->prepare("SELECT submission_id FROM submissions WHERE comp_id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $comp_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $has_submitted = $check_result->num_rows > 0;
        $check_stmt->close();
    }
} else {
    $result = $conn->query("SELECT * FROM competitions WHERE LOWER(status) = 'active' ORDER BY start_date DESC");
}

$winners_result = $conn->query("
    SELECT w.*, c.title as comp_title, u.full_name 
    FROM winners w 
    JOIN competitions c ON w.comp_id = c.comp_id 
    JOIN users u ON w.user_id = u.user_id 
    ORDER BY w.announced_at DESC 
    LIMIT 5
");
?>

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="display-4 fw-bold mb-3">Writing Competitions</h1>
                <p class="lead mb-4">Showcase your creativity and win amazing prizes</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item active">Competitions</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<?php if ($comp_id > 0 && $single_competition): ?>
<section class="competition-detail-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="competition-detail-card">
                    <div class="competition-detail-header">
                        <span class="competition-type-large <?php echo $single_competition['type'] === 'story' ? 'type-story' : 'type-essay'; ?>">
                            <?php echo ucfirst($single_competition['type']); ?> Competition
                        </span>
                        <span class="competition-status-large">
                            Active
                        </span>
                    </div>
                    
                    <h2 class="competition-detail-title"><?php echo htmlspecialchars($single_competition['title']); ?></h2>
                    
                    <div class="competition-meta-info">
                        <div class="meta-item">
                            <i class="bi bi-calendar-event"></i>
                            <div>
                                <strong>Start Date</strong>
                                <span><?php echo date('F j, Y', strtotime($single_competition['start_date'])); ?></span>
                            </div>
                        </div>
                        <div class="meta-item">
                            <i class="bi bi-calendar-x"></i>
                            <div>
                                <strong>End Date</strong>
                                <span><?php echo date('F j, Y', strtotime($single_competition['end_date'])); ?></span>
                            </div>
                        </div>
                        <div class="meta-item">
                            <i class="bi bi-trophy-fill"></i>
                            <div>
                                <strong>Prize</strong>
                                <span class="prize-amount"><?php echo htmlspecialchars($single_competition['prize']); ?></span>
                            </div>
                        </div>
                        <div class="meta-item">
                            <i class="bi bi-clock-history"></i>
                            <div>
                                <strong>Time Limit</strong>
                                <span>3 Hours</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="competition-topic-section">
                        <h4><i class="bi bi-lightbulb me-2"></i>Topic</h4>
                        <div class="topic-box">
                            <?php echo nl2br(htmlspecialchars($single_competition['topic'])); ?>
                        </div>
                    </div>
                    
                    <div class="competition-rules">
                        <h4><i class="bi bi-list-check me-2"></i>Rules & Guidelines</h4>
                        <ul class="rules-list">
                            <li>You must be a registered user to participate</li>
                            <li>Time limit: 3 hours from when you start</li>
                            <li>Accepted formats: .doc, .docx, .pdf</li>
                            <li>Maximum file size: 5MB</li>
                            <li>Submission must be original work</li>
                            <li>Only one submission per user</li>
                            <li>Plagiarism will result in disqualification</li>
                        </ul>
                    </div>
                    
                    <?php if ($is_logged_in): ?>
                        <?php if ($has_submitted): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                You have already submitted your entry for this competition!
                            </div>
                        <?php else: ?>
                            <a href="write_essay.php?comp_id=<?php echo $comp_id; ?>" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-pencil-square me-2"></i>Start Writing Now
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Please <a href="login.php" class="alert-link">login</a> to participate in this competition.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="competition-sidebar">
                    <div class="sidebar-card">
                        <h5><i class="bi bi-info-circle me-2"></i>Quick Info</h5>
                        <div class="quick-info-item">
                            <span>Competition Type</span>
                            <strong><?php echo ucfirst($single_competition['type']); ?></strong>
                        </div>
                        <div class="quick-info-item">
                            <span>Status</span>
                            <strong class="text-success">Active</strong>
                        </div>
                        <div class="quick-info-item">
                            <span>Time Remaining</span>
                            <strong id="countdown"></strong>
                        </div>
                    </div>
                    
                    <div class="sidebar-card mt-4">
                        <h5><i class="bi bi-trophy me-2"></i>Prizes</h5>
                        <div class="prize-breakdown">
                            <div class="prize-item">
                                <span class="position">🥇 1st Place</span>
                                <span class="amount">$<?php echo number_format(floatval(str_replace(['$', ','], '', $single_competition['prize'])) * 0.5, 2); ?></span>
                            </div>
                            <div class="prize-item">
                                <span class="position">🥈 2nd Place</span>
                                <span class="amount">$<?php echo number_format(floatval(str_replace(['$', ','], '', $single_competition['prize'])) * 0.3, 2); ?></span>
                            </div>
                            <div class="prize-item">
                                <span class="position">🥉 3rd Place</span>
                                <span class="amount">$<?php echo number_format(floatval(str_replace(['$', ','], '', $single_competition['prize'])) * 0.2, 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sidebar-card mt-4">
                        <h5><i class="bi bi-people me-2"></i>Need Help?</h5>
                        <p class="small text-muted mb-3">Have questions about the competition?</p>
                        <a href="../contact.php" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-envelope me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const endDate = new Date('<?php echo $single_competition['end_date']; ?>').getTime();

function updateCountdown() {
    const now = new Date().getTime();
    const distance = endDate - now;
    
    if (distance < 0) {
        document.getElementById('countdown').innerHTML = "Ended";
        return;
    }
    
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    
    document.getElementById('countdown').innerHTML = days + "d " + hours + "h " + minutes + "m";
}

updateCountdown();
setInterval(updateCountdown, 60000);
</script>

<?php else: ?>
<section class="competitions-listing-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <span class="section-label">Compete & Win</span><br>
                <h2 class="section-title mb-3">Active Competitions</h2>
                <p class="text-muted">Join our writing competitions, showcase your talent, and win exciting prizes</p>
            </div>
        </div>
        
        <div class="row">
            <?php 
            if ($result->num_rows > 0):
                while ($competition = $result->fetch_assoc()): 
            ?>
            <div class="col-lg-6 mb-4">
                <div class="competition-card-large">
                    <div class="competition-card-header">
                        <span class="competition-type-badge <?php echo $competition['type'] === 'story' ? 'badge-story' : 'badge-essay'; ?>">
                            <?php echo ucfirst($competition['type']); ?>
                        </span>
                        <span class="competition-status-badge">
                            Active
                        </span>
                    </div>
                    
                    <h3 class="competition-card-title"><?php echo htmlspecialchars($competition['title']); ?></h3>
                    
                    <p class="competition-card-description">
                        <?php echo htmlspecialchars(substr($competition['topic'], 0, 150)) . '...'; ?>
                    </p>
                    
                    <div class="competition-card-details">
                        <div class="detail-item">
                            <i class="bi bi-calendar-event"></i>
                            <span>Ends: <?php echo date('M j, Y', strtotime($competition['end_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="bi bi-trophy"></i>
                            <span>Prize: <?php echo htmlspecialchars($competition['prize']); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="bi bi-clock"></i>
                            <span>Duration: 3 Hours</span>
                        </div>
                    </div>
                    
                    <a href="competition.php?id=<?php echo $competition['comp_id']; ?>" class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-arrow-right-circle me-2"></i>View Details & Participate
                    </a>
                </div>
            </div>
            <?php 
                endwhile;
            else:
            ?>
            <div class="col-12">
                <div class="no-competitions">
                    <i class="bi bi-calendar-x"></i>
                    <h4>No Active Competitions</h4>
                    <p>Check back soon for new competitions!</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($winners_result->num_rows > 0): ?>
<section class="winners-section py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto text-center">
                <span class="section-label">Hall of Fame</span><br>
                <h2 class="section-title mb-3">Recent Winners</h2>
                <p class="text-muted">Congratulations to our talented winners!</p>
            </div>
        </div>
        
        <div class="row">
            <?php while ($winner = $winners_result->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="winner-card">
                    <div class="winner-position position-<?php echo $winner['position']; ?>">
                        <?php 
                        $medals = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
                        echo $medals[$winner['position']] ?? '🏆'; 
                        ?>
                    </div>
                    <h5 class="winner-name"><?php echo htmlspecialchars($winner['full_name']); ?></h5>
                    <p class="winner-competition"><?php echo htmlspecialchars($winner['comp_title']); ?></p>
                    <div class="winner-prize">
                        <i class="bi bi-trophy-fill me-2"></i>
                        <?php echo htmlspecialchars($winner['prize']); ?>
                    </div>
                    <small class="winner-date">
                        <i class="bi bi-calendar-check me-1"></i>
                        <?php echo date('M j, Y', strtotime($winner['announced_at'])); ?>
                    </small>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="how-it-works-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <span class="section-label">Simple Process</span><br>
                <h2 class="section-title mb-3">How It Works</h2>
                <p class="text-muted">Follow these simple steps to participate in our competitions</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h5 class="step-title">Register/Login</h5>
                    <p class="step-description">Create an account or login to your existing account</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <h5 class="step-title">Choose Competition</h5>
                    <p class="step-description">Browse and select a competition that interests you</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <h5 class="step-title">Write & Submit</h5>
                    <p class="step-description">Write your entry within 3 hours and submit it</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-icon">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h5 class="step-title">Win Prizes</h5>
                    <p class="step-description">Wait for results and celebrate if you win!</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
include '../includes/footer.php';
?>