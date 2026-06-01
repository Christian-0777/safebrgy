CREATE DATABASE IF NOT EXISTS safebrgy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE safebrgy;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('resident','admin') NOT NULL DEFAULT 'resident',
  username VARCHAR(100) UNIQUE,
  email VARCHAR(255) UNIQUE,
  phone VARCHAR(20),
  password_hash VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255),
  is_verified TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS residents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100),
  last_name VARCHAR(100) NOT NULL,
  birthdate DATE,
  age INT,
  place_of_birth VARCHAR(255),
  gender ENUM('Male','Female','Other'),
  civil_status ENUM('Single','Married','Widowed','Separated','Divorced'),
  nationality VARCHAR(100),
  religion VARCHAR(100),
  complete_address TEXT,
  purok VARCHAR(100),
  years_of_residency INT,
  mobile_number VARCHAR(20),
  voter_status ENUM('Yes','No','Not Sure'),
  employment_status VARCHAR(100),
  occupation VARCHAR(150),
  household_head VARCHAR(150),
  emergency_contact_name VARCHAR(150),
  emergency_contact_number VARCHAR(20),
  number_of_family_member INT,
  educational_attainment VARCHAR(100),
  blood_type VARCHAR(10),
  disabilities TEXT,
  valid_id_path VARCHAR(255),
  profile_image_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  request_type ENUM('Barangay Clearance','Barangay Residency','Barangay Indigency','Barangay Business Clearance','Incident Report','Lost Property') NOT NULL,
  purpose VARCHAR(255),
  location VARCHAR(255),
  attachments JSON,
  status ENUM('Pending','Approved','Rejected','Processing','Ready to Receive','Received') DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_received TIMESTAMP NULL,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  report_type ENUM('Incident','Lost Property') NOT NULL,
  title VARCHAR(255),
  description TEXT,
  location VARCHAR(255),
  attachments JSON,
  status ENUM('New','In Progress','Resolved','Closed') DEFAULT 'New',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  author_id INT,
  published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  scheduled_at DATETIME DEFAULT NULL,
  priority ENUM('normal','important','urgent') NOT NULL DEFAULT 'normal',
  status ENUM('draft','active','scheduled','expired') NOT NULL DEFAULT 'active',
  attachments JSON DEFAULT NULL,
  target_audience JSON DEFAULT NULL,
  pinned TINYINT(1) DEFAULT 0,
  archived TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS admin_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT,
  action VARCHAR(255),
  meta JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS officials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150),
  position VARCHAR(150),
  photo VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
