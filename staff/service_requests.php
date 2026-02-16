<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: staff_login.php");
    exit();
}

// Handle acknowledge service request
if (isset($_POST['acknowledge_request'])) {
    $request_id = (int)$_POST['request_id'];
    $conn->query("UPDATE service_requests SET status='Acknowledged' WHERE request_id=$request_id");
    exit();
}

// Handle complete service request
if (isset($_POST['complete_request'])) {
    $request_id = (int)$_POST['request_id'];
    $conn->query("UPDATE service_requests SET status='Completed' WHERE request_id=$request_id");
    exit();
}

$staff_id = $_SESSION['staff_id'];
$staff_name = $_SESSION['staff_name'];

// Fetch pending service requests
$service_requests = $conn->query("
    SELECT * FROM service_requests 
    WHERE status IN ('Pending', 'Acknowledged') 
    ORDER BY status DESC, request_time ASC
");

// Fetch stats
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status='Acknowledged' THEN 1 ELSE 0 END) as acknowledged,
        SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) as completed
    FROM service_requests 
    WHERE DATE(request_time) = CURDATE()
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Requests | P&S Cafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
        }
        
        .header {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .header h1 {
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            padding: 20px 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .stat-card p {
            font-size: 0.85em;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .container {
            padding: 20px 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            font-size: 1.3em;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .requests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .request-card {
            background: white;
            color: #333;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-left: 5px solid #ff9800;
            transition: all 0.3s;
        }
        
        .request-card.pending {
            border-left-color: #ff5722;
            background: linear-gradient(135deg, #ff5722 0%, rgba(255, 87, 34, 0.1) 100%);
        }
        
        .request-card.acknowledged {
            border-left-color: #2196f3;
            background: linear-gradient(135deg, #2196f3 0%, rgba(33, 150, 243, 0.1) 100%);
        }
        
        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }
        
        .table-badge {
            background: #2196f3;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #ffebee;
            color: #c62828;
        }
        
        .status-acknowledged {
            background: #e3f2fd;
            color: #1565c0;
        }
        
        .request-time {
            font-size: 0.85em;
            color: #666;
            margin-bottom: 10px;
        }
        
        .request-type {
            font-size: 1em;
            color: #333;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
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
            transition: all 0.3s;
            font-size: 0.9em;
        }
        
        .btn-acknowledge {
            background: #2196f3;
            color: white;
        }
        
        .btn-acknowledge:hover {
            background: #1976d2;
        }
        
        .btn-complete {
            background: #4caf50;
            color: white;
        }
        
        .btn-complete:hover {
            background: #388e3c;
        }
        
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
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .requests-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔔 Service Requests</h1>
        <div class="header-actions">
            <a href="staff_dashboard.php" class="back-btn">← Back to Orders</a>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $stats['total_requests'] ?: 0; ?></h3>
            <p>Total Today</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['pending'] ?: 0; ?></h3>
            <p>Pending</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['acknowledged'] ?: 0; ?></h3>
            <p>Acknowledged</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['completed'] ?: 0; ?></h3>
            <p>Completed</p>
        </div>
    </div>
    
    <div class="container">
        <h2 class="section-title">Active Requests</h2>
        
        <?php if($service_requests->num_rows == 0): ?>
            <div class="empty-state">
                <div class="empty-state-icon">✨</div>
                <h3>All Requests Handled!</h3>
                <p>No pending service requests at the moment</p>
            </div>
        <?php else: ?>
            <div class="requests-grid">
                <?php while($req = $service_requests->fetch_assoc()): 
                    $time_ago = time() - strtotime($req['request_time']);
                    $minutes_ago = floor($time_ago / 60);
                    $class = strtolower($req['status']);
                ?>
                    <div class="request-card <?php echo $class; ?>">
                        <div class="request-header">
                            <div class="table-badge">Table <?php echo $req['table_id']; ?></div>
                            <span class="status-badge status-<?php echo $class; ?>">
                                <?php echo strtoupper($req['status']); ?>
                            </span>
                        </div>
                        
                        <div class="request-time">
                            ⏰ <?php echo $minutes_ago > 0 ? $minutes_ago . ' min ago' : 'Just now'; ?>
                        </div>
                        
                        <div class="request-type">
                            📢 <?php echo htmlspecialchars($req['request_type']); ?>
                        </div>
                        
                        <div class="action-buttons">
                            <?php if($req['status'] == 'Pending'): ?>
                                <form method="POST" style="flex: 1;">
                                    <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">
                                    <button type="submit" name="acknowledge_request" class="btn btn-acknowledge">✓ Acknowledge</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if($req['status'] != 'Completed'): ?>
                                <form method="POST" style="flex: 1;">
                                    <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">
                                    <button type="submit" name="complete_request" class="btn btn-complete">✓ Complete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
