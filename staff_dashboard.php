<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: staff_login.php");
    exit();
}

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

// Fetch Active Orders (Pending, Cooking, Ready)
// Uses GROUP_CONCAT to list all items in one row per order
$sql = "SELECT o.*, GROUP_CONCAT(CONCAT(m.name, ' (x', oi.quantity, ')') SEPARATOR '<br>') as items 
        FROM orders o 
        JOIN order_items oi ON o.order_id = oi.order_id 
        JOIN menu_items m ON oi.item_id = m.item_id 
        WHERE o.order_status NOT IN ('Served', 'Rejected', 'Completed')
        GROUP BY o.order_id 
        ORDER BY o.created_at ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Kitchen Display | P&S Cafe</title>
    <meta http-equiv="refresh" content="10"> <!-- Auto refresh every 10s -->
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #222; color: white; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #444; padding-bottom: 10px; margin-bottom: 20px; }
        .order-card { background: #333; border: 1px solid #444; padding: 15px; margin-bottom: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        .items { color: #ff9800; font-weight: bold; margin: 10px 0; line-height: 1.4; }
        button { padding: 10px 15px; cursor: pointer; border: none; border-radius: 4px; font-weight: bold; font-size: 1em; }
        .btn-cook { background: #2196f3; color: white; }
        .btn-ready { background: #4caf50; color: white; }
        .btn-served { background: #757575; color: white; }
        input[type="text"] { padding: 8px; border-radius: 4px; border: none; margin-right: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>👨‍🍳 Kitchen Display System (KDS)</h1>
        <a href="logout.php" style="color: #ff5722; text-decoration: none; font-weight: bold;">Logout</a>
    </div>

    <?php while($row = $result->fetch_assoc()): ?>
        <div class="order-card">
            <div style="flex: 1;">
                <h3 style="margin:0;">Order #<?php echo $row['order_id']; ?> <span style="font-weight:normal; font-size:0.8em; color:#aaa;">(Table <?php echo $row['table_id']; ?>)</span></h3>
                <div class="items"><?php echo $row['items']; ?></div>
                <?php if(!empty($row['order_note'])): ?>
                    <div style="color: #ff5722; font-weight: bold;">⚠️ Note: <?php echo $row['order_note']; ?></div>
                <?php endif; ?>
                <small style="color:#aaa;">Status: <?php echo $row['order_status']; ?></small>
            </div>
            <div>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                    
                    <?php if($row['order_status'] == 'Pending'): ?>
                        <input type="text" name="est_time" placeholder="Time (e.g. 15m)" required size="10">
                        <button type="submit" name="status" value="Cooking" class="btn-cook">Start Cooking</button>
                    
                    <?php elseif($row['order_status'] == 'Cooking'): ?>
                        <button type="submit" name="status" value="Ready" class="btn-ready">Mark Ready</button>
                    
                    <?php elseif($row['order_status'] == 'Ready'): ?>
                        <button type="submit" name="status" value="Served" class="btn-served">Mark Served</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php endwhile; ?>

    <?php if($result->num_rows == 0): ?>
        <p style="text-align:center; color:#777; margin-top:50px;">No active orders. Time to clean! 🧹</p>
    <?php endif; ?>
</body>
</html>