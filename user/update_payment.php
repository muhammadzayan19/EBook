<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $user_id = $_SESSION['user_id'];
    
    if ($order_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
        exit();
    }
    
    if (!in_array($status, ['paid', 'confirmed', 'delivered'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Verify order belongs to user
        $verify_stmt = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?");
        $verify_stmt->bind_param("ii", $order_id, $user_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Order not found or access denied']);
            exit();
        }
        $verify_stmt->close();
        
        // Update order status
        $order_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ? AND user_id = ?");
        $order_stmt->bind_param("sii", $status, $order_id, $user_id);
        
        if (!$order_stmt->execute()) {
            throw new Exception('Failed to update order status');
        }
        $order_stmt->close();
        
        // Update payment status
        $payment_status = 'completed';
        $payment_stmt = $conn->prepare("UPDATE payments SET payment_status = ?, payment_date = NOW() WHERE order_id = ?");
        $payment_stmt->bind_param("si", $payment_status, $order_id);
        
        if (!$payment_stmt->execute()) {
            throw new Exception('Failed to update payment status');
        }
        $payment_stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Log successful payment
        error_log("Payment completed for Order ID: $order_id, User ID: $user_id");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Payment processed successfully',
            'order_id' => $order_id,
            'status' => $status
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Payment update error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to process payment: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>