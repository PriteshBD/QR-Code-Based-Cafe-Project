<?php
/**
 * Simple Menu Image Setup
 * This script creates placeholder images using a color-coded approach
 * Run this once by visiting: http://localhost/QR_Code_Based_Cafe_Project/setup_images.php
 */

header('Content-Type: text/html; charset=UTF-8');

// Create images directory if needed
$imagesDir = __DIR__ . '/images/menu';
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

// Menu configuration with categories and emojis/icons
$menuConfig = [
    ['id' => 1, 'name' => 'Cappuccino', 'cat' => 'Drinks', 'color' => '#FF6B9D'],
    ['id' => 2, 'name' => 'Cafe Latte', 'cat' => 'Drinks', 'color' => '#FF6B9D'],
    ['id' => 3, 'name' => 'Americano', 'cat' => 'Drinks', 'color' => '#FF6B9D'],
    ['id' => 4, 'name' => 'Turkish Coffee', 'cat' => 'Drinks', 'color' => '#FF6B9D'],
    ['id' => 5, 'name' => 'Mint Tea', 'cat' => 'Drinks', 'color' => '#FF6B9D'],
    ['id' => 6, 'name' => 'Fresh Orange Juice', 'cat' => 'Drinks', 'color' => '#FF6B9D'],
    ['id' => 7, 'name' => 'Mango Smoothie', 'cat' => 'Drinks', 'color' => '#FF6B9D'],
    ['id' => 8, 'name' => 'Hot Chocolate', 'cat' => 'Drinks', 'color' => '#FF6B9D'],
    
    ['id' => 9, 'name' => 'Shakshuka', 'cat' => 'Breakfast', 'color' => '#FFC75F'],
    ['id' => 10, 'name' => 'Cheese Omelette', 'cat' => 'Breakfast', 'color' => '#FFC75F'],
    ['id' => 11, 'name' => 'Pancakes', 'cat' => 'Breakfast', 'color' => '#FFC75F'],
    ['id' => 12, 'name' => 'French Toast', 'cat' => 'Breakfast', 'color' => '#FFC75F'],
    ['id' => 13, 'name' => 'Breakfast Platter', 'cat' => 'Breakfast', 'color' => '#FFC75F'],
    
    ['id' => 14, 'name' => 'Chicken Shawarma', 'cat' => 'Main Dishes', 'color' => '#E63946'],
    ['id' => 15, 'name' => 'Beef Burger', 'cat' => 'Main Dishes', 'color' => '#E63946'],
    ['id' => 16, 'name' => 'Grilled Salmon', 'cat' => 'Main Dishes', 'color' => '#E63946'],
    ['id' => 17, 'name' => 'Pasta Carbonara', 'cat' => 'Main Dishes', 'color' => '#E63946'],
    ['id' => 18, 'name' => 'Chicken Tikka', 'cat' => 'Main Dishes', 'color' => '#E63946'],
    ['id' => 19, 'name' => 'Vegetable Stir Fry', 'cat' => 'Main Dishes', 'color' => '#E63946'],
    ['id' => 20, 'name' => 'Fish & Chips', 'cat' => 'Main Dishes', 'color' => '#E63946'],
    
    ['id' => 21, 'name' => 'Chocolate Cake', 'cat' => 'Desserts', 'color' => '#D84315'],
    ['id' => 22, 'name' => 'Cheesecake', 'cat' => 'Desserts', 'color' => '#D84315'],
    ['id' => 23, 'name' => 'Tiramisu', 'cat' => 'Desserts', 'color' => '#D84315'],
    ['id' => 24, 'name' => 'Ice Cream Sundae', 'cat' => 'Desserts', 'color' => '#D84315'],
    ['id' => 25, 'name' => 'Baklava', 'cat' => 'Desserts', 'color' => '#D84315'],
    
    ['id' => 26, 'name' => 'French Fries', 'cat' => 'Snacks', 'color' => '#F77F00'],
    ['id' => 27, 'name' => 'Chicken Wings', 'cat' => 'Snacks', 'color' => '#F77F00'],
    ['id' => 28, 'name' => 'Mozzarella Sticks', 'cat' => 'Snacks', 'color' => '#F77F00'],
    ['id' => 29, 'name' => 'Nachos', 'cat' => 'Snacks', 'color' => '#F77F00'],
    ['id' => 30, 'name' => 'Spring Rolls', 'cat' => 'Snacks', 'color' => '#F77F00'],
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Image Setup</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #333; }
        .option { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .option h3 { margin-top: 0; color: #ff9800; }
        .option p { margin: 10px 0; color: #666; }
        button { background: #ff9800; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #e68900; }
        .status { margin-top: 20px; padding: 15px; background: #e8f5e9; border-radius: 5px; display: none; }
        .status.show { display: block; }
        .error { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖼️ Menu Image Setup</h1>
        <p>Choose how you want to set up menu item images:</p>
        
        <div class="option">
            <h3>Option 1: Use Placeholder Service (Recommended)</h3>
            <p>Downloads placeholder images from placeholder.com for all menu items.</p>
            <button onclick="setupPlaceholders()">Download Placeholder Images</button>
        </div>
        
        <div class="option">
            <h3>Option 2: Use Generated Color Blocks</h3>
            <p>Creates simple colored PNG blocks for each category.</p>
            <button onclick="generateColorImages()">Generate Color Block Images</button>
        </div>
        
        <div class="option">
            <h3>Option 3: Manual Upload</h3>
            <p>Upload your own images to: /images/menu/[item_id].jpg</p>
            <p>For example: 1.jpg for Cappuccino, 2.jpg for Cafe Latte, etc.</p>
        </div>
        
        <div id="status" class="status"></div>
    </div>

    <script>
        function showStatus(message, isError = false) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = 'status show' + (isError ? ' error' : '');
        }

        function setupPlaceholders() {
            showStatus('Setting up placeholder images... This may take a moment...');
            fetch('api/setup_images.php?method=placeholder')
                .then(r => r.text())
                .then(text => {
                    showStatus(text, false);
                    setTimeout(() => window.location.href = 'menu.php', 2000);
                })
                .catch(e => showStatus('Error: ' + e.message, true));
        }

        function generateColorImages() {
            showStatus('Generating color block images...');
            fetch('api/setup_images.php?method=colors')
                .then(r => r.text())
                .then(text => {
                    showStatus(text, false);
                    setTimeout(() => window.location.href = 'menu.php', 2000);
                })
                .catch(e => showStatus('Error: ' + e.message, true));
        }
    </script>
</body>
</html>
