<?php
$page_title = "Competition Winners";
include 'includes/header.php';
require_once 'config/db.php';

// Fetch all competitions with winners
$competitions_query = "
    SELECT DISTINCT 
        c.comp_id,
        c.title,
        c.type,
        c.topic,
        c.prize,
        c.end_date,
        COUNT(w.winner_id) as winner_count
    FROM competitions c
    INNER JOIN winners w ON c.comp_id = w.comp_id
    GROUP BY c.comp_id
    ORDER BY c.end_date DESC
";

$competitions_result = mysqli_query($conn, $competitions_query);
?>

<link rel="stylesheet" href="assets/css/winners.css">

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold text-white mb-3">
                    <i class="bi bi-trophy-fill me-3"></i>Competition Winners
                </h1>
                <p class="lead text-white opacity-90">Celebrating our talented writers and their outstanding achievements</p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Winners</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Winners Stats Section -->
<section class="winners-stats-section py-5 bg-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="stat-card-winner">
                    <div class="stat-icon-winner">
                        <i class="bi bi-award"></i>
                    </div>
                    <h3 class="stat-number-winner"><?php echo mysqli_num_rows($competitions_result); ?></h3>
                    <p class="stat-label-winner">Completed Competitions</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="stat-card-winner">
                    <div class="stat-icon-winner">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="stat-number-winner">
                        <?php 
                        $total_winners = mysqli_query($conn, "SELECT COUNT(*) as count FROM winners");
                        echo mysqli_fetch_assoc($total_winners)['count'];
                        ?>
                    </h3>
                    <p class="stat-label-winner">Total Winners</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                <div class="stat-card-winner">
                    <div class="stat-icon-winner">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h3 class="stat-number-winner">
                        <?php 
                        $total_prize = mysqli_query($conn, "SELECT SUM(CAST(REPLACE(REPLACE(prize, '$', ''), ',', '') AS DECIMAL(10,2))) as total FROM winners");
                        $prize_data = mysqli_fetch_assoc($total_prize);
                        echo '$' . number_format($prize_data['total'] ?? 0);
                        ?>
                    </h3>
                    <p class="stat-label-winner">Total Prizes</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card-winner">
                    <div class="stat-icon-winner">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <h3 class="stat-number-winner">
                        <?php 
                        $total_submissions = mysqli_query($conn, "SELECT COUNT(*) as count FROM submissions");
                        echo mysqli_fetch_assoc($total_submissions)['count'];
                        ?>
                    </h3>
                    <p class="stat-label-winner">Total Submissions</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Winners List Section -->
