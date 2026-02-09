<?php
session_start();
include 'db_connect.php';

// Simple authentication: allow if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Configuration
$base_url = "http://localhost/QR_Code_Based_Cafe_Project/menu.php?table_id=";
$num_tables = isset($_GET['tables']) ? (int)$_GET['tables'] : 20; // Default 20 tables
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
