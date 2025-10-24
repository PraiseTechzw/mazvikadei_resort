-- =====================================================
-- MAZVIKADEI RESORT BOOKING SYSTEM - COMPLETE DATABASE SETUP
-- =====================================================
-- This file contains the complete database schema with all enhancements
-- Run this file to set up the complete Mazvikadei Resort system

-- Create database
CREATE DATABASE IF NOT EXISTS mazvikadei CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mazvikadei;

-- =====================================================
-- CORE TABLES
-- =====================================================

-- Users table (admin and customers) with enhanced security
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(200) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(200) NOT NULL,
  role ENUM('admin','customer') DEFAULT 'customer',
  status ENUM('active','inactive','suspended') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME NULL,
  login_attempts INT DEFAULT 0,
  locked_until DATETIME NULL,
  phone VARCHAR(20),
  address TEXT,
  date_of_birth DATE,
  preferences JSON,
  INDEX idx_email (email),
  INDEX idx_role (role),
  INDEX idx_status (status)
);

-- Rooms table with enhanced features
CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  amenities TEXT,
  image VARCHAR(500),
  gallery JSON,
  room_type ENUM('standard','deluxe','suite','villa') DEFAULT 'standard',
  max_occupancy INT DEFAULT 2,
  bed_type ENUM('single','double','queen','king','twin') DEFAULT 'double',
  available TINYINT(1) DEFAULT 1,
  featured TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_available (available),
  INDEX idx_room_type (room_type),
  INDEX idx_featured (featured)
);

-- Activities table with enhanced features
CREATE TABLE IF NOT EXISTS activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  short_description VARCHAR(500),
  duration VARCHAR(100),
  price DECIMAL(10,2),
  schedule TEXT,
  image VARCHAR(500),
  gallery JSON,
  category ENUM('adventure','relaxation','sports','cultural','nature') DEFAULT 'adventure',
  difficulty_level ENUM('easy','medium','hard') DEFAULT 'easy',
  min_participants INT DEFAULT 1,
  max_participants INT DEFAULT 20,
  available TINYINT(1) DEFAULT 1,
  featured TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_available (available),
  INDEX idx_category (category),
  INDEX idx_featured (featured)
);

-- Events table (venue bookings) with enhanced features
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  short_description VARCHAR(500),
  price DECIMAL(10,2),
  capacity INT DEFAULT 0,
  image VARCHAR(500),
  gallery JSON,
  event_type ENUM('wedding','corporate','birthday','conference','other') DEFAULT 'other',
  venue_type ENUM('indoor','outdoor','both') DEFAULT 'outdoor',
  available TINYINT(1) DEFAULT 1,
  featured TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_available (available),
  INDEX idx_event_type (event_type),
  INDEX idx_featured (featured)
);

-- =====================================================
-- BOOKING SYSTEM TABLES
-- =====================================================

-- Enhanced bookings table
CREATE TABLE IF NOT EXISTS bookings (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  booking_reference VARCHAR(20) UNIQUE NOT NULL,
  type ENUM('room','activity','event') NOT NULL,
  items_json JSON NOT NULL,
  customer_id INT,
  customer_name VARCHAR(200) NOT NULL,
  customer_email VARCHAR(200) NOT NULL,
  customer_phone VARCHAR(50),
  check_in_date DATE,
  check_out_date DATE,
  total_amount DECIMAL(10,2) NOT NULL,
  deposit_amount DECIMAL(10,2) DEFAULT 0,
  balance_amount DECIMAL(10,2) DEFAULT 0,
  extras TEXT,
  special_requests TEXT,
  attachment VARCHAR(500),
  status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  payment_status ENUM('pending','partial','paid','refunded') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_booking_reference (booking_reference),
  INDEX idx_customer_id (customer_id),
  INDEX idx_status (status),
  INDEX idx_payment_status (payment_status),
  INDEX idx_check_in_date (check_in_date),
  INDEX idx_created_at (created_at)
);

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

-- =====================================================
-- PAYMENT SYSTEM TABLES
-- =====================================================

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

-- =====================================================
-- COMMUNICATION TABLES
-- =====================================================

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