<section class="winners-list-section py-5">
    <div class="container">
        <?php 
        mysqli_data_seek($competitions_result, 0); // Reset pointer
        while ($competition = mysqli_fetch_assoc($competitions_result)): 
            // Fetch winners for this competition
            $winners_query = "
                SELECT 
                    w.*,
                    u.full_name,
                    u.email
                FROM winners w
                INNER JOIN users u ON w.user_id = u.user_id
                WHERE w.comp_id = {$competition['comp_id']}
                ORDER BY 
                    CASE w.position
                        WHEN '1st' THEN 1
                        WHEN '2nd' THEN 2
                        WHEN '3rd' THEN 3
                        ELSE 4
                    END
            ";
            $winners_result = mysqli_query($conn, $winners_query);
        ?>
        
        <div class="competition-winners-block mb-5">
            <!-- Competition Header -->
            <div class="competition-winners-header">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="competition-info-header">
                            <span class="competition-type-badge <?php echo $competition['type'] === 'essay' ? 'badge-essay' : 'badge-story'; ?>">
                                <i class="bi bi-<?php echo $competition['type'] === 'essay' ? 'pencil' : 'book'; ?>"></i>
                                <?php echo ucfirst($competition['type']); ?> Competition
                            </span>
                            <h3 class="competition-title-winner"><?php echo htmlspecialchars($competition['title']); ?></h3>
                            <p class="competition-topic">
                                <i class="bi bi-chat-quote me-2"></i>
                                <strong>Topic:</strong> <?php echo htmlspecialchars($competition['topic']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="competition-meta-winner">
                            <div class="meta-item-winner">
                                <i class="bi bi-calendar-check"></i>
                                <span><?php echo date('M d, Y', strtotime($competition['end_date'])); ?></span>
                            </div>
                            <div class="meta-item-winner">
                                <i class="bi bi-trophy"></i>
                                <span><?php echo htmlspecialchars($competition['prize']); ?></span>
                            </div>
                            <div class="meta-item-winner">
                                <i class="bi bi-people"></i>
                                <span><?php echo $competition['winner_count']; ?> Winners</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Winners Cards -->
            <div class="row">
                <?php 
                $position_icons = [
                    '1st' => 'trophy-fill',
                    '2nd' => 'award-fill',
                    '3rd' => 'medal-fill'
                ];
                
                $position_colors = [
                    '1st' => 'gold',
                    '2nd' => 'silver',
                    '3rd' => 'bronze'
                ];
                
                while ($winner = mysqli_fetch_assoc($winners_result)): 
                    $position = $winner['position'];
                    $icon = $position_icons[$position] ?? 'star-fill';
                    $color = $position_colors[$position] ?? 'default';
                ?>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="winner-card position-<?php echo $color; ?>">
                        <div class="winner-card-header">
                            <div class="position-badge position-<?php echo $color; ?>">
                                <i class="bi bi-<?php echo $icon; ?>"></i>
                                <span><?php echo $position; ?> Place</span>
                            </div>
                        </div>
                        <div class="winner-card-body">
                            <div class="winner-avatar">
                                <div class="avatar-circle">
                                    <?php 
                                    $initials = strtoupper(substr($winner['full_name'], 0, 1));
                                    echo $initials;
                                    ?>
                                </div>
                            </div>
                            <h5 class="winner-name"><?php echo htmlspecialchars($winner['full_name']); ?></h5>
                            <div class="winner-details">
                                <div class="detail-row">
                                    <i class="bi bi-envelope"></i>
                                    <span><?php echo htmlspecialchars($winner['email']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <i class="bi bi-cash-coin"></i>
                                    <span class="prize-amount"><?php echo htmlspecialchars($winner['prize']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <i class="bi bi-calendar-event"></i>
                                    <span><?php echo date('M d, Y', strtotime($winner['announced_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="winner-card-footer">
                            <div class="achievement-badge">
                                <i class="bi bi-patch-check-fill"></i>
                                <span>Verified Winner</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php endwhile; ?>
            </div>
        </div>
        
        <?php endwhile; ?>

        <?php if (mysqli_num_rows($competitions_result) === 0): ?>
        <div class="no-winners-section text-center py-5">
            <div class="no-winners-icon">
                <i class="bi bi-trophy"></i>
            </div>
            <h3 class="no-winners-title">No Winners Announced Yet</h3>
            <p class="no-winners-text">
                Check back soon to see the winners of our ongoing competitions!
            </p>
            <a href="user/competition.php" class="btn btn-primary btn-lg mt-3">
                <i class="bi bi-pencil-square me-2"></i>Join Active Competition
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="winners-cta-section py-5 bg-white">
    <div class="container">
        <div class="cta-box-winner">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0 text-center text-lg-start">
                    <h2 class="text-white fw-bold mb-3">
                        <i class="bi bi-star-fill me-2"></i>Ready to Become Our Next Winner?
                    </h2>
                    <p class="text-white opacity-90 mb-0">
                        Join our active competitions and showcase your writing talent. Win exciting prizes and gain recognition!
                    </p>
                </div>
                <div class="col-lg-4 text-center text-lg-end">
                    <a href="user/competition.php" class="btn btn-light btn-lg px-5">
                        <i class="bi bi-pencil-square me-2"></i>Join Competition
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>