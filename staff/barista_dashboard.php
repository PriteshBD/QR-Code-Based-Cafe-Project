<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in and is a Barista
if (!isset($_SESSION['staff_id']) || $_SESSION['staff_role'] !== 'Barista') {
    header("Location: staff_login.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];
$staff_name = $_SESSION['staff_name'];

// Fetch pending beverage orders
$orders_sql = "SELECT DISTINCT o.order_id, o.table_id, o.order_status, o.created_at, o.estimated_time,
               GROUP_CONCAT(CONCAT(m.name, ' x', oi.quantity) SEPARATOR ', ') as items
               FROM orders o
               JOIN order_items oi ON o.order_id = oi.order_id
               JOIN menu_items m ON oi.item_id = m.item_id
               WHERE m.item_type = 'Beverage' AND o.order_status IN ('Pending', 'Cooking')
               GROUP BY o.order_id
               ORDER BY o.created_at ASC";
$orders_result = $conn->query($orders_sql);

// Update order status when barista marks as ready
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'] ?? 'Ready';
    
    $update_sql = "UPDATE orders SET order_status = '$new_status' WHERE order_id = $order_id";
    if ($conn->query($update_sql)) {
        $_SESSION['success'] = "Order #$order_id marked as $new_status!";
    }
    header("Location: barista_dashboard.php");
    exit();
}

// Get total pending orders
$total_sql = "SELECT COUNT(DISTINCT o.order_id) as total FROM orders o
              JOIN order_items oi ON o.order_id = oi.order_id
              JOIN menu_items m ON oi.item_id = m.item_id
              WHERE m.item_type = 'Beverage' AND o.order_status IN ('Pending', 'Cooking')";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$pending_count = $total_row['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barista Dashboard | P&S Cafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        
        .header {
            background: #8b4513;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 { font-size: 1.8em; margin-bottom: 5px; }
        .header-info { font-size: 0.95em; opacity: 0.9; }
        
        .container { max-width: 1200px; margin: 20px auto; padding: 0 15px; }
        
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 { font-size: 2em; color: #8b4513; margin-bottom: 5px; }
        .stat-card p { color: #666; font-size: 0.95em; }
        
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .order-card {
            background: white;
            border-left: 5px solid #8b4513;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .order-id { font-size: 1.3em; font-weight: bold; color: #333; }
        .table-no { background: #8b4513; color: white; padding: 5px 10px; border-radius: 5px; font-weight: bold; }
        
        .order-items {
            margin: 15px 0;
            padding: 10px;
            background: #fafafa;
            border-left: 3px solid #ffc107;
            border-radius: 4px;
        }
        .order-items h4 { color: #333; margin-bottom: 8px; font-size: 0.9em; }
        .items-list { font-size: 0.95em; line-height: 1.6; color: #555; }
        
        .order-meta {
            display: flex;
            gap: 15px;
            margin: 12px 0;
            font-size: 0.9em;
        }
        .meta-item { flex: 1; }
        .meta-label { color: #999; font-size: 0.85em; }
        .meta-value { color: #333; font-weight: bold; }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            margin-top: 10px;
        }
        .status-pending { background: #ffe0b2; color: #e65100; }
        .status-cooking { background: #fff9c4; color: #f57f17; }
        .status-ready { background: #c8e6c9; color: #2e7d32; }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            font-size: 0.95em;
        }
        .btn-cooking {
            background: #fff3e0;
            color: #e65100;
            border: 2px solid #8b4513;
        }
        .btn-cooking:hover {
            background: #8b4513;
            color: white;
        }
        .btn-ready {
            background: #e8f5e9;
            color: #2e7d32;
            border: 2px solid #4caf50;
        }
        .btn-ready:hover {
            background: #4caf50;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }
        .empty-state h2 { font-size: 1.8em; margin-bottom: 10px; }
        
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            color: #8b4513;
            border: 2px solid #8b4513;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .logout-btn:hover { background: #8b4513; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logout-btn" onclick="if(confirm('Logout?')) window.location='staff_login.php'">Logout</div>
        <h1>☕ Barista Dashboard</h1>
        <div class="header-info">Welcome, <strong><?php echo htmlspecialchars($staff_name); ?></strong></div>
    </div>

    <div class="container">
        <div class="stats-bar">
            <div class="stat-card">
                <h3><?php echo $pending_count; ?></h3>
                <p>Pending Beverages</p>
            </div>
            <div class="stat-card">
                <h3>☕</h3>
                <p>Making Drinks</p>
            </div>
            <div class="stat-card">
                <h3>⏱️</h3>
                <p>Check Timing</p>
            </div>
        </div>

        <?php if ($pending_count > 0): ?>
            <div class="orders-grid">
                <?php while($order = $orders_result->fetch_assoc()): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-id">#<?php echo $order['order_id']; ?></span>
                            <span class="table-no">Table <?php echo $order['table_id']; ?></span>
                        </div>
                        
                        <div class="order-items">
                            <h4>☕ Beverages to Prepare:</h4>
                            <div class="items-list">
                                <?php echo htmlspecialchars($order['items']); ?>
                            </div>
                        </div>
                        
                        <div class="order-meta">
                            <div class="meta-item">
                                <div class="meta-label">Order Time</div>
                                <div class="meta-value"><?php echo date('H:i', strtotime($order['created_at'])); ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Est. Time</div>
                                <div class="meta-value"><?php echo $order['estimated_time'] ? $order['estimated_time'] . ' min' : '5 min'; ?></div>
                            </div>
                        </div>
                        
                        <span class="status-badge <?php echo 'status-' . strtolower($order['order_status']); ?>">
                            <?php echo $order['order_status']; ?>
                        </span>
                        
                        <form method="POST" class="action-buttons">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <button type="submit" name="status" value="Cooking" class="btn btn-cooking">🔄 Preparing</button>
                            <button type="submit" name="status" value="Ready" class="btn btn-ready">✅ Ready</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>✨ All Caught Up!</h2>
                <p>No pending beverage orders right now</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        setInterval(function() {
            location.reload();
        }, 15000); // Auto-refresh every 15 seconds
    </script>
</body>
</html>
