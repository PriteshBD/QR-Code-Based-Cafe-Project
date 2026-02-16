<?php
session_start();
include 'includes/db_connect.php';

// If cart is empty, show a message
if (empty($_SESSION['cart'])) {
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Cart Empty | P&S Cafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .empty-cart {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
        }
        .empty-cart h2 {
            font-size: 2em;
            margin-bottom: 15px;
            color: #333;
        }
        .empty-cart p {
            color: #666;
            margin-bottom: 25px;
            font-size: 1.05em;
        }
        .empty-cart a {
            display: inline-block;
            background: #ff9800;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        .empty-cart a:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body>
    <div class='empty-cart'>
        <h2>🛒 Your cart is empty!</h2>
        <p>Start adding delicious items to your order</p>
        <a href='menu.php'>← Back to Menu</a>
    </div>
</body>
</html>";
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: #f4f4f4; 
            padding: 20px;
            padding-bottom: 100px;
        }
        h2 { 
            text-align: center; 
            margin-bottom: 20px;
            color: #333;
        }
        .cart-item { 
            background: white; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 12px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .cart-item-details strong {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-size: 1em;
        }
        .cart-item-details small {
            color: #666;
            font-size: 0.9em;
        }
        .cart-item-price {
            font-weight: bold;
            color: #ff9800;
            font-size: 1.1em;
            min-width: 80px;
            text-align: right;
        }
        .total-box { 
            background: #333; 
            color: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .total-box label {
            display: block;
            margin-top: 15px;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .note-box { 
            width: 100%; 
            padding: 10px; 
            margin-bottom: 15px;
            border: 1px solid #555; 
            border-radius: 5px; 
            font-family: sans-serif;
            background: #444;
            color: white;
            resize: vertical;
        }
        .pay-btn {
            background: #28a745; 
            color: white; 
            border: none; 
            padding: 15px; 
            width: 100%;
            font-size: 1.1em; 
            font-weight: bold; 
            border-radius: 8px; 
            cursor: pointer;
            transition: background 0.3s;
        }
        .pay-btn:active {
            transform: scale(0.98);
            background: #218838;
        }
        .clear-btn { 
            color: #dc3545; 
            text-decoration: none; 
            font-size: 0.9em; 
            display: block; 
            text-align: center; 
            margin-top: 15px;
            padding: 10px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .back-btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        .total-row {
            display: flex; 
            justify-content: space-between; 
            font-size: 1.3em; 
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #555;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body { padding: 15px; padding-bottom: 100px; }
            h2 { font-size: 1.3em; margin-bottom: 15px; }
            .cart-item { 
                padding: 12px; 
                margin-bottom: 10px;
            }
            .total-box { 
                padding: 15px; 
                margin-top: 15px;
            }
            .pay-btn { 
                font-size: 1em; 
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            body { padding: 12px; }
            h2 { font-size: 1.1em; }
            .cart-item { 
                padding: 10px;
                flex-direction: column;
                align-items: flex-start;
            }
            .cart-item-price {
                align-self: flex-end;
                margin-top: 8px;
            }
            .total-row {
                font-size: 1.1em;
            }
        }
    </style>
</head>
<body>

    <a href="menu.php" class="back-btn">← Back to Menu</a>

    <h2>🛒 Your Order</h2>

    <form action="place_order.php" method="POST">
        
        <?php while($row = $result->fetch_assoc()): ?>
            <?php 
                $qty = $_SESSION['cart'][$row['item_id']];
                $subtotal = $row['price'] * $qty;
                $total_price += $subtotal;
            ?>
            <div class="cart-item">
                <div class="cart-item-details">
                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                    <small>₹<?php echo $row['price']; ?> × <?php echo $qty; ?></small>
                </div>
                <div class="cart-item-price">₹<?php echo number_format($subtotal, 2); ?></div>
            </div>
        <?php endwhile; ?>

        <div class="total-box">
            <div class="total-row">
                <span>💰 Total to Pay:</span>
                <span>₹<?php echo number_format($total_price, 2); ?></span>
            </div>

            <label for="note">👨‍🍳 Note to Chef (Optional):</label>
            <textarea name="order_note" class="note-box" rows="2" placeholder="e.g. No onions, Extra spicy, Allergy info..."></textarea>
            
            <input type="hidden" name="total_amount" value="<?php echo $total_price; ?>">

            <button type="submit" class="pay-btn">✓ Confirm & Pay</button>
        </div>

    </form>

    <a href="clear_cart.php" class="clear-btn">🗑️ Clear Cart & Start Over</a>

</body>
</html>