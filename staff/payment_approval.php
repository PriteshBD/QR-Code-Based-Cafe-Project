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

// Handle payment approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action']; // 'approve', 'reject', 'cash_paid'
    
    if ($action === 'approve') {
        // Mark payment as confirmed (online payment received)
        $update_sql = "UPDATE orders SET payment_status = 'Confirmed' WHERE order_id = $order_id";
        $conn->query($update_sql);
        $_SESSION['success'] = "Payment approved for Order #$order_id";
    } elseif ($action === 'reject') {
        // Payment rejected, reset to pending
        $update_sql = "UPDATE orders SET payment_status = 'Pending', payment_id = NULL WHERE order_id = $order_id";
        $conn->query($update_sql);
        $_SESSION['error'] = "Payment rejected for Order #$order_id";
    } elseif ($action === 'cash_paid') {
        // Cash payment received by manager
        $update_sql = "UPDATE orders SET payment_status = 'Confirmed', payment_method = 'Cash' WHERE order_id = $order_id";
        $conn->query($update_sql);
        $_SESSION['success'] = "Cash payment received for Order #$order_id";
    }
    
    header("Location: payment_approval.php");
    exit();
}

// Fetch pending payments
$pending_sql = "SELECT o.order_id, o.table_id, o.total_amount, o.payment_status, o.payment_method, 
                       o.created_at, o.order_status,
                       GROUP_CONCAT(CONCAT(m.name, ' x', oi.quantity) SEPARATOR ', ') as items
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN menu_items m ON oi.item_id = m.item_id
                WHERE o.payment_status IN ('Pending', 'Paid')
                GROUP BY o.order_id
                ORDER BY o.created_at DESC";
$pending_result = $conn->query($pending_sql);

