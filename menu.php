<?php
session_start();
include 'db_connect.php'; // Connects to your database

// 1. Get Table ID from URL (Default to 1 if missing for testing)
if (isset($_GET['table_id'])) {
    $_SESSION['table_id'] = $_GET['table_id'];
}
$table_id = isset($_SESSION['table_id']) ? $_SESSION['table_id'] : 1;

// 2. Fetch Top 3 Selling Items (Logic: Count occurrences in order_items)
$top_sql = "SELECT m.*, COUNT(oi.item_id) as popularity 
            FROM menu_items m 
            LEFT JOIN order_items oi ON m.item_id = oi.item_id 
            WHERE m.is_available = 1
            GROUP BY m.item_id 
            ORDER BY popularity DESC 
            LIMIT 3";
$top_result = $conn->query($top_sql);

// 3. Fetch Full Menu (Categorized)
$menu_sql = "SELECT * FROM menu_items ORDER BY category, name";
$menu_result = $conn->query($menu_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P&S Cafe | Menu</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; margin: 0; padding-bottom: 80px; }
        
        /* Header */
        .header { background: #333; color: white; padding: 15px; text-align: center; position: sticky; top: 0; z-index: 1000; }
        .brand-name { font-size: 1.5em; font-weight: bold; color: #ff9800; }
        .table-info { font-size: 0.9em; opacity: 0.8; }

        /* Sections */
        .section-title { padding: 15px 15px 5px; font-weight: bold; color: #555; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #ddd; margin: 10px 15px; }

        /* Food Card */
        .food-card {
            background: white; margin: 15px; padding: 15px; border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; gap:12px;
        }
        .food-card img { height:72px; width:72px; object-fit:cover; border-radius:8px; flex-shrink:0; }
        .food-info h3 { margin: 0 0 5px 0; font-size: 1.1em; }
        .food-info .price { color: #28a745; font-weight: bold; }
        .food-info .cat { font-size: 0.8em; color: #888; background: #eee; padding: 2px 6px; border-radius: 4px; }

        /* Buttons */
        .add-btn {
            background: #ff9800; color: white; border: none; padding: 8px 15px;
            border-radius: 20px; font-weight: bold; cursor: pointer; text-decoration: none;
        }
        .add-btn:hover { background: #e68900; }

        /* Out of Stock Style */
        .out-of-stock { opacity: 0.6; background: #f0f0f0; }
        .no-stock-badge { color: red; font-weight: bold; font-size: 0.8em; border: 1px solid red; padding: 2px 5px; border-radius: 4px; }

        /* Floating Cart Button */
        .float-cart {
            position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
            background: #333; color: white; padding: 12px 30px; border-radius: 50px;
            text-decoration: none; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            display: flex; align-items: center; gap: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand-name">P&S Cafe ☕</div>
        <div class="table-info">Table #<?php echo $table_id; ?></div>
    </div>

    <div class="section-title">🔥 Bestsellers</div>
    <?php if ($top_result->num_rows > 0): ?>
        <?php while($row = $top_result->fetch_assoc()): ?>
            <div class="food-card" style="border-left: 5px solid #ff9800;">
                <div class="food-info">
                    <h3><?php echo $row['name']; ?></h3>
                    <span class="cat"><?php echo $row['category']; ?></span>
                    <div class="price">₹<?php echo $row['price']; ?></div>
                </div>
                <a href="add_to_cart.php?id=<?php echo $row['item_id']; ?>" class="add-btn">ADD +</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="padding:0 15px; color:#777;">Order something to make it a bestseller!</p>
    <?php endif; ?>

    <div class="section-title">📋 Full Menu</div>
    <?php while($row = $menu_result->fetch_assoc()): ?>
        
        <?php 
        // Logic to disable button if Out of Stock
        $cssClass = $row['is_available'] ? "food-card" : "food-card out-of-stock";

        // Image detection for menu item (images/menu/{item_id}.jpg|png|webp) with fallback
        $fallback_external = 'https://via.placeholder.com/72?text=No+Image';
        $localBase = __DIR__ . '/images/menu/' . $row['item_id'];
        $relImage = '';
        if (file_exists($localBase . '.jpg')) {
            $relImage = 'images/menu/' . $row['item_id'] . '.jpg';
        } elseif (file_exists($localBase . '.png')) {
            $relImage = 'images/menu/' . $row['item_id'] . '.png';
        } elseif (file_exists($localBase . '.webp')) {
            $relImage = 'images/menu/' . $row['item_id'] . '.webp';
        } else {
            $placeholderLocal = __DIR__ . '/images/placeholder.png';
            $relImage = file_exists($placeholderLocal) ? 'images/placeholder.png' : $fallback_external;
        }
        ?>

        <div class="<?php echo $cssClass; ?>">
            <img src="<?php echo htmlspecialchars($relImage); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="height:72px; width:72px; object-fit:cover; border-radius:8px; margin-right:12px;">
            <div class="food-info">
                <h3><?php echo $row['name']; ?></h3>
                <span class="cat"><?php echo $row['category']; ?></span>
                <br>
                <?php if($row['is_available']): ?>
                    <span class="price">₹<?php echo $row['price']; ?></span>
                <?php else: ?>
                    <span class="no-stock-badge">OUT OF STOCK</span>
                <?php endif; ?>
            </div>

            <?php if($row['is_available']): ?>
                <a href="add_to_cart.php?id=<?php echo $row['item_id']; ?>" class="add-btn">ADD +</a>
            <?php else: ?>
                <button disabled style="background:#ccc; border:none; padding:8px 15px; border-radius:20px; color:white;">Sold Out</button>
            <?php endif; ?>
        </div>

    <?php endwhile; ?>

    <a href="cart.php" class="float-cart">
        View Cart 🛒
    </a>

    <a href="call_waiter.php" onclick="return confirm('Call staff for help?')" style="position:fixed; bottom:80px; right:20px; background: white; padding: 10px; border-radius: 50%; box-shadow: 0 2px 10px rgba(0,0,0,0.2); font-size: 20px; text-decoration: none;">
        🔔
    </a>

</body>
</html>