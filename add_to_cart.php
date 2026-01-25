<?php
session_start();

// Check if Product ID exists
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Create cart array if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add item to cart (increment quantity if already exists)
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        $_SESSION['cart'][$id] = 1;
    }
}

// Redirect back to menu so they can keep ordering
header("Location: menu.php");
exit();?>
