-- =====================================================
-- MAZVIKADEI RESORT SAFE DATABASE MIGRATION SCRIPT
-- =====================================================
-- This script safely updates the existing database to include all enhancements
-- It checks for column existence before adding indexes

USE mazvikadei;

-- =====================================================
-- UPDATE EXISTING TABLES SAFELY
-- =====================================================

-- Update users table with enhanced security fields
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS status ENUM('active','inactive','suspended') DEFAULT 'active' AFTER role,
ADD COLUMN IF NOT EXISTS last_login DATETIME NULL AFTER created_at,
ADD COLUMN IF NOT EXISTS login_attempts INT DEFAULT 0 AFTER last_login,
ADD COLUMN IF NOT EXISTS locked_until DATETIME NULL AFTER login_attempts,
ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER locked_until,
ADD COLUMN IF NOT EXISTS address TEXT AFTER phone,
ADD COLUMN IF NOT EXISTS date_of_birth DATE AFTER address,
ADD COLUMN IF NOT EXISTS preferences JSON AFTER date_of_birth;

-- Update rooms table with enhanced features
ALTER TABLE rooms 
ADD COLUMN IF NOT EXISTS description TEXT AFTER title,
ADD COLUMN IF NOT EXISTS gallery JSON AFTER image,
ADD COLUMN IF NOT EXISTS room_type ENUM('standard','deluxe','suite','villa') DEFAULT 'standard' AFTER gallery,
ADD COLUMN IF NOT EXISTS max_occupancy INT DEFAULT 2 AFTER room_type,
ADD COLUMN IF NOT EXISTS bed_type ENUM('single','double','queen','king','twin') DEFAULT 'double' AFTER max_occupancy,
ADD COLUMN IF NOT EXISTS featured TINYINT(1) DEFAULT 0 AFTER bed_type,
ADD COLUMN IF NOT EXISTS updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER featured;

-- Update activities table with enhanced features
ALTER TABLE activities 
ADD COLUMN IF NOT EXISTS short_description VARCHAR(500) AFTER description,
ADD COLUMN IF NOT EXISTS image VARCHAR(500) AFTER schedule,
ADD COLUMN IF NOT EXISTS gallery JSON AFTER image,
ADD COLUMN IF NOT EXISTS category ENUM('adventure','relaxation','sports','cultural','nature') DEFAULT 'adventure' AFTER gallery,
ADD COLUMN IF NOT EXISTS difficulty_level ENUM('easy','medium','hard') DEFAULT 'easy' AFTER category,
ADD COLUMN IF NOT EXISTS min_participants INT DEFAULT 1 AFTER difficulty_level,
ADD COLUMN IF NOT EXISTS max_participants INT DEFAULT 20 AFTER min_participants,
ADD COLUMN IF NOT EXISTS available TINYINT(1) DEFAULT 1 AFTER max_participants,
ADD COLUMN IF NOT EXISTS featured TINYINT(1) DEFAULT 0 AFTER available,
ADD COLUMN IF NOT EXISTS updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER featured;

-- Update events table with enhanced features
ALTER TABLE events 
ADD COLUMN IF NOT EXISTS short_description VARCHAR(500) AFTER description,
ADD COLUMN IF NOT EXISTS image VARCHAR(500) AFTER capacity,
ADD COLUMN IF NOT EXISTS gallery JSON AFTER image,
ADD COLUMN IF NOT EXISTS event_type ENUM('wedding','corporate','birthday','conference','other') DEFAULT 'other' AFTER gallery,
ADD COLUMN IF NOT EXISTS venue_type ENUM('indoor','outdoor','both') DEFAULT 'outdoor' AFTER event_type,
ADD COLUMN IF NOT EXISTS available TINYINT(1) DEFAULT 1 AFTER venue_type,
ADD COLUMN IF NOT EXISTS featured TINYINT(1) DEFAULT 0 AFTER available,
ADD COLUMN IF NOT EXISTS updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER featured;

