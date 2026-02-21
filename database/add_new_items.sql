-- Add new menu items for additional images
USE cafe_project;

INSERT INTO menu_items (name, category, price, is_available) VALUES
-- Additional Drinks
('Mocha', 'Drinks', 4.75, 1),
('Iced Tea', 'Drinks', 3.00, 1),
('Lemonade', 'Drinks', 3.50, 1),

-- Salads
('Caesar Salad', 'Salads', 8.50, 1),
('Greek Salad', 'Salads', 8.00, 1),

-- Pizzas
('Margherita Pizza', 'Pizza', 11.50, 1),
('Pepperoni Pizza', 'Pizza', 12.50, 1);

-- Display confirmation
SELECT 'New menu items added successfully!' AS Status;
SELECT item_id, name, category, price FROM menu_items WHERE item_id > 30;
