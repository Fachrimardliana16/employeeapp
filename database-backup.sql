-- HRIS Database Dump
-- Generated: 2026-04-22 12:17:03
-- Driver: mysql

-- ========================================
-- Table: activity_log
-- ========================================

CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8095 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: attendance_machine_commands
-- ========================================

CREATE TABLE `attendance_machine_commands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attendance_machine_id` bigint unsigned NOT NULL,
  `command` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','sent','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `response_payload` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_machine_commands_attendance_machine_id_foreign` (`attendance_machine_id`),
  CONSTRAINT `attendance_machine_commands_attendance_machine_id_foreign` FOREIGN KEY (`attendance_machine_id`) REFERENCES `attendance_machines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: attendance_machine_logs
-- ========================================

CREATE TABLE `attendance_machine_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attendance_machine_id` bigint unsigned NOT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '0: Check-In, 1: Check-Out, etc.',
  `raw_payload` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_machine_logs_attendance_machine_id_foreign` (`attendance_machine_id`),
  CONSTRAINT `attendance_machine_logs_attendance_machine_id_foreign` FOREIGN KEY (`attendance_machine_id`) REFERENCES `attendance_machines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44834 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: attendance_machines
-- ========================================

CREATE TABLE `attendance_machines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `master_office_location_id` bigint unsigned NOT NULL,
  `last_heard_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offline',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_machines_serial_number_unique` (`serial_number`),
  KEY `attendance_machines_master_office_location_id_foreign` (`master_office_location_id`),
  CONSTRAINT `attendance_machines_master_office_location_id_foreign` FOREIGN KEY (`master_office_location_id`) REFERENCES `master_office_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: attendance_schedules
-- ========================================

CREATE TABLE `attendance_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `day` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `check_in_start` time NOT NULL DEFAULT '06:00:00',
  `check_in_end` time NOT NULL DEFAULT '09:00:00',
  `check_out_start` time NOT NULL DEFAULT '15:00:00',
  `check_out_end` time NOT NULL DEFAULT '20:00:00',
  `late_threshold` time NOT NULL DEFAULT '07:30:59',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_schedules_day_unique` (`day`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: attendance_special_schedules
-- ========================================

CREATE TABLE `attendance_special_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `is_working` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'True = Wajib Masuk, False = Libur/Pengecualian',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emp_date_unique` (`employee_id`,`date`),
  KEY `attendance_special_schedules_users_id_foreign` (`users_id`),
  CONSTRAINT `attendance_special_schedules_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_special_schedules_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: cache
-- ========================================

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: cache_locks
-- ========================================

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_agreement
-- ========================================