-- Update bookings table with enhanced features
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS booking_reference VARCHAR(20) UNIQUE AFTER id,
ADD COLUMN IF NOT EXISTS customer_id INT AFTER items_json,
ADD COLUMN IF NOT EXISTS check_in_date DATE AFTER customer_phone,
ADD COLUMN IF NOT EXISTS check_out_date DATE AFTER check_in_date,
ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER check_out_date,
ADD COLUMN IF NOT EXISTS deposit_amount DECIMAL(10,2) DEFAULT 0 AFTER total_amount,
ADD COLUMN IF NOT EXISTS balance_amount DECIMAL(10,2) DEFAULT 0 AFTER deposit_amount,
ADD COLUMN IF NOT EXISTS special_requests TEXT AFTER extras,
ADD COLUMN IF NOT EXISTS payment_status ENUM('pending','partial','paid','refunded') DEFAULT 'pending' AFTER status,
ADD COLUMN IF NOT EXISTS updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- =====================================================
-- CREATE NEW TABLES
-- =====================================================

-- Room availability tracking
CREATE TABLE IF NOT EXISTS room_availability (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  date DATE NOT NULL,
  available TINYINT(1) DEFAULT 1,
  price_override DECIMAL(10,2) NULL,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  UNIQUE KEY unique_room_date (room_id, date),
  INDEX idx_date (date),
  INDEX idx_available (available)
);

-- Event bookings (separate from general bookings)
CREATE TABLE IF NOT EXISTS event_bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  booking_id BIGINT NOT NULL,
  event_date DATE NOT NULL,
  start_time TIME,
  end_time TIME,
  guest_count INT DEFAULT 1,
  special_requirements TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  INDEX idx_event_id (event_id),
  INDEX idx_booking_id (booking_id),
  INDEX idx_event_date (event_date)
);

-- Activity bookings (separate from general bookings)
CREATE TABLE IF NOT EXISTS activity_bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  activity_id INT NOT NULL,
  booking_id BIGINT NOT NULL,
  activity_date DATE NOT NULL,
  start_time TIME,
  participant_count INT DEFAULT 1,
  special_requirements TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  INDEX idx_activity_id (activity_id),
  INDEX idx_booking_id (booking_id),
  INDEX idx_activity_date (activity_date)
);

-- Payment transactions
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('cash','card','bank_transfer','paynow','ecocash','stripe','paypal') NOT NULL,
  payment_reference VARCHAR(100),
  status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
  transaction_id VARCHAR(100),
  gateway_response JSON,
  processed_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  INDEX idx_booking_id (booking_id),
  INDEX idx_payment_method (payment_method),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
);

-- Contact messages
CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  email VARCHAR(200) NOT NULL,
  phone VARCHAR(20),
  subject VARCHAR(200),
  message TEXT NOT NULL,
  status ENUM('new','read','replied','closed') DEFAULT 'new',
  priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
  assigned_to INT NULL,
  response TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_status (status),
  INDEX idx_priority (priority),
  INDEX idx_assigned_to (assigned_to),
  INDEX idx_created_at (created_at)
);

-- Newsletter subscriptions
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(200) UNIQUE NOT NULL,
  name VARCHAR(200),
  status ENUM('active','unsubscribed','bounced') DEFAULT 'active',
  subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  unsubscribed_at DATETIME NULL,
  INDEX idx_email (email),
  INDEX idx_status (status)
);

-- Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(200) NOT NULL,
  token VARCHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_token (token),
  INDEX idx_email (email),
  INDEX idx_expires_at (expires_at)
);

-- Admin activity logs
CREATE TABLE IF NOT EXISTS admin_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  action VARCHAR(100) NOT NULL,
  description TEXT,
  ip_address VARCHAR(45),
  user_agent TEXT,
  target_id INT NULL,
  target_type VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_admin_id (admin_id),
  INDEX idx_action (action),
  INDEX idx_created_at (created_at)
);

-- User sessions (for enhanced session management)
CREATE TABLE IF NOT EXISTS user_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  session_token VARCHAR(64) UNIQUE NOT NULL,
  ip_address VARCHAR(45),
  user_agent TEXT,
  expires_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_session_token (session_token),
  INDEX idx_expires_at (expires_at)
);

-- System settings
CREATE TABLE IF NOT EXISTS system_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_value TEXT,
  setting_type ENUM('string','number','boolean','json') DEFAULT 'string',
  description TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_setting_key (setting_key)
);

-- Email templates
CREATE TABLE IF NOT EXISTS email_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  template_key VARCHAR(100) UNIQUE NOT NULL,
  subject VARCHAR(200) NOT NULL,
  body TEXT NOT NULL,
  variables JSON,
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_template_key (template_key),
  INDEX idx_active (active)
);

-- =====================================================
-- UPDATE EXISTING DATA
-- =====================================================

