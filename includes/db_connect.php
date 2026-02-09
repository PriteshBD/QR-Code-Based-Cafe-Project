<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafe_project"; // Ensure you create this database in phpMyAdmin

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>