-- =====================================================
-- SECURITY AND LOGGING TABLES
-- =====================================================

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

-- =====================================================
-- SYSTEM CONFIGURATION TABLES
-- =====================================================

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
-- SEED DATA
-- =====================================================

-- Insert default admin user (password: Admin@123)
INSERT INTO users (email, password_hash, name, role, status, phone) VALUES
('admin@mazvikadei.local', '$2y$10$UQAJPP.uRQ8a1aORK744Rux9oEdwUZKQqL6z4QcyWDDJGfSfbxK4K', 'System Administrator', 'admin', 'active', '+263 77 123 4567')
ON DUPLICATE KEY UPDATE email=email;

-- Insert sample rooms
INSERT INTO rooms (id, title, description, price, amenities, image, room_type, max_occupancy, bed_type, featured) VALUES
(1, 'Luxury Ocean Suite', 'Stunning ocean views with private balcony and premium amenities', 350.00, 'King bed, Private balcony, Ocean view, Free breakfast, Mini bar, Air conditioning, WiFi, Room service', 'assets/rooms/ocean.jpg', 'suite', 2, 'king', 1),
(2, 'Garden View Room', 'Comfortable room overlooking our beautiful gardens', 160.00, 'Queen bed, Garden view, Mini bar, Air conditioning, WiFi, Coffee maker', 'assets/rooms/garden.jpg', 'standard', 2, 'queen', 0),
(3, 'Family Villa', 'Spacious villa perfect for families with private pool', 480.00, '2 Bedrooms, Private pool, Kitchen, Living area, Garden, BBQ area, WiFi', 'assets/rooms/villa.jpg', 'villa', 6, 'king', 1),
(4, 'Deluxe Mountain View', 'Premium room with breathtaking mountain views', 280.00, 'King bed, Mountain view, Private balcony, Air conditioning, WiFi, Room service', 'assets/rooms/mountain.jpg', 'deluxe', 2, 'king', 0),
(5, 'Standard Room', 'Comfortable and affordable accommodation', 120.00, 'Double bed, Air conditioning, WiFi, Coffee maker', 'assets/rooms/standard.jpg', 'standard', 2, 'double', 0)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Insert sample activities
INSERT INTO activities (id, title, description, short_description, duration, price, schedule, category, difficulty_level, max_participants, featured) VALUES
(1, 'Boat Cruising', 'Relaxing boat rides on the beautiful Mazvikadei Dam with stunning views', 'Scenic boat rides on the dam', '2 hours', 50.00, 'Daily 09:00, 15:00', 'relaxation', 'easy', 12, 1),
(2, 'Fishing Excursions', 'Guided fishing trips with professional equipment and local expertise', 'Professional fishing with equipment', '4 hours', 40.00, 'Mon, Wed, Fri 07:00', 'nature', 'medium', 8, 0),
(3, 'Nature Walks', 'Guided nature walks through pristine wilderness areas', 'Explore local flora and fauna', '3 hours', 20.00, 'Daily 07:30', 'nature', 'easy', 15, 0),
(4, 'Swimming & Water Sports', 'Resort pool and beach access with water sports equipment', 'Pool and beach access', 'Flexible', 0.00, 'All day', 'sports', 'easy', 50, 0),
(5, 'Picnics & Braais', 'Picnic and braai packages for groups with equipment', 'Group picnic and BBQ packages', '3 hours', 80.00, 'By booking', 'cultural', 'easy', 20, 1),
(6, 'Team Building Activities', 'Corporate team building activities and challenges', 'Professional team building programs', 'Half-day', 120.00, 'By arrangement', 'adventure', 'medium', 30, 0),
(7, 'Camping Adventures', 'Overnight camping experiences in the wilderness', 'Wilderness camping experience', 'Overnight', 30.00, 'Weekends', 'adventure', 'medium', 15, 0),
(8, 'Bird Watching Tours', 'Guided bird watching with expert naturalists', 'Expert-led bird watching', '2 hours', 25.00, 'Daily 06:00', 'nature', 'easy', 10, 0)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Insert sample events
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
-- INDEXES FOR PERFORMANCE
-- =====================================================

