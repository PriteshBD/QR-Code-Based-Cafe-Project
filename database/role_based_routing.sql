-- Add item_type column to categorize items for routing
ALTER TABLE menu_items ADD COLUMN item_type VARCHAR(50) DEFAULT 'Cooking' AFTER spice_level;

-- Update items with correct types
UPDATE menu_items SET item_type = 'Beverage' WHERE category IN ('Coffee', 'Beverages', 'Drinks');
UPDATE menu_items SET item_type = 'Cooking' WHERE category IN ('Burgers', 'Pizza', 'Sandwiches', 'Main Dishes', 'Breakfast', 'Snacks', 'Salad', 'Dessert', 'Indian');

-- Add assigned_role column to orders for role-based tracking
ALTER TABLE orders ADD COLUMN assigned_role VARCHAR(50) AFTER payment_method;

-- Create call_log table for manager to track service requests
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

SELECT 'Role-based order routing tables created successfully!' AS Status;
