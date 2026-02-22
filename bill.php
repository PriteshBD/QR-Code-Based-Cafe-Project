<?php
session_start();
include 'includes/db_connect.php';

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) {
    die('<h2 style="text-align:center; color:red; margin-top:50px;">Invalid Order ID</h2>');
}

// Fetch order details
$sql = "SELECT o.*, 
        GROUP_CONCAT(CONCAT(m.name, ' (', m.category, ')') SEPARATOR ', ') as items,
        GROUP_CONCAT(oi.quantity SEPARATOR ', ') as quantities,
        GROUP_CONCAT(oi.price SEPARATOR ', ') as prices
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN menu_items m ON oi.item_id = m.item_id
        WHERE o.order_id = $order_id
        GROUP BY o.order_id";

$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die('<h2 style="text-align:center; color:red; margin-top:50px;">Order not found</h2>');
}

$order = $result->fetch_assoc();

// Get item details for better formatting
$items_sql = "SELECT m.name, m.category, oi.quantity, oi.price, (oi.quantity * oi.price) as subtotal
              FROM order_items oi
              JOIN menu_items m ON oi.item_id = m.item_id
              WHERE oi.order_id = $order_id";
$items_result = $conn->query($items_sql);

// Calculate totals
$items_list = [];
$total_items = 0;
$subtotal = 0;
while ($item = $items_result->fetch_assoc()) {
    $items_list[] = $item;
    $total_items += $item['quantity'];
    $subtotal += $item['subtotal'];
}

$tax = $subtotal * 0.05; // 5% tax
$total = $subtotal + $tax;

// Handle email if requested
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = sanitize_email($_POST['email']);
    // Build the bill HTML for email
    $bill_html = "<h2>P&S Cafe - Bill</h2>";
    $bill_html .= "<p>Order #$order_id</p>";
    $bill_html .= "<table border='1'><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>";
    foreach ($items_list as $item) {
        $bill_html .= "<tr><td>" . htmlspecialchars($item['name']) . "</td><td>" . $item['quantity'] . "</td><td>₹" . number_format($item['price'], 2) . "</td><td>₹" . number_format($item['subtotal'], 2) . "</td></tr>";
    }
    $bill_html .= "</table>";
    $bill_html .= "<p>Subtotal: ₹" . number_format($subtotal, 2) . "</p>";
    $bill_html .= "<p>Tax (5%): ₹" . number_format($tax, 2) . "</p>";
    $bill_html .= "<p><strong>Total: ₹" . number_format($total, 2) . "</strong></p>";
    
    $subject = "Your Bill - P&S Cafe - Order #$order_id";
    $headers = "MIME-Version: 1.0" . "\r\n" . "Content-type: text/html; charset=UTF-8" . "\r\n";
    
    if (mail($email, $subject, $bill_html, $headers)) {
        echo '<div style="text-align:center; padding:20px; background:#c8e6c9; color:#2e7d32; border-radius:5px;">
                ✅ Bill sent to ' . htmlspecialchars($email) . '
              </div>';
    }
}

function sanitize_email($email) {
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill | Order #<?php echo $order_id; ?> - P&S Cafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 20px; }
        
        .bill-container {
            max-width: 500px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .bill-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .cafe-name { font-size: 1.8em; font-weight: bold; color: #ff9800; }
        .bill-title { font-size: 1.2em; margin: 10px 0; }
        .bill-date { font-size: 0.9em; color: #666; }
        
        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 0.95em;
        }
        .info-label { color: #666; }
        .info-value { font-weight: bold; }
        
        .items-section {
            margin: 20px 0;
            border-top: 1px dashed #999;
            border-bottom: 1px dashed #999;
            padding: 15px 0;
        }
        .section-title { font-weight: bold; margin-bottom: 10px; background: #f0f0f0; padding: 5px; }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.95em;
        }
        .item-name { flex: 1; }
        .item-qty { width: 30px; text-align: center; }
        .item-price { width: 60px; text-align: right; }
        
        .totals-section {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.95em;
        }
        .total-row.final {
            font-weight: bold;
            font-size: 1.1em;
            border-top: 1px solid #999;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .bill-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #999;
            font-size: 0.9em;
            color: #666;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-print {
            background: #2196f3;
            color: white;
        }
        .btn-print:hover {
            background: #1976d2;
        }
        .btn-email {
            background: #4caf50;
            color: white;
        }
        .btn-email:hover {
            background: #388e3c;
        }
        .btn-download {
            background: #ff9800;
            color: white;
        }
        .btn-download:hover {
            background: #e68900; 
        }
        
        @media print {
            body { background: white; padding: 0; }
            .action-buttons { display: none; }
            .bill-container { box-shadow: none; margin: 0; }
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
        }
        .close {
            color: #999;
            font-size: 2em;
            font-weight: bold;
            cursor: pointer;
            float: right;
        }
        .close:hover { color: #333; }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .modal-buttons button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="bill-header">
            <div class="cafe-name">☕ P&S CAFE</div>
            <div class="bill-title">BILL / RECEIPT</div>
            <div class="bill-date"><?php echo date('d-m-Y H:i:s'); ?></div>
        </div>
        
        <div class="order-info">
            <div>
                <div class="info-label">Order ID</div>
                <div class="info-value">#<?php echo $order_id; ?></div>
            </div>
            <div>
                <div class="info-label">Table No.</div>
                <div class="info-value"><?php echo $order['table_id']; ?></div>
            </div>
            <div>
                <div class="info-label">Status</div>
                <div class="info-value"><?php echo $order['order_status']; ?></div>
            </div>
            <div>
                <div class="info-label">Payment</div>
                <div class="info-value"><?php echo $order['payment_method']; ?></div>
            </div>
        </div>
        
        <div class="items-section">
            <div class="section-title">ITEMS ORDERED</div>
            <?php foreach ($items_list as $item): ?>
                <div class="item-row">
                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="item-qty">x<?php echo $item['quantity']; ?></div>
                    <div class="item-price">₹<?php echo number_format($item['price'], 0); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal</span>
                <span>₹<?php echo number_format($subtotal, 0); ?></span>
            </div>
            <div class="total-row">
                <span>Tax (5%)</span>
                <span>₹<?php echo number_format($tax, 0); ?></span>
            </div>
            <div class="total-row final">
                <span>TOTAL AMOUNT</span>
                <span>₹<?php echo number_format($total, 0); ?></span>
            </div>
        </div>
        
        <div class="bill-footer">
            <p>Thank you for your visit!</p>
            <p>Please visit us again</p>
        </div>
        
        <div class="action-buttons">
            <button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
            <button class="btn btn-email" onclick="showEmailModal()">📧 Email</button>
            <button class="btn btn-download" onclick="downloadPDF()">📥 Download</button>
        </div>
    </div>

    <div id="emailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEmailModal()">&times;</span>
            <h2>Send Bill via Email</h2>
            <form method="POST">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required style="width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:5px;">
                <div class="modal-buttons">
                    <button type="button" onclick="closeEmailModal()" style="background:#999; color:white;">Cancel</button>
                    <button type="submit" style="background:#4caf50; color:white;">Send</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showEmailModal() {
            document.getElementById('emailModal').style.display = 'block';
        }
        
        function closeEmailModal() {
            document.getElementById('emailModal').style.display = 'none';
        }
        
        function downloadPDF() {
            alert('PDF download feature coming soon!');
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('emailModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
