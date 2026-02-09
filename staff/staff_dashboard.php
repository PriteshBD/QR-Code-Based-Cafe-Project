<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: staff_login.php");
    exit();
}

$staff_name = $_SESSION['staff_name'];
$staff_role = isset($_SESSION['staff_role']) ? $_SESSION['staff_role'] : 'Staff';

// Handle Status Updates
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $est_time = isset($_POST['est_time']) ? $_POST['est_time'] : NULL;
    
    $sql = "UPDATE orders SET order_status='$status'";
    if($est_time) {
        $sql .= ", estimated_time='$est_time'";
    }
    $sql .= " WHERE order_id='$order_id'";
    
    $conn->query($sql);
    header("Location: staff_dashboard.php");
    exit();
}

// Fetch Active Orders grouped by status
$sql = "SELECT o.*, GROUP_CONCAT(CONCAT(m.name, ' (x', oi.quantity, ')') SEPARATOR '|') as items 
        FROM orders o 
        JOIN order_items oi ON o.order_id = oi.order_id 
        JOIN menu_items m ON oi.item_id = m.item_id 
        WHERE o.order_status NOT IN ('Served', 'Rejected', 'Completed')
        GROUP BY o.order_id 
        ORDER BY FIELD(o.order_status, 'Pending', 'Cooking', 'Ready'), o.created_at ASC";
$result = $conn->query($sql);

