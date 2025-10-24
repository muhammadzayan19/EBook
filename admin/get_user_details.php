<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false]);
    exit();
}

require_once '../config/db.php';
$user_id = intval($_GET['id']);

// Fetch user details with order history
$query = "SELECT u.*, 
          COUNT(DISTINCT o.order_id) as total_orders,
          SUM(CASE WHEN o.status = 'paid' THEN o.total_amount ELSE 0 END) as total_spent,
          COUNT(DISTINCT s.submission_id) as total_submissions
          FROM users u
          LEFT JOIN orders o ON u.user_id = o.user_id
          LEFT JOIN submissions s ON u.user_id = s.user_id
          WHERE u.user_id = $user_id
          GROUP BY u.user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo json_encode(['success' => false]);
    exit();
}

// Fetch recent orders
$orders_query = "SELECT o.*, b.title FROM orders o 
                 LEFT JOIN books b ON o.book_id = b.book_id
                 WHERE o.user_id = $user_id 
                 ORDER BY o.order_date DESC LIMIT 5";
$orders_result = mysqli_query($conn, $orders_query);

$html = '<div class="user-details-grid">';
$html .= '<div class="user-detail-item"><label><i class="bi bi-person"></i> Full Name</label><div class="user-detail-value">' . htmlspecialchars($user['full_name']) . '</div></div>';
$html .= '<div class="user-detail-item"><label><i class="bi bi-envelope"></i> Email</label><div class="user-detail-value">' . htmlspecialchars($user['email']) . '</div></div>';
$html .= '<div class="user-detail-item"><label><i class="bi bi-telephone"></i> Phone</label><div class="user-detail-value">' . ($user['phone'] ?: 'N/A') . '</div></div>';
$html .= '<div class="user-detail-item"><label><i class="bi bi-calendar"></i> Registered</label><div class="user-detail-value">' . date('M j, Y', strtotime($user['registered_at'])) . '</div></div>';
$html .= '<div class="user-detail-item"><label><i class="bi bi-bag"></i> Total Orders</label><div class="user-detail-value">' . $user['total_orders'] . '</div></div>';
$html .= '<div class="user-detail-item"><label><i class="bi bi-currency-dollar"></i> Total Spent</label><div class="user-detail-value">$' . number_format($user['total_spent'], 2) . '</div></div>';
$html .= '</div>';

if ($user['address']) {
    $html .= '<div style="padding: 1rem; background: #f8fafc; border-radius: 12px; margin-bottom: 1.5rem;">';
    $html .= '<label style="font-weight: 700; color: var(--admin-secondary); font-size: 0.85rem; display: block; margin-bottom: 0.5rem;"><i class="bi bi-geo-alt"></i> ADDRESS</label>';
    $html .= '<p style="margin: 0; color: var(--admin-primary);">' . htmlspecialchars($user['address']) . '</p>';
    $html .= '</div>';
}

// Recent orders
if (mysqli_num_rows($orders_result) > 0) {
    $html .= '<div class="user-activity-timeline">';
    $html .= '<div class="timeline-header"><i class="bi bi-clock-history"></i> Recent Orders</div>';
    $html .= '<div class="timeline-list">';
    while ($order = mysqli_fetch_assoc($orders_result)) {
        $html .= '<div class="timeline-item">';
        $html .= '<div class="timeline-icon"><i class="bi bi-bag"></i></div>';
        $html .= '<div class="timeline-content">';
        $html .= '<strong>' . htmlspecialchars($order['title']) . ' - $' . number_format($order['total_amount'], 2) . '</strong>';
        $html .= '<small>Order #' . $order['order_id'] . ' • ' . date('M j, Y', strtotime($order['order_date'])) . ' • ' . ucfirst($order['status']) . '</small>';
        $html .= '</div></div>';
    }
    $html .= '</div></div>';
}

echo json_encode(['success' => true, 'html' => $html]);
?>