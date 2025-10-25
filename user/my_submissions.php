<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$page_title = "My Submissions";

$stmt = $conn->prepare("
    SELECT 
        s.submission_id,
        s.file_path,
        s.submitted_at,
        c.comp_id,
        c.title as competition_title,
        c.type as competition_type,
        c.topic,
        c.start_date,
        c.end_date,
        c.prize,
        c.status as competition_status,
        w.winner_id,
        w.position,
        w.prize as won_prize,
        w.announced_at
    FROM submissions s
    INNER JOIN competitions c ON s.comp_id = c.comp_id
    LEFT JOIN winners w ON s.comp_id = w.comp_id AND s.user_id = w.user_id
    WHERE s.user_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$submissions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_submissions = count($submissions);
$essay_count = 0;
$story_count = 0;
$wins_count = 0;
$active_competitions = 0;

foreach ($submissions as $submission) {
    if ($submission['competition_type'] === 'essay') {
        $essay_count++;
    } else {
        $story_count++;
    }
    
    if ($submission['winner_id']) {
        $wins_count++;
    }
    
    if ($submission['competition_status'] === 'active') {
        $active_competitions++;
    }
}

include '../includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="display-5 fw-bold mb-3">My Submissions</h1>
                <p class="lead mb-4">Track your competition entries and achievements</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="profile.php">Profile</a></li>
                        <li class="breadcrumb-item active">My Submissions</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="my-submissions-section py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="submissions-stat-card">
                    <div class="stat-icon-wrapper stat-primary">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $total_submissions; ?></div>
                        <div class="stat-label">Total Submissions</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="submissions-stat-card">
                    <div class="stat-icon-wrapper stat-info">
                        <i class="bi bi-pen"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $essay_count; ?></div>
                        <div class="stat-label">Essays</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="submissions-stat-card">
                    <div class="stat-icon-wrapper stat-warning">
                        <i class="bi bi-book"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $story_count; ?></div>
                        <div class="stat-label">Stories</div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="submissions-stat-card stat-card-winner">
                    <div class="stat-icon-wrapper stat-success">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $wins_count; ?></div>
                        <div class="stat-label">Wins</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="submissions-filters-bar">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="search-box-submissions">
                        <input type="text" id="searchSubmissions" class="form-control" 
                               placeholder="Search by competition title...">
                        <i class="bi bi-search search-icon"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">
                            <i class="bi bi-grid"></i> All
                        </button>
                        <button class="filter-btn" data-filter="essay">
                            <i class="bi bi-pen"></i> Essays
                        </button>
                        <button class="filter-btn" data-filter="story">
                            <i class="bi bi-book"></i> Stories
                        </button>
                        <button class="filter-btn" data-filter="winner">
                            <i class="bi bi-trophy"></i> Wins
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($submissions)): ?>
        <div class="no-submissions-section">
            <div class="no-submissions-icon">
                <i class="bi bi-file-earmark-x"></i>
            </div>
            <h3 class="no-submissions-title">No Submissions Yet</h3>
            <p class="no-submissions-text">You haven't participated in any competitions yet. Join now and showcase your talent!</p>
            <a href="competition.php" class="btn btn-primary btn-lg">
                <i class="bi bi-trophy me-2"></i>Browse Competitions
            </a>
        </div>
        <?php else: ?>
        <div class="submissions-list" id="submissionsList">
            <?php foreach ($submissions as $submission): ?>
            <div class="submission-item" 
                 data-type="<?php echo $submission['competition_type']; ?>" 
                 data-is-winner="<?php echo $submission['winner_id'] ? 'true' : 'false'; ?>"
                 data-title="<?php echo strtolower($submission['competition_title']); ?>">
                <div class="submission-card <?php echo $submission['winner_id'] ? 'submission-winner' : ''; ?>">
                    <?php if ($submission['winner_id']): ?>
                    <div class="winner-ribbon">
                        <i class="bi bi-trophy-fill"></i>
                        <span>Winner</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="submission-card-header">
                        <div class="submission-header-left">
                            <div class="submission-id">
                                <span class="submission-id-label">Submission ID:</span>
                                <span class="submission-id-value">#<?php echo str_pad($submission['submission_id'], 5, '0', STR_PAD_LEFT); ?></span>
                            </div>
                            <div class="competition-type-badge badge-<?php echo $submission['competition_type']; ?>">
                                <i class="bi bi-<?php echo $submission['competition_type'] === 'essay' ? 'pen' : 'book'; ?>"></i>
                                <?php echo ucfirst($submission['competition_type']); ?>
                            </div>
                        </div>
                        <div class="submission-status">
                            <?php if ($submission['competition_status'] === 'active'): ?>
                                <span class="status-badge status-active">
                                    <i class="bi bi-hourglass-split"></i> Under Review
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-closed">
                                    <i class="bi bi-check-circle"></i> Competition Closed
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="submission-card-body">
                        <h5 class="submission-competition-title">
                            <?php echo htmlspecialchars($submission['competition_title']); ?>
                        </h5>
                        
                        <div class="submission-topic">
                            <strong><i class="bi bi-lightbulb me-2"></i>Topic:</strong>
                            <p><?php echo htmlspecialchars(substr($submission['topic'], 0, 150)) . (strlen($submission['topic']) > 150 ? '...' : ''); ?></p>
                        </div>
                        
                        <div class="row submission-details-grid">
                            <div class="col-md-6">
                                <div class="submission-detail-card">
                                    <div class="detail-icon">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Submitted On</span>
                                        <span class="detail-value"><?php echo date('M j, Y', strtotime($submission['submitted_at'])); ?></span>
                                        <span class="detail-time"><?php echo date('h:i A', strtotime($submission['submitted_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="submission-detail-card">
                                    <div class="detail-icon">
                                        <i class="bi bi-gift"></i>
                                    </div>
                                    <div class="detail-content">
                                        <span class="detail-label">Prize</span>
                                        <span class="detail-value prize-value"><?php echo htmlspecialchars($submission['prize']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($submission['winner_id']): ?>
                        <div class="winner-announcement">
                            <div class="winner-confetti"></div>
                            <div class="winner-info">
                                <h6>
                                    <i class="bi bi-trophy-fill"></i>
                                    Congratulations! You Won <?php echo htmlspecialchars($submission['position']); ?> Place
                                </h6>
                                <p class="winner-prize">
                                    <i class="bi bi-award"></i>
                                    Prize: <strong><?php echo htmlspecialchars($submission['won_prize']); ?></strong>
                                </p>
                                <p class="winner-date">
                                    <i class="bi bi-calendar-event"></i>
                                    Announced on <?php echo date('M j, Y', strtotime($submission['announced_at'])); ?>
                                </p>
                            </div>
                            <div class="winner-confetti"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="submission-card-footer">
                        <div class="submission-file-info">
                            <i class="bi bi-file-earmark-pdf"></i>
                            <span>Submission File Uploaded</span>
                        </div>
                        <div class="submission-actions">
                            <?php if (!empty($submission['file_path'])): ?>
                            <a href="../<?php echo htmlspecialchars($submission['file_path']); ?>" 
                               class="btn btn-sm btn-outline-primary" 
                               download>
                                <i class="bi bi-download"></i> Download
                            </a>
                            <?php endif; ?>
                            <a href="competition.php?id=<?php echo $submission['comp_id']; ?>" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> View Competition
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="modal fade" id="achievementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content achievement-modal-content">
            <div class="modal-body text-center p-5">
                <div class="achievement-icon">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <h3 class="achievement-title">Amazing Work!</h3>
                <p class="achievement-text">You have <?php echo $total_submissions; ?> submissions across <?php echo $essay_count + $story_count; ?> competitions!</p>
                <?php if ($wins_count > 0): ?>
                <div class="achievement-wins">
                    <i class="bi bi-star-fill"></i>
                    <span>Including <?php echo $wins_count; ?> winning <?php echo $wins_count === 1 ? 'entry' : 'entries'; ?>!</span>
                </div>
                <?php endif; ?>
                <button type="button" class="btn btn-primary mt-3" data-bs-dismiss="modal">
                    Continue Writing
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('searchSubmissions').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const submissions = document.querySelectorAll('.submission-item');
    
    submissions.forEach(submission => {
        const title = submission.dataset.title;
        
        if (title.includes(searchTerm)) {
            submission.style.display = '';
        } else {
            submission.style.display = 'none';
        }
    });
});

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const submissions = document.querySelectorAll('.submission-item');
        
        submissions.forEach(submission => {
            const type = submission.dataset.type;
            const isWinner = submission.dataset.isWinner;
            
            if (filter === 'all') {
                submission.style.display = '';
            } else if (filter === 'winner') {
                submission.style.display = isWinner === 'true' ? '' : 'none';
            } else {
                submission.style.display = type === filter ? '' : 'none';
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const submissions = document.querySelectorAll('.submission-item');
    submissions.forEach((submission, index) => {
        setTimeout(() => {
            submission.style.opacity = '0';
            submission.style.transform = 'translateY(20px)';
            submission.style.transition = 'all 0.4s ease-out';
            
            setTimeout(() => {
                submission.style.opacity = '1';
                submission.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
    
    <?php if ($total_submissions >= 5 && !isset($_SESSION['achievement_shown'])): ?>
    setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('achievementModal'));
        modal.show();
        <?php $_SESSION['achievement_shown'] = true; ?>
    }, 1000);
    <?php endif; ?>
});

document.querySelectorAll('.submission-winner').forEach(card => {
    card.addEventListener('mouseenter', function() {
        const confetti = this.querySelectorAll('.winner-confetti');
        confetti.forEach(c => {
            c.style.animation = 'confettiFloat 1s ease-in-out infinite';
        });
    });
});
</script>

<style>
@keyframes confettiFloat {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    25% {
        transform: translateY(-5px) rotate(5deg);
    }
    75% {
        transform: translateY(-5px) rotate(-5deg);
    }
}
</style>

<?php
include '../includes/footer.php';
?>