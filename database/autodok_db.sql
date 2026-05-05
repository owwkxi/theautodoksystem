
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `autodok_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `autodok_db`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_order_number` varchar(20) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `vehicle_year` varchar(4) DEFAULT NULL,
  `vehicle_make` varchar(50) DEFAULT NULL,
  `vehicle_model` varchar(50) DEFAULT NULL,
  `vehicle_color` varchar(30) DEFAULT NULL,
  `vehicle_license` varchar(20) DEFAULT NULL,
  `vehicle_mileage` varchar(20) DEFAULT NULL,
  `service_type` varchar(50) NOT NULL,
  `service_description` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `parts_cost` decimal(10,2) DEFAULT 0.00,
  `discount_type` enum('none','percentage','fixed') DEFAULT 'none',
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','card','online_payment') DEFAULT 'cash',
  `payment_status` enum('pending','paid','partial') NOT NULL DEFAULT 'pending',
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `job_order_number` (`job_order_number`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_job_orders_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) NOT NULL,
  `service_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_code` (`service_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin_owwkxi', '$2y$12$gatZHDc5g1ac9/7H2IIHJOj01ggs23VBcjL0ckwVYP8cmxMxxE80W', 'admin'),
('staff_owwkxi', '$2y$12$6At.Up9ADU1iq2.wqK6avekDMK1b/Dm08oj0dl63H6Q7P2xhJRr1K', 'staff');

INSERT INTO `services` (`service_name`, `service_code`, `description`, `base_price`, `status`) VALUES
('Oil Change', 'SVC-001', 'Complete oil change service with filter replacement', 1500.00, 'active'),
('Brake Service', 'SVC-002', 'Brake pad replacement and inspection', 3500.00, 'active'),
('Tire Rotation', 'SVC-003', 'Four-wheel tire rotation and balance', 800.00, 'active'),
('Engine Diagnostic', 'SVC-004', 'Complete engine diagnostic scan', 2000.00, 'active'),
('Battery Replacement', 'SVC-005', 'Battery testing and replacement', 4500.00, 'active'),
('Air Filter Replacement', 'SVC-006', 'Engine air filter replacement', 500.00, 'active'),
('Wheel Alignment', 'SVC-007', 'Four-wheel alignment service', 2500.00, 'active'),
('Transmission Service', 'SVC-008', 'Transmission fluid change and inspection', 3000.00, 'active');

INSERT INTO `job_orders` (`job_order_number`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `vehicle_license`, `vehicle_mileage`, `service_type`, `service_description`, `subtotal`, `parts_cost`, `discount_type`, `discount_amount`, `total_amount`, `payment_method`, `payment_status`, `status`, `created_by`) VALUES
('JO-2026-0001', 'Roberto Cruz', '09123456789', 'roberto@email.com', '123 Main Street, Manila', '2018', 'Toyota', 'Vios', 'White', 'ABC1234', '45000', 'Brake Service', 'Brake pad replacement and rotor resurfacing', 3500.00, 1500.00, 'percentage', 10.00, 4500.00, 'cash', 'paid', 'completed', 1),
('JO-2026-0002', 'Maria Santos', '09187654321', 'maria@email.com', '456 Rizal Ave, Quezon City', '2020', 'Honda', 'City', 'Silver', 'XYZ5678', '30000', 'Oil Change', 'Oil change and filter replacement', 1500.00, 700.00, 'none', 0.00, 2200.00, 'card', 'paid', 'in_progress', 1),
('JO-2026-0003', 'Carlos Mendoza', '09198765432', 'carlos@email.com', '789 EDSA, Makati', '2019', 'Mitsubishi', 'Mirage', 'Red', 'DEF9012', '52000', 'Engine Diagnostic', 'Engine diagnostic and repair', 2000.00, 3500.00, 'fixed', 500.00, 5000.00, 'cash', 'pending', 'pending', 1);

INSERT INTO `activity_logs` (`user_id`, `action`, `description`, `ip_address`) VALUES
(1, 'login', 'Admin logged in', '127.0.0.1'),
(1, 'create_job_order', 'Created job order JO-2026-0001', '127.0.0.1'),
(1, 'create_job_order', 'Created job order JO-2026-0002', '127.0.0.1'),
(1, 'update_job_order', 'Updated job order JO-2026-0001 status to completed', '127.0.0.1');

COMMIT;


