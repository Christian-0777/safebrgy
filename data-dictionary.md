# Safebrgy Data Dictionary

This data dictionary is based on `sql/safebrgy_schema.sql`.

## `users`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the user |
| `role` | `enum('resident','admin')` | Access role of the user |
| `username` | `varchar(100)` | Username of the user |
| `email` | `varchar(255)` | Email address of the user |
| `phone` | `varchar(20)` | Phone number of the user |
| `password_hash` | `varchar(255)` | Encrypted password of the user |
| `profile_image` | `varchar(255)` | Profile image of the user |
| `cover_photo` | `varchar(255)` | Cover photo of the user |
| `two_factor_enabled` | `tinyint(1)` | Two-factor authentication status |
| `is_verified` | `tinyint(1)` | Account verification status |
| `created_at` | `timestamp` | Account creation date and time |
| `updated_at` | `timestamp` | Account update date and time |

## `remember_tokens`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the token |
| `user_id` | `int(11)` | Identifier of the user |
| `selector` | `char(32)` | Remember-me token selector |
| `token_hash` | `char(64)` | Hashed remember-me token |
| `expires_at` | `datetime` | Token expiration date and time |
| `created_at` | `timestamp` | Token creation date and time |

## `barangay_settings`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `tinyint unsigned` | Settings record identifier |
| `name` | `varchar(150)` | Name of the barangay |
| `address` | `varchar(255)` | Address of the barangay |
| `contact_number` | `varchar(30)` | Official contact number |
| `official_email` | `varchar(255)` | Official email address |
| `website_url` | `varchar(255)` | Official website address |
| `logo_path` | `varchar(255)` | Barangay logo path |
| `description` | `text` | Description of the barangay |
| `updated_by` | `int(11)` | Identifier of the updating user |
| `updated_at` | `timestamp` | Settings update date and time |

## `requests`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(10) unsigned` | Unique identifier of the request |
| `user_id` | `int(11)` | Identifier of the requesting user |
| `reference_no` | `varchar(30)` | Request reference number |
| `document_type` | `enum('Barangay Clearance','Barangay Residency','Barangay Indigency','Barangay Business Clearance')` | Type of requested document |
| `resident_name` | `varchar(150)` | Full name of the user |
| `resident_email` | `varchar(150)` | Email address of the user |
| `supporting_file` | `varchar(255)` | Supporting document path |
| `status` | `enum('Pending','Approved','Rejected','Ready for Pickup','Processing','Received')` | Request status |
| `submitted_at` | `datetime` | Request submission date and time |
| `updated_at` | `datetime` | Request update date and time |
| `date_received` | `datetime` | Document receipt date and time |

## `officials`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the official |
| `name` | `varchar(150)` | Full name of the official |
| `position` | `varchar(150)` | Position of the official |
| `photo` | `varchar(255)` | Official photo path |
| `created_at` | `timestamp` | Official record creation date and time |

## `email_logs`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the email log |
| `recipient` | `varchar(255)` | Email address of the recipient |
| `subject` | `varchar(255)` | Subject of the email |
| `status` | `enum('sent','failed')` | Email delivery status |
| `error_message` | `text` | Email delivery error details |
| `sent_at` | `timestamp` | Email sending date and time |

## `admin_logs`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the admin log |
| `admin_id` | `int(11)` | Identifier of the admin user |
| `action` | `varchar(255)` | Administrative action description |
| `meta` | `longtext` | Administrative action metadata |
| `created_at` | `timestamp` | Log creation date and time |

## `announcements`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the announcement |
| `title` | `varchar(255)` | Title of the announcement |
| `body` | `text` | Content of the announcement |
| `author_id` | `int(11)` | Identifier of the author |
| `published_at` | `datetime` | Announcement publication date and time |
| `scheduled_at` | `datetime` | Scheduled publication date and time |
| `priority` | `enum('normal','important','urgent')` | Announcement priority |
| `status` | `enum('draft','active','scheduled','expired')` | Announcement status |
| `attachments` | `longtext` | Announcement attachment data |
| `target_audience` | `longtext` | Announcement target audience data |
| `pinned` | `tinyint(1)` | Announcement pin status |
| `archived` | `tinyint(1)` | Announcement archive status |
| `created_at` | `timestamp` | Announcement creation date and time |
| `updated_at` | `timestamp` | Announcement update date and time |

## `announcement_reads`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the read record |
| `announcement_id` | `int(11)` | Identifier of the announcement |
| `user_id` | `int(11)` | Identifier of the user |
| `read_at` | `timestamp` | Announcement read date and time |

## `reports`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the report |
| `case_number` | `varchar(30)` | Report case number |
| `user_id` | `int(11)` | Identifier of the reporting user |
| `report_type` | `enum('Incident','Lost Property','Blotter')` | Type of report |
| `title` | `varchar(255)` | Title of the report |
| `description` | `text` | Details of the report |
| `location` | `varchar(255)` | Report location |
| `attachments` | `longtext` | Report attachment data |
| `status` | `enum('Pending','Ongoing','Resolved','Dismissed')` | Report status |
| `created_at` | `timestamp` | Report creation date and time |
| `updated_at` | `timestamp` | Report update date and time |

## `guest_reports`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(10) unsigned` | Unique identifier of the guest report |
| `case_number` | `varchar(30)` | Guest report case number |
| `report_type` | `enum('Incident','Lost Property','Blotter')` | Type of guest report |
| `title` | `varchar(255)` | Title of the guest report |
| `description` | `text` | Details of the guest report |
| `location` | `varchar(255)` | Guest report location |
| `attachments` | `longtext` | Guest report attachment data |
| `guest_aka` | `varchar(150)` | Name or alias of the guest |
| `contact_method` | `enum('email','mobile')` | Preferred guest contact method |
| `contact_email` | `varchar(255)` | Email address of the guest |
| `contact_mobile` | `varchar(20)` | Mobile number of the guest |
| `status` | `enum('Pending','Ongoing','Resolved','Dismissed')` | Guest report status |
| `created_at` | `timestamp` | Guest report creation date and time |
| `updated_at` | `timestamp` | Guest report update date and time |
| `expires_at` | `datetime` | Guest report expiration date and time |

