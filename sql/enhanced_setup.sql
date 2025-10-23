-- Enhanced Mazvikadei Resort Database Setup (MySQL)
-- This is a comprehensive database schema for a complete resort management system

CREATE DATABASE IF NOT EXISTS mazvikadei CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mazvikadei;

-- Users (admin, staff, and customers)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(200) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(200) NOT NULL,
  phone VARCHAR(50),
  role ENUM('admin','staff','customer') DEFAULT 'customer',
  status ENUM('active','inactive','suspended') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Room categories
CREATE TABLE IF NOT EXISTS room_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Rooms with enhanced structure
CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_number VARCHAR(20) UNIQUE NOT NULL,
  title VARCHAR(200) NOT NULL,
  category_id INT,
  price DECIMAL(10,2) NOT NULL,
  max_occupancy INT DEFAULT 2,
  amenities TEXT,
  description TEXT,
  image VARCHAR(500),
  status ENUM('available','occupied','maintenance','out_of_order') DEFAULT 'available',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES room_categories(id) ON DELETE SET NULL
);

-- Room availability calendar
CREATE TABLE IF NOT EXISTS room_availability (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  date DATE NOT NULL,
  status ENUM('available','booked','blocked') DEFAULT 'available',
  price_override DECIMAL(10,2) NULL,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  UNIQUE KEY unique_room_date (room_id, date)
);

-- Activity categories
CREATE TABLE IF NOT EXISTS activity_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Activities with enhanced structure
CREATE TABLE IF NOT EXISTS activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  category_id INT,
  description TEXT,
  duration VARCHAR(100),
  price DECIMAL(10,2),
  max_participants INT DEFAULT 10,
  min_participants INT DEFAULT 1,
  schedule TEXT,
  requirements TEXT,
  image VARCHAR(500),
  status ENUM('active','inactive') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES activity_categories(id) ON DELETE SET NULL
);

-- Event categories
CREATE TABLE IF NOT EXISTS event_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Events (venue bookings) with enhanced structure
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  category_id INT,
  description TEXT,
  price DECIMAL(10,2),
  capacity INT DEFAULT 0,
  venue VARCHAR(200),
  duration_hours INT DEFAULT 4,
  includes TEXT,
  requirements TEXT,
  image VARCHAR(500),
  status ENUM('active','inactive') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES event_categories(id) ON DELETE SET NULL
);

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
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  deposit_amount DECIMAL(10,2) DEFAULT 0,
  balance_amount DECIMAL(10,2) DEFAULT 0,
  payment_status ENUM('pending','partial','paid','refunded') DEFAULT 'pending',
  payment_method VARCHAR(50),
  extras TEXT,
  special_requests TEXT,
  attachment VARCHAR(500),
  status ENUM('pending','confirmed','checked_in','checked_out','cancelled','no_show') DEFAULT 'pending',
  cancellation_reason TEXT,
  cancelled_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Payments tracking
CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  transaction_id VARCHAR(100),
  status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
  payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Reviews and ratings
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT NOT NULL,
  customer_id INT,
  rating INT CHECK (rating >= 1 AND rating <= 5),
  title VARCHAR(200),
  comment TEXT,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Staff schedules
CREATE TABLE IF NOT EXISTS staff_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT NOT NULL,
  date DATE NOT NULL,
  shift_start TIME NOT NULL,
  shift_end TIME NOT NULL,
  role VARCHAR(100),
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
);

-- System settings
CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_value TEXT,
  description TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact messages
CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  email VARCHAR(200) NOT NULL,
  phone VARCHAR(50),
  subject VARCHAR(200),
  message TEXT NOT NULL,
  status ENUM('new','read','replied','closed') DEFAULT 'new',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Newsletter subscriptions
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(200) UNIQUE NOT NULL,
  name VARCHAR(200),
  status ENUM('active','unsubscribed') DEFAULT 'active',
  subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  unsubscribed_at DATETIME NULL
);

