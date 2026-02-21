-- Add more menu items to expand the menu
USE cafe_project;

INSERT INTO menu_items (name, category, price, is_available, is_veg, spice_level) VALUES
-- More Coffee
('Espresso', 'Coffee', 300, 1, 1, 0),
('Cold Coffee', 'Coffee', 550, 1, 1, 0),

-- More Beverages
('Green Tea', 'Beverages', 250, 1, 1, 0),
('Masala Chai', 'Beverages', 200, 1, 1, 1),

-- Sandwiches
('Veg Grilled Sandwich', 'Sandwiches', 350, 1, 1, 1),
('Chicken Grilled Sandwich', 'Sandwiches', 450, 1, 0, 1),
('Club Sandwich', 'Sandwiches', 550, 1, 0, 1),

-- More Burgers  
('Veg Burger', 'Burgers', 899, 1, 1, 1),
('Paneer Burger', 'Burgers', 950, 1, 1, 1),
('Beef Burger', 'Burgers', 1099, 1, 0, 2),

-- Snacks
('French Fries', 'Snacks', 299, 1, 1, 0),
('Cheese Fries', 'Snacks', 399, 1, 1, 0),
('Nachos', 'Snacks', 450, 1, 1, 1),
('Spring Rolls', 'Snacks', 499, 1, 1, 1),
('Chicken Wings', 'Snacks', 650, 1, 0, 2),

-- Indian Snacks
('Veg Maggi', 'Indian', 250, 1, 1, 1),
('Masala Maggi', 'Indian', 299, 1, 1, 2),
('Veg Pakora', 'Indian', 350, 1, 1, 2),
('Samosa', 'Indian', 199, 1, 1, 2);

SELECT 'New menu items added successfully!' AS Status;
SELECT item_id, name, category, price, is_veg FROM menu_items WHERE item_id > 19 ORDER BY category, item_id;
