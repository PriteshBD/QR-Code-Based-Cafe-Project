<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Add Item
if (isset($_POST['add_item'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $price = (float)$_POST['price'];
    $category = $conn->real_escape_string($_POST['category']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    $sql = "INSERT INTO menu_items (name, price, category, is_available) VALUES ('$name', '$price', '$category', '$is_available')";
    $conn->query($sql);
    header("Location: manage_menu.php?success=added");
    exit();
}

// Handle Update Item
if (isset($_POST['update_item'])) {
    $item_id = (int)$_POST['item_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $price = (float)$_POST['price'];
    $category = $conn->real_escape_string($_POST['category']);
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    $sql = "UPDATE menu_items SET name='$name', price='$price', category='$category', is_available='$is_available' WHERE item_id=$item_id";
    $conn->query($sql);
    header("Location: manage_menu.php?success=updated");
    exit();
}

// Handle Delete Item
if (isset($_GET['delete'])) {
    $item_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM menu_items WHERE item_id=$item_id");
    header("Location: manage_menu.php?success=deleted");
    exit();
}

// Handle Toggle Availability
if (isset($_GET['toggle'])) {
    $item_id = (int)$_GET['toggle'];
    $conn->query("UPDATE menu_items SET is_available = NOT is_available WHERE item_id=$item_id");
    header("Location: manage_menu.php?success=toggled");
    exit();
}

// Fetch all menu items
$menu_result = $conn->query("SELECT * FROM menu_items ORDER BY category, name");

// Get unique categories
$cat_result = $conn->query("SELECT DISTINCT category FROM menu_items ORDER BY category");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu | P&S Cafe</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: #f4f6f9; 
            margin: 0;
            padding: 20px;
        }
        .header { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        th, td { 
            padding: 12px; 
            border-bottom: 1px solid #ddd; 
            text-align: left; 
        }
        th { 
            background: #343a40; 
            color: white; 
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9em;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-secondary { background: #6c757d; color: white; }
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .badge-available { background: #d4edda; color: #155724; }
        .badge-unavailable { background: #f8d7da; color: #721c24; }
        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">📋 Menu Management</h1>
        <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="success-msg">
            ✓ Action completed successfully!
        </div>
    <?php endif; ?>

    <div class="container">
        <h2>➕ Add New Menu Item</h2>
        <form method="POST">
            <div class="form-grid">
                <input type="text" name="name" placeholder="Item Name" required>
                <input type="number" step="0.01" name="price" placeholder="Price (₹)" required>
            </div>
            <div class="form-grid">
                <select name="category" required>
                    <option value="">Select Category</option>
                    <?php 
                    $cat_result->data_seek(0);
                    while($cat = $cat_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                            <?php echo htmlspecialchars($cat['category']); ?>
                        </option>
                    <?php endwhile; ?>
                    <option value="Coffee">Coffee</option>
                    <option value="Tea">Tea</option>
                    <option value="Beverages">Beverages</option>
                    <option value="Snacks">Snacks</option>
                    <option value="Meals">Meals</option>
                    <option value="Dessert">Dessert</option>
                    <option value="Pizza">Pizza</option>
                    <option value="Burgers">Burgers</option>
                    <option value="Salad">Salad</option>
                </select>
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_available" checked style="width: auto;">
                    Available
                </label>
            </div>
            <button type="submit" name="add_item" class="btn btn-success">Add Item</button>
        </form>
    </div>

    <div class="container">
        <h2>📋 Current Menu Items</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $menu_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $item['item_id']; ?></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        <?php if($item['is_available']): ?>
                            <span class="badge badge-available">Available</span>
                        <?php else: ?>
                            <span class="badge badge-unavailable">Out of Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?toggle=<?php echo $item['item_id']; ?>" 
                           class="btn btn-warning"
                           onclick="return confirm('Toggle availability?')">
                            Toggle
                        </a>
                        <a href="edit_item.php?id=<?php echo $item['item_id']; ?>" 
                           class="btn btn-primary">
                            Edit
                        </a>
                        <a href="?delete=<?php echo $item['item_id']; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Delete this item?')">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