## `residents`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the resident |
| `resident_id` | `varchar(7)` | Resident identification number |
| `user_id` | `int(11)` | Identifier of the user |
| `first_name` | `varchar(100)` | First name of the resident |
| `middle_name` | `varchar(100)` | Middle name of the resident |
| `last_name` | `varchar(100)` | Last name of the resident |
| `birthdate` | `date` | Birth date of the resident |
| `age` | `int(11)` | Age of the resident |
| `place_of_birth` | `varchar(255)` | Birthplace of the resident |
| `gender` | `varchar(30)` | Gender of the resident |
| `civil_status` | `varchar(50)` | Civil status of the resident |
| `nationality` | `varchar(100)` | Nationality of the resident |
| `religion` | `varchar(100)` | Religion of the resident |
| `complete_address` | `text` | Complete address of the resident |
| `purok` | `varchar(100)` | Purok of the resident |
| `years_of_residency` | `int(11)` | Years of residency in the barangay |
| `mobile_number` | `varchar(20)` | Mobile number of the resident |
| `voter_status` | `varchar(50)` | Voter status of the resident |
| `employment_status` | `varchar(100)` | Employment status of the resident |
| `occupation` | `varchar(150)` | Occupation of the resident |
| `household_head` | `varchar(150)` | Household head name |
| `emergency_contact_name` | `varchar(150)` | Emergency contact name |
| `emergency_contact_number` | `varchar(20)` | Emergency contact number |
| `number_of_family_member` | `int(11)` | Number of family members |
| `educational_attainment` | `varchar(100)` | Educational attainment of the resident |
| `blood_type` | `varchar(10)` | Blood type of the resident |
| `disabilities` | `text` | Disability information of the resident |
| `valid_id_path` | `varchar(255)` | Front valid ID path |
| `valid_id_back_path` | `varchar(255)` | Back valid ID path |
| `profile_image_path` | `varchar(255)` | Resident profile image path |
| `cover_photo_path` | `varchar(255)` | Resident cover photo path |
| `created_at` | `timestamp` | Resident record creation date and time |
| `updated_at` | `timestamp` | Resident record update date and time |

## `registration_otps`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the registration OTP |
| `email` | `varchar(255)` | Email address of the registering user |
| `otp_hash` | `varchar(255)` | Hashed registration OTP |
| `expires_at` | `datetime` | OTP expiration date and time |
| `consumed_at` | `datetime` | OTP consumption date and time |
| `created_at` | `timestamp` | OTP creation date and time |

## `password_reset_otps`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the password reset OTP |
| `user_id` | `int(11)` | Identifier of the user |
| `email` | `varchar(255)` | Email address of the user |
| `otp_hash` | `varchar(255)` | Hashed password reset OTP |
| `expires_at` | `datetime` | OTP expiration date and time |
| `consumed_at` | `datetime` | OTP consumption date and time |
| `created_at` | `timestamp` | OTP creation date and time |

## `sms_logs`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(11)` | Unique identifier of the SMS log |
| `user_id` | `int(11)` | Identifier of the user |
| `email` | `varchar(255)` | Email address of the recipient |
| `mobile_number` | `varchar(20)` | Mobile number of the recipient |
| `event_type` | `varchar(100)` | Notification event type |
| `event_meta` | `longtext` | Notification event metadata |
| `email_sent` | `tinyint(1)` | Email delivery status |
| `sms_sent` | `tinyint(1)` | SMS delivery status |
| `status` | `varchar(50)` | Notification processing status |
| `created_at` | `timestamp` | Log creation date and time |

## `barangay_business_clearance`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(10) unsigned` | Unique identifier of the business clearance |
| `request_id` | `int(10) unsigned` | Identifier of the document request |
| `business_name` | `varchar(150)` | Name of the business |
| `business_description` | `text` | Description of the business |
| `business_logo` | `varchar(255)` | Business logo path |
| `business_address` | `varchar(255)` | Address of the business |
| `contact_number` | `varchar(20)` | Business contact number |
| `tin_number` | `varchar(30)` | Taxpayer identification number |
| `business_started` | `date` | Business start date |
| `purpose` | `text` | Purpose of the clearance request |

## `barangay_clearance`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(10) unsigned` | Unique identifier of the clearance |
| `request_id` | `int(10) unsigned` | Identifier of the document request |
| `purpose` | `text` | Purpose of the clearance request |

## `barangay_indigency`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(10) unsigned` | Unique identifier of the indigency record |
| `request_id` | `int(10) unsigned` | Identifier of the document request |
| `monthly_income` | `decimal(10,2)` | Monthly income of the household |
| `household_members` | `int(10) unsigned` | Number of household members |
| `purpose` | `enum('Medical Assistance','Educational Assistance','Financial Assistance','Burial Assistance','Other')` | Purpose of the indigency certificate |
| `purpose_other` | `varchar(255)` | Other indigency certificate purpose |

## `barangay_residency`
| Field Name | Data Type | Short Description |
|---|---|---|
| `id` | `int(10) unsigned` | Unique identifier of the residency record |
| `request_id` | `int(10) unsigned` | Identifier of the document request |
| `years_of_residency` | `int(10) unsigned` | Number of years of residency |
| `date_started` | `date` | Residency start date |
| `purpose` | `text` | Purpose of the residency certification request |