// Get order counts by status
$stats = $conn->query("SELECT 
    SUM(CASE WHEN order_status='Pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN order_status='Cooking' THEN 1 ELSE 0 END) as cooking_count,
    SUM(CASE WHEN order_status='Ready' THEN 1 ELSE 0 END) as ready_count
    FROM orders 
    WHERE order_status NOT IN ('Served', 'Rejected', 'Completed')
    AND DATE(created_at) = CURDATE()")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Kitchen Display | P&S Cafe</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="15"> <!-- Auto refresh every 15s -->
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); 
            color: white; 
            min-height: 100vh;
            padding: 0;
        }
        
        /* Header */
        .top-bar {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .top-bar .brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .top-bar h1 {
            font-size: 1.5em;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.95em;
        }
        
        .user-info .welcome {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .logout-btn {
            background: #ff5722;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: #e64a19;
            transform: translateY(-2px);
        }
        
        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px 30px;
            background: rgba(0, 0, 0, 0.2);
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-card h3 {
            font-size: 2.5em;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .stat-card p {
            font-size: 0.9em;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-pending h3 { color: #ffc107; }
        .stat-cooking h3 { color: #2196f3; }
        .stat-ready h3 { color: #4caf50; }
        
        /* Orders Section */
        .orders-container {
            padding: 20px 30px;
        }
        
        .section-title {
            font-size: 1.2em;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        /* Order Card */
        .order-card {
            background: white;
            color: #333;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-left: 5px solid #ccc;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        .order-card.status-pending { border-left-color: #ffc107; }
        .order-card.status-cooking { border-left-color: #2196f3; }
        .order-card.status-ready { border-left-color: #4caf50; }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .order-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
        }
        
        .table-number {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 12px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 0.9em;
        }
        
        .order-time {
            font-size: 0.85em;
            color: #666;
            margin-bottom: 10px;
        }
        
        .items-list {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .item {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .item:last-child { border-bottom: none; }
        
        .item-name {
            font-weight: 600;
            color: #333;
        }
        
        .item-qty {
            background: #ff9800;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.85em;
            font-weight: bold;
        }
        
        .order-note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
            font-weight: bold;
            font-size: 0.9em;
        }
        
        .order-note:before {
            content: "⚠️ ";
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .status-badge.pending { background: #fff3cd; color: #856404; }
        .status-badge.cooking { background: #cfe2ff; color: #084298; }
        .status-badge.ready { background: #d1e7dd; color: #0f5132; }
        
        /* Action Forms */
        .action-form {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .action-form input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 0.95em;
        }
        
        .action-form button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.95em;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .action-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-cook { background: #2196f3; color: white; }
        .btn-cook:hover { background: #1976d2; }
        
        .btn-ready { background: #4caf50; color: white; }
        .btn-ready:hover { background: #388e3c; }
        
        .btn-served { background: #757575; color: white; }
        .btn-served:hover { background: #616161; }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .empty-state-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            opacity: 0.8;
            font-size: 1.1em;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr;
            }
            
            .top-bar {
                flex-direction: column;
                gap: 15px;
            }
            
            .action-form {
                flex-direction: column;
            }
            
            .action-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="brand">
            <h1>👨‍🍳 Kitchen Display System</h1>
        </div>
        <div class="user-info">
            <div class="welcome">
                Welcome, <strong><?php echo htmlspecialchars($staff_name); ?></strong> (<?php echo htmlspecialchars($staff_role); ?>)
            </div>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="stats-bar">
        <div class="stat-card stat-pending">
            <h3><?php echo $stats['pending_count'] ?: 0; ?></h3>
            <p>Pending Orders</p>
        </div>
        <div class="stat-card stat-cooking">
            <h3><?php echo $stats['cooking_count'] ?: 0; ?></h3>
            <p>Cooking Now</p>
        </div>
        <div class="stat-card stat-ready">
            <h3><?php echo $stats['ready_count'] ?: 0; ?></h3>
            <p>Ready to Serve</p>
        </div>
    </div>
    
    <div class="orders-container">

        <?php if($result->num_rows == 0): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🧹</div>
                <h3>All Caught Up!</h3>
                <p>No active orders at the moment. Time for a break!</p>
            </div>
        <?php else: ?>
            <div class="orders-grid">
                <?php while($row = $result->fetch_assoc()): 
                    $items_array = explode('|', $row['items']);
                    $status_class = strtolower($row['order_status']);
                    $time_ago = time() - strtotime($row['created_at']);
                    $minutes_ago = floor($time_ago / 60);
                ?>
                    <div class="order-card status-<?php echo $status_class; ?>">
                        <div class="order-header">
                            <div class="order-number">
                                Order #<?php echo $row['order_id']; ?>
                            </div>
                            <div class="table-number">
                                Table <?php echo $row['table_id']; ?>
                            </div>
                        </div>
                        
                        <div class="order-time">
                            ⏰ Placed <?php echo $minutes_ago; ?> minute<?php echo $minutes_ago != 1 ? 's' : ''; ?> ago
                        </div>
                        
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo strtoupper($row['order_status']); ?>
                        </span>
                        
                        <div class="items-list">
                            <?php foreach($items_array as $item): 
                                preg_match('/(.+) \(x(\d+)\)/', $item, $matches);
                                if(count($matches) >= 3):
                                    $item_name = trim($matches[1]);
                                    $item_qty = $matches[2];
                            ?>
                                <div class="item">
                                    <span class="item-name"><?php echo htmlspecialchars($item_name); ?></span>
                                    <span class="item-qty">×<?php echo $item_qty; ?></span>
                                </div>
                            <?php endif; endforeach; ?>
                        </div>
                        
                        <?php if(!empty($row['order_note'])): ?>
                            <div class="order-note">
                                Note: <?php echo htmlspecialchars($row['order_note']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="action-form">
                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                            <input type="hidden" name="update_status" value="1">
                            
                            <?php if($row['order_status'] == 'Pending'): ?>
                                <input type="text" name="est_time" placeholder="Est. time (e.g. 15 mins)" required>
                                <button type="submit" name="status" value="Cooking" class="btn-cook">▶️ Start Cooking</button>
                            
                            <?php elseif($row['order_status'] == 'Cooking'): ?>
                                <button type="submit" name="status" value="Ready" class="btn-ready" style="flex: 1;">✅ Mark Ready</button>
                            
                            <?php elseif($row['order_status'] == 'Ready'): ?>
                                <button type="submit" name="status" value="Served" class="btn-served" style="flex: 1;">🍽️ Mark Served</button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>