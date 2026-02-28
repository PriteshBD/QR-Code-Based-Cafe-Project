-- ============================================
-- QR CODE BASED CAFE PROJECT - COMPLETE DATABASE SETUP
-- Last Updated: February 28, 2026
-- Version: 5.1 (UPDATED)
-- ============================================
-- 
-- This is a consolidated database file that includes:
-- ✅ Complete schema (Phase 1-5)
-- ✅ Payment system with CASH ONLY
-- ✅ Inventory tracking
-- ✅ Kitchen Display System (KDS)
-- ✅ Role-based order routing
-- ✅ Admin and staff management
-- ✅ Comprehensive menu items
--
-- Run this SINGLE file to set up the complete database from scratch
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS cafe_project;
USE cafe_project;

-- ============================================
-- SECTION 1: CORE TABLES
-- ============================================

-- 1. Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Menu Items Table (with all enhancements)
CREATE TABLE IF NOT EXISTS menu_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    is_veg TINYINT(1) DEFAULT 1,
    spice_level INT DEFAULT 0,
    item_type VARCHAR(50) DEFAULT 'Cooking',
    stock_quantity INT DEFAULT 999,
    low_stock_threshold INT DEFAULT 50,
    last_restocked TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Orders Table (with payment + approval fields)
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    order_note TEXT,
    payment_status VARCHAR(50) DEFAULT 'Pending',
    payment_id VARCHAR(255) DEFAULT NULL,
    payment_method VARCHAR(50) DEFAULT 'Cash',
    assigned_role VARCHAR(50),
    approved_by INT,
    approval_notes TEXT,
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
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE CASCADE
);

-- 5. Staff Table
CREATE TABLE IF NOT EXISTS staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    role VARCHAR(50),
    salary DECIMAL(10,2),
    salary_per_day DECIMAL(10,2),
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

-- ============================================
-- SECTION 2: PAYMENT SYSTEM TABLES (Phase 4)
-- ============================================

-- 8. Payment Logs Table (for cash payments)
CREATE TABLE IF NOT EXISTS payment_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_id VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    payment_method VARCHAR(50) DEFAULT 'Cash',
    transaction_id VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    INDEX idx_order_id (order_id),
    INDEX idx_payment_id (payment_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- ============================================
-- SECTION 3: NOTIFICATION SYSTEM TABLES (Phase 4)
-- ============================================

-- 9. Notification Logs Table
CREATE TABLE IF NOT EXISTS notification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT,
    staff_role VARCHAR(50) NOT NULL,
    notification_type VARCHAR(50),
    order_id INT,
    table_id INT,
    message VARCHAR(500),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    INDEX idx_staff_role (staff_role),
    INDEX idx_unread (is_read),
    INDEX idx_created (created_at)
);

-- 10. Notification Preferences Table
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    sound_enabled TINYINT(1) DEFAULT 1,
    popup_enabled TINYINT(1) DEFAULT 1,
    check_interval INT DEFAULT 8000,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id),
    UNIQUE KEY unique_staff (staff_id)
);

-- ============================================
-- SECTION 4: INVENTORY SYSTEM TABLES (Phase 5)
-- ============================================

-- 11. Inventory Logs Table
CREATE TABLE IF NOT EXISTS inventory_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    quantity INT,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id),
    INDEX idx_item_id (item_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);

-- ============================================
-- SECTION 5: PAYMENT APPROVAL SYSTEM (Phase 5)
-- ============================================

-- 12. Payment Approvals Table
CREATE TABLE IF NOT EXISTS payment_approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    approved_by INT,
    approval_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (approved_by) REFERENCES staff(staff_id),
    INDEX idx_order_id (order_id),
    INDEX idx_created (created_at)
);

-- ============================================
-- SECTION 6: CALL LOG TABLE (Role-based Routing)
-- ============================================

-- 13. Call Log Table
CREATE TABLE IF NOT EXISTS call_log (
    call_id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NOT NULL,
    call_type VARCHAR(50),
    assigned_to INT,
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (assigned_to) REFERENCES staff(staff_id)
);

-- ============================================
-- SECTION 7: INDEXES FOR PERFORMANCE
-- ============================================

CREATE INDEX idx_orders_payment_status ON orders(payment_status);
CREATE INDEX idx_orders_approved_by ON orders(approved_by);
CREATE INDEX idx_payment_approvals_status ON payment_approvals(new_status);
CREATE INDEX idx_orders_table_id ON orders(table_id);
CREATE INDEX idx_orders_created ON orders(created_at);
CREATE INDEX idx_menu_category ON menu_items(category);
CREATE INDEX idx_menu_availability ON menu_items(is_available);

