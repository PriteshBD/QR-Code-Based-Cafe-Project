<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P&S Cafe - QR Based Ordering System</title>
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
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .tagline {
            color: #666;
            margin-bottom: 40px;
            font-size: 1.1em;
        }
        .features {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }
        .features h3 {
            color: #667eea;
            margin-bottom: 15px;
            text-align: center;
        }
        .features ul {
            list-style: none;
            padding: 0;
        }
        .features li {
            padding: 8px 0;
            color: #555;
        }
        .features li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
            margin-right: 10px;
        }
        .buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }
        .btn {
            padding: 15px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1em;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-admin {
            background: #667eea;
            color: white;
        }
        .btn-staff {
            background: #ff9800;
            color: white;
        }
        .btn-menu {
            background: #28a745;
            color: white;
        }
        .btn-demo {
            background: #17a2b8;
            color: white;
        }
        .footer {
            margin-top: 30px;
            color: #999;
            font-size: 0.9em;
        }
        @media (max-width: 600px) {
            .buttons {
                grid-template-columns: 1fr;
            }
            h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🍽️ P&S Cafe</h1>
        <p class="tagline">QR Code Based Digital Ordering System</p>
        
        <div class="features">
            <h3>✨ Features</h3>
            <ul>
                <li>Scan QR code to view menu & place orders (Works on mobile!)</li>
                <li>Real-time order tracking with estimated time</li>
                <li>UPI payment integration + Demo payment for presentations</li>
                <li>Kitchen display system for staff</li>
                <li>Admin dashboard with analytics</li>
                <li>Staff attendance management</li>
                <li>Mobile-friendly responsive design</li>
            </ul>
        </div>
        
        <div class="features" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #2196F3;">
            <h3>🎓 Demo Mode for Presentations</h3>
            <p style="margin-bottom: 10px;"><strong>Perfect for degree project demonstrations!</strong></p>
            <ol style="text-align: left; margin: 10px 0; padding-left: 20px;">
                <li>Open menu on mobile and add items to cart</li>
                <li>Place your order</li>
                <li>On the order tracking page, you'll see <strong>"Demo Payment Options"</strong></li>
                <li>Click <strong>"Pay with Cash"</strong>, <strong>"Pay with Card"</strong>, or <strong>"Pay with UPI"</strong></li>
                <li>Order automatically moves to kitchen! ✅</li>
            </ol>
            <p style="font-size: 0.9em; margin-top: 10px;"><em>No real payment needed - perfect for showcasing your project!</em></p>
        </div>

        <div class="buttons">
            <a href="admin/admin_login.php" class="btn btn-admin">
                🔐 Admin Login
            </a>
            <a href="staff/staff_login.php" class="btn btn-staff">
                👨‍🍳 Staff Login
            </a>
            <a href="menu.php?table_id=1" class="btn btn-menu">
                📱 Demo Menu (Table 1)
            </a>
            <a href="admin/generate_qr.php" class="btn btn-demo" onclick="alert('Please login as admin first'); return false;">
                🎯 Generate QR Codes
            </a>
        </div>

        <div class="footer">
            <p><strong>Default Credentials:</strong></p>
            <p>Admin: admin / admin123</p>
            <p>Staff: Ahmed / 123456789</p>
        </div>
    </div>
</body>
</html>