-- Update existing rooms with new fields
UPDATE rooms SET 
  room_type = 'suite',
  max_occupancy = 2,
  bed_type = 'king',
  featured = 1
WHERE id = 1;

UPDATE rooms SET 
  room_type = 'standard',
  max_occupancy = 2,
  bed_type = 'queen',
  featured = 0
WHERE id = 2;

UPDATE rooms SET 
  room_type = 'villa',
  max_occupancy = 6,
  bed_type = 'king',
  featured = 1
WHERE id = 3;

-- Add new rooms with enhanced data
INSERT INTO rooms (id, title, description, price, amenities, image, room_type, max_occupancy, bed_type, featured) VALUES
(4, 'Deluxe Mountain View', 'Premium room with breathtaking mountain views', 280.00, 'King bed, Mountain view, Private balcony, Air conditioning, WiFi, Room service', 'assets/rooms/mountain.jpg', 'deluxe', 2, 'king', 0),
(5, 'Standard Room', 'Comfortable and affordable accommodation', 120.00, 'Double bed, Air conditioning, WiFi, Coffee maker', 'assets/rooms/standard.jpg', 'standard', 2, 'double', 0)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Update existing activities with new fields
UPDATE activities SET 
  category = 'relaxation',
  difficulty_level = 'easy',
  max_participants = 12,
  featured = 1
WHERE id = 1;

UPDATE activities SET 
  category = 'nature',
  difficulty_level = 'medium',
  max_participants = 8,
  featured = 0
WHERE id = 2;

UPDATE activities SET 
  category = 'nature',
  difficulty_level = 'easy',
  max_participants = 15,
  featured = 0
WHERE id = 3;

UPDATE activities SET 
  category = 'sports',
  difficulty_level = 'easy',
  max_participants = 50,
  featured = 0
WHERE id = 4;

UPDATE activities SET 
  category = 'cultural',
  difficulty_level = 'easy',
  max_participants = 20,
  featured = 1
WHERE id = 5;

UPDATE activities SET 
  category = 'adventure',
  difficulty_level = 'medium',
  max_participants = 30,
  featured = 0
WHERE id = 6;

UPDATE activities SET 
  category = 'adventure',
  difficulty_level = 'medium',
  max_participants = 15,
  featured = 0
WHERE id = 7;

-- Add new activities
INSERT INTO activities (id, title, description, short_description, duration, price, schedule, category, difficulty_level, max_participants, featured) VALUES
(8, 'Bird Watching Tours', 'Guided bird watching with expert naturalists', 'Expert-led bird watching', '2 hours', 25.00, 'Daily 06:00', 'nature', 'easy', 10, 0)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Add events
INSERT INTO events (id, title, description, short_description, price, capacity, event_type, venue_type, featured) VALUES
(1, 'Wedding Venue', 'Beautiful outdoor wedding venue with stunning dam views', 'Romantic wedding venue with dam views', 500.00, 150, 'wedding', 'outdoor', 1),
(2, 'Corporate Conference', 'Professional conference facilities with modern amenities', 'Business conference and meeting space', 300.00, 100, 'corporate', 'indoor', 0),
(3, 'Birthday Celebrations', 'Perfect venue for birthday parties and celebrations', 'Birthday party venue', 200.00, 50, 'birthday', 'both', 0),
(4, 'Anniversary Celebrations', 'Romantic venue for anniversary celebrations', 'Anniversary celebration venue', 250.00, 80, 'other', 'outdoor', 0)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Mazvikadei Resort', 'string', 'Name of the resort'),
('site_email', 'info@mazvikadei.local', 'string', 'Main contact email'),
('booking_email', 'bookings@mazvikadei.local', 'string', 'Booking confirmation email'),
('admin_email', 'admin@mazvikadei.local', 'string', 'Admin notification email'),
('currency', 'USD', 'string', 'Default currency'),
('timezone', 'Africa/Harare', 'string', 'System timezone'),
('booking_advance_days', '30', 'number', 'Maximum days in advance for booking'),
('cancellation_hours', '24', 'number', 'Hours before check-in for cancellation'),
('deposit_percentage', '30', 'number', 'Default deposit percentage'),
('maintenance_mode', 'false', 'boolean', 'Maintenance mode status')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Insert email templates
INSERT INTO email_templates (template_key, subject, body, variables) VALUES
('booking_confirmation', 'Booking Confirmation - {{booking_reference}}', 'Dear {{customer_name}},\n\nYour booking has been confirmed!\n\nBooking Reference: {{booking_reference}}\nCheck-in: {{check_in_date}}\nCheck-out: {{check_out_date}}\nTotal Amount: ${{total_amount}}\n\nThank you for choosing Mazvikadei Resort!\n\nBest regards,\nMazvikadei Resort Team', '["booking_reference","customer_name","check_in_date","check_out_date","total_amount"]'),
('booking_cancellation', 'Booking Cancelled - {{booking_reference}}', 'Dear {{customer_name}},\n\nYour booking {{booking_reference}} has been cancelled.\n\nIf you have any questions, please contact us.\n\nBest regards,\nMazvikadei Resort Team', '["booking_reference","customer_name"]'),
('password_reset', 'Password Reset Request', 'Dear {{name}},\n\nYou requested a password reset. Click the link below to reset your password:\n\n{{reset_link}}\n\nThis link will expire in 1 hour.\n\nIf you didn\'t request this reset, please ignore this email.\n\nBest regards,\nMazvikadei Resort Team', '["name","reset_link"]')
ON DUPLICATE KEY UPDATE subject=VALUES(subject);

