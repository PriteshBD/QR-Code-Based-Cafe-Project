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
    <link rel="stylesheet" href="styles/theme.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .payment-container {
            background: var(--panel);
            border-radius: 14px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .payment-header {
            background: linear-gradient(135deg, var(--accent-2) 0%, var(--accent) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .payment-header h1 {
            font-size: 1.8em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .payment-header p {
            font-size: 0.95em;
            opacity: 0.9;
        }

        .payment-body {
            padding: 30px;
        }

        .order-summary {
            background: var(--panel-2);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95em;
            color: var(--ink);
        }

        .summary-row label {
            color: var(--muted);
            font-weight: 600;
        }

        .summary-row.total {
            border-top: 2px solid var(--border);
            padding-top: 12px;
            margin-top: 12px;
            font-weight: 700;
            font-size: 1.1em;
            color: var(--accent);
        }

        .payment-methods {
            margin: 25px 0;
        }

        .payment-methods h3 {
            color: var(--ink);
            margin-bottom: 15px;
            font-size: 1em;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .method-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .payment-method {
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--panel-2);
        }

        .payment-method:hover {
            border-color: var(--accent-2);
            background: rgba(42, 157, 143, 0.05);
            transform: translateY(-2px);
        }

        .payment-method input[type="radio"] {
            display: none;
        }

        .payment-method input[type="radio"]:checked + label {
            color: var(--accent-2);
            font-weight: 700;
        }

        .payment-method label {
            display: block;
            font-size: 1.5em;
            margin-bottom: 5px;
            cursor: pointer;
        }

        .payment-method .method-name {
            font-size: 0.85em;
            color: var(--muted);
            cursor: pointer;
        }

        .info-box {
            background: rgba(37, 99, 235, 0.1);
            border-left: 4px solid var(--info);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9em;
            color: var(--info);
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
            border-radius: 10px;
            font-weight: 700;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Manrope', sans-serif;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-2) 0%, var(--accent) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(42, 157, 143, 0.3);
        }

        .btn-secondary {
            background: var(--panel-2);
            color: var(--ink);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--border);
            border-color: var(--muted);
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
                flex-direction: column;
            }

            .proceed-btn {
                width: 100%;
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

        <div class="payment-body">
            <div class="order-summary">
                <div class="summary-row">
                    <label>Order ID:</label>
                    <strong>#<?php echo $order_id; ?></strong>
                </div>
                
                <div class="summary-row">
                    <label>Table Number:</label>
                    <strong><?php echo $table_id; ?></strong>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, var(--accent-2) 0%, var(--accent) 100%); color: white; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center;">
                <div style="font-size: 0.9em; opacity: 0.9; margin-bottom: 8px;">Total Amount</div>
                <div style="font-size: 2em; font-weight: 700;">₹<?php echo number_format($amount, 2); ?></div>
            </div>

            <div class="order-summary">
                <div style="font-weight: 700; text-align: center; color: var(--ink); padding: 10px;">💰 Cash Payment at Counter</div>
                <p style="font-size: 0.9em; margin: 10px 0; text-align: center; color: var(--muted);">Payment will be collected at the counter</p>
            </div>

            <form id="paymentForm" method="POST" action="verify_payment.php">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                
                <div class="button-group" style="margin-top: 30px;">
                    <button type="button" class="btn btn-secondary" onclick="goBack()">❌ Cancel</button>
                    <button type="submit" class="btn btn-primary proceed-btn">✅ Confirm Payment</button>
                </div>
            </form>

            <div class="info-box" style="margin-top: 20px;">
                <strong>ℹ️ Payment Instructions:</strong>
                <p style="margin-top: 10px;">Please pay ₹<?php echo number_format($amount, 2); ?> at the counter. Click "Confirm Payment" to proceed.</p>
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
                        window.location.href = 'menu.php';
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
