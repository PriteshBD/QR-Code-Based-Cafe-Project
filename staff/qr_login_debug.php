<?php
session_start();
include '../includes/db_connect.php';

echo "<div style='font-family:Arial; padding:20px; max-width:900px; margin:0 auto;'>";
echo "<h1>🔍 Staff QR Code Debug Tool</h1>";
echo "<p style='color:#666;'>This page helps troubleshoot staff code issues.</p>";

// Check database connection
echo "<h2>1️⃣ Database Connection</h2>";
if ($conn->connect_error) {
    echo "<p style='color:red;'>❌ Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color:green;'>✅ Database connected successfully</p>";
}

// Check staff table
echo "<h2>2️⃣ Staff in Database</h2>";
$result = $conn->query("SELECT staff_id, name, phone, role FROM staff ORDER BY staff_id");
if ($result && $result->num_rows > 0) {
    echo "<p style='color:green;'>✅ Found " . $result->num_rows . " staff members</p>";
    echo "<table style='border-collapse:collapse; margin-top:10px; width:100%;'>";
    echo "<tr style='background:#f0f0f0;'><th style='border:1px solid #ddd; padding:10px;'>ID</th><th style='border:1px solid #ddd; padding:10px;'>Name</th><th style='border:1px solid #ddd; padding:10px;'>Phone</th><th style='border:1px solid #ddd; padding:10px;'>Role</th><th style='border:1px solid #ddd; padding:10px;'>Hash</th></tr>";
    while ($staff = $result->fetch_assoc()) {
        $phone_trimmed = trim($staff['phone']);
        $hash = md5($phone_trimmed . 'cafe_secret_2026');
        echo "<tr><td style='border:1px solid #ddd; padding:10px;'>" . $staff['staff_id'] . "</td>";
        echo "<td style='border:1px solid #ddd; padding:10px;'>" . htmlspecialchars($staff['name']) . "</td>";
        echo "<td style='border:1px solid #ddd; padding:10px;'>" . htmlspecialchars($staff['phone']) . "</td>";
        echo "<td style='border:1px solid #ddd; padding:10px;'>" . htmlspecialchars($staff['role']) . "</td>";
        echo "<td style='border:1px solid #ddd; padding:10px; font-family:monospace; font-size:0.9em;'>" . substr($hash, 0, 12) . "...</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>❌ No staff found in database</p>";
    echo "<p>To add staff, visit: <a href='../admin/staff_management.php' style='color:#007bff;'>Admin > Staff Management</a></p>";
}

// Test QR token generation
echo "<h2>3️⃣ Test QR Token Generation</h2>";
$test_result = $conn->query("SELECT staff_id, name, phone FROM staff LIMIT 1");
if ($test_result && $test_result->num_rows > 0) {
    $test_staff = $test_result->fetch_assoc();
    $phone_trimmed = trim($test_staff['phone']);
    $phone_hash = md5($phone_trimmed . 'cafe_secret_2026');
    $token = base64_encode($test_staff['staff_id'] . ':' . $phone_hash);
    
    // Use correct IP detection
    $server_ip = 'localhost';
    if (!empty($_SERVER['HTTP_HOST'])) {
        $http_host = $_SERVER['HTTP_HOST'];
        $server_ip = explode(':', $http_host)[0];
    }
    
    $qr_url = "http://" . $server_ip . "/QR_Code_Based_Cafe_Project/staff/qr_login.php?token=" . urlencode($token);
    
    echo "<p style='color:green;'>✅ Token generated successfully for test staff:</p>";
    echo "<p><strong>Staff:</strong> " . htmlspecialchars($test_staff['name']) . " (ID: " . $test_staff['staff_id'] . ")</p>";
    echo "<p><strong>Token:</strong> <code style='background:#f0f0f0; padding:5px; display:block; word-break:break-all;'>" . $token . "</code></p>";
    echo "<p><strong>URL:</strong> <code style='background:#f0f0f0; padding:5px; display:block; word-break:break-all;'>" . htmlspecialchars(substr($qr_url, 0, 150)) . "...</code></p>";
    echo "<p><a href='" . $qr_url . "' class='btn' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>🧪 Test Login</a></p>";
}

// Test QR token decoding
echo "<h2>4️⃣ Test QR Token Decoding</h2>";
if (isset($_GET['test_token'])) {
    $test_token = $_GET['test_token'];
    $decoded = base64_decode($test_token, true);
    
    if ($decoded === false) {
        echo "<p style='color:red;'>❌ Token is not valid base64</p>";
    } else {
        if (!strpos($decoded, ':')) {
            echo "<p style='color:red;'>❌ Decoded token doesn't contain ':' separator</p>";
        } else {
            list($staff_id, $phone_hash) = explode(':', $decoded, 2);
            echo "<p style='color:green;'>✅ Token decoded successfully</p>";
            echo "<p><strong>Staff ID:</strong> " . htmlspecialchars($staff_id) . " (type: " . gettype($staff_id) . ")</p>";
            echo "<p><strong>Phone Hash:</strong> " . htmlspecialchars($phone_hash) . "</p>";
            
            // Verify against database
            $staff_id_int = (int)$staff_id;
            $verify_result = $conn->query("SELECT phone FROM staff WHERE staff_id = $staff_id_int");
            if ($verify_result && $verify_result->num_rows > 0) {
                $verify_staff = $verify_result->fetch_assoc();
                $verify_hash = md5(trim($verify_staff['phone']) . 'cafe_secret_2026');
                echo "<p><strong>Expected Hash:</strong> " . htmlspecialchars($verify_hash) . "</p>";
                echo "<p><strong>Match:</strong> <span style='color:" . ($phone_hash === $verify_hash ? "green" : "red") . ";'>" . ($phone_hash === $verify_hash ? "✅ YES" : "❌ NO") . "</span></p>";
            }
        }
    }
} else {
    echo "<p>To test a specific QR token, add <code>&test_token=YOUR_TOKEN</code> to the URL</p>";
}

echo "<h2>5️⃣ Verify Generate Staff QR Page</h2>";
echo "<p><a href='../admin/generate_staff_qr.php' style='display:inline-block; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Generate Staff QR Codes</a></p>";

echo "</div>";
?>
