<?php

function hasActiveSubscription($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT * FROM subscriptions 
        WHERE user_id = ? 
        AND status = 'active' 
        AND end_date > NOW()
        ORDER BY end_date DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $subscription = $result->fetch_assoc();
        $stmt->close();
        return $subscription;
    }
    
    $stmt->close();
    return false;
}


function canDownloadBook($conn, $user_id, $book_id) {
    $book_stmt = $conn->prepare("SELECT is_subscription_allowed, type, is_free FROM books WHERE book_id = ?");
    $book_stmt->bind_param("i", $book_id);
    $book_stmt->execute();
    $book_result = $book_stmt->get_result();
    $book = $book_result->fetch_assoc();
    $book_stmt->close();
    
    if (!$book) {
        return ['can_download' => false, 'method' => null, 'message' => 'Book not found'];
    }
    
    if ($book['is_free']) {
        return ['can_download' => true, 'method' => 'free', 'message' => 'Free download'];
    }
    
    $purchase_stmt = $conn->prepare("
        SELECT o.order_id, o.status, p.payment_status 
        FROM orders o 
        LEFT JOIN payments p ON o.order_id = p.order_id 
        WHERE o.user_id = ? AND o.book_id = ? 
        AND (o.status = 'paid' OR p.payment_status = 'completed')
    ");
    $purchase_stmt->bind_param("ii", $user_id, $book_id);
    $purchase_stmt->execute();
    $purchase_result = $purchase_stmt->get_result();
    
    if ($purchase_result->num_rows > 0) {
        $purchase_stmt->close();
        return ['can_download' => true, 'method' => 'purchased', 'message' => 'Access via purchase'];
    }
    $purchase_stmt->close();
    
    if ($book['is_subscription_allowed']) {
        $subscription = hasActiveSubscription($conn, $user_id);
        if ($subscription) {
            return ['can_download' => true, 'method' => 'subscription', 'message' => 'Access via subscription'];
        }
    }
    
    return ['can_download' => false, 'method' => null, 'message' => 'No access'];
}

/**
 * Log book download
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $book_id Book ID
 * @return bool Success status
 */
function logDownload($conn, $user_id, $book_id) {
    $stmt = $conn->prepare("INSERT INTO download_logs (user_id, book_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $book_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/**
 * Check if user has purchased a book
 * @param mysqli $conn Database connection
 * @param int $user_id User ID
 * @param int $book_id Book ID
 * @return bool|array Returns order data if purchased, false otherwise
 */
function hasPurchasedBook($conn, $user_id, $book_id) {
    $stmt = $conn->prepare("
        SELECT o.*, p.payment_status 
        FROM orders o 
        LEFT JOIN payments p ON o.order_id = p.order_id 
        WHERE o.user_id = ? AND o.book_id = ?
    ");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $stmt->close();
        return $order;
    }
    
    $stmt->close();
    return false;
}

/**
 * Get subscription pricing from settings
 * @param mysqli $conn Database connection
 * @return array Returns monthly and yearly prices
 */
function getSubscriptionPricing($conn) {
    $pricing = ['monthly' => 9.99, 'yearly' => 99.99];
    
    $result = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('monthly_subscription_price', 'yearly_subscription_price')");
    while ($row = $result->fetch_assoc()) {
        if ($row['setting_key'] === 'monthly_subscription_price') {
            $pricing['monthly'] = floatval($row['setting_value']);
        } elseif ($row['setting_key'] === 'yearly_subscription_price') {
            $pricing['yearly'] = floatval($row['setting_value']);
        }
    }
    
    return $pricing;
}

function createSubscription($conn, $user_id, $plan_type, $amount, $payment_method) {
    $existing = hasActiveSubscription($conn, $user_id);
    
    if ($existing) {
        $duration = ($plan_type === 'yearly') ? '+1 year' : '+1 month';
        $new_end_date = date('Y-m-d H:i:s', strtotime($existing['end_date'] . ' ' . $duration));
        
        $stmt = $conn->prepare("UPDATE subscriptions SET end_date = ? WHERE subscription_id = ?");
        $stmt->bind_param("si", $new_end_date, $existing['subscription_id']);
        $stmt->execute();
        $stmt->close();
        
        return $existing['subscription_id'];
    } else {
        $duration = ($plan_type === 'yearly') ? '+1 year' : '+1 month';
        $end_date = date('Y-m-d H:i:s', strtotime($duration));
        
        $stmt = $conn->prepare("
            INSERT INTO subscriptions (user_id, plan_type, end_date, amount, payment_method, status) 
            VALUES (?, ?, ?, ?, ?, 'active')
        ");
        $stmt->bind_param("issds", $user_id, $plan_type, $end_date, $amount, $payment_method);
        $stmt->execute();
        $subscription_id = $conn->insert_id;
        $stmt->close();
        
        return $subscription_id;
    }
}

function cancelSubscription($conn, $user_id) {
    $stmt = $conn->prepare("
        UPDATE subscriptions 
        SET status = 'cancelled', auto_renew = 0 
        WHERE user_id = ? AND status = 'active'
    ");
    $stmt->bind_param("i", $user_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}
?>