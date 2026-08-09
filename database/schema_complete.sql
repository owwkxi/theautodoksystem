-- ============================================================================
-- THE AUTODOK - CLEAN, ORGANIZED, SECURED SCHEMA (SINGLE FILE)
-- Target: MySQL/MariaDB (InnoDB, utf8mb4)
-- Notes:
--   1) This script is intended for fresh setup or controlled rebuild.
--   2) It drops and recreates application tables in dependency-safe order.
-- ============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS autodok_prime_auto_services_db
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE autodok_prime_auto_services_db;

-- ============================================================================
-- DROP TABLES (dependency-safe reverse order)
-- ============================================================================
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS csrf_tokens;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS job_order_approvals;
DROP TABLE IF EXISTS work_sessions;
DROP TABLE IF EXISTS job_order_technicians;
DROP TABLE IF EXISTS job_estimates;
DROP TABLE IF EXISTS job_order_products;
DROP TABLE IF EXISTS job_order_services;
DROP TABLE IF EXISTS job_orders;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS inventory_transactions;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS brands;
DROP TABLE IF EXISTS product_categories;
DROP TABLE IF EXISTS bundle_services;
DROP TABLE IF EXISTS service_bundles;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS staff;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS system_settings;

SET FOREIGN_KEY_CHECKS = 1;

START TRANSACTION;

-- ============================================================================
-- USERS & AUTHENTICATION
-- ============================================================================
CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) DEFAULT NULL,
  role ENUM('admin') NOT NULL DEFAULT 'admin',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  last_login DATETIME DEFAULT NULL,
  last_login_ip VARCHAR(45) DEFAULT NULL,
  password_changed_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_status (status),
  KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- STAFF, TEAMS, ATTENDANCE