-- ============================================
-- SECTION 8: VIEWS (Kitchen Display System)
-- ============================================

CREATE VIEW kds_pending_orders AS
SELECT 
    o.order_id, 
    o.table_id, 
    o.created_at, 
    o.order_status, 
    o.estimated_time,
    GROUP_CONCAT(
        CONCAT(
            m.name, 
            ' (', m.category, ') x', oi.quantity, ' - ', 
            CASE WHEN m.is_veg = 1 THEN '🌱' ELSE '🍗' END
        ) SEPARATOR ' | '
    ) as items,
    TIME_FORMAT(TIMEDIFF(NOW(), o.created_at), '%i:%s') as wait_time
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN menu_items m ON oi.item_id = m.item_id
WHERE m.item_type = 'Cooking' AND o.order_status IN ('Pending', 'Cooking')
GROUP BY o.order_id
ORDER BY o.created_at ASC;

-- ============================================
-- SECTION 9: INSERT SAMPLE DATA
-- ============================================

-- Admin User
INSERT INTO admin_users (username, password) VALUES 
('admin', 'admin123');

-- Staff Members
INSERT INTO staff (name, phone, role, salary, join_date) VALUES
('Ahmed', '123456789', 'Head Chef', 3500.00, '2024-01-15'),
('Fatima', '123456790', 'Chef', 2800.00, '2024-02-01'),
('Hassan', '123456791', 'Sous Chef', 3000.00, '2024-01-20'),
('Zainab', '123456792', 'Line Cook', 2500.00, '2024-03-01'),
('Mohamed', '123456793', 'Barista', 2200.00, '2024-02-15'),
('Layla', '123456794', 'Waiter', 1800.00, '2024-03-10'),
('Rashid', '123456795', 'Manager', 4000.00, '2024-01-01'),
('Noor', '123456796', 'Payment Staff', 2000.00, '2024-04-01');

-- Menu Items - COMPREHENSIVE LIST (with all enhancements)

-- DRINKS
INSERT INTO menu_items (name, category, price, description, is_available, is_veg, spice_level, item_type, stock_quantity, low_stock_threshold) VALUES
('Cappuccino', 'Drinks', 4.50, 'Classic Italian coffee with steamed milk foam', 1, 1, 0, 'Beverage', 150, 10),
('Cafe Latte', 'Drinks', 4.00, 'Espresso with steamed milk', 1, 1, 0, 'Beverage', 150, 10),
('Americano', 'Drinks', 3.50, 'Espresso with hot water', 1, 1, 0, 'Beverage', 150, 10),
('Turkish Coffee', 'Drinks', 3.00, 'Traditional Turkish style coffee', 1, 1, 0, 'Beverage', 100, 10),
('Mint Tea', 'Drinks', 2.50, 'Fresh mint leaves tea', 1, 1, 0, 'Beverage', 120, 10),
('Fresh Orange Juice', 'Drinks', 5.00, 'Freshly squeezed orange juice', 1, 1, 0, 'Beverage', 80, 10),
('Mango Smoothie', 'Drinks', 6.00, 'Blended mango with yogurt', 1, 1, 0, 'Beverage', 75, 10),
('Hot Chocolate', 'Drinks', 4.50, 'Rich chocolate drink with whipped cream', 1, 1, 0, 'Beverage', 100, 10),
('Mocha', 'Drinks', 4.75, 'Coffee and chocolate combination', 1, 1, 0, 'Beverage', 100, 10),
('Iced Tea', 'Drinks', 3.00, 'Chilled refreshing tea', 1, 1, 0, 'Beverage', 120, 10),
('Lemonade', 'Drinks', 3.50, 'Fresh lemon juice drink', 1, 1, 0, 'Beverage', 100, 10),
('Espresso', 'Drinks', 3.00, 'Strong concentrated coffee', 1, 1, 0, 'Beverage', 150, 10),
('Cold Coffee', 'Drinks', 5.50, 'Chilled coffee with ice cream', 1, 1, 0, 'Beverage', 100, 10),
('Green Tea', 'Drinks', 2.50, 'Healthy green tea', 1, 1, 0, 'Beverage', 120, 10),
('Masala Chai', 'Drinks', 2.00, 'Spiced traditional tea', 1, 1, 1, 'Beverage', 130, 10),

