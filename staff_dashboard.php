<?php
session_start();
include 'db_connect.php';

// Security Check
if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: staff_login.php");
    exit();
}

// HANDLER: Accept Order & Verify Payment
if (isset($_POST['accept_order'])) {
    $order_id = $_POST['order_id'];
    $time = $_POST['prep_time'];
    // Update: Mark Paid, Set Time, Status = Cooking
    $conn->query("UPDATE orders SET order_status='Cooking', payment_status='Paid', estimated_time='$time' WHERE order_id=$order_id");
    header("Location: staff_dashboard.php"); // Refresh
}

// HANDLER: Mark Ready
if (isset($_POST['mark_ready'])) {
    $order_id = $_POST['order_id'];
    $conn->query("UPDATE orders SET order_status='Ready', estimated_time='Ready for Pickup' WHERE order_id=$order_id");
    header("Location: staff_dashboard.php");
}

// HANDLER: Reject Order
if (isset($_POST['reject_order'])) {
    $order_id = $_POST['order_id'];
    $conn->query("UPDATE orders SET order_status='Rejected' WHERE order_id=$order_id");
    header("Location: staff_dashboard.php");
}

// HANDLER: Resolve Table Help
if (isset($_POST['resolve_help'])) {
    $req_id = $_POST['req_id'];
    $conn->query("UPDATE table_requests SET status='Resolved' WHERE request_id=$req_id");
    header("Location: staff_dashboard.php");
}

// HANDLER: Menu Image Upload
if (isset($_POST['upload_image'])) {
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    if ($item_id <= 0 || !isset($_FILES['item_image'])) {
        $_SESSION['upload_msg'] = 'Invalid item selected or no file uploaded.';
        header("Location: staff_dashboard.php"); exit();
    }

    $file = $_FILES['item_image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['upload_msg'] = 'Upload error (code ' . $file['error'] . ').';
        header("Location: staff_dashboard.php"); exit();
    }

    // Basic validation
    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['upload_msg'] = 'File too large. Max 5MB.';
        header("Location: staff_dashboard.php"); exit();
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) {
        $_SESSION['upload_msg'] = 'Invalid file type. Only JPG, PNG, WEBP allowed.';
        header("Location: staff_dashboard.php"); exit();
    }

    $ext = $allowed[$mime];
    $targetDir = __DIR__ . '/images/menu/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    $targetPath = $targetDir . $item_id . '.' . $ext;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $_SESSION['upload_msg'] = 'Failed to save uploaded file.';
        header("Location: staff_dashboard.php"); exit();
    }

    // Create square thumbnail 400x400 (jpg)
    $thumbPath = $targetDir . $item_id . '_thumb.jpg';
    $src = null;
    if ($ext === 'jpg') $src = @imagecreatefromjpeg($targetPath);
    if ($ext === 'png') $src = @imagecreatefrompng($targetPath);
    if ($ext === 'webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($targetPath);

    if ($src) {
        $w = imagesx($src); $h = imagesy($src); $size = min($w, $h);
        $sx = (int)(($w - $size) / 2); $sy = (int)(($h - $size) / 2);
        $dst = imagecreatetruecolor(400, 400);
        // White background to avoid black for transparent PNGs
        $white = imagecolorallocate($dst, 255, 255, 255); imagefill($dst, 0, 0, $white);
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, 400, 400, $size, $size);
        imagejpeg($dst, $thumbPath, 85);
        imagedestroy($dst); imagedestroy($src);
    }

    $_SESSION['upload_msg'] = 'Image uploaded successfully for item ID ' . $item_id . '.';
    header("Location: staff_dashboard.php"); exit();
}

// HANDLER: Delete Image
if (isset($_POST['delete_image'])) {
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    $targetDir = __DIR__ . '/images/menu/';
    $deleted = false;
    foreach (['jpg','png','webp'] as $e) {
        $f = $targetDir . $item_id . '.' . $e;
        if (file_exists($f)) { @unlink($f); $deleted = true; }
    }
    $thumb = $targetDir . $item_id . '_thumb.jpg';
    if (file_exists($thumb)) { @unlink($thumb); $deleted = true; }
    $_SESSION['upload_msg'] = $deleted ? 'Image deleted.' : 'No image found for that item.';
    header("Location: staff_dashboard.php"); exit();
}

// DATA FETCHING
// 0. Menu Items (for image management)
$menu_items = $conn->query("SELECT item_id, name FROM menu_items ORDER BY name");

