<?php
// API endpoint for real-time notifications
session_start();
include 'includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['staff_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$staff_role = $_SESSION['staff_role'] ?? '';
$last_check = $_GET['last_check'] ?? 0;

$notifications = [];

// Get new orders based on role
if ($staff_role === 'Chef') {
    $sql = "SELECT o.order_id, o.table_id, COUNT(oi.id) as item_count
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN menu_items m ON oi.item_id = m.item_id
            WHERE m.item_type = 'Cooking' AND o.order_status = 'Pending'
            AND UNIX_TIMESTAMP(o.created_at) > $last_check
            GROUP BY o.order_id
            ORDER BY o.created_at DESC
            LIMIT 1";
} elseif ($staff_role === 'Barista') {
    $sql = "SELECT o.order_id, o.table_id, COUNT(oi.id) as item_count
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN menu_items m ON oi.item_id = m.item_id
            WHERE m.item_type = 'Beverage' AND o.order_status = 'Pending'
            AND UNIX_TIMESTAMP(o.created_at) > $last_check
            GROUP BY o.order_id
            ORDER BY o.created_at DESC
            LIMIT 1";
} elseif ($staff_role === 'Waiter') {
    $sql = "SELECT o.order_id, o.table_id, COUNT(oi.id) as item_count
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.order_status = 'Ready'
            AND UNIX_TIMESTAMP(o.updated_at) > $last_check
            GROUP BY o.order_id
            ORDER BY o.updated_at DESC
            LIMIT 1";
} elseif ($staff_role === 'Manager') {
    $sql = "SELECT sr.request_id as order_id, sr.table_id, sr.request_type as item_count
            FROM service_requests sr
            WHERE sr.status = 'Pending'
            AND UNIX_TIMESTAMP(sr.request_time) > $last_check
            ORDER BY sr.request_time DESC
            LIMIT 1";
} else {
    $sql = null;
}

if ($sql) {
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'id' => $row['order_id'],
                'table' => $row['table_id'],
                'title' => $staff_role === 'Chef' ? '🔥 New Cooking Order!' : 
                          ($staff_role === 'Barista' ? '☕ New Beverage Order!' : 
                          ($staff_role === 'Waiter' ? '📦 Order Ready to Serve!' : 
                          '🔔 Customer Service Call!')),
                'message' => 'Table #' . $row['table_id'],
                'timestamp' => time()
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'timestamp' => time()
]);
?>
