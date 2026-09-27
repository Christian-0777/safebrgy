SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Parent Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('resident','admin') NOT NULL DEFAULT 'resident',
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `cover_photo` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `selector` char(32) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `idx_remember_user` (`user_id`),
  KEY `idx_remember_expiry` (`expires_at`),
  CONSTRAINT `remember_tokens_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Singleton configuration for the barangay identity shown across the portal.
CREATE TABLE `barangay_settings` (
  `id` tinyint unsigned NOT NULL DEFAULT 1,
  `name` varchar(150) NOT NULL DEFAULT 'Barangay San Jose',
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `official_email` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `barangay_settings_updated_by_fk` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO `barangay_settings` (`id`) VALUES (1);

-- --------------------------------------------------------
-- Parent Table: requests
-- --------------------------------------------------------
CREATE TABLE `requests` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `reference_no` varchar(30) NOT NULL,
  `document_type` enum('Barangay Clearance','Barangay Residency','Barangay Indigency','Barangay Business Clearance') NOT NULL,
  `resident_name` varchar(150) NOT NULL,
  `resident_email` varchar(150) NOT NULL,
  `supporting_file` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Ready for Pickup','Processing','Received') NOT NULL DEFAULT 'Pending',
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_received` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  UNIQUE KEY `reference_no` (`reference_no`),
  KEY `idx_requests_email` (`resident_email`),
  KEY `idx_requests_status` (`status`),
  CONSTRAINT `requests_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Standalone Table: officials
-- --------------------------------------------------------
CREATE TABLE `officials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `position` varchar(150) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Standalone Table: email_logs
-- --------------------------------------------------------
CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('sent','failed') NOT NULL DEFAULT 'failed',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_recipient` (`recipient`),
  KEY `idx_email_status` (`status`),
  KEY `idx_email_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Child Tables depending on users
-- --------------------------------------------------------
CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `published_at` datetime DEFAULT current_timestamp(),
  `scheduled_at` datetime DEFAULT NULL,
  `priority` enum('normal','important','urgent') NOT NULL DEFAULT 'normal',
  `status` enum('draft','active','scheduled','expired') NOT NULL DEFAULT 'active',
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `target_audience` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_audience`)),
  `pinned` tinyint(1) DEFAULT 0,
  `archived` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `announcement_reads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `announcement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `announcement_user` (`announcement_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `announcement_reads_announcement_fk` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcement_reads_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `target` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `entity_type` varchar(32) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_read` (`user_id`,`is_read`,`id`),
  KEY `idx_notifications_target` (`user_id`,`target`,`is_read`),
  CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_number` varchar(30) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `report_type` enum('Incident','Lost Property','Blotter', 'Public Concerns') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `status` enum('Pending','In Progress','Resolved','Dismissed') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_reports_expires_at` (`expires_at`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `guest_reports` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_number` varchar(30) NOT NULL,
  `report_type` enum('Incident','Lost Property','Public Concerns','Blotter') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `guest_aka` varchar(150) NOT NULL,
  `contact_method` enum('email','mobile') NOT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_mobile` varchar(20) DEFAULT NULL,
  `status` enum('Pending','In Progress','Resolved','Dismissed') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `case_number` (`case_number`),
  KEY `idx_guest_reports_status` (`status`),
  KEY `idx_guest_reports_expires` (`expires_at`),
  KEY `idx_guest_reports_type` (`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `report_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` enum('Incident','Lost Property','Public Concerns','Blotter') NOT NULL,
  `tag_name` varchar(60) NOT NULL,
  `is_predefined` tinyint(1) NOT NULL DEFAULT 0,
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_report_tags_type_name` (`report_type`,`tag_name`),
  KEY `idx_report_tags_type` (`report_type`),
  CONSTRAINT `report_tags_creator_fk` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `report_tag_assignments` (
  `report_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`report_id`,`tag_id`),
  KEY `idx_report_tag_assignments_tag` (`tag_id`),
  CONSTRAINT `report_tag_assignments_report_fk` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_tag_assignments_tag_fk` FOREIGN KEY (`tag_id`) REFERENCES `report_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `report_tags` (`report_type`, `tag_name`, `is_predefined`) VALUES
