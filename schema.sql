-- Database schema for room booking app
CREATE DATABASE IF NOT EXISTS room_booking DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE room_booking;

-- roles
CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
);

-- users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id INT NOT NULL DEFAULT 2,
  created_at DATETIME,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- rooms
CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  location VARCHAR(150) DEFAULT NULL,
  capacity INT DEFAULT 0,
  created_at DATETIME
);

-- bookings
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- indexes
CREATE INDEX idx_bookings_room ON bookings(room_id);

-- seed roles
INSERT IGNORE INTO roles (id,name) VALUES (1,'admin'),(2,'user');

-- timeslots: predefined time ranges for bookings (optional)
CREATE TABLE IF NOT EXISTS timeslots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  created_at DATETIME
);

-- sample timeslots
INSERT IGNORE INTO timeslots (id,name,start_time,end_time,created_at) VALUES
(1,'Morning','08:00:00','10:00:00',NOW()),
(2,'Late Morning','10:00:00','12:00:00',NOW()),
(3,'Afternoon','13:00:00','15:00:00',NOW());

-- password_resets for reset token workflow
CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  token_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT NOW(),
  INDEX (email),
  INDEX (token_hash)
);

-- persistent logins for "remember me" (selector/token pattern)
CREATE TABLE IF NOT EXISTS persistent_logins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  selector VARCHAR(24) NOT NULL,
  token_hash VARCHAR(128) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME DEFAULT NOW(),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (selector),
  INDEX (user_id)
);

-- activity logs for auditing user actions
CREATE TABLE IF NOT EXISTS activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  action VARCHAR(100) NOT NULL,
  details TEXT,
  ip VARCHAR(45) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT NOW(),
  INDEX (user_id),
  INDEX (action)
);
