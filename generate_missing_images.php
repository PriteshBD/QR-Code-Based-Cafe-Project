<?php
// Generate placeholder images for missing menu items using external service

include 'includes/db_connect.php';

// Fetch all menu items
$sql = "SELECT item_id, name, category FROM menu_items WHERE is_available = 1 ORDER BY item_id";
$result = $conn->query($sql);

$generated = 0;
$skipped = 0;

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $item_id = $row['item_id'];
        $item_name = $row['name'];
        $category = $row['category'];
        
        // Check if image already exists
        $imageBase = __DIR__ . '/images/menu/' . $item_id;
        if (file_exists($imageBase . '.jpg') || file_exists($imageBase . '.png') || file_exists($imageBase . '.webp')) {
            echo "✓ Image exists for ID $item_id: $item_name\n";
            $skipped++;
            continue;
        }
        
        // Generate placeholder using placeholder.com
        $imagePath = __DIR__ . '/images/menu/' . $item_id . '.png';
        
        // Category-based colors (hex format for URL)
        $colors = [
            'Coffee' => '654321',
            'Beverages' => '87CEEB',
            'Drinks' => '87CEEB',
            'Burgers' => 'FFA500',
            'Pizza' => 'FF6347',
            'Sandwiches' => 'F4A460',
            'Salad' => '90EE90',
            'Snacks' => 'FFD700',
            'Dessert' => 'FFB6C1',
            'Indian' => 'FF8C00',
        ];
        
        $color = isset($colors[$category]) ? $colors[$category] : '808080';
        
        // Create simple placeholder text
        $text = urlencode($item_name);
        $url = "https://via.placeholder.com/400x350/$color/FFFFFF?text=$text";
        
        // Download image
        $imageData = @file_get_contents($url);
        
        if ($imageData !== false) {
            file_put_contents($imagePath, $imageData);
            echo "✓ Generated image for ID $item_id: $item_name\n";
            $generated++;
        } else {
            echo "✗ Failed to generate image for ID $item_id: $item_name\n";
        }
        
        // Small delay to avoid rate limiting
        usleep(200000); // 0.2 seconds
    }
}

echo "\nSummary:\n";
echo "✓ Generated: $generated images\n";
echo "✓ Skipped (already exist): $skipped images\n";

$conn->close();
?>
