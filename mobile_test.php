<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Access Test | P&S Cafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .success-icon {
            font-size: 4em;
            margin-bottom: 10px;
        }
        
        .success h2 {
            color: #155724;
            margin: 10px 0;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .info-box h3 {
            color: #1976d2;
            margin-bottom: 15px;
        }
        
        .info-item {
            background: white;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .info-item strong {
            color: #555;
        }
        
        .info-item code {
            background: #f5f5f5;
            padding: 5px 10px;
            border-radius: 4px;
            color: #e91e63;
            font-family: 'Courier New', monospace;
        }
        
        .device-info {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .device-info h3 {
            color: #856404;
            margin-bottom: 15px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            flex: 1;
            padding: 15px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 600px) {
            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📱 Mobile Access Test</h1>
        
        <div class="success">
            <div class="success-icon">✅</div>
            <h2>Connection Successful!</h2>
            <p>You can access this page, which means mobile QR codes will work!</p>
        </div>
        
        <div class="info-box">
            <h3>🌐 Your Connection Details</h3>
            
            <div class="info-item">
                <strong>Your IP Address:</strong>
                <code><?php echo $_SERVER['REMOTE_ADDR']; ?></code>
            </div>
            
            <div class="info-item">
                <strong>Server IP:</strong>
                <code><?php 
                    $server_ip = $_SERVER['SERVER_ADDR'];
                    if ($server_ip == '::1' || $server_ip == '127.0.0.1') {
                        $server_ip = gethostbyname(gethostname());
                    }
                    echo $server_ip; 
                ?></code>
            </div>
            
            <div class="info-item">
                <strong>Device:</strong>
                <code><?php 
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    if (strpos($user_agent, 'Mobile') !== false) {
                        echo '📱 Mobile Device';
                    } elseif (strpos($user_agent, 'Tablet') !== false) {
                        echo '📱 Tablet';
                    } else {
                        echo '💻 Desktop/Laptop';
                    }
                ?></code>
            </div>
            
            <div class="info-item">
                <strong>Browser:</strong>
                <code><?php 
                    $browser = 'Unknown';
                    if (strpos($user_agent, 'Chrome') !== false) $browser = 'Chrome';
                    elseif (strpos($user_agent, 'Safari') !== false) $browser = 'Safari';
                    elseif (strpos($user_agent, 'Firefox') !== false) $browser = 'Firefox';
                    elseif (strpos($user_agent, 'Edge') !== false) $browser = 'Edge';
                    echo $browser;
                ?></code>
            </div>
        </div>
        
        <div class="device-info">
            <h3>💡 What This Means</h3>
            <p>
                <?php if (strpos($user_agent, 'Mobile') !== false || strpos($user_agent, 'Tablet') !== false): ?>
                    🎉 <strong>Perfect!</strong> You're viewing this from a mobile device, which means:
                    <ul style="margin-top: 10px; padding-left: 20px;">
                        <li>QR codes will work when scanned with your phone camera</li>
                        <li>Customers can browse the menu on their phones</li>
                        <li>Mobile ordering is fully functional</li>
                    </ul>
                <?php else: ?>
                    💻 You're viewing this from a desktop/laptop. To test mobile access:
                    <ol style="margin-top: 10px; padding-left: 20px;">
                        <li>Make sure your phone is on the same WiFi network</li>
                        <li>Open phone camera and scan a QR code</li>
                        <li>The menu should open on your phone</li>
                    </ol>
                <?php endif; ?>
            </p>
        </div>
        
        <div class="btn-group">
            <a href="menu.php?table_id=1" class="btn btn-success">
                📱 Test Menu Page
            </a>
            <a href="index.php" class="btn btn-primary">
                🏠 Back to Home
            </a>
        </div>
        
        <p style="text-align: center; margin-top: 20px; color: #666; font-size: 0.9em;">
            Share this URL with your phone to test: <br>
            <code style="background: #f5f5f5; padding: 5px 10px; border-radius: 4px; display: inline-block; margin-top: 5px;">
                http://<?php echo $server_ip; ?>/QR_Code_Based_Cafe_Project/mobile_test.php
            </code>
        </p>
    </div>
</body>
</html>
