<?php
session_start();
include 'includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $payment_id = isset($_POST['payment_id']) ? $conn->real_escape_string($_POST['payment_id']) : '';
    $razorpay_order_id = isset($_POST['order_id_razorpay']) ? $conn->real_escape_string($_POST['order_id_razorpay']) : '';
    $razorpay_signature = isset($_POST['signature']) ? $conn->real_escape_string($_POST['signature']) : '';

    if (!$order_id || !$payment_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }

    // Razorpay Secret (keep this secret, never expose in frontend)
    $razorpay_secret = 'nxrHnLj1E0sQ8L0jFwxX1234'; // Replace with your actual secret

    // Verify signature
    $generated_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $payment_id, $razorpay_secret);

    if ($generated_signature !== $razorpay_signature) {
        echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
        exit;
    }

    // Signature is valid, update order status
    $update_sql = "UPDATE orders SET 
                   payment_status = 'Paid',
                   payment_method = 'Razorpay',
                   order_status = 'Pending',
                   payment_id = '$payment_id'
                   WHERE order_id = $order_id";

    if ($conn->query($update_sql) === TRUE) {
        // Log payment transaction
        $log_sql = "INSERT INTO payment_logs (order_id, payment_id, amount, status, payment_method, created_at) 
                   SELECT $order_id, '$payment_id', total_amount, 'Success', 'Razorpay', NOW()
                   FROM orders WHERE order_id = $order_id";
        $conn->query($log_sql);

        echo json_encode(['success' => true, 'message' => 'Payment verified successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update order']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
