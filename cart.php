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
    <link rel='stylesheet' href='styles/theme.css'>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .empty-cart {
            background: var(--panel);
            padding: 40px;
            border-radius: 14px;
            text-align: center;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            max-width: 400px;
            width: 100%;
        }
        .empty-cart h2 {
            font-size: 2em;
            margin-bottom: 15px;
            color: var(--ink);
        }
        .empty-cart p {
            color: var(--muted);
            margin-bottom: 25px;
            font-size: 1.05em;
        }
        .empty-cart a {
            display: inline-block;
            background: var(--accent-2);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            letter-spacing: 0.3px;
        }
        .empty-cart a:hover {
            background: #22867b;
            box-shadow: 0 5px 15px rgba(42, 157, 143, 0.3);
            transform: translateY(-2px);
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
    <link rel="stylesheet" href="styles/theme.css">
    <style>
        body { 
            padding: 20px;
            padding-bottom: 100px;
        }
        h2 { 
            text-align: center; 
            margin-bottom: 20px;
            color: var(--ink);
            font-size: 1.8em;
        }
        .cart-item { 
            background: var(--panel);
            padding: 15px; 
            border-radius: 10px; 
            margin-bottom: 12px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        .cart-item:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .cart-item-details strong {
            display: block;
            margin-bottom: 5px;
            color: var(--ink);
            font-size: 1em;
            font-weight: 700;
        }
        .cart-item-details small {
            color: var(--muted);
            font-size: 0.9em;
        }
        .cart-item-price {
            font-weight: 700;
            color: var(--accent);
            font-size: 1.1em;
            min-width: 80px;
            text-align: right;
        }
        .total-box { 
            background: var(--panel);
            border: 2px solid var(--accent-2);
            padding: 20px; 
            border-radius: 12px; 
            margin-top: 20px;
            box-shadow: var(--shadow);
        }
        .total-box label {
            display: block;
            margin-top: 15px;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--ink);
            font-size: 0.95em;
            letter-spacing: 0.3px;
        }
        .note-box { 
            width: 100%; 
            padding: 10px; 
            margin-bottom: 15px;
            border: 1px solid var(--border); 
            border-radius: 8px; 
            font-family: 'Manrope', sans-serif;
            background: var(--panel-2);
            color: var(--ink);
            resize: vertical;
        }
        .note-box:focus {
            outline: none;
            border-color: var(--accent-2);
            box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.15);
        }
        .pay-btn {
            background: var(--accent-2); 
            color: white; 
            border: none; 
            padding: 15px; 
            width: 100%;
            font-size: 1.1em; 
            font-weight: 700; 
            border-radius: 10px; 
            cursor: pointer;
            transition: all 0.3s;
            letter-spacing: 0.3px;
            font-family: 'Manrope', sans-serif;
        }
        .pay-btn:hover {
            background: #22867b;
            box-shadow: 0 10px 25px rgba(42, 157, 143, 0.3);
            transform: translateY(-2px);
        }
        .pay-btn:active {
            transform: translateY(0px);
        }
        .clear-btn { 
            color: var(--danger); 
            text-decoration: none; 
            font-size: 0.9em; 
            display: block; 
            text-align: center; 
            margin-top: 15px;
            padding: 10px;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: var(--shadow);
            font-weight: 600;
            transition: all 0.3s;
            letter-spacing: 0.3px;
        }
        .clear-btn:hover {
            background: rgba(230, 57, 70, 0.1);
            border-color: var(--danger);
        }
        .back-btn {
            display: inline-block;
            background: var(--muted);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 15px;
            font-size: 0.9em;
            font-weight: 600;
            transition: all 0.3s;
            letter-spacing: 0.3px;
        }
        .back-btn:hover {
            background: #574b50;
            transform: translateY(-2px);
        }
        .total-row {
            display: flex; 
            justify-content: space-between; 
            font-size: 1.3em; 
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
            color: var(--ink);
            font-weight: 700;
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

            <label for="note">👨‍🍳 Special Instructions (Optional):</label>
            <textarea name="order_note" class="note-box" rows="2" placeholder="e.g. No onions, Extra spicy, Allergy info..."></textarea>
            
            <input type="hidden" name="total_amount" value="<?php echo $total_price; ?>">

            <button type="submit" class="pay-btn">✓ Confirm & Pay</button>
        </div>

    </form>

    <a href="clear_cart.php" class="clear-btn">🗑️ Clear Cart & Start Over</a>

</body>
</html>