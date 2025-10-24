-- Add missing columns to payments table
ALTER TABLE payments ADD COLUMN payment_reference VARCHAR(100);
ALTER TABLE payments ADD COLUMN transaction_id VARCHAR(100);
ALTER TABLE payments ADD COLUMN processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
