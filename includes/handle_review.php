<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

require_once '../config/db.php';

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please login to submit a review';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : 'submit_review';

if ($action === 'submit_review') {
    $book_id = intval($_POST['book_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');

    // Validation
    if ($book_id <= 0 || $rating < 1 || $rating > 5) {
        $response['message'] = 'Invalid rating';
        echo json_encode($response);
        exit();
    }

    if (strlen($review_text) > 500) {
        $response['message'] = 'Review must be 500 characters or less';
        echo json_encode($response);
        exit();
    }

    // Check if user has purchased the book
    $purchase_check = $conn->prepare("
        SELECT order_id FROM orders 
        WHERE user_id = ? AND book_id = ? AND status = 'paid'
        LIMIT 1
    ");
    
    if (!$purchase_check) {
        $response['message'] = 'Database error: ' . $conn->error;
        echo json_encode($response);
        exit();
    }
    
    $purchase_check->bind_param("ii", $user_id, $book_id);
    $purchase_check->execute();
    $purchase_result = $purchase_check->get_result();
    $has_purchase = $purchase_result->num_rows > 0;
    $purchase_check->close();

    // Check if user has active subscription - try user_subscriptions first, then subscriptions
    $has_subscription_access = false;
    
    $subscription_check = $conn->prepare("
        SELECT subscription_id FROM user_subscriptions
        WHERE user_id = ? 
        AND status = 'active' 
        AND end_date > NOW()
        LIMIT 1
    ");
    
    if ($subscription_check) {
        $subscription_check->bind_param("i", $user_id);
        if ($subscription_check->execute()) {
            $subscription_result = $subscription_check->get_result();
            $has_subscription_access = $subscription_result->num_rows > 0;
        }
        $subscription_check->close();
    }
    
    // If no subscription found in user_subscriptions, try subscriptions table
    if (!$has_subscription_access) {
        $subscription_check = $conn->prepare("
            SELECT subscription_id FROM subscriptions
            WHERE user_id = ? 
            AND status = 'active' 
            AND end_date > NOW()
            LIMIT 1
        ");
        
        if ($subscription_check) {
            $subscription_check->bind_param("i", $user_id);
            if ($subscription_check->execute()) {
                $subscription_result = $subscription_check->get_result();
                $has_subscription_access = $subscription_result->num_rows > 0;
            }
            $subscription_check->close();
        }
    }

    // Check if book allows subscription access
    $book_check = $conn->prepare("SELECT is_subscription_allowed FROM books WHERE book_id = ?");
    $book_allows_subscription = false;
    
    if ($book_check) {
        $book_check->bind_param("i", $book_id);
        $book_check->execute();
        $book_result = $book_check->get_result();
        $book_data = $book_result->fetch_assoc();
        $book_check->close();
        $book_allows_subscription = $book_data && (int)$book_data['is_subscription_allowed'] === 1;
    }

    // User can review if: they purchased OR (they have subscription AND book allows it)
    $can_access = $has_purchase || ($has_subscription_access && $book_allows_subscription);

    if (!$can_access) {
        $response['message'] = 'You must purchase this book or have an active subscription to review it';
        echo json_encode($response);
        exit();
    }

    // Check if user already reviewed
    $existing_check = $conn->prepare("
        SELECT review_id FROM book_reviews 
        WHERE book_id = ? AND user_id = ?
    ");
    
    if (!$existing_check) {
        $response['message'] = 'Database error: ' . $conn->error;
        echo json_encode($response);
        exit();
    }
    
    $existing_check->bind_param("ii", $book_id, $user_id);
    $existing_check->execute();
    $existing_result = $existing_check->get_result();
    
    if ($existing_result->num_rows > 0) {
        // Update existing review
        $existing_review = $existing_result->fetch_assoc();
        $review_id = $existing_review['review_id'];
        
        $update_stmt = $conn->prepare("
            UPDATE book_reviews 
            SET rating = ?, review_text = ?, status = 'approved', updated_at = NOW()
            WHERE review_id = ?
        ");
        
        if (!$update_stmt) {
            $response['message'] = 'Database error: ' . $conn->error;
            echo json_encode($response);
            exit();
        }
        
        $update_stmt->bind_param("isi", $rating, $review_text, $review_id);
        
        if ($update_stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Review updated successfully! It will be visible after admin approval.';
        } else {
            $response['message'] = 'Failed to update review: ' . $update_stmt->error;
        }
        $update_stmt->close();
    } else {
        // Insert new review
        $review_status = 'approved'

        $stmt = $conn->prepare("
            INSERT INTO book_reviews (book_id, user_id, rating, review_text, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiiss", $book_id, $user_id, $rating, $review_text, $review_status);
        
        if (!$insert_stmt) {
            $response['message'] = 'Database error: ' . $conn->error;
            echo json_encode($response);
            exit();
        }
        
        $insert_stmt->bind_param("iiis", $book_id, $user_id, $rating, $review_text);
        
        if ($insert_stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Thank you for your review!';
            
            // Update summary stats
            updateReviewSummary($conn, $book_id);
        } else {
            $response['message'] = 'Failed to submit review: ' . $insert_stmt->error;
        }
        $insert_stmt->close();
    }
    $existing_check->close();

} elseif ($action === 'mark_helpful') {
    $review_id = intval($_POST['review_id'] ?? 0);

    if ($review_id <= 0) {
        $response['message'] = 'Invalid review';
        echo json_encode($response);
        exit();
    }

    // Increment helpful count
    $update_stmt = $conn->prepare("
        UPDATE book_reviews 
        SET helpful_count = helpful_count + 1 
        WHERE review_id = ?
    ");
    
    if (!$update_stmt) {
        $response['message'] = 'Database error: ' . $conn->error;
        echo json_encode($response);
        exit();
    }
    
    $update_stmt->bind_param("i", $review_id);
    
    if ($update_stmt->execute()) {
        // Get updated count
        $get_stmt = $conn->prepare("SELECT helpful_count FROM book_reviews WHERE review_id = ?");
        if ($get_stmt) {
            $get_stmt->bind_param("i", $review_id);
            $get_stmt->execute();
            $get_result = $get_stmt->get_result();
            $review = $get_result->fetch_assoc();
            
            $response['success'] = true;
            $response['helpful_count'] = (int)$review['helpful_count'];
            
            $get_stmt->close();
        }
    } else {
        $response['message'] = 'Failed to mark as helpful: ' . $update_stmt->error;
    }
    $update_stmt->close();
}

echo json_encode($response);
exit();

// Helper function to update review summary
function updateReviewSummary($conn, $book_id) {
    $stats_stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as average_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM book_reviews
        WHERE book_id = ? AND status = 'approved'
    ");
    
    if (!$stats_stmt) {
        return;
    }
    
    $stats_stmt->bind_param("i", $book_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    $stats_stmt->close();

    // Update or insert summary
    $summary_check = $conn->prepare("SELECT summary_id FROM review_ratings_summary WHERE book_id = ?");
    if (!$summary_check) {
        return;
    }
    
    $summary_check->bind_param("i", $book_id);
    $summary_check->execute();
    $summary_check_result = $summary_check->get_result();
    $has_summary = $summary_check_result->num_rows > 0;
    $summary_check->close();

    if ($has_summary) {
        $update_summary = $conn->prepare("
            UPDATE review_ratings_summary 
            SET total_reviews = ?, average_rating = ?, five_star = ?, four_star = ?, 
                three_star = ?, two_star = ?, one_star = ?, last_updated = NOW()
            WHERE book_id = ?
        ");
        
        if ($update_summary) {
            $avg = (float)($stats['average_rating'] ?? 0);
            $update_summary->bind_param(
                "idiiiiiii",
                $stats['total_reviews'],
                $avg,
                $stats['five_star'],
                $stats['four_star'],
                $stats['three_star'],
                $stats['two_star'],
                $stats['one_star'],
                $book_id
            );
            $update_summary->execute();
            $update_summary->close();
        }
    } else {
        $insert_summary = $conn->prepare("
            INSERT INTO review_ratings_summary 
            (book_id, total_reviews, average_rating, five_star, four_star, three_star, two_star, one_star, last_updated)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        if ($insert_summary) {
            $avg = (float)($stats['average_rating'] ?? 0);
            $insert_summary->bind_param(
                "idiiiiii",
                $book_id,
                $stats['total_reviews'],
                $avg,
                $stats['five_star'],
                $stats['four_star'],
                $stats['three_star'],
                $stats['two_star'],
                $stats['one_star']
            );
            $insert_summary->execute();
            $insert_summary->close();
        }
    }
}
?>