-- Additional indexes for better performance
CREATE INDEX idx_bookings_customer_email ON bookings(customer_email);
CREATE INDEX idx_bookings_created_at_desc ON bookings(created_at DESC);
CREATE INDEX idx_contact_messages_created_at_desc ON contact_messages(created_at DESC);
CREATE INDEX idx_admin_logs_created_at_desc ON admin_logs(created_at DESC);

-- =====================================================
-- VIEWS FOR REPORTING
-- =====================================================

-- Monthly booking summary view
CREATE OR REPLACE VIEW monthly_booking_summary AS
SELECT 
    YEAR(created_at) as year,
    MONTH(created_at) as month,
    COUNT(*) as total_bookings,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as average_booking_value,
    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings
FROM bookings
GROUP BY YEAR(created_at), MONTH(created_at)
ORDER BY year DESC, month DESC;

-- Room occupancy summary view
CREATE OR REPLACE VIEW room_occupancy_summary AS
SELECT 
    r.id,
    r.title,
    r.room_type,
    COUNT(b.id) as total_bookings,
    SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
    SUM(b.total_amount) as total_revenue
FROM rooms r
LEFT JOIN bookings b ON JSON_CONTAINS(b.items_json, JSON_OBJECT('room_id', r.id))
GROUP BY r.id, r.title, r.room_type
ORDER BY total_revenue DESC;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

-- Procedure to generate booking reference
DELIMITER //
CREATE PROCEDURE GenerateBookingReference(OUT booking_ref VARCHAR(20))
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE new_ref VARCHAR(20);
    DECLARE ref_exists INT DEFAULT 1;
    
    WHILE ref_exists > 0 DO
        SET new_ref = CONCAT('MZ', DATE_FORMAT(NOW(), '%y%m%d'), LPAD(FLOOR(RAND() * 10000), 4, '0'));
        SELECT COUNT(*) INTO ref_exists FROM bookings WHERE booking_reference = new_ref;
    END WHILE;
    
    SET booking_ref = new_ref;
END //
DELIMITER ;

-- Procedure to clean up expired password reset tokens
DELIMITER //
CREATE PROCEDURE CleanupExpiredTokens()
BEGIN
    DELETE FROM password_resets WHERE expires_at < NOW();
    DELETE FROM user_sessions WHERE expires_at < NOW();
END //
DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Trigger to update booking reference if not provided
DELIMITER //
CREATE TRIGGER tr_booking_reference
BEFORE INSERT ON bookings
FOR EACH ROW
BEGIN
    IF NEW.booking_reference IS NULL OR NEW.booking_reference = '' THEN
        CALL GenerateBookingReference(@new_ref);
        SET NEW.booking_reference = @new_ref;
    END IF;
END //
DELIMITER ;

-- Trigger to update room availability when booking is confirmed
DELIMITER //
CREATE TRIGGER tr_update_room_availability
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF NEW.status = 'confirmed' AND OLD.status != 'confirmed' THEN
        -- Mark rooms as unavailable for the booking period
        INSERT INTO room_availability (room_id, date, available)
        SELECT 
            JSON_UNQUOTE(JSON_EXTRACT(item.value, '$.room_id')) as room_id,
            DATE_ADD(NEW.check_in_date, INTERVAL seq.seq DAY) as date,
            0 as available
        FROM JSON_TABLE(NEW.items_json, '$[*]' COLUMNS (value JSON PATH '$')) as item
        CROSS JOIN (
            SELECT 0 as seq UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION
            SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
            SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
        ) as seq
        WHERE JSON_UNQUOTE(JSON_EXTRACT(item.value, '$.room_id')) IS NOT NULL
        AND DATE_ADD(NEW.check_in_date, INTERVAL seq.seq DAY) < NEW.check_out_date
        ON DUPLICATE KEY UPDATE available = 0;
    END IF;
END //
DELIMITER ;

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'Mazvikadei Resort Database Setup Complete!' as message,
       'Admin Login: admin@mazvikadei.local / Admin@123' as admin_info,
       'Registration Key: MAZVIKADEI_ADMIN_2024' as registration_info;
