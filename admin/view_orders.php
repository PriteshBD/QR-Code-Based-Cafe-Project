<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Filter options
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Build query based on filter
$where_clause = "1=1";
if ($filter != 'all') {
    $filter_escaped = $conn->real_escape_string($filter);
    $where_clause .= " AND o.order_status = '$filter_escaped'";
}
if ($date_filter) {
    $where_clause .= " AND DATE(o.created_at) = '$date_filter'";
}

$sql = "SELECT o.*, GROUP_CONCAT(CONCAT(m.name, ' (x', oi.quantity, ')') SEPARATOR ', ') as items
        FROM orders o 
        LEFT JOIN order_items oi ON o.order_id = oi.order_id 
        LEFT JOIN menu_items m ON oi.item_id = m.item_id 
        WHERE $where_clause
        GROUP BY o.order_id 
        ORDER BY o.created_at DESC
        LIMIT 100";
$result = $conn->query($sql);

// Get order statistics
$stats = $conn->query("SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN payment_status='Paid' THEN total_amount ELSE 0 END) as total_revenue,
    SUM(CASE WHEN order_status='Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN order_status='Cooking' THEN 1 ELSE 0 END) as cooking,
    SUM(CASE WHEN order_status='Ready' THEN 1 ELSE 0 END) as ready
    FROM orders 
    WHERE DATE(created_at) = '$date_filter'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management | P&S Cafe</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: #f4f6f9; 
            margin: 0;
            padding: 20px;
        }
        .header { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 { margin: 0 0 10px 0; font-size: 2em; color: #007bff; }
        .stat-card p { margin: 0; color: #666; }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            padding: 12px; 
            border-bottom: 1px solid #ddd; 
            text-align: left; 
        }
        th { 
            background: #343a40; 
            color: white; 
            position: sticky;
            top: 0;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: bold;
            display: inline-block;
        }
        .badge-pending { background: #ffc107; color: #333; }
        .badge-cooking { background: #2196f3; color: white; }
        .badge-ready { background: #4caf50; color: white; }
        .badge-served { background: #757575; color: white; }
        .badge-rejected { background: #f44336; color: white; }
        .badge-paid { background: #28a745; color: white; }
        .badge-unpaid { background: #dc3545; color: white; }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9em;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        select, input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">📦 Orders Management</h1>
        <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $stats['total_orders']; ?></h3>
            <p>Total Orders</p>
        </div>
        <div class="stat-card">
            <h3>₹<?php echo number_format($stats['total_revenue'], 2); ?></h3>
            <p>Revenue Today</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['pending']; ?></h3>
            <p>Pending</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['cooking']; ?></h3>
            <p>Cooking</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['ready']; ?></h3>
            <p>Ready</p>
        </div>
    </div>

    <div class="filters">
        <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center; width: 100%;">
            <label>
                Status:
                <select name="filter" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter=='all'?'selected':''; ?>>All Orders</option>
                    <option value="Pending" <?php echo $filter=='Pending'?'selected':''; ?>>Pending</option>
                    <option value="Cooking" <?php echo $filter=='Cooking'?'selected':''; ?>>Cooking</option>
                    <option value="Ready" <?php echo $filter=='Ready'?'selected':''; ?>>Ready</option>
                    <option value="Served" <?php echo $filter=='Served'?'selected':''; ?>>Served</option>
                </select>
            </label>
            <label>
                Date:
                <input type="date" name="date" value="<?php echo $date_filter; ?>" onchange="this.form.submit()">
            </label>
        </form>
    </div>

    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Table</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                        <td>Table <?php echo $order['table_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['items']); ?></td>
                        <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($order['order_status']); ?>">
                                <?php echo $order['order_status']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $order['payment_status']=='Paid'?'badge-paid':'badge-unpaid'; ?>">
                                <?php echo $order['payment_status']; ?>
                            </span>
                        </td>
                        <td><?php echo date('H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                            No orders found for the selected filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
