-- =========================================================
-- Barangay Resident Request System - Database Schema
-- =========================================================
CREATE DATABASE IF NOT EXISTS barangay_resident_system
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE barangay_resident_system;

-- ---------------------------------------------------------
-- MASTER TABLE: requests
-- Holds the information shown in the "My Requests" table:
-- reference number, document type, submitted on, status.
-- Every specific document table below links back here
-- through request_id (one-to-one).
-- ---------------------------------------------------------
CREATE TABLE requests (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference_no    VARCHAR(30) NOT NULL UNIQUE,
    document_type   ENUM(
                        'Barangay Clearance',
                        'Barangay Residency',
                        'Barangay Indigency',
                        'Barangay Business Clearance'
                    ) NOT NULL,
    resident_name   VARCHAR(150) NOT NULL,
    resident_email  VARCHAR(150) NOT NULL,
    supporting_file VARCHAR(255) NULL,          -- path of the uploaded supporting doc/image
    status          ENUM('Pending','Approved','Rejected','Ready for Pickup')
                        NOT NULL DEFAULT 'Pending',
    submitted_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 1. BARANGAY CLEARANCE
-- ---------------------------------------------------------
CREATE TABLE barangay_clearance (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id  INT UNSIGNED NOT NULL,
    purpose     TEXT NOT NULL,
    CONSTRAINT fk_clearance_request
        FOREIGN KEY (request_id) REFERENCES requests(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 2. BARANGAY RESIDENCY
-- ---------------------------------------------------------
CREATE TABLE barangay_residency (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id          INT UNSIGNED NOT NULL,
    years_of_residency  INT UNSIGNED NOT NULL,
    date_started        DATE NOT NULL,
    purpose             TEXT NOT NULL,
    CONSTRAINT fk_residency_request
        FOREIGN KEY (request_id) REFERENCES requests(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3. BARANGAY INDIGENCY
-- ---------------------------------------------------------
CREATE TABLE barangay_indigency (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id          INT UNSIGNED NOT NULL,
    monthly_income      DECIMAL(10,2) NOT NULL,
    household_members   INT UNSIGNED NOT NULL,
    purpose             ENUM(
                            'Medical Assistance',
                            'Educational Assistance',
                            'Financial Assistance',
                            'Burial Assistance',
                            'Other'
                        ) NOT NULL,
    purpose_other       VARCHAR(255) NULL,       -- filled only when purpose = 'Other'
    CONSTRAINT fk_indigency_request
        FOREIGN KEY (request_id) REFERENCES requests(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. BARANGAY BUSINESS CLEARANCE
-- ---------------------------------------------------------
CREATE TABLE barangay_business_clearance (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id            INT UNSIGNED NOT NULL,
    business_name         VARCHAR(150) NOT NULL,
    business_description  TEXT NOT NULL,
    business_logo         VARCHAR(255) NULL,     -- optional uploaded logo path
    business_address      VARCHAR(255) NOT NULL,
    contact_number        VARCHAR(20) NOT NULL,
    tin_number             VARCHAR(30) NULL,       -- optional but recommended
    business_started      DATE NOT NULL,
    purpose               TEXT NOT NULL,
    CONSTRAINT fk_business_request
        FOREIGN KEY (request_id) REFERENCES requests(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Helpful index for quick lookup of a resident's requests
-- ---------------------------------------------------------
CREATE INDEX idx_requests_email ON requests(resident_email);
CREATE INDEX idx_requests_status ON requests(status);
