<?php
session_start();
include 'includes/db_connect.php';

// Get table ID from session
$table_id = isset($_SESSION['table_id']) ? (int)$_SESSION['table_id'] : 1;

// Insert service request into database
$sql = "INSERT INTO service_requests (table_id, request_type, status) VALUES ($table_id, 'Waiter Assistance', 'Pending')";
$result = $conn->query($sql);

if ($result) {
    // Return success response (can be JSON for AJAX)
    if (!empty($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Staff notified! They will be at your table shortly.']);
    } else {
        echo "<script>alert('✓ Staff notified! They will be at your table shortly.'); window.location.href='menu.php';</script>";
    }
} else {
    if (!empty($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error notifying staff']);
    } else {
        echo "<script>alert('❌ Unable to notify staff. Please try again.'); window.location.href='menu.php';</script>";
    }
}
?>