-- ============================================================================
CREATE TABLE staff (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  staff_id VARCHAR(20) NOT NULL,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  full_name VARCHAR(101) GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED,
  email VARCHAR(100) DEFAULT NULL,
  phone VARCHAR(20) NOT NULL,
  address TEXT DEFAULT NULL,
  role ENUM('admin','cashier','chief_mechanic','service_adviser','technician') NOT NULL,
  username VARCHAR(50) DEFAULT NULL,
  password VARCHAR(255) DEFAULT NULL,
  profile_photo VARCHAR(255) DEFAULT NULL,
  hire_date DATE NOT NULL,
  status ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active',
  team_id INT UNSIGNED DEFAULT NULL,
  supervisor_id INT UNSIGNED DEFAULT NULL,
  hourly_rate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_staff_staff_id (staff_id),
  UNIQUE KEY uq_staff_username (username),
  UNIQUE KEY uq_staff_email (email),
  KEY idx_staff_role (role),
  KEY idx_staff_status (status),
  KEY idx_staff_team (team_id),
  KEY idx_staff_supervisor (supervisor_id),
  CONSTRAINT fk_staff_supervisor
    FOREIGN KEY (supervisor_id) REFERENCES staff (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE teams (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  team_name VARCHAR(100) NOT NULL,
  team_leader_id INT UNSIGNED DEFAULT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_teams_leader (team_leader_id),
  CONSTRAINT fk_teams_leader
    FOREIGN KEY (team_leader_id) REFERENCES staff (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendance (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  staff_id INT UNSIGNED NOT NULL,
  date DATE NOT NULL,
  time_in TIME NOT NULL,
  time_out TIME DEFAULT NULL,
  photo_in VARCHAR(255) DEFAULT NULL,
  photo_out VARCHAR(255) DEFAULT NULL,
  status ENUM('present','late','absent','on_leave') NOT NULL DEFAULT 'present',
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_attendance_staff_date (staff_id, date),
  KEY idx_attendance_date (date),
  KEY idx_attendance_status (status),
  CONSTRAINT fk_attendance_staff
    FOREIGN KEY (staff_id) REFERENCES staff (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SERVICES & BUNDLES
-- ============================================================================
CREATE TABLE service_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_name VARCHAR(100) NOT NULL,
  category_code VARCHAR(20) NOT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_service_categories_code (category_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  service_name VARCHAR(100) NOT NULL,
  service_code VARCHAR(20) NOT NULL,
  category_id INT UNSIGNED DEFAULT NULL,
  description TEXT DEFAULT NULL,
  base_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  labor_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estimated_duration INT UNSIGNED DEFAULT NULL COMMENT 'minutes',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_services_code (service_code),
  KEY idx_services_category (category_id),
  KEY idx_services_status (status),
  CONSTRAINT fk_services_category
    FOREIGN KEY (category_id) REFERENCES service_categories (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE service_bundles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  bundle_name VARCHAR(100) NOT NULL,
  bundle_code VARCHAR(20) NOT NULL,
  bundle_type ENUM('light_pms','regular_pms','heavy_pms','custom') NOT NULL,
  description TEXT DEFAULT NULL,
  package_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estimated_duration INT UNSIGNED DEFAULT NULL COMMENT 'minutes',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_service_bundles_code (bundle_code),
  KEY idx_service_bundles_type (bundle_type),
  KEY idx_service_bundles_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bundle_services (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  bundle_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bundle_services_pair (bundle_id, service_id),
  KEY idx_bundle_services_service (service_id),
  CONSTRAINT fk_bundle_services_bundle
    FOREIGN KEY (bundle_id) REFERENCES service_bundles (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_bundle_services_service
    FOREIGN KEY (service_id) REFERENCES services (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INVENTORY
-- ============================================================================
CREATE TABLE product_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_name VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE brands (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  brand_name VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE units (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  unit_name VARCHAR(50) NOT NULL,
  unit_symbol VARCHAR(10) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE suppliers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  supplier_name VARCHAR(100) NOT NULL,
  contact_person VARCHAR(100) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(100) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_code VARCHAR(50) NOT NULL,
  product_name VARCHAR(100) NOT NULL,
  category_id INT UNSIGNED DEFAULT NULL,
  brand_id INT UNSIGNED DEFAULT NULL,
  unit_id INT UNSIGNED DEFAULT NULL,
  description TEXT DEFAULT NULL,
  cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  selling_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  quantity INT NOT NULL DEFAULT 0,
  min_stock_level INT NOT NULL DEFAULT 10,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_products_code (product_code),
  KEY idx_products_category (category_id),
  KEY idx_products_brand (brand_id),
  KEY idx_products_unit (unit_id),
  KEY idx_products_status (status),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES product_categories (id)
    ON DELETE SET NULL,
  CONSTRAINT fk_products_brand
    FOREIGN KEY (brand_id) REFERENCES brands (id)
    ON DELETE SET NULL,
  CONSTRAINT fk_products_unit
    FOREIGN KEY (unit_id) REFERENCES units (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE inventory_transactions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  transaction_type ENUM('stock_in','stock_out','adjustment','return') NOT NULL,
  quantity INT NOT NULL,
  reference_type VARCHAR(50) DEFAULT NULL,
  reference_id INT UNSIGNED DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_by INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_inventory_product (product_id),
  KEY idx_inventory_type (transaction_type),
  KEY idx_inventory_reference (reference_type, reference_id),
  CONSTRAINT fk_inventory_transactions_product
    FOREIGN KEY (product_id) REFERENCES products (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CUSTOMERS, VEHICLES, JOB ORDERS
-- ============================================================================
CREATE TABLE customers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_code VARCHAR(20) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(100) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_customers_code (customer_code),
  KEY idx_customers_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehicles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id INT UNSIGNED NOT NULL,
  vehicle_owner VARCHAR(100) DEFAULT NULL,
  vehicle_type VARCHAR(50) DEFAULT NULL,
  brand VARCHAR(50) DEFAULT NULL,
  model VARCHAR(50) DEFAULT NULL,
  year_model VARCHAR(4) DEFAULT NULL,
  plate_number VARCHAR(20) DEFAULT NULL,
  engine_type VARCHAR(50) DEFAULT NULL,
  mileage VARCHAR(20) DEFAULT NULL,
  color VARCHAR(30) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_vehicles_customer (customer_id),
  KEY idx_vehicles_plate (plate_number),
  CONSTRAINT fk_vehicles_customer
    FOREIGN KEY (customer_id) REFERENCES customers (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_order_number VARCHAR(20) NOT NULL,
  customer_id INT UNSIGNED NOT NULL,
  vehicle_id INT UNSIGNED NOT NULL,
  service_adviser_id INT UNSIGNED DEFAULT NULL,
  status ENUM('pending','ongoing','under_inspection','car_washing','completed','released','returned_for_revision','cancelled') NOT NULL DEFAULT 'pending',
  priority ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  labor_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  parts_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  discount_type ENUM('none','senior_citizen','pwd','promotional','custom') NOT NULL DEFAULT 'none',
  discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  discount_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  partial_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  payment_status ENUM('pending','partial','paid') NOT NULL DEFAULT 'pending',
  payment_method ENUM('cash','card','bank_transfer','gcash','paymaya') DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  estimated_completion DATETIME DEFAULT NULL,
  actual_completion DATETIME DEFAULT NULL,
  status_timer_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  status_timer_started_at DATETIME DEFAULT NULL,
  work_started_at DATETIME DEFAULT NULL,
  inspection_started_at DATETIME DEFAULT NULL,
  completed_at DATETIME DEFAULT NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_job_orders_number (job_order_number),
  KEY idx_job_orders_customer (customer_id),
  KEY idx_job_orders_vehicle (vehicle_id),
  KEY idx_job_orders_adviser (service_adviser_id),
  KEY idx_job_orders_status (status),
  KEY idx_job_orders_payment_status (payment_status),
  KEY idx_job_orders_created_at (created_at),
  CONSTRAINT fk_job_orders_customer
    FOREIGN KEY (customer_id) REFERENCES customers (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_job_orders_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_job_orders_adviser
    FOREIGN KEY (service_adviser_id) REFERENCES staff (id)
    ON DELETE SET NULL,
  CONSTRAINT fk_job_orders_created_by
    FOREIGN KEY (created_by) REFERENCES users (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_order_services (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_order_id INT UNSIGNED NOT NULL,
  service_id INT UNSIGNED DEFAULT NULL,
  bundle_id INT UNSIGNED DEFAULT NULL,
  service_name VARCHAR(100) NOT NULL,
  service_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  labor_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  quantity INT NOT NULL DEFAULT 1,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_job_order_services_jo (job_order_id),
  KEY idx_job_order_services_service (service_id),
  KEY idx_job_order_services_bundle (bundle_id),
  CONSTRAINT fk_job_order_services_jo
    FOREIGN KEY (job_order_id) REFERENCES job_orders (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_job_order_services_service
    FOREIGN KEY (service_id) REFERENCES services (id)
    ON DELETE SET NULL,
  CONSTRAINT fk_job_order_services_bundle
    FOREIGN KEY (bundle_id) REFERENCES service_bundles (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_order_products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED DEFAULT NULL,
  product_name VARCHAR(100) NOT NULL,
  product_type ENUM('engine_oil','oil_filter','parts','fluids','others') NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  quantity INT NOT NULL DEFAULT 1,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_job_order_products_jo (job_order_id),
  KEY idx_job_order_products_product (product_id),
  CONSTRAINT fk_job_order_products_jo
    FOREIGN KEY (job_order_id) REFERENCES job_orders (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_job_order_products_product
    FOREIGN KEY (product_id) REFERENCES products (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_estimates (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  estimate_number VARCHAR(20) NOT NULL,
  vehicle_make VARCHAR(100) DEFAULT NULL,
  vehicle_model VARCHAR(100) DEFAULT NULL,
  vehicle_year VARCHAR(20) DEFAULT NULL,
  vehicle_plate VARCHAR(50) DEFAULT NULL,
  vehicle_color VARCHAR(50) DEFAULT NULL,
  vehicle_mileage VARCHAR(50) DEFAULT NULL,
  services_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  products_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  services_json TEXT NOT NULL,
  products_json TEXT NOT NULL,
  status ENUM('draft','sent','approved','rejected','converted') NOT NULL DEFAULT 'draft',
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_job_estimates_number (estimate_number),
  KEY idx_job_estimates_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_order_technicians (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_order_id INT UNSIGNED NOT NULL,
  technician_id INT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL,
  started_at DATETIME DEFAULT NULL,
  completed_at DATETIME DEFAULT NULL,
  work_duration INT UNSIGNED DEFAULT NULL COMMENT 'minutes',
  status ENUM('assigned','working','completed','on_hold') NOT NULL DEFAULT 'assigned',
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_job_order_technicians_jo (job_order_id),
  KEY idx_job_order_technicians_tech (technician_id),
  KEY idx_job_order_technicians_status (status),
  CONSTRAINT fk_job_order_technicians_jo
    FOREIGN KEY (job_order_id) REFERENCES job_orders (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_job_order_technicians_staff
    FOREIGN KEY (technician_id) REFERENCES staff (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE work_sessions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_order_technician_id INT UNSIGNED NOT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME DEFAULT NULL,
  duration INT UNSIGNED DEFAULT NULL COMMENT 'minutes',
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_work_sessions_assignment (job_order_technician_id),
  CONSTRAINT fk_work_sessions_assignment
    FOREIGN KEY (job_order_technician_id) REFERENCES job_order_technicians (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_order_approvals (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_order_id INT UNSIGNED NOT NULL,
  reviewer_id INT UNSIGNED NOT NULL,
  reviewer_role ENUM('service_adviser','chief_mechanic') NOT NULL,
  status ENUM('approved','needs_revision','rework_required') NOT NULL,
  comments TEXT DEFAULT NULL,
  reviewed_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_job_order_approvals_jo (job_order_id),
  KEY idx_job_order_approvals_reviewer (reviewer_id),
  CONSTRAINT fk_job_order_approvals_jo
    FOREIGN KEY (job_order_id) REFERENCES job_orders (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_job_order_approvals_reviewer
    FOREIGN KEY (reviewer_id) REFERENCES staff (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PAYMENTS, NOTIFICATIONS, AUDIT
-- ============================================================================
CREATE TABLE payments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  job_order_id INT UNSIGNED NOT NULL,
  payment_date DATETIME NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('cash','card','bank_transfer','gcash','paymaya') NOT NULL,
  reference_number VARCHAR(100) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  received_by INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_payments_jo (job_order_id),
  KEY idx_payments_date (payment_date),
  KEY idx_payments_received_by (received_by),
  CONSTRAINT fk_payments_jo
    FOREIGN KEY (job_order_id) REFERENCES job_orders (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED DEFAULT NULL,
  staff_id INT UNSIGNED DEFAULT NULL,
  type ENUM('job_assigned','job_status','payment','low_stock','system','staff_update','account_update') NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  reference_type VARCHAR(50) DEFAULT NULL,
  reference_id INT UNSIGNED DEFAULT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  read_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_notifications_user (user_id),
  KEY idx_notifications_staff (staff_id),
  KEY idx_notifications_type (type),
  KEY idx_notifications_is_read (is_read),
  KEY idx_notifications_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED DEFAULT NULL,
  staff_id INT UNSIGNED DEFAULT NULL,
  action VARCHAR(100) NOT NULL,
  description TEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_activity_logs_user (user_id),
  KEY idx_activity_logs_staff (staff_id),
  KEY idx_activity_logs_action (action),
  KEY idx_activity_logs_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE csrf_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  token VARCHAR(64) NOT NULL,
  user_id INT UNSIGNED DEFAULT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_csrf_tokens_token (token),
  KEY idx_csrf_tokens_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PERMISSIONS & SETTINGS
-- ============================================================================
CREATE TABLE permissions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  permission_name VARCHAR(100) NOT NULL,
  permission_code VARCHAR(50) NOT NULL,
  description TEXT DEFAULT NULL,
  module VARCHAR(50) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permissions_code (permission_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role VARCHAR(50) NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_role_permissions_pair (role, permission_id),
  KEY idx_role_permissions_permission (permission_id),
  CONSTRAINT fk_role_permissions_permission
    FOREIGN KEY (permission_id) REFERENCES permissions (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT DEFAULT NULL,
  setting_type ENUM('string','number','boolean','json') NOT NULL DEFAULT 'string',
  description TEXT DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_system_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