('Incident', 'FIRE', 1), ('Incident', 'THEFT', 1), ('Incident', 'ACCIDENT', 1), ('Incident', 'FIGHT', 1),
('Incident', 'VANDALISM', 1), ('Incident', 'ASSAULT', 1), ('Incident', 'ROAD ACCIDENT', 1), ('Incident', 'VEHICLE INCIDENT', 1),
('Incident', 'MEDICAL EMERGENCY', 1), ('Incident', 'DOMESTIC DISPUTE', 1), ('Incident', 'MISSING PERSON', 1), ('Incident', 'DISTURBANCE', 1),
('Incident', 'ILLEGAL ACTIVITY', 1), ('Incident', 'DAMAGE TO PROPERTY', 1),
('Public Concerns', 'ROAD', 1), ('Public Concerns', 'STREETLIGHT', 1), ('Public Concerns', 'GARBAGE', 1), ('Public Concerns', 'DRAINAGE', 1),
('Public Concerns', 'WATER', 1), ('Public Concerns', 'ELECTRICITY', 1), ('Public Concerns', 'NOISE', 1), ('Public Concerns', 'ANIMAL', 1),
('Public Concerns', 'SANITATION', 1), ('Public Concerns', 'INFRASTRUCTURE', 1),
('Lost Property', 'ID CARD', 1), ('Lost Property', 'WALLET', 1), ('Lost Property', 'PHONE', 1), ('Lost Property', 'KEYS', 1),
('Lost Property', 'BAG', 1), ('Lost Property', 'DOCUMENT', 1), ('Lost Property', 'JEWELRY', 1), ('Lost Property', 'ELECTRONICS', 1), ('Lost Property', 'VEHICLE', 1),
('Blotter', 'THEFT', 1), ('Blotter', 'ASSAULT', 1), ('Blotter', 'THREAT', 1), ('Blotter', 'HARASSMENT', 1),
('Blotter', 'TRESPASSING', 1), ('Blotter', 'PROPERTY DAMAGE', 1), ('Blotter', 'FAMILY DISPUTE', 1), ('Blotter', 'NEIGHBOR DISPUTE', 1);

CREATE TABLE `residents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` varchar(7) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `complete_address` text DEFAULT NULL,
  `purok` varchar(100) DEFAULT NULL,
  `years_of_residency` int(11) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `voter_status` varchar(50) DEFAULT NULL,
  `employment_status` varchar(100) DEFAULT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `household_head` varchar(150) DEFAULT NULL,
  `emergency_contact_name` varchar(150) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `number_of_family_member` int(11) DEFAULT NULL,
  `educational_attainment` varchar(100) DEFAULT NULL,
  `blood_type` varchar(10) DEFAULT NULL,
  `disabilities` text DEFAULT NULL,
  `valid_id_path` varchar(255) DEFAULT NULL,
  `valid_id_back_path` varchar(255) DEFAULT NULL,
  `profile_image_path` varchar(255) DEFAULT NULL,
  `cover_photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `resident_id` (`resident_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `residents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registration verification codes are intentionally separate from any
-- password-reset verification flow.
CREATE TABLE `registration_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `consumed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_registration_otp_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resident password reset codes are separate from registration verification codes.
CREATE TABLE `password_reset_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `consumed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_password_reset_user` (`user_id`),
  CONSTRAINT `password_reset_otps_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`event_meta`)),
  `email_sent` tinyint(1) NOT NULL DEFAULT 0,
  `sms_sent` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sms_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Child Tables depending on requests
-- --------------------------------------------------------
CREATE TABLE `barangay_business_clearance` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` int(10) UNSIGNED NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `business_description` text NOT NULL,
  `business_logo` varchar(255) DEFAULT NULL,
  `business_address` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `tin_number` varchar(30) DEFAULT NULL,
  `business_started` date NOT NULL,
  `purpose` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_business_request` (`request_id`),
  CONSTRAINT `fk_business_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `barangay_clearance` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` int(10) UNSIGNED NOT NULL,
  `purpose` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_clearance_request` (`request_id`),
  CONSTRAINT `fk_clearance_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `barangay_indigency` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` int(10) UNSIGNED NOT NULL,
  `monthly_income` decimal(10,2) NOT NULL,
  `household_members` int(10) UNSIGNED NOT NULL,
  `purpose` enum('Medical Assistance','Educational Assistance','Financial Assistance','Burial Assistance','Other') NOT NULL,
  `purpose_other` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_indigency_request` (`request_id`),
  CONSTRAINT `fk_indigency_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `barangay_residency` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` int(10) UNSIGNED NOT NULL,
  `years_of_residency` int(10) UNSIGNED NOT NULL,
  `date_started` date NOT NULL,
  `purpose` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_residency_request` (`request_id`),
  CONSTRAINT `fk_residency_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
