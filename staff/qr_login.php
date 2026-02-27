<?php
session_start();
include '../includes/db_connect.php';

// Get token from URL
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    die("<div style='font-family:Arial; padding:40px; text-align:center;'><h2>❌ Invalid QR Code</h2><p>No authentication token found.</p><a href='staff_login.php' style='color:#007bff;'>Go to Staff Login</a></div>");
}

// Decode token: token format is base64(staff_id:phone_hash)
$decoded = base64_decode($token, true); // Strict mode to detect invalid base64
if ($decoded === false || !strpos($decoded, ':')) {
    die("<div style='font-family:Arial; padding:40px; text-align:center;'><h2>❌ Invalid QR Code</h2><p>Authentication token is malformed or corrupted.</p><p style='font-size:0.9em; color:#999;'>Token: " . htmlspecialchars(substr($token, 0, 50)) . "...</p><a href='staff_login.php' style='color:#007bff;'>Go to Staff Login</a></div>");
}

list($staff_id, $phone_hash) = explode(':', $decoded, 2);

// Fetch staff from database
$staff_id = (int)$staff_id; // Cast to integer to match database column type
$stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
if (!$stmt) {
    die("<div style='font-family:Arial; padding:40px; text-align:center;'><h2>❌ Database Error</h2><p>Prepare failed: " . htmlspecialchars($conn->error) . "</p><a href='staff_login.php' style='color:#007bff;'>Try Again</a></div>");
}

$stmt->bind_param("i", $staff_id);
$stmt->execute();
if ($stmt->errno) {
    die("<div style='font-family:Arial; padding:40px; text-align:center;'><h2>❌ Database Error</h2><p>Execute failed: " . htmlspecialchars($stmt->error) . "</p><a href='staff_login.php' style='color:#007bff;'>Try Again</a></div>");
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<div style='font-family:Arial; padding:40px; text-align:center;'><h2>❌ Staff Not Found</h2><p>Staff ID #" . $staff_id . " does not exist in the database.</p><a href='staff_login.php' style='color:#007bff;'>Go to Staff Login</a></div>");
}

$staff = $result->fetch_assoc();

// Verify phone hash matches
$db_phone = trim($staff['phone']); // Trim any whitespace
$expected_hash = md5($db_phone . 'cafe_secret_2026');
if ($phone_hash !== $expected_hash) {
    die("<div style='font-family:Arial; padding:40px; text-align:center;'><h2>❌ Authentication Failed</h2><p>QR code verification failed. This QR code may be outdated or corrupted.</p><p style='font-size:0.85em; color:#999;'>Staff: " . htmlspecialchars($staff['name']) . " | Hash mismatch</p><a href='staff_login.php' style='color:#007bff;'>Go to Staff Login</a></div>");
}

// Login successful - set session
$_SESSION['staff_logged_in'] = true;
$_SESSION['staff_name'] = $staff['name'];
$_SESSION['staff_id'] = $staff['staff_id'];
$_SESSION['staff_role'] = $staff['role'];

// Auto-mark attendance as Present when staff logs in
$date = date('Y-m-d');
$conn->query("INSERT INTO attendance (staff_id, date, status) VALUES ('$staff_id', '$date', 'Present') ON DUPLICATE KEY UPDATE status='Present'");

// Redirect based on role
$role_map = [
    'Chef' => 'chef_dashboard.php',
    'Barista' => 'barista_dashboard.php',
    'Waiter' => 'waiter_dashboard.php',
    'Manager' => 'manager_dashboard.php'
];

$redirect_page = $role_map[$staff['role']] ?? 'staff_dashboard.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging in...</title>
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
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 400px;
        }
        .spinner {
            font-size: 3em;
            animation: bounce 1s infinite;
            margin-bottom: 20px;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        h2 { color: #333; margin-bottom: 15px; font-size: 1.5em; }
        p { color: #666; font-size: 1.05em; }
        .staff-name { font-weight: 600; color: #667eea; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner">✨</div>
        <h2>Welcome, <?php echo htmlspecialchars($staff['name']); ?>!</h2>
        <p>Logging you in to your dashboard...</p>
        <div class="staff-name"><?php echo htmlspecialchars($staff['role']); ?></div>
    </div>
    <script>
        // Redirect after 1 second to show welcome message
        setTimeout(function() {
            window.location.href = '<?php echo $redirect_page; ?>';
        }, 1000);
    </script>
</body>
</html>
<?php
// Also do a meta redirect as backup
exit();
