<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in and is a Manager
if (!isset($_SESSION['staff_id']) || $_SESSION['staff_role'] !== 'Manager') {
    header("Location: staff_login.php");
    exit();
}

$staff_id = $_SESSION['staff_id'];
$staff_name = $_SESSION['staff_name'];

// Fetch pending service requests (calls from tables)
$requests_sql = "SELECT sr.request_id, sr.table_id, sr.request_type, sr.status, sr.request_time
                 FROM service_requests sr
                 WHERE sr.status = 'Pending'
                 ORDER BY sr.request_time DESC";
$requests_result = $conn->query($requests_sql);

// Mark request as resolved when manager responds
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    
    $update_sql = "UPDATE service_requests SET status = 'Resolved' WHERE request_id = $request_id";
    if ($conn->query($update_sql)) {
        $_SESSION['success'] = "Request #$request_id marked as resolved!";
    }
    header("Location: manager_dashboard.php");
    exit();
}

// Get stats
$pending_requests = 0;
if ($requests_result->num_rows > 0) {
    $pending_requests = $requests_result->num_rows;
    $requests_result->data_seek(0);
}

// Get pending orders count
$orders_count_sql = "SELECT COUNT(DISTINCT order_id) as total FROM orders WHERE order_status IN ('Pending', 'Cooking')";
$orders_count_result = $conn->query($orders_count_sql);
$orders_count = $orders_count_result->fetch_assoc()['total'];

// Get revenue today
$revenue_sql = "SELECT SUM(total_amount) as revenue FROM orders WHERE DATE(created_at) = CURDATE() AND order_status = 'Served'";
$revenue_result = $conn->query($revenue_sql);
$revenue_row = $revenue_result->fetch_assoc();
$daily_revenue = $revenue_row['revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | P&S Cafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        
        .header {
            background: #d32f2f;
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
        .stat-card h3 { font-size: 2em; color: #d32f2f; margin-bottom: 5px; }
        .stat-card p { color: #666; font-size: 0.95em; }
        
        .section-title {
            font-size: 1.4em;
            color: #333;
            margin: 25px 0 15px 0;
            border-left: 4px solid #d32f2f;
            padding-left: 12px;
        }
        
        .requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .request-card {
            background: white;
            border-left: 5px solid #ff6f00;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .request-id { font-size: 1.3em; font-weight: bold; color: #333; }
        .table-no { background: #d32f2f; color: white; padding: 5px 10px; border-radius: 5px; font-weight: bold; }
        
        .request-details {
            margin: 15px 0;
            padding: 10px;
            background: #fff3e0;
            border-left: 3px solid #ff6f00;
            border-radius: 4px;
        }
        .request-type { font-size: 1.1em; font-weight: bold; color: #e65100; margin-bottom: 5px; }
        .request-time { font-size: 0.9em; color: #666; }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            background: #ffe0b2;
            color: #e65100;
            margin-top: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            font-size: 0.95em;
            background: #4caf50;
            color: white;
        }
        .btn:hover {
            background: #388e3c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
            background: white;
            border-radius: 8px;
            margin-top: 20px;
        }
        .empty-state h2 { font-size: 1.8em; margin-bottom: 10px; }
        
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            color: #d32f2f;
            border: 2px solid #d32f2f;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .logout-btn:hover { background: #d32f2f; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logout-btn" onclick="if(confirm('Logout?')) window.location='staff_login.php'">Logout</div>
        <h1>👔 Manager Dashboard</h1>
        <div class="header-info">Welcome, <strong><?php echo htmlspecialchars($staff_name); ?></strong></div>
    </div>

    <div class="container">
        <div class="stats-bar">
            <div class="stat-card">
                <h3><?php echo $pending_requests; ?></h3>
                <p>Pending Service Calls</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $orders_count; ?></h3>
                <p>Orders in Progress</p>
            </div>
            <div class="stat-card">
                <h3>₹<?php echo number_format($daily_revenue, 0); ?></h3>
                <p>Today's Revenue</p>
            </div>
        </div>

        <h2 class="section-title">🔔 Customer Service Requests</h2>

        <?php if ($pending_requests > 0): ?>
            <div class="requests-grid">
                <?php while($request = $requests_result->fetch_assoc()): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <span class="request-id">#<?php echo $request['request_id']; ?></span>
                            <span class="table-no">Table <?php echo $request['table_id']; ?></span>
                        </div>
                        
                        <div class="request-details">
                            <div class="request-type">
                                <?php 
                                $icons = [
                                    'Call Waiter' => '🔔',
                                    'Complaint' => '😞',
                                    'Request' => '👋',
                                    'Bill' => '💵'
                                ];
                                $icon = $icons[$request['request_type']] ?? '❓';
                                echo $icon . ' ' . htmlspecialchars($request['request_type']);
                                ?>
                            </div>
                            <div class="request-time">
                                Request at: <?php echo date('H:i:s', strtotime($request['request_time'])); ?>
                            </div>
                        </div>
                        
                        <span class="status-badge">⏳ Waiting</span>
                        
                        <form method="POST" class="action-buttons">
                            <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                            <button type="submit" class="btn">✅ Resolved</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>✨ All Good!</h2>
                <p>No pending service requests</p>
            </div>
        <?php endif; ?>

        <h2 class="section-title">📊 Quick Stats</h2>
        <div class="stats-bar">
            <div class="stat-card">
                <h3>👨‍🍳</h3>
                <p>Chef is cooking</p>
            </div>
            <div class="stat-card">
                <h3>☕</h3>
                <p>Barista making drinks</p>
            </div>
            <div class="stat-card">
                <h3>🚶</h3>
                <p>Waiters serving</p>
            </div>
        </div>
    </div>

    <script>
        setInterval(function() {
            location.reload();
        }, 10000); // Auto-refresh every 10 seconds
    </script>
</body>
</html>
