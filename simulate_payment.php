<?php
// simulate_payment.php
// Simple demo utility to mark an order as Paid and (optionally) move it to Cooking.
// Accessible if staff is logged in OR if ?demo=1 OR if a matching 'key' is provided.
// NOTE: Keep this file out of public repos or protect it in production.

session_start();
include 'db_connect.php';

if (!isset($_REQUEST['order_id'])) {
    http_response_code(400);
    echo 'Missing order_id';
    exit();
}

$order_id = (int)$_REQUEST['order_id'];

// Authorization: allow if staff logged in, or demo param, or matching key
$allowed = false;
if (!empty($_SESSION['staff_logged_in'])) $allowed = true;
if (isset($_REQUEST['demo']) && $_REQUEST['demo'] === '1') $allowed = true;
// Optional secret key (change or remove as desired)
$SIM_KEY = 'demo_secret_please_change';
if (isset($_REQUEST['key']) && $_REQUEST['key'] === $SIM_KEY) $allowed = true;

if (!$allowed) {
    http_response_code(403);
    echo 'Not authorized to simulate payment. Log in as staff or use ?demo=1';
    exit();
}

// Ensure order exists
$res = $conn->query("SELECT * FROM orders WHERE order_id = $order_id");
if (!$res || $res->num_rows === 0) {
    http_response_code(404);
    echo 'Order not found';
    exit();
}
$order = $res->fetch_assoc();

// Prepare update: mark paid; if pending, advance to Cooking with optional prep_time
$prep_time = isset($_REQUEST['prep_time']) ? $conn->real_escape_string($_REQUEST['prep_time']) : '10 mins';
if (strtolower($order['order_status']) === 'pending') {
    $sql = "UPDATE orders SET payment_status='Paid', order_status='Cooking', estimated_time='$prep_time' WHERE order_id=$order_id";
} else {
    $sql = "UPDATE orders SET payment_status='Paid' WHERE order_id=$order_id";
}
$conn->query($sql);

// Make this order visible to customer track page
$_SESSION['last_order_id'] = $order_id;

// Optional flash message
$_SESSION['simulate_msg'] = 'Payment simulated for Order #' . $order_id;

// Redirect to tracking page so presenter can show the result
header('Location: track_order.php');
exit();
