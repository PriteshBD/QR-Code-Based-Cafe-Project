<?php
session_start();
// In a real application, you would insert a record into a 'service_requests' table here.
// For this demo, we will just alert the user.
echo "<script>alert('Waiter has been notified! They will be at your table shortly.'); window.location.href='menu.php';</script>";
?>