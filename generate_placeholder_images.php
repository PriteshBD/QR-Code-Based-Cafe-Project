<?php
/**
 * Menu Item Image Generator
 * This script generates placeholder images for all menu items
 * Run this once: http://localhost/QR_Code_Based_Cafe_Project/generate_placeholder_images.php
 */

// Color scheme for different categories
$colorSchemes = [
    'Drinks' => ['bg' => '#FF6B9D', 'text' => '#FFFFFF'],
    'Breakfast' => ['bg' => '#FFC75F', 'text' => '#333333'],
    'Main Dishes' => ['bg' => '#E63946', 'text' => '#FFFFFF'],
    'Desserts' => ['bg' => '#D84315', 'text' => '#FFFFFF'],
    'Snacks' => ['bg' => '#F77F00', 'text' => '#FFFFFF'],
];

// Menu items data
$menuItems = [
    // Drinks
    ['id' => 1, 'name' => 'Cappuccino', 'category' => 'Drinks', 'emoji' => '☕'],
    ['id' => 2, 'name' => 'Cafe Latte', 'category' => 'Drinks', 'emoji' => '☕'],
    ['id' => 3, 'name' => 'Americano', 'category' => 'Drinks', 'emoji' => '☕'],
    ['id' => 4, 'name' => 'Turkish Coffee', 'category' => 'Drinks', 'emoji' => '☕'],
    ['id' => 5, 'name' => 'Mint Tea', 'category' => 'Drinks', 'emoji' => '🍵'],
    ['id' => 6, 'name' => 'Fresh Orange Juice', 'category' => 'Drinks', 'emoji' => '🧃'],
    ['id' => 7, 'name' => 'Mango Smoothie', 'category' => 'Drinks', 'emoji' => '🥤'],
    ['id' => 8, 'name' => 'Hot Chocolate', 'category' => 'Drinks', 'emoji' => '🍫'],
    
    // Breakfast
    ['id' => 9, 'name' => 'Shakshuka', 'category' => 'Breakfast', 'emoji' => '🍳'],
    ['id' => 10, 'name' => 'Cheese Omelette', 'category' => 'Breakfast', 'emoji' => '🍳'],
    ['id' => 11, 'name' => 'Pancakes', 'category' => 'Breakfast', 'emoji' => '🥞'],
    ['id' => 12, 'name' => 'French Toast', 'category' => 'Breakfast', 'emoji' => '🍞'],
    ['id' => 13, 'name' => 'Breakfast Platter', 'category' => 'Breakfast', 'emoji' => '🍽️'],
    
    // Main Dishes
    ['id' => 14, 'name' => 'Chicken Shawarma', 'category' => 'Main Dishes', 'emoji' => '🍗'],
    ['id' => 15, 'name' => 'Beef Burger', 'category' => 'Main Dishes', 'emoji' => '🍔'],
    ['id' => 16, 'name' => 'Grilled Salmon', 'category' => 'Main Dishes', 'emoji' => '🐟'],
    ['id' => 17, 'name' => 'Pasta Carbonara', 'category' => 'Main Dishes', 'emoji' => '🍝'],
    ['id' => 18, 'name' => 'Chicken Tikka', 'category' => 'Main Dishes', 'emoji' => '🍗'],
    ['id' => 19, 'name' => 'Vegetable Stir Fry', 'category' => 'Main Dishes', 'emoji' => '🥘'],
    ['id' => 20, 'name' => 'Fish & Chips', 'category' => 'Main Dishes', 'emoji' => '🍟'],
    
    // Desserts
    ['id' => 21, 'name' => 'Chocolate Cake', 'category' => 'Desserts', 'emoji' => '🍰'],
    ['id' => 22, 'name' => 'Cheesecake', 'category' => 'Desserts', 'emoji' => '🍰'],
    ['id' => 23, 'name' => 'Tiramisu', 'category' => 'Desserts', 'emoji' => '🍮'],
    ['id' => 24, 'name' => 'Ice Cream Sundae', 'category' => 'Desserts', 'emoji' => '🍨'],
    ['id' => 25, 'name' => 'Baklava', 'category' => 'Desserts', 'emoji' => '🥜'],
    
    // Snacks
    ['id' => 26, 'name' => 'French Fries', 'category' => 'Snacks', 'emoji' => '🍟'],
    ['id' => 27, 'name' => 'Chicken Wings', 'category' => 'Snacks', 'emoji' => '🍗'],
    ['id' => 28, 'name' => 'Mozzarella Sticks', 'category' => 'Snacks', 'emoji' => '🧀'],
    ['id' => 29, 'name' => 'Nachos', 'category' => 'Snacks', 'emoji' => '🌮'],
    ['id' => 30, 'name' => 'Spring Rolls', 'category' => 'Snacks', 'emoji' => '🥟'],
];

$imagesDir = __DIR__ . '/images/menu';

// Create images
$generated = 0;
$errors = 0;

foreach ($menuItems as $item) {
    $filename = $imagesDir . '/' . $item['id'] . '.png';
    
    // Skip if already exists
    if (file_exists($filename)) {
        continue;
    }
    
    try {
        $colors = $colorSchemes[$item['category']] ?? ['bg' => '#888888', 'text' => '#FFFFFF'];
        
        // Create image
        $image = imagecreatetruecolor(160, 140);
        
        // Convert hex to RGB
        $bgColor = hex2rgb($colors['bg']);
        $bgImageColor = imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);
        
        // Fill background
        imagefill($image, 0, 0, $bgImageColor);
        
        // Add emoji if available (fallback to colored rectangle)
        $text = $item['emoji'] ?? '🍽️';
        $textColor = hex2rgb($colors['text']);
        $textImageColor = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);
        
        // Draw emoji as big as possible
        $fontSize = 5; // Max font size in GD
        $textBox = imagettfbbox($fontSize, 0, __DIR__ . '/arial.ttf', $text);
        
        // Fallback to using imagestring if TTF not available
        imagestring($image, 5, 70, 60, $text, $textImageColor);
        
        // Save image
        imagepng($image, $filename);
        imagedestroy($image);
        
        $generated++;
        echo "✓ Generated image for: {$item['name']} (ID: {$item['id']})<br>";
        
    } catch (Exception $e) {
        $errors++;
        echo "✗ Error generating image for: {$item['name']} - {$e->getMessage()}<br>";
    }
}

echo "<hr>";
echo "<strong>✓ Successfully generated: $generated images</strong><br>";
if ($errors > 0) {
    echo "<strong>✗ Errors: $errors</strong><br>";
}
echo "<br><a href='menu.php'>← Back to Menu</a>";

function hex2rgb($hex) {
    $hex = ltrim($hex, '#');
    return array(
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    );
}
?>
