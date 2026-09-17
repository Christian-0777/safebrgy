-- Add Public Concerns as a supported report type for registered and guest reports.
ALTER TABLE `reports`
	MODIFY COLUMN `report_type` ENUM('Incident', 'Lost Property', 'Public Concerns', 'Blotter') NOT NULL;

ALTER TABLE `guest_reports`
	MODIFY COLUMN `report_type` ENUM('Incident', 'Lost Property', 'Public Concerns', 'Blotter') NOT NULL;
