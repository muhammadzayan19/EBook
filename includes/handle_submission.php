<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Create drafts table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS competition_drafts (
    draft_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    comp_id INT NOT NULL,
    content LONGTEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_draft (user_id, comp_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (comp_id) REFERENCES competitions(comp_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

switch ($action) {
    case 'save_draft':
        $comp_id = intval($_POST['comp_id']);
        $content = $_POST['content'] ?? '';
        
        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'No content to save']);
            exit();
        }
        
        // Check if competition is still active
        $check = $conn->prepare("SELECT comp_id FROM competitions WHERE comp_id = ? AND status = 'active'");
        $check->bind_param("i", $comp_id);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Competition is not active']);
            exit();
        }
        $check->close();
        
        // Save or update draft
        $stmt = $conn->prepare("INSERT INTO competition_drafts (user_id, comp_id, content) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE content = ?, updated_at = CURRENT_TIMESTAMP");
        $stmt->bind_param("iiss", $user_id, $comp_id, $content, $content);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Draft saved']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save draft']);
        }
        $stmt->close();
        break;
        
    case 'load_draft':
        $comp_id = intval($_POST['comp_id']);
        
        $stmt = $conn->prepare("SELECT content FROM competition_drafts WHERE user_id = ? AND comp_id = ?");
        $stmt->bind_param("ii", $user_id, $comp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $draft = $result->fetch_assoc();
            echo json_encode(['success' => true, 'content' => $draft['content']]);
        } else {
            echo json_encode(['success' => true, 'content' => '']);
        }
        $stmt->close();
        break;
        
    case 'submit_entry':
        $comp_id = intval($_POST['comp_id']);
        $content = $_POST['content'] ?? '';
        
        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'No content to submit']);
            exit();
        }
        
        // Verify competition is active
        $check = $conn->prepare("SELECT * FROM competitions WHERE comp_id = ? AND status = 'active'");
        $check->bind_param("i", $comp_id);
        $check->execute();
        $comp_result = $check->get_result();
        
        if ($comp_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Competition is not active']);
            exit();
        }
        $competition = $comp_result->fetch_assoc();
        $check->close();
        
        // Check time limit
        $session_key = 'comp_start_' . $comp_id;
        if (isset($_SESSION[$session_key])) {
            $start_time = $_SESSION[$session_key];
            $elapsed = time() - $start_time;
            $time_limit = 3 * 60 * 60; // 3 hours
            
            if ($elapsed > $time_limit) {
                echo json_encode(['success' => false, 'message' => 'Time limit exceeded']);
                exit();
            }
        }
        
        // Check if already submitted
        $check_sub = $conn->prepare("SELECT submission_id FROM submissions WHERE comp_id = ? AND user_id = ?");
        $check_sub->bind_param("ii", $comp_id, $user_id);
        $check_sub->execute();
        if ($check_sub->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Already submitted']);
            exit();
        }
        $check_sub->close();
        
        // Include document converter
        require_once '../includes/DocConverter.php';
        
        // Create uploads directory if it doesn't exist
        $upload_dir = '../uploads/submissions/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate filenames for multiple formats
        $base_filename = 'submission_' . $comp_id . '_' . $user_id . '_' . time();
        
        // Get user and competition info for document title
        $user_stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        $user_stmt->close();
        
        $doc_title = $competition['title'] . ' - ' . $user_data['full_name'];
        
        // Save as DOC format (primary format)
        $doc_filename = $base_filename . '.doc';
        $doc_filepath = $upload_dir . $doc_filename;
        
        if (!DocConverter::saveAsDoc($content, $doc_filepath, $doc_title)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save submission document']);
            exit();
        }
        
        // Also save plain text backup
        $txt_filename = $base_filename . '.txt';
        $txt_filepath = $upload_dir . $txt_filename;
        $plain_text = strip_tags($content);
        $plain_text = html_entity_decode($plain_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        file_put_contents($txt_filepath, $plain_text);
        
        $file_path = $doc_filepath;
        
        // Insert submission record
        $stmt = $conn->prepare("INSERT INTO submissions (comp_id, user_id, file_path, submitted_at) 
                                VALUES (?, ?, ?, NOW())");
        $relative_path = 'uploads/submissions/' . $doc_filename;
        $stmt->bind_param("iis", $comp_id, $user_id, $relative_path);
        
        if ($stmt->execute()) {
            // Delete draft after successful submission
            $delete_draft = $conn->prepare("DELETE FROM competition_drafts WHERE user_id = ? AND comp_id = ?");
            $delete_draft->bind_param("ii", $user_id, $comp_id);
            $delete_draft->execute();
            $delete_draft->close();
            
            // Clear session timer
            unset($_SESSION[$session_key]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Submission successful',
                'submission_id' => $stmt->insert_id
            ]);
        } else {
            // Clean up file if database insert fails
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            echo json_encode(['success' => false, 'message' => 'Failed to save submission']);
        }
        $stmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

$conn->close();
?>