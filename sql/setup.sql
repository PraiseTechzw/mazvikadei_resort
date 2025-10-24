-- Mazvikadei Resort DB Setup (MySQL)
CREATE DATABASE IF NOT EXISTS mazvikadei CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mazvikadei;

-- Users (admin and customers)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(200) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(200),
  role ENUM('admin','customer') DEFAULT 'customer',
  status ENUM('active','inactive','suspended') DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME NULL,
  login_attempts INT DEFAULT 0,
  locked_until DATETIME NULL
);

-- Rooms
CREATE TABLE IF NOT EXISTS rooms (
  id INT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  amenities TEXT,
  image VARCHAR(500),
  available TINYINT(1) DEFAULT 1
);

-- Activities
CREATE TABLE IF NOT EXISTS activities (
  id INT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  duration VARCHAR(100),
  price DECIMAL(10,2),
  schedule TEXT
);

-- Events (venue bookings)
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200),
  description TEXT,
  price DECIMAL(10,2),
  capacity INT DEFAULT 0
);

-- Bookings (rooms/activities/events)
CREATE TABLE IF NOT EXISTS bookings (
  id BIGINT PRIMARY KEY,
  type ENUM('room','activity','event') NOT NULL,
  items_json JSON NOT NULL,
  customer_name VARCHAR(200) NOT NULL,
  customer_email VARCHAR(200) NOT NULL,
  customer_phone VARCHAR(50),
  extras TEXT,
  attachment VARCHAR(500),
  status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(200) NOT NULL,
  token VARCHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_token (token),
  INDEX idx_email (email)
);

-- Admin activity logs
CREATE TABLE IF NOT EXISTS admin_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  action VARCHAR(100) NOT NULL,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed admin user (password: Admin@123)
INSERT INTO users (email, password_hash, name, role, status)
VALUES ('admin@mazvikadei.local', '$2y$10$UQAJPP.uRQ8a1aORK744Rux9oEdwUZKQqL6z4QcyWDDJGfSfbxK4K', 'Admin', 'admin', 'active')
ON DUPLICATE KEY UPDATE email=email;

-- Seed rooms and activities
INSERT INTO rooms (id, title, price, amenities, image, available) VALUES
(1,'Luxury Ocean Suite',350.00,'King bed, Balcony, Free breakfast','assets/rooms/ocean.jpg',1),
(2,'Garden View Room',160.00,'Queen bed, Garden view, Mini bar','assets/rooms/garden.jpg',1),
(3,'Family Villa',480.00,'2 Bedrooms, Private pool','assets/rooms/villa.jpg',1)
ON DUPLICATE KEY UPDATE title=VALUES(title);

INSERT INTO activities (id, title, description, duration, price, schedule) VALUES
(1,'Boat Cruising','Relaxing boat rides on the bay','2 hours',50.00,'Daily 09:00, 15:00'),
(2,'Fishing','Guided fishing trips with equipment','4 hours',40.00,'Mon, Wed, Fri 07:00'),
(3,'Nature Walks','Guided nature walks and hiking','3 hours',20.00,'Daily 07:30'),
(4,'Swimming','Resort pool and beach access','Flexible',0.00,'All day'),
(5,'Picnics & Braais','Picnic and braai packages for groups','3 hours',80.00,'By booking'),
(6,'Team Building','Corporate team building activities','Half-day',120.00,'By arrangement'),
(7,'Camping','Overnight camping experiences','Overnight',30.00,'Weekends')
ON DUPLICATE KEY UPDATE title=VALUES(title);

