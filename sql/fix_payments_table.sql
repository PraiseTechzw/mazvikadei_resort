-- Fix payments table structure
-- Add missing columns to payments table

-- First, check if payments table exists, if not create it
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    customer_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    payment_reference VARCHAR(100),
    transaction_id VARCHAR(100),
    gateway_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add missing columns if they don't exist
ALTER TABLE payments ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(100);
ALTER TABLE payments ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(100);
ALTER TABLE payments ADD COLUMN IF NOT EXISTS gateway_response TEXT;
ALTER TABLE payments ADD COLUMN IF NOT EXISTS currency VARCHAR(3) DEFAULT 'USD';

-- Add indexes for better performance
ALTER TABLE payments ADD INDEX IF NOT EXISTS idx_booking_id (booking_id);
ALTER TABLE payments ADD INDEX IF NOT EXISTS idx_customer_id (customer_id);
ALTER TABLE payments ADD INDEX IF NOT EXISTS idx_status (status);
ALTER TABLE payments ADD INDEX IF NOT EXISTS idx_payment_reference (payment_reference);
