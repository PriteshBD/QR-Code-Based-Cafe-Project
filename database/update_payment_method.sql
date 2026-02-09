-- Add payment_method column to orders table
-- Run this if you get "Unknown column 'payment_method'" error

USE cafe_project;

-- Add payment_method column to orders table
ALTER TABLE orders 
ADD COLUMN payment_method VARCHAR(50) DEFAULT 'Cash' AFTER payment_status;

-- Update existing orders with default payment method
UPDATE orders 
SET payment_method = 'Cash' 
WHERE payment_method IS NULL;

SELECT 'Successfully added payment_method column to orders table!' AS Status;

-- Note: If you already ran this and it worked, you don't need to run it again.
