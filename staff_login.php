<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['username'];
    $phone = $_POST['password']; // Using Phone as Password for simplicity

    // Check against the Staff table
    // Use prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM staff WHERE name=? AND phone=? AND role='Head Chef'");
    $stmt->bind_param("ss", $name, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['staff_logged_in'] = true;
        $_SESSION['staff_name'] = $name;
        header("Location: staff_dashboard.php");
        exit();
    } else {
        $error = "Invalid Credentials! (Try: Ramesh Kumar / 9876543210)";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Staff Login | P&S Cafe</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #333; }
        .login-box { background: white; padding: 40px; border-radius: 10px; text-align: center; width: 300px; }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #ff9800; color: white; border: none; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>👨‍🍳 Kitchen Login</h2>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Staff Name" required>
            <input type="password" name="password" placeholder="Phone Number" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>