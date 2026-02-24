<?php
session_start();
include 'includes/db_connect.php'; // Connects to your database

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
$bestseller_ids = [];
if ($top_result->num_rows > 0) {
    $top_result->data_seek(0);
    while($row = $top_result->fetch_assoc()) {
        $bestseller_ids[] = $row['item_id'];
    }
    $top_result->data_seek(0);
}

// 3. Fetch Full Menu (Categorized)
$menu_sql = "SELECT * FROM menu_items ORDER BY category, name";
$menu_result = $conn->query($menu_sql);

// 4. Calculate total cart items
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $cart_count += $quantity;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P&S Cafe | Menu</title>
    <link rel="stylesheet" href="styles/theme.css">
    <style>
        body { padding-bottom: 120px; }
        
        .header { 
            background: var(--panel);
            color: var(--ink);
            padding: 15px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            border-bottom: 2px solid var(--border);
        }
        .brand-name { font-size: 1.5em; font-weight: 700; color: var(--accent); }
        .table-info { font-size: 0.9em; color: var(--muted); margin-top: 5px; }

        /* Sections */
        .section-title { 
            padding: 15px 15px 5px; 
            font-weight: 700; 
            color: var(--ink);
            text-transform: uppercase; 
            letter-spacing: 1.2px;
            border-bottom: 3px solid var(--accent-2);
            margin: 20px 15px 15px 15px;
        }

        /* Food Card Grid Container */
        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 15px;
            padding: 15px;
        }

        /* Food Card */
        .food-card {
            background: var(--panel);
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
        }
        .food-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-color: var(--accent-2);
        }
        .food-card img { 
            width: 100%; 
            height: 140px; 
            object-fit: cover; 
            display: block;
        }
        .food-info { 
            padding: 12px; 
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .food-info h3 { 
            margin: 0 0 5px 0; 
            font-size: 0.95em; 
            color: var(--ink);
            line-height: 1.2;
            font-weight: 600;
        }
        .food-info .cat { 
            font-size: 0.7em; 
            color: white; 
            background: var(--accent-2); 
            padding: 4px 8px; 
            border-radius: 5px; 
            display: inline-block;
            margin-bottom: 6px;
            width: fit-content;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .food-info .price { 
            color: var(--accent);
            font-weight: 700;
            font-size: 1.1em;
            margin-bottom: 8px;
        }

        /* Veg/Non-Veg Badge */
        .veg-badge {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--success);
            border-radius: 3px;
            position: relative;
            vertical-align: middle;
            margin-right: 5px;
        }
        .veg-badge::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .non-veg-badge {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--danger);
            border-radius: 3px;
            position: relative;
            vertical-align: middle;
            margin-right: 5px;
        }
        .non-veg-badge::after {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 8px solid var(--danger);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Spice Level */
        .spice-indicator {
            font-size: 0.7em;
            color: var(--accent);
            margin-left: 5px;
        }

        /* Bestseller Badge */
        .bestseller-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #333;
            font-size: 0.65em;
            font-weight: 700;
            padding: 5px 8px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 10;
            letter-spacing: 0.2px;
        }

        /* Search and Filter Bar */
        .search-filter-bar {
            background: var(--panel);
            padding: 15px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            position: sticky;
            top: 56px;
            z-index: 999;
            margin: 10px;
            border-radius: 10px;
        }
        .search-box {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.95em;
            margin-bottom: 10px;
            font-family: 'Manrope', sans-serif;
        }
        .search-box:focus {
            outline: none;
            border-color: var(--accent-2);
            box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.15);
        }
        .filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .filter-btn {
            background: var(--panel-2);
            border: 2px solid var(--border);
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 0.85em;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            color: var(--muted);
            font-family: 'Manrope', sans-serif;
        }
        .filter-btn:hover {
            background: var(--border);
            border-color: var(--accent-2);
        }
        .filter-btn.active {
            background: var(--accent-2);
            color: white;
            border-color: var(--accent-2);
        }
        .food-card {
            position: relative;
        }

        /* Buttons */
        .add-btn {
            background: var(--accent-2); 
            color: white; 
            border: none; 
            padding: 8px 12px;
            border-radius: 8px; 
            font-weight: 600; 
            cursor: pointer; 
            text-decoration: none;
            font-size: 0.85em;
            width: 100%;
            transition: all 0.3s;
            margin-top: auto;
            display: block;
            font-family: 'Manrope', sans-serif;
            letter-spacing: 0.3px;
        }
        .add-btn:hover { 
            background: #22867b;
            box-shadow: 0 5px 15px rgba(42, 157, 143, 0.3);
            transform: translateY(-2px);
        }

        /* Out of Stock Style */
        .out-of-stock { 
            opacity: 0.6; 
            background: var(--panel-2); 
        }
        .no-stock-badge { 
            color: var(--danger); 
            font-weight: 700; 
            font-size: 0.75em; 
            border: 1px solid var(--danger); 
            padding: 4px 8px; 
            border-radius: 5px; 
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Bestseller grid */
        .bestseller-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 15px;
            padding: 15px;
        }

        /* Floating Action Buttons */
        .float-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--panel);
            border-top: 2px solid var(--border);
            display: flex;
            gap: 10px;
            padding: 10px;
            z-index: 999;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }

        .float-btn {
            flex: 1;
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9em;
            transition: all 0.3s;
            white-space: nowrap;
            font-family: 'Manrope', sans-serif;
            letter-spacing: 0.3px;
        }

        .float-cart-btn {
            background: var(--ink);
            color: white;
            position: relative;
        }
        .float-cart-btn:active {
            transform: scale(0.95);
            background: #1a1f35;
        }
        .float-cart-btn:hover {
            box-shadow: 0 5px 15px rgba(15, 23, 42, 0.3);
        }

        .float-bell-btn {
            background: var(--accent-2);
            color: white;
            position: relative;
        }
        .float-bell-btn:hover {
            box-shadow: 0 5px 15px rgba(42, 157, 143, 0.3);
            background: #22867b;
        }
        .float-bell-btn:active {
            transform: scale(0.95);
        }
        .float-bell-btn.loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Bell animation */
        @keyframes bellRing {
            0%, 100% { transform: rotate(0deg); }
            10%, 30% { transform: rotate(-10deg); }
            20%, 40% { transform: rotate(10deg); }
            50% { transform: rotate(0deg); }
        }

        .bell-animate {
            animation: bellRing 0.5s ease-in-out;
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75em;
            font-weight: 700;
            border: 2px solid var(--panel);
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body { padding-bottom: 130px; }
            .food-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                gap: 12px;
                padding: 12px;
            }
            .section-title {
                margin: 12px;
                padding: 12px 0;
                font-size: 0.95em;
            }
            .float-action-bar {
                padding: 8px;
                gap: 8px;
            }
            .float-btn {
                padding: 10px 12px;
                font-size: 0.85em;
            }
        }

        @media (max-width: 480px) {
            .brand-name { font-size: 1.2em; }
            .table-info { font-size: 0.8em; }
            .food-grid, .bestseller-grid {
                grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
                gap: 10px;
                padding: 10px;
            }
            .food-card img { 
                height: 120px;
            }
            .food-info h3 {
                font-size: 0.85em;
            }
            .float-action-bar {
                padding: 6px;
                gap: 6px;
            }
            .float-btn {
                padding: 8px 10px;
                font-size: 0.75em;
                border-radius: 6px;
            }
        }
    </style>
    </style>
