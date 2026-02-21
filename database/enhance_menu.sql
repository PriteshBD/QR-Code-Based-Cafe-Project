-- Enhance menu items table with new features
USE cafe_project;

-- Add veg/non-veg column
ALTER TABLE menu_items ADD COLUMN is_veg TINYINT(1) DEFAULT 1 AFTER is_available;

-- Add spice level column (0=None, 1=Mild, 2=Medium, 3=Spicy)
ALTER TABLE menu_items ADD COLUMN spice_level INT DEFAULT 0 AFTER is_veg;

-- Update existing items with veg/non-veg status and spice level
UPDATE menu_items SET 
    is_veg = CASE item_id
        WHEN 1 THEN 1  -- Cappuccino - Veg
        WHEN 3 THEN 1  -- Latte - Veg
        WHEN 4 THEN 1  -- Americano - Veg
        WHEN 6 THEN 1  -- Caesar Salad - Veg
        WHEN 7 THEN 1  -- Greek Salad - Veg
        WHEN 8 THEN 1  -- Margherita Pizza - Veg
        WHEN 9 THEN 0  -- Pepperoni Pizza - Non-Veg
        WHEN 10 THEN 0 -- Chicken Burger - Non-Veg
        WHEN 12 THEN 1 -- Chocolate Cake - Veg
        WHEN 13 THEN 1 -- Cheesecake - Veg
        WHEN 14 THEN 1 -- Iced Tea - Veg
        WHEN 15 THEN 1 -- Lemonade - Veg
        WHEN 19 THEN 1 -- Mocha - Veg
        ELSE 1
    END,
    spice_level = CASE item_id
        WHEN 10 THEN 1 -- Chicken Burger - Mild
        ELSE 0
    END
WHERE item_id IN (1,3,4,6,7,8,9,10,12,13,14,15,19);

SELECT 'Menu items enhanced successfully!' AS Status;
