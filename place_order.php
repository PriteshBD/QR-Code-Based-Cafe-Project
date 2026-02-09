<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Get Data
    $table_id = isset($_SESSION['table_id']) ? $_SESSION['table_id'] : 1;
    $order_note = $conn->real_escape_string($_POST['order_note']);
    
    // Recalculate total server-side to prevent manipulation
    $total_amount = 0;
    if (!empty($_SESSION['cart'])) {
        $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
        $price_res = $conn->query("SELECT item_id, price FROM menu_items WHERE item_id IN ($ids)");
        while($row = $price_res->fetch_assoc()) {
            if (isset($_SESSION['cart'][$row['item_id']])) {
                $total_amount += $row['price'] * $_SESSION['cart'][$row['item_id']];
            }
        }
    }
    
    // 2. Create the Order Entry
    // Note: payment_status is 'Pending' initially
    $sql = "INSERT INTO orders (table_id, total_amount, order_note, payment_status, order_status) 
            VALUES ('$table_id', '$total_amount', '$order_note', 'Pending', 'Pending')";
    
    if ($conn->query($sql) === TRUE) {
        $order_id = $conn->insert_id; // Get the new Order ID
        $_SESSION['last_order_id'] = $order_id; // Save it to track

        // 3. Move Items from Session Cart to Database
        foreach ($_SESSION['cart'] as $item_id => $qty) {
            // Get price dynamically to ensure accuracy
            $item_id = (int)$item_id;
            $price_query = "SELECT price FROM menu_items WHERE item_id = $item_id";
            $price_res = $conn->query($price_query);
            $price_row = $price_res->fetch_assoc();
            $price = $price_row['price'];

            $item_sql = "INSERT INTO order_items (order_id, item_id, quantity, price) 
                         VALUES ('$order_id', '$item_id', '$qty', '$price')";
            $conn->query($item_sql);
        }

        // 4. Clear Cart and Redirect
        unset($_SESSION['cart']);
        header("Location: track_order.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>