<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['username'];
    $phone = $_POST['password']; // Using Phone as Password for simplicity

    // Check against the Staff table
    // Use prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM staff WHERE name=? AND phone=?");
    $stmt->bind_param("ss", $name, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        $_SESSION['staff_logged_in'] = true;
        $_SESSION['staff_name'] = $staff['name'];
        $_SESSION['staff_id'] = $staff['staff_id'];
        $_SESSION['staff_role'] = $staff['role'];
        
        // Auto-mark attendance as Present when staff logs in
        $staff_id = $staff['staff_id'];
        $date = date('Y-m-d');
        $conn->query("INSERT INTO attendance (staff_id, date, status) VALUES ('$staff_id', '$date', 'Present') ON DUPLICATE KEY UPDATE status='Present'");
        
        header("Location: staff_dashboard.php");
        exit();
    } else {
        $error = "Invalid Credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | P&S Cafe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-container {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 400px;
            max-width: 90%;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-icon {
            font-size: 4em;
            margin-bottom: 15px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .login-header h2 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 0.95em;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #555;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95em;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2em;
            color: #999;
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            font-size: 0.9em;
        }
        
        .info-box {
            background: #e3f2fd;
            color: #1565c0;
            padding: 15px;
            border-radius: 8px;
            margin-top: 25px;
            font-size: 0.85em;
            text-align: center;
            border-left: 4px solid #2196f3;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">👨‍🍳</div>
            <h2>Kitchen Staff Login</h2>
            <p>Enter your credentials to access the kitchen display</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="error-message">
                ❌ <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Staff Name</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" name="username" id="username" placeholder="Enter your name" required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Phone Number</label>
                <div class="input-wrapper">
                    <span class="input-icon">📱</span>
                    <input type="password" name="password" id="password" placeholder="Enter your phone number" required>
                </div>
            </div>
            
            <button type="submit" class="btn-login">🔓 Login to Kitchen</button>
        </form>
        
        <div class="info-box">
            <strong>ℹ️ Auto Attendance</strong>
            Your attendance will be automatically marked as Present when you login
        </div>
        
        <div class="back-link">
            <a href="../index.php">← Back to Home</a>
        </div>
    </div>
</body>
</html>