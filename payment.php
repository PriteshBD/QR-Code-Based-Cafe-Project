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

// Razorpay Keys (Replace with your actual keys from Razorpay Dashboard)
// Get from: https://dashboard.razorpay.com/settings/api-keys
// For testing: Use provided test keys
$razorpay_key = 'rzp_test_1IfboxQnxM4G6w'; // Replace with your actual key
$razorpay_secret = 'nxrHnLj1E0sQ8L0jFwxX1234'; // Replace with your actual secret (keep secret!)

// Generate unique receipt ID
$receipt_id = "PS_CAFE_" . $order_id . "_" . time();

// Payment data
$payment_data = array(
    'key' => $razorpay_key,
    'amount' => $amount * 100, // Convert to paise (smallest unit in INR)
    'currency' => 'INR',
    'order_id' => $order_id,
    'description' => "P&S Cafe - Order #$order_id",
    'prefill' => array(
        'contact' => '9099099090',
        'email' => 'customer@pscafe.com',
    ),
    'notes' => array(
        'table_id' => $table_id,
        'order_id' => $order_id,
    )
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment | P&S Cafe</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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

        .security-badges {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 0.85em;
            color: #666;
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        @media (max-width: 480px) {
            .payment-container {
                margin: 0;
            }

            .payment-header {
                padding: 20px;
            }

            .payment-body {
                padding: 20px;
            }

            .method-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>☕ P&S Cafe</h1>
            <p>Secure Payment</p>
        </div>

        <div class="payment-body">
            <div class="order-summary">
                <div class="summary-row">
                    <label>Order ID</label>
                    <span>#<?php echo $order_id; ?></span>
                </div>
                <div class="summary-row">
                    <label>Table Number</label>
                    <span><?php echo $table_id; ?></span>
                </div>
                <div class="summary-row">
                    <label>Amount</label>
                    <span>₹<?php echo number_format($amount, 0); ?></span>
                </div>
                <div class="summary-row total">
                    <label>Total Due</label>
                    <span>₹<?php echo number_format($amount, 0); ?></span>
                </div>
            </div>

            <div class="info-box">
                🔒 Your payment is secure and encrypted with Razorpay
            </div>

            <form id="paymentForm">
                <div class="payment-methods">
                    <h3>💳 Choose Payment Method</h3>
                    <div class="method-grid">
                        <div class="payment-method">
                            <input type="radio" id="card" name="payment_method" value="card" checked>
                            <label for="card">💳</label>
                            <div class="method-name">Debit/Credit Card</div>
                        </div>

                        <div class="payment-method">
                            <input type="radio" id="upi" name="payment_method" value="upi">
                            <label for="upi">📱</label>
                            <div class="method-name">UPI</div>
                        </div>

                        <div class="payment-method">
                            <input type="radio" id="wallet" name="payment_method" value="wallet">
                            <label for="wallet">💰</label>
                            <div class="method-name">Wallet</div>
                        </div>

                        <div class="payment-method">
                            <input type="radio" id="netbanking" name="payment_method" value="netbanking">
                            <label for="netbanking">🏦</label>
                            <div class="method-name">Net Banking</div>
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <a href="track_order.php" class="btn btn-secondary">← Back</a>
                    <button type="button" class="btn btn-primary" onclick="processPayment()">Pay ₹<?php echo number_format($amount, 0); ?></button>
                </div>
            </form>

            <div class="security-badges">
                <div class="badge">🔒 SSL Encrypted</div>
                <div class="badge">✓ Verified</div>
                <div class="badge">🛡️ Safe</div>
            </div>
        </div>
    </div>

    <script>
        function processPayment() {
            const options = {
                "key": "<?php echo $razorpay_key; ?>",
                "amount": <?php echo $amount * 100; ?>,
                "currency": "INR",
                "name": "P&S Cafe",
                "description": "Order #<?php echo $order_id; ?>",
                "image": "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext x='50' y='70' font-size='70' text-anchor='middle'%3E☕%3C/text%3E%3C/svg%3E",
                "order_id": "<?php echo $receipt_id; ?>",
                "handler": function (response) {
                    verifyPayment(response.razorpay_payment_id, response.razorpay_order_id, response.razorpay_signature);
                },
                "prefill": {
                    "name": "Customer",
                    "contact": "9099099090",
                    "email": "customer@pscafe.com"
                },
                "notes": {
                    "table_id": "<?php echo $table_id; ?>",
                    "order_id": "<?php echo $order_id; ?>"
                },
                "theme": {
                    "color": "#667eea"
                },
                "modal": {
                    "ondismiss": function() {
                        alert("Payment window closed. Please try again.");
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();
        }

        function verifyPayment(paymentId, orderId, signature) {
            // Send payment verification to server
            fetch('verify_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'order_id=<?php echo $order_id; ?>&payment_id=' + paymentId + '&order_id_razorpay=' + orderId + '&signature=' + signature
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Payment Successful!\nYour order is being prepared.');
                    window.location.href = 'track_order.php';
                } else {
                    alert('❌ Payment verification failed. Please contact support.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Payment processing error. Please try again.');
            });
        }

        // Allow Enter key to trigger payment
        document.getElementById('paymentForm').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processPayment();
            }
        });
    </script>
</body>
</html>
