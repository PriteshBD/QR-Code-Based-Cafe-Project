<?php
/**
 * Mark Absent Staff
 * 
 * This script marks all staff members who haven't logged in today as "Absent"
 * Run this script at the end of each day (e.g., via Windows Task Scheduler)
 * Or call it from admin dashboard
 * 
 * Usage: Run manually or schedule with Task Scheduler
 */

include '../includes/db_connect.php';

$date = date('Y-m-d');

// Get all staff IDs
$staff_result = $conn->query("SELECT staff_id FROM staff");

while($staff = $staff_result->fetch_assoc()) {
    $staff_id = $staff['staff_id'];
    
    // Check if attendance record exists for today
    $check = $conn->query("SELECT * FROM attendance WHERE staff_id = $staff_id AND date = '$date'");
    
    // If no record exists, mark as Absent
    if($check->num_rows == 0) {
        $conn->query("INSERT INTO attendance (staff_id, date, status) VALUES ($staff_id, '$date', 'Absent')");
    }
}

// Return success message
if(isset($_GET['ajax'])) {
    echo json_encode(['success' => true, 'message' => 'Attendance updated successfully']);
} else {
    echo "Attendance marking complete. All staff without login today marked as Absent.";
}
?>
