<?php
session_start();
include 'includes/db_connect.php';

// Get order ID
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : (isset($_SESSION['last_order_id']) ? $_SESSION['last_order_id'] : 0);

if (!$order_id) {
    die('<h2 style="text-align:center; color:red; margin-top:50px;">Invalid Order ID</h2>');
}

// Fetch order details
$sql = "SELECT * FROM orders WHERE order_id = $order_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die('<h2 style="text-align:center; color:red; margin-top:50px;">Order not found</h2>');
}

$order = $result->fetch_assoc();
$amount = $order['total_amount'];
$table_id = $order['table_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment | P&S Cafe</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .payment-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .payment-header h1 {
            font-size: 1.8em;
            margin-bottom: 10px;
        }

        .payment-header p {
            font-size: 0.95em;
            opacity: 0.9;
        }

        .payment-body {
            padding: 30px;
        }

        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95em;
        }

        .summary-row label {
            color: #666;
            font-weight: 500;
        }

        .summary-row.total {
            border-top: 2px solid #ddd;
            padding-top: 12px;
            margin-top: 12px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .payment-methods {
            margin: 25px 0;
        }

        .payment-methods h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1em;
        }

        .method-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .payment-method {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }

        .payment-method:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateY(-2px);
        }

        .payment-method input[type="radio"] {
            display: none;
        }

        .payment-method input[type="radio"]:checked + label {
            color: #667eea;
            font-weight: bold;
        }

        .payment-method label {
            display: block;
            font-size: 1.5em;
            margin-bottom: 5px;
            cursor: pointer;
        }

        .payment-method .method-name {
            font-size: 0.85em;
            color: #666;
            cursor: pointer;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9em;
            color: #1976d2;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        @media (max-width: 480px) {
            .payment-container {
                margin: 0;
            }

            .payment-header {
                padding: 20px;
            }

            .payment-content {
                padding: 20px;
            }

            .button-group {
                grid-template-columns: 1fr;
            }

            .proceed-btn {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>💳 Payment Confirmation</h1>
            <p>Secure Payment Processing</p>
        </div>

        <div class="payment-content">
            <div class="order-info">
                <label>Order ID</label>
                <div class="value">#<?php echo $order_id; ?></div>
                
                <label>Table Number</label>
                <div class="value"><?php echo $table_id; ?></div>
            </div>

            <div class="amount-section">
                <div class="amount-label">Total Amount</div>
                <div class="amount">₹<?php echo number_format($amount, 2); ?></div>
            </div>

            <div class="payment-method">
                <div class="method">💰 Cash Payment</div>
                <p style="font-size: 0.9em; margin-top: 5px;">Payment to be collected at counter</p>
            </div>

            <form id="paymentForm" method="POST" action="verify_payment.php">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                
                <div class="button-group">
                    <button type="button" class="cancel-btn" onclick="goBack()">❌ Cancel</button>
                    <button type="submit" class="proceed-btn">✅ Confirm Payment</button>
                </div>
            </form>

            <div class="security-note">
                <strong>✓ Secure</strong> - Your payment information is safe
            </div>

            <div class="info-box">
                <strong>ℹ️ Payment Instructions:</strong>
                <p style="margin-top: 10px;">Please pay ₹<?php echo number_format($amount, 2); ?> at the counter. Click "Confirm Payment" to mark this order as paid.</p>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }

        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show confirmation
            if (confirm('✅ Confirm payment of ₹<?php echo number_format($amount, 2); ?> for Order #<?php echo $order_id; ?>?')) {
                // Submit form
                fetch('verify_payment.php', {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Payment Recorded Successfully!\nYour order is being prepared.');
                        window.location.href = 'track_order.php';
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Payment processing error. Please try again.');
                });
            }
        });
    </script>
</body>
</html>
