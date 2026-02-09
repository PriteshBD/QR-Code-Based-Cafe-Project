<?php
session_start();
include '../includes/db_connect.php';

// Simple authentication: allow if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Auto-detect server IP address
$server_ip = $_SERVER['SERVER_ADDR'];
if ($server_ip == '::1' || $server_ip == '127.0.0.1') {
    // Try to get actual IP if localhost
    $server_ip = gethostbyname(gethostname());
}

// Allow manual IP override
$custom_ip = isset($_GET['ip']) ? $_GET['ip'] : '';
$use_ip = $custom_ip ?: $server_ip;

// Configuration
$base_url = "http://" . $use_ip . "/QR_Code_Based_Cafe_Project/menu.php?table_id=";
$num_tables = isset($_GET['tables']) ? (int)$_GET['tables'] : 20; // Default 20 tables
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'ip'; // 'localhost' or 'ip'

if ($mode == 'localhost') {
    $base_url = "http://localhost/QR_Code_Based_Cafe_Project/menu.php?table_id=";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Table QR Codes | P&S Cafe</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: #f4f6f9; 
            padding: 20px; 
        }
        .header { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 30px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .qr-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); 
            gap: 20px; 
        }
        .qr-card { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            text-align: center; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        .qr-card h3 { 
            margin: 0 0 15px 0; 
            color: #333; 
            font-size: 1.5em;
        }
        .qr-card img { 
            max-width: 100%; 
            height: auto; 
            margin-bottom: 10px;
        }
        .qr-card .url { 
            font-size: 0.75em; 
            color: #888; 
            word-break: break-all;
            margin-top: 5px;
        }
        .btn { 
            padding: 10px 20px; 
            background: #007bff; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        
        .info-panel {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-panel h3 {
            margin: 0 0 15px 0;
            color: #1976d2;
        }
        
        .info-panel .step {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 3px solid #4caf50;
        }
        
        .info-panel .step strong {
            color: #4caf50;
        }
        
        .config-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .config-box label {
            font-weight: bold;
            margin-right: 10px;
        }
        
        .toggle-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .toggle-buttons a {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        
        .active-mode {
            background: #28a745;
            color: white;
        }
        
        .inactive-mode {
            background: #e0e0e0;
            color: #666;
        }
        
        @media print {
            body { background: white; }
            .header, .no-print { display: none; }
            .qr-card { 
                box-shadow: none; 
                border: 2px solid #333;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header no-print">
        <div>
            <h1 style="margin: 0;">🎯 Table QR Code Generator</h1>
            <p style="margin: 5px 0 0 0; color: #666;">Generate QR codes for customers to scan and order</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-success">🖨️ Print All</button>
            <a href="admin_dashboard.php" class="btn">← Back to Dashboard</a>
        </div>
    </div>

    <div class="no-print">
        <div class="config-box">
            <h3 style="margin: 0 0 10px 0; color: #856404;">⚙️ Configuration</h3>
            <p style="margin: 0 0 10px 0;">
                <strong>Current Mode:</strong> 
                <?php if($mode == 'localhost'): ?>
                    <span style="color: #dc3545;">Localhost Mode</span> (Desktop/PC only)
                <?php else: ?>
                    <span style="color: #28a745;">Network Mode</span> (Mobile & Desktop)
                <?php endif; ?>
            </p>
            <p style="margin: 0 0 10px 0;">
                <strong>Current URL:</strong> <code><?php echo htmlspecialchars($base_url); ?>X</code>
            </p>
            <div class="toggle-buttons">
                <a href="?mode=ip&tables=<?php echo $num_tables; ?>" 
                   class="<?php echo $mode != 'localhost' ? 'active-mode' : 'inactive-mode'; ?>">
                    📱 Mobile Access (IP: <?php echo $use_ip; ?>)
                </a>
                <a href="?mode=localhost&tables=<?php echo $num_tables; ?>" 
                   class="<?php echo $mode == 'localhost' ? 'active-mode' : 'inactive-mode'; ?>">
                    💻 Localhost Only
                </a>
            </div>
        </div>

        <div class="info-panel">
            <h3>📱 How to Use QR Codes on Mobile Phones</h3>
            
            <div class="step">
                <strong>Step 1:</strong> Make sure your mobile phone and computer are on the <strong>same WiFi network</strong>
            </div>
            
            <div class="step">
                <strong>Step 2:</strong> Switch to <strong>"📱 Mobile Access"</strong> mode above (if not already selected)
            </div>
            
            <div class="step">
                <strong>Step 3:</strong> Print the QR codes or keep this page open
            </div>
            
            <div class="step">
                <strong>Step 4:</strong> Open your phone's camera app and point it at any QR code
            </div>
            
            <div class="step">
                <strong>Step 5:</strong> Tap the notification to open the menu page on your phone
            </div>
            
            <p style="margin: 15px 0 5px 0; color: #1976d2;">
                <strong>💡 Pro Tip:</strong> Test with your own phone first by scanning one QR code before printing all!
            </p>
            
            <p style="margin: 10px 0 0 0; font-size: 0.9em; color: #555;">
                <strong>⚠️ Important:</strong> Localhost mode only works on this computer. 
                For mobile phones, always use Mobile Access mode which uses IP address: <code><?php echo $use_ip; ?></code>
            </p>
        </div>
    </div>

    <div class="qr-grid">
        <?php for($table = 1; $table <= $num_tables; $table++): ?>
            <?php 
                $menu_url = $base_url . $table;
                $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($menu_url);
            ?>
            <div class="qr-card">
                <h3>Table <?php echo $table; ?></h3>
                <img src="<?php echo $qr_api_url; ?>" alt="Table <?php echo $table; ?> QR Code">
                <div style="margin-top: 10px; font-weight: bold; color: #ff9800;">
                    Scan to Order
                </div>
                <div class="url no-print"><?php echo $menu_url; ?></div>
            </div>
        <?php endfor; ?>
    </div>

    <div style="text-align: center; margin-top: 40px;" class="no-print">
        <form action="" method="GET" style="display: inline-block;">
            <label for="tables">Generate for </label>
            <input type="number" name="tables" id="tables" value="<?php echo $num_tables; ?>" min="1" max="100" style="width: 60px; padding: 5px;">
            <label> tables</label>
            <button type="submit" class="btn">Generate</button>
        </form>
    </div>

</body>
</html>
