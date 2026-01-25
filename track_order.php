<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['last_order_id'])) {
    echo "No active order.";
    exit();
}

$order_id = $_SESSION['last_order_id'];

// Fetch latest status
$sql = "SELECT * FROM orders WHERE order_id = $order_id";
$result = $conn->query($sql);
$order = $result->fetch_assoc();

// UPI Configuration (Replace with your actual UPI ID for the demo)
$my_upi_id = "yourname@upi"; 
$amount = $order['total_amount'];
$upi_link = "upi://pay?pa=$my_upi_id&pn=PS_Cafe&am=$amount&tn=Order-$order_id&cu=INR";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?php echo $order_id; ?></title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 30px; background: #f8f9fa; }
        .status-box { padding: 20px; border-radius: 10px; color: white; margin-bottom: 20px; }
        
        /* Dynamic Colors based on Status */
        .pending { background: #ff9800; } /* Orange */
        .cooking { background: #2196F3; } /* Blue */
        .ready { background: #4CAF50; }   /* Green */
        .rejected { background: #f44336; } /* Red */

        .pay-btn {
            background: #28a745; color: white; text-decoration: none;
            padding: 15px 30px; border-radius: 30px; font-weight: bold; font-size: 1.2em;
            display: inline-block; margin-top: 10px;
        }
    </style>
</head>
<body>

    <h1>📦 Order #<?php echo $order_id; ?></h1>

    <?php
        $status = strtolower($order['order_status']);
        $css_class = ($status == 'served') ? 'ready' : $status; // Handle 'served' same as 'ready' visually
    ?>
    
    <div class="status-box <?php echo $css_class; ?>">
        <h2>Status: <?php echo strtoupper($order['order_status']); ?></h2>
        
        <?php if ($order['order_status'] == 'Pending'): ?>
            <p>Waiting for kitchen confirmation...</p>
            <div style="background:white; color:black; padding:15px; margin-top:10px; border-radius:8px;">
                <strong>Payment Required: ₹<?php echo $amount; ?></strong><br>
                <small>Click below to pay via GPay / PhonePe</small><br><br>
                <a href="<?php echo $upi_link; ?>" class="pay-btn">Pay Now ₹<?php echo $amount; ?></a>
                
                <br><br>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($upi_link); ?>" alt="Scan to Pay">
            </div>

        <?php elseif ($order['order_status'] == 'Cooking'): ?>
            <h1>🔥 <?php echo $order['estimated_time']; ?></h1>
            <p>Your food is being prepared!</p>

        <?php elseif ($order['order_status'] == 'Ready'): ?>
            <h1>✅ FOOD IS READY!</h1>
            <p>Please collect it from the counter.</p>
            
        <?php elseif ($order['order_status'] == 'Rejected'): ?>
            <h1>❌ Order Canceled</h1>
            <p>Please visit the counter for a refund.</p>
        <?php endif; ?>
    </div>

    <p><small>Page auto-refreshes every 5 seconds...</small></p>
    <a href="menu.php" style="color:#555;">Back to Menu</a>

</body>
</html>