CREATE TABLE `employee_agreement` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_application_archives_id` bigint unsigned DEFAULT NULL,
  `employees_id` bigint unsigned DEFAULT NULL,
  `agreement_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `place_birth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_birth` date DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` enum('single','married','divorced','widowed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agreement_id` bigint unsigned NOT NULL,
  `employee_position_id` bigint unsigned NOT NULL,
  `employment_status_id` bigint unsigned NOT NULL,
  `basic_salary_id` bigint unsigned DEFAULT NULL,
  `non_permanent_salary_id` bigint unsigned DEFAULT NULL,
  `employee_education_id` bigint unsigned DEFAULT NULL,
  `agreement_date_start` date NOT NULL,
  `agreement_date_end` date DEFAULT NULL,
  `departments_id` bigint unsigned NOT NULL,
  `sub_department_id` bigint unsigned DEFAULT NULL,
  `docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_agreement_agreement_id_foreign` (`agreement_id`),
  KEY `employee_agreement_employee_position_id_foreign` (`employee_position_id`),
  KEY `employee_agreement_employment_status_id_foreign` (`employment_status_id`),
  KEY `employee_agreement_basic_salary_id_foreign` (`basic_salary_id`),
  KEY `employee_agreement_departments_id_foreign` (`departments_id`),
  KEY `employee_agreement_sub_department_id_foreign` (`sub_department_id`),
  KEY `employee_agreement_users_id_foreign` (`users_id`),
  KEY `employee_agreement_employee_education_id_foreign` (`employee_education_id`),
  KEY `ea_non_perm_salary_fk` (`non_permanent_salary_id`),
  CONSTRAINT `ea_non_perm_salary_fk` FOREIGN KEY (`non_permanent_salary_id`) REFERENCES `master_employee_non_permanent_salaries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_agreement_agreement_id_foreign` FOREIGN KEY (`agreement_id`) REFERENCES `master_employee_agreements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_agreement_basic_salary_id_foreign` FOREIGN KEY (`basic_salary_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_agreement_departments_id_foreign` FOREIGN KEY (`departments_id`) REFERENCES `master_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_agreement_employee_education_id_foreign` FOREIGN KEY (`employee_education_id`) REFERENCES `master_employee_education` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_agreement_employee_position_id_foreign` FOREIGN KEY (`employee_position_id`) REFERENCES `master_employee_positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_agreement_employment_status_id_foreign` FOREIGN KEY (`employment_status_id`) REFERENCES `master_employee_status_employments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_agreement_sub_department_id_foreign` FOREIGN KEY (`sub_department_id`) REFERENCES `master_sub_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_agreement_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_appointments
-- ========================================

CREATE TABLE `employee_appointments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `decision_letter_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `appointment_date` date NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `old_employment_status_id` bigint unsigned DEFAULT NULL,
  `new_employment_status_id` bigint unsigned NOT NULL,
  `employee_grade_id` bigint unsigned DEFAULT NULL,
  `employee_service_grade_id` bigint unsigned DEFAULT NULL,
  `docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `users_id` bigint unsigned NOT NULL,
  `is_applied` tinyint(1) NOT NULL DEFAULT '0',
  `proposal_docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `applied_at` timestamp NULL DEFAULT NULL,
  `applied_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_appointments_employee_id_foreign` (`employee_id`),
  KEY `employee_appointments_old_employment_status_id_foreign` (`old_employment_status_id`),
  KEY `employee_appointments_new_employment_status_id_foreign` (`new_employment_status_id`),
  KEY `employee_appointments_users_id_foreign` (`users_id`),
  KEY `employee_appointments_employee_grade_id_foreign` (`employee_grade_id`),
  KEY `employee_appointments_applied_by_foreign` (`applied_by`),
  KEY `employee_appointments_employee_service_grade_id_foreign` (`employee_service_grade_id`),
  CONSTRAINT `employee_appointments_applied_by_foreign` FOREIGN KEY (`applied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_appointments_employee_grade_id_foreign` FOREIGN KEY (`employee_grade_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_appointments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_appointments_employee_service_grade_id_foreign` FOREIGN KEY (`employee_service_grade_id`) REFERENCES `master_employee_service_grade` (`id`),
  CONSTRAINT `employee_appointments_new_employment_status_id_foreign` FOREIGN KEY (`new_employment_status_id`) REFERENCES `master_employee_status_employments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_appointments_old_employment_status_id_foreign` FOREIGN KEY (`old_employment_status_id`) REFERENCES `master_employee_status_employments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_appointments_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_assignment_letters
-- ========================================

CREATE TABLE `employee_assignment_letters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `registration_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'on progress',
  `assigning_employee_id` bigint unsigned NOT NULL,
  `additional_employee_ids` json DEFAULT NULL,
  `additional_employees_detail` json DEFAULT NULL,
  `employee_position_id` bigint unsigned NOT NULL,
  `task` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `signatory_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signatory_position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signatory_employee_id` bigint unsigned DEFAULT NULL,
  `pdf_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signed_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visit_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_assignment_letters_assigning_employee_id_foreign` (`assigning_employee_id`),
  KEY `employee_assignment_letters_employee_position_id_foreign` (`employee_position_id`),
  KEY `employee_assignment_letters_users_id_foreign` (`users_id`),
  KEY `employee_assignment_letters_signatory_employee_id_foreign` (`signatory_employee_id`),
  CONSTRAINT `employee_assignment_letters_assigning_employee_id_foreign` FOREIGN KEY (`assigning_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_assignment_letters_employee_position_id_foreign` FOREIGN KEY (`employee_position_id`) REFERENCES `master_employee_positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_assignment_letters_signatory_employee_id_foreign` FOREIGN KEY (`signatory_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_assignment_letters_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_attendance_records
-- ========================================

CREATE TABLE `employee_attendance_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attendance_time` timestamp NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `location_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distance_meters` decimal(10,2) DEFAULT NULL,
  `verification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reserved` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users_id` bigint unsigned DEFAULT NULL,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office_location_id` bigint unsigned DEFAULT NULL,
  `check_latitude` decimal(10,8) DEFAULT NULL,
  `check_longitude` decimal(11,8) DEFAULT NULL,
  `distance_from_office` int DEFAULT NULL COMMENT 'Distance in meters',
  `is_within_radius` tinyint(1) NOT NULL DEFAULT '0',
  `photo_checkin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_checkout` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attendance_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_attendance_records_office_location_id_foreign` (`office_location_id`),
  KEY `employee_attendance_records_users_id_foreign` (`users_id`),
  CONSTRAINT `employee_attendance_records_office_location_id_foreign` FOREIGN KEY (`office_location_id`) REFERENCES `master_office_locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_attendance_records_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_benefits
-- ========================================

CREATE TABLE `employee_benefits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `benefit_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_benefits_employee_id_foreign` (`employee_id`),
  KEY `employee_benefits_users_id_foreign` (`users_id`),
  CONSTRAINT `employee_benefits_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_benefits_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_business_travel_letters
-- ========================================

CREATE TABLE `employee_business_travel_letters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `registration_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'on progress',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `additional_employees` text COLLATE utf8mb4_unicode_ci,
  `additional_employee_ids` json DEFAULT NULL,
  `additional_employees_detail` json DEFAULT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination_detail` text COLLATE utf8mb4_unicode_ci,
  `shs_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shs_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accommodation_reserve_cost` decimal(15,2) DEFAULT NULL,
  `purpose_of_trip` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_trip_expenses` decimal(15,2) NOT NULL DEFAULT '0.00',
  `accommodation_cost` decimal(15,2) DEFAULT NULL,
  `pocket_money_cost` decimal(15,2) DEFAULT NULL,
  `reserve_cost` decimal(15,2) DEFAULT NULL,
  `transport_cost` decimal(15,2) DEFAULT NULL,
  `meal_cost` decimal(15,2) DEFAULT NULL,
  `total_cost` decimal(15,2) DEFAULT NULL,
  `trip_duration_days` int DEFAULT NULL,
  `total_employees` int DEFAULT NULL,
  `pasal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_signatory_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `signatory_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signatory_position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signatory_employee_id` bigint unsigned DEFAULT NULL,
  `pdf_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signed_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visit_file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_business_travel_letters_employee_id_foreign` (`employee_id`),
  KEY `employee_business_travel_letters_employee_signatory_id_foreign` (`employee_signatory_id`),
  KEY `employee_business_travel_letters_users_id_foreign` (`users_id`),
  KEY `employee_business_travel_letters_signatory_employee_id_foreign` (`signatory_employee_id`),
  CONSTRAINT `employee_business_travel_letters_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_business_travel_letters_employee_signatory_id_foreign` FOREIGN KEY (`employee_signatory_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_business_travel_letters_signatory_employee_id_foreign` FOREIGN KEY (`signatory_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_business_travel_letters_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_career_movements
-- ========================================

CREATE TABLE `employee_career_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `type` enum('promotion','demotion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'promotion',
  `decision_letter_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `movement_date` date NOT NULL,
  `old_department_id` bigint unsigned DEFAULT NULL,
  `old_sub_department_id` bigint unsigned DEFAULT NULL,
  `old_position_id` bigint unsigned DEFAULT NULL,
  `new_department_id` bigint unsigned DEFAULT NULL,
  `new_sub_department_id` bigint unsigned DEFAULT NULL,
  `new_position_id` bigint unsigned DEFAULT NULL,
  `doc_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `users_id` bigint unsigned DEFAULT NULL,
  `is_applied` tinyint(1) NOT NULL DEFAULT '0',
  `proposal_docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `applied_at` timestamp NULL DEFAULT NULL,
  `applied_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_career_movements_employee_id_foreign` (`employee_id`),
  KEY `employee_career_movements_old_department_id_foreign` (`old_department_id`),
  KEY `employee_career_movements_old_sub_department_id_foreign` (`old_sub_department_id`),
  KEY `employee_career_movements_old_position_id_foreign` (`old_position_id`),
  KEY `employee_career_movements_new_department_id_foreign` (`new_department_id`),
  KEY `employee_career_movements_new_sub_department_id_foreign` (`new_sub_department_id`),
  KEY `employee_career_movements_new_position_id_foreign` (`new_position_id`),
  KEY `employee_career_movements_users_id_foreign` (`users_id`),
  KEY `employee_career_movements_applied_by_foreign` (`applied_by`),
  CONSTRAINT `employee_career_movements_applied_by_foreign` FOREIGN KEY (`applied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_career_movements_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_career_movements_new_department_id_foreign` FOREIGN KEY (`new_department_id`) REFERENCES `master_departments` (`id`),
  CONSTRAINT `employee_career_movements_new_position_id_foreign` FOREIGN KEY (`new_position_id`) REFERENCES `master_employee_positions` (`id`),
  CONSTRAINT `employee_career_movements_new_sub_department_id_foreign` FOREIGN KEY (`new_sub_department_id`) REFERENCES `master_sub_departments` (`id`),
  CONSTRAINT `employee_career_movements_old_department_id_foreign` FOREIGN KEY (`old_department_id`) REFERENCES `master_departments` (`id`),
  CONSTRAINT `employee_career_movements_old_position_id_foreign` FOREIGN KEY (`old_position_id`) REFERENCES `master_employee_positions` (`id`),
  CONSTRAINT `employee_career_movements_old_sub_department_id_foreign` FOREIGN KEY (`old_sub_department_id`) REFERENCES `master_sub_departments` (`id`),
  CONSTRAINT `employee_career_movements_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_daily_reports
-- ========================================

CREATE TABLE `employee_daily_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `daily_report_date` date NOT NULL,
  `work_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_status` enum('completed','in_progress','pending','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_daily_reports_employee_id_foreign` (`employee_id`),
  KEY `employee_daily_reports_users_id_foreign` (`users_id`),
  CONSTRAINT `employee_daily_reports_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_daily_reports_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_documents
-- ========================================

CREATE TABLE `employee_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `master_employee_archive_type_id` bigint unsigned DEFAULT NULL,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `uploaded_by` enum('employee','hr') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hr',
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_documents_employee_id_foreign` (`employee_id`),
  KEY `employee_documents_users_id_foreign` (`users_id`),
  KEY `employee_documents_master_employee_archive_type_id_foreign` (`master_employee_archive_type_id`),
  CONSTRAINT `employee_documents_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_documents_master_employee_archive_type_id_foreign` FOREIGN KEY (`master_employee_archive_type_id`) REFERENCES `master_employee_archive_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_documents_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_families
-- ========================================

CREATE TABLE `employee_families` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employees_id` bigint unsigned NOT NULL,
  `master_employee_families_id` bigint unsigned NOT NULL,
  `family_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_gender` enum('male','female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_id_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `family_place_birth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `family_date_birth` date DEFAULT NULL,
  `family_address` text COLLATE utf8mb4_unicode_ci,
  `family_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_emergency_contact` tinyint(1) NOT NULL DEFAULT '0',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_families_employees_id_foreign` (`employees_id`),
  KEY `employee_families_master_employee_families_id_foreign` (`master_employee_families_id`),
  KEY `employee_families_users_id_foreign` (`users_id`),
  CONSTRAINT `employee_families_employees_id_foreign` FOREIGN KEY (`employees_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_families_master_employee_families_id_foreign` FOREIGN KEY (`master_employee_families_id`) REFERENCES `master_employee_families` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_families_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_mutations
-- ========================================

CREATE TABLE `employee_mutations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `decision_letter_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mutation_date` date NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `old_department_id` bigint unsigned NOT NULL,
  `old_sub_department_id` bigint unsigned DEFAULT NULL,
  `new_department_id` bigint unsigned NOT NULL,
  `new_sub_department_id` bigint unsigned DEFAULT NULL,
  `old_position_id` bigint unsigned NOT NULL,
  `new_position_id` bigint unsigned NOT NULL,
  `docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proposal_docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users_id` bigint unsigned NOT NULL,
  `is_applied` tinyint(1) NOT NULL DEFAULT '0',
  `applied_at` timestamp NULL DEFAULT NULL,
  `applied_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_mutations_employee_id_foreign` (`employee_id`),
  KEY `employee_mutations_old_department_id_foreign` (`old_department_id`),
  KEY `employee_mutations_old_sub_department_id_foreign` (`old_sub_department_id`),
  KEY `employee_mutations_new_department_id_foreign` (`new_department_id`),
  KEY `employee_mutations_new_sub_department_id_foreign` (`new_sub_department_id`),
  KEY `employee_mutations_old_position_id_foreign` (`old_position_id`),
  KEY `employee_mutations_new_position_id_foreign` (`new_position_id`),
  KEY `employee_mutations_users_id_foreign` (`users_id`),
  KEY `employee_mutations_applied_by_foreign` (`applied_by`),
  CONSTRAINT `employee_mutations_applied_by_foreign` FOREIGN KEY (`applied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_mutations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_mutations_new_department_id_foreign` FOREIGN KEY (`new_department_id`) REFERENCES `master_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_mutations_new_position_id_foreign` FOREIGN KEY (`new_position_id`) REFERENCES `master_employee_positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_mutations_new_sub_department_id_foreign` FOREIGN KEY (`new_sub_department_id`) REFERENCES `master_sub_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_mutations_old_department_id_foreign` FOREIGN KEY (`old_department_id`) REFERENCES `master_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_mutations_old_position_id_foreign` FOREIGN KEY (`old_position_id`) REFERENCES `master_employee_positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_mutations_old_sub_department_id_foreign` FOREIGN KEY (`old_sub_department_id`) REFERENCES `master_sub_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_mutations_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_payroll_details
-- ========================================

CREATE TABLE `employee_payroll_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_payroll_id` bigint unsigned NOT NULL,
  `payroll_component_id` bigint unsigned NOT NULL,
  `component_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_type` enum('income','deduction','bonus') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `calculation_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_payroll_details_employee_payroll_id_foreign` (`employee_payroll_id`),
  KEY `employee_payroll_details_payroll_component_id_foreign` (`payroll_component_id`),
  CONSTRAINT `employee_payroll_details_employee_payroll_id_foreign` FOREIGN KEY (`employee_payroll_id`) REFERENCES `employee_payrolls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_payroll_details_payroll_component_id_foreign` FOREIGN KEY (`payroll_component_id`) REFERENCES `payroll_components` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_payrolls
-- ========================================

CREATE TABLE `employee_payrolls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `payroll_period` date NOT NULL,
  `base_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_allowance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_deduction` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_bonus` decimal(15,2) NOT NULL DEFAULT '0.00',
  `gross_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
  `net_salary` decimal(15,2) NOT NULL DEFAULT '0.00',
  `work_days` int NOT NULL DEFAULT '0',
  `present_days` int NOT NULL DEFAULT '0',
  `late_count` int NOT NULL DEFAULT '0',
  `absent_count` int NOT NULL DEFAULT '0',
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('draft','calculated','approved','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `payment_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_payrolls_employee_id_foreign` (`employee_id`),
  KEY `employee_payrolls_approved_by_foreign` (`approved_by`),
  KEY `employee_payrolls_users_id_foreign` (`users_id`),
  CONSTRAINT `employee_payrolls_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_payrolls_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_periodic_salary_increase
-- ========================================

CREATE TABLE `employee_periodic_salary_increase` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `number_psi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_periodic_salary_increase` date NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `old_basic_salary_id` bigint unsigned NOT NULL,
  `new_basic_salary_id` bigint unsigned NOT NULL,
  `total_basic_salary` decimal(15,2) NOT NULL,
  `docs_letter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docs_archive` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `users_id` bigint unsigned NOT NULL,
  `is_applied` tinyint(1) NOT NULL DEFAULT '0',
  `proposal_docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_employee_service_grade_id` bigint unsigned DEFAULT NULL,
  `applied_at` timestamp NULL DEFAULT NULL,
  `applied_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_periodic_salary_increase_employee_id_foreign` (`employee_id`),
  KEY `employee_periodic_salary_increase_old_basic_salary_id_foreign` (`old_basic_salary_id`),
  KEY `employee_periodic_salary_increase_new_basic_salary_id_foreign` (`new_basic_salary_id`),
  KEY `employee_periodic_salary_increase_users_id_foreign` (`users_id`),
  KEY `epsi_new_grade_foreign` (`new_employee_service_grade_id`),
  KEY `employee_periodic_salary_increase_applied_by_foreign` (`applied_by`),
  CONSTRAINT `employee_periodic_salary_increase_applied_by_foreign` FOREIGN KEY (`applied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_periodic_salary_increase_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_periodic_salary_increase_new_basic_salary_id_foreign` FOREIGN KEY (`new_basic_salary_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_periodic_salary_increase_old_basic_salary_id_foreign` FOREIGN KEY (`old_basic_salary_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_periodic_salary_increase_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `epsi_new_grade_foreign` FOREIGN KEY (`new_employee_service_grade_id`) REFERENCES `master_employee_service_grade` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_permissions
-- ========================================

CREATE TABLE `employee_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `start_permission_date` date NOT NULL,
  `end_permission_date` date NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `permission_desc` text COLLATE utf8mb4_unicode_ci,
  `approval_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text COLLATE utf8mb4_unicode_ci,
  `is_resign` tinyint(1) NOT NULL DEFAULT '0',
  `scan_doc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_permissions_employee_id_foreign` (`employee_id`),
  KEY `employee_permissions_permission_id_foreign` (`permission_id`),
  KEY `employee_permissions_users_id_foreign` (`users_id`),
  KEY `employee_permissions_approved_by_foreign` (`approved_by`),
  CONSTRAINT `employee_permissions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_permissions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `master_employee_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_permissions_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_promotions
-- ========================================

CREATE TABLE `employee_promotions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `decision_letter_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `promotion_date` date NOT NULL,
  `next_promotion_date` date DEFAULT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `old_basic_salary_id` bigint unsigned NOT NULL,
  `new_basic_salary_id` bigint unsigned NOT NULL,
  `doc_promotion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proposal_docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `users_id` bigint unsigned NOT NULL,
  `is_applied` tinyint(1) NOT NULL DEFAULT '0',
  `applied_at` timestamp NULL DEFAULT NULL,
  `applied_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_promotions_employee_id_foreign` (`employee_id`),
  KEY `employee_promotions_old_basic_salary_id_foreign` (`old_basic_salary_id`),
  KEY `employee_promotions_new_basic_salary_id_foreign` (`new_basic_salary_id`),
  KEY `employee_promotions_users_id_foreign` (`users_id`),
  KEY `employee_promotions_applied_by_foreign` (`applied_by`),
  CONSTRAINT `employee_promotions_applied_by_foreign` FOREIGN KEY (`applied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_promotions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_promotions_new_basic_salary_id_foreign` FOREIGN KEY (`new_basic_salary_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_promotions_old_basic_salary_id_foreign` FOREIGN KEY (`old_basic_salary_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_promotions_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_retirements
-- ========================================

CREATE TABLE `employee_retirements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `master_employee_retirement_type_id` bigint unsigned DEFAULT NULL,
  `retirement_type` enum('resign','pension','contract_end','termination') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'resign',
  `retirement_date` date NOT NULL,
  `last_working_day` date DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `handover_notes` text COLLATE utf8mb4_unicode_ci,
  `company_assets` text COLLATE utf8mb4_unicode_ci,
  `handover_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_applied` tinyint(1) NOT NULL DEFAULT '0',
  `applied_at` timestamp NULL DEFAULT NULL,
  `applied_by` bigint unsigned DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text COLLATE utf8mb4_unicode_ci,
  `docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `realization_docs` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forwarding_address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forwarding_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forwarding_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `need_reference_letter` tinyint(1) NOT NULL DEFAULT '0',
  `agree_exit_interview` tinyint(1) NOT NULL DEFAULT '1',
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_retirements_employee_id_foreign` (`employee_id`),
  KEY `employee_retirements_users_id_foreign` (`users_id`),
  KEY `employee_retirements_approved_by_foreign` (`approved_by`),
  KEY `employee_retirements_master_employee_retirement_type_id_foreign` (`master_employee_retirement_type_id`),
  KEY `employee_retirements_applied_by_foreign` (`applied_by`),
  CONSTRAINT `employee_retirements_applied_by_foreign` FOREIGN KEY (`applied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_retirements_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_retirements_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_retirements_master_employee_retirement_type_id_foreign` FOREIGN KEY (`master_employee_retirement_type_id`) REFERENCES `master_employee_retirement_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_retirements_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_salaries
-- ========================================

CREATE TABLE `employee_salaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `benefits_1` decimal(15,2) NOT NULL DEFAULT '0.00',
  `benefits_2` decimal(15,2) NOT NULL DEFAULT '0.00',
  `benefits_3` decimal(15,2) NOT NULL DEFAULT '0.00',
  `benefits_4` decimal(15,2) NOT NULL DEFAULT '0.00',
  `benefits_5` decimal(15,2) NOT NULL DEFAULT '0.00',
  `benefits_6` decimal(15,2) NOT NULL DEFAULT '0.00',
  `benefits_7` decimal(15,2) NOT NULL DEFAULT '0.00',
  `benefits_8` decimal(15,2) NOT NULL DEFAULT '0.00',
  `amount` decimal(15,2) NOT NULL,
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_salaries_employee_id_foreign` (`employee_id`),
  KEY `employee_salaries_users_id_foreign` (`users_id`),
  CONSTRAINT `employee_salaries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_salaries_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_salary_cuts
-- ========================================

CREATE TABLE `employee_salary_cuts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `cut_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cut_type` enum('permanent','temporary') COLLATE utf8mb4_unicode_ci NOT NULL,
  `calculation_type` enum('fixed','percentage') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `installment_months` int DEFAULT NULL,
  `paid_months` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_salary_cuts_employee_id_foreign` (`employee_id`),
  KEY `employee_salary_cuts_users_id_foreign` (`users_id`),
  CONSTRAINT `employee_salary_cuts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_salary_cuts_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employee_training
-- ========================================

CREATE TABLE `employee_training` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `training_date` date NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `training_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `training_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organizer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_training` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docs_training` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_training_employee_id_foreign` (`employee_id`),
  KEY `employee_training_users_id_foreign` (`users_id`),
  CONSTRAINT `employee_training_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_training_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: employees
-- ========================================

CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nippam` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'PIN for attendance machine matching',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `place_birth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_birth` date DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` int DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `blood_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` enum('single','married','divorced','widowed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `familycard_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_tk_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_tk_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_kes_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_kes_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_kes_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rek_dplk_pribadi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rek_dplk_bersama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dapenma_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dapenma_phdp` decimal(15,2) DEFAULT NULL,
  `dapenma_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leave_balance` int NOT NULL DEFAULT '0',
  `entry_date` date DEFAULT NULL,
  `probation_appointment_date` date DEFAULT NULL,
  `permanent_appointment_date` date DEFAULT NULL,
  `length_service` int DEFAULT NULL,
  `retirement` date DEFAULT NULL,
  `employment_status_id` bigint unsigned DEFAULT NULL,
  `master_employee_agreement_id` bigint unsigned DEFAULT NULL,
  `agreement_date_start` date DEFAULT NULL,
  `agreement_date_end` date DEFAULT NULL,
  `employee_education_id` bigint unsigned DEFAULT NULL,
  `grade_date_start` date DEFAULT NULL,
  `grade_date_end` date DEFAULT NULL,
  `next_promotion_date` date DEFAULT NULL,
  `basic_salary_id` bigint unsigned DEFAULT NULL,
  `non_permanent_salary_id` bigint unsigned DEFAULT NULL,
  `employee_service_grade_id` bigint unsigned DEFAULT NULL,
  `periodic_salary_date_start` date DEFAULT NULL,
  `periodic_salary_date_end` date DEFAULT NULL,
  `next_kgb_date` date DEFAULT NULL,
  `employee_position_id` bigint unsigned DEFAULT NULL,
  `departments_id` bigint unsigned DEFAULT NULL,
  `sub_department_id` bigint unsigned DEFAULT NULL,
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `bagian_id` bigint unsigned DEFAULT NULL,
  `cabang_id` bigint unsigned DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_nippam_unique` (`nippam`),
  UNIQUE KEY `employees_username_unique` (`username`),
  UNIQUE KEY `employees_email_unique` (`email`),
  UNIQUE KEY `employees_office_email_unique` (`office_email`),
  UNIQUE KEY `employees_pin_unique` (`pin`),
  KEY `employees_employment_status_id_foreign` (`employment_status_id`),
  KEY `employees_master_employee_agreement_id_foreign` (`master_employee_agreement_id`),
  KEY `employees_employee_education_id_foreign` (`employee_education_id`),
  KEY `employees_basic_salary_id_foreign` (`basic_salary_id`),
  KEY `employees_employee_position_id_foreign` (`employee_position_id`),
  KEY `employees_departments_id_foreign` (`departments_id`),
  KEY `employees_users_id_foreign` (`users_id`),
  KEY `employees_sub_department_id_foreign` (`sub_department_id`),
  KEY `employees_bagian_id_foreign` (`bagian_id`),
  KEY `employees_cabang_id_foreign` (`cabang_id`),
  KEY `employees_unit_id_foreign` (`unit_id`),
  KEY `employees_employee_service_grade_id_foreign` (`employee_service_grade_id`),
  KEY `emp_non_perm_salary_fk` (`non_permanent_salary_id`),
  CONSTRAINT `emp_non_perm_salary_fk` FOREIGN KEY (`non_permanent_salary_id`) REFERENCES `master_employee_non_permanent_salaries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_bagian_id_foreign` FOREIGN KEY (`bagian_id`) REFERENCES `master_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_basic_salary_id_foreign` FOREIGN KEY (`basic_salary_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_cabang_id_foreign` FOREIGN KEY (`cabang_id`) REFERENCES `master_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_departments_id_foreign` FOREIGN KEY (`departments_id`) REFERENCES `master_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_employee_education_id_foreign` FOREIGN KEY (`employee_education_id`) REFERENCES `master_employee_education` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_employee_position_id_foreign` FOREIGN KEY (`employee_position_id`) REFERENCES `master_employee_positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_employee_service_grade_id_foreign` FOREIGN KEY (`employee_service_grade_id`) REFERENCES `master_employee_service_grade` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_employment_status_id_foreign` FOREIGN KEY (`employment_status_id`) REFERENCES `master_employee_status_employments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_master_employee_agreement_id_foreign` FOREIGN KEY (`master_employee_agreement_id`) REFERENCES `master_employee_agreements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_sub_department_id_foreign` FOREIGN KEY (`sub_department_id`) REFERENCES `master_sub_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `master_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: failed_jobs
-- ========================================

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: interview_processes
-- ========================================

CREATE TABLE `interview_processes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_application_id` bigint unsigned NOT NULL,
  `interview_stage` int NOT NULL DEFAULT '1',
  `interview_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `interview_date` date NOT NULL,
  `interview_time` time NOT NULL,
  `interview_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interviewer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interviewer_id` bigint unsigned DEFAULT NULL,
  `result` enum('passed','failed','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `score` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `status` enum('scheduled','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `interview_processes_job_application_id_foreign` (`job_application_id`),
  KEY `interview_processes_interviewer_id_foreign` (`interviewer_id`),
  KEY `interview_processes_users_id_foreign` (`users_id`),
  CONSTRAINT `interview_processes_interviewer_id_foreign` FOREIGN KEY (`interviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `interview_processes_job_application_id_foreign` FOREIGN KEY (`job_application_id`) REFERENCES `job_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interview_processes_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: job_application_archives
-- ========================================

CREATE TABLE `job_application_archives` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_application_id` bigint unsigned NOT NULL,
  `application_data` json NOT NULL,
  `interview_data` json DEFAULT NULL,
  `decision` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `decision_reason` text COLLATE utf8mb4_unicode_ci,
  `decision_date` date NOT NULL,
  `decided_by` bigint unsigned NOT NULL,
  `proposed_agreement_type_id` bigint unsigned DEFAULT NULL,
  `proposed_employment_status_id` bigint unsigned DEFAULT NULL,
  `proposed_grade_id` bigint unsigned DEFAULT NULL,
  `proposed_non_permanent_salary_id` bigint unsigned DEFAULT NULL,
  `proposed_salary` decimal(15,2) DEFAULT NULL,
  `proposed_start_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_application_archives_job_application_id_foreign` (`job_application_id`),
  KEY `job_application_archives_proposed_agreement_type_id_foreign` (`proposed_agreement_type_id`),
  KEY `job_application_archives_proposed_employment_status_id_foreign` (`proposed_employment_status_id`),
  KEY `job_application_archives_proposed_grade_id_foreign` (`proposed_grade_id`),
  KEY `job_application_archives_decision_decision_date_index` (`decision`,`decision_date`),
  KEY `jaa_non_perm_salary_fk` (`proposed_non_permanent_salary_id`),
  CONSTRAINT `jaa_non_perm_salary_fk` FOREIGN KEY (`proposed_non_permanent_salary_id`) REFERENCES `master_employee_non_permanent_salaries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_application_archives_job_application_id_foreign` FOREIGN KEY (`job_application_id`) REFERENCES `job_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_application_archives_proposed_agreement_type_id_foreign` FOREIGN KEY (`proposed_agreement_type_id`) REFERENCES `master_employee_agreements` (`id`),
  CONSTRAINT `job_application_archives_proposed_employment_status_id_foreign` FOREIGN KEY (`proposed_employment_status_id`) REFERENCES `master_employee_status_employments` (`id`),
  CONSTRAINT `job_application_archives_proposed_grade_id_foreign` FOREIGN KEY (`proposed_grade_id`) REFERENCES `master_employee_grades` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: job_applications
-- ========================================

CREATE TABLE `job_applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `application_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `place_birth` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_birth` date NOT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `marital_status` enum('single','married','divorced','widowed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_number` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `applied_position_id` bigint unsigned NOT NULL,
  `applied_department_id` bigint unsigned NOT NULL,
  `applied_sub_department_id` bigint unsigned DEFAULT NULL,
  `education_level_id` bigint unsigned NOT NULL,
  `education_institution` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `education_major` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `education_graduation_year` year NOT NULL,
  `education_gpa` decimal(3,2) DEFAULT NULL,
  `last_company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_work_start_date` date DEFAULT NULL,
  `last_work_end_date` date DEFAULT NULL,
  `last_work_description` text COLLATE utf8mb4_unicode_ci,
  `last_salary` decimal(15,2) DEFAULT NULL,
  `expected_salary` decimal(15,2) DEFAULT NULL,
  `available_start_date` date DEFAULT NULL,
  `documents` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `interview_schedule` json DEFAULT NULL,
  `interview_results` json DEFAULT NULL,
  `reference_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_relation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `interview_at` timestamp NULL DEFAULT NULL,
  `decision_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_applications_application_number_unique` (`application_number`),
  UNIQUE KEY `job_applications_email_unique` (`email`),
  KEY `job_applications_applied_position_id_foreign` (`applied_position_id`),
  KEY `job_applications_applied_department_id_foreign` (`applied_department_id`),
  KEY `job_applications_applied_sub_department_id_foreign` (`applied_sub_department_id`),
  KEY `job_applications_education_level_id_foreign` (`education_level_id`),
  KEY `job_applications_status_applied_position_id_index` (`status`,`applied_position_id`),
  KEY `job_applications_submitted_at_status_index` (`submitted_at`,`status`),
  CONSTRAINT `job_applications_applied_department_id_foreign` FOREIGN KEY (`applied_department_id`) REFERENCES `master_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_applications_applied_position_id_foreign` FOREIGN KEY (`applied_position_id`) REFERENCES `master_employee_positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_applications_applied_sub_department_id_foreign` FOREIGN KEY (`applied_sub_department_id`) REFERENCES `master_sub_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_applications_education_level_id_foreign` FOREIGN KEY (`education_level_id`) REFERENCES `master_employee_education` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: job_batches
-- ========================================

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: jobs
-- ========================================

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_departments
-- ========================================

CREATE TABLE `master_departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bagian',
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_departments_users_id_foreign` (`users_id`),
  CONSTRAINT `master_departments_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_agreements
-- ========================================

CREATE TABLE `master_employee_agreements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_agreements_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_agreements_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_archive_types
-- ========================================

CREATE TABLE `master_employee_archive_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_archive_types_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_archive_types_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_basic_salary
-- ========================================

CREATE TABLE `master_employee_basic_salary` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_service_grade_id` bigint unsigned NOT NULL,
  `employee_grade_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_basic_salary_employee_service_grade_id_foreign` (`employee_service_grade_id`),
  KEY `master_employee_basic_salary_employee_grade_id_foreign` (`employee_grade_id`),
  KEY `master_employee_basic_salary_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_basic_salary_employee_grade_id_foreign` FOREIGN KEY (`employee_grade_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_employee_basic_salary_employee_service_grade_id_foreign` FOREIGN KEY (`employee_service_grade_id`) REFERENCES `master_employee_service_grade` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_employee_basic_salary_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=579 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_benefits
-- ========================================

CREATE TABLE `master_employee_benefits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_benefits_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_benefits_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_education
-- ========================================

CREATE TABLE `master_employee_education` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_education_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_education_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_families
-- ========================================

CREATE TABLE `master_employee_families` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_families_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_families_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_grade_benefit
-- ========================================

CREATE TABLE `master_employee_grade_benefit` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_grade_id` bigint unsigned NOT NULL,
  `benefit_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_grade_benefit_employee_grade_id_foreign` (`employee_grade_id`),
  KEY `master_employee_grade_benefit_benefit_id_foreign` (`benefit_id`),
  KEY `master_employee_grade_benefit_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_grade_benefit_benefit_id_foreign` FOREIGN KEY (`benefit_id`) REFERENCES `master_employee_benefits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_employee_grade_benefit_employee_grade_id_foreign` FOREIGN KEY (`employee_grade_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_employee_grade_benefit_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_grade_salary_cuts
-- ========================================

CREATE TABLE `master_employee_grade_salary_cuts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_grade_id` bigint unsigned NOT NULL,
  `salary_cuts_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_grade_salary_cuts_employee_grade_id_foreign` (`employee_grade_id`),
  KEY `master_employee_grade_salary_cuts_salary_cuts_id_foreign` (`salary_cuts_id`),
  KEY `master_employee_grade_salary_cuts_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_grade_salary_cuts_employee_grade_id_foreign` FOREIGN KEY (`employee_grade_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_employee_grade_salary_cuts_salary_cuts_id_foreign` FOREIGN KEY (`salary_cuts_id`) REFERENCES `master_employee_salary_cuts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_employee_grade_salary_cuts_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_grades
-- ========================================

CREATE TABLE `master_employee_grades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_grades_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_grades_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_non_permanent_salaries
-- ========================================

CREATE TABLE `master_employee_non_permanent_salaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employment_status_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mens_emp_status_fk` (`employment_status_id`),
  KEY `master_employee_non_permanent_salaries_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_non_permanent_salaries_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mens_emp_status_fk` FOREIGN KEY (`employment_status_id`) REFERENCES `master_employee_status_employments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_permissions
-- ========================================

CREATE TABLE `master_employee_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_permissions_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_permissions_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_position_benefit
-- ========================================

CREATE TABLE `master_employee_position_benefit` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_position_id` bigint unsigned NOT NULL,
  `benefit_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_position_benefit_employee_position_id_foreign` (`employee_position_id`),
  KEY `master_employee_position_benefit_benefit_id_foreign` (`benefit_id`),
  KEY `master_employee_position_benefit_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_position_benefit_benefit_id_foreign` FOREIGN KEY (`benefit_id`) REFERENCES `master_employee_benefits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_employee_position_benefit_employee_position_id_foreign` FOREIGN KEY (`employee_position_id`) REFERENCES `master_employee_positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_employee_position_benefit_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_position_salary_cuts
-- ========================================

CREATE TABLE `master_employee_position_salary_cuts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_position_id` bigint unsigned NOT NULL,
  `salary_cuts_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_salary_cuts_position_fk` (`employee_position_id`),
  KEY `pos_salary_cuts_cut_fk` (`salary_cuts_id`),
  KEY `master_employee_position_salary_cuts_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_position_salary_cuts_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_salary_cuts_cut_fk` FOREIGN KEY (`salary_cuts_id`) REFERENCES `master_employee_salary_cuts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pos_salary_cuts_position_fk` FOREIGN KEY (`employee_position_id`) REFERENCES `master_employee_positions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_positions
-- ========================================

CREATE TABLE `master_employee_positions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_positions_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_positions_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_retirement_types
-- ========================================

CREATE TABLE `master_employee_retirement_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_retirement_types_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_retirement_types_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_salary_cuts
-- ========================================

CREATE TABLE `master_employee_salary_cuts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_salary_cuts_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_salary_cuts_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_service_grade
-- ========================================

CREATE TABLE `master_employee_service_grade` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_grade_id` bigint unsigned DEFAULT NULL,
  `service_grade` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_service_grade_employee_grade_id_foreign` (`employee_grade_id`),
  KEY `master_employee_service_grade_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_service_grade_employee_grade_id_foreign` FOREIGN KEY (`employee_grade_id`) REFERENCES `master_employee_grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_employee_service_grade_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_employee_status_employments
-- ========================================

CREATE TABLE `master_employee_status_employments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_employee_status_employments_users_id_foreign` (`users_id`),
  CONSTRAINT `master_employee_status_employments_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_office_locations
-- ========================================

CREATE TABLE `master_office_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `radius` int NOT NULL DEFAULT '100',
  `departments_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_office_locations_code_unique` (`code`),
  KEY `master_office_locations_users_id_foreign` (`users_id`),
  KEY `master_office_locations_departments_id_foreign` (`departments_id`),
  CONSTRAINT `master_office_locations_departments_id_foreign` FOREIGN KEY (`departments_id`) REFERENCES `master_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `master_office_locations_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_standar_harga_satuans
-- ========================================

CREATE TABLE `master_standar_harga_satuans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spesifikasi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'per_day',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_standar_harga_satuans_users_id_foreign` (`users_id`),
  CONSTRAINT `master_standar_harga_satuans_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: master_sub_departments
-- ========================================

CREATE TABLE `master_sub_departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `departments_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `users_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_sub_departments_departments_id_foreign` (`departments_id`),
  KEY `master_sub_departments_users_id_foreign` (`users_id`),
  CONSTRAINT `master_sub_departments_departments_id_foreign` FOREIGN KEY (`departments_id`) REFERENCES `master_departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `master_sub_departments_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: migrations
-- ========================================

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: model_has_permissions
-- ========================================

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: model_has_roles
-- ========================================

CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: password_reset_tokens
-- ========================================

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: payroll_components
-- ========================================

CREATE TABLE `payroll_components` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `component_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_type` enum('income','deduction','bonus') COLLATE utf8mb4_unicode_ci NOT NULL,
  `calculation_method` enum('fixed','percentage','formula') COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `formula` text COLLATE utf8mb4_unicode_ci,
  `is_taxable` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_components_component_code_unique` (`component_code`),
  KEY `payroll_components_users_id_foreign` (`users_id`),
  CONSTRAINT `payroll_components_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: payroll_formulas
-- ========================================

CREATE TABLE `payroll_formulas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `formula_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `formula_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applies_to` enum('status','grade','position','all') COLLATE utf8mb4_unicode_ci NOT NULL,
  `applies_to_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `formula_components` json NOT NULL,
  `calculation_rules` text COLLATE utf8mb4_unicode_ci,
  `percentage_multiplier` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_formulas_formula_code_unique` (`formula_code`),
  KEY `payroll_formulas_users_id_foreign` (`users_id`),
  CONSTRAINT `payroll_formulas_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: performance_appraisals
-- ========================================

CREATE TABLE `performance_appraisals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `appraisal_period` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `appraisal_date` date NOT NULL,
  `appraiser_id` bigint unsigned NOT NULL,
  `criteria_scores` json NOT NULL,
  `total_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `performance_grade` enum('A','B','C','D','E') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `strengths` text COLLATE utf8mb4_unicode_ci,
  `weaknesses` text COLLATE utf8mb4_unicode_ci,
  `recommendations` text COLLATE utf8mb4_unicode_ci,
  `employee_comment` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','submitted','reviewed','approved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `users_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `performance_appraisals_employee_id_foreign` (`employee_id`),
  KEY `performance_appraisals_appraiser_id_foreign` (`appraiser_id`),
  KEY `performance_appraisals_approved_by_foreign` (`approved_by`),
  KEY `performance_appraisals_users_id_foreign` (`users_id`),
  CONSTRAINT `performance_appraisals_appraiser_id_foreign` FOREIGN KEY (`appraiser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `performance_appraisals_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `performance_appraisals_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `performance_appraisals_users_id_foreign` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: permissions
-- ========================================

CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=396 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: role_has_permissions
-- ========================================

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: roles
-- ========================================

CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: sessions
-- ========================================

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: telescope_entries
-- ========================================

CREATE TABLE `telescope_entries` (
  `sequence` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `should_display_on_index` tinyint(1) NOT NULL DEFAULT '1',
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`sequence`),
  UNIQUE KEY `telescope_entries_uuid_unique` (`uuid`),
  KEY `telescope_entries_batch_id_index` (`batch_id`),
  KEY `telescope_entries_family_hash_index` (`family_hash`),
  KEY `telescope_entries_created_at_index` (`created_at`),
  KEY `telescope_entries_type_should_display_on_index_index` (`type`,`should_display_on_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: telescope_entries_tags
-- ========================================

CREATE TABLE `telescope_entries_tags` (
  `entry_uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`entry_uuid`,`tag`),
  KEY `telescope_entries_tags_tag_index` (`tag`),
  CONSTRAINT `telescope_entries_tags_entry_uuid_foreign` FOREIGN KEY (`entry_uuid`) REFERENCES `telescope_entries` (`uuid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: telescope_monitoring
-- ========================================

CREATE TABLE `telescope_monitoring` (
  `tag` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

-- ========================================
-- Table: users
-- ========================================

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data error: You must specify an orderBy clause when using this function.

