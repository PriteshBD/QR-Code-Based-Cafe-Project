<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_menu.php");
    exit();
}

$item_id = (int)$_GET['id'];
$item_result = $conn->query("SELECT * FROM menu_items WHERE item_id = $item_id");

if ($item_result->num_rows == 0) {
    header("Location: manage_menu.php?error=notfound");
    exit();
}

$item = $item_result->fetch_assoc();

// Find current image
$upload_dir = '../images/menu/';
$current_image = null;
if (is_dir($upload_dir)) {
    $images = glob($upload_dir . $item_id . '.*');
    if (!empty($images)) {
        $current_image = basename($images[0]);
    }
}

// Handle Update
if (isset($_POST['update_item'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $price = (float)$_POST['price'];
    $category = $conn->real_escape_string($_POST['category']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    $sql = "UPDATE menu_items SET name='$name', price='$price', category='$category', is_available='$is_available' WHERE item_id=$item_id";
    $conn->query($sql);
    
    // Handle image upload
    if (isset($_FILES['item_image']) && $_FILES['item_image']['size'] > 0) {
        $upload_dir = '../images/menu/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = $_FILES['item_image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array(strtolower($ext), $allowed) && $_FILES['item_image']['size'] < 5000000) {
            // Delete old image if exists
            $oldImages = glob($upload_dir . $item_id . '.*');
            foreach ($oldImages as $oldFile) {
                unlink($oldFile);
            }
            
            $new_filename = $item_id . '.' . $ext;
            move_uploaded_file($_FILES['item_image']['tmp_name'], $upload_dir . $new_filename);
        }
    }
    
    header("Location: manage_menu.php?success=updated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item | P&S Cafe</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: #f4f6f9; 
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 { margin-top: 0; color: #333; }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 1em;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input {
            width: auto;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .image-preview {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .image-preview p {
            margin: 10px 0 0 0;
            color: #666;
            font-size: 0.9em;
        }
    </style>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body class="admin-ui">
    <div class="container">
        <h1>✏️ Edit Menu Item</h1>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Item Name:</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Price (₹):</label>
                <input type="number" step="0.01" name="price" id="price" value="<?php echo $item['price']; ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select name="category" id="category" required>
                    <option value="">Select Category</option>
                    <option value="Coffee" <?php echo $item['category']=='Coffee'?'selected':''; ?>>Coffee</option>
                    <option value="Tea" <?php echo $item['category']=='Tea'?'selected':''; ?>>Tea</option>
                    <option value="Beverages" <?php echo $item['category']=='Beverages'?'selected':''; ?>>Beverages</option>
                    <option value="Snacks" <?php echo $item['category']=='Snacks'?'selected':''; ?>>Snacks</option>
                    <option value="Meals" <?php echo $item['category']=='Meals'?'selected':''; ?>>Meals</option>
                    <option value="Dessert" <?php echo $item['category']=='Dessert'?'selected':''; ?>>Dessert</option>
                    <option value="Pizza" <?php echo $item['category']=='Pizza'?'selected':''; ?>>Pizza</option>
                    <option value="Burgers" <?php echo $item['category']=='Burgers'?'selected':''; ?>>Burgers</option>
                    <option value="Salad" <?php echo $item['category']=='Salad'?'selected':''; ?>>Salad</option>
                </select>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_available" id="is_available" <?php echo $item['is_available']?'checked':''; ?>>
                    <label for="is_available" style="margin: 0;">Available for ordering</label>
                </div>
            </div>

            <?php if ($current_image): ?>
            <div class="image-preview">
                <strong>📷 Current Image:</strong>
                <div>
                    <img src="../images/menu/<?php echo htmlspecialchars($current_image); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                </div>
                <p>Click below to replace this image</p>
            </div>
            <?php else: ?>
            <div class="image-preview">
                <p style="color: #999;">No image uploaded yet</p>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="item_image">📷 Change Item Image (Optional):</label>
                <input type="file" name="item_image" id="item_image" accept="image/jpeg,image/png,image/webp">
                <small style="color: #666; display: block; margin-top: 5px;">Accepted formats: JPG, PNG, WebP (Max 5MB). Leave blank to keep current image.</small>
            </div>

            <div class="btn-group">
                <button type="submit" name="update_item" class="btn btn-primary">💾 Save Changes</button>
                <a href="manage_menu.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
