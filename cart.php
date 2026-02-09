<?php
session_start();
include 'includes/db_connect.php';

// If cart is empty, show a message
if (empty($_SESSION['cart'])) {
    echo "<h2 style='text-align:center; margin-top:50px;'>🛒 Your cart is empty!</h2>";
    echo "<p style='text-align:center;'><a href='menu.php'>Go back to Menu</a></p>";
    exit();
}

// Fetch item details for everything in the cart
$ids = implode(',', array_keys($_SESSION['cart']));
$sql = "SELECT * FROM menu_items WHERE item_id IN ($ids)";
$result = $conn->query($sql);

$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | P&S Cafe</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; padding: 20px; }
        .cart-item { background: white; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .total-box { background: #333; color: white; padding: 20px; border-radius: 10px; margin-top: 20px; }
        .note-box { width: 100%; padding: 10px; margin-top: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: sans-serif; }
        
        .pay-btn {
            background: #28a745; color: white; border: none; padding: 15px; width: 100%;
            font-size: 1.2em; font-weight: bold; border-radius: 8px; cursor: pointer; margin-top: 15px;
        }
        .clear-btn { color: red; text-decoration: none; font-size: 0.9em; display: block; text-align: center; margin-top: 15px; }
    </style>
</head>
<body>

    <h2>🛒 Your Order</h2>

    <form action="place_order.php" method="POST">
        
        <?php while($row = $result->fetch_assoc()): ?>
            <?php 
                $qty = $_SESSION['cart'][$row['item_id']];
                $subtotal = $row['price'] * $qty;
                $total_price += $subtotal;
            ?>
            <div class="cart-item">
                <div>
                    <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                    <small>₹<?php echo $row['price']; ?> x <?php echo $qty; ?></small>
                </div>
                <div style="font-weight:bold;">₹<?php echo $subtotal; ?></div>
            </div>
        <?php endwhile; ?>

        <div class="total-box">
            <div style="display:flex; justify-content:space-between; font-size:1.2em; margin-bottom:15px;">
                <span>Total to Pay:</span>
                <span>₹<?php echo $total_price; ?></span>
            </div>

            <label for="note">👨‍🍳 Note to Chef (Optional):</label>
            <textarea name="order_note" class="note-box" rows="2" placeholder="e.g. No onions, Extra spicy, Allergy info..."></textarea>
            
            <input type="hidden" name="total_amount" value="<?php echo $total_price; ?>">

            <button type="submit" class="pay-btn">Confirm & Pay via UPI</button>
        </div>

    </form>

    <a href="clear_cart.php" class="clear-btn">Clear Cart & Start Over</a>

</body>
</html>