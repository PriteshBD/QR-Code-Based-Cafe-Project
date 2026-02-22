<?php
session_start();
include 'includes/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'Cash';

    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid order']);
        exit;
    }

    // Payment Processing
    $update_sql = "UPDATE orders SET 
                   payment_status = 'Paid',
                   payment_method = '$payment_method',
                   order_status = 'Pending'
                   WHERE order_id = $order_id";

    if ($conn->query($update_sql) === TRUE) {
        // Log payment transaction
        $log_sql = "INSERT INTO payment_logs (order_id, payment_id, amount, status, payment_method, created_at) 
                   SELECT $order_id, CONCAT('" . strtoupper($payment_method) . "_', order_id, '_', UNIX_TIMESTAMP()), total_amount, 'Success', '$payment_method', NOW()
                   FROM orders WHERE order_id = $order_id";
        $conn->query($log_sql);

        echo json_encode(['success' => true, 'message' => 'Payment recorded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update order']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