-- BREAKFAST
('Shakshuka', 'Breakfast', 8.50, 'Poached eggs in tomato sauce with spices', 1, 1, 1, 'Cooking', 50, 20),
('Cheese Omelette', 'Breakfast', 7.00, 'Fluffy omelette with melted cheese', 1, 1, 0, 'Cooking', 60, 20),
('Pancakes', 'Breakfast', 6.50, 'Stack of 3 fluffy pancakes with maple syrup', 1, 1, 0, 'Cooking', 50, 20),
('French Toast', 'Breakfast', 7.50, 'Golden toast with cinnamon and honey', 1, 1, 0, 'Cooking', 50, 20),
('Breakfast Platter', 'Breakfast', 12.00, 'Eggs, sausage, toast, and beans', 1, 0, 0, 'Cooking', 40, 15),

-- MAIN DISHES
('Chicken Shawarma', 'Main Dishes', 10.00, 'Marinated chicken with garlic sauce', 1, 0, 1, 'Cooking', 80, 25),
('Beef Burger', 'Main Dishes', 11.00, 'Juicy beef patty with cheese and fries', 1, 0, 1, 'Cooking', 70, 20),
('Grilled Salmon', 'Main Dishes', 15.00, 'Fresh salmon with lemon butter sauce', 1, 0, 0, 'Cooking', 40, 15),
('Pasta Carbonara', 'Main Dishes', 12.00, 'Creamy pasta with bacon', 1, 0, 0, 'Cooking', 60, 20),
('Chicken Tikka', 'Main Dishes', 13.00, 'Spiced grilled chicken pieces', 1, 0, 2, 'Cooking', 70, 20),
('Vegetable Stir Fry', 'Main Dishes', 9.00, 'Mixed vegetables in Asian sauce', 1, 1, 1, 'Cooking', 50, 20),
('Fish & Chips', 'Main Dishes', 12.50, 'Crispy battered fish with chips', 1, 0, 0, 'Cooking', 60, 20),

-- DESSERTS
('Chocolate Cake', 'Desserts', 5.50, 'Rich chocolate layer cake', 1, 1, 0, 'Cooking', 50, 15),
('Cheesecake', 'Desserts', 6.00, 'Classic New York style cheesecake', 1, 1, 0, 'Cooking', 45, 15),
('Tiramisu', 'Desserts', 6.50, 'Italian coffee-flavored dessert', 1, 1, 0, 'Cooking', 40, 15),
('Ice Cream Sundae', 'Desserts', 5.00, 'Ice cream with toppings', 1, 1, 0, 'Cooking', 60, 20),
('Baklava', 'Desserts', 4.50, 'Sweet pastry with nuts and honey', 1, 1, 0, 'Cooking', 50, 15),

-- SNACKS
('French Fries', 'Snacks', 3.50, 'Crispy golden fries', 1, 1, 0, 'Cooking', 80, 20),
('Chicken Wings', 'Snacks', 8.00, '6 pieces spicy wings', 1, 0, 2, 'Cooking', 70, 20),
('Mozzarella Sticks', 'Snacks', 6.50, 'Breaded mozzarella with marinara sauce', 1, 1, 0, 'Cooking', 60, 20),
('Nachos', 'Snacks', 7.00, 'Tortilla chips with cheese and salsa', 1, 1, 1, 'Cooking', 50, 20),
('Spring Rolls', 'Snacks', 5.50, 'Vegetable spring rolls with sweet chili sauce', 1, 1, 1, 'Cooking', 60, 20),
('Veg Grilled Sandwich', 'Snacks', 3.50, 'Toasted vegetable sandwich', 1, 1, 1, 'Cooking', 50, 15),
('Chicken Grilled Sandwich', 'Snacks', 4.50, 'Grilled chicken sandwich', 1, 0, 1, 'Cooking', 50, 15),
('Club Sandwich', 'Snacks', 5.50, 'Premium triple layer sandwich', 1, 0, 0, 'Cooking', 40, 15),
('Cheese Fries', 'Snacks', 3.99, 'Fries with melted cheese', 1, 1, 0, 'Cooking', 70, 20),

-- SALADS
('Caesar Salad', 'Salads', 8.50, 'Fresh romaine with Caesar dressing', 1, 1, 0, 'Cooking', 50, 15),
('Greek Salad', 'Salads', 8.00, 'Mix of vegetables with feta cheese', 1, 1, 0, 'Cooking', 50, 15),