-- =====================================================
-- ADD INDEXES SAFELY (only if columns exist)
-- =====================================================

-- Add indexes to users table
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_email (email);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_role (role);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_status (status);

-- Add indexes to rooms table (only if columns exist)
-- Check if room_type column exists before adding index
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'rooms' 
     AND COLUMN_NAME = 'room_type') > 0,
    'ALTER TABLE rooms ADD INDEX IF NOT EXISTS idx_room_type (room_type)',
    'SELECT "room_type column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if featured column exists before adding index
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'rooms' 
     AND COLUMN_NAME = 'featured') > 0,
    'ALTER TABLE rooms ADD INDEX IF NOT EXISTS idx_featured (featured)',
    'SELECT "featured column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes to activities table (only if columns exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'activities' 
     AND COLUMN_NAME = 'category') > 0,
    'ALTER TABLE activities ADD INDEX IF NOT EXISTS idx_category (category)',
    'SELECT "category column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'activities' 
     AND COLUMN_NAME = 'featured') > 0,
    'ALTER TABLE activities ADD INDEX IF NOT EXISTS idx_featured (featured)',
    'SELECT "featured column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes to events table (only if columns exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'events' 
     AND COLUMN_NAME = 'event_type') > 0,
    'ALTER TABLE events ADD INDEX IF NOT EXISTS idx_event_type (event_type)',
    'SELECT "event_type column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'events' 
     AND COLUMN_NAME = 'featured') > 0,
    'ALTER TABLE events ADD INDEX IF NOT EXISTS idx_featured (featured)',
    'SELECT "featured column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes to bookings table (only if columns exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'bookings' 
     AND COLUMN_NAME = 'booking_reference') > 0,
    'ALTER TABLE bookings ADD INDEX IF NOT EXISTS idx_booking_reference (booking_reference)',
    'SELECT "booking_reference column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'bookings' 
     AND COLUMN_NAME = 'customer_id') > 0,
    'ALTER TABLE bookings ADD INDEX IF NOT EXISTS idx_customer_id (customer_id)',
    'SELECT "customer_id column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'bookings' 
     AND COLUMN_NAME = 'status') > 0,
    'ALTER TABLE bookings ADD INDEX IF NOT EXISTS idx_status (status)',
    'SELECT "status column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'bookings' 
     AND COLUMN_NAME = 'payment_status') > 0,
    'ALTER TABLE bookings ADD INDEX IF NOT EXISTS idx_payment_status (payment_status)',
    'SELECT "payment_status column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'bookings' 
     AND COLUMN_NAME = 'check_in_date') > 0,
    'ALTER TABLE bookings ADD INDEX IF NOT EXISTS idx_check_in_date (check_in_date)',
    'SELECT "check_in_date column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'mazvikadei' 
     AND TABLE_NAME = 'bookings' 
     AND COLUMN_NAME = 'created_at') > 0,
    'ALTER TABLE bookings ADD INDEX IF NOT EXISTS idx_created_at (created_at)',
    'SELECT "created_at column does not exist" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'Safe Database Migration Complete!' as message,
       'All tables have been updated with enhanced features' as status,
       'Admin Login: admin@mazvikadei.local / Admin@123' as admin_info;
