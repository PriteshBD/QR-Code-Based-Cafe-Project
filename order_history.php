<?php
session_start();
include 'includes/db_connect.php';

// Temporary order history - accessed via QR code, no login required
// Orders are shown for 2 hours after completion, then automatically hidden

$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : (isset($_SESSION['table_id']) ? $_SESSION['table_id'] : 0);

if (!$table_id) {
    $_SESSION['table_id'] = $table_id = 1; // Default for testing
}

// Fetch orders from last 2 hours only (auto-expire after that)
// Show completed orders for current table
$order_sql = "SELECT o.order_id, o.total_amount, o.created_at, o.order_status, o.payment_status,
              TIMEDIFF(NOW(), o.updated_at) as time_since_completion,
              GROUP_CONCAT(CONCAT(m.name, ' (', m.category, ') x', oi.quantity) SEPARATOR ', ') as items,
              GROUP_CONCAT(oi.price * oi.quantity SEPARATOR ',') as item_totals
              FROM orders o
              JOIN order_items oi ON o.order_id = oi.order_id
              JOIN menu_items m ON oi.item_id = m.item_id
              WHERE o.table_id = $table_id 
              AND o.order_status = 'Served'
              AND TIMESTAMPDIFF(HOUR, o.updated_at, NOW()) < 2
              GROUP BY o.order_id
              ORDER BY o.created_at DESC
              LIMIT 10";

$order_result = $conn->query($order_sql);

// Get table info
$table_info_sql = "SELECT COUNT(*) as total_orders, SUM(total_amount) as total_spent
                   FROM orders
                   WHERE table_id = $table_id";
$table_info_result = $conn->query($table_info_sql);
$table_info = $table_info_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders | P&S Cafe</title>
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
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            text-align: center;
        }

        .header h1 {
            font-size: 2em;
            color: #333;
            margin-bottom: 10px;
        }

        .header-info {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .info-label {
            color: #999;
            font-size: 0.9em;
            font-weight: bold;
        }

        .info-value {
            color: #667eea;
            font-size: 1.5em;
            font-weight: bold;
        }

        .table-badge {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .orders-container {
            display: grid;
            gap: 15px;
        }

        .order-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 4px solid #4caf50;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .order-id {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
        }

        .order-time {
            color: #999;
            font-size: 0.9em;
        }

        .order-items {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .order-items h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 0.95em;
        }

        .item-list {
            list-style: none;
            font-size: 0.9em;
            color: #666;
            line-height: 1.8;
        }

        .item-list li {
            display: flex;
            justify-content: space-between;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
            padding: 12px;
            background: #fff3cd;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .status-served {
            background: #c8e6c9;
            color: #2e7d32;
        }

        .status-paid {
            background: #c5e1a5;
            color: #558b2f;
        }

        .expiring-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #856404;
        }

        .expiring-notice strong {
            display: block;
            margin-bottom: 5px;
        }

        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .empty-state h2 {
            font-size: 1.5em;
            color: #999;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #bbb;
            margin-bottom: 20px;
        }

        .back-btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        .footer {
            text-align: center;
            color: white;
            margin-top: 20px;
            font-size: 0.9em;
            opacity: 0.8;
        }

        .order-total-row {
            display: flex;
            justify-content: space-between;
        }

        @media (max-width: 600px) {
            .header-info {
                flex-direction: column;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .order-total {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>☕ Your Order History</h1>
            <div class="header-info">
                <div class="info-item">
                    <span class="info-label">Table Number</span>
                    <span class="table-badge">#<?php echo $table_id; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Orders</span>
                    <span class="info-value"><?php echo $table_info['total_orders']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Spent</span>
                    <span class="info-value">₹<?php echo number_format($table_info['total_spent'] ?? 0, 0); ?></span>
                </div>
            </div>
        </div>

        <div class="expiring-notice">
            <strong>⏰ Auto-Expiring History</strong>
            <p>Your order history is displayed for 2 hours after completion. Older orders are automatically removed to keep your data private and not require login.</p>
        </div>

        <div class="orders-container">
            <?php if ($order_result->num_rows > 0): ?>
                <?php while($order = $order_result->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                                <div class="order-time"><?php echo date('M d, H:i', strtotime($order['created_at'])); ?></div>
                            </div>
                            <div>
                                <span class="status-badge status-served">✅ Served</span>
                                <span class="status-badge status-paid" style="margin-left: 8px;">
                                    <?php echo $order['payment_status']; ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-items">
                            <h4>📋 Items Ordered:</h4>
                            <ul class="item-list">
                                <?php 
                                $items = explode(', ', $order['items']);
                                foreach ($items as $item): 
                                ?>
                                    <li>
                                        <span><?php echo htmlspecialchars($item); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="order-total">
                            <span>Total Amount</span>
                            <span>₹<?php echo number_format($order['total_amount'], 0); ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h2>📭 No Order History</h2>
                    <p>Your completed orders will appear here for the next 2 hours</p>
                    <a href="menu.php" class="back-btn">← Back to Menu</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>💡 Tip: This history is temporary and doesn't require login. It automatically disappears after 2 hours to protect your privacy.</p>
            <p style="margin-top: 10px;">Need help? Contact the staff or return to <a href="menu.php" style="color: white; text-decoration: underline;">menu</a></p>
        </div>
    </div>
</body>
</html>