</head>
<body>

    <div class="header">
        <div class="brand-name">P&S Cafe ☕</div>
        <div class="table-info">Table #<?php echo $table_id; ?></div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="search-filter-bar">
        <input type="text" class="search-box" id="searchBox" placeholder="🔍 Search menu items...">
        <div class="filter-buttons">
            <button class="filter-btn active" onclick="filterCategory('all')">All</button>
            <button class="filter-btn" onclick="filterVeg('veg')">🟢 Veg</button>
            <button class="filter-btn" onclick="filterVeg('non-veg')">🔴 Non-Veg</button>
        </div>
    </div>

    <div class="section-title">🔥 Bestsellers</div>
    <?php if ($top_result->num_rows > 0): ?>
        <div class="bestseller-grid">
            <?php while($row = $top_result->fetch_assoc()): ?>
                <div class="food-card">
                    <span class="bestseller-badge">⭐ Bestseller</span>
                    <?php 
                    $fallback_external = 'https://via.placeholder.com/160?text=No+Image';
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
                    <img src="<?php echo htmlspecialchars($relImage); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <div class="food-info">
                        <h3>
                            <?php if(isset($row['is_veg'])): ?>
                                <span class="<?php echo $row['is_veg'] ? 'veg-badge' : 'non-veg-badge'; ?>"></span>
                            <?php endif; ?>
                            <?php echo $row['name']; ?>
                            <?php if(isset($row['spice_level']) && $row['spice_level'] > 0): ?>
                                <span class="spice-indicator"><?php echo str_repeat('🌶️', $row['spice_level']); ?></span>
                            <?php endif; ?>
                        </h3>
                        <span class="cat"><?php echo $row['category']; ?></span>
                        <div class="price">₹<?php echo number_format($row['price'], 0); ?></div>
                        <a href="add_to_cart.php?id=<?php echo $row['item_id']; ?>" class="add-btn">ADD +</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p style="padding:0 15px; color:#777;">Order something to make it a bestseller!</p>
    <?php endif; ?>

    <div class="section-title">📋 Full Menu</div>
    <div class="food-grid">
    <?php while($row = $menu_result->fetch_assoc()): ?>
        
        <?php 
        // Logic to disable button if Out of Stock
        $cssClass = $row['is_available'] ? "food-card" : "food-card out-of-stock";

        // Image detection for menu item (images/menu/{item_id}.jpg|png|webp) with fallback
        $fallback_external = 'https://via.placeholder.com/160?text=No+Image';
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

        <div class="<?php echo $cssClass; ?>" data-name="<?php echo strtolower($row['name']); ?>" data-category="<?php echo strtolower($row['category']); ?>" data-veg="<?php echo isset($row['is_veg']) ? $row['is_veg'] : 1; ?>">
            <?php if(in_array($row['item_id'], $bestseller_ids)): ?>
                <span class="bestseller-badge">⭐ Bestseller</span>
            <?php endif; ?>
            <img src="<?php echo htmlspecialchars($relImage); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
            <div class="food-info">
                <h3>
                    <?php if(isset($row['is_veg'])): ?>
                        <span class="<?php echo $row['is_veg'] ? 'veg-badge' : 'non-veg-badge'; ?>"></span>
                    <?php endif; ?>
                    <?php echo $row['name']; ?>
                    <?php if(isset($row['spice_level']) && $row['spice_level'] > 0): ?>
                        <span class="spice-indicator"><?php echo str_repeat('🌶️', $row['spice_level']); ?></span>
                    <?php endif; ?>
                </h3>
                <span class="cat"><?php echo $row['category']; ?></span>
                <?php if($row['is_available']): ?>
                    <div class="price">₹<?php echo number_format($row['price'], 0); ?></div>
                    <a href="add_to_cart.php?id=<?php echo $row['item_id']; ?>" class="add-btn">ADD +</a>
                <?php else: ?>
                    <span class="no-stock-badge">OUT OF STOCK</span>
                    <button disabled style="background:#ccc; border:none; padding:8px 12px; border-radius:6px; color:white; margin-top:auto; width:100%; cursor:not-allowed; display:block;">Sold Out</button>
                <?php endif; ?>
            </div>
        </div>

    <?php endwhile; ?>
    </div>

    <!-- Floating Action Bar (Mobile Friendly) -->
    <div class="float-action-bar">
        <a href="cart.php" class="float-btn float-cart-btn">
            🛒 View Cart
            <?php if ($cart_count > 0): ?>
                <span class="notification-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>
        <button class="float-btn float-bell-btn" id="bellBtn" onclick="callStaff()">
            🔔 Call Staff
        </button>
    </div>

    <script>
        function callStaff() {
            if (confirm('Call staff for help?')) {
                const btn = document.getElementById('bellBtn');
                btn.classList.add('loading', 'bell-animate');
                btn.disabled = true;
                
                // Send AJAX request
                fetch('call_waiter.php?ajax=1')
                    .then(response => response.json())
                    .then(data => {
                        btn.classList.remove('loading');
                        btn.disabled = false;
                        if (data.success) {
                            alert('✓ ' + data.message);
                        } else {
                            alert('❌ ' + data.message);
                        }
                    })
                    .catch(error => {
                        btn.classList.remove('loading');
                        btn.disabled = false;
                        alert('❌ Error notifying staff. Please try again.');
                        console.error('Error:', error);
                    });
            }
        }

        // Search functionality
        document.getElementById('searchBox').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.food-grid .food-card');
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const category = card.getAttribute('data-category');
                
                if (name.includes(searchTerm) || category.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        let currentFilter = 'all';

        function filterCategory(filter) {
            currentFilter = filter;
            updateFilterButtons();
            applyFilters();
        }

        function filterVeg(filter) {
            const cards = document.querySelectorAll('.food-grid .food-card');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Update button states
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            cards.forEach(card => {
                const isVeg = card.getAttribute('data-veg') === '1';
                
                if (filter === 'veg' && !isVeg) {
                    card.style.display = 'none';
                } else if (filter === 'non-veg' && isVeg) {
                    card.style.display = 'none';
                } else {
                    card.style.display = 'flex';
                }
            });
            
            // Clear search
            document.getElementById('searchBox').value = '';
        }

        function updateFilterButtons() {
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            if (currentFilter === 'all') {
                buttons[0].classList.add('active');
            }
        }

        function applyFilters() {
            const cards = document.querySelectorAll('.food-grid .food-card');
            cards.forEach(card => {
                card.style.display = 'flex';
            });
            document.getElementById('searchBox').value = '';
        }
    </script>

</body>
</html>