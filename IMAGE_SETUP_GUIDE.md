# Card Layout & Image Setup Guide

## What Changed ✨

### 1. **Card Layout Changed to Box-Type** 🎨
- **Before**: Horizontal cards (image left, info middle, button right)
- **After**: Vertical box cards displayed in a responsive grid
- Cards now show product images on top with larger, better visibility
- Better hover effect with elevation animation
- Mobile-friendly grid that adapts to screen size

### 2. **Image Directory Structure** 📁
Created: `images/menu/` directory for all menu item images
- Expected naming: `{item_id}.jpg`, `{item_id}.png`, or `{item_id}.webp`
- Examples: `1.jpg` (Cappuccino), `15.jpg` (Beef Burger), etc.

---

## How to Set Up Images 🖼️

### **Quick Setup (Recommended)**

1. Open your browser and go to:
   ```
   http://localhost/QR_Code_Based_Cafe_Project/setup_images.php
   ```

2. Choose one of three options:
   - **Option 1**: Download placeholder images (easiest, no requirements)
   - **Option 2**: Generate color-coded blocks (requires GD library)
   - **Option 3**: Upload your own images manually

### **Manual Image Setup**

If you want to add real images:
1. Place images in folder: `images/menu/`
2. Name them: `1.jpg`, `2.jpg`, `3.jpg`, etc. (matching item IDs)
3. Supported formats: `.jpg`, `.png`, `.webp`
4. Recommended size: 160×140 pixels (will auto-fit)

### **Free Image Sources**
- Unsplash: https://unsplash.com/
- Pexels: https://www.pexels.com/
- Pixabay: https://pixabay.com/
- Flaticon: https://www.flaticon.com/

---

## Menu Item IDs & Names 📋

### Drinks (IDs 1-8)
- 1: Cappuccino
- 2: Cafe Latte
- 3: Americano
- 4: Turkish Coffee
- 5: Mint Tea
- 6: Fresh Orange Juice
- 7: Mango Smoothie
- 8: Hot Chocolate

### Breakfast (IDs 9-13)
- 9: Shakshuka
- 10: Cheese Omelette
- 11: Pancakes
- 12: French Toast
- 13: Breakfast Platter

### Main Dishes (IDs 14-20)
- 14: Chicken Shawarma
- 15: Beef Burger
- 16: Grilled Salmon
- 17: Pasta Carbonara
- 18: Chicken Tikka
- 19: Vegetable Stir Fry
- 20: Fish & Chips

### Desserts (IDs 21-25)
- 21: Chocolate Cake
- 22: Cheesecake
- 23: Tiramisu
- 24: Ice Cream Sundae
- 25: Baklava

### Snacks (IDs 26-30)
- 26: French Fries
- 27: Chicken Wings
- 28: Mozzarella Sticks
- 29: Nachos
- 30: Spring Rolls

---

## Files Modified ✅

1. **`menu.php`**
   - Changed CSS from horizontal flex layout to CSS Grid
   - Updated card styling for box-type display
   - Card images now 160×140px, displayed on top
   - Added hover effects and better spacing

2. **`setup_images.php`** (NEW)
   - User-friendly image setup interface
   - Options to download or generate images

3. **`api/setup_images.php`** (NEW)
   - Backend API for image setup
   - Supports placeholder downloads and color generation

---

## Testing the Changes 🧪

1. Start XAMPP (Apache + MySQL)
2. Visit: `http://localhost/QR_Code_Based_Cafe_Project/menu.php`
3. You should see box-type cards in a grid layout
4. Cards show placeholders if no images are set up yet

---

## Styling Details 🎨

### Card Properties
- Grid columns: `repeat(auto-fill, minmax(160px, 1fr))`
- Responsive: adjusts from 2-6 columns based on screen width
- Shadow: `0 4px 8px rgba(0,0,0,0.1)`
- Hover effect: lifts up 5px with enhanced shadow

### Color Scheme by Category
- **Drinks**: Magenta (#FF6B9D)
- **Breakfast**: Yellow (#FFC75F)
- **Main Dishes**: Red (#E63946)
- **Desserts**: Orange-Red (#D84315)
- **Snacks**: Orange (#F77F00)

---

## Need Help? 💡

If images don't appear:
1. Check browser console for broken image errors
2. Verify files are in `images/menu/` folder
3. Check file names match item IDs (1.jpg, 2.jpg, etc.)
4. Run `setup_images.php` to auto-generate placeholders
