-- Create the database
CREATE DATABASE IF NOT EXISTS cafe_project;
USE cafe_project;

-- Create admin_users table
CREATE TABLE IF NOT EXISTS admin_users (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create menu_items table
CREATE TABLE IF NOT EXISTS menu_items (
  item_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  category VARCHAR(50),
  image VARCHAR(255),
  is_available TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  table_id INT NOT NULL,
  total_amount DECIMAL(10, 2) NOT NULL,
  order_note TEXT,
  payment_status VARCHAR(50) DEFAULT 'Pending',
  order_status VARCHAR(50) DEFAULT 'Pending',
  estimated_time INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
  order_item_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  item_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
  FOREIGN KEY (item_id) REFERENCES menu_items(item_id)
);

-- Create staff table
CREATE TABLE IF NOT EXISTS staff (
  staff_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  role VARCHAR(50) NOT NULL,
  phone VARCHAR(20),
  salary_per_day DECIMAL(10, 2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create attendance table
CREATE TABLE IF NOT EXISTS attendance (
  attendance_id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT NOT NULL,
  date DATE NOT NULL,
  status VARCHAR(50),
  FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
  UNIQUE KEY unique_staff_date (staff_id, date)
);

-- Create service_requests table (for waiter calls)
CREATE TABLE IF NOT EXISTS service_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  table_id INT NOT NULL,
  request_type VARCHAR(50),
  status VARCHAR(50) DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample admin user (username: admin, password: admin123)
INSERT INTO admin_users (username, password) VALUES ('admin', 'admin123');

-- Insert sample menu items
INSERT INTO menu_items (name, price, category) VALUES
('Cappuccino', 5.99, 'Coffee'),
('Espresso', 3.99, 'Coffee'),
('Latte', 6.49, 'Coffee'),
('Americano', 4.49, 'Coffee'),
('Mocha', 7.99, 'Coffee'),
('Caesar Salad', 12.99, 'Salad'),
('Greek Salad', 11.99, 'Salad'),
('Margherita Pizza', 14.99, 'Pizza'),
('Pepperoni Pizza', 15.99, 'Pizza'),
('Chicken Burger', 11.99, 'Burgers'),
('Beef Burger', 12.99, 'Burgers'),
('Chocolate Cake', 6.99, 'Dessert'),
('Cheesecake', 7.99, 'Dessert'),
('Iced Tea', 4.99, 'Beverages'),
('Lemonade', 3.99, 'Beverages');

-- Insert sample staff
INSERT INTO staff (name, role, phone, salary_per_day) VALUES
('Ahmed', 'Head Chef', '123456789', 50.00),
('Fatima', 'Waiter', '123456790', 30.00),
('Hassan', 'Waiter', '123456791', 30.00),
('Zainab', 'Manager', '123456792', 40.00);
