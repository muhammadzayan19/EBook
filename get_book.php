<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die("Unauthorized - Please log in");
}

require_once __DIR__ . '/config/db.php';

$book_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if ($book_id === 0) {
    http_response_code(400);
    die("Invalid book ID");
}

$stmt = $conn->prepare("SELECT file_path, title, type FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die("Book not found");
}

$book = $result->fetch_assoc();
$stmt->close();

if ($book['type'] !== 'pdf') {
    http_response_code(403);
    die("Only PDFs can be downloaded");
}

$has_access = false;

$stmt = $conn->prepare("
    SELECT o.order_id 
    FROM orders o
    LEFT JOIN payments p ON o.order_id = p.order_id
    WHERE o.user_id = ? AND o.book_id = ?
    AND (o.status = 'paid' OR p.payment_status = 'completed')
    LIMIT 1
");
$stmt->bind_param("ii", $user_id, $book_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $has_access = true;
}
$stmt->close();

if (!$has_access) {
    $stmt = $conn->prepare("
        SELECT sa.access_id 
        FROM subscription_access sa
        INNER JOIN subscriptions s ON sa.subscription_id = s.subscription_id
        WHERE s.user_id = ? AND sa.book_id = ?
        AND s.status = 'active' 
        AND s.end_date > NOW()
        LIMIT 1
    ");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $has_access = true;
    }
    $stmt->close();
}

if (!$has_access) {
    http_response_code(403);
    die("Access denied. Please purchase or subscribe.");
}

$file_path = __DIR__ . '/' . ltrim($book['file_path'], './');

if (!file_exists($file_path)) {
    http_response_code(404);
    die("File not found on server");
}

$uploads_dir = realpath(__DIR__ . '/uploads');
$real_path = realpath($file_path);

if (strpos($real_path, $uploads_dir) !== 0) {
    http_response_code(403);
    die("Access denied");
}

$stmt = $conn->prepare("INSERT INTO download_logs (user_id, book_id, downloaded_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $user_id, $book_id);
$stmt->execute();
$stmt->close();

$conn->close();

$filename = preg_replace('/[^a-zA-Z0-9._\- ]/', '_', $book['title']) . '.pdf';
$file_size = filesize($file_path);

while (ob_get_level()) {
    ob_end_clean();
}

set_time_limit(0);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $file_size);
header('Pragma: public');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

readfile($file_path);
exit;
?>