// Get statistics
$stats_sql = "SELECT 
              SUM(CASE WHEN payment_status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
              SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) as paid_count,
              SUM(CASE WHEN payment_status = 'Pending' THEN total_amount ELSE 0 END) as pending_amount,
              SUM(CASE WHEN payment_status = 'Paid' THEN total_amount ELSE 0 END) as paid_amount,
              SUM(CASE WHEN payment_status = 'Confirmed' AND DATE(created_at) = CURDATE() THEN total_amount ELSE 0 END) as today_confirmed
              FROM orders 
              WHERE payment_status IN ('Pending', 'Paid', 'Confirmed')";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="10">
    <title>Payment Approval | P&S Cafe Manager</title>
    <link rel="stylesheet" href="../admin/admin_styles.css">
    <style>
        body { 
            font-family: 'Manrope', sans-serif;
            background: #1a1a1a;
            min-height: 100vh;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #000;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .header h1 { font-size: 1.8em; margin-bottom: 5px; }
        .header-info { font-size: 0.95em; opacity: 0.9; }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid white;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.3); }
        
        .container { max-width: 1200px; margin: 0 auto; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 { font-size: 2em; color: #667eea; margin-bottom: 8px; }
        .stat-card p { color: #666; font-size: 0.9em; }
        .stat-value { color: #333; font-weight: bold; font-size: 1.1em; }
        
        .payments-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.4em;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .payment-card {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 20px;
            align-items: center;
        }
        
        .payment-details h3 {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 8px;
        }
        
        .payment-info {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.8em;
            color: #999;
            font-weight: bold;
        }
        
        .info-value {
            font-size: 1.1em;
            color: #333;
            font-weight: bold;
        }
        
        .items-list {
            font-size: 0.9em;
            color: #666;
            line-height: 1.6;
            max-height: 60px;
            overflow-y: auto;
        }
        
        .payment-status {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            text-align: center;
        }
        
        .status-pending { background: #ffe0b2; color: #e65100; }
        .status-paid { background: #c5e1a5; color: #558b2f; }
        .status-confirmed { background: #c8e6c9; color: #2e7d32; }
        .status-cash { background: #ffccbc; color: #d84315; }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9em;
            width: 100%;
        }
        
        .btn-approve {
            background: #4caf50;
            color: white;
        }
        
        .btn-approve:hover {
            background: #388e3c;
            transform: translateY(-2px);
        }
        
        .btn-cash {
            background: #ff9800;
            color: white;
        }
        
        .btn-cash:hover {
            background: #e68900;
            transform: translateY(-2px);
        }
        
        .btn-reject {
            background: #f44336;
            color: white;
        }
        
        .btn-reject:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            animation: slideDown 0.3s ease;
        }
        
        .alert-success {
            background: #c8e6c9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }
        
        .alert-error {
            background: #ffcdd2;
            color: #c62828;
            border-left: 4px solid #f44336;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state h2 { font-size: 1.5em; margin-bottom: 10px; }
        
        @media (max-width: 768px) {
            .payment-card {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
            }
            
            .action-buttons {
                flex-direction: row;
            }
            
            .btn {
                width: auto;
                flex: 1;
            }
        }
    </style>
</head>
<body data-staff-role="Manager">
    <div class="container">
        <div class="header">
            <div>
                <h1>💳 Payment Approval Center</h1>
                <div class="header-info">Welcome, <strong><?php echo htmlspecialchars($staff_name); ?></strong></div>
            </div>
            <button class="logout-btn" onclick="if(confirm('Logout?')) window.location='staff_login.php'">Logout</button>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($_SESSION['success']); ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">❌ <?php echo htmlspecialchars($_SESSION['error']); ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>⏳</h3>
                <p>Pending Payments</p>
                <div class="stat-value"><?php echo $stats['pending_count'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>💵</h3>
                <p>Pending Amount</p>
                <div class="stat-value">₹<?php echo number_format($stats['pending_amount'] ?? 0, 0); ?></div>
            </div>
            <div class="stat-card">
                <h3>✅</h3>
                <p>Paid (Awaiting Approval)</p>
                <div class="stat-value"><?php echo $stats['paid_count'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>💰</h3>
                <p>Today's Confirmed</p>
                <div class="stat-value">₹<?php echo number_format($stats['today_confirmed'] ?? 0, 0); ?></div>
            </div>
        </div>
        
        <div class="payments-section">
            <h2 class="section-title">📋 Pending & Verified Payments</h2>
            
            <?php if ($pending_result->num_rows > 0): ?>
                <?php while($payment = $pending_result->fetch_assoc()): ?>
                    <div class="payment-card">
                        <div class="payment-details">
                            <h3>Order #<?php echo $payment['order_id']; ?> • Table <?php echo $payment['table_id']; ?></h3>
                            <div class="items-list">
                                <strong>Items:</strong> <?php echo htmlspecialchars($payment['items']); ?>
                            </div>
                            <div class="payment-info">
                                <div class="info-item">
                                    <span class="info-label">Order Time</span>
                                    <span class="info-value"><?php echo date('H:i:s', strtotime($payment['created_at'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Order Status</span>
                                    <span class="info-value"><?php echo $payment['order_status']; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="payment-status">
                            <div class="status-badge <?php echo 'status-' . strtolower($payment['payment_status']); ?>">
                                <?php echo $payment['payment_status']; ?>
                            </div>
                            <div style="font-size: 0.9em; color: #666;">
                                <strong>Method:</strong> <?php echo $payment['payment_method']; ?>
                            </div>
                            <div style="font-size: 1.3em; font-weight: bold; color: #667eea; margin-top: 8px;">
                                ₹<?php echo number_format($payment['total_amount'], 0); ?>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <?php if ($payment['payment_status'] === 'Paid'): ?>
                                <!-- Online payment received - Manager confirms -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $payment['order_id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-approve">✅ Confirm Payment</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $payment['order_id']; ?>">
                                    <button type="submit" name="action" value="reject" class="btn btn-reject">❌ Reject & Reset</button>
                                </form>
                            <?php elseif ($payment['payment_status'] === 'Pending'): ?>
                                <!-- Pending payment - Cash or awaiting online -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $payment['order_id']; ?>">
                                    <button type="submit" name="action" value="cash_paid" class="btn btn-cash" onclick="return confirm('💵 Mark cash payment received from customer?')">💵 Cash Received</button>
                                </form>
                                <div style="padding: 10px; background: #fff3cd; border-radius: 5px; text-align: center; font-size: 0.85em;">
                                    ⏳ Waiting for payment...
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h2>✨ All payments processed!</h2>
                    <p>No pending or paid payments to review</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Notification System -->
    <script src="../js/notifications.js"></script>
    
    <script>
        setInterval(function() {
            location.reload();
        }, 10000); // Auto-refresh every 10 seconds
    </script>
</body>
</html>
