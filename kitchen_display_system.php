<?php
session_start();
include 'includes/db_connect.php';

// KDS doesn't require login - large screen view for kitchen
// Can be accessed via: /kitchen_display_system.php

// Get all pending cooking orders
$orders_sql = "SELECT DISTINCT o.order_id, o.table_id, o.created_at, o.order_status,
               GROUP_CONCAT(CONCAT(m.name, ' (', m.category, ') x', oi.quantity, ' - ', 
               CASE WHEN m.is_veg = 1 THEN '🌱 Veg' ELSE '🍗 Non-Veg' END) SEPARATOR ' | ') as items,
               TIME_FORMAT(TIMEDIFF(NOW(), o.created_at), '%i:%s') as wait_time
               FROM orders o
               JOIN order_items oi ON o.order_id = oi.order_id
               JOIN menu_items m ON oi.item_id = m.item_id
               WHERE m.item_type = 'Cooking' AND o.order_status IN ('Pending', 'Cooking')
               GROUP BY o.order_id
               ORDER BY CASE 
                   WHEN o.order_status = 'Pending' THEN 1
                   WHEN o.order_status = 'Cooking' THEN 2
                   ELSE 3
               END, o.created_at ASC";

$orders_result = $conn->query($orders_sql);

// Get statistics
$total_sql = "SELECT 
              COUNT(DISTINCT o.order_id) as total_orders,
              SUM(CASE WHEN o.order_status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
              SUM(CASE WHEN o.order_status = 'Cooking' THEN 1 ELSE 0 END) as cooking_count
              FROM orders o
              JOIN order_items oi ON o.order_id = oi.order_id
              JOIN menu_items m ON oi.item_id = m.item_id
              WHERE m.item_type = 'Cooking' AND o.order_status IN ('Pending', 'Cooking')";

$total_result = $conn->query($total_sql);
$totals = $total_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="5">
    <title>Kitchen Display System | P&S Cafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #1a1a1a;
            color: #fff;
            min-height: 100vh;
            padding: 20px;
            overflow: hidden;
        }
        
        .kds-container {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .kds-header {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }
        
        .kds-header h1 {
            font-size: 2.5em;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .kds-stats {
            display: flex;
            gap: 20px;
            font-size: 1.3em;
        }
        
        .stat-item {
            background: rgba(255,255,255,0.2);
            padding: 12px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stat-number {
            font-size: 1.5em;
            font-weight: bold;
        }
        
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .order-card {
            background: #2a2a2a;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            border-top: 5px solid;
            position: relative;
            overflow: hidden;
        }
        
        .order-card.pending {
            border-top-color: #ff6b35;
            background: repeating-linear-gradient(
                45deg,
                #2a2a2a,
                #2a2a2a 10px,
                #3a2a1a 10px,
                #3a2a1a 20px
            );
            box-shadow: 0 0 20px rgba(255, 107, 53, 0.3);
        }
        
        .order-card.cooking {
            border-top-color: #ffc107;
            background: repeating-linear-gradient(
                45deg,
                #2a2a2a,
                #2a2a2a 10px,
                #3a3a1a 10px,
                #3a3a1a 20px
            );
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.3);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #444;
            padding-bottom: 12px;
        }
        
        .order-id {
            font-size: 2.5em;
            font-weight: bold;
            color: #ff6b35;
        }
        
        .table-number {
            background: #ff6b35;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.3em;
        }
        
        .wait-time {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.9em;
        }
        
        .wait-time.critical {
            background: #ff4444;
            color: white;
            font-weight: bold;
        }
        
        .order-items {
            margin: 15px 0;
            padding: 15px;
            background: rgba(0,0,0,0.3);
            border-radius: 8px;
            border-left: 3px solid #ffc107;
        }
        
        .order-items h3 {
            font-size: 1.1em;
            color: #ffc107;
            margin-bottom: 10px;
        }
        
        .item {
            padding: 8px 0;
            font-size: 1em;
            line-height: 1.5;
            color: #ddd;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-qty {
            background: #ff6b35;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            margin: 0 5px;
            display: inline-block;
            min-width: 30px;
            text-align: center;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.2em;
            margin: 10px 0;
        }
        
        .status-pending {
            background: #ff6b35;
            color: white;
            animation: pulse 1.5s infinite;
        }
        
        .status-cooking {
            background: #ffc107;
            color: #1a1a1a;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #2a2a2a;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        
        .empty-state h2 {
            font-size: 2em;
            margin-bottom: 10px;
            color: #ffc107;
        }
        
        .empty-state p {
            font-size: 1.2em;
            color: #aaa;
        }
        
        /* Responsive design for multiple screen sizes */
        @media (max-width: 1200px) {
            .orders-grid {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr;
            }
            
            .kds-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .kds-header h1 {
                font-size: 1.8em;
            }
            
            .kds-stats {
                width: 100%;
                justify-content: space-around;
            }
            
            .order-id {
                font-size: 2em;
            }
        }
        
        /* Large screen optimization (23"+ monitors) */
        @media (min-width: 1920px) {
            body {
                font-size: 1.2em;
            }
            
            .orders-grid {
                grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
            }
            
            .order-card {
                padding: 30px;
            }
            
            .order-id {
                font-size: 3.5em;
            }
            
            .kds-header h1 {
                font-size: 3.5em;
            }
            
            .item {
                font-size: 1.3em;
            }
        }
        
        .footer {
            text-align: center;
            color: #666;
            font-size: 0.9em;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="kds-container">
        <div class="kds-header">
            <h1>👨‍🍳 KITCHEN DISPLAY SYSTEM</h1>
            <div class="kds-stats">
                <div class="stat-item">
                    <span>📋 Total:</span>
                    <span class="stat-number"><?php echo $totals['total_orders'] ?? 0; ?></span>
                </div>
                <div class="stat-item">
                    <span>⏳ Pending:</span>
                    <span class="stat-number"><?php echo $totals['pending_count'] ?? 0; ?></span>
                </div>
                <div class="stat-item">
                    <span>🔥 Cooking:</span>
                    <span class="stat-number"><?php echo $totals['cooking_count'] ?? 0; ?></span>
                </div>
            </div>
        </div>
        
        <?php if ($orders_result->num_rows > 0): ?>
            <div class="orders-grid">
                <?php while($order = $orders_result->fetch_assoc()): ?>
                    <div class="order-card <?php echo strtolower($order['order_status']); ?>">
                        <div class="order-header">
                            <div class="order-id">
                                #<?php echo str_pad($order['order_id'], 3, '0', STR_PAD_LEFT); ?>
                            </div>
                            <div>
                                <div class="table-number">Table <?php echo $order['table_id']; ?></div>
                            </div>
                        </div>
                        
                        <div class="order-items">
                            <h3>📋 ITEMS TO PREPARE:</h3>
                            <?php foreach (array_filter(explode(' | ', $order['items'])) as $item): ?>
                                <div class="item">
                                    <?php 
                                    preg_match('/(.+?)\s*x(\d+)/', $item, $matches);
                                    $item_name = $matches[1] ?? $item;
                                    $qty = $matches[2] ?? 1;
                                    ?>
                                    <span class="item-qty">×<?php echo $qty; ?></span>
                                    <?php echo htmlspecialchars($item_name); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                            <span class="status-badge <?php echo 'status-' . strtolower($order['order_status']); ?>">
                                <?php echo $order['order_status']; ?>
                            </span>
                            <div class="wait-time <?php echo (intval(explode(':', $order['wait_time'])[0]) > 15) ? 'critical' : ''; ?>">
                                ⏱️ <?php echo $order['wait_time']; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>✨ ALL ORDERS COMPLETE!</h2>
                <p>No pending or cooking orders at the moment</p>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            Last updated: <?php echo date('H:i:s'); ?> | Auto-refreshing every 5 seconds
        </div>
    </div>
</body>
</html>