-- Insert default admin user (password: Admin@123)
INSERT INTO users (email, password_hash, name, role) VALUES 
('admin@mazvikadei.local', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8eQ1K/3r4d8Y6p/6Q8VnZ8J7q6Ww6', 'Admin', 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- Insert room categories
INSERT INTO room_categories (id, name, description) VALUES
(1, 'Standard Rooms', 'Comfortable rooms with basic amenities'),
(2, 'Deluxe Rooms', 'Enhanced rooms with premium features'),
(3, 'Suites', 'Luxury accommodations with extra space and amenities')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Insert enhanced rooms
INSERT INTO rooms (id, room_number, title, category_id, price, max_occupancy, amenities, description, image) VALUES
(1, 'R001', 'Luxury Ocean Suite', 3, 350.00, 2, 'King bed, Balcony, Free breakfast, Mini bar, Ocean view', 'Premium suite with stunning ocean views and luxury amenities', 'assets/rooms/ocean.jpg'),
(2, 'R002', 'Garden View Room', 2, 160.00, 2, 'Queen bed, Garden view, Mini bar, Air conditioning', 'Comfortable room overlooking our beautiful gardens', 'assets/rooms/garden.jpg'),
(3, 'R003', 'Family Villa', 3, 480.00, 6, '2 Bedrooms, Private pool, Kitchenette, Living area', 'Spacious villa perfect for families with private pool access', 'assets/rooms/villa.jpg'),
(4, 'R004', 'Standard Double', 1, 80.00, 2, 'Double bed, Air conditioning, Private bathroom', 'Comfortable standard room with all essential amenities', 'assets/rooms/standard.jpg'),
(5, 'R005', 'Standard Twin', 1, 80.00, 2, 'Twin beds, Air conditioning, Private bathroom', 'Standard room with twin beds for shared accommodation', 'assets/rooms/twin.jpg')
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Insert activity categories
INSERT INTO activity_categories (id, name, description) VALUES
(1, 'Water Activities', 'Activities on or near the water'),
(2, 'Land Activities', 'Activities on land and nature'),
(3, 'Adventure Sports', 'High-energy adventure activities'),
(4, 'Relaxation', 'Peaceful and relaxing activities')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Insert enhanced activities
INSERT INTO activities (id, title, category_id, description, duration, price, max_participants, min_participants, schedule, requirements, image) VALUES
(1, 'Boat Cruising', 1, 'Relaxing boat rides on the beautiful Mazvikadei Dam', '2 hours', 50.00, 8, 2, 'Daily 09:00, 15:00', 'Swimming ability recommended', 'assets/activities/boating.jpg'),
(2, 'Fishing', 1, 'Guided fishing trips with professional equipment provided', '4 hours', 40.00, 6, 1, 'Mon, Wed, Fri 07:00', 'Fishing license required', 'assets/activities/fishing.jpg'),
(3, 'Nature Walks', 2, 'Guided nature walks through scenic trails', '3 hours', 20.00, 15, 2, 'Daily 07:30', 'Comfortable walking shoes', 'assets/activities/walking.jpg'),
(4, 'Swimming', 4, 'Resort pool and beach access with lifeguard supervision', 'Flexible', 0.00, 50, 1, 'All day', 'None', 'assets/activities/swimming.jpg'),
(5, 'Picnics & Braais', 4, 'Picnic and braai packages for groups with equipment', '3 hours', 80.00, 20, 4, 'By booking', 'Advance booking required', 'assets/activities/picnic.jpg'),
(6, 'Team Building', 3, 'Corporate team building activities and challenges', 'Half-day', 120.00, 30, 8, 'By arrangement', 'Corporate booking', 'assets/activities/teambuilding.jpg'),
(7, 'Camping', 2, 'Overnight camping experiences with equipment', 'Overnight', 30.00, 12, 2, 'Weekends', 'Camping gear provided', 'assets/activities/camping.jpg'),
(8, 'Bird Watching', 2, 'Guided bird watching tours with expert guide', '2 hours', 25.00, 10, 2, 'Daily 06:00, 17:00', 'Binoculars provided', 'assets/activities/birdwatching.jpg')
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Insert event categories
INSERT INTO event_categories (id, name, description) VALUES
(1, 'Weddings', 'Wedding ceremonies and receptions'),
(2, 'Corporate Events', 'Business meetings and conferences'),
(3, 'Private Parties', 'Birthday parties and celebrations'),
(4, 'Retreats', 'Wellness and spiritual retreats')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Insert enhanced events
INSERT INTO events (id, title, category_id, description, price, capacity, venue, duration_hours, includes, requirements, image) VALUES
(1, 'Wedding Ceremony', 1, 'Beautiful outdoor wedding ceremony by the lake', 500.00, 100, 'Lakeside Pavilion', 4, 'Setup, chairs, sound system, decorations', 'Advance booking required', 'assets/events/wedding.jpg'),
(2, 'Corporate Conference', 2, 'Professional conference facilities with modern amenities', 300.00, 50, 'Conference Hall', 8, 'Projector, sound system, catering, refreshments', 'Corporate booking', 'assets/events/conference.jpg'),
(3, 'Birthday Party', 3, 'Fun birthday celebration with activities and catering', 200.00, 30, 'Party Pavilion', 4, 'Decorations, activities, basic catering', 'Advance booking required', 'assets/events/birthday.jpg'),
(4, 'Wellness Retreat', 4, 'Peaceful wellness retreat with yoga and meditation', 150.00, 20, 'Meditation Garden', 6, 'Yoga mats, meditation cushions, healthy meals', 'Comfortable clothing', 'assets/events/retreat.jpg')
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Insert system settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'Mazvikadei Resort', 'Name of the resort'),
('contact_email', 'info@mazvikadei.com', 'Main contact email'),
('contact_phone', '+263 77 123 4567', 'Main contact phone'),
('check_in_time', '14:00', 'Standard check-in time'),
('check_out_time', '10:00', 'Standard check-out time'),
('cancellation_policy', 'Free cancellation up to 48 hours before check-in', 'Cancellation policy'),
('payment_methods', 'EcoCash,Paynow,PayPal,Bank Transfer', 'Accepted payment methods'),
('currency', 'USD', 'Default currency'),
('timezone', 'Africa/Harare', 'Resort timezone')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Create indexes for better performance
CREATE INDEX idx_bookings_customer ON bookings(customer_email);
CREATE INDEX idx_bookings_dates ON bookings(check_in_date, check_out_date);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_room_availability_date ON room_availability(date);
CREATE INDEX idx_room_availability_room ON room_availability(room_id);
CREATE INDEX idx_payments_booking ON payments(booking_id);
CREATE INDEX idx_reviews_booking ON reviews(booking_id);
CREATE INDEX idx_contact_status ON contact_messages(status);
