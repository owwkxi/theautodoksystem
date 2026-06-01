-- ============================================================================
-- THE AUTODOK - COMPLETE DATABASE SCHEMA
-- Automotive Care Management System
-- Version: 2.0
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `autodok_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `autodok_db`;

-- ============================================================================
-- USERS & AUTHENTICATION
-- ============================================================================

-- Main users table (Admin only)
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('admin') NOT NULL DEFAULT 'admin',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `last_login_ip` VARCHAR(45) DEFAULT NULL,
  `password_changed_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STAFF MANAGEMENT
-- ============================================================================

-- Staff table (All employees including technicians)
CREATE TABLE `staff` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `staff_id` VARCHAR(20) NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `full_name` VARCHAR(100) GENERATED ALWAYS AS (CONCAT(`first_name`,' ',`last_name`)) STORED,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT DEFAULT NULL,
  `role` ENUM('cashier','chief_mechanic','service_adviser','lead_man','technician') NOT NULL,
  `username` VARCHAR(50) DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `profile_photo` VARCHAR(255) DEFAULT NULL,
  `hire_date` DATE NOT NULL,
  `status` ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active',
  `team_id` INT(11) DEFAULT NULL,
  `supervisor_id` INT(11) DEFAULT NULL,
  `hourly_rate` DECIMAL(10,2) DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_id` (`staff_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  KEY `idx_team` (`team_id`),
  KEY `idx_supervisor` (`supervisor_id`),
  CONSTRAINT `fk_staff_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teams table
CREATE TABLE `teams` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `team_name` VARCHAR(100) NOT NULL,
  `team_leader_id` INT(11) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leader` (`team_leader_id`),
  CONSTRAINT `fk_team_leader` FOREIGN KEY (`team_leader_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- ATTENDANCE MANAGEMENT
-- ============================================================================

CREATE TABLE `attendance` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `staff_id` INT(11) NOT NULL,
  `date` DATE NOT NULL,
  `time_in` TIME NOT NULL,
  `time_out` TIME DEFAULT NULL,
  `photo_in` VARCHAR(255) DEFAULT NULL,
  `photo_out` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('present','late','absent','on_leave') NOT NULL DEFAULT 'present',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_date` (`staff_id`,`date`),
  KEY `idx_date` (`date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_attendance_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SERVICES MANAGEMENT
-- ============================================================================

-- Service categories
CREATE TABLE `service_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(100) NOT NULL,
  `category_code` VARCHAR(20) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_code` (`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services table
CREATE TABLE `services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `service_name` VARCHAR(100) NOT NULL,
  `service_code` VARCHAR(20) NOT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `base_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `labor_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `estimated_duration` INT(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_code` (`service_code`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_service_category` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Service bundles (PMS packages)
CREATE TABLE `service_bundles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `bundle_name` VARCHAR(100) NOT NULL,
  `bundle_code` VARCHAR(20) NOT NULL,
  `bundle_type` ENUM('light_pms','regular_pms','heavy_pms','custom') NOT NULL,
  `description` TEXT DEFAULT NULL,
  `package_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `estimated_duration` INT(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundle_code` (`bundle_code`),
  KEY `idx_type` (`bundle_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bundle services (many-to-many)
CREATE TABLE `bundle_services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `bundle_id` INT(11) NOT NULL,
  `service_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundle_service` (`bundle_id`,`service_id`),
  KEY `idx_service` (`service_id`),
  CONSTRAINT `fk_bundle_services_bundle` FOREIGN KEY (`bundle_id`) REFERENCES `service_bundles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bundle_services_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INVENTORY MANAGEMENT
-- ============================================================================

-- Product categories
CREATE TABLE `product_categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Brands
CREATE TABLE `brands` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `brand_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Units of measurement
CREATE TABLE `units` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `unit_name` VARCHAR(50) NOT NULL,
  `unit_symbol` VARCHAR(10) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers
CREATE TABLE `suppliers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` VARCHAR(100) NOT NULL,
  `contact_person` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products/Inventory
CREATE TABLE `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_code` VARCHAR(50) NOT NULL,
  `product_name` VARCHAR(100) NOT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `brand_id` INT(11) DEFAULT NULL,
  `unit_id` INT(11) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `cost_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `quantity` INT(11) NOT NULL DEFAULT 0,
  `min_stock_level` INT(11) DEFAULT 10,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code` (`product_code`),
  KEY `idx_category` (`category_id`),
  KEY `idx_brand` (`brand_id`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_product_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_product_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Inventory transactions
CREATE TABLE `inventory_transactions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `transaction_type` ENUM('stock_in','stock_out','adjustment','return') NOT NULL,
  `quantity` INT(11) NOT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL COMMENT 'job_order, purchase_order, etc',
  `reference_id` INT(11) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_type` (`transaction_type`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  CONSTRAINT `fk_inventory_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- JOB ORDERS
-- ============================================================================

-- Customers
CREATE TABLE `customers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_code` VARCHAR(20) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_code` (`customer_code`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vehicles
CREATE TABLE `vehicles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL,
  `vehicle_owner` VARCHAR(100) DEFAULT NULL,
  `vehicle_type` VARCHAR(50) DEFAULT NULL,
  `brand` VARCHAR(50) DEFAULT NULL,
  `model` VARCHAR(50) DEFAULT NULL,
  `year_model` VARCHAR(4) DEFAULT NULL,
  `plate_number` VARCHAR(20) DEFAULT NULL,
  `engine_type` VARCHAR(50) DEFAULT NULL,
  `mileage` VARCHAR(20) DEFAULT NULL,
  `color` VARCHAR(30) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_plate` (`plate_number`),
  CONSTRAINT `fk_vehicle_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Job Orders
CREATE TABLE `job_orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `job_order_number` VARCHAR(20) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `vehicle_id` INT(11) NOT NULL,
  `service_adviser_id` INT(11) DEFAULT NULL,
  `status` ENUM('pending','ongoing','under_inspection','for_approval','completed','released','returned_for_revision','cancelled') NOT NULL DEFAULT 'pending',
  `priority` ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `labor_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `parts_total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` ENUM('none','senior_citizen','pwd','promotional','custom') DEFAULT 'none',
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `discount_percentage` DECIMAL(5,2) DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` ENUM('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `payment_method` ENUM('cash','card','bank_transfer','gcash','paymaya') DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `estimated_completion` DATETIME DEFAULT NULL,
  `actual_completion` DATETIME DEFAULT NULL,
  `created_by` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_order_number` (`job_order_number`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_vehicle` (`vehicle_id`),
  KEY `idx_adviser` (`service_adviser_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_jo_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jo_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jo_adviser` FOREIGN KEY (`service_adviser_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_jo_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job Order Services
CREATE TABLE `job_order_services` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `job_order_id` INT(11) NOT NULL,
  `service_id` INT(11) DEFAULT NULL,
  `bundle_id` INT(11) DEFAULT NULL,
  `service_name` VARCHAR(100) NOT NULL,
  `service_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `labor_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job_order` (`job_order_id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_bundle` (`bundle_id`),
  CONSTRAINT `fk_jos_job_order` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jos_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_jos_bundle` FOREIGN KEY (`bundle_id`) REFERENCES `service_bundles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Job Order Products
CREATE TABLE `job_order_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `job_order_id` INT(11) NOT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `product_name` VARCHAR(100) NOT NULL,
  `product_type` ENUM('engine_oil','oil_filter','parts','fluids','others') NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job_order` (`job_order_id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `fk_jop_job_order` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jop_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job Order Technician Assignments
CREATE TABLE `job_order_technicians` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `job_order_id` INT(11) NOT NULL,
  `technician_id` INT(11) NOT NULL,
  `assigned_at` DATETIME NOT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `work_duration` INT(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `status` ENUM('assigned','working','completed','on_hold') NOT NULL DEFAULT 'assigned',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job_order` (`job_order_id`),
  KEY `idx_technician` (`technician_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_jot_job_order` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jot_technician` FOREIGN KEY (`technician_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Work Timer Sessions
CREATE TABLE `work_sessions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `job_order_technician_id` INT(11) NOT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME DEFAULT NULL,
  `duration` INT(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_assignment` (`job_order_technician_id`),
  CONSTRAINT `fk_ws_assignment` FOREIGN KEY (`job_order_technician_id`) REFERENCES `job_order_technicians` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Job Order Approvals
CREATE TABLE `job_order_approvals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `job_order_id` INT(11) NOT NULL,
  `reviewer_id` INT(11) NOT NULL,
  `reviewer_role` ENUM('service_adviser','chief_mechanic') NOT NULL,
  `status` ENUM('approved','needs_revision','rework_required') NOT NULL,
  `comments` TEXT DEFAULT NULL,
  `reviewed_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job_order` (`job_order_id`),
  KEY `idx_reviewer` (`reviewer_id`),
  CONSTRAINT `fk_joa_job_order` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_joa_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PAYMENTS
-- ============================================================================

CREATE TABLE `payments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `job_order_id` INT(11) NOT NULL,
  `payment_date` DATETIME NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cash','card','bank_transfer','gcash','paymaya') NOT NULL,
  `reference_number` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `received_by` INT(11) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job_order` (`job_order_id`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_received_by` (`received_by`),
  CONSTRAINT `fk_payment_job_order` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- NOTIFICATIONS
-- ============================================================================

CREATE TABLE `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `staff_id` INT(11) DEFAULT NULL,
  `type` ENUM('job_assigned','job_status','payment','low_stock','system','staff_update','account_update') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `reference_type` VARCHAR(50) DEFAULT NULL,
  `reference_id` INT(11) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_staff` (`staff_id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- ACTIVITY LOGS & SECURITY
-- ============================================================================

CREATE TABLE `activity_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `staff_id` INT(11) DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_staff` (`staff_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CSRF Tokens
CREATE TABLE `csrf_tokens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `token` VARCHAR(64) NOT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PERMISSIONS & ROLES
-- ============================================================================

CREATE TABLE `permissions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `permission_name` VARCHAR(100) NOT NULL,
  `permission_code` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `module` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_code` (`permission_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role` VARCHAR(50) NOT NULL,
  `permission_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role`,`permission_id`),
  KEY `idx_permission` (`permission_id`),
  CONSTRAINT `fk_role_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SYSTEM SETTINGS
-- ============================================================================

CREATE TABLE `system_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `setting_type` ENUM('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