-- PIZZAS
('Margherita Pizza', 'Pizza', 11.50, 'Classic cheese and tomato pizza', 1, 1, 0, 'Cooking', 40, 15),
('Pepperoni Pizza', 'Pizza', 12.50, 'Pizza with pepperoni slices', 1, 0, 0, 'Cooking', 40, 15),

-- BURGERS
('Veg Burger', 'Burgers', 8.99, 'Vegetable patty with fresh toppings', 1, 1, 1, 'Cooking', 50, 15),
('Paneer Burger', 'Burgers', 9.50, 'Paneer patty burger', 1, 1, 1, 'Cooking', 45, 15),
('Chicken Burger', 'Burgers', 10.99, 'Grilled chicken burger', 1, 0, 1, 'Cooking', 50, 15),

-- INDIAN
('Veg Maggi', 'Indian', 2.50, 'Vegetable noodles', 1, 1, 1, 'Cooking', 80, 20),
('Masala Maggi', 'Indian', 2.99, 'Spiced instant noodles', 1, 1, 2, 'Cooking', 80, 20),
('Veg Pakora', 'Indian', 3.50, 'Vegetable fritters', 1, 1, 2, 'Cooking', 60, 20),
('Samosa', 'Indian', 1.99, 'Traditional fried pastry', 1, 1, 1, 'Cooking', 100, 30),

-- BEVERAGES (Extra)
('Iced Coffee', 'Beverages', 4.50, 'Chilled coffee drink', 1, 1, 0, 'Beverage', 100, 10);

-- Sample Orders for Testing
INSERT INTO orders (table_id, total_amount, payment_status, order_status, payment_method) VALUES
(1, 15.50, 'Paid', 'Served', 'Cash'),
(2, 28.00, 'Paid', 'Ready', 'Cash'),
(3, 42.50, 'Pending', 'Cooking', 'Cash'),
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

-- ============================================
-- SECTION 10: STORED PROCEDURES
-- ============================================

DELIMITER $$

-- Check low stock items
CREATE PROCEDURE IF NOT EXISTS check_low_stock()
BEGIN
    SELECT item_id, name, stock_quantity, low_stock_threshold
    FROM menu_items
    WHERE stock_quantity <= low_stock_threshold AND is_available = 1
    ORDER BY stock_quantity ASC;
END$$

-- Log inventory movement
CREATE PROCEDURE IF NOT EXISTS log_inventory(
    IN p_item_id INT,
    IN p_action VARCHAR(50),
    IN p_quantity INT,
    IN p_notes TEXT
)
BEGIN
    INSERT INTO inventory_logs (item_id, action, quantity, notes)
    VALUES (p_item_id, p_action, p_quantity, p_notes);
END$$

-- Auto-toggle item availability based on stock
CREATE PROCEDURE IF NOT EXISTS update_availability()
BEGIN
    UPDATE menu_items SET is_available = 0 WHERE stock_quantity = 0;
    UPDATE menu_items SET is_available = 1 WHERE stock_quantity > 0;
END$$

-- Get daily revenue
CREATE PROCEDURE IF NOT EXISTS daily_revenue(IN p_date DATE)
BEGIN
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as orders,
        SUM(total_amount) as revenue,
        AVG(total_amount) as avg_order
    FROM orders
    WHERE order_status = 'Served' AND payment_status = 'Paid'
    AND DATE(created_at) = p_date;
END$$

DELIMITER ;

-- ============================================
-- SECTION 11: VERIFICATION QUERIES
-- ============================================

-- Verify all tables created
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME;

-- Verify all views created
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'VIEW';

-- ============================================
-- FINAL VERIFICATION
-- ============================================

SELECT 'Database setup completed successfully!' AS Status;
SELECT COUNT(*) as total_menu_items FROM menu_items;
SELECT COUNT(*) as total_staff FROM staff;
SELECT COUNT(*) as test_orders FROM orders;

-- ============================================
-- PAYMENT SYSTEM - CASH ONLY
-- ============================================
-- 
-- All payments are processed as CASH payments
-- Payment method field stores: 'Cash'
-- No external payment gateway integration required
-- Payments logged to payment_logs table for tracking
--
-- ============================================
-- END OF CONSOLIDATED DATABASE SETUP
-- ============================================

COMMIT;

COMMIT;
