-- Add Public Concerns as a supported report type for registered and guest reports.
ALTER TABLE `reports`
	MODIFY COLUMN `report_type` ENUM('Incident', 'Lost Property', 'Public Concerns', 'Blotter') NOT NULL;

ALTER TABLE `guest_reports`
	MODIFY COLUMN `report_type` ENUM('Incident', 'Lost Property', 'Public Concerns', 'Blotter') NOT NULL;

ALTER TABLE `reports`
	ADD COLUMN `expires_at` DATETIME NULL AFTER `updated_at`,
	MODIFY COLUMN `status` ENUM('Pending', 'Ongoing', 'In Progress', 'Resolved', 'Dismissed') NOT NULL DEFAULT 'Pending';

ALTER TABLE `guest_reports`
	MODIFY COLUMN `status` ENUM('Pending', 'Ongoing', 'In Progress', 'Resolved', 'Dismissed') NOT NULL DEFAULT 'Pending';

UPDATE `reports` SET `expires_at` = DATE_ADD(`created_at`, INTERVAL 15 DAY) WHERE `expires_at` IS NULL;
UPDATE `guest_reports` SET `expires_at` = DATE_ADD(`created_at`, INTERVAL 15 DAY);
UPDATE `reports` SET `expires_at` = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 DAY) WHERE `status` = 'Ongoing';
UPDATE `guest_reports` SET `expires_at` = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 DAY) WHERE `status` = 'Ongoing';
UPDATE `reports` SET `status` = 'In Progress' WHERE `status` = 'Ongoing';
UPDATE `guest_reports` SET `status` = 'In Progress' WHERE `status` = 'Ongoing';

ALTER TABLE `reports`
	MODIFY COLUMN `status` ENUM('Pending', 'In Progress', 'Resolved', 'Dismissed') NOT NULL DEFAULT 'Pending',
	ADD KEY `idx_reports_expires_at` (`expires_at`);

ALTER TABLE `guest_reports`
	MODIFY COLUMN `status` ENUM('Pending', 'In Progress', 'Resolved', 'Dismissed') NOT NULL DEFAULT 'Pending';

CREATE TABLE `report_tags` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`report_type` ENUM('Incident', 'Lost Property', 'Public Concerns', 'Blotter') NOT NULL,
	`tag_name` VARCHAR(60) NOT NULL,
	`is_predefined` TINYINT(1) NOT NULL DEFAULT 0,
	`created_by_user_id` INT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uq_report_tags_type_name` (`report_type`, `tag_name`),
	KEY `idx_report_tags_type` (`report_type`),
	CONSTRAINT `report_tags_creator_fk` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `report_tag_assignments` (
	`report_id` INT NOT NULL,
	`tag_id` INT NOT NULL,
	PRIMARY KEY (`report_id`, `tag_id`),
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
