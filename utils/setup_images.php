<?php
/**
 * Image Setup API
 * Handles placeholder image generation and downloading
 */

header('Content-Type: text/plain; charset=UTF-8');

$imagesDir = __DIR__ . '/../images/menu';
$method = $_GET['method'] ?? 'placeholder';

// Menu items for image setup
$menuItems = range(1, 30);

function downloadPlaceholder($id, $imagesDir) {
    $filename = $imagesDir . '/' . $id . '.jpg';
    if (file_exists($filename)) {
        return "✓ Image $id already exists\n";
    }
    
    $url = "https://via.placeholder.com/160x140.jpg?text=Item+" . urlencode("$id");
    try {
        $data = @file_get_contents($url);
        if ($data === false) {
            return "✗ Failed to download image $id\n";
        }
        file_put_contents($filename, $data);
        return "✓ Downloaded image $id\n";
    } catch (Exception $e) {
        return "✗ Error downloading image $id: " . $e->getMessage() . "\n";
    }
}

function generateColorImage($id, $imagesDir) {
    $filename = $imagesDir . '/' . $id . '.png';
    if (file_exists($filename)) {
        return "✓ Image $id already exists\n";
    }
    
    // Color mapping for categories
    $categoryColors = [
        1 => 0xFF6B9D,  // Drinks (magenta)
        2 => 0xFF6B9D,
        3 => 0xFF6B9D,
        4 => 0xFF6B9D,
        5 => 0xFF6B9D,
        6 => 0xFF6B9D,
        7 => 0xFF6B9D,
        8 => 0xFF6B9D,
        9 => 0xFFC75F,   // Breakfast (yellow)
        10 => 0xFFC75F,
        11 => 0xFFC75F,
        12 => 0xFFC75F,
        13 => 0xFFC75F,
        14 => 0xE63946,  // Main Dishes (red)
        15 => 0xE63946,
        16 => 0xE63946,
        17 => 0xE63946,
        18 => 0xE63946,
        19 => 0xE63946,
        20 => 0xE63946,
        21 => 0xD84315,  // Desserts (orange-red)
        22 => 0xD84315,
        23 => 0xD84315,
        24 => 0xD84315,
        25 => 0xD84315,
        26 => 0xF77F00,  // Snacks (orange)
        27 => 0xF77F00,
        28 => 0xF77F00,
        29 => 0xF77F00,
        30 => 0xF77F00,
    ];
    
    try {
        if (!extension_loaded('gd')) {
            return "✗ GD extension not available for image $id\n";
        }
        
        $img = imagecreatetruecolor(160, 140);
        $color = $categoryColors[$id] ?? 0x888888;
        
        // Convert hex to RGB
        $r = ($color >> 16) & 0xFF;
        $g = ($color >> 8) & 0xFF;
        $b = $color & 0xFF;
        
        $bgColor = imagecolorallocate($img, $r, $g, $b);
        imagefill($img, 0, 0, $bgColor);
        
        // Add text
        $textColor = imagecolorallocate($img, 255, 255, 255);
        imagestring($img, 5, 60, 65, "Item " . $id, $textColor);
        
        imagepng($img, $filename);
        imagedestroy($img);
        return "✓ Generated image $id\n";
    } catch (Exception $e) {
        return "✗ Error generating image $id: " . $e->getMessage() . "\n";
    }
}

// Process request
$output = "Setting up images...\n";
$output .= "========================\n\n";

switch ($method) {
    case 'placeholder':
        foreach ($menuItems as $id) {
            $output .= downloadPlaceholder($id, $imagesDir);
        }
        break;
    case 'colors':
        if (!extension_loaded('gd')) {
            $output .= "⚠️ GD library not available.\n";
            $output .= "Falling back to placeholder service...\n\n";
            foreach ($menuItems as $id) {
                $output .= downloadPlaceholder($id, $imagesDir);
            }
        } else {
            foreach ($menuItems as $id) {
                $output .= generateColorImage($id, $imagesDir);
            }
        }
        break;
}

$output .= "\n========================\n";
$output .= "✓ Image setup complete!\n";
$output .= "Redirecting to menu...\n";

echo $output;
?>