// 1. New Orders (Pending)
$new_orders = $conn->query("SELECT * FROM orders WHERE order_status='Pending' ORDER BY order_id DESC");

// 2. Active Orders (Cooking)
$active_orders = $conn->query("SELECT * FROM orders WHERE order_status='Cooking' ORDER BY order_id ASC");

// 3. Help Requests
$help_reqs = $conn->query("SELECT * FROM table_requests WHERE status='Pending'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="refresh" content="10"> <title>Kitchen Dashboard | P&S Cafe</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #222; color: #fff; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 1px solid #444; padding-bottom: 10px; margin-bottom: 20px; }
        
        /* ALERT BANNER */
        .alert-banner { background: #ff4444; color: white; padding: 10px; text-align: center; font-weight: bold; margin-bottom: 20px; animation: blink 1s infinite; }
        @keyframes blink { 50% { opacity: 0.5; } }

        .container { display: flex; gap: 20px; }
        .col { flex: 1; background: #333; padding: 15px; border-radius: 10px; min-height: 80vh; }
        .col h2 { border-bottom: 2px solid #555; padding-bottom: 10px; margin-top: 0; }

        /* Order Cards */
        .card { background: white; color: black; padding: 15px; margin-bottom: 15px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
        .card-header { display: flex; justify-content: space-between; font-weight: bold; font-size: 1.1em; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
        .note { background: #fff3cd; color: #856404; padding: 5px; font-size: 0.9em; margin: 10px 0; border: 1px solid #ffeeba; }
        
        /* Buttons */
        .btn-time { padding: 8px 12px; margin: 2px; border: none; cursor: pointer; color: white; font-weight: bold; border-radius: 4px; }
        .green { background: #28a745; } .blue { background: #007bff; } .purple { background: #6f42c1; }
        .red { background: #dc3545; width: 100%; margin-top: 5px; }
        .ready-btn { background: #28a745; width: 100%; padding: 10px; font-size: 1.1em; cursor: pointer; color: white; border: none; }

        /* Image upload UI */
        .img-grid { display:flex; flex-direction:column; gap:8px; }
        .img-item { display:flex; align-items:center; justify-content:space-between; gap:8px; background:#2b2b2b; padding:8px; border-radius:8px; }
        .img-item img { height:64px; width:64px; object-fit:cover; border-radius:6px; }
        .upload-form { display:flex; gap:8px; align-items:center; }
        .upload-form select, .upload-form input[type=file] { padding:6px; border-radius:6px; border:1px solid #444; background:#222; color:#fff; }
        .upload-form button { padding:8px 10px; border-radius:6px; border:none; cursor:pointer; }
    </style>
</head>
<body>

    <?php if ($help_reqs->num_rows > 0): ?>
        <?php while($row = $help_reqs->fetch_assoc()): ?>
            <div class="alert-banner">
                🔔 Table #<?php echo $row['table_id']; ?> needs help! (<?php echo $row['request_type']; ?>)
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="req_id" value="<?php echo $row['request_id']; ?>">
                    <button type="submit" name="resolve_help" style="margin-left:10px; cursor:pointer;">✅ Done</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <div class="header">
        <div>P&S Kitchen Display System</div>
        <div>Logged in as: <?php echo $_SESSION['staff_name']; ?> | <a href="staff_login.php" style="color:#aaa;">Logout</a></div>
    </div>

    <?php if (isset($_SESSION['upload_msg'])): ?>
        <div class="card" style="background:#fff; color:#111; margin-bottom:12px;">
            <?php echo htmlspecialchars($_SESSION['upload_msg']); unset($_SESSION['upload_msg']); ?>
        </div>
    <?php endif; ?>

    <div class="container">
        
        <div class="col">
            <h2 style="color:#ff9800;">🔔 Incoming (Verify Payment)</h2>
            
            <?php while($row = $new_orders->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-header">
                        <span>Table <?php echo $row['table_id']; ?></span>
                        <span>₹<?php echo $row['total_amount']; ?></span>
                    </div>
                    
                    <?php 
                    $oid = $row['order_id'];
                    $items = $conn->query("SELECT oi.*, m.name FROM order_items oi JOIN menu_items m ON oi.item_id = m.item_id WHERE order_id=$oid");
                    while($i = $items->fetch_assoc()) {
                        echo "<div>" . $i['quantity'] . "x " . $i['name'] . "</div>";
                    }
                    ?>

                    <?php if(!empty($row['order_note'])): ?>
                        <div class="note">📝 Note: <?php echo $row['order_note']; ?></div>
                    <?php endif; ?>

                    <hr>
                    <small>Payment Received? Set Time & Accept:</small>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <div style="display:flex; justify-content:space-between; margin-top:5px;">
                            <button name="accept_order" value="1" class="btn-time green">
                                <input type="hidden" name="prep_time" value="10 mins">10m
                            </button>
                            <button type="submit" name="prep_time" value="10 mins" class="btn-time green">10m</button>
                            <button type="submit" name="prep_time" value="20 mins" class="btn-time blue">20m</button>
                            <button type="submit" name="prep_time" value="30 mins" class="btn-time purple">30m</button>
                        </div>
                        <input type="hidden" name="accept_order" value="true">
                        <button type="submit" name="reject_order" class="btn-time red">❌ Reject / Not Paid</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="col">
            <h2 style="color:#28a745;">🔥 Cooking Now</h2>
            
            <?php while($row = $active_orders->fetch_assoc()): ?>
                <div class="card" style="border-left: 5px solid #28a745;">
                    <div class="card-header">
                        <span>Table <?php echo $row['table_id']; ?></span>
                        <span>Due: <?php echo $row['estimated_time']; ?></span>
                    </div>
                    
                    <?php 
                    $oid = $row['order_id'];
                    $items = $conn->query("SELECT oi.*, m.name FROM order_items oi JOIN menu_items m ON oi.item_id = m.item_id WHERE order_id=$oid");
                    while($i = $items->fetch_assoc()) {
                        echo "<div>" . $i['quantity'] . "x " . $i['name'] . "</div>";
                    }
                    ?>
                    
                    <?php if(!empty($row['order_note'])): ?>
                        <div class="note">📝 <?php echo $row['order_note']; ?></div>
                    <?php endif; ?>

                    <form method="POST" style="margin-top:10px;">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <button type="submit" name="mark_ready" class="ready-btn">✅ Mark Ready</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="col">
            <h2 style="color:#ffc107;">🖼️ Menu Images</h2>

            <form method="POST" enctype="multipart/form-data" class="upload-form" style="margin-bottom:12px;">
                <select name="item_id" required>
                    <option value="">Select item</option>
                    <?php
                    // reset menu_items pointer in case used earlier
                    $menu_items = $conn->query("SELECT item_id, name FROM menu_items ORDER BY name");
                    while($mi = $menu_items->fetch_assoc()): ?>
                        <option value="<?php echo $mi['item_id']; ?>"><?php echo htmlspecialchars($mi['name']); ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="file" name="item_image" accept="image/*" required>
                <button type="submit" name="upload_image" style="background:#ffc107;">Upload</button>
            </form>

            <div class="img-grid">
                <?php
                $menu_items_list = $conn->query("SELECT item_id, name FROM menu_items ORDER BY name");
                while($mi = $menu_items_list->fetch_assoc()):
                    $imgPath = '';
                    $base = __DIR__ . '/images/menu/' . $mi['item_id'];
                    if (file_exists($base . '.jpg')) $imgPath = 'images/menu/' . $mi['item_id'] . '.jpg';
                    elseif (file_exists($base . '.png')) $imgPath = 'images/menu/' . $mi['item_id'] . '.png';
                    elseif (file_exists($base . '.webp')) $imgPath = 'images/menu/' . $mi['item_id'] . '.webp';
                    elseif (file_exists(__DIR__ . '/images/placeholder.png')) $imgPath = 'images/placeholder.png';
                    else $imgPath = 'https://via.placeholder.com/64?text=No+Img';
                ?>
                    <div class="img-item">
                        <div style="display:flex; gap:8px; align-items:center;">
                            <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="<?php echo htmlspecialchars($mi['name']); ?>">
                            <div><?php echo htmlspecialchars($mi['name']); ?></div>
                        </div>
                        <form method="POST" onsubmit="return confirm('Delete image for <?php echo htmlspecialchars($mi['name']); ?>?');">
                            <input type="hidden" name="item_id" value="<?php echo $mi['item_id']; ?>">
                            <button type="submit" name="delete_image" style="background:#dc3545; color:#fff; border:none; padding:8px 10px; border-radius:6px;">Delete</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>

        </div>

    </div>

</body>
</html>