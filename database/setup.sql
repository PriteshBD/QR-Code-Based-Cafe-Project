-- QR Code Based Cafe Project - Database Setup
-- Created: February 9, 2026
-- Run this script to create the database and all required tables

-- Create Database
CREATE DATABASE IF NOT EXISTS cafe_project;
USE cafe_project;

-- 1. Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Menu Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Orders Table
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    order_note TEXT,
    payment_status VARCHAR(50) DEFAULT 'Pending',
    payment_method VARCHAR(50) DEFAULT 'Cash',
    order_status VARCHAR(50) DEFAULT 'Pending',
    estimated_time INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    item_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id)
);

-- 5. Staff Table
CREATE TABLE IF NOT EXISTS staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    role VARCHAR(50),
    salary DECIMAL(10,2),
    join_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent') DEFAULT 'Absent',
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (staff_id, date)
);

-- 7. Service Requests Table
CREATE TABLE IF NOT EXISTS service_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NOT NULL,
    request_type VARCHAR(50),
    status VARCHAR(20) DEFAULT 'Pending',
    request_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Sample Data

-- Admin User
INSERT INTO admin_users (username, password) VALUES 
('admin', 'admin123');

-- Staff Members
INSERT INTO staff (name, phone, role, salary, join_date) VALUES
('Ahmed', '123456789', 'Head Chef', 3500.00, '2024-01-15'),
('Fatima', '123456790', 'Chef', 2800.00, '2024-02-01'),
('Hassan', '123456791', 'Sous Chef', 3000.00, '2024-01-20'),
('Zainab', '123456792', 'Line Cook', 2500.00, '2024-03-01');

-- Menu Items
INSERT INTO menu_items (name, category, price, description, is_available) VALUES
-- Drinks
('Cappuccino', 'Drinks', 4.50, 'Classic Italian coffee with steamed milk foam', 1),
('Cafe Latte', 'Drinks', 4.00, 'Espresso with steamed milk', 1),
('Americano', 'Drinks', 3.50, 'Espresso with hot water', 1),
('Turkish Coffee', 'Drinks', 3.00, 'Traditional Turkish style coffee', 1),
('Mint Tea', 'Drinks', 2.50, 'Fresh mint leaves tea', 1),
('Fresh Orange Juice', 'Drinks', 5.00, 'Freshly squeezed orange juice', 1),
('Mango Smoothie', 'Drinks', 6.00, 'Blended mango with yogurt', 1),
('Hot Chocolate', 'Drinks', 4.50, 'Rich chocolate drink with whipped cream', 1),

-- Breakfast
('Shakshuka', 'Breakfast', 8.50, 'Poached eggs in tomato sauce with spices', 1),
('Cheese Omelette', 'Breakfast', 7.00, 'Fluffy omelette with melted cheese', 1),
('Pancakes', 'Breakfast', 6.50, 'Stack of 3 fluffy pancakes with maple syrup', 1),
('French Toast', 'Breakfast', 7.50, 'Golden toast with cinnamon and honey', 1),
('Breakfast Platter', 'Breakfast', 12.00, 'Eggs, sausage, toast, and beans', 1),

-- Main Dishes
('Chicken Shawarma', 'Main Dishes', 10.00, 'Marinated chicken with garlic sauce', 1),
('Beef Burger', 'Main Dishes', 11.00, 'Juicy beef patty with cheese and fries', 1),
('Grilled Salmon', 'Main Dishes', 15.00, 'Fresh salmon with lemon butter sauce', 1),
('Pasta Carbonara', 'Main Dishes', 12.00, 'Creamy pasta with bacon', 1),
('Chicken Tikka', 'Main Dishes', 13.00, 'Spiced grilled chicken pieces', 1),
('Vegetable Stir Fry', 'Main Dishes', 9.00, 'Mixed vegetables in Asian sauce', 1),
('Fish & Chips', 'Main Dishes', 12.50, 'Crispy battered fish with chips', 1),

-- Desserts
('Chocolate Cake', 'Desserts', 5.50, 'Rich chocolate layer cake', 1),
('Cheesecake', 'Desserts', 6.00, 'Classic New York style cheesecake', 1),
('Tiramisu', 'Desserts', 6.50, 'Italian coffee-flavored dessert', 1),
('Ice Cream Sundae', 'Desserts', 5.00, 'Ice cream with toppings', 1),
('Baklava', 'Desserts', 4.50, 'Sweet pastry with nuts and honey', 1),

-- Snacks
('French Fries', 'Snacks', 3.50, 'Crispy golden fries', 1),
('Chicken Wings', 'Snacks', 8.00, '6 pieces spicy wings', 1),
('Mozzarella Sticks', 'Snacks', 6.50, 'Breaded mozzarella with marinara sauce', 1),
('Nachos', 'Snacks', 7.00, 'Tortilla chips with cheese and salsa', 1),
('Spring Rolls', 'Snacks', 5.50, 'Vegetable spring rolls with sweet chili sauce', 1);

-- Sample Orders (Optional - for testing)
INSERT INTO orders (table_id, total_amount, payment_status, order_status, payment_method) VALUES
(1, 15.50, 'Paid', 'Served', 'Cash'),
(2, 28.00, 'Paid', 'Ready', 'UPI'),
(3, 42.50, 'Paid', 'Cooking', 'Cash'),
(5, 19.00, 'Pending', 'Pending', 'Cash');

-- Sample Order Items
INSERT INTO order_items (order_id, item_id, quantity, price) VALUES
(1, 1, 2, 4.50),
(1, 26, 1, 3.50),
(2, 14, 2, 10.00),
(2, 3, 2, 3.50),
(3, 16, 1, 15.00),
(3, 18, 2, 12.00),
(3, 21, 1, 5.50),
(4, 8, 1, 4.50),
(4, 10, 1, 7.00),
(4, 26, 1, 3.50);

COMMIT;

-- Display Success Message
SELECT 'Database setup completed successfully!' AS Status;
