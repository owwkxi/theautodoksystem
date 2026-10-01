-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 01, 2026 at 12:29 AM
-- Server version: 11.8.9-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u141753080_pautodok`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `staff_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(13, 2, NULL, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 06:42:05'),
(14, 1, NULL, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 12:56:25'),
(15, 0, NULL, 'failed_login', 'Failed login attempt for ID: admin_owwkxi', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 12:56:32'),
(16, 2, NULL, 'login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 12:56:54'),
(17, 2, NULL, 'update_profile', 'Updated own profile details', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 12:57:23'),
(18, 2, NULL, 'update_profile', 'Updated own profile details', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 12:59:14'),
(19, 2, NULL, 'create_service', 'Created service: Oil Filter Replacement', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 12:59:42'),
(20, 2, NULL, 'create_bundle', 'Created bundle: avsdvas', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 12:59:50'),
(21, 2, NULL, 'create_job_order', 'Created job order #JO001', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:00:00'),
(22, 2, NULL, 'create_estimate', 'Created estimate #JE001', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:00:15'),
(23, 2, NULL, 'create_job_order', 'Created job order #JO002', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:00:23'),
(24, 2, NULL, 'delete_estimate', 'Deleted estimate #JE001', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:00:23'),
(25, 2, NULL, 'delete_job_order', 'Deleted job order #JO002', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:00:27'),
(26, 2, NULL, 'delete_job_order', 'Deleted job order #JO001', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:00:29'),
(27, 0, NULL, 'failed_login', 'Failed login attempt for ID: 00000', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:02:50'),
(28, 2, NULL, 'login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:02:58'),
(29, 2, NULL, 'create_staff', 'Created staff: Dj Cortez', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:05:00'),
(30, 2, NULL, 'update_staff', 'Updated staff: Dj Cortez', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:09:01'),
(31, 2, NULL, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:09:11'),
(32, 1, NULL, 'login', 'Staff (service_adviser) logged in successfully', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:09:24'),
(33, 0, NULL, 'failed_login', 'Failed login attempt for ID: 00000', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:09:40'),
(34, 0, NULL, 'failed_login', 'Failed login attempt for ID: 00000', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:09:46'),
(35, 0, NULL, 'failed_login', 'Failed login attempt for ID: 00000', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:09:53'),
(36, 0, NULL, 'failed_login', 'Failed login attempt for ID: 00000', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:10:03'),
(37, 0, NULL, 'failed_login', 'Failed login attempt for ID: 00000', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:10:14'),
(38, 2, NULL, 'login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:10:22'),
(39, 2, NULL, 'create_job_order', 'Created job order #JO001', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:10:32'),
(40, 1, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Pending → Completed', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:10:43'),
(41, 1, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Completed → Released', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:10:50'),
(42, 1, NULL, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:10:58'),
(43, 2, NULL, 'login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:11:07'),
(44, 2, NULL, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:11:09'),
(45, 2, NULL, 'update_profile', 'Updated own profile details', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:18:03'),
(46, 2, NULL, 'update_staff', 'Updated staff: Dj Cortez', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:19:59'),
(47, 2, NULL, 'create_staff', 'Created staff: 3252435', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:20:33'),
(48, 2, NULL, 'update_staff_status', 'Updated staff status: 3252435 ', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:22:03'),
(49, 2, NULL, 'update_staff_status', 'Updated staff status: 3252435 ', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:22:07'),
(50, 2, NULL, 'create_service', 'Created service: 23', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:31:04'),
(51, 2, NULL, 'update_profile', 'Updated own profile details', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:47:11'),
(52, 2, NULL, 'delete_staff', 'Deleted staff: Dj Cortez', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:47:23'),
(53, 2, NULL, 'update_staff', 'Updated staff: 3252435', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:47:41'),
(54, 2, NULL, 'update_staff', 'Updated staff: 3252435', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:50:43'),
(55, 2, NULL, 'update_staff', 'Updated staff: 3252435', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:50:56'),
(56, 2, NULL, 'update_staff', 'Updated staff: 3252435', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:54:42'),
(57, 2, NULL, 'update_staff', 'Updated staff: 3252435', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:54:50'),
(58, 2, NULL, 'delete_staff', 'Deleted staff: 3252435 ', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 13:54:53'),
(59, 2, NULL, 'delete_job_order', 'Deleted job order #JO001', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:04:47'),
(60, 2, NULL, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:22:25'),
(61, 2, NULL, 'login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:23:47'),
(62, 2, NULL, 'update_profile', 'Updated own profile details', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:24:24'),
(63, 2, NULL, 'update_profile', 'Updated own profile details', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:26:49'),
(64, 2, NULL, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:28:49'),
(65, 2, NULL, 'login', 'Admin logged in successfully', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:28:58'),
(66, 2, NULL, 'create_staff', 'Created staff: service adviser', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:30:12'),
(67, 3, NULL, 'login', 'Staff (service_adviser) logged in successfully', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:30:50'),
(68, 2, NULL, 'create_job_order', 'Created job order #JO001', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:31:07'),
(69, 2, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Pending → Car Washing', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:31:14'),
(70, 2, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Car Washing → Completed', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:31:15'),
(71, 3, NULL, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:31:28'),
(72, 2, NULL, 'delete_job_order', 'Deleted job order #JO001', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:32:15'),
(73, 2, NULL, 'delete_staff', 'Deleted staff: service adviser', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 14:41:25'),
(74, 2, NULL, 'login', 'Admin logged in successfully', '138.84.112.52', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2026-08-09 17:02:28'),
(75, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:98bf:203f:7b38:f737', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 17:09:02'),
(76, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:98bf:203f:7b38:f737', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 17:13:38'),
(77, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:98bf:203f:7b38:f737', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 17:13:47'),
(78, 2, NULL, 'update_system_logo', 'Updated system logo settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:98bf:203f:7b38:f737', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 17:19:40'),
(79, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:98bf:203f:7b38:f737', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 17:19:55'),
(80, 2, NULL, 'update_system_logo', 'Updated system logo settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:98bf:203f:7b38:f737', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 17:26:04'),
(81, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:98bf:203f:7b38:f737', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 17:26:14'),
(82, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:98bf:203f:7b38:f737', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 17:26:27'),
(83, 2, NULL, 'update_system_logo', 'Updated system logo settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:98bf:203f:7b38:f737', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 17:26:58'),
(84, 2, NULL, 'login', 'Admin logged in successfully', '138.84.112.52', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2026-08-09 17:29:33'),
(85, 2, NULL, 'logout', 'User logged out', '138.84.112.52', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2026-08-09 17:31:22'),
(86, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:d8c5:ebcd:8c40:d9c6', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2026-08-09 17:38:17'),
(87, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-10 10:01:36'),
(88, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-10 10:02:07'),
(89, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 11:12:49'),
(90, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 11:13:11'),
(91, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 11:56:48'),
(92, 2, NULL, 'create_staff', 'Created staff: Danilo Guingue Cortez Jr.', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 11:59:36'),
(93, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 12:04:06'),
(94, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 00:18:56'),
(95, 2, NULL, 'create_service', 'Created service: Oil Filter Replacement', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 00:19:31'),
(96, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 00:21:01'),
(97, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 00:23:28'),
(98, 2, NULL, 'update_system_logo', 'Updated system logo settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 00:24:00'),
(99, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 00:24:24'),
(100, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 10:24:12'),
(101, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 10:25:28'),
(102, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 10:25:34'),
(103, 2, NULL, 'update_system_logo', 'Updated system logo settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 10:25:59'),
(104, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 10:26:29'),
(105, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:18:15'),
(106, 2, NULL, 'create_staff', 'Created staff: Erin Patricia Martinez', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:19:51'),
(107, 2, NULL, 'create_staff', 'Created staff: Lovely Joyce Gambong', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:22:26'),
(108, 2, NULL, 'create_staff', 'Created staff: Iloisa Joy P. Mejias', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:24:05'),
(109, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:27:26'),
(110, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:28:16'),
(111, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:28:35'),
(112, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:28:51'),
(113, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:c554:e74f:284c:6ded', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:28:54'),
(114, 0, NULL, 'failed_login', 'Failed login attempt for staff ID: 53468', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:31:20'),
(115, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:31:33'),
(116, 6, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:33:57'),
(117, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c826:c200:55e6:6b7d:1715:8db9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:42:55'),
(118, 6, NULL, 'create_staff', 'Created staff: Aian P. Alderite', '2001:fd8:c826:c200:55e6:6b7d:1715:8db9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:46:14'),
(119, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:a15d:b17e:cd1d:1f03', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:51:11'),
(120, 6, NULL, 'create_staff', 'Created staff: Nexander M.Gayan', '2001:fd8:c826:c200:55e6:6b7d:1715:8db9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:52:42'),
(121, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:a15d:b17e:cd1d:1f03', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:53:34'),
(122, 6, NULL, 'create_staff', 'Created staff: Kineth Pandian', '2001:fd8:c826:c200:55e6:6b7d:1715:8db9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 11:58:18'),
(123, 6, NULL, 'create_staff', 'Created staff: Jerald  E. Changco', '2001:fd8:c826:c200:55e6:6b7d:1715:8db9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 12:04:23'),
(124, 6, NULL, 'create_staff', 'Created staff: John Paul Villamente', '2001:fd8:c826:c200:55e6:6b7d:1715:8db9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 12:07:03'),
(125, 6, NULL, 'create_staff', 'Created staff: Legario Mosaso', '2001:fd8:c826:c200:55e6:6b7d:1715:8db9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 12:26:30'),
(126, 6, NULL, 'create_staff', 'Created staff: Jan Carlo Padios', '2001:fd8:c826:c200:55e6:6b7d:1715:8db9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 12:34:01'),
(127, 6, NULL, 'create_staff', 'Created staff: Artemio Baquirel Jr.', '2001:fd8:c826:c200:55e6:6b7d:1715:8db9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 12:42:50'),
(128, 5, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:19:34'),
(129, 5, NULL, 'create_service', 'Created service: CHANGE OIL (LABOR)', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:20:29'),
(130, 5, NULL, 'create_service', 'Created service: HEAVY PMS (LABOR)', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:21:50'),
(131, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:a15d:b17e:cd1d:1f03', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:22:23'),
(132, 2, NULL, 'create_staff', 'Created staff: Gracesilyn Pelvira Chen', '2001:fd8:c826:c200:a15d:b17e:cd1d:1f03', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:23:16'),
(133, 5, NULL, 'create_service', 'Created service: REGULAR PMS (LABOR)', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:23:28'),
(134, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:a15d:b17e:cd1d:1f03', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:24:17'),
(135, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:a15d:b17e:cd1d:1f03', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:25:50'),
(136, 5, NULL, 'update_service', 'Updated service: REGULAR PMS (LABOR) (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:26:06'),
(137, 5, NULL, 'update_service', 'Updated service: REGULAR PMS (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:27:19'),
(138, 5, NULL, 'update_service', 'Updated service: HEAVY PMS (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:27:28'),
(139, 5, NULL, 'update_service', 'Updated service: CHANGE OIL (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:e9a6:9993:1d4:3f41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:27:36'),
(140, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:a15d:b17e:cd1d:1f03', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-12 13:28:05'),
(141, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:34:46'),
(142, 6, NULL, 'logout', 'User logged out', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:35:46'),
(143, 10, NULL, 'login', 'Staff (technician) logged in successfully', '110.54.205.202', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', '2026-08-13 01:40:37'),
(144, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:41:20'),
(145, 6, NULL, 'create_job_order', 'Created job order #JO001', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:45:49'),
(146, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Pending → Ongoing', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:46:57'),
(147, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO001 (elapsed: 16s)', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:47:13'),
(148, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO001 (elapsed: 16s)', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:47:28'),
(149, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO001 (elapsed: 18s)', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:47:30'),
(150, 6, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 18s)', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:47:32'),
(151, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO001 (elapsed: 34s)', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:47:48'),
(152, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO001 (elapsed: 34s)', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:48:06'),
(153, 10, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 48s)', '110.54.205.202', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', '2026-08-13 01:48:20'),
(154, 6, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 53s)', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:48:25'),
(155, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Under Inspection → Car Washing', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:48:33'),
(156, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Car Washing → Completed', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:48:36'),
(157, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Completed → Released', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:48:41'),
(158, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Released → Returned For Revision', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:48:56'),
(159, 10, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 74s)', '110.54.205.202', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', '2026-08-13 01:49:09'),
(160, 6, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 82s)', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:49:17'),
(161, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Under Inspection → Completed', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:49:22'),
(162, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Completed → Released', '110.54.205.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 01:49:27'),
(163, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:29d4:2001:499d:653a:9954:b22c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 03:49:55'),
(164, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Released → Returned For Revision', '2001:fd8:29d4:2001:499d:653a:9954:b22c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 03:51:27'),
(165, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO001 (elapsed: 91s)', '2001:fd8:29d4:2001:499d:653a:9954:b22c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 03:51:31'),
(166, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:29d4:2001:499d:653a:9954:b22c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 04:32:22'),
(167, 6, NULL, 'update_job_order', 'Updated job order #JO001: Notes: sample → sample for technician; Services/Bundles updated; Products updated', '2001:fd8:29d4:2001:499d:653a:9954:b22c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 04:33:46'),
(168, 10, NULL, 'login', 'Staff (technician) logged in successfully', '2001:fd8:29d4:2001:18cb:40ab:2293:ec2e', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36', '2026-08-13 04:34:18'),
(169, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO001 (elapsed: 91s)', '2001:fd8:29d4:2001:499d:653a:9954:b22c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 04:34:32'),
(170, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Returned For Revision → Ongoing', '2001:fd8:29d4:2001:499d:653a:9954:b22c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 04:34:35'),
(171, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO001 (elapsed: 109s)', '2001:fd8:29d4:2001:499d:653a:9954:b22c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 04:34:50'),
(172, 6, NULL, 'update_job_order', 'Updated job order #JO001: Notes: sample for technician → sample for technician notes: pls ko check sa blabla; Services/Bundles updated; Products updated', '2001:fd8:29d4:2001:499d:653a:9954:b22c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 04:36:47'),
(173, 12, NULL, 'login', 'Staff (technician) logged in successfully', '2001:fd8:29d4:2001:df04:328c:f156:63b9', 'Mozilla/5.0 (Linux; Android 14; TECNO KL4 Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/523.0.0.10.106;FBCX/modulariab;]', '2026-08-13 04:38:04'),
(174, 0, NULL, 'failed_login', 'Failed login attempt for staff ID: 12086', '2405:8d40:4102:91f1:18cb:378c:57e7:2fed', 'Mozilla/5.0 (Linux; Android 10; M2006C3MG Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/522.0.0.5.107;FBCX/modulariab;]', '2026-08-13 04:38:24'),
(175, 0, NULL, 'failed_login', 'Failed login attempt for staff ID: 12086', '2405:8d40:4102:91f1:18cb:378c:57e7:2fed', 'Mozilla/5.0 (Linux; Android 10; M2006C3MG Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/522.0.0.5.107;FBCX/modulariab;]', '2026-08-13 04:39:10'),
(176, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '64.226.60.132', 'Mozilla/5.0 (Linux; Android 8.1.0; INE-LX2 Build/HUAWEIINE-LX2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/105.0.5195.77 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/573.0.0.44.88;]', '2026-08-13 04:39:15'),
(177, 10, NULL, 'login', 'Staff (technician) logged in successfully', '2405:8d40:4102:91f1:18cb:378c:57e7:2fed', 'Mozilla/5.0 (Linux; Android 10; M2006C3MG Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/522.0.0.5.107;FBCX/modulariab;]', '2026-08-13 04:39:49'),
(178, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '64.226.60.132', 'Mozilla/5.0 (Linux; Android 8.1.0; INE-LX2 Build/HUAWEIINE-LX2; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/105.0.5195.77 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/573.0.0.44.88;]', '2026-08-13 04:40:05'),
(179, 11, NULL, 'login', 'Staff (technician) logged in successfully', '2405:8d40:4113:2df5:44b0:45c2:2543:32d', 'Mozilla/5.0 (Linux; Android 9; SM-J610G Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/138.0.7204.180 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/573.0.0.44.88;]', '2026-08-13 04:40:43'),
(180, 12, NULL, 'login', 'Staff (technician) logged in successfully', '2001:fd8:29d4:2001:df04:328c:f156:63b9', 'Mozilla/5.0 (Linux; Android 14; TECNO KL4 Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/523.0.0.10.106;FBCX/modulariab;]', '2026-08-13 04:41:29'),
(181, 12, NULL, 'login', 'Staff (technician) logged in successfully', '2001:fd8:29d4:2001:df04:328c:f156:63b9', 'Mozilla/5.0 (Linux; Android 14; TECNO KL4 Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/523.0.0.10.106;FBCX/modulariab;]', '2026-08-13 04:42:21'),
(182, 0, NULL, 'failed_login', 'Failed login attempt for staff ID: 97893', '175.158.238.92', 'Mozilla/5.0 (Linux; Android 13; V2254 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.178 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/565.0.0.43.88;]', '2026-08-13 04:42:44'),
(183, 14, NULL, 'login', 'Staff (technician) logged in successfully', '2405:8d40:4113:2df5:d880:46ff:fee3:93a2', 'Mozilla/5.0 (Linux; Android 14; RMX3938 Build/UP1A.231005.007; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/151.0.7922.83 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/522.0.0.5.107;FBCX/modulariab;]', '2026-08-13 04:43:04'),
(184, 0, NULL, 'failed_login', 'Failed login attempt for staff ID: 97893', '175.158.238.92', 'Mozilla/5.0 (Linux; Android 13; V2254 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.178 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/565.0.0.43.88;]', '2026-08-13 04:44:27'),
(185, 0, NULL, 'failed_login', 'Failed login attempt for staff ID: 97893', '175.158.238.92', 'Mozilla/5.0 (Linux; Android 13; V2254 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.178 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/565.0.0.43.88;]', '2026-08-13 04:45:24'),
(186, 13, NULL, 'login', 'Staff (technician) logged in successfully', '175.158.238.92', 'Mozilla/5.0 (Linux; Android 13; V2254 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/148.0.7778.178 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/565.0.0.43.88;]', '2026-08-13 04:45:52'),
(187, 11, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 109s)', '2405:8d40:4113:2df5:44b0:45c2:2543:32d', 'Mozilla/5.0 (Linux; Android 9; SM-J610G Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/138.0.7204.180 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/573.0.0.44.88;]', '2026-08-13 04:45:56'),
(188, 11, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 118s)', '2405:8d40:4113:2df5:44b0:45c2:2543:32d', 'Mozilla/5.0 (Linux; Android 9; SM-J610G Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/138.0.7204.180 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/573.0.0.44.88;]', '2026-08-13 04:46:05'),
(189, 11, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 123s)', '2405:8d40:4113:2df5:44b0:45c2:2543:32d', 'Mozilla/5.0 (Linux; Android 9; SM-J610G Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/138.0.7204.180 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/573.0.0.44.88;]', '2026-08-13 04:46:10'),
(190, 10, NULL, 'login', 'Staff (technician) logged in successfully', '2405:8d40:4102:91f1:18cb:378c:57e7:2fed', 'Mozilla/5.0 (Linux; Android 10; M2006C3MG Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/522.0.0.5.107;FBCX/modulariab;]', '2026-08-13 04:48:26'),
(191, 10, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 272s)', '2405:8d40:4102:91f1:18cb:378c:57e7:2fed', 'Mozilla/5.0 (Linux; Android 10; M2006C3MG Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/522.0.0.5.107;FBCX/modulariab;]', '2026-08-13 04:48:39'),
(192, 10, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 281s)', '2405:8d40:4102:91f1:18cb:378c:57e7:2fed', 'Mozilla/5.0 (Linux; Android 10; M2006C3MG Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/522.0.0.5.107;FBCX/modulariab;]', '2026-08-13 04:48:48'),
(193, 10, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 285s)', '2405:8d40:4102:91f1:18cb:378c:57e7:2fed', 'Mozilla/5.0 (Linux; Android 10; M2006C3MG Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/522.0.0.5.107;FBCX/modulariab;]', '2026-08-13 04:48:52'),
(194, 10, NULL, 'update_job_order_timer', 'Done timer for job order #JO001 (elapsed: 286s)', '2405:8d40:4102:91f1:18cb:378c:57e7:2fed', 'Mozilla/5.0 (Linux; Android 10; M2006C3MG Build/QP1A.190711.020; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/150.0.7871.181 Mobile Safari/537.36[FBAN/EMA;FBLC/en_US;FBAV/522.0.0.5.107;FBCX/modulariab;]', '2026-08-13 04:48:53'),
(195, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Under Inspection → Released', '2001:fd8:29d4:2001:499d:653a:9954:b22c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 04:50:23'),
(196, 5, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:24:32'),
(197, 5, NULL, 'update_service', 'Updated service: FLUSHING BRAKE FLUID (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:26:20'),
(198, 5, NULL, 'create_service', 'Created service: CHARGE FREON', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:27:04'),
(199, 5, NULL, 'create_service', 'Created service: RADIATOR CLEANING', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:27:27'),
(200, 5, NULL, 'create_service', 'Created service: REPLACE DRIVE BELT', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:28:04'),
(201, 5, NULL, 'create_service', 'Created service: REPLACE DRIVE BELT (FORD)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:28:35'),
(202, 5, NULL, 'update_service', 'Updated service: REPLACE DRIVE BELT (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:28:49'),
(203, 5, NULL, 'create_service', 'Created service: THROTTLE BODY CLEANING', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:29:13'),
(204, 5, NULL, 'create_service', 'Created service: REPLACCE AUXILIARY FAN MOTOR', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:29:50'),
(205, 5, NULL, 'update_service', 'Updated service: REPLACE AUXILIARY FAN MOTOR (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:30:10'),
(206, 5, NULL, 'create_service', 'Created service: PULL OUT/INSTALL FRT. LOWER SUSPENSION ASSY', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:30:32'),
(207, 5, NULL, 'update_service', 'Updated service: PULL OUT/INSTALL FRT. LOWER SUSPENSION ASSY RH/LH (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 06:30:50'),
(208, 5, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 07:53:56'),
(209, 5, NULL, 'create_service', 'Created service: FUEL INJECTOR CLEANING (LABOR)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 07:56:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `staff_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(210, 5, NULL, 'create_service', 'Created service: WHEEL ALIGNMENT - TOE IN/TOE OUT', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:46:49'),
(211, 5, NULL, 'create_service', 'Created service: WHEEL ALIGNMENT - COMPLETE', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:47:04'),
(212, 5, NULL, 'create_service', 'Created service: STEERING RACK REPAIR - PULL OUT/INSTALL', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:49:31'),
(213, 5, NULL, 'add_product', 'Added product: GTX AIR FILTER [PRD01]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:52:32'),
(214, 5, NULL, 'add_product', 'Added product: RELAY [PRD02]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:53:18'),
(215, 5, NULL, 'add_product', 'Added product: AIR FILTER (HILUX, FORTUNER) [PRD03]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:53:50'),
(216, 5, NULL, 'add_product', 'Added product: AIR FILTER (MULTI-VEHICLE) [PRD04]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:54:29'),
(217, 5, NULL, 'add_product', 'Added product: AIR FILTER (NAVARA) [PRD05]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:55:03'),
(218, 5, NULL, 'add_product', 'Added product: ATF J4 (MIRAGE) [PRD06]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:56:04'),
(219, 5, NULL, 'add_product', 'Added product: ATF LV D111/ STEERING FLUID [PRD07]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:56:47'),
(220, 5, NULL, 'add_product', 'Added product: ATF LV MV (TOYOTA) [PRD08]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:57:11'),
(221, 5, NULL, 'add_product', 'Added product: ATF MAXLIFE DEX [PRD09]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:57:31'),
(222, 5, NULL, 'add_product', 'Added product: 950 [PRD10]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:57:52'),
(223, 5, NULL, 'edit_product', 'Updated product: ATF PETRON (HTP) (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:58:38'),
(224, 5, NULL, 'stock_in', 'Stock in: ATF LV D111/ STEERING FLUID (+1)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:59:04'),
(225, 5, NULL, 'stock_in', 'Stock in: ATF LV D111/ STEERING FLUID (+19)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:59:13'),
(226, 5, NULL, 'stock_in', 'Stock in: ATF LV MV (TOYOTA) (+20)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:59:23'),
(227, 5, NULL, 'stock_in', 'Stock in: ATF MAXLIFE DEX (+20)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:59:32'),
(228, 5, NULL, 'stock_in', 'Stock in: ATF PETRON (HTP) (+20)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 08:59:40'),
(229, 5, NULL, 'add_product', 'Added product: ATF PREMIUM SAE-20 [PRD11]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:00:17'),
(230, 5, NULL, 'stock_in', 'Stock in: ATF PREMIUM SAE-20 (+20)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:01:17'),
(231, 5, NULL, 'edit_product', 'Updated product: AIR FILTER (TRANSFORMER) (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:04:23'),
(232, 5, NULL, 'edit_product', 'Updated product: ENGINE OIL 5W-30 (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:05:32'),
(233, 5, NULL, 'add_product', 'Added product: ENGINE OIL 5W-40 [PRD12]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:06:02'),
(234, 5, NULL, 'stock_in', 'Stock in: ENGINE OIL 5W-40 (+20)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:06:23'),
(235, 5, NULL, 'edit_product', 'Updated product: PETRON ATF SAE-20 (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:07:42'),
(236, 5, NULL, 'edit_product', 'Updated product: ATF LV MV (STOCKS) (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:08:13'),
(237, 5, NULL, 'edit_product', 'Updated product: OIL FILTER 415 (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:08:57'),
(238, 5, NULL, 'edit_product', 'Updated product: OIL FITER 110 (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:09:21'),
(239, 5, NULL, 'edit_product', 'Updated product: OIL FILTER 111 (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:09:59'),
(240, 5, NULL, 'edit_product', 'Updated product: BRAKE CLEANER (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:10:46'),
(241, 5, NULL, 'edit_product', 'Updated product: FRONT HUB BEARING (MIRAGE) (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:11:13'),
(242, 5, NULL, 'add_product', 'Added product: REAR HUB BEARING (MIRAGE [PRD13]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:11:27'),
(243, 5, NULL, 'edit_product', 'Updated product: FRONT HUB BEARING (MIRAGE) (status ACTIVE -> ACTIVE)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:11:43'),
(244, 5, NULL, 'stock_in', 'Stock in: REAR HUB BEARING (MIRAGE (+10)', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:11:55'),
(245, 5, NULL, 'add_product', 'Added product: PENETRATING [PRD14]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:12:26'),
(246, 5, NULL, 'add_product', 'Added product: CARB CLEANER [PRD15]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:12:35'),
(247, 5, NULL, 'add_product', 'Added product: GEAR OIL [PRD16]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:12:42'),
(248, 5, NULL, 'add_product', 'Added product: GREASE [PRD17]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:12:52'),
(249, 5, NULL, 'add_product', 'Added product: COOLANT BLUE [PRD18]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:13:00'),
(250, 5, NULL, 'add_product', 'Added product: COOLANT GREEN [PRD19]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:13:06'),
(251, 5, NULL, 'add_product', 'Added product: BATTERY [PRD20]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:13:15'),
(252, 5, NULL, 'add_product', 'Added product: BRAKE PADS (MIRAGE) [PRD21]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:13:22'),
(253, 5, NULL, 'add_product', 'Added product: STAB. LINK (TRANSFORMER) [PRD22]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:13:28'),
(254, 5, NULL, 'add_product', 'Added product: STAB. CLAMP (TRANSFORMER) [PRD23]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:13:35'),
(255, 5, NULL, 'add_product', 'Added product: VALVE COVER GASKET (TRANSFORMER) [PRD24]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:13:45'),
(256, 5, NULL, 'add_product', 'Added product: OIL FILTER (GEELY COOLRAY) [PRD25]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:14:00'),
(257, 5, NULL, 'add_product', 'Added product: FLUSHING [PRD26]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:14:08'),
(258, 5, NULL, 'add_product', 'Added product: BRAKE FLUID DOT-3 [PRD27]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:16:56'),
(259, 5, NULL, 'add_product', 'Added product: ROBERLO SILTEX 8000 [PRD28]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:17:03'),
(260, 5, NULL, 'add_product', 'Added product: CABIN FILTER (87139-0N010) [PRD29]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:17:10'),
(261, 5, NULL, 'add_product', 'Added product: WIRE [PRD30]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:17:17'),
(262, 5, NULL, 'add_product', 'Added product: STAB. CLAMP (TRANSFORMER) [PRD31]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:17:32'),
(263, 5, NULL, 'add_product', 'Added product: BRAKE PADS (MIRAGE) [PRD32]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:17:40'),
(264, 5, NULL, 'add_product', 'Added product: OIL FILTER-NAVARA 231 [PRD33]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:17:58'),
(265, 5, NULL, 'add_product', 'Added product: GEAR OIL -PETRON NEXUS [PRD34]', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:18:07'),
(266, 5, NULL, 'update_profile', 'Updated own profile details', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:23:39'),
(267, 5, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:2953:459:c8e6:5ca9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:42:37'),
(268, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:1c25:f47:c974:ec27', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:57:56'),
(269, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:1c25:f47:c974:ec27', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 09:58:34'),
(270, 2, NULL, 'login', 'Admin logged in successfully', '138.84.112.52', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2026-08-14 13:17:33'),
(271, 2, NULL, 'delete_job_order', 'Deleted job order #JO001', '138.84.112.52', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2026-08-14 13:17:52'),
(272, 2, NULL, 'logout', 'User logged out', '138.84.112.52', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2026-08-14 13:21:01'),
(273, 0, NULL, 'failed_login', 'Failed login attempt for ID: 43700', '2001:fd8:28bb:a558:8523:4c3a:3cbe:ce40', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 08:38:51'),
(274, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:28bb:a558:8523:4c3a:3cbe:ce40', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 08:40:07'),
(275, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:13:38'),
(276, 6, NULL, 'update_service', 'Updated service: REGULAR PMS (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:15:42'),
(277, 6, NULL, 'update_service', 'Updated service: REGULAR PMS (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:17:26'),
(278, 6, NULL, 'update_service', 'Updated service: REGULAR PMS (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:22:27'),
(279, 6, NULL, 'update_service', 'Updated service: HEAVY PMS (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:26:21'),
(280, 6, NULL, 'update_service', 'Updated service: HEAVY PMS (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:35:21'),
(281, 6, NULL, 'create_service', 'Created service: LIGHT PMS', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:37:34'),
(282, 6, NULL, 'edit_product', 'Updated product: ENGINE OIL 5W-30 (status ACTIVE -> INACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:41:37'),
(283, 6, NULL, 'edit_product', 'Updated product: ENGINE OIL 5W-30 (status INACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:41:50'),
(284, 6, NULL, 'stock_out', 'Stock out: ENGINE OIL 5W-30 (-20)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:43:33'),
(285, 6, NULL, 'stock_out', 'Stock out: ENGINE OIL 5W-40 (-11)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:43:57'),
(286, 6, NULL, 'stock_out', 'Stock out: OIL FILTER 415 (-10)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:44:22'),
(287, 6, NULL, 'stock_out', 'Stock out: OIL FITER 110 (-10)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:44:49'),
(288, 6, NULL, 'stock_out', 'Stock out: BRAKE CLEANER (-17)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:45:34'),
(289, 6, NULL, 'stock_in', 'Stock in: COOLANT GREEN (+4)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:46:42'),
(290, 6, NULL, 'stock_out', 'Stock out: ATF LV MV (STOCKS) (-19)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:47:19'),
(291, 6, NULL, 'add_product', 'Added product: ATF SAE-20 [PRD35]', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:49:49'),
(292, 6, NULL, 'edit_product', 'Updated product: ATF LV MV (STOCKS) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:50:06'),
(293, 6, NULL, 'edit_product', 'Updated product: ATF LV MV (STOCKS) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:50:28'),
(294, 6, NULL, 'edit_product', 'Updated product: ATF SAE-20 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:50:36'),
(295, 6, NULL, 'edit_product', 'Updated product: BRAKE CLEANER (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:50:46'),
(296, 6, NULL, 'edit_product', 'Updated product: BRAKE FLUID DOT-3 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:51:27'),
(297, 6, NULL, 'stock_in', 'Stock in: BRAKE FLUID DOT-3 (+9)', '2001:fd8:28bb:a558:502:84c9:d8e:64b0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 01:51:40'),
(298, 6, NULL, 'edit_product', 'Updated product: AIR FILTER (MULTI-VEHICLE) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:06:09'),
(299, 6, NULL, 'stock_out', 'Stock out: AIR FILTER (MULTI-VEHICLE) (-20)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:06:18'),
(300, 6, NULL, 'edit_product', 'Updated product: AIR FILTER (TRANSFORMER) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:07:30'),
(301, 6, NULL, 'stock_out', 'Stock out: AIR FILTER (TRANSFORMER) (-20)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:07:39'),
(302, 6, NULL, 'edit_product', 'Updated product: ATF LV MV (STOCKS) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:08:17'),
(303, 6, NULL, 'edit_product', 'Updated product: ATF SAE-20 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:08:35'),
(304, 6, NULL, 'edit_product', 'Updated product: BATTERY (IMARFLEX) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:12:52'),
(305, 6, NULL, 'edit_product', 'Updated product: BRAKE CLEANER (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:14:57'),
(306, 6, NULL, 'edit_product', 'Updated product: BRAKE FLUID DOT-3 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:15:46'),
(307, 6, NULL, 'stock_out', 'Stock out: BRAKE FLUID DOT-3 (-1)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:15:57'),
(308, 6, NULL, 'edit_product', 'Updated product: BRAKE CLEANER (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:16:04'),
(309, 6, NULL, 'edit_product', 'Updated product: BRAKE PADS (MIRAGE) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:17:43'),
(310, 6, NULL, 'edit_product', 'Updated product: BRAKE PADS (TRANSORMER) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:18:11'),
(311, 6, NULL, 'edit_product', 'Updated product: CABIN FILTER (87139-0N010) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:19:12'),
(312, 6, NULL, 'edit_product', 'Updated product: THROTTLE/CARB CLEANER (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:31:36'),
(313, 6, NULL, 'edit_product', 'Updated product: COOLANT BLUE (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:33:38'),
(314, 6, NULL, 'edit_product', 'Updated product: COOLANT GREEN (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:34:06'),
(315, 6, NULL, 'edit_product', 'Updated product: COOLANT GREEN (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:34:25'),
(316, 6, NULL, 'edit_product', 'Updated product: COOLANT BLUE (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:34:38'),
(317, 6, NULL, 'edit_product', 'Updated product: ENGINE OIL 5W-30 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:35:04'),
(318, 6, NULL, 'edit_product', 'Updated product: ENGINE OIL 5W-40 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:35:19'),
(319, 6, NULL, 'edit_product', 'Updated product: FLUSHING (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:37:21'),
(320, 6, NULL, 'stock_in', 'Stock in: FRONT HUB BEARING (MIRAGE) (+1)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:37:58'),
(321, 6, NULL, 'stock_out', 'Stock out: FRONT HUB BEARING (MIRAGE) (-20)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:38:07'),
(322, 6, NULL, 'edit_product', 'Updated product: FRONT HUB BEARING (MIRAGE) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:38:23'),
(323, 6, NULL, 'edit_product', 'Updated product: ATF LV MV (STOCKS) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:39:01'),
(324, 6, NULL, 'edit_product', 'Updated product: ATF SAE-20 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:39:13'),
(325, 6, NULL, 'edit_product', 'Updated product: GEAR OIL -PETRON NEXUS (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:41:06'),
(326, 6, NULL, 'stock_in', 'Stock in: GEAR OIL -PETRON NEXUS (+1)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:41:13'),
(327, 6, NULL, 'edit_product', 'Updated product: GEAR OIL (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:41:35'),
(328, 6, NULL, 'edit_product', 'Updated product: OIL FILTER 111 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:43:47'),
(329, 6, NULL, 'stock_out', 'Stock out: OIL FILTER 111 (-18)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:44:03'),
(330, 6, NULL, 'stock_in', 'Stock in: OIL FILTER 111 (+5)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:44:32'),
(331, 6, NULL, 'edit_product', 'Updated product: OIL FILTER 415 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:45:17'),
(332, 6, NULL, 'edit_product', 'Updated product: OIL FILTER 111 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:45:29'),
(333, 6, NULL, 'stock_in', 'Stock in: OIL FILTER-NAVARA 231 (+1)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:46:39'),
(334, 6, NULL, 'edit_product', 'Updated product: OIL FILTER-NAVARA 231 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:47:43'),
(335, 6, NULL, 'edit_product', 'Updated product: OIL FITER 110 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:48:59'),
(336, 6, NULL, 'stock_in', 'Stock in: PENETRATING (+3)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:49:21'),
(337, 6, NULL, 'edit_product', 'Updated product: PENETRATING (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:51:01'),
(338, 6, NULL, 'edit_product', 'Updated product: PETRON ATF SAE-20 (status ACTIVE -> INACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:51:23'),
(339, 6, NULL, 'edit_product', 'Updated product: REAR HUB BEARING (MIRAGE) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:52:23'),
(340, 6, NULL, 'stock_out', 'Stock out: REAR HUB BEARING (MIRAGE) (-8)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:52:34'),
(341, 6, NULL, 'stock_in', 'Stock in: ROBERLO SILTEX 8000 (+4)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:54:22'),
(342, 6, NULL, 'edit_product', 'Updated product: ROBERLO SILTEX 8000 (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:55:15'),
(343, 6, NULL, 'stock_in', 'Stock in: STAB. CLAMP (TRANSFORMER) (+1)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:55:44'),
(344, 6, NULL, 'edit_product', 'Updated product: STAB. LINK (TRANSFORMER) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:56:30'),
(345, 6, NULL, 'stock_in', 'Stock in: STAB. LINK (TRANSFORMER) (+3)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:56:57'),
(346, 6, NULL, 'edit_product', 'Updated product: STAB. CLAMP (TRANSFORMER) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:57:12'),
(347, 6, NULL, 'edit_product', 'Updated product: STAB. LINK (TRANSFORMER) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:57:24'),
(348, 6, NULL, 'edit_product', 'Updated product: STAB. LINK (TRANSFORMER) (status ACTIVE -> INACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:57:45'),
(349, 6, NULL, 'stock_in', 'Stock in: THROTTLE/CARB CLEANER (+3)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:58:20'),
(350, 6, NULL, 'edit_product', 'Updated product: VALVE COVER GASKET (TRANSFORMER) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:59:22'),
(351, 6, NULL, 'edit_product', 'Updated product: WIRE (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 02:59:58'),
(352, 6, NULL, 'create_service', 'Created service: AIRCON CLEANING (SINGLE EVAPORATOR)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:18:12'),
(353, 6, NULL, 'update_service', 'Updated service: AIRCON CLEANING (SINGLE EVAPORATOR) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:18:38'),
(354, 6, NULL, 'create_service', 'Created service: AIRCON CLEANING (DUAL EVAPORATOR)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:19:12'),
(355, 6, NULL, 'update_service', 'Updated service: AIRCON CLEANING (DUAL EVAPORATOR) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:19:26'),
(356, 6, NULL, 'update_service', 'Updated service: CHANGE OIL (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:19:39'),
(357, 6, NULL, 'update_service', 'Updated service: CHANGE OIL (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:21:28'),
(358, 6, NULL, 'create_service', 'Created service: EGR, INTAKE AND TURBO CLEANING', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:22:13'),
(359, 6, NULL, 'create_service', 'Created service: EGR AND INTAKE CLEANING', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:22:59'),
(360, 6, NULL, 'create_service', 'Created service: TURBO CLEANING', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:23:33'),
(361, 6, NULL, 'create_service', 'Created service: EGR, INTAKE, AND TURBO CLEANING', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:24:26'),
(362, 6, NULL, 'create_service', 'Created service: CARWASH', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:25:02'),
(363, 6, NULL, 'update_service', 'Updated service: WHEEL ALIGNMENT (COMPLETE) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:25:35'),
(364, 6, NULL, 'update_service', 'Updated service: WHEEL ALIGNMENT (TOE IN/TOE OUT) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:25:55'),
(365, 6, NULL, 'update_service', 'Updated service: WHEEL ALIGNMENT (TOE IN/TOE OUT) (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:26:03'),
(366, 6, NULL, 'create_service', 'Created service: BRAKE CLEANING', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:27:21'),
(367, 6, NULL, 'update_service', 'Updated service: FUEL INJECTOR CLEANING (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:28:19'),
(368, 6, NULL, 'create_service', 'Created service: DRIVE BELT REPLACEMENT', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:30:05'),
(369, 6, NULL, 'create_service', 'Created service: THROTTLE BODY CLEANING', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:30:35'),
(370, 6, NULL, 'create_service', 'Created service: REPLACE AUX. FAN MOTOR', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:31:12'),
(371, 6, NULL, 'create_service', 'Created service: PULL OUT / INSTALL FRONT LOWER SUSP. ASSY (RH/LH)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:32:13'),
(372, 6, NULL, 'create_service', 'Created service: REPLACE AIR FILTER AND CABIN FILTER', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:33:06'),
(373, 6, NULL, 'create_service', 'Created service: RADIATOR CLEANING', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:33:31'),
(374, 6, NULL, 'create_service', 'Created service: RESCUE', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:34:07'),
(375, 6, NULL, 'update_service', 'Updated service: RESCUE (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:34:42'),
(376, 6, NULL, 'create_service', 'Created service: TOWING', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:35:05'),
(377, 6, NULL, 'update_service', 'Updated service: CHANGE/FLUSH BRAKE FLUID (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:36:55'),
(378, 6, NULL, 'update_service', 'Updated service: EGR AND INTAKE CLEANING (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:37:26'),
(379, 6, NULL, 'update_service', 'Updated service: EGR AND INTAKE CLEANING (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:37:41'),
(380, 6, NULL, 'update_service', 'Updated service: STEERING RACK REPAIR - PULL OUT/INSTALL (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:38:11'),
(381, 6, NULL, 'create_service', 'Created service: TIE ROD REPLACEMENT', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:38:40'),
(382, 6, NULL, 'create_service', 'Created service: WHEEL BALANCING', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:39:36'),
(383, 6, NULL, 'create_service', 'Created service: CHECK/CORRECT LEAK COMING INSIDE', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:40:15'),
(384, 6, NULL, 'create_service', 'Created service: PULL OUT CAR MATTING (CLEAN AND DRY)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:40:52'),
(385, 6, NULL, 'create_service', 'Created service: OXYGEN SENSOR CLEANING', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:41:27'),
(386, 6, NULL, 'update_service', 'Updated service: REPLACE SPARK PLUG (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:42:13'),
(387, 6, NULL, 'create_service', 'Created service: REFACE ROTO DISC (BOTH SIDES) - SEDAN', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:43:07'),
(388, 6, NULL, 'create_service', 'Created service: REFACE ROTO DISC (BOTH SIDES) - PICK UP, SUV', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:43:33'),
(389, 6, NULL, 'update_service', 'Updated service: REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:43:45'),
(390, 6, NULL, 'update_service', 'Updated service: REFACE ROTOR DISC (BOTH SIDES) - SEDAN (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:43:58'),
(391, 6, NULL, 'create_service', 'Created service: REPLACE LOWER BALL JOINT (BOTH SIDES)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:44:23'),
(392, 6, NULL, 'update_service', 'Updated service: REPLACE SPARK PLUG (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:44:52'),
(393, 6, NULL, 'update_service', 'Updated service: LIGHT PMS GAS (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:56:28'),
(394, 6, NULL, 'update_service', 'Updated service: LIGHT PMS GAS (status ACTIVE -> ACTIVE)', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 03:59:12'),
(395, 6, NULL, 'create_service', 'Created service: LIGHT PMS DIESEL', '2001:fd8:28bb:a558:6cae:748f:7e8d:e483', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 04:00:51'),
(396, 0, NULL, 'failed_login', 'Failed login attempt for ID: 00000', '45.64.83.197', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 04:25:46'),
(397, 2, NULL, 'login', 'Admin logged in successfully', '45.64.83.197', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 04:25:53'),
(398, 2, NULL, 'logout', 'User logged out', '45.64.83.197', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-17 04:26:30'),
(399, 2, NULL, 'login', 'Admin logged in successfully', '45.64.83.197', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-19 04:38:09'),
(400, 2, NULL, 'logout', 'User logged out', '45.64.83.197', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-19 04:50:39'),
(401, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:49c7:e8ce:7237:1c30', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-20 02:24:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `staff_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(402, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:49c7:e8ce:7237:1c30', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-20 02:26:10'),
(403, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c826:c200:49c7:e8ce:7237:1c30', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-20 02:55:09'),
(404, 2, NULL, 'logout', 'User logged out', '2001:fd8:c826:c200:49c7:e8ce:7237:1c30', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-20 02:55:32'),
(405, 2, NULL, 'login', 'Admin logged in successfully', '45.64.83.197', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-20 06:10:43'),
(406, 2, NULL, 'logout', 'User logged out', '45.64.83.197', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-20 06:10:55'),
(407, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c810:cc00:7530:1684:5f79:2c29', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 13:20:35'),
(408, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c810:cc00:d3f:c33f:b13a:d40', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-22 13:23:58'),
(409, 2, NULL, 'logout', 'User logged out', '2001:fd8:c810:cc00:d3f:c33f:b13a:d40', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-22 13:28:56'),
(410, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.232', 'Mozilla/5.0 (Linux; Android 8.1.0; INE-LX2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Mobile Safari/537.36', '2026-08-27 04:55:23'),
(411, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.232', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-28 09:57:48'),
(412, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c810:cc00:f90c:6fd5:71a5:6527', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-28 13:20:56'),
(413, 2, NULL, 'logout', 'User logged out', '2001:fd8:c810:cc00:f90c:6fd5:71a5:6527', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-08-28 13:33:11'),
(414, 0, NULL, 'failed_login', 'Failed login attempt for ID: 21411', '2001:fd8:c810:cc00:a8f6:dfd2:4d6b:20f1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 12:42:33'),
(415, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c810:cc00:a8f6:dfd2:4d6b:20f1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 12:42:46'),
(416, 6, NULL, 'update_staff', 'Updated staff: Aian P. Alderite', '2001:fd8:c810:cc00:a8f6:dfd2:4d6b:20f1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 12:43:31'),
(417, 6, NULL, 'update_staff_status', 'Updated staff status: Jan Carlo Padios (active -> inactive)', '2001:fd8:c810:cc00:a8f6:dfd2:4d6b:20f1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 12:43:50'),
(418, 6, NULL, 'logout', 'User logged out', '2001:fd8:c810:cc00:a8f6:dfd2:4d6b:20f1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 12:45:47'),
(419, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '2001:fd8:c810:cc00:a8f6:dfd2:4d6b:20f1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-31 12:46:07'),
(420, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-08-31 23:35:43'),
(421, 8, NULL, 'update_service', 'Updated service: BRAKE CLEANING (status ACTIVE -> ACTIVE)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-08-31 23:36:34'),
(422, 8, NULL, 'update_service', 'Updated service: EGR, INTAKE AND TURBO CLEANING (status ACTIVE -> ACTIVE)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-08-31 23:37:32'),
(423, 8, NULL, 'update_service', 'Updated service: REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV (status ACTIVE -> ACTIVE)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-08-31 23:39:12'),
(424, 8, NULL, 'update_service', 'Updated service: REFACE ROTOR DISC (BOTH SIDES) - SEDAN (status ACTIVE -> ACTIVE)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-08-31 23:39:28'),
(425, 8, NULL, 'create_estimate', 'Created estimate #JE001', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-08-31 23:47:08'),
(426, 8, NULL, 'create_job_order', 'Created job order #JO001', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-08-31 23:49:27'),
(427, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO001: Pending → Cancelled', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-08-31 23:52:54'),
(428, 0, NULL, 'failed_login', 'Failed login attempt for ID: 21411', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 00:46:52'),
(429, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 00:46:58'),
(430, 8, NULL, 'create_estimate', 'Created estimate #JE002', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 00:56:09'),
(431, 6, NULL, 'logout', 'User logged out', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 00:57:08'),
(432, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 00:59:08'),
(433, 6, NULL, 'stock_in', 'Stock in: ENGINE OIL 5W-30 (+10)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 01:20:08'),
(434, 8, NULL, 'create_job_order', 'Created job order #JO002', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 01:20:27'),
(435, 8, NULL, 'update_job_order', 'Updated job order #JO002: Services/Bundles updated; Products count: 0 → 2', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 01:23:05'),
(436, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO002: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 01:23:08'),
(437, 8, NULL, 'update_job_order', 'Updated job order #JO002: Services/Bundles count: 1 → 2; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 01:25:11'),
(438, 6, NULL, 'update_service', 'Updated service: REGULAR PMS (status ACTIVE -> ACTIVE)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 01:27:46'),
(439, 6, NULL, 'update_service', 'Updated service: REGULAR PMS (status ACTIVE -> ACTIVE)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 01:28:22'),
(440, 6, NULL, 'update_job_order', 'Updated job order #JO002: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 01:29:14'),
(441, 8, NULL, 'update_job_order', 'Updated job order #JO002: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 01:33:13'),
(442, 6, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 01:35:00'),
(443, 6, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 01:35:17'),
(444, 6, NULL, 'logout', 'User logged out', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:13:19'),
(445, 0, NULL, 'failed_login', 'Failed login attempt for ID: 21411', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:14:00'),
(446, 0, NULL, 'failed_login', 'Failed login attempt for ID: 21411', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:19:42'),
(447, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:19:55'),
(448, 8, NULL, 'create_job_order', 'Created job order #JO003', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 02:20:23'),
(449, 6, NULL, 'update_job_order', 'Updated job order #JO002: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:21:07'),
(450, 8, NULL, 'update_job_order', 'Updated job order #JO003: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 02:22:52'),
(451, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO003: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 02:22:59'),
(452, 6, NULL, 'update_job_order', 'Updated job order #JO003: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:24:58'),
(453, 6, NULL, 'update_job_order', 'Updated job order #JO002: Services/Bundles updated; Products count: 2 → 1', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:28:00'),
(454, 6, NULL, 'create_service', 'Created service: AIR FILTER 039', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:28:30'),
(455, 6, NULL, 'update_job_order', 'Updated job order #JO002: Services/Bundles updated; Products count: 1 → 2', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:29:23'),
(456, 6, NULL, 'update_job_order', 'Updated job order #JO002: Services/Bundles updated; Products count: 2 → 1', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:30:09'),
(457, 6, NULL, 'update_job_order', 'Updated job order #JO002: Services/Bundles updated; Products count: 1 → 2', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:30:42'),
(458, 6, NULL, 'update_service', 'Updated service:  (status ACTIVE -> INACTIVE)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:31:46'),
(459, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO002 (elapsed: 4588s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:39:36'),
(460, 6, NULL, 'update_job_order', 'Updated job order #JO001: Services/Bundles updated; Products count: 1 → 0', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:41:57'),
(461, 6, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:42:55'),
(462, 6, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 02:44:24'),
(463, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO003 (elapsed: 5950s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 04:02:09'),
(464, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO003 (elapsed: 5950s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:15:56'),
(465, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO002 (elapsed: 4588s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:17:42'),
(466, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO002 (elapsed: 5227s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:28:21'),
(467, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 05:33:03'),
(468, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 05:33:39'),
(469, 8, NULL, 'update_job_order', 'Updated job order #JO002: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 05:37:02'),
(470, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO002: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 05:40:32'),
(471, 6, NULL, 'update_staff', 'Updated staff: Pam-pam', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:46:44'),
(472, 6, NULL, 'update_staff', 'Updated staff: Kineth', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:46:56'),
(473, 6, NULL, 'update_staff', 'Updated staff: Nexander G.', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:47:19'),
(474, 6, NULL, 'update_staff', 'Updated staff: Kineth P.', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:47:29'),
(475, 6, NULL, 'update_staff', 'Updated staff: Jerald C.', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:47:43'),
(476, 6, NULL, 'update_staff', 'Updated staff: Legario M.', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:47:55'),
(477, 6, NULL, 'update_staff', 'Updated staff: John Paul V.', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:48:07'),
(478, 6, NULL, 'update_staff', 'Updated staff: Artemio B. Jr', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 05:48:22'),
(479, 8, NULL, 'update_job_order', 'Updated job order #JO003: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 06:42:42'),
(480, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO003 (elapsed: 11190s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 06:43:16'),
(481, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO003: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 06:43:28'),
(482, 6, NULL, 'update_job_order', 'Updated job order #JO003: Payment method: cash → bank_transfer; Payment status: pending → paid; Partial amount: 0.00 → 12,500.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱12,500.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 06:47:13'),
(483, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO003: Completed → Released', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 06:47:18'),
(484, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO003: Released → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 07:01:34'),
(485, 6, NULL, 'update_job_order', 'Updated job order #JO002: Payment status: pending → paid; Partial amount: 0.00 → 5,800.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱5,800.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 07:44:55'),
(486, 6, NULL, 'update_profile', 'Updated own profile details', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 08:05:03'),
(487, 6, NULL, 'update_profile', 'Updated own profile details', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 08:07:08'),
(488, 8, NULL, 'create_job_order', 'Created job order #JO004', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 08:07:46'),
(489, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO004: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 08:08:39'),
(490, 8, NULL, 'update_job_order', 'Updated job order #JO004: Vehicle mileage: 201,386 KM → 201,386; Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 08:09:36'),
(491, 6, NULL, 'update_profile', 'Updated own profile details', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 08:24:42'),
(492, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO004 (elapsed: 1008s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-01 08:25:27'),
(493, 6, NULL, 'update_profile', 'Updated own profile details', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 08:32:36'),
(494, 6, NULL, 'add_expense', 'Added expense entry: ₱450.00 on Sep 01, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 08:33:37'),
(495, 6, NULL, 'add_product', 'Added product: BEARING [PRD36]', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 10:01:02'),
(496, 6, NULL, 'edit_product', 'Updated product: BEARING (status ACTIVE -> INACTIVE)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 10:02:58'),
(497, 6, NULL, 'add_expense', 'Added expense entry: ₱1,960.00 on Sep 01, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 10:03:38'),
(498, 6, NULL, 'add_expense', 'Added expense entry: ₱1,891.00 on Sep 01, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-09-01 10:08:02'),
(499, 2, NULL, 'login', 'Admin logged in successfully', '138.84.110.54', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 16:09:34'),
(500, 2, NULL, 'logout', 'User logged out', '138.84.110.54', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 16:11:46'),
(501, 2, NULL, 'login', 'Admin logged in successfully', '138.84.110.54', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 16:12:22'),
(502, 2, NULL, 'logout', 'User logged out', '2001:fd8:c810:cc00:74b8:6840:4300:5e7f', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 16:21:14'),
(503, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c810:cc00:74b8:6840:4300:5e7f', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 16:28:45'),
(504, 2, NULL, 'logout', 'User logged out', '2001:fd8:c810:cc00:74b8:6840:4300:5e7f', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 16:29:20'),
(505, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c810:cc00:74b8:6840:4300:5e7f', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 16:59:15'),
(506, 2, NULL, 'add_expense', 'Added expense entry: ₱100.00 on Sep 02, 2026', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:18:12'),
(507, 2, NULL, 'logout', 'User logged out', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:18:18'),
(508, 2, NULL, 'login', 'Admin logged in successfully', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:19:34'),
(509, 2, NULL, 'add_manual_income', 'Added manual income: ₱100.00 on Sep 02, 2026', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:19:51'),
(510, 2, NULL, 'logout', 'User logged out', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:20:15'),
(511, 2, NULL, 'login', 'Admin logged in successfully', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:20:42'),
(512, 2, NULL, 'delete_manual_income', 'Deleted manual income entry', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:20:53'),
(513, 2, NULL, 'delete_expense', 'Deleted expense entry: ₱100.00 on Sep 02, 2026', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:20:58'),
(514, 2, NULL, 'logout', 'User logged out', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:21:02'),
(515, 2, NULL, 'login', 'Admin logged in successfully', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:25:32'),
(516, 2, NULL, 'add_manual_income', 'Added manual income: ₱200.00 on Sep 02, 2026', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:25:45'),
(517, 2, NULL, 'add_expense', 'Added expense entry: ₱100.00 on Sep 02, 2026', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:25:51'),
(518, 2, NULL, 'logout', 'User logged out', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:25:57'),
(519, 2, NULL, 'login', 'Admin logged in successfully', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:26:29'),
(520, 2, NULL, 'delete_manual_income', 'Deleted manual income entry', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:26:39'),
(521, 2, NULL, 'delete_expense', 'Deleted expense entry: ₱100.00 on Sep 02, 2026', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:26:42'),
(522, 2, NULL, 'logout', 'User logged out', '2405:8d40:4511:56ce:a9c0:47ac:607f:aafc', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:35:33'),
(523, 2, NULL, 'login', 'Admin logged in successfully', '112.198.163.163', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:45:03'),
(524, 2, NULL, 'add_expense', 'Added expense entry: ₱100.00 on Sep 02, 2026', '112.198.163.163', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:45:14'),
(525, 2, NULL, 'logout', 'User logged out', '112.198.163.163', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:45:16'),
(526, 2, NULL, 'login', 'Admin logged in successfully', '112.198.163.163', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:45:38'),
(527, 2, NULL, 'delete_expense', 'Deleted expense entry: ₱100.00 on Sep 02, 2026', '112.198.163.163', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:45:53'),
(528, 2, NULL, 'logout', 'User logged out', '112.198.163.163', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-01 17:54:41'),
(529, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 00:47:45'),
(530, 8, NULL, 'create_job_order', 'Created job order #JO005', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 00:50:35'),
(531, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO005: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 00:50:43'),
(532, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 00:51:16'),
(533, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 02:28:41'),
(534, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 03:17:19'),
(535, 2, NULL, 'login', 'Admin logged in successfully', '203.177.19.21', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-02 03:33:11'),
(536, 2, NULL, 'logout', 'User logged out', '203.177.19.21', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-02 03:33:41'),
(537, 2, NULL, 'login', 'Admin logged in successfully', '203.177.19.21', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-02 03:35:05'),
(538, 2, NULL, 'logout', 'User logged out', '203.177.19.21', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-02 03:36:15'),
(539, 2, NULL, 'login', 'Admin logged in successfully', '203.177.19.21', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 03:36:51'),
(540, 2, NULL, 'logout', 'User logged out', '203.177.19.21', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 03:39:53'),
(541, 6, NULL, 'add_expense', 'Added expense entry: ₱450.00 on Sep 01, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 03:57:23'),
(542, 6, NULL, 'add_expense', 'Added expense entry: ₱1,788.00 on Sep 01, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 03:57:54'),
(543, 8, NULL, 'create_job_order', 'Created job order #JO006', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 03:59:20'),
(544, 6, NULL, 'add_expense', 'Added expense entry: ₱1,960.00 on Sep 01, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 04:01:03'),
(545, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO005 (elapsed: 11479s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 04:02:02'),
(546, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO005 (elapsed: 11479s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 05:07:54'),
(547, 8, NULL, 'update_job_order', 'Updated job order #JO004: Customer phone: 09363408302 → 0995 611 5639; Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 05:11:46'),
(548, 8, NULL, 'update_job_order', 'Updated job order #JO006: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 05:12:16'),
(549, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:38:29'),
(550, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 06:46:18'),
(551, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO006: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 06:46:25'),
(552, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO004 (elapsed: 1008s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 06:47:16'),
(553, 8, NULL, 'update_job_order', 'Updated job order #JO004: Services/Bundles count: 1 → 2; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 06:50:59'),
(554, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO004 (elapsed: 3101s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 07:22:09'),
(555, 8, NULL, 'update_job_order', 'Updated job order #JO006: Services/Bundles count: 4 → 6; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 07:56:00'),
(556, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO006 (elapsed: 5574s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 08:19:19'),
(557, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO005 (elapsed: 22968s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 08:19:23'),
(558, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO005 (elapsed: 22968s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 08:27:07'),
(559, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO005 (elapsed: 23729s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 08:39:48'),
(560, 6, NULL, 'update_job_order', 'Updated job order #JO005: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 08:40:53'),
(561, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82b:4500:4961:261d:f3b:ff81', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-02 08:47:34'),
(562, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82b:4500:4961:261d:f3b:ff81', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-02 08:48:45'),
(563, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 09:04:33'),
(564, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 09:04:56'),
(565, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 23:37:47'),
(566, 8, NULL, 'update_job_order', 'Updated job order #JO006: Services/Bundles count: 6 → 2; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 23:49:26'),
(567, 8, NULL, 'update_job_order', 'Updated job order #JO006: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-02 23:52:35'),
(568, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 00:33:07'),
(569, 8, NULL, 'update_job_order', 'Updated job order #JO006: Services/Bundles updated; Products count: 0 → 1', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 00:55:55'),
(570, 8, NULL, 'update_job_order', 'Updated job order #JO005: Services/Bundles updated; Products count: 0 → 1', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 01:04:57'),
(571, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 03:18:47'),
(572, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO005 (elapsed: 23729s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 03:19:02'),
(573, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO006 (elapsed: 5574s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 03:19:04'),
(574, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 05:00:17'),
(575, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 05:01:02'),
(576, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO006 (elapsed: 11700s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 05:01:10'),
(577, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO005 (elapsed: 29860s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 05:01:13'),
(578, 8, NULL, 'update_job_order', 'Updated job order #JO006: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 05:03:27'),
(579, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO005 (elapsed: 29860s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 05:17:25'),
(580, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO006 (elapsed: 11700s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 05:17:27'),
(581, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO006 (elapsed: 12072s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 05:23:39'),
(582, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO004 (elapsed: 3101s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 05:48:57'),
(583, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 06:50:33'),
(584, 6, NULL, 'create_estimate', 'Created estimate #JE003', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 06:53:19'),
(585, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 08:11:58'),
(586, 6, NULL, 'add_manual_income', 'Added manual income: ₱300.00 on Sep 03, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 08:17:02'),
(587, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 08:21:14'),
(588, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO006: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 08:21:31'),
(589, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO005 (elapsed: 40926s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 08:21:51'),
(590, 8, NULL, 'update_job_order', 'Updated job order #JO004: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 08:26:45'),
(591, 8, NULL, 'update_job_order', 'Updated job order #JO004: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 08:28:14'),
(592, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO004: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 08:28:35'),
(593, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 09:56:17'),
(594, 6, NULL, 'update_job_order', 'Updated job order #JO006: Payment status: pending → paid; Partial amount: 0.00 → 5,700.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱5,700.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 09:56:57'),
(595, 6, NULL, 'update_job_order', 'Updated job order #JO004: Payment status: pending → paid; Partial amount: 0.00 → 4,000.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱4,000.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 09:57:12'),
(596, 6, NULL, 'add_expense', 'Added expense entry: ₱250.00 on Sep 03, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 09:57:37'),
(597, 6, NULL, 'add_expense', 'Added expense entry: ₱1,320.00 on Sep 03, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 09:57:51'),
(598, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82b:4500:1193:6456:d60e:bb3b', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 16:58:20'),
(599, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82b:4500:1193:6456:d60e:bb3b', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 17:18:32'),
(600, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82b:4500:1193:6456:d60e:bb3b', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 17:18:43'),
(601, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82b:4500:1193:6456:d60e:bb3b', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 17:19:07'),
(602, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82b:4500:1193:6456:d60e:bb3b', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 17:19:15'),
(603, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82b:4500:1193:6456:d60e:bb3b', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-03 17:20:07'),
(604, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 23:48:58'),
(605, 8, NULL, 'update_job_order', 'Updated job order #JO005: Customer phone: 0921 505 3257 → 09215053257; Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-03 23:49:55'),
(606, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO005: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 00:23:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `staff_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(607, 8, NULL, 'update_job_order', 'Updated job order #JO005: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 00:24:30'),
(608, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:49:35'),
(609, 6, NULL, 'update_job_order', 'Updated job order #JO005: Payment method: cash → bank_transfer; Payment status: pending → paid; Partial amount: 0.00 → 11,800.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱11,800.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 00:50:04'),
(610, 0, NULL, 'failed_login', 'Failed login attempt for ID: 31422', '2001:fd8:c82b:4500:853f:8ea0:b9a5:c258', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-04 00:52:47'),
(611, 5, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c82b:4500:853f:8ea0:b9a5:c258', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-04 00:52:57'),
(612, 5, NULL, 'update_profile', 'Updated own profile details', '2001:fd8:c82b:4500:853f:8ea0:b9a5:c258', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-04 00:53:33'),
(613, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 01:47:50'),
(614, 8, NULL, 'create_job_order', 'Created job order #JO007', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 01:52:57'),
(615, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO007: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 01:53:02'),
(616, 8, NULL, 'update_job_order', 'Updated job order #JO007: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 02:17:41'),
(617, 8, NULL, 'update_job_order', 'Updated job order #JO007: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 02:20:24'),
(618, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO007 (elapsed: 2373s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 02:32:35'),
(619, 8, NULL, 'update_job_order', 'Updated job order #JO007: Services/Bundles count: 2 → 3; Products count: 0 → 1', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 03:02:08'),
(620, 8, NULL, 'create_job_order', 'Created job order #JO008', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 03:59:14'),
(621, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 05:06:06'),
(622, 6, NULL, 'add_expense', 'Added expense entry: ₱200.00 on Sep 04, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 05:36:53'),
(623, 6, NULL, 'add_expense', 'Added expense entry: ₱10.00 on Sep 04, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 05:37:04'),
(624, 6, NULL, 'add_expense', 'Added expense entry: ₱600.00 on Sep 04, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 05:37:19'),
(625, 6, NULL, 'add_expense', 'Added expense entry: ₱86.00 on Sep 04, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 05:37:36'),
(626, 6, NULL, 'add_expense', 'Added expense entry: ₱2,733.00 on Sep 03, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 05:40:39'),
(627, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 06:47:53'),
(628, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO007 (elapsed: 2373s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 06:48:06'),
(629, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 07:36:45'),
(630, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO008: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 07:36:50'),
(631, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO007 (elapsed: 5431s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 07:39:04'),
(632, 8, NULL, 'update_job_order', 'Updated job order #JO007: Services/Bundles updated; Products count: 1 → 2', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 07:44:32'),
(633, 8, NULL, 'update_job_order', 'Updated job order #JO007: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 07:47:58'),
(634, 8, NULL, 'update_job_order', 'Updated job order #JO007: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 07:52:03'),
(635, 8, NULL, 'update_job_order', 'Updated job order #JO008: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 07:52:39'),
(636, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82b:4500:552a:8460:6222:a72c', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-04 09:09:21'),
(637, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO008 (elapsed: 6556s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-04 09:26:06'),
(638, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 09:27:58'),
(639, 6, NULL, 'add_manual_income', 'Added manual income: ₱1,900.00 on Sep 04, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 09:28:26'),
(640, 6, NULL, 'add_expense', 'Added expense entry: ₱1,100.00 on Sep 04, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-04 09:32:10'),
(641, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 00:51:38'),
(642, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 01:01:34'),
(643, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 01:32:28'),
(644, 6, NULL, 'add_expense', 'Added expense entry: ₱6,395.00 on Sep 04, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 01:33:49'),
(645, 8, NULL, 'create_job_order', 'Created job order #JO009', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 01:37:01'),
(646, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO009: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 01:37:05'),
(647, 8, NULL, 'update_job_order', 'Updated job order #JO009: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 01:37:16'),
(648, 8, NULL, 'update_job_order', 'Updated job order #JO009: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 01:37:47'),
(649, 8, NULL, 'update_job_order', 'Updated job order #JO008: Services/Bundles updated; Products count: 4 → 6', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 02:52:21'),
(650, 8, NULL, 'update_job_order', 'Updated job order #JO008: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 02:56:03'),
(651, 8, NULL, 'update_job_order_timer', 'Start timer for job order #JO008 (elapsed: 6556s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 03:06:45'),
(652, 8, NULL, 'update_job_order', 'Updated job order #JO008: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 03:24:54'),
(653, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO008 (elapsed: 8826s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 03:44:35'),
(654, 8, NULL, 'update_job_order', 'Updated job order #JO009: Services/Bundles updated; Products count: 3 → 2', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 03:46:07'),
(655, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 04:01:24'),
(656, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO009 (elapsed: 9652s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 04:17:57'),
(657, 8, NULL, 'update_job_order', 'Updated job order #JO009: Services/Bundles updated; Products count: 2 → 1', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 04:18:40'),
(658, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO009: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 04:29:04'),
(659, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 06:32:46'),
(660, 6, NULL, 'update_job_order', 'Updated job order #JO009: Payment status: pending → paid; Partial amount: 0.00 → 8,000.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱8,000.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 06:33:22'),
(661, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 06:36:52'),
(662, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 06:37:25'),
(663, 6, NULL, 'add_expense', 'Added expense entry: ₱2,499.86 on Sep 05, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 06:38:26'),
(664, 8, NULL, 'create_job_order', 'Created job order #JO010', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 06:42:24'),
(665, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO010: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 06:42:27'),
(666, 8, NULL, 'update_job_order', 'Updated job order #JO010: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 06:45:40'),
(667, 8, NULL, 'update_job_order_timer', 'Start timer for job order #JO008 (elapsed: 8826s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 06:45:51'),
(668, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82b:4500:39be:fb90:bc0f:1fcf', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-05 06:46:46'),
(669, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82b:4500:39be:fb90:bc0f:1fcf', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-05 06:47:12'),
(670, 8, NULL, 'update_job_order', 'Updated job order #JO010: Notes: — → ---RECOMMENDATIONS---\n- NOTE: NEED TO REPLACE REAR BRAKE SHOE (BELOW 4MM); Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:10:07'),
(671, 8, NULL, 'update_job_order', 'Updated job order #JO010: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:10:37'),
(672, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO010 (elapsed: 2634s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:26:21'),
(673, 8, NULL, 'update_job_order', 'Updated job order #JO010: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:26:44'),
(674, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO008 (elapsed: 11564s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:31:29'),
(675, 8, NULL, 'update_job_order', 'Updated job order #JO008: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:36:33'),
(676, 8, NULL, 'logout', 'User logged out', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:38:32'),
(677, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:38:42'),
(678, 8, NULL, 'logout', 'User logged out', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:38:58'),
(679, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:39:40'),
(680, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO010: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 07:53:26'),
(681, 8, NULL, 'update_job_order', 'Updated job order #JO008: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 08:12:04'),
(682, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO008: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-05 09:13:11'),
(683, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO007: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 09:47:36'),
(684, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO007: Completed → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 09:47:51'),
(685, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO007 (elapsed: 5438s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 09:47:58'),
(686, 6, NULL, 'update_job_order', 'Updated job order #JO008: Payment status: pending → paid; Partial amount: 0.00 → 36,720.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱36,720.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 09:48:33'),
(687, 6, NULL, 'add_expense', 'Added expense entry: ₱18,070.00 on Sep 05, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 09:49:45'),
(688, 6, NULL, 'add_manual_income', 'Added manual income: ₱2,300.00 on Sep 05, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 09:51:01'),
(689, 6, NULL, 'add_expense', 'Added expense entry: ₱100.00 on Sep 05, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 09:51:36'),
(690, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '2001:fd8:c82b:4500:e9dd:685b:c47b:3285', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 12:06:38'),
(691, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82b:4500:39be:fb90:bc0f:1fcf', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 12:50:40'),
(692, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82b:4500:39be:fb90:bc0f:1fcf', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 12:58:30'),
(693, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c82b:4500:e9dd:685b:c47b:3285', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 13:13:31'),
(694, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82b:4500:39be:fb90:bc0f:1fcf', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 13:29:28'),
(695, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82b:4500:39be:fb90:bc0f:1fcf', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 13:29:38'),
(696, 6, NULL, 'add_expense', 'Added expense entry: ₱2,761.00 on Sep 05, 2026', '2001:fd8:c82b:4500:e9dd:685b:c47b:3285', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-05 13:30:00'),
(697, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 00:51:09'),
(698, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-07 01:46:53'),
(699, 6, NULL, 'update_job_order', 'Updated job order #JO010: Payment method: cash → bank_transfer; Payment status: pending → paid; Partial amount: 0.00 → 7,950.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱7,950.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 01:47:30'),
(700, 6, NULL, 'stock_out', 'Stock out: ENGINE OIL 5W-40 (-4)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 01:48:40'),
(701, 6, NULL, 'stock_in', 'Stock in: BRAKE CLEANER (+19)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 01:50:00'),
(702, 8, NULL, 'create_job_order', 'Created job order #JO011', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-07 02:14:38'),
(703, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO011: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-07 02:15:11'),
(704, 8, NULL, 'update_job_order', 'Updated job order #JO011: Vehicle model: — → WIGO/CVT; Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-07 02:15:34'),
(705, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO011 (elapsed: 4333s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-07 03:27:24'),
(706, 8, NULL, 'update_job_order', 'Updated job order #JO011: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-07 03:28:02'),
(707, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO011: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 03:35:14'),
(708, 6, NULL, 'update_job_order', 'Updated job order #JO011: Payment status: pending → paid; Partial amount: 0.00 → 1,200.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱1,200.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 03:39:02'),
(709, 6, NULL, 'add_expense', 'Added expense entry: ₱20.00 on Sep 07, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 03:39:28'),
(710, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 06:27:14'),
(711, 8, NULL, 'update_job_order', 'Updated job order #JO007: Vehicle model: VIOS → VIOS/MT; Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-07 06:32:06'),
(712, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82b:4500:e130:7841:cb82:1b77', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 08:32:32'),
(713, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82b:4500:e130:7841:cb82:1b77', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 08:59:16'),
(714, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 09:25:38'),
(715, 6, NULL, 'add_expense', 'Added expense entry: ₱3,409.00 on Sep 07, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 09:26:08'),
(716, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 00:18:59'),
(717, 8, NULL, 'create_job_order', 'Created job order #JO012', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 00:24:35'),
(718, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO012: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 00:25:02'),
(719, 8, NULL, 'update_job_order', 'Updated job order #JO012: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 00:30:54'),
(720, 8, NULL, 'update_job_order_timer', 'Start timer for job order #JO007 (elapsed: 5438s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 00:31:33'),
(721, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 01:16:08'),
(722, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO012 (elapsed: 6714s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 02:16:56'),
(723, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO007 (elapsed: 13088s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 02:39:03'),
(724, 8, NULL, 'update_job_order', 'Updated job order #JO007: Services/Bundles updated; Products count: 2 → 1', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 02:40:00'),
(725, 8, NULL, 'update_job_order', 'Updated job order #JO012: Services/Bundles count: 1 → 2; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 02:53:37'),
(726, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 04:51:56'),
(727, 8, NULL, 'create_job_order', 'Created job order #JO013', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 04:58:09'),
(728, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO013: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 04:58:15'),
(729, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO013 (elapsed: 2509s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 05:40:04'),
(730, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO013: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 05:40:07'),
(731, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 06:00:25'),
(732, 6, NULL, 'update_job_order', 'Updated job order #JO013: Payment status: pending → paid; Partial amount: 0.00 → 5,500.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱5,500.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 06:03:20'),
(733, 6, NULL, 'update_job_order', 'Updated job order #JO013: Services/Bundles updated; Products updated; Partial payment: ₱5,500.00 → ₱5,500.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 06:12:07'),
(734, 6, NULL, 'add_expense', 'Added expense entry: ₱400.00 on Sep 08, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-08 06:19:17'),
(735, 8, NULL, 'create_job_order', 'Created job order #JO014', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 06:34:37'),
(736, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO014: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 06:34:43'),
(737, 8, NULL, 'update_job_order', 'Updated job order #JO014: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 06:35:29'),
(738, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO014 (elapsed: 3726s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 07:36:49'),
(739, 8, NULL, 'update_job_order', 'Updated job order #JO007: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-08 07:40:20'),
(740, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:57:19'),
(741, 6, NULL, 'add_expense', 'Added expense entry: ₱533.00 on Sep 08, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:58:05'),
(742, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO012: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:58:16'),
(743, 6, NULL, 'update_job_order', 'Updated job order #JO012: Payment status: pending → paid; Partial amount: 0.00 → 3,000.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱3,000.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:58:39'),
(744, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO002: Completed → Released', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:58:47'),
(745, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO003: Completed → Released', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:58:51'),
(746, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO004: Completed → Released', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:58:53'),
(747, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO005: Completed → Released', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:58:57'),
(748, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO006: Completed → Released', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:59:00'),
(749, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO008: Completed → Released', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:59:02'),
(750, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO009: Completed → Released', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:59:05'),
(751, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO010: Completed → Released', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 00:59:12'),
(752, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-09 01:11:37'),
(753, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 02:32:13'),
(754, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO007: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 02:32:26'),
(755, 6, NULL, 'update_job_order', 'Updated job order #JO007: Payment method: cash → bank_transfer; Payment status: pending → paid; Partial amount: 0.00 → 10,200.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱10,200.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 02:33:18'),
(756, 6, NULL, 'add_expense', 'Added expense entry: ₱5,000.00 on Sep 09, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 02:33:41'),
(757, 6, NULL, 'delete_expense', 'Deleted expense entry: ₱5,000.00 on Sep 09, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 02:33:57'),
(758, 6, NULL, 'add_expense', 'Added expense entry: ₱5,000.00 on Sep 09, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 02:34:20'),
(759, 6, NULL, 'add_expense', 'Added expense entry: ₱150.00 on Sep 09, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 02:34:35'),
(760, 6, NULL, 'update_job_order_timer', 'Start timer for job order #JO014 (elapsed: 3726s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 03:20:44'),
(761, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 05:22:33'),
(762, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-09 05:24:25'),
(763, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO014 (elapsed: 11158s)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-09 05:24:36'),
(764, 8, NULL, 'update_job_order', 'Updated job order #JO014: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-09 05:25:48'),
(765, 8, NULL, 'update_job_order', 'Updated job order #JO014: Customer name: AHLIA MAMANG KAO → AHLIA MAMANGKAO; Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-09 06:13:05'),
(766, 8, NULL, 'create_job_order', 'Created job order #JO015', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-09 06:31:30'),
(767, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO015: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-09 06:31:48'),
(768, 8, NULL, 'update_job_order', 'Updated job order #JO015: Vehicle mileage: — → 176378; Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-09 06:32:29'),
(769, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 07:21:49'),
(770, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO015: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 08:00:00'),
(771, 6, NULL, 'add_product', 'Added product: CVT VALVOLINE [PRD37]', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 08:42:25'),
(772, 6, NULL, 'stock_in', 'Stock in: CVT VALVOLINE (+4)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 08:43:37'),
(773, 6, NULL, 'update_job_order', 'Updated job order #JO015: Services/Bundles updated; Products updated', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 08:44:07'),
(774, 6, NULL, 'update_job_order', 'Updated job order #JO015: Payment status: pending → paid; Partial amount: 0.00 → 4,650.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱4,650.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 08:44:30'),
(775, 6, NULL, 'stock_out', 'Stock out: CVT VALVOLINE (-1)', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 08:47:51'),
(776, 6, NULL, 'add_expense', 'Added expense entry: ₱5,600.00 on Sep 09, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 09:02:25'),
(777, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-10 00:16:04'),
(778, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-10 03:51:26'),
(779, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO014: Ongoing → Completed', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-10 03:51:39'),
(780, 6, NULL, 'update_job_order', 'Updated job order #JO014: Payment status: pending → paid; Partial amount: 0.00 → 9,300.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱9,300.00', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-10 03:51:57'),
(781, 6, NULL, 'add_expense', 'Added expense entry: ₱1,180.00 on Sep 10, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-10 03:52:19'),
(782, 6, NULL, 'add_expense', 'Added expense entry: ₱500.00 on Sep 10, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-10 03:52:41'),
(783, 6, NULL, 'add_expense', 'Added expense entry: ₱1,120.00 on Sep 10, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-10 03:53:05'),
(784, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-10 07:20:33'),
(785, 8, NULL, 'create_job_order', 'Created job order #JO016', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-10 07:22:06'),
(786, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO016: Pending → Ongoing', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-10 07:22:12'),
(787, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-10 07:28:09'),
(788, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-10 08:59:19'),
(789, 6, NULL, 'add_manual_income', 'Added manual income: ₱1,000.00 on Sep 10, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-10 09:00:34'),
(790, 6, NULL, 'add_expense', 'Added expense entry: ₱500.00 on Sep 10, 2026', '143.44.184.155', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-10 09:01:01'),
(791, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-10 23:54:06'),
(792, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-10 23:54:43'),
(793, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO016 (elapsed: 64447s)', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-11 01:16:19'),
(794, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-11 01:16:27'),
(795, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO016: Ongoing → Completed', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-11 01:16:48'),
(796, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-11 02:37:25'),
(797, 6, NULL, 'add_expense', 'Added expense entry: ₱350.00 on Sep 10, 2026', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-11 02:38:18'),
(798, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-11 09:54:17'),
(799, 6, NULL, 'add_expense', 'Added expense entry: ₱500.00 on Sep 11, 2026', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-11 09:54:49'),
(800, 6, NULL, 'update_job_order', 'Updated job order #JO016: Payment status: pending → paid; Partial amount: 0.00 → 1,200.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱1,200.00', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-11 09:55:11'),
(801, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '110.54.207.51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 01:10:43'),
(802, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:2cb2:ebca:e5e9:3b06:bdac:c573', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 03:07:53'),
(803, 6, NULL, 'create_job_order', 'Created job order #JO017', '2001:fd8:2cb2:ebca:e5e9:3b06:bdac:c573', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 03:10:10'),
(804, 6, NULL, 'update_job_order', 'Updated job order #JO017: Vehicle make: CHEVY → CHEVROLET; Vehicle model: CHEVROLET → TRAILBLAZER; Vehicle color: GRAY → —; Services/Bundles updated; Products updated', '2001:fd8:2cb2:ebca:fce3:af6b:d408:a06f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 03:15:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `staff_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(805, 6, NULL, 'update_job_order', 'Updated job order #JO017: Customer phone: 09090909090 → 09000000000; Services/Bundles updated; Products updated', '2001:fd8:2cb2:ebca:fce3:af6b:d408:a06f', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 03:15:39'),
(806, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO017: Pending → Ongoing', '2001:fd8:2cb2:ebca:61a1:77a6:92fa:849c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 03:18:00'),
(807, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 07:01:04'),
(808, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO017: Ongoing → Completed', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 07:02:15'),
(809, 6, NULL, 'create_job_order', 'Created job order #JO018', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 07:06:00'),
(810, 6, NULL, 'stock_in', 'Stock in: AIR FILTER (TRANSFORMER) (+1)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 07:06:31'),
(811, 6, NULL, 'update_job_order', 'Updated job order #JO018: Services/Bundles updated; Products count: 3 → 4', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 07:08:13'),
(812, 6, NULL, 'stock_in', 'Stock in: CABIN FILTER (87139-0N010) (+1)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 07:08:26'),
(813, 6, NULL, 'update_job_order', 'Updated job order #JO018: Services/Bundles updated; Products count: 4 → 0', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 07:09:52'),
(814, 2, NULL, 'login', 'Admin logged in successfully', '138.84.111.40', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-12 08:18:47'),
(815, 2, NULL, 'logout', 'User logged out', '138.84.111.40', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-12 08:19:42'),
(816, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 09:14:45'),
(817, 6, NULL, 'update_job_order', 'Updated job order #JO017: Payment status: pending → paid; Partial amount: 0.00 → 800.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱800.00', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 09:15:45'),
(818, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO018: Pending → Ongoing', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 09:15:52'),
(819, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO018 (elapsed: 3s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 09:15:55'),
(820, 6, NULL, 'stock_in', 'Stock in: AIR FILTER (MULTI-VEHICLE) (+1)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 10:06:24'),
(821, 6, NULL, 'edit_product', 'Updated product: CABIN FILTER (status ACTIVE -> ACTIVE)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 10:36:54'),
(822, 6, NULL, 'edit_product', 'Updated product: CABIN FILTER (status ACTIVE -> ACTIVE)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 10:37:22'),
(823, 6, NULL, 'stock_in', 'Stock in: COOLANT BLUE (+3)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 10:38:14'),
(824, 6, NULL, 'stock_in', 'Stock in: GEAR OIL (+3)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 10:38:31'),
(825, 6, NULL, 'edit_product', 'Updated product: GEAR OIL (status ACTIVE -> ACTIVE)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-12 10:39:09'),
(826, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82f:6600:d1a6:ced2:dfd0:df9e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-12 12:03:55'),
(827, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c82f:6600:d1a6:ced2:dfd0:df9e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-12 12:06:34'),
(828, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82f:6600:d1a6:ced2:dfd0:df9e', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-12 12:06:42'),
(829, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82f:6600:fccb:5588:9e95:d592', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-12 13:01:29'),
(830, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82f:6600:fccb:5588:9e95:d592', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-12 13:02:08'),
(831, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82f:6600:fccb:5588:9e95:d592', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-12 13:02:40'),
(832, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82f:6600:fccb:5588:9e95:d592', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-12 13:04:07'),
(833, 0, NULL, 'failed_login', 'Failed login attempt for ID: 43700', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-14 02:04:35'),
(834, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-14 02:04:57'),
(835, 8, NULL, 'update_job_order', 'Updated job order #JO018: Services/Bundles updated; Products count: 0 → 4', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-14 02:06:58'),
(836, 8, NULL, 'update_job_order_timer', 'Start timer for job order #JO018 (elapsed: 3s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-14 02:07:03'),
(837, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-14 09:16:56'),
(838, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO018 (elapsed: 25823s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-14 09:17:23'),
(839, 8, NULL, 'update_job_order', 'Updated job order #JO018: Notes: — → ---RECOMMENDATIONS---\n- NOTE: NEED TO REPLACE CLAMP BUSHING AND LINKIT RH/LH\n- NEED TO REPLACE FRONT SHOCK ABSORBER RH/LH; Services/Bundles updated; Products updated', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-14 09:22:17'),
(840, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-14 09:26:28'),
(841, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-14 23:26:48'),
(842, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO018: Ongoing → Completed', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-14 23:26:57'),
(843, 8, NULL, 'create_job_order', 'Created job order #JO019', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-15 00:37:54'),
(844, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO019: Pending → Ongoing', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-15 00:37:58'),
(845, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 00:43:26'),
(846, 6, NULL, 'update_job_order', 'Updated job order #JO018: Payment status: pending → paid; Partial amount: 0.00 → 8,500.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱8,500.00', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 00:44:01'),
(847, 6, NULL, 'add_expense', 'Added expense entry: ₱1,830.00 on Sep 14, 2026', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 00:50:17'),
(848, 6, NULL, 'update_job_order', 'Updated job order #JO018: Services/Bundles updated; Products updated; Partial payment: ₱8,500.00 → ₱8,500.00', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 00:50:48'),
(849, 6, NULL, 'update_job_order', 'Updated job order #JO018: Services/Bundles updated; Products updated; Partial payment: ₱8,500.00 → ₱8,500.00', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 00:51:35'),
(850, 6, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 01:16:24'),
(851, 6, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 01:16:48'),
(852, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c82f:6600:293b:34e4:bbd7:7eeb', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-15 02:09:00'),
(853, 2, NULL, 'update_print_template', 'Updated print template settings for Autodok Prime Auto Services', '2001:fd8:c82f:6600:293b:34e4:bbd7:7eeb', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-15 02:12:44'),
(854, 2, NULL, 'logout', 'User logged out', '2001:fd8:c82f:6600:293b:34e4:bbd7:7eeb', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.5.2 Safari/605.1.15', '2026-09-15 02:13:00'),
(855, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 02:24:59'),
(856, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-15 03:20:36'),
(857, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO019 (elapsed: 9765s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-15 03:20:43'),
(858, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 05:06:01'),
(859, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-15 23:36:38'),
(860, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO019: Ongoing → Cancelled', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-15 23:36:46'),
(861, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-16 05:05:41'),
(862, 8, NULL, 'create_job_order', 'Created job order #JO020', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-16 05:08:16'),
(863, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO020: Pending → Ongoing', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-16 05:08:22'),
(864, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 06:01:06'),
(865, 6, NULL, 'stock_out', 'Stock out: ENGINE OIL 5W-30 (-3)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 06:07:53'),
(866, 6, NULL, 'stock_in', 'Stock in: ENGINE OIL 5W-40 (+2)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 06:08:05'),
(867, 6, NULL, 'stock_out', 'Stock out: COOLANT BLUE (-2)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 06:08:22'),
(868, 6, NULL, 'stock_out', 'Stock out: COOLANT GREEN (-3)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 06:08:28'),
(869, 6, NULL, 'stock_in', 'Stock in: ATF LV MV (STOCKS) (+2)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 06:08:50'),
(870, 6, NULL, 'edit_product', 'Updated product: PENETRATING OIL (status ACTIVE -> ACTIVE)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 06:09:08'),
(871, 6, NULL, 'stock_out', 'Stock out: PENETRATING OIL (-1)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 06:09:18'),
(872, 6, NULL, 'stock_in', 'Stock in: BRAKE CLEANER (+2)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 06:09:53'),
(873, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO020 (elapsed: 9033s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-16 07:38:54'),
(874, 8, NULL, 'update_job_order', 'Updated job order #JO020: Services/Bundles updated; Products count: 3 → 4', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-16 07:53:11'),
(875, 8, NULL, 'update_job_order', 'Updated job order #JO020: Services/Bundles count: 1 → 2; Products updated', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-16 07:54:39'),
(876, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO020: Ongoing → Completed', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-16 08:00:38'),
(877, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 01:37:10'),
(878, 8, NULL, 'create_job_order', 'Created job order #JO021', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 01:53:34'),
(879, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO021: Pending → Ongoing', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 01:53:46'),
(880, 8, NULL, 'update_job_order', 'Updated job order #JO021: Services/Bundles updated; Products updated', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 01:57:35'),
(881, 8, NULL, 'create_job_order', 'Created job order #JO022', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 02:01:52'),
(882, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO022: Pending → Ongoing', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 02:01:56'),
(883, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO022 (elapsed: 5803s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 03:38:39'),
(884, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO021 (elapsed: 6296s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 03:38:42'),
(885, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-17 05:32:17'),
(886, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 07:04:24'),
(887, 8, NULL, 'update_job_order', 'Updated job order #JO021: Services/Bundles updated; Products updated', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 08:10:53'),
(888, 8, NULL, 'update_job_order', 'Updated job order #JO021: Services/Bundles updated; Products updated', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 08:11:31'),
(889, 8, NULL, 'update_job_order', 'Updated job order #JO022: Services/Bundles updated; Products updated', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 08:12:37'),
(890, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO022: Ongoing → Completed', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-17 08:57:49'),
(891, 0, NULL, 'failed_login', 'Failed login attempt for staff ID: 72001', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-18 00:35:38'),
(892, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-18 00:36:03'),
(893, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO021: Ongoing → Completed', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-18 01:20:09'),
(894, 8, NULL, 'update_job_order', 'Updated job order #JO021: Payment status: pending → paid; Partial amount: 0.00 → 4,000.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱4,000.00', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-18 01:58:50'),
(895, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-18 02:58:14'),
(896, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-20 23:20:16'),
(897, 8, NULL, 'create_job_order', 'Created job order #JO023', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-20 23:23:15'),
(898, 8, NULL, 'create_job_order', 'Created job order #JO024', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-20 23:23:15'),
(899, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO024: Pending → Ongoing', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-20 23:25:01'),
(900, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO023: Pending → Cancelled', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-21 00:45:15'),
(901, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 02:26:38'),
(902, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-21 07:19:04'),
(903, 8, NULL, 'create_job_order', 'Created job order #JO025', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-21 07:21:04'),
(904, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO025: Pending → Ongoing', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-21 07:21:10'),
(905, 8, NULL, 'update_job_order_timer', 'Stop timer for job order #JO024 (elapsed: 28699s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-21 07:23:20'),
(906, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 07:53:35'),
(907, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO025 (elapsed: 1957s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 07:53:47'),
(908, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO025: Ongoing → Completed', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 07:53:51'),
(909, 6, NULL, 'update_job_order', 'Updated job order #JO025: Payment method: cash → bank_transfer; Payment status: pending → paid; Partial amount: 0.00 → 1,350.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱1,350.00', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 08:00:28'),
(910, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-21 09:21:03'),
(911, 8, NULL, 'update_job_order', 'Updated job order #JO024: Services/Bundles updated; Products updated', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-21 09:22:51'),
(912, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO024: Ongoing → Completed', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-21 09:23:10'),
(913, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 09:49:52'),
(914, 6, NULL, 'add_expense', 'Added expense entry: ₱389.00 on Sep 21, 2026', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 09:50:26'),
(915, 6, NULL, 'update_job_order', 'Updated job order #JO024: Payment method: cash → gcash; Payment status: pending → paid; Partial amount: 0.00 → 5,500.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱5,500.00', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 09:51:02'),
(916, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-21 23:44:08'),
(917, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-22 05:44:23'),
(918, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-22 08:44:09'),
(919, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-22 23:41:36'),
(920, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-23 03:47:19'),
(921, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-23 03:50:35'),
(922, 5, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c860:1e00:e524:9e8c:22a6:d081', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-23 06:56:41'),
(923, 8, NULL, 'login', 'Staff (service_adviser) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-23 07:11:12'),
(924, 8, NULL, 'create_job_order', 'Created job order #JO026', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-23 07:12:23'),
(925, 8, NULL, 'update_job_order_status', 'Updated status for job order #JO026: Pending → Ongoing', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-23 07:12:31'),
(926, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-23 08:16:49'),
(927, 6, NULL, 'stock_out', 'Stock out: ATF LV MV (STOCKS) (-1)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-23 08:32:55'),
(928, 6, NULL, 'add_manual_income', 'Added manual income: ₱950.00 on Sep 23, 2026', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-23 08:33:22'),
(929, 6, NULL, 'add_expense', 'Added expense entry: ₱384.00 on Sep 23, 2026', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-23 08:33:49'),
(930, 6, NULL, 'add_manual_income', 'Added manual income: ₱750.00 on Sep 23, 2026', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-23 08:34:02'),
(931, 6, NULL, 'add_expense', 'Added expense entry: ₱250.00 on Sep 23, 2026', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-23 08:34:21'),
(932, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-24 03:21:15'),
(933, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-24 03:22:19'),
(934, 6, NULL, 'create_job_order', 'Created job order #JO027', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-24 03:24:11'),
(935, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO027: Pending → Ongoing', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-24 03:24:17'),
(936, 6, NULL, 'update_job_order', 'Updated job order #JO027: Vehicle plate: LAN 7419 → LAN7419; Services/Bundles updated; Products updated', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-24 03:24:30'),
(937, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO026 (elapsed: 72732s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-24 03:24:43'),
(938, 6, NULL, 'update_job_order', 'Updated job order #JO026: Notes: — → ---RECOMMENDATIONS---\n- NEED TO REPLACE SHOCKING MOUNTING AND STAB. LINK; Services/Bundles updated; Products updated', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-24 05:17:30'),
(939, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO026: Ongoing → Completed', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-24 05:17:37'),
(940, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO023: Cancelled → Released', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-24 05:23:45'),
(941, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO023: Released → Cancelled', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-24 05:23:48'),
(942, 6, NULL, 'update_job_order_timer', 'Stop timer for job order #JO027 (elapsed: 7185s)', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-24 05:24:02'),
(943, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-24 06:50:27'),
(944, 6, NULL, 'update_job_order', 'Updated job order #JO026: Services/Bundles updated; Products count: 0 → 1', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-24 06:51:18'),
(945, 6, NULL, 'update_job_order', 'Updated job order #JO026: Payment status: pending → paid; Partial amount: 0.00 → 1,200.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱1,200.00', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-24 06:54:26'),
(946, 6, NULL, 'add_expense', 'Added expense entry: ₱290.00 on Sep 24, 2026', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-24 07:04:14'),
(947, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-24 09:33:09'),
(948, 6, NULL, 'add_expense', 'Added expense entry: ₱790.00 on Sep 24, 2026', '143.44.184.220', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-24 09:33:29'),
(949, 5, NULL, 'login', 'Staff (cashier) logged in successfully', '110.54.207.111', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Mobile Safari/537.36', '2026-09-26 00:26:34'),
(950, 2, NULL, 'login', 'Admin logged in successfully', '110.54.158.251', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Mobile/15E148 Safari/604.1', '2026-09-26 00:44:59'),
(951, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c84e:1700:85d:a1ac:f094:eb8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 01:16:44'),
(952, 2, NULL, 'logout', 'User logged out', '2001:fd8:c84e:1700:85d:a1ac:f094:eb8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 02:02:12'),
(953, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 10:55:44'),
(954, 6, NULL, 'stock_in', 'Stock in: ENGINE OIL 5W-40 (+24)', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:07:27'),
(955, 6, NULL, 'stock_out', 'Stock out: OIL FILTER 415 (-6)', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:08:29'),
(956, 6, NULL, 'stock_out', 'Stock out: OIL FITER 110 (-4)', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:08:52'),
(957, 6, NULL, 'stock_out', 'Stock out: OIL FILTER 111 (-2)', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:09:54'),
(958, 6, NULL, 'create_job_order', 'Created job order #JO028', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:33:21'),
(959, 6, NULL, 'update_job_order', 'Updated job order #JO028: Payment status: pending → paid; Partial amount: 0.00 → 8,000.00; Services/Bundles updated; Products count: 2 → 3; Partial payment: ₱0.00 → ₱8,000.00', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:37:55'),
(960, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO028: Pending → Completed', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:38:03'),
(961, 6, NULL, 'add_expense', 'Added expense entry: ₱995.00 on Sep 26, 2026', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:39:25'),
(962, 6, NULL, 'add_expense', 'Added expense entry: ₱1,720.00 on Sep 26, 2026', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:40:41'),
(963, 6, NULL, 'create_job_order', 'Created job order #JO029', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:47:58'),
(964, 6, NULL, 'update_job_order_status', 'Updated status for job order #JO029: Pending → Completed', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:48:11'),
(965, 6, NULL, 'update_job_order', 'Updated job order #JO029: Payment status: pending → paid; Partial amount: 0.00 → 5,550.00; Services/Bundles updated; Products updated; Partial payment: ₱0.00 → ₱5,550.00', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:48:27'),
(966, 6, NULL, 'update_job_order', 'Updated job order #JO029: Services/Bundles updated; Products updated; Partial payment: ₱5,550.00 → ₱5,550.00', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 11:49:06'),
(967, 6, NULL, 'update_job_order', 'Updated job order #JO029: Services/Bundles updated; Products updated; Partial payment: ₱5,550.00 → ₱5,550.00', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 12:10:22'),
(968, 6, NULL, 'add_expense', 'Added expense entry: ₱200.00 on Sep 26, 2026', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 12:10:52'),
(969, 6, NULL, 'add_manual_income', 'Added manual income: ₱40,000.00 on Sep 26, 2026', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 12:19:26'),
(970, 6, NULL, 'add_expense', 'Added expense entry: ₱28,253.00 on Sep 26, 2026', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 12:21:18'),
(971, 6, NULL, 'add_expense', 'Added expense entry: ₱5,185.00 on Sep 26, 2026', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 12:21:54'),
(972, 6, NULL, 'add_manual_income', 'Added manual income: ₱300.00 on Sep 26, 2026', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 12:22:41'),
(973, 6, NULL, 'add_expense', 'Added expense entry: ₱46.00 on Sep 26, 2026', '2001:fd8:c84e:1700:ac70:b309:ce7:ef0d', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-09-26 12:22:59'),
(974, 6, NULL, 'login', 'Staff (cashier) logged in successfully', '143.44.184.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-28 03:23:05'),
(975, 6, NULL, 'create_job_order', 'Created job order #JO030', '143.44.184.89', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '2026-09-28 03:39:50'),
(976, 2, NULL, 'login', 'Admin logged in successfully', '138.84.112.47', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', '2026-09-29 02:25:15'),
(977, 2, NULL, 'logout', 'User logged out', '138.84.112.47', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', '2026-09-29 02:25:29'),
(978, 2, NULL, 'login', 'Admin logged in successfully', '2001:fd8:c84e:7500:3d61:97ff:2180:ff95', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', '2026-09-29 02:49:07'),
(979, 2, NULL, 'logout', 'User logged out', '2001:fd8:c84e:7500:3d61:97ff:2180:ff95', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Safari/605.1.15', '2026-09-29 02:49:13');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time DEFAULT NULL,
  `photo_in` varchar(255) DEFAULT NULL,
  `photo_out` varchar(255) DEFAULT NULL,
  `status` enum('present','late','absent','on_leave','other') NOT NULL DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bundle_products`
--

CREATE TABLE `bundle_products` (
  `id` int(11) NOT NULL,
  `bundle_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bundle_services`
--

CREATE TABLE `bundle_services` (
  `id` int(11) NOT NULL,
  `bundle_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `csrf_tokens`
--

CREATE TABLE `csrf_tokens` (
  `id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_code` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_code`, `full_name`, `phone`, `email`, `address`, `created_at`, `updated_at`) VALUES
(1, 'CUST-2026-0001', 'Dj Cortez', '343245234', 'owwkxi@gmail.com', NULL, '2026-08-09 13:00:00', '2026-08-09 13:00:00'),
(2, 'CUST-2026-0002', 'Dj Cortez', '324234', 'owwkxi@gmail.com', NULL, '2026-08-09 13:00:23', '2026-08-09 13:00:23'),
(3, 'CUST-2026-0003', 'Dj Cortez', '3425234', 'owwkxi@gmail.com', NULL, '2026-08-09 13:10:32', '2026-08-09 13:10:32'),
(4, 'CUST-2026-0004', 'Dj Cortez', '243242', 'owwkxi@gmail.com', NULL, '2026-08-09 14:31:06', '2026-08-09 14:31:06'),
(5, 'CUST-2026-0005', 'sample', '0922', NULL, NULL, '2026-08-13 01:45:49', '2026-08-13 01:45:49'),
(6, 'CUST-2026-0006', 'AIAN', '09363408302', NULL, 'TORRES', '2026-08-31 23:49:27', '2026-08-31 23:49:27'),
(7, 'CUST-2026-0007', 'JEHELLE CLOMA', '0991 650 8344', NULL, NULL, '2026-09-01 01:20:27', '2026-09-01 01:20:27'),
(8, 'CUST-2026-0008', 'REYNALD BARCENA', '0976 460 9296', NULL, NULL, '2026-09-01 02:20:23', '2026-09-01 02:20:23'),
(9, 'CUST-2026-0009', 'MARICHU LEONES MENDOZA', '0995 611 5639', NULL, NULL, '2026-09-01 08:07:46', '2026-09-02 05:11:46'),
(10, 'CUST-2026-0010', 'KUYA BONG', '09215053257', NULL, NULL, '2026-09-02 00:50:35', '2026-09-03 23:49:55'),
(11, 'CUST-2026-0011', 'EMEL PAUL L. DUAVES', '0995 613 4341', NULL, NULL, '2026-09-02 03:59:20', '2026-09-02 03:59:20'),
(12, 'CUST-2026-0012', 'JHON DELOS SANTOS', '09158097434', NULL, 'BLK 2,LOT 21,ISAIAH ST.SUSANA', '2026-09-04 01:52:57', '2026-09-04 01:52:57'),
(13, 'CUST-2026-0013', 'ROY', '09471474971', NULL, NULL, '2026-09-04 03:59:14', '2026-09-04 03:59:14'),
(14, 'CUST-2026-0014', 'CATABAY REYNAN', '09918355655', NULL, NULL, '2026-09-05 01:37:01', '2026-09-05 01:37:01'),
(15, 'CUST-2026-0015', 'ERNESTO', '09228404165', NULL, NULL, '2026-09-05 06:42:24', '2026-09-05 06:42:24'),
(16, 'CUST-2026-0016', 'JADE OLITA', '09179398056', NULL, NULL, '2026-09-07 02:14:38', '2026-09-07 02:14:38'),
(17, 'CUST-2026-0017', 'EMEL PAUL L. DUAVES', '09956134341', NULL, NULL, '2026-09-08 00:24:35', '2026-09-08 00:24:35'),
(18, 'CUST-2026-0018', 'JADE', '09988243233', NULL, NULL, '2026-09-08 04:58:09', '2026-09-08 04:58:09'),
(19, 'CUST-2026-0019', 'AHLIA MAMANGKAO', '09984749255', NULL, NULL, '2026-09-08 06:34:37', '2026-09-09 06:13:05'),
(20, 'CUST-2026-0020', 'JADE', '09988243233', NULL, NULL, '2026-09-09 06:31:30', '2026-09-09 06:31:30'),
(21, 'CUST-2026-0021', 'ERNESTO', '09228404165', NULL, NULL, '2026-09-10 07:22:06', '2026-09-10 07:22:06'),
(22, 'CUST-2026-0022', 'CAMILLES TUZON', '09000000000', '-', 'BULUSAN BANGKAL DC', '2026-09-12 03:10:10', '2026-09-12 03:15:39'),
(23, 'CUST-2026-0023', 'ALEXANDER GUEVARA', '09053780582', NULL, 'BANGKAL, DAVAO CITY', '2026-09-12 07:06:00', '2026-09-12 07:06:00'),
(24, 'CUST-2026-0024', 'CHRISTIAN MIOLE', '09912137009', NULL, NULL, '2026-09-15 00:37:54', '2026-09-15 00:37:54'),
(25, 'CUST-2026-0025', 'GURES,ROMEL', '09182923532', NULL, NULL, '2026-09-16 05:08:16', '2026-09-16 05:08:16'),
(26, 'CUST-2026-0026', 'LUCKY YAP', '09171665888', NULL, NULL, '2026-09-17 01:53:34', '2026-09-17 01:53:34'),
(27, 'CUST-2026-0027', 'PENON MARK PACATANG', '09176114306', NULL, NULL, '2026-09-17 02:01:52', '2026-09-17 02:01:52'),
(28, 'CUST-2026-0028', 'MICHAEL', '09120461264', NULL, NULL, '2026-09-20 23:23:15', '2026-09-20 23:23:15'),
(29, 'CUST-2026-0029', 'MICHAEL', '09120461264', NULL, NULL, '2026-09-20 23:23:15', '2026-09-20 23:23:15'),
(30, 'CUST-2026-0030', 'CHRISTIAN MIOLE', '09912137009', NULL, NULL, '2026-09-21 07:21:04', '2026-09-21 07:21:04'),
(31, 'CUST-2026-0031', 'PAO COBRADO', '09455442179', NULL, NULL, '2026-09-23 07:12:23', '2026-09-23 07:12:23'),
(32, 'CUST-2026-0032', 'FLY ACE CORPORATION', '09190742583', NULL, NULL, '2026-09-24 03:24:11', '2026-09-24 03:24:11'),
(33, 'CUST-2026-0033', 'RAUL  OLE', '09702724236', NULL, NULL, '2026-09-26 11:33:21', '2026-09-26 11:33:21'),
(34, 'CUST-2026-0034', 'MARLON LAVA BERDIN', '09758510835', NULL, NULL, '2026-09-26 11:47:58', '2026-09-26 11:47:58'),
(35, 'CUST-2026-0035', 'RONALD', '09502209659', NULL, 'MATINA PANGI DAVAO CITY', '2026-09-28 03:39:50', '2026-09-28 03:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `transaction_type` enum('stock_in','stock_out','adjustment','return') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'job_order, purchase_order, etc',
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`id`, `product_id`, `transaction_type`, `quantity`, `reference_type`, `reference_id`, `notes`, `created_by`, `created_at`) VALUES
(1, 8, 'stock_in', 1, NULL, NULL, '', 5, '2026-08-13 08:59:04'),
(2, 8, 'stock_in', 19, NULL, NULL, '', 5, '2026-08-13 08:59:13'),
(3, 9, 'stock_in', 20, NULL, NULL, '', 5, '2026-08-13 08:59:23'),
(4, 10, 'stock_in', 20, NULL, NULL, '', 5, '2026-08-13 08:59:32'),
(5, 11, 'stock_in', 20, NULL, NULL, '', 5, '2026-08-13 08:59:40'),
(6, 12, 'stock_in', 20, NULL, NULL, '', 5, '2026-08-13 09:01:17'),
(7, 13, 'stock_in', 20, NULL, NULL, '', 5, '2026-08-13 09:06:23'),
(8, 14, 'stock_in', 10, NULL, NULL, '', 5, '2026-08-13 09:11:55'),
(9, 6, 'stock_out', 20, NULL, NULL, '', 6, '2026-08-17 01:43:33'),
(10, 13, 'stock_out', 11, NULL, NULL, '', 6, '2026-08-17 01:43:57'),
(11, 7, 'stock_out', 10, NULL, NULL, '', 6, '2026-08-17 01:44:22'),
(12, 8, 'stock_out', 10, NULL, NULL, '', 6, '2026-08-17 01:44:49'),
(13, 12, 'stock_out', 17, NULL, NULL, '', 6, '2026-08-17 01:45:34'),
(14, 20, 'stock_in', 4, NULL, NULL, '', 6, '2026-08-17 01:46:42'),
(15, 9, 'stock_out', 19, NULL, NULL, '', 6, '2026-08-17 01:47:19'),
(16, 28, 'stock_in', 9, NULL, NULL, '', 6, '2026-08-17 01:51:40'),
(17, 5, 'stock_out', 20, NULL, NULL, '', 6, '2026-08-17 02:06:18'),
(18, 4, 'stock_out', 20, NULL, NULL, '', 6, '2026-08-17 02:07:39'),
(19, 28, 'stock_out', 1, NULL, NULL, '', 6, '2026-08-17 02:15:57'),
(20, 2, 'stock_in', 1, NULL, NULL, '', 6, '2026-08-17 02:37:58'),
(21, 2, 'stock_out', 20, NULL, NULL, '', 6, '2026-08-17 02:38:07'),
(22, 35, 'stock_in', 1, NULL, NULL, '', 6, '2026-08-17 02:41:13'),
(23, 10, 'stock_out', 18, NULL, NULL, '', 6, '2026-08-17 02:44:03'),
(24, 10, 'stock_in', 5, NULL, NULL, '', 6, '2026-08-17 02:44:32'),
(25, 34, 'stock_in', 1, NULL, NULL, '', 6, '2026-08-17 02:46:39'),
(26, 15, 'stock_in', 3, NULL, NULL, '', 6, '2026-08-17 02:49:21'),
(27, 14, 'stock_out', 8, NULL, NULL, '', 6, '2026-08-17 02:52:34'),
(28, 29, 'stock_in', 4, NULL, NULL, '', 6, '2026-08-17 02:54:22'),
(29, 24, 'stock_in', 1, NULL, NULL, '', 6, '2026-08-17 02:55:44'),
(30, 23, 'stock_in', 3, NULL, NULL, '', 6, '2026-08-17 02:56:57'),
(31, 16, 'stock_in', 3, NULL, NULL, '', 6, '2026-08-17 02:58:20'),
(32, 6, 'stock_in', 10, NULL, NULL, '', 6, '2026-09-01 01:20:08'),
(33, 6, 'stock_out', 4, 'job_order', 7, 'Started JO #JO002 - stock deducted', 8, '2026-09-01 01:23:08'),
(34, 7, 'stock_out', 1, 'job_order', 7, 'Started JO #JO002 - stock deducted', 8, '2026-09-01 01:23:08'),
(35, 7, 'return', 1, 'job_order', 7, 'Edit qty reduced JO #JO002', 6, '2026-09-01 02:28:00'),
(36, 13, 'stock_out', 4, NULL, NULL, '', 6, '2026-09-07 01:48:40'),
(37, 12, 'stock_in', 19, NULL, NULL, '', 6, '2026-09-07 01:50:00'),
(38, 38, 'stock_in', 4, NULL, NULL, '', 6, '2026-09-09 08:43:37'),
(39, 38, 'stock_out', 1, NULL, NULL, '', 6, '2026-09-09 08:47:51'),
(40, 4, 'stock_in', 1, NULL, NULL, '', 6, '2026-09-12 07:06:31'),
(41, 30, 'stock_in', 1, NULL, NULL, '', 6, '2026-09-12 07:08:26'),
(42, 5, 'stock_in', 1, NULL, NULL, '', 6, '2026-09-12 10:06:24'),
(43, 19, 'stock_in', 3, NULL, NULL, '', 6, '2026-09-12 10:38:14'),
(44, 17, 'stock_in', 3, NULL, NULL, '', 6, '2026-09-12 10:38:31'),
(45, 12, 'stock_out', 1, 'job_order', 25, 'Started JO #JO020 - stock deducted', 8, '2026-09-16 05:08:21'),
(46, 6, 'stock_out', 3, NULL, NULL, '', 6, '2026-09-16 06:07:53'),
(47, 13, 'stock_in', 2, NULL, NULL, '', 6, '2026-09-16 06:08:05'),
(48, 19, 'stock_out', 2, NULL, NULL, '', 6, '2026-09-16 06:08:22'),
(49, 20, 'stock_out', 3, NULL, NULL, '', 6, '2026-09-16 06:08:28'),
(50, 9, 'stock_in', 2, NULL, NULL, '', 6, '2026-09-16 06:08:50'),
(51, 15, 'stock_out', 1, NULL, NULL, '', 6, '2026-09-16 06:09:18'),
(52, 12, 'stock_in', 2, NULL, NULL, '', 6, '2026-09-16 06:09:53'),
(53, 9, 'stock_out', 1, NULL, NULL, '', 6, '2026-09-23 08:32:55'),
(54, 13, 'stock_in', 24, NULL, NULL, '', 6, '2026-09-26 11:07:27'),
(55, 7, 'stock_out', 6, NULL, NULL, '', 6, '2026-09-26 11:08:29'),
(56, 8, 'stock_out', 4, NULL, NULL, '', 6, '2026-09-26 11:08:52'),
(57, 10, 'stock_out', 2, NULL, NULL, '', 6, '2026-09-26 11:09:54'),
(58, 12, 'stock_out', 1, 'job_order', 33, 'Started JO #JO028 - stock deducted', 6, '2026-09-26 11:38:03'),
(59, 13, 'stock_out', 7, 'job_order', 34, 'Started JO #JO029 - stock deducted', 6, '2026-09-26 11:48:08'),
(60, 10, 'stock_out', 1, 'job_order', 34, 'Started JO #JO029 - stock deducted', 6, '2026-09-26 11:48:08');

-- --------------------------------------------------------

--
-- Table structure for table `job_estimates`
--

CREATE TABLE `job_estimates` (
  `id` int(11) NOT NULL,
  `estimate_number` varchar(20) NOT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `vehicle_make` varchar(100) DEFAULT NULL,
  `vehicle_model` varchar(100) DEFAULT NULL,
  `vehicle_year` varchar(20) DEFAULT NULL,
  `vehicle_plate` varchar(50) DEFAULT NULL,
  `vehicle_color` varchar(50) DEFAULT NULL,
  `vehicle_mileage` varchar(50) DEFAULT NULL,
  `services_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `products_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` varchar(20) DEFAULT 'none',
  `discount_value` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `recommendations_json` text DEFAULT NULL,
  `services_json` text NOT NULL,
  `products_json` text NOT NULL,
  `status` enum('draft','sent','approved','rejected','converted') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_estimates`
--

INSERT INTO `job_estimates` (`id`, `estimate_number`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `vehicle_make`, `vehicle_model`, `vehicle_year`, `vehicle_plate`, `vehicle_color`, `vehicle_mileage`, `services_total`, `products_total`, `grand_total`, `discount_type`, `discount_value`, `notes`, `recommendations_json`, `services_json`, `products_json`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'JE001', 'AIAN', '09363408302', '', 'TORRES', 'MONTERO', 'MITSUBISHI', '2010', 'LGT291', 'SILVER GRAY', '238,976', 1200.00, 450.00, 1650.00, 'none', 0.00, '', '[]', '[{\"id\":0,\"type\":\"custom\",\"name\":\"CHECK UP BRAKES\",\"price\":1200,\"base_price\":0,\"labor_cost\":1200,\"qty\":1,\"selectedSubItems\":[]}]', '[{\"id\":12,\"name\":\"BRAKE CLEANER\",\"code\":\"PRD11\",\"price\":450,\"qty\":1}]', 'converted', 8, '2026-08-31 23:47:08', '2026-08-31 23:49:00'),
(3, 'JE002', '', '', '', '', 'TOYOTA', 'INNOVA', '2018', 'LAA7677', 'SIVER GRAY', '164,228KM', 6700.00, 11200.00, 17900.00, 'none', 0.00, '', '[]', '[{\"id\":0,\"type\":\"custom\",\"name\":\"1. PULLOUT ALTERNATOR ASSY-REPLACE ALTERNATOR PULLEY\",\"price\":2200,\"base_price\":0,\"labor_cost\":2200,\"qty\":1,\"selectedSubItems\":[]},{\"id\":0,\"type\":\"custom\",\"name\":\"2. DISASSEMBLE ALTERNATOR ASSY-REPLACE BEARING (INTERNAL)\",\"price\":4500,\"base_price\":0,\"labor_cost\":4500,\"qty\":1,\"selectedSubItems\":[]}]', '[{\"id\":0,\"name\":\"1. ALTERNATOR PULLEY\",\"code\":\"\",\"price\":7800,\"qty\":1},{\"id\":0,\"name\":\"2. ALTERNATOR BEARING\",\"code\":\"\",\"price\":3400,\"qty\":1}]', 'draft', 8, '2026-09-01 00:56:09', '2026-09-01 00:56:09'),
(4, 'JE003', '', '', '', '', 'TOYOTA', 'WIGO', '2017', '', '', '', 3450.00, 3350.00, 6800.00, 'none', 0.00, '', '[]', '[{\"id\":6,\"type\":\"service\",\"name\":\"REGULAR PMS\",\"price\":3450,\"base_price\":0,\"labor_cost\":3450,\"qty\":1,\"selectedSubItems\":[\"CHANGE OIL\",\"CHANGE OIL FILTER\",\"BRAKE CLEANING\\/ADJUST\",\"CLEANING AIR\\/CABIN FILTER\",\"CLEANING SPARK PLUG\",\"BOLT AND NUT TIGHTENING\",\"CHECK UNDERCHASSIS\",\"CHECK FLUID\",\"CHECK LIGHTS\",\"CHECK TIRES\",\"CHECK BATTERY CONDITION\",\"CHECK BELTS\",\"FREE SCANNING\"]}]', '[{\"id\":6,\"name\":\"ENGINE OIL 5W-30\",\"code\":\"PRD05\",\"price\":600,\"qty\":4,\"stock\":6},{\"id\":7,\"name\":\"OIL FILTER 415\",\"code\":\"PRD06\",\"price\":500,\"qty\":1,\"stock\":10},{\"id\":12,\"name\":\"BRAKE CLEANER\",\"code\":\"PRD11\",\"price\":450,\"qty\":1,\"stock\":3}]', 'draft', 6, '2026-09-03 06:53:19', '2026-09-03 06:53:19');

-- --------------------------------------------------------

--
-- Table structure for table `job_orders`
--

CREATE TABLE `job_orders` (
  `id` int(11) NOT NULL,
  `job_order_number` varchar(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `service_adviser_id` int(11) DEFAULT NULL,
  `status` enum('pending','ongoing','under_inspection','car_washing','completed','released','returned_for_revision','cancelled') NOT NULL DEFAULT 'pending',
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `labor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `parts_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('none','senior_citizen','pwd','promotional','custom') DEFAULT 'none',
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `partial_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `payment_method` enum('cash','card','bank_transfer','gcash','paymaya') DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `estimated_completion` datetime DEFAULT NULL,
  `actual_completion` datetime DEFAULT NULL,
  `status_timer_seconds` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status_timer_started_at` datetime DEFAULT NULL,
  `work_started_at` datetime DEFAULT NULL,
  `inspection_started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_by_type` enum('user','staff') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_orders`
--

INSERT INTO `job_orders` (`id`, `job_order_number`, `customer_id`, `vehicle_id`, `service_adviser_id`, `status`, `priority`, `subtotal`, `labor_total`, `parts_total`, `discount_type`, `discount_amount`, `discount_percentage`, `partial_amount`, `total_amount`, `payment_status`, `payment_method`, `payment_date`, `notes`, `estimated_completion`, `actual_completion`, `status_timer_seconds`, `status_timer_started_at`, `work_started_at`, `inspection_started_at`, `completed_at`, `created_by`, `created_by_type`, `created_at`, `updated_at`) VALUES
(6, 'JO001', 6, 6, 15, 'cancelled', 'normal', 0.00, 0.00, 0.00, 'none', 0.00, 0.00, 0.00, 0.00, 'pending', 'cash', NULL, 'NEED TO CHECK', NULL, NULL, 0, NULL, NULL, NULL, NULL, 8, 'user', '2026-08-31 23:49:27', '2026-09-01 02:41:57'),
(7, 'JO002', 7, 7, 11, 'released', 'normal', 2800.00, 0.00, 3000.00, 'none', 0.00, 0.00, 5800.00, 5800.00, 'paid', 'cash', '2026-09-01 15:44:55', '', NULL, NULL, 5227, NULL, '2026-09-01 09:23:08', NULL, NULL, 8, 'user', '2026-09-01 01:20:27', '2026-09-09 00:58:47'),
(8, 'JO003', 8, 8, 11, 'released', 'normal', 12500.00, 0.00, 0.00, 'none', 0.00, 0.00, 12500.00, 12500.00, 'paid', 'bank_transfer', '2026-09-01 14:47:13', '', NULL, NULL, 11190, NULL, '2026-09-01 10:22:59', NULL, NULL, 8, 'user', '2026-09-01 02:20:23', '2026-09-09 00:58:51'),
(9, 'JO004', 9, 9, 11, 'released', 'normal', 4000.00, 0.00, 0.00, 'none', 0.00, 0.00, 4000.00, 4000.00, 'paid', 'cash', '2026-09-03 17:57:12', '', NULL, NULL, 12679, NULL, '2026-09-01 16:08:39', NULL, NULL, 8, 'user', '2026-09-01 08:07:46', '2026-09-09 00:58:53'),
(10, 'JO005', 10, 10, 11, 'released', 'normal', 10000.00, 0.00, 1800.00, 'none', 0.00, 0.00, 11800.00, 11800.00, 'paid', 'bank_transfer', '2026-09-04 08:50:04', '', NULL, NULL, 40926, NULL, '2026-09-02 08:50:43', NULL, NULL, 8, 'user', '2026-09-02 00:50:35', '2026-09-09 00:58:57'),
(11, 'JO006', 11, 11, 10, 'released', 'normal', 3700.00, 0.00, 2000.00, 'none', 0.00, 0.00, 5700.00, 5700.00, 'paid', 'cash', '2026-09-03 17:56:57', '', NULL, NULL, 12072, NULL, '2026-09-02 14:46:25', NULL, NULL, 8, 'user', '2026-09-02 03:59:20', '2026-09-09 00:59:00'),
(12, 'JO007', 12, 12, 9, 'completed', 'normal', 3500.00, 0.00, 7600.00, 'custom', 900.00, 0.00, 10200.00, 10200.00, 'paid', 'bank_transfer', '2026-09-09 10:33:18', '', NULL, NULL, 13088, NULL, '2026-09-04 09:53:02', NULL, '2026-09-09 10:32:26', 8, 'user', '2026-09-04 01:52:57', '2026-09-09 02:33:18'),
(13, 'JO008', 13, 13, 10, 'released', 'normal', 8100.00, 0.00, 30120.00, 'custom', 1500.00, 0.00, 36720.00, 36720.00, 'paid', 'cash', '2026-09-05 17:48:33', '', NULL, NULL, 11564, NULL, '2026-09-04 15:36:50', NULL, NULL, 8, 'user', '2026-09-04 03:59:14', '2026-09-09 00:59:02'),
(14, 'JO009', 14, 14, 10, 'released', 'normal', 2800.00, 0.00, 5500.00, 'custom', 300.00, 0.00, 8000.00, 8000.00, 'paid', 'cash', '2026-09-05 14:33:22', '', NULL, NULL, 9652, NULL, '2026-09-05 09:37:05', NULL, NULL, 8, 'user', '2026-09-05 01:37:01', '2026-09-09 00:59:05'),
(15, 'JO010', 15, 15, 12, 'released', 'normal', 2800.00, 0.00, 5150.00, 'none', 0.00, 0.00, 7950.00, 7950.00, 'paid', 'bank_transfer', '2026-09-07 09:47:30', '---RECOMMENDATIONS---\n- NOTE: NEED TO REPLACE REAR BRAKE SHOE (BELOW 4MM)', NULL, NULL, 2634, NULL, '2026-09-05 14:42:27', NULL, NULL, 8, 'user', '2026-09-05 06:42:24', '2026-09-09 00:59:12'),
(16, 'JO011', 16, 16, 11, 'completed', 'normal', 1200.00, 0.00, 0.00, 'none', 0.00, 0.00, 1200.00, 1200.00, 'paid', 'cash', '2026-09-07 11:39:02', '', NULL, NULL, 4333, NULL, '2026-09-07 10:15:11', NULL, '2026-09-07 11:35:13', 8, 'user', '2026-09-07 02:14:38', '2026-09-07 03:39:02'),
(17, 'JO012', 17, 17, 11, 'completed', 'normal', 3000.00, 0.00, 0.00, 'none', 0.00, 0.00, 3000.00, 3000.00, 'paid', 'cash', '2026-09-09 08:58:39', '', NULL, NULL, 6714, NULL, '2026-09-08 08:25:02', NULL, '2026-09-09 08:58:16', 8, 'user', '2026-09-08 00:24:35', '2026-09-09 00:58:39'),
(18, 'JO013', 18, 18, 9, 'completed', 'normal', 1500.00, 0.00, 4250.00, 'custom', 250.00, 0.00, 5500.00, 5500.00, 'paid', 'cash', '2026-09-08 14:12:07', '---RECOMMENDATIONS---\n- NEED TO REPLACE RACK END BOTH SIDES\n- NEED TO PERFORM WHEEL ALIGNMENT', NULL, NULL, 2509, NULL, '2026-09-08 12:58:15', NULL, '2026-09-08 13:40:06', 8, 'user', '2026-09-08 04:58:09', '2026-09-08 06:12:07'),
(19, 'JO014', 19, 19, 13, 'completed', 'normal', 5100.00, 0.00, 4200.00, 'none', 0.00, 0.00, 9300.00, 9300.00, 'paid', 'cash', '2026-09-10 11:51:57', '', NULL, NULL, 11158, NULL, '2026-09-08 14:34:43', NULL, '2026-09-10 11:51:39', 8, 'user', '2026-09-08 06:34:37', '2026-09-10 03:51:57'),
(20, 'JO015', 20, 20, 9, 'completed', 'normal', 1800.00, 0.00, 2850.00, 'none', 0.00, 0.00, 4650.00, 4650.00, 'paid', 'cash', '2026-09-09 16:44:30', '', NULL, NULL, 5291, NULL, '2026-09-09 14:31:48', NULL, '2026-09-09 15:59:59', 8, 'user', '2026-09-09 06:31:30', '2026-09-09 08:44:30'),
(21, 'JO016', 21, 21, 13, 'completed', 'normal', 1200.00, 0.00, 0.00, 'none', 0.00, 0.00, 1200.00, 1200.00, 'paid', 'cash', '2026-09-11 17:55:11', '', NULL, NULL, 64447, NULL, '2026-09-10 15:22:12', NULL, '2026-09-11 09:16:48', 8, 'user', '2026-09-10 07:22:06', '2026-09-11 09:55:11'),
(22, 'JO017', 22, 22, 9, 'completed', 'normal', 800.00, 0.00, 0.00, 'none', 0.00, 0.00, 800.00, 800.00, 'paid', 'cash', '2026-09-12 17:15:45', '', NULL, NULL, 13454, NULL, '2026-09-12 11:18:00', NULL, '2026-09-12 15:02:14', 6, 'user', '2026-09-12 03:10:10', '2026-09-12 09:15:45'),
(23, 'JO018', 23, 23, NULL, 'completed', 'normal', 3800.00, 0.00, 5200.00, 'custom', 500.00, 0.00, 8500.00, 8500.00, 'paid', 'cash', '2026-09-15 08:51:35', '---RECOMMENDATIONS---\n- NOTE: NEED TO REPLACE CLAMP BUSHING AND LINKIT RH/LH\n- NEED TO REPLACE FRONT SHOCK ABSORBER RH/LH', NULL, NULL, 25823, NULL, '2026-09-12 17:15:52', NULL, '2026-09-15 07:26:57', 6, 'user', '2026-09-12 07:06:00', '2026-09-15 00:51:35'),
(24, 'JO019', 24, 24, 9, 'cancelled', 'normal', 0.00, 0.00, 0.00, 'none', 0.00, 0.00, 0.00, 0.00, 'pending', 'cash', NULL, '', NULL, NULL, 9765, NULL, '2026-09-15 08:37:58', NULL, NULL, 8, 'user', '2026-09-15 00:37:54', '2026-09-15 23:36:46'),
(25, 'JO020', 25, 25, 10, 'completed', 'normal', 2800.00, 0.00, 10950.00, 'none', 0.00, 0.00, 0.00, 13750.00, 'pending', 'cash', NULL, '', NULL, NULL, 9033, NULL, '2026-09-16 13:08:21', NULL, '2026-09-16 16:00:38', 8, 'user', '2026-09-16 05:08:16', '2026-09-16 08:00:38'),
(26, 'JO021', 26, 26, 13, 'completed', 'normal', 4000.00, 0.00, 0.00, 'none', 0.00, 0.00, 4000.00, 4000.00, 'paid', 'cash', '2026-09-18 09:58:50', '', NULL, NULL, 6296, NULL, '2026-09-17 09:53:46', NULL, '2026-09-18 09:20:09', 8, 'user', '2026-09-17 01:53:34', '2026-09-18 01:58:50'),
(27, 'JO022', 27, 27, 10, 'completed', 'normal', 6200.00, 0.00, 5300.00, 'none', 0.00, 0.00, 0.00, 11500.00, 'pending', 'cash', NULL, '', NULL, NULL, 5803, NULL, '2026-09-17 10:01:56', NULL, '2026-09-17 16:57:49', 8, 'user', '2026-09-17 02:01:52', '2026-09-17 08:57:49'),
(28, 'JO023', 28, 28, 10, 'cancelled', 'normal', 0.00, 0.00, 0.00, 'none', 0.00, 0.00, 0.00, 0.00, 'pending', 'cash', NULL, '', NULL, NULL, 0, NULL, NULL, NULL, NULL, 8, 'user', '2026-09-20 23:23:15', '2026-09-24 05:23:48'),
(29, 'JO024', 29, 29, 10, 'completed', 'normal', 6500.00, 0.00, 0.00, 'custom', 1000.00, 0.00, 5500.00, 5500.00, 'paid', 'gcash', '2026-09-21 17:51:02', '', NULL, NULL, 28699, NULL, '2026-09-21 07:25:01', NULL, '2026-09-21 17:23:09', 8, 'user', '2026-09-20 23:23:15', '2026-09-21 09:51:02'),
(30, 'JO025', 30, 30, 10, 'completed', 'normal', 1350.00, 0.00, 0.00, 'none', 0.00, 0.00, 1350.00, 1350.00, 'paid', 'bank_transfer', '2026-09-21 16:00:28', '', NULL, NULL, 1957, NULL, '2026-09-21 15:21:10', NULL, '2026-09-21 15:53:50', 8, 'user', '2026-09-21 07:21:04', '2026-09-21 08:00:28'),
(31, 'JO026', 31, 31, 10, 'completed', 'normal', 1200.00, 0.00, 0.00, 'none', 0.00, 0.00, 1200.00, 1200.00, 'paid', 'cash', '2026-09-24 14:54:26', '---RECOMMENDATIONS---\n- NEED TO REPLACE SHOCKING MOUNTING AND STAB. LINK', NULL, NULL, 72732, NULL, '2026-09-23 15:12:31', NULL, '2026-09-24 13:17:36', 8, 'user', '2026-09-23 07:12:23', '2026-09-24 06:54:26'),
(32, 'JO027', 32, 32, 9, 'ongoing', 'normal', 0.00, 0.00, 0.00, 'none', 0.00, 0.00, 0.00, 0.00, 'pending', 'cash', NULL, '', NULL, NULL, 7185, NULL, '2026-09-24 11:24:17', NULL, NULL, 6, 'user', '2026-09-24 03:24:11', '2026-09-24 05:24:02'),
(33, 'JO028', 33, 33, 11, 'completed', 'normal', 2800.00, 0.00, 5250.00, 'custom', 50.00, 0.00, 8000.00, 8000.00, 'paid', 'cash', '2026-09-26 19:37:55', '', NULL, NULL, 0, NULL, NULL, NULL, '2026-09-26 19:38:03', 6, 'user', '2026-09-26 11:33:21', '2026-09-26 11:38:03'),
(34, 'JO029', 34, 34, 9, 'completed', 'normal', 850.00, 0.00, 4700.00, 'none', 0.00, 0.00, 5550.00, 5550.00, 'paid', 'cash', '2026-09-26 20:10:22', '', NULL, NULL, 0, NULL, NULL, NULL, '2026-09-26 19:48:08', 6, 'user', '2026-09-26 11:47:58', '2026-09-26 12:10:22'),
(35, 'JO030', 35, 35, NULL, 'pending', 'normal', 0.00, 0.00, 0.00, 'none', 0.00, 0.00, 0.00, 0.00, 'pending', 'cash', NULL, '', NULL, NULL, 0, NULL, NULL, NULL, NULL, 6, 'user', '2026-09-28 03:39:50', '2026-09-28 03:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `job_order_approvals`
--

CREATE TABLE `job_order_approvals` (
  `id` int(11) NOT NULL,
  `job_order_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewer_role` enum('service_adviser','chief_mechanic') NOT NULL,
  `status` enum('approved','needs_revision','rework_required') NOT NULL,
  `comments` text DEFAULT NULL,
  `reviewed_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_order_inspections`
--

CREATE TABLE `job_order_inspections` (
  `id` int(11) NOT NULL,
  `job_order_id` int(11) NOT NULL,
  `result` enum('pass','revision') NOT NULL,
  `inspected_by` int(11) DEFAULT NULL,
  `inspected_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_order_payments`
--

CREATE TABLE `job_order_payments` (
  `id` int(11) NOT NULL,
  `job_order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','gcash','paymaya','bank_transfer') NOT NULL DEFAULT 'cash',
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_by` varchar(100) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `payment_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_order_payments`
--

INSERT INTO `job_order_payments` (`id`, `job_order_id`, `amount`, `payment_method`, `reference_number`, `notes`, `paid_by`, `created_by`, `payment_date`, `created_at`) VALUES
(1, 8, 12500.00, 'bank_transfer', '', NULL, NULL, 6, '2026-09-01 14:47:13', '2026-09-01 06:47:13'),
(2, 7, 5800.00, 'cash', '', NULL, NULL, 6, '2026-09-01 15:44:55', '2026-09-01 07:44:55'),
(3, 11, 5700.00, 'cash', '', NULL, NULL, 6, '2026-09-03 17:56:57', '2026-09-03 09:56:57'),
(4, 9, 4000.00, 'cash', '', NULL, NULL, 6, '2026-09-03 17:57:12', '2026-09-03 09:57:12'),
(5, 10, 11800.00, 'bank_transfer', '', NULL, NULL, 6, '2026-09-04 08:50:04', '2026-09-04 00:50:04'),
(6, 14, 8000.00, 'cash', '', NULL, NULL, 6, '2026-09-05 14:33:22', '2026-09-05 06:33:22'),
(7, 13, 36720.00, 'cash', '', NULL, NULL, 6, '2026-09-05 17:48:33', '2026-09-05 09:48:33'),
(8, 15, 7950.00, 'bank_transfer', '', NULL, NULL, 6, '2026-09-07 09:47:30', '2026-09-07 01:47:30'),
(9, 16, 1200.00, 'cash', '', NULL, NULL, 6, '2026-09-07 11:39:02', '2026-09-07 03:39:02'),
(10, 18, 5500.00, 'cash', '', NULL, NULL, 6, '2026-09-08 14:03:21', '2026-09-08 06:03:21'),
(11, 17, 3000.00, 'cash', '', NULL, NULL, 6, '2026-09-09 08:58:39', '2026-09-09 00:58:39'),
(12, 12, 1200.00, 'gcash', '', NULL, NULL, 6, '2026-09-09 10:33:18', '2026-09-09 02:33:18'),
(13, 12, 9000.00, 'bank_transfer', '', NULL, NULL, 6, '2026-09-09 10:33:18', '2026-09-09 02:33:18'),
(14, 20, 4650.00, 'cash', '', NULL, NULL, 6, '2026-09-09 16:44:30', '2026-09-09 08:44:30'),
(15, 19, 9300.00, 'cash', '', NULL, NULL, 6, '2026-09-10 11:51:57', '2026-09-10 03:51:57'),
(16, 21, 1200.00, 'cash', '', NULL, NULL, 6, '2026-09-11 17:55:11', '2026-09-11 09:55:11'),
(17, 22, 800.00, 'cash', '', NULL, NULL, 6, '2026-09-12 17:15:45', '2026-09-12 09:15:45'),
(18, 23, 8500.00, 'cash', '', NULL, NULL, 6, '2026-09-15 08:44:01', '2026-09-15 00:44:01'),
(19, 26, 4000.00, 'cash', '', NULL, NULL, 8, '2026-09-18 09:58:50', '2026-09-18 01:58:50'),
(20, 30, 1350.00, 'bank_transfer', '', NULL, NULL, 6, '2026-09-21 16:00:28', '2026-09-21 08:00:28'),
(21, 29, 4000.00, 'cash', '', NULL, NULL, 6, '2026-09-21 17:51:02', '2026-09-21 09:51:02'),
(22, 29, 1500.00, 'gcash', '', NULL, NULL, 6, '2026-09-21 17:51:02', '2026-09-21 09:51:02'),
(23, 31, 1200.00, 'cash', '', NULL, NULL, 6, '2026-09-24 14:54:26', '2026-09-24 06:54:26'),
(24, 33, 8000.00, 'cash', '', NULL, NULL, 6, '2026-09-26 19:37:55', '2026-09-26 11:37:55'),
(25, 34, 5550.00, 'cash', '', NULL, NULL, 6, '2026-09-26 19:48:27', '2026-09-26 11:48:27');

-- --------------------------------------------------------

--
-- Table structure for table `job_order_products`
--

CREATE TABLE `job_order_products` (
  `id` int(11) NOT NULL,
  `job_order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_type` enum('engine_oil','oil_filter','parts','fluids','others') NOT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_order_products`
--

INSERT INTO `job_order_products` (`id`, `job_order_id`, `product_id`, `product_name`, `product_type`, `unit_price`, `quantity`, `total`, `created_at`) VALUES
(20, 7, 6, 'ENGINE OIL 5W-30', 'parts', 600.00, 4, 2400.00, '2026-09-01 07:44:55'),
(21, 7, NULL, 'OIL FILTER 039', 'parts', 600.00, 1, 600.00, '2026-09-01 07:44:55'),
(25, 11, NULL, 'SPARK PLUGS', 'parts', 500.00, 4, 2000.00, '2026-09-03 09:56:57'),
(28, 10, NULL, 'FUEL FILTER ELEMENT', 'parts', 1800.00, 1, 1800.00, '2026-09-04 00:50:04'),
(74, 14, NULL, 'WATER PUMP ASSY (SURPLUS)', 'parts', 5500.00, 1, 5500.00, '2026-09-05 06:33:22'),
(102, 13, NULL, 'UPPER SUSPENSION ASSY WITH BUSHING', 'parts', 5800.00, 2, 11600.00, '2026-09-05 09:48:33'),
(103, 13, NULL, 'STAB CLAMP BUSHING R/L', 'parts', 585.00, 2, 1170.00, '2026-09-05 09:48:33'),
(104, 13, NULL, 'STAB LINK R/L', 'parts', 975.00, 2, 1950.00, '2026-09-05 09:48:33'),
(105, 13, NULL, 'LOWER BALL JOINT R/L', 'parts', 2700.00, 2, 5400.00, '2026-09-05 09:48:33'),
(106, 13, NULL, 'UPPER ARM BALL JOINT', 'parts', 2200.00, 2, 4400.00, '2026-09-05 09:48:33'),
(107, 13, NULL, 'UPPER ARM SHAFT ASSY', 'parts', 5600.00, 1, 5600.00, '2026-09-05 09:48:33'),
(108, 15, NULL, 'FULLY SYNTHETIC OIL 5W-40', 'parts', 600.00, 7, 4200.00, '2026-09-07 01:47:30'),
(109, 15, NULL, 'OIL FILTER 111', 'parts', 500.00, 1, 500.00, '2026-09-07 01:47:30'),
(110, 15, NULL, 'BRAKE CLEANER', 'parts', 450.00, 1, 450.00, '2026-09-07 01:47:30'),
(120, 18, NULL, 'COOLANT', 'parts', 450.00, 4, 1800.00, '2026-09-08 06:12:07'),
(121, 18, 6, 'ENGINE OIL 5W-30', 'parts', 600.00, 3, 1800.00, '2026-09-08 06:12:07'),
(122, 18, 10, 'OIL FILTER 111', 'parts', 650.00, 1, 650.00, '2026-09-08 06:12:07'),
(128, 12, NULL, 'OXYGEN SENSOR BANK 2', 'parts', 7600.00, 1, 7600.00, '2026-09-09 02:33:18'),
(136, 20, 38, 'CVT VALVOLINE', 'parts', 950.00, 3, 2850.00, '2026-09-09 08:44:30'),
(137, 19, NULL, 'CLAMP BUSHING', 'parts', 900.00, 2, 1800.00, '2026-09-10 03:51:57'),
(138, 19, NULL, 'STABILIZER LINKIT', 'parts', 1200.00, 2, 2400.00, '2026-09-10 03:51:57'),
(162, 23, NULL, 'SPARK PLUGS', 'parts', 500.00, 3, 1500.00, '2026-09-15 00:51:35'),
(163, 23, NULL, 'COOLANT', 'parts', 350.00, 4, 1400.00, '2026-09-15 00:51:35'),
(164, 23, 6, 'ENGINE OIL 5W-30', 'parts', 600.00, 3, 1800.00, '2026-09-15 00:51:35'),
(165, 23, 10, 'OIL FILTER 111', 'parts', 500.00, 1, 500.00, '2026-09-15 00:51:35'),
(173, 25, NULL, 'FULLY SYNTHETIC OIL 5W40', 'parts', 600.00, 8, 4800.00, '2026-09-16 07:54:39'),
(174, 25, NULL, 'OIL FILTER', 'parts', 500.00, 1, 500.00, '2026-09-16 07:54:39'),
(175, 25, 12, 'BRAKE CLEANER', 'parts', 450.00, 1, 450.00, '2026-09-16 07:54:39'),
(176, 25, NULL, 'FRONT BRAKE PADS SET', 'parts', 5200.00, 1, 5200.00, '2026-09-16 07:54:39'),
(179, 27, NULL, 'RACK END RH/LH', 'parts', 2100.00, 2, 4200.00, '2026-09-17 08:12:37'),
(180, 27, NULL, 'HIGH TEMP GREASE', 'parts', 550.00, 2, 1100.00, '2026-09-17 08:12:37'),
(182, 31, NULL, 'AIRCON BELT', 'parts', 0.00, 1, 0.00, '2026-09-24 06:54:26'),
(185, 33, 12, 'BRAKE CLEANER', 'parts', 450.00, 1, 450.00, '2026-09-26 11:37:55'),
(186, 33, NULL, '4 PCS SPARK PLUGS', 'parts', 2000.00, 1, 2000.00, '2026-09-26 11:37:55'),
(187, 33, NULL, 'REAR BRAKE PADS', 'parts', 2800.00, 1, 2800.00, '2026-09-26 11:37:55'),
(194, 34, 13, 'ENGINE OIL 5W-40', 'parts', 600.00, 7, 4200.00, '2026-09-26 12:10:22'),
(195, 34, 10, 'OIL FILTER 111', 'parts', 500.00, 1, 500.00, '2026-09-26 12:10:22');

-- --------------------------------------------------------

--
-- Table structure for table `job_order_services`
--

CREATE TABLE `job_order_services` (
  `id` int(11) NOT NULL,
  `job_order_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `bundle_id` int(11) DEFAULT NULL,
  `service_name` varchar(100) NOT NULL,
  `service_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `labor_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sub_items_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_order_services`
--

INSERT INTO `job_order_services` (`id`, `job_order_id`, `service_id`, `bundle_id`, `service_name`, `service_price`, `labor_cost`, `quantity`, `total`, `sub_items_json`, `created_at`) VALUES
(37, 6, NULL, NULL, 'CHECK UP', 0.00, 0.00, 1, 0.00, NULL, '2026-09-01 02:41:57'),
(43, 8, NULL, NULL, 'REPLACE FRONT LEFT WHEEL HUB BEARING (LABOR/MATERIALS)', 0.00, 4500.00, 1, 4500.00, NULL, '2026-09-01 06:47:13'),
(44, 8, NULL, NULL, 'ALIGN FRONT BUMPER DUE TO IMPACT (WIDE GAP BETWEEN BUMPER AND HOOD)', 0.00, 3500.00, 1, 3500.00, NULL, '2026-09-01 06:47:13'),
(45, 8, NULL, NULL, 'REPLACE FRONT RIGHT WHEEL HUB BEARING (LABOR AND MATERIALS)', 0.00, 4500.00, 1, 4500.00, NULL, '2026-09-01 06:47:13'),
(46, 7, 6, NULL, 'REGULAR PMS', 0.00, 2800.00, 1, 2800.00, '[\"CHANGE OIL\",\"CHANGE OIL FILTER\",\"BRAKE CLEANING\\/ADJUST\",\"CLEANING AIR\\/CABIN FILTER\",\"CLEANING SPARK PLUG\",\"BOLT AND NUT TIGHTENING\",\"CHECK UNDERCHASSIS\",\"CHECK FLUID\",\"CHECK LIGHTS\",\"CHECK TIRES\",\"CHECK BATTERY CONDITION\",\"CHECK BELTS\",\"FREE SCANNING\"]', '2026-09-01 07:44:55'),
(47, 7, NULL, NULL, 'CHECK UP DRIVER SIDE POWER WINDOW WONT FUNCTION( RESCHEDULE)', 0.00, 0.00, 1, 0.00, NULL, '2026-09-01 07:44:55'),
(82, 11, NULL, NULL, '1. CHECK UP ENGINE PERFORMANCE (PALYADO) SMOKE COMING OUT ON REAR ENGINE BAY', 0.00, 2200.00, 1, 2200.00, '[\"- PULLOUT\\/INSTALL MUFFLER TO GIVE WAY\",\"- RETIGHT EXHAUST MANIFOLD DUE TO BOLT LOOSEN\\/FIXED\"]', '2026-09-03 09:56:57'),
(83, 11, NULL, NULL, '2. CHECK UP AIRCON SYSTEM NOT WORKING', 0.00, 1500.00, 1, 1500.00, '[\"RECONNECT COMPRESSOR WIRE ON MAGNETIC CLUTCH (OPEN CIRCUIT)\",\"ADD REFRIGERANT FREON (LOW FREON)\"]', '2026-09-03 09:56:57'),
(84, 9, NULL, NULL, 'REPLACE REAR LEFT WHEEL HUB BEARING (CUSTOMER SUPPLY)', 0.00, 2000.00, 1, 2000.00, NULL, '2026-09-03 09:57:12'),
(85, 9, NULL, NULL, 'REPLACE REAR RIGHT WHEEL HUB BEARING (CUSTOMER SUPPLY)', 0.00, 2000.00, 1, 2000.00, NULL, '2026-09-03 09:57:12'),
(88, 10, NULL, NULL, 'EGR, THROTTLE BODY, INTAKE MANIFOLD, AND TURBOCHARGER CLEANING (PACKAGE)', 0.00, 10000.00, 1, 10000.00, NULL, '2026-09-04 00:50:04'),
(152, 14, NULL, NULL, 'CHECK UP ENGINE OVERHEATING ISSUE', 0.00, 2800.00, 1, 2800.00, '[\"-UPON CHECKING ,COOLANT LEAK FROM THE WATER PUMP\",\"-PULL OUT WATER PUMP ASSY\",\"-APLLY SILICON SEALANT\",\"-REPLACE WATER PUMP ASSY (SURPLUS ONLY)\",\"-ADD DISTILLED WATER AS PER CUSTOMER REQUEST\",\"-ENGINE RUN TEST\",\"-\"]', '2026-09-05 06:33:22'),
(174, 13, NULL, NULL, 'REPLACE UPPER ARM SUSPENSION ASSEMBLY RH/LH', 0.00, 1800.00, 1, 1800.00, NULL, '2026-09-05 09:48:33'),
(175, 13, NULL, NULL, 'PULLOUT/INSTALL SUSPENSION ASSEMBLY RH/LH', 0.00, 2800.00, 1, 2800.00, NULL, '2026-09-05 09:48:33'),
(176, 13, NULL, NULL, 'REPLACE FRONT STAB CLAMP BUSHING AND STAB LINK RH/LH', 0.00, 800.00, 1, 800.00, NULL, '2026-09-05 09:48:33'),
(177, 13, NULL, NULL, 'PRESS IN/PRESS OUT LOWER SUSPENSION BALL JOINT RH/LH', 0.00, 500.00, 1, 500.00, NULL, '2026-09-05 09:48:33'),
(178, 13, NULL, NULL, 'PERFORM WHEEL ALIGNMENT (COMPLETE)', 0.00, 2200.00, 1, 2200.00, NULL, '2026-09-05 09:48:33'),
(179, 13, NULL, NULL, 'CHECK UP AIRCON (INSUFFICIENT COOL)', 0.00, 0.00, 1, 0.00, '[\"-CHARGE FREON ONLY\\/NEED TO CHECK AC SYSTEM ,NEED ENOUGH TIME\"]', '2026-09-05 09:48:33'),
(180, 13, NULL, NULL, 'CHECK UP ENGINE PERFORMANCE (LACK OF POWER)', 0.00, 0.00, 1, 0.00, '[\"CLEAN SHUT OFF VALVE STRAINER\",\"NEED ENOUGH TIME TO TROUBLESHOOT\\/OUT OF TIME\"]', '2026-09-05 09:48:33'),
(181, 13, NULL, NULL, 'CHECK UP DOOR MECHANISM', 0.00, 0.00, 1, 0.00, '[\"FRONT DRIVER SIDE DOOR MECHANISM\\/RESCHEDULE\\/OUT OF TIME\",\"REAR PASSENGER SIDE DOOR MECHANISM\\/RESCHEDULE\\/OUT TIME\"]', '2026-09-05 09:48:33'),
(182, 15, 6, NULL, 'REGULAR PMS', 0.00, 2800.00, 1, 2800.00, '[\"CHANGE OIL\",\"CHANGE OIL FILTER\",\"BRAKE CLEANING\\/ADJUST\",\"CLEANING AIR\\/CABIN FILTER\",\"BOLT AND NUT TIGHTENING\",\"CHECK UNDERCHASSIS\",\"CHECK FLUID\",\"CHECK LIGHTS\",\"CHECK TIRES\",\"CHECK BATTERY CONDITION\",\"CHECK BELTS\",\"FREE SCANNING\"]', '2026-09-07 01:47:30'),
(189, 16, NULL, NULL, 'CHECK UP STEERING WHEEL ALIGNMENT (SWERVING)', 0.00, 1200.00, 1, 1200.00, '[\"REPLACE RACK END AND TIE ROD END RH.LH (CUSTOMER SUPPLY\"]', '2026-09-07 03:39:02'),
(190, 16, NULL, NULL, 'NOTE: TO FOLLOW WHEEL ALIGNMENT AS PER CUSTOMER REQUEST', 0.00, 0.00, 1, 0.00, NULL, '2026-09-07 03:39:02'),
(205, 18, NULL, NULL, 'CHANGE OIL AND CHANGE OIL FILTER ALONE', 0.00, 500.00, 1, 500.00, '[\"SCANNING\"]', '2026-09-08 06:12:07'),
(206, 18, NULL, NULL, 'THROTTLE BODSY CLEANING', 0.00, 1000.00, 1, 1000.00, '[\"-ADD COOLANT\"]', '2026-09-08 06:12:07'),
(214, 17, NULL, NULL, '-CHECK UP ENGINE PERFORMANCE (WHEN AC IS ON-ENGINE VIBRATION)', 0.00, 2200.00, 1, 2200.00, '[\"REPLACE FUEL INJECTORS 4PCS (CUSTOMER SUPPLY)\",\"CHECK FUSE,RELAYS,WIRINGS,SOCKET CONNECTORS\",\"SCANNING\"]', '2026-09-09 00:58:39'),
(215, 17, NULL, NULL, 'REPLACE DRIVE BELT (CUSTOMER SUPPLY)', 0.00, 800.00, 1, 800.00, NULL, '2026-09-09 00:58:39'),
(216, 12, NULL, NULL, 'CHECK UP ENGINE LAMP LIGHT ILLUMINATE', 0.00, 0.00, 1, 0.00, NULL, '2026-09-09 02:33:18'),
(217, 12, NULL, NULL, 'SCANNING', 0.00, 0.00, 1, 0.00, '[\"- DTC RESULT\",\"- OXYGEN SENSOR BANK 1 AND 2 OPEN CIRCUIT\",\"- DUE TO OPEN WIRE (OPEN CIRCUIT)\"]', '2026-09-09 02:33:18'),
(218, 12, NULL, NULL, 'JOB DONE:', 0.00, 3500.00, 1, 3500.00, '[\"TROUBLESHOOTING\",\"PULL OUT OXYGEN SENSOR BANK 1  SENSOR 2\",\"REPLACE OXYGEN SENSOR BANK 1 SENSOR 2\",\"ERASE DTC USING SCAN TOOL\",\"REWIRING\",\"ENGINE RUN TEST\"]', '2026-09-09 02:33:18'),
(226, 20, NULL, NULL, 'PULLDOWN CVT TRANSMISSION OIL PAN', 0.00, 1800.00, 1, 1800.00, '[\"-DRAIN FLUID\",\"-CLEAN CVT FILTER\",\"-REFILL NEW TRANSMISSION FLUID\",\"-RESEAL TRANS OIL PAN\",\"-SCANNING\"]', '2026-09-09 08:44:30'),
(227, 19, NULL, NULL, 'REPLACE CLAMP BUSHING AND STABILIZER LINKIT RH/LH', 0.00, 1600.00, 1, 1600.00, NULL, '2026-09-10 03:51:57'),
(228, 19, NULL, NULL, 'CALIPER MACHINING RH/LH', 0.00, 3500.00, 1, 3500.00, NULL, '2026-09-10 03:51:57'),
(230, 21, NULL, NULL, 'REPLACE REAR BRAKE SHOE(CUSTOMER SUPPLY)-GENUINE', 0.00, 1200.00, 1, 1200.00, NULL, '2026-09-11 09:55:11'),
(237, 22, NULL, NULL, 'FLUSHING COOLANT (LABOR)', 0.00, 800.00, 1, 800.00, NULL, '2026-09-12 09:15:45'),
(240, 24, NULL, NULL, 'CHECK UP BRAKE SYSTEM( BRAKE PEDAL -MUBASYO)', 0.00, 0.00, 1, 0.00, '[\"-CHECK BRAKE FLUID\",\"-CHECK LEAKAGE\"]', '2026-09-15 00:37:54'),
(243, 23, 5, NULL, 'HEAVY PMS', 0.00, 3800.00, 1, 3800.00, '[\"CHANGE OIL\",\"CHANGE OIL FILTER\",\"BRAKE CLEANING AND ADJUST\",\"CLEANING THROTTLE BODY\",\"CLEANING INTAKE MANIFOLD\",\"CLEANING OXYGEN SENSOR\",\"CLEANING MAF SENSOR\",\"FLUSHING COOLANT\",\"REPLACE SPARK PLUG\",\"CHECK LIGHTS\",\"CHECK UNDER CHASSIS\",\"SCANNING\"]', '2026-09-15 00:51:35'),
(246, 25, 6, NULL, 'REGULAR PMS', 0.00, 2800.00, 1, 2800.00, '[\"CHANGE OIL\",\"CHANGE OIL FILTER\",\"BRAKE CLEANING\\/ADJUST\",\"CLEANING AIR\\/CABIN FILTER\",\"BOLT AND NUT TIGHTENING\",\"CHECK UNDERCHASSIS\",\"CHECK FLUID\",\"CHECK LIGHTS\",\"CHECK TIRES\",\"CHECK BATTERY CONDITION\",\"CHECK BELTS\",\"FREE SCANNING\"]', '2026-09-16 07:54:39'),
(247, 25, NULL, NULL, '2. REPLACE FRONT BRAKE PADS SET (BELOW MINIMUM 3MM)', 0.00, 0.00, 1, 0.00, NULL, '2026-09-16 07:54:39'),
(259, 27, NULL, NULL, '-REPLACE RACK END RH/LH', 0.00, 1200.00, 1, 1200.00, NULL, '2026-09-17 08:12:37'),
(260, 27, NULL, NULL, '-REPACK FRONT HUB WHEEL AXLE VELOCITY RH/LH', 0.00, 3800.00, 1, 3800.00, NULL, '2026-09-17 08:12:37'),
(261, 27, NULL, NULL, '-WHEEL ALIGNMENT (TOE IN/TO OUT)', 0.00, 1200.00, 1, 1200.00, NULL, '2026-09-17 08:12:37'),
(262, 26, NULL, NULL, '-REPLACE EBNGINE SUPPORT RH/LH (CUSTOMER SUPPLY)', 0.00, 2200.00, 1, 2200.00, NULL, '2026-09-18 01:58:50'),
(263, 26, NULL, NULL, '-REPLACE STABILIZER LINKIT RH/LH (CUSTOMER SUPPLY)', 0.00, 1800.00, 1, 1800.00, NULL, '2026-09-18 01:58:50'),
(264, 28, NULL, NULL, 'CHECK UP OIL LEAK ON TRANSMISSION', 0.00, 0.00, 1, 0.00, '[\"-PULLDOWN TRANSMISSION ASSY TO GIVEWAY\",\"-REPLACE REAR CRANKSHAFT OIL SEAL\"]', '2026-09-20 23:23:15'),
(267, 30, NULL, NULL, 'REPLACE BRAKE MASTER ASSY (CUSTOMER SUPPLY)', 0.00, 1350.00, 1, 1350.00, NULL, '2026-09-21 08:00:28'),
(269, 29, NULL, NULL, 'CHECK UP OIL LEAK ON TRANSMISSION', 0.00, 6500.00, 1, 6500.00, '[\"-PULLDOWN TRANSMISSION ASSY TO GIVEWAY\",\"-REPLACE REAR CRANKSHAFT OIL SEAL (CUSTOMER SUPPLY)\",\"-REPLACE INPUT AND OUTPUT SHAFT OIL SEAL (CUSTOMER SUPPLY)\",\"-ADD 2 LTRS ATF (CUSTOMER SUPPLY)\"]', '2026-09-21 09:51:02'),
(273, 32, NULL, NULL, 'INTERIOR DETAILING', 0.00, 0.00, 1, 0.00, NULL, '2026-09-24 03:24:30'),
(274, 32, NULL, NULL, 'EXTERIOR DETAILING', 0.00, 0.00, 1, 0.00, NULL, '2026-09-24 03:24:30'),
(277, 31, NULL, NULL, 'CHECK UP AIRCON NO COOL', 0.00, 1200.00, 1, 1200.00, NULL, '2026-09-24 06:54:26'),
(281, 33, 6, NULL, 'REGULAR PMS', 0.00, 2800.00, 1, 2800.00, '[\"BRAKE CLEANING\\/ADJUST\",\"CLEANING AIR\\/CABIN FILTER\",\"BOLT AND NUT TIGHTENING\",\"CHECK UNDERCHASSIS\",\"CHECK FLUID\",\"CHECK LIGHTS\",\"CHECK TIRES\",\"CHECK BATTERY CONDITION\",\"CHECK BELTS\",\"FREE SCANNING\"]', '2026-09-26 11:37:55'),
(282, 33, NULL, NULL, 'REPLACE SPARK PLUGS', 0.00, 0.00, 1, 0.00, NULL, '2026-09-26 11:37:55'),
(283, 33, NULL, NULL, 'CHECK UP REAR RIGHT WHEEL (UNGOT)', 0.00, 0.00, 1, 0.00, NULL, '2026-09-26 11:37:55'),
(290, 34, 4, NULL, 'CHANGE OIL', 0.00, 500.00, 1, 500.00, NULL, '2026-09-26 12:10:22'),
(291, 34, NULL, NULL, 'CHECK UP ENGINE LAMP LIGHT ILLUMINATE', 0.00, 350.00, 1, 350.00, '[\"- PERFORM SCANNING USING SCAN TOOL\",\"- DTC P012 INLET PRESSURE SENSORE\",\"- REPLACE INTERCOOLER HOSE 2 PCS (CX SUPPLY)\",\"- PULL OUT INTERCOOLER ASSY\",\"- CLEAN INTERCOOLER D\\/T OIL LEAK ON HOSE\"]', '2026-09-26 12:10:22'),
(292, 35, NULL, NULL, 'FLUSHING ATF FLUID AND REPLACE ATF FILTER', 0.00, 0.00, 1, 0.00, NULL, '2026-09-28 03:39:50'),
(293, 35, NULL, NULL, 'REMOVE AND REPLACE TURBO ASSYMBLY', 0.00, 0.00, 1, 0.00, NULL, '2026-09-28 03:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `job_order_status_history`
--

CREATE TABLE `job_order_status_history` (
  `id` int(11) NOT NULL,
  `job_order_id` int(11) NOT NULL,
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `job_order_status_history`
--

INSERT INTO `job_order_status_history` (`id`, `job_order_id`, `from_status`, `to_status`, `changed_by`, `changed_at`) VALUES
(1, 6, NULL, 'pending', 8, '2026-09-01 07:49:27'),
(2, 6, 'pending', 'cancelled', 8, '2026-09-01 07:52:54'),
(3, 7, NULL, 'pending', 8, '2026-09-01 09:20:27'),
(4, 7, 'pending', 'ongoing', 8, '2026-09-01 09:23:08'),
(5, 8, NULL, 'pending', 8, '2026-09-01 10:20:23'),
(6, 8, 'pending', 'ongoing', 8, '2026-09-01 10:22:59'),
(7, 7, 'ongoing', 'completed', 8, '2026-09-01 13:40:32'),
(8, 8, 'ongoing', 'completed', 8, '2026-09-01 14:43:28'),
(9, 8, 'completed', 'released', 6, '2026-09-01 14:47:18'),
(10, 8, 'released', 'completed', 6, '2026-09-01 15:01:34'),
(11, 9, NULL, 'pending', 8, '2026-09-01 16:07:46'),
(12, 9, 'pending', 'ongoing', 8, '2026-09-01 16:08:39'),
(13, 10, NULL, 'pending', 8, '2026-09-02 08:50:35'),
(14, 10, 'pending', 'ongoing', 8, '2026-09-02 08:50:43'),
(15, 11, NULL, 'pending', 8, '2026-09-02 11:59:20'),
(16, 11, 'pending', 'ongoing', 8, '2026-09-02 14:46:25'),
(17, 11, 'ongoing', 'completed', 8, '2026-09-03 16:21:31'),
(18, 9, 'ongoing', 'completed', 8, '2026-09-03 16:28:35'),
(19, 10, 'ongoing', 'completed', 8, '2026-09-04 08:23:46'),
(20, 12, NULL, 'pending', 8, '2026-09-04 09:52:57'),
(21, 12, 'pending', 'ongoing', 8, '2026-09-04 09:53:02'),
(22, 13, NULL, 'pending', 8, '2026-09-04 11:59:14'),
(23, 13, 'pending', 'ongoing', 6, '2026-09-04 15:36:50'),
(24, 14, NULL, 'pending', 8, '2026-09-05 09:37:01'),
(25, 14, 'pending', 'ongoing', 8, '2026-09-05 09:37:05'),
(26, 14, 'ongoing', 'completed', 8, '2026-09-05 12:29:04'),
(27, 15, NULL, 'pending', 8, '2026-09-05 14:42:24'),
(28, 15, 'pending', 'ongoing', 8, '2026-09-05 14:42:27'),
(29, 15, 'ongoing', 'completed', 8, '2026-09-05 15:53:26'),
(30, 13, 'ongoing', 'completed', 8, '2026-09-05 17:13:11'),
(31, 12, 'ongoing', 'completed', 6, '2026-09-05 17:47:36'),
(32, 12, 'completed', 'ongoing', 6, '2026-09-05 17:47:51'),
(33, 16, NULL, 'pending', 8, '2026-09-07 10:14:38'),
(34, 16, 'pending', 'ongoing', 8, '2026-09-07 10:15:11'),
(35, 16, 'ongoing', 'completed', 6, '2026-09-07 11:35:13'),
(36, 17, NULL, 'pending', 8, '2026-09-08 08:24:35'),
(37, 17, 'pending', 'ongoing', 8, '2026-09-08 08:25:02'),
(38, 18, NULL, 'pending', 8, '2026-09-08 12:58:09'),
(39, 18, 'pending', 'ongoing', 8, '2026-09-08 12:58:15'),
(40, 18, 'ongoing', 'completed', 8, '2026-09-08 13:40:06'),
(41, 19, NULL, 'pending', 8, '2026-09-08 14:34:37'),
(42, 19, 'pending', 'ongoing', 8, '2026-09-08 14:34:43'),
(43, 17, 'ongoing', 'completed', 6, '2026-09-09 08:58:16'),
(44, 7, 'completed', 'released', 6, '2026-09-09 08:58:47'),
(45, 8, 'completed', 'released', 6, '2026-09-09 08:58:51'),
(46, 9, 'completed', 'released', 6, '2026-09-09 08:58:53'),
(47, 10, 'completed', 'released', 6, '2026-09-09 08:58:57'),
(48, 11, 'completed', 'released', 6, '2026-09-09 08:59:00'),
(49, 13, 'completed', 'released', 6, '2026-09-09 08:59:02'),
(50, 14, 'completed', 'released', 6, '2026-09-09 08:59:05'),
(51, 15, 'completed', 'released', 6, '2026-09-09 08:59:12'),
(52, 12, 'ongoing', 'completed', 6, '2026-09-09 10:32:26'),
(53, 20, NULL, 'pending', 8, '2026-09-09 14:31:30'),
(54, 20, 'pending', 'ongoing', 8, '2026-09-09 14:31:48'),
(55, 20, 'ongoing', 'completed', 6, '2026-09-09 15:59:59'),
(56, 19, 'ongoing', 'completed', 6, '2026-09-10 11:51:39'),
(57, 21, NULL, 'pending', 8, '2026-09-10 15:22:06'),
(58, 21, 'pending', 'ongoing', 8, '2026-09-10 15:22:12'),
(59, 21, 'ongoing', 'completed', 6, '2026-09-11 09:16:48'),
(60, 22, NULL, 'pending', 6, '2026-09-12 11:10:10'),
(61, 22, 'pending', 'ongoing', 6, '2026-09-12 11:18:00'),
(62, 22, 'ongoing', 'completed', 6, '2026-09-12 15:02:14'),
(63, 23, NULL, 'pending', 6, '2026-09-12 15:06:00'),
(64, 23, 'pending', 'ongoing', 6, '2026-09-12 17:15:52'),
(65, 23, 'ongoing', 'completed', 8, '2026-09-15 07:26:57'),
(66, 24, NULL, 'pending', 8, '2026-09-15 08:37:54'),
(67, 24, 'pending', 'ongoing', 8, '2026-09-15 08:37:58'),
(68, 24, 'ongoing', 'cancelled', 8, '2026-09-16 07:36:46'),
(69, 25, NULL, 'pending', 8, '2026-09-16 13:08:16'),
(70, 25, 'pending', 'ongoing', 8, '2026-09-16 13:08:21'),
(71, 25, 'ongoing', 'completed', 8, '2026-09-16 16:00:38'),
(72, 26, NULL, 'pending', 8, '2026-09-17 09:53:34'),
(73, 26, 'pending', 'ongoing', 8, '2026-09-17 09:53:46'),
(74, 27, NULL, 'pending', 8, '2026-09-17 10:01:52'),
(75, 27, 'pending', 'ongoing', 8, '2026-09-17 10:01:56'),
(76, 27, 'ongoing', 'completed', 8, '2026-09-17 16:57:49'),
(77, 26, 'ongoing', 'completed', 8, '2026-09-18 09:20:09'),
(78, 28, NULL, 'pending', 8, '2026-09-21 07:23:15'),
(79, 29, NULL, 'pending', 8, '2026-09-21 07:23:15'),
(80, 29, 'pending', 'ongoing', 8, '2026-09-21 07:25:01'),
(81, 28, 'pending', 'cancelled', 8, '2026-09-21 08:45:15'),
(82, 30, NULL, 'pending', 8, '2026-09-21 15:21:05'),
(83, 30, 'pending', 'ongoing', 8, '2026-09-21 15:21:10'),
(84, 30, 'ongoing', 'completed', 6, '2026-09-21 15:53:50'),
(85, 29, 'ongoing', 'completed', 8, '2026-09-21 17:23:09'),
(86, 31, NULL, 'pending', 8, '2026-09-23 15:12:23'),
(87, 31, 'pending', 'ongoing', 8, '2026-09-23 15:12:31'),
(88, 32, NULL, 'pending', 6, '2026-09-24 11:24:11'),
(89, 32, 'pending', 'ongoing', 6, '2026-09-24 11:24:17'),
(90, 31, 'ongoing', 'completed', 6, '2026-09-24 13:17:36'),
(91, 28, 'cancelled', 'released', 6, '2026-09-24 13:23:45'),
(92, 28, 'released', 'cancelled', 6, '2026-09-24 13:23:48'),
(93, 33, NULL, 'pending', 6, '2026-09-26 19:33:21'),
(94, 33, 'pending', 'completed', 6, '2026-09-26 19:38:03'),
(95, 34, NULL, 'pending', 6, '2026-09-26 19:47:58'),
(96, 34, 'pending', 'completed', 6, '2026-09-26 19:48:08'),
(97, 35, NULL, 'pending', 6, '2026-09-28 11:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `job_order_technicians`
--

CREATE TABLE `job_order_technicians` (
  `id` int(11) NOT NULL,
  `job_order_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `is_assist` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_at` datetime NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `work_duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `status` enum('assigned','working','completed','on_hold','removed') NOT NULL DEFAULT 'assigned',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_order_technicians`
--

INSERT INTO `job_order_technicians` (`id`, `job_order_id`, `technician_id`, `is_assist`, `assigned_at`, `started_at`, `completed_at`, `work_duration`, `status`, `notes`, `created_at`) VALUES
(8, 6, 15, 0, '2026-09-01 07:49:27', NULL, NULL, 0, 'working', NULL, '2026-08-31 23:49:27'),
(9, 7, 9, 0, '2026-09-01 09:23:05', NULL, '2026-09-09 08:58:47', 5227, 'completed', NULL, '2026-09-01 01:23:05'),
(10, 7, 11, 1, '2026-09-01 09:33:13', NULL, '2026-09-09 08:58:47', 4622, 'completed', NULL, '2026-09-01 01:33:13'),
(11, 8, 10, 0, '2026-09-01 10:20:23', NULL, '2026-09-01 10:22:52', 0, 'removed', 'Transfer', '2026-09-01 02:20:23'),
(12, 8, 9, 0, '2026-09-01 10:20:23', NULL, '2026-09-01 14:47:18', 11190, 'completed', NULL, '2026-09-01 02:20:23'),
(13, 8, 11, 1, '2026-09-01 10:22:52', NULL, '2026-09-01 14:47:18', 11190, 'completed', NULL, '2026-09-01 02:22:52'),
(14, 9, 11, 0, '2026-09-01 16:07:46', NULL, '2026-09-09 08:58:53', 12679, 'completed', NULL, '2026-09-01 08:07:46'),
(15, 10, 11, 0, '2026-09-02 08:50:35', NULL, '2026-09-04 08:50:04', 40926, 'completed', NULL, '2026-09-02 00:50:35'),
(16, 10, 9, 1, '2026-09-02 08:50:35', NULL, '2026-09-04 08:50:04', 40926, 'completed', NULL, '2026-09-02 00:50:35'),
(17, 11, 10, 0, '2026-09-02 13:12:16', NULL, '2026-09-09 08:59:00', 12072, 'completed', NULL, '2026-09-02 05:12:16'),
(18, 10, 13, 1, '2026-09-02 16:40:53', NULL, '2026-09-04 08:50:04', 17197, 'completed', NULL, '2026-09-02 08:40:53'),
(19, 12, 9, 0, '2026-09-04 09:52:57', NULL, NULL, 13088, 'working', NULL, '2026-09-04 01:52:57'),
(20, 13, 10, 0, '2026-09-04 15:52:39', NULL, '2026-09-09 08:59:02', 10615, 'completed', NULL, '2026-09-04 07:52:39'),
(21, 13, 13, 1, '2026-09-04 15:52:39', NULL, '2026-09-09 08:59:02', 10615, 'completed', NULL, '2026-09-04 07:52:39'),
(22, 14, 10, 0, '2026-09-05 09:37:16', NULL, '2026-09-09 08:59:05', 9641, 'completed', NULL, '2026-09-05 01:37:16'),
(23, 15, 12, 0, '2026-09-05 14:42:24', NULL, '2026-09-09 08:59:12', 2634, 'completed', NULL, '2026-09-05 06:42:24'),
(24, 16, 11, 0, '2026-09-07 10:14:38', NULL, NULL, 4333, 'working', NULL, '2026-09-07 02:14:38'),
(25, 16, 13, 1, '2026-09-07 10:14:38', NULL, NULL, 4333, 'working', NULL, '2026-09-07 02:14:38'),
(26, 17, 11, 0, '2026-09-08 08:24:35', NULL, NULL, 6714, 'working', NULL, '2026-09-08 00:24:35'),
(27, 18, 9, 0, '2026-09-08 12:58:09', NULL, '2026-09-08 14:12:07', 2509, 'completed', NULL, '2026-09-08 04:58:09'),
(28, 19, 13, 0, '2026-09-08 14:34:37', NULL, NULL, 11158, 'working', NULL, '2026-09-08 06:34:37'),
(29, 19, 9, 1, '2026-09-08 14:34:37', NULL, NULL, 11158, 'working', NULL, '2026-09-08 06:34:37'),
(30, 20, 9, 0, '2026-09-09 14:31:30', NULL, '2026-09-09 16:44:30', 5291, 'completed', NULL, '2026-09-09 06:31:30'),
(31, 21, 13, 0, '2026-09-10 15:22:06', NULL, NULL, 64447, 'working', NULL, '2026-09-10 07:22:06'),
(32, 22, 9, 0, '2026-09-12 11:10:10', NULL, NULL, 13454, 'working', NULL, '2026-09-12 03:10:10'),
(33, 24, 9, 0, '2026-09-15 08:37:54', NULL, '2026-09-16 07:36:46', 9765, 'completed', NULL, '2026-09-15 00:37:54'),
(34, 25, 10, 0, '2026-09-16 13:08:16', NULL, '2026-09-16 16:00:38', 9033, 'completed', NULL, '2026-09-16 05:08:16'),
(35, 26, 13, 0, '2026-09-17 09:53:34', NULL, NULL, 6296, 'working', NULL, '2026-09-17 01:53:34'),
(36, 26, 9, 1, '2026-09-17 09:53:34', NULL, NULL, 6296, 'working', NULL, '2026-09-17 01:53:34'),
(37, 27, 10, 0, '2026-09-17 10:01:52', NULL, '2026-09-17 16:57:49', 5803, 'completed', NULL, '2026-09-17 02:01:52'),
(38, 28, 10, 0, '2026-09-21 07:23:15', NULL, '2026-09-21 08:45:15', 0, 'completed', NULL, '2026-09-20 23:23:15'),
(39, 29, 10, 0, '2026-09-21 07:23:15', NULL, NULL, 28699, 'working', NULL, '2026-09-20 23:23:15'),
(40, 30, 10, 0, '2026-09-21 15:21:04', NULL, NULL, 1957, 'working', NULL, '2026-09-21 07:21:04'),
(41, 31, 10, 0, '2026-09-23 15:12:23', NULL, '2026-09-24 14:54:26', 72732, 'completed', NULL, '2026-09-23 07:12:23'),
(42, 32, 9, 0, '2026-09-24 11:24:11', NULL, NULL, 7185, 'working', NULL, '2026-09-24 03:24:11'),
(43, 33, 11, 0, '2026-09-26 19:33:21', NULL, '2026-09-26 19:38:03', 0, 'completed', NULL, '2026-09-26 11:33:21'),
(44, 34, 9, 0, '2026-09-26 19:49:06', NULL, NULL, 0, 'working', NULL, '2026-09-26 11:49:06');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `type` enum('job_assigned','job_status','payment','low_stock','system','staff_update','account_update') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `staff_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `read_at`, `created_at`) VALUES
(5, 1, NULL, 'job_status', 'New Job Order Created', 'Dj Guingue Cortez created job order #JO001.', 'job_order', 3, 0, NULL, '2026-08-09 13:10:32'),
(7, 1, NULL, 'job_status', 'Job Order Status Updated', 'Dj Cortez updated job order #JO001 (Status: Completed).', 'job_order', 3, 0, NULL, '2026-08-09 13:10:43'),
(9, 1, NULL, 'job_status', 'Job Order Status Updated', 'Dj Cortez updated job order #JO001 (Status: Released).', 'job_order', 3, 0, NULL, '2026-08-09 13:10:50'),
(20, 3, NULL, 'job_status', 'New Job Order Created', 'Dj Guingue Cortez created job order #JO001.', 'job_order', 4, 0, NULL, '2026-08-09 14:31:07'),
(22, 3, NULL, 'job_status', 'Job Order Status Updated', 'Dj Guingue Cortez updated job order #JO001 (Status: Car Washing).', 'job_order', 4, 0, NULL, '2026-08-09 14:31:14'),
(24, 3, NULL, 'job_status', 'Job Order Status Updated', 'Dj Guingue Cortez updated job order #JO001 (Status: Completed).', 'job_order', 4, 0, NULL, '2026-08-09 14:31:15'),
(26, 3, NULL, 'system', 'Job Order Deleted', 'Dj Guingue Cortez deleted job order #JO001.', 'job_order', 4, 0, NULL, '2026-08-09 14:32:15'),
(32, 4, NULL, 'staff_update', 'Staff Added', 'Dj Guingue Cortez added staff Danilo Guingue Cortez Jr. (Role: ADMIN, Status: ACTIVE).', 'staff', 4, 0, NULL, '2026-08-10 11:59:36'),
(33, 4, NULL, 'system', 'Service Added', 'Dj Guingue Cortez added service Oil Filter Replacement (Price: ₱0.00, Status: ACTIVE).', 'service', 3, 0, NULL, '2026-08-12 00:19:31'),
(34, 4, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 00:21:01'),
(35, 4, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 00:23:28'),
(36, 4, NULL, 'system', 'System Logo Updated', 'Dj Guingue Cortez updated system logo for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 00:24:00'),
(37, 4, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 10:25:28'),
(38, 4, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 10:25:34'),
(39, 4, NULL, 'system', 'System Logo Updated', 'Dj Guingue Cortez updated system logo for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 10:25:59'),
(40, 4, NULL, 'staff_update', 'Staff Added', 'Dj Guingue Cortez added staff Erin Patricia Martinez (Role: CASHIER, Status: ACTIVE).', 'staff', 5, 0, NULL, '2026-08-12 11:19:51'),
(42, 4, NULL, 'staff_update', 'Staff Added', 'Dj Guingue Cortez added staff Lovely Joyce Gambong (Role: CASHIER, Status: ACTIVE).', 'staff', 6, 0, NULL, '2026-08-12 11:22:26'),
(45, 4, NULL, 'staff_update', 'Staff Added', 'Dj Guingue Cortez added staff Iloisa Joy P. Mejias (Role: CASHIER, Status: ACTIVE).', 'staff', 7, 0, NULL, '2026-08-12 11:24:05'),
(48, 7, NULL, 'staff_update', 'Staff Added', 'Dj Guingue Cortez added staff Iloisa Joy P. Mejias (Role: CASHIER, Status: ACTIVE).', 'staff', 7, 0, NULL, '2026-08-12 11:24:05'),
(49, 4, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 11:27:26'),
(52, 7, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 11:27:26'),
(53, 4, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 11:28:16'),
(56, 7, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 11:28:16'),
(57, 4, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 11:28:35'),
(60, 7, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 11:28:35'),
(61, 4, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 11:28:51'),
(64, 7, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 11:28:51'),
(65, 4, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 11:33:57'),
(67, 7, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-08-12 11:33:57'),
(69, 4, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Aian P. Alderite (Role: SERVICE_ADVISER, Status: ACTIVE).', 'staff', 8, 0, NULL, '2026-08-12 11:46:14'),
(71, 7, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Aian P. Alderite (Role: SERVICE_ADVISER, Status: ACTIVE).', 'staff', 8, 0, NULL, '2026-08-12 11:46:14'),
(73, 4, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Nexander M.Gayan (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 9, 0, NULL, '2026-08-12 11:52:42'),
(75, 7, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Nexander M.Gayan (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 9, 0, NULL, '2026-08-12 11:52:42'),
(77, 4, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Kineth Pandian (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 10, 0, NULL, '2026-08-12 11:58:18'),
(79, 7, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Kineth Pandian (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 10, 0, NULL, '2026-08-12 11:58:18'),
(81, 4, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Jerald  E. Changco (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 11, 0, NULL, '2026-08-12 12:04:23'),
(83, 7, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Jerald  E. Changco (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 11, 0, NULL, '2026-08-12 12:04:23'),
(85, 4, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff John Paul Villamente (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 12, 0, NULL, '2026-08-12 12:07:03'),
(87, 7, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff John Paul Villamente (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 12, 0, NULL, '2026-08-12 12:07:03'),
(89, 4, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Legario Mosaso (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 13, 0, NULL, '2026-08-12 12:26:30'),
(91, 7, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Legario Mosaso (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 13, 0, NULL, '2026-08-12 12:26:30'),
(93, 4, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Jan Carlo Padios (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 14, 0, NULL, '2026-08-12 12:34:01'),
(95, 7, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Jan Carlo Padios (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 14, 0, NULL, '2026-08-12 12:34:01'),
(97, 4, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Artemio Baquirel Jr. (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 15, 0, NULL, '2026-08-12 12:42:50'),
(99, 7, NULL, 'staff_update', 'Staff Added', 'Lovely Joyce Gambong added staff Artemio Baquirel Jr. (Role: TECHNICIAN, Status: ACTIVE).', 'staff', 15, 0, NULL, '2026-08-12 12:42:50'),
(101, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service CHANGE OIL (LABOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 4, 0, NULL, '2026-08-12 13:20:29'),
(103, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service CHANGE OIL (LABOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 4, 0, NULL, '2026-08-12 13:20:29'),
(106, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service HEAVY PMS (LABOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 5, 0, NULL, '2026-08-12 13:21:50'),
(108, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service HEAVY PMS (LABOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 5, 0, NULL, '2026-08-12 13:21:50'),
(111, 4, NULL, 'staff_update', 'Staff Added', 'Dj Guingue Cortez added staff Gracesilyn Pelvira Chen (Role: ADMIN, Status: ACTIVE).', 'staff', 16, 0, NULL, '2026-08-12 13:23:16'),
(112, 16, NULL, 'staff_update', 'Staff Added', 'Dj Guingue Cortez added staff Gracesilyn Pelvira Chen (Role: ADMIN, Status: ACTIVE).', 'staff', 16, 0, NULL, '2026-08-12 13:23:16'),
(115, 7, NULL, 'staff_update', 'Staff Added', 'Dj Guingue Cortez added staff Gracesilyn Pelvira Chen (Role: ADMIN, Status: ACTIVE).', 'staff', 16, 0, NULL, '2026-08-12 13:23:16'),
(116, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REGULAR PMS (LABOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 6, 0, NULL, '2026-08-12 13:23:28'),
(117, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REGULAR PMS (LABOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 6, 0, NULL, '2026-08-12 13:23:28'),
(119, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REGULAR PMS (LABOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 6, 0, NULL, '2026-08-12 13:23:28'),
(122, 4, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REGULAR PMS (LABOR) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-12 13:26:06'),
(123, 16, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REGULAR PMS (LABOR) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-12 13:26:06'),
(125, 7, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REGULAR PMS (LABOR) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-12 13:26:06'),
(128, 4, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-12 13:27:19'),
(129, 16, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-12 13:27:19'),
(131, 7, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-12 13:27:19'),
(134, 4, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service HEAVY PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 5, 0, NULL, '2026-08-12 13:27:28'),
(135, 16, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service HEAVY PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 5, 0, NULL, '2026-08-12 13:27:28'),
(137, 7, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service HEAVY PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 5, 0, NULL, '2026-08-12 13:27:28'),
(140, 4, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service CHANGE OIL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 4, 0, NULL, '2026-08-12 13:27:36'),
(141, 16, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service CHANGE OIL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 4, 0, NULL, '2026-08-12 13:27:36'),
(143, 7, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service CHANGE OIL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 4, 0, NULL, '2026-08-12 13:27:36'),
(146, 4, NULL, 'job_status', 'New Job Order Created', 'Lovely Joyce Gambong created job order #JO001.', 'job_order', 5, 0, NULL, '2026-08-13 01:45:49'),
(149, 7, NULL, 'job_status', 'New Job Order Created', 'Lovely Joyce Gambong created job order #JO001.', 'job_order', 5, 0, NULL, '2026-08-13 01:45:49'),
(151, 10, NULL, 'job_status', 'New Job Order Created', 'Lovely Joyce Gambong created job order #JO001.', 'job_order', 5, 1, NULL, '2026-08-13 01:45:49'),
(152, 16, NULL, 'job_status', 'New Job Order Created', 'Lovely Joyce Gambong created job order #JO001.', 'job_order', 5, 0, NULL, '2026-08-13 01:45:49'),
(154, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 01:46:57'),
(157, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 01:46:57'),
(159, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 1, NULL, '2026-08-13 01:46:57'),
(160, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 01:46:57'),
(162, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Lovely Joyce Gambong marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:47:32'),
(163, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Lovely Joyce Gambong marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:47:32'),
(166, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Lovely Joyce Gambong marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:47:32'),
(169, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:20'),
(170, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:20'),
(173, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:20'),
(176, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Lovely Joyce Gambong marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:25'),
(177, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Lovely Joyce Gambong marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:25'),
(180, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Lovely Joyce Gambong marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:25'),
(183, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Car Washing).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:33'),
(186, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Car Washing).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:33'),
(188, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Car Washing).', 'job_order', 5, 1, NULL, '2026-08-13 01:48:33'),
(189, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Car Washing).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:33'),
(191, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Completed).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:36'),
(194, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Completed).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:36'),
(196, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Completed).', 'job_order', 5, 1, NULL, '2026-08-13 01:48:36'),
(197, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Completed).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:36'),
(199, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:41'),
(202, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:41'),
(204, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 1, NULL, '2026-08-13 01:48:41'),
(205, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:41'),
(207, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Returned For Revision).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:56'),
(210, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Returned For Revision).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:56'),
(212, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Returned For Revision).', 'job_order', 5, 1, NULL, '2026-08-13 01:48:56'),
(213, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Returned For Revision).', 'job_order', 5, 0, NULL, '2026-08-13 01:48:56'),
(215, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:09'),
(216, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:09'),
(219, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:09'),
(222, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Lovely Joyce Gambong marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:17'),
(223, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Lovely Joyce Gambong marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:17'),
(226, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Lovely Joyce Gambong marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:17'),
(229, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Completed).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:22'),
(232, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Completed).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:22'),
(234, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Completed).', 'job_order', 5, 1, NULL, '2026-08-13 01:49:22'),
(235, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Completed).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:22'),
(237, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:27'),
(240, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:27'),
(242, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 1, NULL, '2026-08-13 01:49:27'),
(243, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 01:49:27'),
(245, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Returned For Revision).', 'job_order', 5, 0, NULL, '2026-08-13 03:51:27'),
(248, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Returned For Revision).', 'job_order', 5, 0, NULL, '2026-08-13 03:51:27'),
(250, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Returned For Revision).', 'job_order', 5, 1, NULL, '2026-08-13 03:51:27'),
(251, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Returned For Revision).', 'job_order', 5, 0, NULL, '2026-08-13 03:51:27'),
(253, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 04:34:35'),
(256, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 04:34:35'),
(258, 9, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 04:34:35'),
(259, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 1, NULL, '2026-08-13 04:34:35'),
(260, 11, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 04:34:35'),
(261, 12, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 04:34:35'),
(262, 13, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 04:34:35'),
(263, 14, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 04:34:35'),
(264, 15, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 04:34:35'),
(265, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Ongoing).', 'job_order', 5, 0, NULL, '2026-08-13 04:34:35'),
(267, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Jerald E. Changco marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:45:56'),
(268, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Jerald E. Changco marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:45:56'),
(271, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Jerald E. Changco marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:45:56'),
(274, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Jerald E. Changco marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:46:05'),
(275, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Jerald E. Changco marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:46:05'),
(278, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Jerald E. Changco marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:46:05'),
(281, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Jerald E. Changco marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:46:10'),
(282, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Jerald E. Changco marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:46:10'),
(285, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Jerald E. Changco marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:46:10'),
(288, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:39'),
(289, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:39'),
(292, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:39'),
(295, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:48'),
(296, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:48'),
(299, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:48'),
(302, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:52'),
(303, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:52'),
(306, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:52'),
(309, 4, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:53'),
(310, 16, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:53'),
(313, 7, NULL, 'job_status', 'Job Order Ready for Inspection', 'Kineth Pandian marked done job order #JO001 (Moved to Under Inspection).', 'job_order', 5, 0, NULL, '2026-08-13 04:48:53'),
(316, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 04:50:23'),
(319, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 04:50:23'),
(321, 9, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 04:50:23'),
(322, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 04:50:23'),
(323, 11, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 04:50:23'),
(324, 12, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 04:50:23'),
(325, 13, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 04:50:23'),
(326, 14, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 04:50:23'),
(327, 15, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 04:50:23'),
(328, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO001 (Status: Released).', 'job_order', 5, 0, NULL, '2026-08-13 04:50:23'),
(330, 4, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service FLUSHING BRAKE FLUID (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 3, 0, NULL, '2026-08-13 06:26:20'),
(331, 16, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service FLUSHING BRAKE FLUID (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 3, 0, NULL, '2026-08-13 06:26:20'),
(333, 7, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service FLUSHING BRAKE FLUID (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 3, 0, NULL, '2026-08-13 06:26:20'),
(336, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service CHARGE FREON (Price: ₱0.00, Status: ACTIVE).', 'service', 7, 0, NULL, '2026-08-13 06:27:04'),
(337, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service CHARGE FREON (Price: ₱0.00, Status: ACTIVE).', 'service', 7, 0, NULL, '2026-08-13 06:27:04'),
(339, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service CHARGE FREON (Price: ₱0.00, Status: ACTIVE).', 'service', 7, 0, NULL, '2026-08-13 06:27:04'),
(342, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service RADIATOR CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 8, 0, NULL, '2026-08-13 06:27:27'),
(343, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service RADIATOR CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 8, 0, NULL, '2026-08-13 06:27:27'),
(345, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service RADIATOR CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 8, 0, NULL, '2026-08-13 06:27:27'),
(348, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REPLACE DRIVE BELT (Price: ₱0.00, Status: ACTIVE).', 'service', 9, 0, NULL, '2026-08-13 06:28:04'),
(349, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REPLACE DRIVE BELT (Price: ₱0.00, Status: ACTIVE).', 'service', 9, 0, NULL, '2026-08-13 06:28:04'),
(351, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REPLACE DRIVE BELT (Price: ₱0.00, Status: ACTIVE).', 'service', 9, 0, NULL, '2026-08-13 06:28:04'),
(354, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REPLACE DRIVE BELT (FORD) (Price: ₱0.00, Status: ACTIVE).', 'service', 10, 0, NULL, '2026-08-13 06:28:35'),
(355, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REPLACE DRIVE BELT (FORD) (Price: ₱0.00, Status: ACTIVE).', 'service', 10, 0, NULL, '2026-08-13 06:28:35'),
(357, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REPLACE DRIVE BELT (FORD) (Price: ₱0.00, Status: ACTIVE).', 'service', 10, 0, NULL, '2026-08-13 06:28:35'),
(360, 4, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REPLACE DRIVE BELT (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 9, 0, NULL, '2026-08-13 06:28:49'),
(361, 16, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REPLACE DRIVE BELT (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 9, 0, NULL, '2026-08-13 06:28:49'),
(363, 7, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REPLACE DRIVE BELT (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 9, 0, NULL, '2026-08-13 06:28:49'),
(366, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service THROTTLE BODY CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 11, 0, NULL, '2026-08-13 06:29:13'),
(367, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service THROTTLE BODY CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 11, 0, NULL, '2026-08-13 06:29:13'),
(369, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service THROTTLE BODY CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 11, 0, NULL, '2026-08-13 06:29:13'),
(372, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REPLACCE AUXILIARY FAN MOTOR (Price: ₱0.00, Status: ACTIVE).', 'service', 12, 0, NULL, '2026-08-13 06:29:50'),
(373, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REPLACCE AUXILIARY FAN MOTOR (Price: ₱0.00, Status: ACTIVE).', 'service', 12, 0, NULL, '2026-08-13 06:29:50'),
(375, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service REPLACCE AUXILIARY FAN MOTOR (Price: ₱0.00, Status: ACTIVE).', 'service', 12, 0, NULL, '2026-08-13 06:29:50'),
(378, 4, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REPLACE AUXILIARY FAN MOTOR (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 12, 0, NULL, '2026-08-13 06:30:10'),
(379, 16, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REPLACE AUXILIARY FAN MOTOR (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 12, 0, NULL, '2026-08-13 06:30:10'),
(381, 7, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service REPLACE AUXILIARY FAN MOTOR (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 12, 0, NULL, '2026-08-13 06:30:10'),
(384, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service PULL OUT/INSTALL FRT. LOWER SUSPENSION ASSY (Price: ₱0.00, Status: ACTIVE).', 'service', 13, 0, NULL, '2026-08-13 06:30:32'),
(385, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service PULL OUT/INSTALL FRT. LOWER SUSPENSION ASSY (Price: ₱0.00, Status: ACTIVE).', 'service', 13, 0, NULL, '2026-08-13 06:30:32'),
(387, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service PULL OUT/INSTALL FRT. LOWER SUSPENSION ASSY (Price: ₱0.00, Status: ACTIVE).', 'service', 13, 0, NULL, '2026-08-13 06:30:32'),
(390, 4, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service PULL OUT/INSTALL FRT. LOWER SUSPENSION ASSY RH/LH (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 13, 0, NULL, '2026-08-13 06:30:50'),
(391, 16, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service PULL OUT/INSTALL FRT. LOWER SUSPENSION ASSY RH/LH (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 13, 0, NULL, '2026-08-13 06:30:50'),
(393, 7, NULL, 'system', 'Service Updated', 'Erin Patricia Martinez updated service PULL OUT/INSTALL FRT. LOWER SUSPENSION ASSY RH/LH (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 13, 0, NULL, '2026-08-13 06:30:50'),
(396, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service FUEL INJECTOR CLEANING (LABOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 14, 0, NULL, '2026-08-13 07:56:49'),
(397, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service FUEL INJECTOR CLEANING (LABOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 14, 0, NULL, '2026-08-13 07:56:49'),
(399, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service FUEL INJECTOR CLEANING (LABOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 14, 0, NULL, '2026-08-13 07:56:49'),
(402, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service WHEEL ALIGNMENT - TOE IN/TOE OUT (Price: ₱0.00, Status: ACTIVE).', 'service', 15, 0, NULL, '2026-08-13 08:46:49'),
(403, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service WHEEL ALIGNMENT - TOE IN/TOE OUT (Price: ₱0.00, Status: ACTIVE).', 'service', 15, 0, NULL, '2026-08-13 08:46:49'),
(405, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service WHEEL ALIGNMENT - TOE IN/TOE OUT (Price: ₱0.00, Status: ACTIVE).', 'service', 15, 0, NULL, '2026-08-13 08:46:49'),
(408, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service WHEEL ALIGNMENT - COMPLETE (Price: ₱0.00, Status: ACTIVE).', 'service', 16, 0, NULL, '2026-08-13 08:47:04'),
(409, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service WHEEL ALIGNMENT - COMPLETE (Price: ₱0.00, Status: ACTIVE).', 'service', 16, 0, NULL, '2026-08-13 08:47:04'),
(411, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service WHEEL ALIGNMENT - COMPLETE (Price: ₱0.00, Status: ACTIVE).', 'service', 16, 0, NULL, '2026-08-13 08:47:04'),
(414, 4, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service STEERING RACK REPAIR - PULL OUT/INSTALL (Price: ₱0.00, Status: ACTIVE).', 'service', 17, 0, NULL, '2026-08-13 08:49:31'),
(415, 16, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service STEERING RACK REPAIR - PULL OUT/INSTALL (Price: ₱0.00, Status: ACTIVE).', 'service', 17, 0, NULL, '2026-08-13 08:49:31'),
(417, 7, NULL, 'system', 'Service Added', 'Erin Patricia Martinez added service STEERING RACK REPAIR - PULL OUT/INSTALL (Price: ₱0.00, Status: ACTIVE).', 'service', 17, 0, NULL, '2026-08-13 08:49:31'),
(420, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GTX AIR FILTER (Code: PRD01, Status: ACTIVE).', 'product', 2, 0, NULL, '2026-08-13 08:52:32'),
(421, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GTX AIR FILTER (Code: PRD01, Status: ACTIVE).', 'product', 2, 0, NULL, '2026-08-13 08:52:32'),
(423, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GTX AIR FILTER (Code: PRD01, Status: ACTIVE).', 'product', 2, 0, NULL, '2026-08-13 08:52:32'),
(425, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product RELAY (Code: PRD02, Status: ACTIVE).', 'product', 3, 0, NULL, '2026-08-13 08:53:18'),
(426, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product RELAY (Code: PRD02, Status: ACTIVE).', 'product', 3, 0, NULL, '2026-08-13 08:53:18'),
(428, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product RELAY (Code: PRD02, Status: ACTIVE).', 'product', 3, 0, NULL, '2026-08-13 08:53:18'),
(430, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product AIR FILTER (HILUX, FORTUNER) (Code: PRD03, Status: ACTIVE).', 'product', 4, 0, NULL, '2026-08-13 08:53:50'),
(431, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product AIR FILTER (HILUX, FORTUNER) (Code: PRD03, Status: ACTIVE).', 'product', 4, 0, NULL, '2026-08-13 08:53:50'),
(433, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product AIR FILTER (HILUX, FORTUNER) (Code: PRD03, Status: ACTIVE).', 'product', 4, 0, NULL, '2026-08-13 08:53:50'),
(435, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product AIR FILTER (MULTI-VEHICLE) (Code: PRD04, Status: ACTIVE).', 'product', 5, 0, NULL, '2026-08-13 08:54:29'),
(436, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product AIR FILTER (MULTI-VEHICLE) (Code: PRD04, Status: ACTIVE).', 'product', 5, 0, NULL, '2026-08-13 08:54:29'),
(438, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product AIR FILTER (MULTI-VEHICLE) (Code: PRD04, Status: ACTIVE).', 'product', 5, 0, NULL, '2026-08-13 08:54:29'),
(440, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product AIR FILTER (NAVARA) (Code: PRD05, Status: ACTIVE).', 'product', 6, 0, NULL, '2026-08-13 08:55:03'),
(441, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product AIR FILTER (NAVARA) (Code: PRD05, Status: ACTIVE).', 'product', 6, 0, NULL, '2026-08-13 08:55:03'),
(443, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product AIR FILTER (NAVARA) (Code: PRD05, Status: ACTIVE).', 'product', 6, 0, NULL, '2026-08-13 08:55:03'),
(445, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF J4 (MIRAGE) (Code: PRD06, Status: ACTIVE).', 'product', 7, 0, NULL, '2026-08-13 08:56:04'),
(446, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF J4 (MIRAGE) (Code: PRD06, Status: ACTIVE).', 'product', 7, 0, NULL, '2026-08-13 08:56:04'),
(448, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF J4 (MIRAGE) (Code: PRD06, Status: ACTIVE).', 'product', 7, 0, NULL, '2026-08-13 08:56:04'),
(450, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF LV D111/ STEERING FLUID (Code: PRD07, Status: ACTIVE).', 'product', 8, 0, NULL, '2026-08-13 08:56:47'),
(451, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF LV D111/ STEERING FLUID (Code: PRD07, Status: ACTIVE).', 'product', 8, 0, NULL, '2026-08-13 08:56:47'),
(453, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF LV D111/ STEERING FLUID (Code: PRD07, Status: ACTIVE).', 'product', 8, 0, NULL, '2026-08-13 08:56:47'),
(455, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF LV MV (TOYOTA) (Code: PRD08, Status: ACTIVE).', 'product', 9, 0, NULL, '2026-08-13 08:57:11'),
(456, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF LV MV (TOYOTA) (Code: PRD08, Status: ACTIVE).', 'product', 9, 0, NULL, '2026-08-13 08:57:11'),
(458, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF LV MV (TOYOTA) (Code: PRD08, Status: ACTIVE).', 'product', 9, 0, NULL, '2026-08-13 08:57:11'),
(460, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF MAXLIFE DEX (Code: PRD09, Status: ACTIVE).', 'product', 10, 0, NULL, '2026-08-13 08:57:31'),
(461, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF MAXLIFE DEX (Code: PRD09, Status: ACTIVE).', 'product', 10, 0, NULL, '2026-08-13 08:57:31'),
(463, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF MAXLIFE DEX (Code: PRD09, Status: ACTIVE).', 'product', 10, 0, NULL, '2026-08-13 08:57:31'),
(465, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product 950 (Code: PRD10, Status: ACTIVE).', 'product', 11, 0, NULL, '2026-08-13 08:57:52'),
(466, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product 950 (Code: PRD10, Status: ACTIVE).', 'product', 11, 0, NULL, '2026-08-13 08:57:52'),
(468, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product 950 (Code: PRD10, Status: ACTIVE).', 'product', 11, 0, NULL, '2026-08-13 08:57:52'),
(470, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product ATF PETRON (HTP) (Status: ACTIVE -> ACTIVE).', 'product', 11, 0, NULL, '2026-08-13 08:58:38'),
(471, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product ATF PETRON (HTP) (Status: ACTIVE -> ACTIVE).', 'product', 11, 0, NULL, '2026-08-13 08:58:38'),
(473, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product ATF PETRON (HTP) (Status: ACTIVE -> ACTIVE).', 'product', 11, 0, NULL, '2026-08-13 08:58:38'),
(475, 4, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF LV D111/ STEERING FLUID (Quantity: +1).', 'inventory_transaction', 8, 0, NULL, '2026-08-13 08:59:04'),
(476, 16, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF LV D111/ STEERING FLUID (Quantity: +1).', 'inventory_transaction', 8, 0, NULL, '2026-08-13 08:59:04'),
(479, 7, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF LV D111/ STEERING FLUID (Quantity: +1).', 'inventory_transaction', 8, 0, NULL, '2026-08-13 08:59:04'),
(481, 4, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF LV D111/ STEERING FLUID (Quantity: +19).', 'inventory_transaction', 8, 0, NULL, '2026-08-13 08:59:13'),
(482, 16, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF LV D111/ STEERING FLUID (Quantity: +19).', 'inventory_transaction', 8, 0, NULL, '2026-08-13 08:59:13'),
(485, 7, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF LV D111/ STEERING FLUID (Quantity: +19).', 'inventory_transaction', 8, 0, NULL, '2026-08-13 08:59:13'),
(487, 4, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF LV MV (TOYOTA) (Quantity: +20).', 'inventory_transaction', 9, 0, NULL, '2026-08-13 08:59:23'),
(488, 16, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF LV MV (TOYOTA) (Quantity: +20).', 'inventory_transaction', 9, 0, NULL, '2026-08-13 08:59:23'),
(491, 7, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF LV MV (TOYOTA) (Quantity: +20).', 'inventory_transaction', 9, 0, NULL, '2026-08-13 08:59:23'),
(493, 4, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF MAXLIFE DEX (Quantity: +20).', 'inventory_transaction', 10, 0, NULL, '2026-08-13 08:59:32'),
(494, 16, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF MAXLIFE DEX (Quantity: +20).', 'inventory_transaction', 10, 0, NULL, '2026-08-13 08:59:32'),
(497, 7, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF MAXLIFE DEX (Quantity: +20).', 'inventory_transaction', 10, 0, NULL, '2026-08-13 08:59:32'),
(499, 4, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF PETRON (HTP) (Quantity: +20).', 'inventory_transaction', 11, 0, NULL, '2026-08-13 08:59:40'),
(500, 16, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF PETRON (HTP) (Quantity: +20).', 'inventory_transaction', 11, 0, NULL, '2026-08-13 08:59:40'),
(503, 7, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF PETRON (HTP) (Quantity: +20).', 'inventory_transaction', 11, 0, NULL, '2026-08-13 08:59:40'),
(505, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF PREMIUM SAE-20 (Code: PRD11, Status: ACTIVE).', 'product', 12, 0, NULL, '2026-08-13 09:00:17'),
(506, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF PREMIUM SAE-20 (Code: PRD11, Status: ACTIVE).', 'product', 12, 0, NULL, '2026-08-13 09:00:17'),
(508, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ATF PREMIUM SAE-20 (Code: PRD11, Status: ACTIVE).', 'product', 12, 0, NULL, '2026-08-13 09:00:17'),
(510, 4, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF PREMIUM SAE-20 (Quantity: +20).', 'inventory_transaction', 12, 0, NULL, '2026-08-13 09:01:17'),
(511, 16, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF PREMIUM SAE-20 (Quantity: +20).', 'inventory_transaction', 12, 0, NULL, '2026-08-13 09:01:17'),
(514, 7, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ATF PREMIUM SAE-20 (Quantity: +20).', 'inventory_transaction', 12, 0, NULL, '2026-08-13 09:01:17'),
(516, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product AIR FILTER (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 4, 0, NULL, '2026-08-13 09:04:23'),
(517, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product AIR FILTER (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 4, 0, NULL, '2026-08-13 09:04:23'),
(519, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product AIR FILTER (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 4, 0, NULL, '2026-08-13 09:04:23'),
(521, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product ENGINE OIL 5W-30 (Status: ACTIVE -> ACTIVE).', 'product', 6, 0, NULL, '2026-08-13 09:05:32'),
(522, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product ENGINE OIL 5W-30 (Status: ACTIVE -> ACTIVE).', 'product', 6, 0, NULL, '2026-08-13 09:05:32'),
(524, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product ENGINE OIL 5W-30 (Status: ACTIVE -> ACTIVE).', 'product', 6, 0, NULL, '2026-08-13 09:05:32'),
(526, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ENGINE OIL 5W-40 (Code: PRD12, Status: ACTIVE).', 'product', 13, 0, NULL, '2026-08-13 09:06:02'),
(527, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ENGINE OIL 5W-40 (Code: PRD12, Status: ACTIVE).', 'product', 13, 0, NULL, '2026-08-13 09:06:02'),
(529, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ENGINE OIL 5W-40 (Code: PRD12, Status: ACTIVE).', 'product', 13, 0, NULL, '2026-08-13 09:06:02'),
(531, 4, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ENGINE OIL 5W-40 (Quantity: +20).', 'inventory_transaction', 13, 0, NULL, '2026-08-13 09:06:23'),
(532, 16, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ENGINE OIL 5W-40 (Quantity: +20).', 'inventory_transaction', 13, 0, NULL, '2026-08-13 09:06:23'),
(535, 7, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock ENGINE OIL 5W-40 (Quantity: +20).', 'inventory_transaction', 13, 0, NULL, '2026-08-13 09:06:23'),
(537, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product PETRON ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 11, 0, NULL, '2026-08-13 09:07:42'),
(538, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product PETRON ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 11, 0, NULL, '2026-08-13 09:07:42'),
(540, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product PETRON ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 11, 0, NULL, '2026-08-13 09:07:42'),
(542, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-13 09:08:13'),
(543, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-13 09:08:13');
INSERT INTO `notifications` (`id`, `user_id`, `staff_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `read_at`, `created_at`) VALUES
(545, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-13 09:08:13'),
(547, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product OIL FILTER 415 (Status: ACTIVE -> ACTIVE).', 'product', 7, 0, NULL, '2026-08-13 09:08:57'),
(548, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product OIL FILTER 415 (Status: ACTIVE -> ACTIVE).', 'product', 7, 0, NULL, '2026-08-13 09:08:57'),
(550, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product OIL FILTER 415 (Status: ACTIVE -> ACTIVE).', 'product', 7, 0, NULL, '2026-08-13 09:08:57'),
(552, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product OIL FITER 110 (Status: ACTIVE -> ACTIVE).', 'product', 8, 0, NULL, '2026-08-13 09:09:21'),
(553, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product OIL FITER 110 (Status: ACTIVE -> ACTIVE).', 'product', 8, 0, NULL, '2026-08-13 09:09:21'),
(555, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product OIL FITER 110 (Status: ACTIVE -> ACTIVE).', 'product', 8, 0, NULL, '2026-08-13 09:09:21'),
(557, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product OIL FILTER 111 (Status: ACTIVE -> ACTIVE).', 'product', 10, 0, NULL, '2026-08-13 09:09:59'),
(558, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product OIL FILTER 111 (Status: ACTIVE -> ACTIVE).', 'product', 10, 0, NULL, '2026-08-13 09:09:59'),
(560, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product OIL FILTER 111 (Status: ACTIVE -> ACTIVE).', 'product', 10, 0, NULL, '2026-08-13 09:09:59'),
(562, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-13 09:10:46'),
(563, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-13 09:10:46'),
(565, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-13 09:10:46'),
(567, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product FRONT HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 2, 0, NULL, '2026-08-13 09:11:13'),
(568, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product FRONT HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 2, 0, NULL, '2026-08-13 09:11:13'),
(570, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product FRONT HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 2, 0, NULL, '2026-08-13 09:11:13'),
(572, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product REAR HUB BEARING (MIRAGE (Code: PRD13, Status: ACTIVE).', 'product', 14, 0, NULL, '2026-08-13 09:11:27'),
(573, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product REAR HUB BEARING (MIRAGE (Code: PRD13, Status: ACTIVE).', 'product', 14, 0, NULL, '2026-08-13 09:11:27'),
(575, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product REAR HUB BEARING (MIRAGE (Code: PRD13, Status: ACTIVE).', 'product', 14, 0, NULL, '2026-08-13 09:11:27'),
(577, 4, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product FRONT HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 2, 0, NULL, '2026-08-13 09:11:43'),
(578, 16, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product FRONT HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 2, 0, NULL, '2026-08-13 09:11:43'),
(580, 7, NULL, 'system', 'Product Updated', 'Erin Patricia Martinez updated product FRONT HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 2, 0, NULL, '2026-08-13 09:11:43'),
(582, 4, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock REAR HUB BEARING (MIRAGE (Quantity: +10).', 'inventory_transaction', 14, 0, NULL, '2026-08-13 09:11:55'),
(583, 16, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock REAR HUB BEARING (MIRAGE (Quantity: +10).', 'inventory_transaction', 14, 0, NULL, '2026-08-13 09:11:55'),
(586, 7, NULL, 'system', 'Inventory Stock In', 'Erin Patricia Martinez added stock REAR HUB BEARING (MIRAGE (Quantity: +10).', 'inventory_transaction', 14, 0, NULL, '2026-08-13 09:11:55'),
(588, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product PENETRATING (Code: PRD14, Status: ACTIVE).', 'product', 15, 0, NULL, '2026-08-13 09:12:26'),
(589, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product PENETRATING (Code: PRD14, Status: ACTIVE).', 'product', 15, 0, NULL, '2026-08-13 09:12:26'),
(591, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product PENETRATING (Code: PRD14, Status: ACTIVE).', 'product', 15, 0, NULL, '2026-08-13 09:12:26'),
(593, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product CARB CLEANER (Code: PRD15, Status: ACTIVE).', 'product', 16, 0, NULL, '2026-08-13 09:12:35'),
(594, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product CARB CLEANER (Code: PRD15, Status: ACTIVE).', 'product', 16, 0, NULL, '2026-08-13 09:12:35'),
(596, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product CARB CLEANER (Code: PRD15, Status: ACTIVE).', 'product', 16, 0, NULL, '2026-08-13 09:12:35'),
(598, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GEAR OIL (Code: PRD16, Status: ACTIVE).', 'product', 17, 0, NULL, '2026-08-13 09:12:42'),
(599, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GEAR OIL (Code: PRD16, Status: ACTIVE).', 'product', 17, 0, NULL, '2026-08-13 09:12:42'),
(601, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GEAR OIL (Code: PRD16, Status: ACTIVE).', 'product', 17, 0, NULL, '2026-08-13 09:12:42'),
(603, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GREASE (Code: PRD17, Status: ACTIVE).', 'product', 18, 0, NULL, '2026-08-13 09:12:52'),
(604, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GREASE (Code: PRD17, Status: ACTIVE).', 'product', 18, 0, NULL, '2026-08-13 09:12:52'),
(606, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GREASE (Code: PRD17, Status: ACTIVE).', 'product', 18, 0, NULL, '2026-08-13 09:12:52'),
(608, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product COOLANT BLUE (Code: PRD18, Status: ACTIVE).', 'product', 19, 0, NULL, '2026-08-13 09:13:00'),
(609, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product COOLANT BLUE (Code: PRD18, Status: ACTIVE).', 'product', 19, 0, NULL, '2026-08-13 09:13:00'),
(611, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product COOLANT BLUE (Code: PRD18, Status: ACTIVE).', 'product', 19, 0, NULL, '2026-08-13 09:13:00'),
(613, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product COOLANT GREEN (Code: PRD19, Status: ACTIVE).', 'product', 20, 0, NULL, '2026-08-13 09:13:06'),
(614, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product COOLANT GREEN (Code: PRD19, Status: ACTIVE).', 'product', 20, 0, NULL, '2026-08-13 09:13:06'),
(616, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product COOLANT GREEN (Code: PRD19, Status: ACTIVE).', 'product', 20, 0, NULL, '2026-08-13 09:13:06'),
(618, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BATTERY (Code: PRD20, Status: ACTIVE).', 'product', 21, 0, NULL, '2026-08-13 09:13:15'),
(619, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BATTERY (Code: PRD20, Status: ACTIVE).', 'product', 21, 0, NULL, '2026-08-13 09:13:15'),
(621, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BATTERY (Code: PRD20, Status: ACTIVE).', 'product', 21, 0, NULL, '2026-08-13 09:13:15'),
(623, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BRAKE PADS (MIRAGE) (Code: PRD21, Status: ACTIVE).', 'product', 22, 0, NULL, '2026-08-13 09:13:22'),
(624, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BRAKE PADS (MIRAGE) (Code: PRD21, Status: ACTIVE).', 'product', 22, 0, NULL, '2026-08-13 09:13:22'),
(626, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BRAKE PADS (MIRAGE) (Code: PRD21, Status: ACTIVE).', 'product', 22, 0, NULL, '2026-08-13 09:13:22'),
(628, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product STAB. LINK (TRANSFORMER) (Code: PRD22, Status: ACTIVE).', 'product', 23, 0, NULL, '2026-08-13 09:13:28'),
(629, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product STAB. LINK (TRANSFORMER) (Code: PRD22, Status: ACTIVE).', 'product', 23, 0, NULL, '2026-08-13 09:13:28'),
(631, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product STAB. LINK (TRANSFORMER) (Code: PRD22, Status: ACTIVE).', 'product', 23, 0, NULL, '2026-08-13 09:13:28'),
(633, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product STAB. CLAMP (TRANSFORMER) (Code: PRD23, Status: ACTIVE).', 'product', 24, 0, NULL, '2026-08-13 09:13:35'),
(634, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product STAB. CLAMP (TRANSFORMER) (Code: PRD23, Status: ACTIVE).', 'product', 24, 0, NULL, '2026-08-13 09:13:35'),
(636, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product STAB. CLAMP (TRANSFORMER) (Code: PRD23, Status: ACTIVE).', 'product', 24, 0, NULL, '2026-08-13 09:13:35'),
(638, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product VALVE COVER GASKET (TRANSFORMER) (Code: PRD24, Status: ACTIVE).', 'product', 25, 0, NULL, '2026-08-13 09:13:45'),
(639, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product VALVE COVER GASKET (TRANSFORMER) (Code: PRD24, Status: ACTIVE).', 'product', 25, 0, NULL, '2026-08-13 09:13:45'),
(641, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product VALVE COVER GASKET (TRANSFORMER) (Code: PRD24, Status: ACTIVE).', 'product', 25, 0, NULL, '2026-08-13 09:13:45'),
(643, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product OIL FILTER (GEELY COOLRAY) (Code: PRD25, Status: ACTIVE).', 'product', 26, 0, NULL, '2026-08-13 09:14:00'),
(644, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product OIL FILTER (GEELY COOLRAY) (Code: PRD25, Status: ACTIVE).', 'product', 26, 0, NULL, '2026-08-13 09:14:00'),
(646, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product OIL FILTER (GEELY COOLRAY) (Code: PRD25, Status: ACTIVE).', 'product', 26, 0, NULL, '2026-08-13 09:14:00'),
(648, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product FLUSHING (Code: PRD26, Status: ACTIVE).', 'product', 27, 0, NULL, '2026-08-13 09:14:08'),
(649, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product FLUSHING (Code: PRD26, Status: ACTIVE).', 'product', 27, 0, NULL, '2026-08-13 09:14:08'),
(651, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product FLUSHING (Code: PRD26, Status: ACTIVE).', 'product', 27, 0, NULL, '2026-08-13 09:14:08'),
(653, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BRAKE FLUID DOT-3 (Code: PRD27, Status: ACTIVE).', 'product', 28, 0, NULL, '2026-08-13 09:16:56'),
(654, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BRAKE FLUID DOT-3 (Code: PRD27, Status: ACTIVE).', 'product', 28, 0, NULL, '2026-08-13 09:16:56'),
(656, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BRAKE FLUID DOT-3 (Code: PRD27, Status: ACTIVE).', 'product', 28, 0, NULL, '2026-08-13 09:16:56'),
(658, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ROBERLO SILTEX 8000 (Code: PRD28, Status: ACTIVE).', 'product', 29, 0, NULL, '2026-08-13 09:17:03'),
(659, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ROBERLO SILTEX 8000 (Code: PRD28, Status: ACTIVE).', 'product', 29, 0, NULL, '2026-08-13 09:17:03'),
(661, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product ROBERLO SILTEX 8000 (Code: PRD28, Status: ACTIVE).', 'product', 29, 0, NULL, '2026-08-13 09:17:03'),
(663, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product CABIN FILTER (87139-0N010) (Code: PRD29, Status: ACTIVE).', 'product', 30, 0, NULL, '2026-08-13 09:17:10'),
(664, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product CABIN FILTER (87139-0N010) (Code: PRD29, Status: ACTIVE).', 'product', 30, 0, NULL, '2026-08-13 09:17:10'),
(666, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product CABIN FILTER (87139-0N010) (Code: PRD29, Status: ACTIVE).', 'product', 30, 0, NULL, '2026-08-13 09:17:10'),
(668, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product WIRE (Code: PRD30, Status: ACTIVE).', 'product', 31, 0, NULL, '2026-08-13 09:17:17'),
(669, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product WIRE (Code: PRD30, Status: ACTIVE).', 'product', 31, 0, NULL, '2026-08-13 09:17:17'),
(671, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product WIRE (Code: PRD30, Status: ACTIVE).', 'product', 31, 0, NULL, '2026-08-13 09:17:17'),
(673, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product STAB. CLAMP (TRANSFORMER) (Code: PRD31, Status: ACTIVE).', 'product', 32, 0, NULL, '2026-08-13 09:17:32'),
(674, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product STAB. CLAMP (TRANSFORMER) (Code: PRD31, Status: ACTIVE).', 'product', 32, 0, NULL, '2026-08-13 09:17:32'),
(676, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product STAB. CLAMP (TRANSFORMER) (Code: PRD31, Status: ACTIVE).', 'product', 32, 0, NULL, '2026-08-13 09:17:32'),
(678, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BRAKE PADS (MIRAGE) (Code: PRD32, Status: ACTIVE).', 'product', 33, 0, NULL, '2026-08-13 09:17:40'),
(679, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BRAKE PADS (MIRAGE) (Code: PRD32, Status: ACTIVE).', 'product', 33, 0, NULL, '2026-08-13 09:17:40'),
(681, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product BRAKE PADS (MIRAGE) (Code: PRD32, Status: ACTIVE).', 'product', 33, 0, NULL, '2026-08-13 09:17:40'),
(683, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product OIL FILTER-NAVARA 231 (Code: PRD33, Status: ACTIVE).', 'product', 34, 0, NULL, '2026-08-13 09:17:58'),
(684, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product OIL FILTER-NAVARA 231 (Code: PRD33, Status: ACTIVE).', 'product', 34, 0, NULL, '2026-08-13 09:17:58'),
(686, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product OIL FILTER-NAVARA 231 (Code: PRD33, Status: ACTIVE).', 'product', 34, 0, NULL, '2026-08-13 09:17:58'),
(688, 4, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GEAR OIL -PETRON NEXUS (Code: PRD34, Status: ACTIVE).', 'product', 35, 0, NULL, '2026-08-13 09:18:07'),
(689, 16, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GEAR OIL -PETRON NEXUS (Code: PRD34, Status: ACTIVE).', 'product', 35, 0, NULL, '2026-08-13 09:18:07'),
(691, 7, NULL, 'system', 'Product Added', 'Erin Patricia Martinez added product GEAR OIL -PETRON NEXUS (Code: PRD34, Status: ACTIVE).', 'product', 35, 0, NULL, '2026-08-13 09:18:07'),
(693, 4, NULL, 'system', 'Job Order Deleted', 'Dj Guingue Cortez deleted job order #JO001.', 'job_order', 5, 0, NULL, '2026-08-14 13:17:52'),
(694, 16, NULL, 'system', 'Job Order Deleted', 'Dj Guingue Cortez deleted job order #JO001.', 'job_order', 5, 0, NULL, '2026-08-14 13:17:52'),
(697, 7, NULL, 'system', 'Job Order Deleted', 'Dj Guingue Cortez deleted job order #JO001.', 'job_order', 5, 0, NULL, '2026-08-14 13:17:52'),
(700, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-17 01:15:42'),
(701, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-17 01:15:42'),
(703, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-17 01:15:42'),
(706, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-17 01:17:26'),
(707, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-17 01:17:26'),
(709, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-17 01:17:26'),
(712, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-17 01:22:27'),
(713, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-17 01:22:27'),
(715, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-08-17 01:22:27'),
(718, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service HEAVY PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 5, 0, NULL, '2026-08-17 01:26:21'),
(719, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service HEAVY PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 5, 0, NULL, '2026-08-17 01:26:21'),
(721, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service HEAVY PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 5, 0, NULL, '2026-08-17 01:26:21'),
(724, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service HEAVY PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 5, 0, NULL, '2026-08-17 01:35:21'),
(725, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service HEAVY PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 5, 0, NULL, '2026-08-17 01:35:21'),
(727, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service HEAVY PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 5, 0, NULL, '2026-08-17 01:35:21'),
(730, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service LIGHT PMS (Price: ₱0.00, Status: ACTIVE).', 'service', 18, 0, NULL, '2026-08-17 01:37:34'),
(731, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service LIGHT PMS (Price: ₱0.00, Status: ACTIVE).', 'service', 18, 0, NULL, '2026-08-17 01:37:34'),
(733, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service LIGHT PMS (Price: ₱0.00, Status: ACTIVE).', 'service', 18, 0, NULL, '2026-08-17 01:37:34'),
(736, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-30 (Status: ACTIVE -> INACTIVE).', 'product', 6, 0, NULL, '2026-08-17 01:41:37'),
(737, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-30 (Status: ACTIVE -> INACTIVE).', 'product', 6, 0, NULL, '2026-08-17 01:41:37'),
(739, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-30 (Status: ACTIVE -> INACTIVE).', 'product', 6, 0, NULL, '2026-08-17 01:41:37'),
(741, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-30 (Status: INACTIVE -> ACTIVE).', 'product', 6, 0, NULL, '2026-08-17 01:41:50'),
(742, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-30 (Status: INACTIVE -> ACTIVE).', 'product', 6, 0, NULL, '2026-08-17 01:41:50'),
(744, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-30 (Status: INACTIVE -> ACTIVE).', 'product', 6, 0, NULL, '2026-08-17 01:41:50'),
(746, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock ENGINE OIL 5W-30 (Quantity: -20).', 'inventory_transaction', 6, 0, NULL, '2026-08-17 01:43:33'),
(747, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock ENGINE OIL 5W-30 (Quantity: -20).', 'inventory_transaction', 6, 0, NULL, '2026-08-17 01:43:33'),
(750, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock ENGINE OIL 5W-30 (Quantity: -20).', 'inventory_transaction', 6, 0, NULL, '2026-08-17 01:43:33'),
(752, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock ENGINE OIL 5W-40 (Quantity: -11).', 'inventory_transaction', 13, 0, NULL, '2026-08-17 01:43:57'),
(753, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock ENGINE OIL 5W-40 (Quantity: -11).', 'inventory_transaction', 13, 0, NULL, '2026-08-17 01:43:57'),
(756, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock ENGINE OIL 5W-40 (Quantity: -11).', 'inventory_transaction', 13, 0, NULL, '2026-08-17 01:43:57'),
(758, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock OIL FILTER 415 (Quantity: -10).', 'inventory_transaction', 7, 0, NULL, '2026-08-17 01:44:22'),
(759, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock OIL FILTER 415 (Quantity: -10).', 'inventory_transaction', 7, 0, NULL, '2026-08-17 01:44:22'),
(762, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock OIL FILTER 415 (Quantity: -10).', 'inventory_transaction', 7, 0, NULL, '2026-08-17 01:44:22'),
(764, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock OIL FITER 110 (Quantity: -10).', 'inventory_transaction', 8, 0, NULL, '2026-08-17 01:44:49'),
(765, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock OIL FITER 110 (Quantity: -10).', 'inventory_transaction', 8, 0, NULL, '2026-08-17 01:44:49'),
(768, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock OIL FITER 110 (Quantity: -10).', 'inventory_transaction', 8, 0, NULL, '2026-08-17 01:44:49'),
(770, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock BRAKE CLEANER (Quantity: -17).', 'inventory_transaction', 12, 0, NULL, '2026-08-17 01:45:34'),
(771, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock BRAKE CLEANER (Quantity: -17).', 'inventory_transaction', 12, 0, NULL, '2026-08-17 01:45:34'),
(774, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock BRAKE CLEANER (Quantity: -17).', 'inventory_transaction', 12, 0, NULL, '2026-08-17 01:45:34'),
(776, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock COOLANT GREEN (Quantity: +4).', 'inventory_transaction', 20, 0, NULL, '2026-08-17 01:46:42'),
(777, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock COOLANT GREEN (Quantity: +4).', 'inventory_transaction', 20, 0, NULL, '2026-08-17 01:46:42'),
(780, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock COOLANT GREEN (Quantity: +4).', 'inventory_transaction', 20, 0, NULL, '2026-08-17 01:46:42'),
(782, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock ATF LV MV (STOCKS) (Quantity: -19).', 'inventory_transaction', 9, 0, NULL, '2026-08-17 01:47:19'),
(783, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock ATF LV MV (STOCKS) (Quantity: -19).', 'inventory_transaction', 9, 0, NULL, '2026-08-17 01:47:19'),
(786, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock ATF LV MV (STOCKS) (Quantity: -19).', 'inventory_transaction', 9, 0, NULL, '2026-08-17 01:47:19'),
(788, 4, NULL, 'system', 'Product Added', 'Lovely Joyce Gambong added product ATF SAE-20 (Code: PRD35, Status: ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 01:49:49'),
(789, 16, NULL, 'system', 'Product Added', 'Lovely Joyce Gambong added product ATF SAE-20 (Code: PRD35, Status: ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 01:49:49'),
(791, 7, NULL, 'system', 'Product Added', 'Lovely Joyce Gambong added product ATF SAE-20 (Code: PRD35, Status: ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 01:49:49'),
(793, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 01:50:06'),
(794, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 01:50:06'),
(796, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 01:50:06'),
(798, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 01:50:28'),
(799, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 01:50:28'),
(801, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 01:50:28'),
(803, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 01:50:36'),
(804, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 01:50:36'),
(806, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 01:50:36'),
(808, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-17 01:50:46'),
(809, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-17 01:50:46'),
(811, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-17 01:50:46'),
(813, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE FLUID DOT-3 (Status: ACTIVE -> ACTIVE).', 'product', 28, 0, NULL, '2026-08-17 01:51:27'),
(814, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE FLUID DOT-3 (Status: ACTIVE -> ACTIVE).', 'product', 28, 0, NULL, '2026-08-17 01:51:27'),
(816, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE FLUID DOT-3 (Status: ACTIVE -> ACTIVE).', 'product', 28, 0, NULL, '2026-08-17 01:51:27'),
(818, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock BRAKE FLUID DOT-3 (Quantity: +9).', 'inventory_transaction', 28, 0, NULL, '2026-08-17 01:51:40'),
(819, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock BRAKE FLUID DOT-3 (Quantity: +9).', 'inventory_transaction', 28, 0, NULL, '2026-08-17 01:51:40'),
(822, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock BRAKE FLUID DOT-3 (Quantity: +9).', 'inventory_transaction', 28, 0, NULL, '2026-08-17 01:51:40'),
(824, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product AIR FILTER (MULTI-VEHICLE) (Status: ACTIVE -> ACTIVE).', 'product', 5, 0, NULL, '2026-08-17 02:06:09'),
(825, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product AIR FILTER (MULTI-VEHICLE) (Status: ACTIVE -> ACTIVE).', 'product', 5, 0, NULL, '2026-08-17 02:06:09'),
(827, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product AIR FILTER (MULTI-VEHICLE) (Status: ACTIVE -> ACTIVE).', 'product', 5, 0, NULL, '2026-08-17 02:06:09'),
(829, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock AIR FILTER (MULTI-VEHICLE) (Quantity: -20).', 'inventory_transaction', 5, 0, NULL, '2026-08-17 02:06:18'),
(830, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock AIR FILTER (MULTI-VEHICLE) (Quantity: -20).', 'inventory_transaction', 5, 0, NULL, '2026-08-17 02:06:18'),
(833, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock AIR FILTER (MULTI-VEHICLE) (Quantity: -20).', 'inventory_transaction', 5, 0, NULL, '2026-08-17 02:06:18'),
(835, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product AIR FILTER (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 4, 0, NULL, '2026-08-17 02:07:30'),
(836, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product AIR FILTER (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 4, 0, NULL, '2026-08-17 02:07:30'),
(838, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product AIR FILTER (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 4, 0, NULL, '2026-08-17 02:07:30'),
(840, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock AIR FILTER (TRANSFORMER) (Quantity: -20).', 'inventory_transaction', 4, 0, NULL, '2026-08-17 02:07:39'),
(841, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock AIR FILTER (TRANSFORMER) (Quantity: -20).', 'inventory_transaction', 4, 0, NULL, '2026-08-17 02:07:39'),
(844, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock AIR FILTER (TRANSFORMER) (Quantity: -20).', 'inventory_transaction', 4, 0, NULL, '2026-08-17 02:07:39'),
(846, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 02:08:17'),
(847, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 02:08:17'),
(849, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 02:08:17'),
(851, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 02:08:35'),
(852, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 02:08:35'),
(854, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 02:08:35'),
(856, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BATTERY (IMARFLEX) (Status: ACTIVE -> ACTIVE).', 'product', 21, 0, NULL, '2026-08-17 02:12:52'),
(857, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BATTERY (IMARFLEX) (Status: ACTIVE -> ACTIVE).', 'product', 21, 0, NULL, '2026-08-17 02:12:52'),
(859, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BATTERY (IMARFLEX) (Status: ACTIVE -> ACTIVE).', 'product', 21, 0, NULL, '2026-08-17 02:12:52'),
(861, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-17 02:14:57'),
(862, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-17 02:14:57'),
(864, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-17 02:14:57'),
(866, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE FLUID DOT-3 (Status: ACTIVE -> ACTIVE).', 'product', 28, 0, NULL, '2026-08-17 02:15:46'),
(867, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE FLUID DOT-3 (Status: ACTIVE -> ACTIVE).', 'product', 28, 0, NULL, '2026-08-17 02:15:46'),
(869, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE FLUID DOT-3 (Status: ACTIVE -> ACTIVE).', 'product', 28, 0, NULL, '2026-08-17 02:15:46'),
(871, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock BRAKE FLUID DOT-3 (Quantity: -1).', 'inventory_transaction', 28, 0, NULL, '2026-08-17 02:15:57'),
(872, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock BRAKE FLUID DOT-3 (Quantity: -1).', 'inventory_transaction', 28, 0, NULL, '2026-08-17 02:15:57'),
(875, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock BRAKE FLUID DOT-3 (Quantity: -1).', 'inventory_transaction', 28, 0, NULL, '2026-08-17 02:15:57'),
(877, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-17 02:16:04'),
(878, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-17 02:16:04'),
(880, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 12, 0, NULL, '2026-08-17 02:16:04'),
(882, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE PADS (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 22, 0, NULL, '2026-08-17 02:17:43'),
(883, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE PADS (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 22, 0, NULL, '2026-08-17 02:17:43'),
(885, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE PADS (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 22, 0, NULL, '2026-08-17 02:17:43'),
(887, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE PADS (TRANSORMER) (Status: ACTIVE -> ACTIVE).', 'product', 33, 0, NULL, '2026-08-17 02:18:11'),
(888, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE PADS (TRANSORMER) (Status: ACTIVE -> ACTIVE).', 'product', 33, 0, NULL, '2026-08-17 02:18:11'),
(890, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product BRAKE PADS (TRANSORMER) (Status: ACTIVE -> ACTIVE).', 'product', 33, 0, NULL, '2026-08-17 02:18:11'),
(892, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product CABIN FILTER (87139-0N010) (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-08-17 02:19:12'),
(893, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product CABIN FILTER (87139-0N010) (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-08-17 02:19:12'),
(895, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product CABIN FILTER (87139-0N010) (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-08-17 02:19:12'),
(897, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product THROTTLE/CARB CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 16, 0, NULL, '2026-08-17 02:31:36'),
(898, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product THROTTLE/CARB CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 16, 0, NULL, '2026-08-17 02:31:36'),
(900, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product THROTTLE/CARB CLEANER (Status: ACTIVE -> ACTIVE).', 'product', 16, 0, NULL, '2026-08-17 02:31:36'),
(902, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT BLUE (Status: ACTIVE -> ACTIVE).', 'product', 19, 0, NULL, '2026-08-17 02:33:38'),
(903, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT BLUE (Status: ACTIVE -> ACTIVE).', 'product', 19, 0, NULL, '2026-08-17 02:33:38'),
(905, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT BLUE (Status: ACTIVE -> ACTIVE).', 'product', 19, 0, NULL, '2026-08-17 02:33:38'),
(907, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT GREEN (Status: ACTIVE -> ACTIVE).', 'product', 20, 0, NULL, '2026-08-17 02:34:06'),
(908, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT GREEN (Status: ACTIVE -> ACTIVE).', 'product', 20, 0, NULL, '2026-08-17 02:34:06'),
(910, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT GREEN (Status: ACTIVE -> ACTIVE).', 'product', 20, 0, NULL, '2026-08-17 02:34:06'),
(912, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT GREEN (Status: ACTIVE -> ACTIVE).', 'product', 20, 0, NULL, '2026-08-17 02:34:25'),
(913, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT GREEN (Status: ACTIVE -> ACTIVE).', 'product', 20, 0, NULL, '2026-08-17 02:34:25'),
(915, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT GREEN (Status: ACTIVE -> ACTIVE).', 'product', 20, 0, NULL, '2026-08-17 02:34:25'),
(917, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT BLUE (Status: ACTIVE -> ACTIVE).', 'product', 19, 0, NULL, '2026-08-17 02:34:38'),
(918, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT BLUE (Status: ACTIVE -> ACTIVE).', 'product', 19, 0, NULL, '2026-08-17 02:34:38'),
(920, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product COOLANT BLUE (Status: ACTIVE -> ACTIVE).', 'product', 19, 0, NULL, '2026-08-17 02:34:38'),
(922, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-30 (Status: ACTIVE -> ACTIVE).', 'product', 6, 0, NULL, '2026-08-17 02:35:04'),
(923, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-30 (Status: ACTIVE -> ACTIVE).', 'product', 6, 0, NULL, '2026-08-17 02:35:04'),
(925, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-30 (Status: ACTIVE -> ACTIVE).', 'product', 6, 0, NULL, '2026-08-17 02:35:04'),
(927, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-40 (Status: ACTIVE -> ACTIVE).', 'product', 13, 0, NULL, '2026-08-17 02:35:19'),
(928, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-40 (Status: ACTIVE -> ACTIVE).', 'product', 13, 0, NULL, '2026-08-17 02:35:19'),
(930, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ENGINE OIL 5W-40 (Status: ACTIVE -> ACTIVE).', 'product', 13, 0, NULL, '2026-08-17 02:35:19'),
(932, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product FLUSHING (Status: ACTIVE -> ACTIVE).', 'product', 27, 0, NULL, '2026-08-17 02:37:21'),
(933, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product FLUSHING (Status: ACTIVE -> ACTIVE).', 'product', 27, 0, NULL, '2026-08-17 02:37:21'),
(935, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product FLUSHING (Status: ACTIVE -> ACTIVE).', 'product', 27, 0, NULL, '2026-08-17 02:37:21'),
(937, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock FRONT HUB BEARING (MIRAGE) (Quantity: +1).', 'inventory_transaction', 2, 0, NULL, '2026-08-17 02:37:58'),
(938, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock FRONT HUB BEARING (MIRAGE) (Quantity: +1).', 'inventory_transaction', 2, 0, NULL, '2026-08-17 02:37:58'),
(941, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock FRONT HUB BEARING (MIRAGE) (Quantity: +1).', 'inventory_transaction', 2, 0, NULL, '2026-08-17 02:37:58'),
(943, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock FRONT HUB BEARING (MIRAGE) (Quantity: -20).', 'inventory_transaction', 2, 0, NULL, '2026-08-17 02:38:07'),
(944, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock FRONT HUB BEARING (MIRAGE) (Quantity: -20).', 'inventory_transaction', 2, 0, NULL, '2026-08-17 02:38:07'),
(947, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock FRONT HUB BEARING (MIRAGE) (Quantity: -20).', 'inventory_transaction', 2, 0, NULL, '2026-08-17 02:38:07'),
(949, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product FRONT HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 2, 0, NULL, '2026-08-17 02:38:23'),
(950, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product FRONT HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 2, 0, NULL, '2026-08-17 02:38:23'),
(952, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product FRONT HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 2, 0, NULL, '2026-08-17 02:38:23'),
(954, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 02:39:01'),
(955, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 02:39:01'),
(957, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF LV MV (STOCKS) (Status: ACTIVE -> ACTIVE).', 'product', 9, 0, NULL, '2026-08-17 02:39:01'),
(959, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 02:39:13'),
(960, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 02:39:13'),
(962, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ATF SAE-20 (Status: ACTIVE -> ACTIVE).', 'product', 36, 0, NULL, '2026-08-17 02:39:13'),
(964, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product GEAR OIL -PETRON NEXUS (Status: ACTIVE -> ACTIVE).', 'product', 35, 0, NULL, '2026-08-17 02:41:06'),
(965, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product GEAR OIL -PETRON NEXUS (Status: ACTIVE -> ACTIVE).', 'product', 35, 0, NULL, '2026-08-17 02:41:06'),
(967, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product GEAR OIL -PETRON NEXUS (Status: ACTIVE -> ACTIVE).', 'product', 35, 0, NULL, '2026-08-17 02:41:06'),
(969, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock GEAR OIL -PETRON NEXUS (Quantity: +1).', 'inventory_transaction', 35, 0, NULL, '2026-08-17 02:41:13'),
(970, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock GEAR OIL -PETRON NEXUS (Quantity: +1).', 'inventory_transaction', 35, 0, NULL, '2026-08-17 02:41:13'),
(973, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock GEAR OIL -PETRON NEXUS (Quantity: +1).', 'inventory_transaction', 35, 0, NULL, '2026-08-17 02:41:13'),
(975, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product GEAR OIL (Status: ACTIVE -> ACTIVE).', 'product', 17, 0, NULL, '2026-08-17 02:41:35'),
(976, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product GEAR OIL (Status: ACTIVE -> ACTIVE).', 'product', 17, 0, NULL, '2026-08-17 02:41:35'),
(978, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product GEAR OIL (Status: ACTIVE -> ACTIVE).', 'product', 17, 0, NULL, '2026-08-17 02:41:35'),
(980, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER 111 (Status: ACTIVE -> ACTIVE).', 'product', 10, 0, NULL, '2026-08-17 02:43:47'),
(981, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER 111 (Status: ACTIVE -> ACTIVE).', 'product', 10, 0, NULL, '2026-08-17 02:43:47'),
(983, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER 111 (Status: ACTIVE -> ACTIVE).', 'product', 10, 0, NULL, '2026-08-17 02:43:47'),
(985, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock OIL FILTER 111 (Quantity: -18).', 'inventory_transaction', 10, 0, NULL, '2026-08-17 02:44:03'),
(986, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock OIL FILTER 111 (Quantity: -18).', 'inventory_transaction', 10, 0, NULL, '2026-08-17 02:44:03'),
(989, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock OIL FILTER 111 (Quantity: -18).', 'inventory_transaction', 10, 0, NULL, '2026-08-17 02:44:03'),
(991, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock OIL FILTER 111 (Quantity: +5).', 'inventory_transaction', 10, 0, NULL, '2026-08-17 02:44:32'),
(992, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock OIL FILTER 111 (Quantity: +5).', 'inventory_transaction', 10, 0, NULL, '2026-08-17 02:44:32'),
(995, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock OIL FILTER 111 (Quantity: +5).', 'inventory_transaction', 10, 0, NULL, '2026-08-17 02:44:32'),
(997, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER 415 (Status: ACTIVE -> ACTIVE).', 'product', 7, 0, NULL, '2026-08-17 02:45:17'),
(998, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER 415 (Status: ACTIVE -> ACTIVE).', 'product', 7, 0, NULL, '2026-08-17 02:45:17'),
(1000, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER 415 (Status: ACTIVE -> ACTIVE).', 'product', 7, 0, NULL, '2026-08-17 02:45:17'),
(1002, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER 111 (Status: ACTIVE -> ACTIVE).', 'product', 10, 0, NULL, '2026-08-17 02:45:29'),
(1003, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER 111 (Status: ACTIVE -> ACTIVE).', 'product', 10, 0, NULL, '2026-08-17 02:45:29'),
(1005, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER 111 (Status: ACTIVE -> ACTIVE).', 'product', 10, 0, NULL, '2026-08-17 02:45:29'),
(1007, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock OIL FILTER-NAVARA 231 (Quantity: +1).', 'inventory_transaction', 34, 0, NULL, '2026-08-17 02:46:39'),
(1008, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock OIL FILTER-NAVARA 231 (Quantity: +1).', 'inventory_transaction', 34, 0, NULL, '2026-08-17 02:46:39'),
(1011, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock OIL FILTER-NAVARA 231 (Quantity: +1).', 'inventory_transaction', 34, 0, NULL, '2026-08-17 02:46:39'),
(1013, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER-NAVARA 231 (Status: ACTIVE -> ACTIVE).', 'product', 34, 0, NULL, '2026-08-17 02:47:43'),
(1014, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER-NAVARA 231 (Status: ACTIVE -> ACTIVE).', 'product', 34, 0, NULL, '2026-08-17 02:47:43'),
(1016, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FILTER-NAVARA 231 (Status: ACTIVE -> ACTIVE).', 'product', 34, 0, NULL, '2026-08-17 02:47:43'),
(1018, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FITER 110 (Status: ACTIVE -> ACTIVE).', 'product', 8, 0, NULL, '2026-08-17 02:48:59'),
(1019, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FITER 110 (Status: ACTIVE -> ACTIVE).', 'product', 8, 0, NULL, '2026-08-17 02:48:59'),
(1021, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product OIL FITER 110 (Status: ACTIVE -> ACTIVE).', 'product', 8, 0, NULL, '2026-08-17 02:48:59'),
(1023, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock PENETRATING (Quantity: +3).', 'inventory_transaction', 15, 0, NULL, '2026-08-17 02:49:21'),
(1024, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock PENETRATING (Quantity: +3).', 'inventory_transaction', 15, 0, NULL, '2026-08-17 02:49:21'),
(1027, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock PENETRATING (Quantity: +3).', 'inventory_transaction', 15, 0, NULL, '2026-08-17 02:49:21'),
(1029, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product PENETRATING (Status: ACTIVE -> ACTIVE).', 'product', 15, 0, NULL, '2026-08-17 02:51:01'),
(1030, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product PENETRATING (Status: ACTIVE -> ACTIVE).', 'product', 15, 0, NULL, '2026-08-17 02:51:01'),
(1032, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product PENETRATING (Status: ACTIVE -> ACTIVE).', 'product', 15, 0, NULL, '2026-08-17 02:51:01'),
(1034, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product PETRON ATF SAE-20 (Status: ACTIVE -> INACTIVE).', 'product', 11, 0, NULL, '2026-08-17 02:51:23'),
(1035, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product PETRON ATF SAE-20 (Status: ACTIVE -> INACTIVE).', 'product', 11, 0, NULL, '2026-08-17 02:51:23'),
(1037, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product PETRON ATF SAE-20 (Status: ACTIVE -> INACTIVE).', 'product', 11, 0, NULL, '2026-08-17 02:51:23'),
(1039, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product REAR HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 14, 0, NULL, '2026-08-17 02:52:23');
INSERT INTO `notifications` (`id`, `user_id`, `staff_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `read_at`, `created_at`) VALUES
(1040, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product REAR HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 14, 0, NULL, '2026-08-17 02:52:23'),
(1042, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product REAR HUB BEARING (MIRAGE) (Status: ACTIVE -> ACTIVE).', 'product', 14, 0, NULL, '2026-08-17 02:52:23'),
(1044, 4, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock REAR HUB BEARING (MIRAGE) (Quantity: -8).', 'inventory_transaction', 14, 0, NULL, '2026-08-17 02:52:34'),
(1045, 16, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock REAR HUB BEARING (MIRAGE) (Quantity: -8).', 'inventory_transaction', 14, 0, NULL, '2026-08-17 02:52:34'),
(1048, 7, NULL, 'system', 'Inventory Stock Out', 'Lovely Joyce Gambong deducted stock REAR HUB BEARING (MIRAGE) (Quantity: -8).', 'inventory_transaction', 14, 0, NULL, '2026-08-17 02:52:34'),
(1050, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock ROBERLO SILTEX 8000 (Quantity: +4).', 'inventory_transaction', 29, 0, NULL, '2026-08-17 02:54:22'),
(1051, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock ROBERLO SILTEX 8000 (Quantity: +4).', 'inventory_transaction', 29, 0, NULL, '2026-08-17 02:54:22'),
(1054, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock ROBERLO SILTEX 8000 (Quantity: +4).', 'inventory_transaction', 29, 0, NULL, '2026-08-17 02:54:22'),
(1056, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ROBERLO SILTEX 8000 (Status: ACTIVE -> ACTIVE).', 'product', 29, 0, NULL, '2026-08-17 02:55:15'),
(1057, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ROBERLO SILTEX 8000 (Status: ACTIVE -> ACTIVE).', 'product', 29, 0, NULL, '2026-08-17 02:55:15'),
(1059, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product ROBERLO SILTEX 8000 (Status: ACTIVE -> ACTIVE).', 'product', 29, 0, NULL, '2026-08-17 02:55:15'),
(1061, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock STAB. CLAMP (TRANSFORMER) (Quantity: +1).', 'inventory_transaction', 24, 0, NULL, '2026-08-17 02:55:44'),
(1062, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock STAB. CLAMP (TRANSFORMER) (Quantity: +1).', 'inventory_transaction', 24, 0, NULL, '2026-08-17 02:55:44'),
(1065, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock STAB. CLAMP (TRANSFORMER) (Quantity: +1).', 'inventory_transaction', 24, 0, NULL, '2026-08-17 02:55:44'),
(1067, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. LINK (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 32, 0, NULL, '2026-08-17 02:56:30'),
(1068, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. LINK (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 32, 0, NULL, '2026-08-17 02:56:30'),
(1070, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. LINK (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 32, 0, NULL, '2026-08-17 02:56:30'),
(1072, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock STAB. LINK (TRANSFORMER) (Quantity: +3).', 'inventory_transaction', 23, 0, NULL, '2026-08-17 02:56:57'),
(1073, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock STAB. LINK (TRANSFORMER) (Quantity: +3).', 'inventory_transaction', 23, 0, NULL, '2026-08-17 02:56:57'),
(1076, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock STAB. LINK (TRANSFORMER) (Quantity: +3).', 'inventory_transaction', 23, 0, NULL, '2026-08-17 02:56:57'),
(1078, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. CLAMP (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 24, 0, NULL, '2026-08-17 02:57:12'),
(1079, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. CLAMP (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 24, 0, NULL, '2026-08-17 02:57:12'),
(1081, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. CLAMP (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 24, 0, NULL, '2026-08-17 02:57:12'),
(1083, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. LINK (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 23, 0, NULL, '2026-08-17 02:57:24'),
(1084, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. LINK (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 23, 0, NULL, '2026-08-17 02:57:24'),
(1086, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. LINK (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 23, 0, NULL, '2026-08-17 02:57:24'),
(1088, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. LINK (TRANSFORMER) (Status: ACTIVE -> INACTIVE).', 'product', 32, 0, NULL, '2026-08-17 02:57:45'),
(1089, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. LINK (TRANSFORMER) (Status: ACTIVE -> INACTIVE).', 'product', 32, 0, NULL, '2026-08-17 02:57:45'),
(1091, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product STAB. LINK (TRANSFORMER) (Status: ACTIVE -> INACTIVE).', 'product', 32, 0, NULL, '2026-08-17 02:57:45'),
(1093, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock THROTTLE/CARB CLEANER (Quantity: +3).', 'inventory_transaction', 16, 0, NULL, '2026-08-17 02:58:20'),
(1094, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock THROTTLE/CARB CLEANER (Quantity: +3).', 'inventory_transaction', 16, 0, NULL, '2026-08-17 02:58:20'),
(1097, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock THROTTLE/CARB CLEANER (Quantity: +3).', 'inventory_transaction', 16, 0, NULL, '2026-08-17 02:58:20'),
(1099, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product VALVE COVER GASKET (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 25, 0, NULL, '2026-08-17 02:59:22'),
(1100, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product VALVE COVER GASKET (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 25, 0, NULL, '2026-08-17 02:59:22'),
(1102, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product VALVE COVER GASKET (TRANSFORMER) (Status: ACTIVE -> ACTIVE).', 'product', 25, 0, NULL, '2026-08-17 02:59:22'),
(1104, 4, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product WIRE (Status: ACTIVE -> ACTIVE).', 'product', 31, 0, NULL, '2026-08-17 02:59:58'),
(1105, 16, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product WIRE (Status: ACTIVE -> ACTIVE).', 'product', 31, 0, NULL, '2026-08-17 02:59:58'),
(1107, 7, NULL, 'system', 'Product Updated', 'Lovely Joyce Gambong updated product WIRE (Status: ACTIVE -> ACTIVE).', 'product', 31, 0, NULL, '2026-08-17 02:59:58'),
(1109, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIRCON CLEANING (SINGLE EVAPORATOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 19, 0, NULL, '2026-08-17 03:18:12'),
(1110, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIRCON CLEANING (SINGLE EVAPORATOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 19, 0, NULL, '2026-08-17 03:18:12'),
(1112, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIRCON CLEANING (SINGLE EVAPORATOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 19, 0, NULL, '2026-08-17 03:18:12'),
(1115, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service AIRCON CLEANING (SINGLE EVAPORATOR) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 19, 0, NULL, '2026-08-17 03:18:38'),
(1116, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service AIRCON CLEANING (SINGLE EVAPORATOR) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 19, 0, NULL, '2026-08-17 03:18:38'),
(1118, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service AIRCON CLEANING (SINGLE EVAPORATOR) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 19, 0, NULL, '2026-08-17 03:18:38'),
(1121, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIRCON CLEANING (DUAL EVAPORATOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 20, 0, NULL, '2026-08-17 03:19:12'),
(1122, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIRCON CLEANING (DUAL EVAPORATOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 20, 0, NULL, '2026-08-17 03:19:12'),
(1124, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIRCON CLEANING (DUAL EVAPORATOR) (Price: ₱0.00, Status: ACTIVE).', 'service', 20, 0, NULL, '2026-08-17 03:19:12'),
(1127, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service AIRCON CLEANING (DUAL EVAPORATOR) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 20, 0, NULL, '2026-08-17 03:19:26'),
(1128, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service AIRCON CLEANING (DUAL EVAPORATOR) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 20, 0, NULL, '2026-08-17 03:19:26'),
(1130, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service AIRCON CLEANING (DUAL EVAPORATOR) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 20, 0, NULL, '2026-08-17 03:19:26'),
(1133, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service CHANGE OIL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 4, 0, NULL, '2026-08-17 03:19:39'),
(1134, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service CHANGE OIL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 4, 0, NULL, '2026-08-17 03:19:39'),
(1136, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service CHANGE OIL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 4, 0, NULL, '2026-08-17 03:19:39'),
(1139, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service CHANGE OIL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 4, 0, NULL, '2026-08-17 03:21:28'),
(1140, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service CHANGE OIL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 4, 0, NULL, '2026-08-17 03:21:28'),
(1142, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service CHANGE OIL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 4, 0, NULL, '2026-08-17 03:21:28'),
(1145, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service EGR, INTAKE AND TURBO CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 21, 0, NULL, '2026-08-17 03:22:13'),
(1146, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service EGR, INTAKE AND TURBO CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 21, 0, NULL, '2026-08-17 03:22:13'),
(1148, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service EGR, INTAKE AND TURBO CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 21, 0, NULL, '2026-08-17 03:22:13'),
(1151, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service EGR AND INTAKE CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 22, 0, NULL, '2026-08-17 03:22:59'),
(1152, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service EGR AND INTAKE CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 22, 0, NULL, '2026-08-17 03:22:59'),
(1154, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service EGR AND INTAKE CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 22, 0, NULL, '2026-08-17 03:22:59'),
(1157, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service TURBO CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 23, 0, NULL, '2026-08-17 03:23:34'),
(1158, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service TURBO CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 23, 0, NULL, '2026-08-17 03:23:34'),
(1160, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service TURBO CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 23, 0, NULL, '2026-08-17 03:23:34'),
(1163, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service EGR, INTAKE, AND TURBO CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 24, 0, NULL, '2026-08-17 03:24:26'),
(1164, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service EGR, INTAKE, AND TURBO CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 24, 0, NULL, '2026-08-17 03:24:26'),
(1166, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service EGR, INTAKE, AND TURBO CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 24, 0, NULL, '2026-08-17 03:24:26'),
(1169, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service CARWASH (Price: ₱0.00, Status: ACTIVE).', 'service', 25, 0, NULL, '2026-08-17 03:25:02'),
(1170, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service CARWASH (Price: ₱0.00, Status: ACTIVE).', 'service', 25, 0, NULL, '2026-08-17 03:25:02'),
(1172, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service CARWASH (Price: ₱0.00, Status: ACTIVE).', 'service', 25, 0, NULL, '2026-08-17 03:25:02'),
(1175, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service WHEEL ALIGNMENT (COMPLETE) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 16, 0, NULL, '2026-08-17 03:25:35'),
(1176, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service WHEEL ALIGNMENT (COMPLETE) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 16, 0, NULL, '2026-08-17 03:25:35'),
(1178, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service WHEEL ALIGNMENT (COMPLETE) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 16, 0, NULL, '2026-08-17 03:25:35'),
(1181, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service WHEEL ALIGNMENT (TOE IN/TOE OUT) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 15, 0, NULL, '2026-08-17 03:25:55'),
(1182, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service WHEEL ALIGNMENT (TOE IN/TOE OUT) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 15, 0, NULL, '2026-08-17 03:25:55'),
(1184, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service WHEEL ALIGNMENT (TOE IN/TOE OUT) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 15, 0, NULL, '2026-08-17 03:25:55'),
(1187, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service WHEEL ALIGNMENT (TOE IN/TOE OUT) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 15, 0, NULL, '2026-08-17 03:26:03'),
(1188, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service WHEEL ALIGNMENT (TOE IN/TOE OUT) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 15, 0, NULL, '2026-08-17 03:26:03'),
(1190, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service WHEEL ALIGNMENT (TOE IN/TOE OUT) (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 15, 0, NULL, '2026-08-17 03:26:03'),
(1193, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service BRAKE CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 26, 0, NULL, '2026-08-17 03:27:21'),
(1194, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service BRAKE CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 26, 0, NULL, '2026-08-17 03:27:21'),
(1196, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service BRAKE CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 26, 0, NULL, '2026-08-17 03:27:21'),
(1199, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service FUEL INJECTOR CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 14, 0, NULL, '2026-08-17 03:28:19'),
(1200, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service FUEL INJECTOR CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 14, 0, NULL, '2026-08-17 03:28:19'),
(1202, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service FUEL INJECTOR CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 14, 0, NULL, '2026-08-17 03:28:19'),
(1205, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service DRIVE BELT REPLACEMENT (Price: ₱0.00, Status: ACTIVE).', 'service', 27, 0, NULL, '2026-08-17 03:30:05'),
(1206, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service DRIVE BELT REPLACEMENT (Price: ₱0.00, Status: ACTIVE).', 'service', 27, 0, NULL, '2026-08-17 03:30:05'),
(1208, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service DRIVE BELT REPLACEMENT (Price: ₱0.00, Status: ACTIVE).', 'service', 27, 0, NULL, '2026-08-17 03:30:05'),
(1211, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service THROTTLE BODY CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 28, 0, NULL, '2026-08-17 03:30:35'),
(1212, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service THROTTLE BODY CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 28, 0, NULL, '2026-08-17 03:30:35'),
(1214, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service THROTTLE BODY CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 28, 0, NULL, '2026-08-17 03:30:35'),
(1217, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REPLACE AUX. FAN MOTOR (Price: ₱0.00, Status: ACTIVE).', 'service', 29, 0, NULL, '2026-08-17 03:31:12'),
(1218, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REPLACE AUX. FAN MOTOR (Price: ₱0.00, Status: ACTIVE).', 'service', 29, 0, NULL, '2026-08-17 03:31:12'),
(1220, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REPLACE AUX. FAN MOTOR (Price: ₱0.00, Status: ACTIVE).', 'service', 29, 0, NULL, '2026-08-17 03:31:12'),
(1223, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service PULL OUT / INSTALL FRONT LOWER SUSP. ASSY (RH/LH) (Price: ₱0.00, Status: ACTIVE).', 'service', 30, 0, NULL, '2026-08-17 03:32:13'),
(1224, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service PULL OUT / INSTALL FRONT LOWER SUSP. ASSY (RH/LH) (Price: ₱0.00, Status: ACTIVE).', 'service', 30, 0, NULL, '2026-08-17 03:32:13'),
(1226, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service PULL OUT / INSTALL FRONT LOWER SUSP. ASSY (RH/LH) (Price: ₱0.00, Status: ACTIVE).', 'service', 30, 0, NULL, '2026-08-17 03:32:13'),
(1229, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REPLACE AIR FILTER AND CABIN FILTER (Price: ₱0.00, Status: ACTIVE).', 'service', 31, 0, NULL, '2026-08-17 03:33:06'),
(1230, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REPLACE AIR FILTER AND CABIN FILTER (Price: ₱0.00, Status: ACTIVE).', 'service', 31, 0, NULL, '2026-08-17 03:33:06'),
(1232, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REPLACE AIR FILTER AND CABIN FILTER (Price: ₱0.00, Status: ACTIVE).', 'service', 31, 0, NULL, '2026-08-17 03:33:06'),
(1235, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service RADIATOR CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 32, 0, NULL, '2026-08-17 03:33:31'),
(1236, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service RADIATOR CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 32, 0, NULL, '2026-08-17 03:33:31'),
(1238, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service RADIATOR CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 32, 0, NULL, '2026-08-17 03:33:31'),
(1241, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service RESCUE (Price: ₱0.00, Status: ACTIVE).', 'service', 33, 0, NULL, '2026-08-17 03:34:07'),
(1242, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service RESCUE (Price: ₱0.00, Status: ACTIVE).', 'service', 33, 0, NULL, '2026-08-17 03:34:07'),
(1244, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service RESCUE (Price: ₱0.00, Status: ACTIVE).', 'service', 33, 0, NULL, '2026-08-17 03:34:07'),
(1247, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service RESCUE (Status: ACTIVE -> ACTIVE; Price: ₱1,500.00).', 'service', 33, 0, NULL, '2026-08-17 03:34:42'),
(1248, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service RESCUE (Status: ACTIVE -> ACTIVE; Price: ₱1,500.00).', 'service', 33, 0, NULL, '2026-08-17 03:34:42'),
(1250, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service RESCUE (Status: ACTIVE -> ACTIVE; Price: ₱1,500.00).', 'service', 33, 0, NULL, '2026-08-17 03:34:42'),
(1253, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service TOWING (Price: ₱2,500.00, Status: ACTIVE).', 'service', 34, 0, NULL, '2026-08-17 03:35:05'),
(1254, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service TOWING (Price: ₱2,500.00, Status: ACTIVE).', 'service', 34, 0, NULL, '2026-08-17 03:35:05'),
(1256, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service TOWING (Price: ₱2,500.00, Status: ACTIVE).', 'service', 34, 0, NULL, '2026-08-17 03:35:05'),
(1259, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service CHANGE/FLUSH BRAKE FLUID (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 3, 0, NULL, '2026-08-17 03:36:55'),
(1260, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service CHANGE/FLUSH BRAKE FLUID (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 3, 0, NULL, '2026-08-17 03:36:55'),
(1262, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service CHANGE/FLUSH BRAKE FLUID (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 3, 0, NULL, '2026-08-17 03:36:55'),
(1265, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service EGR AND INTAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱3,500.00).', 'service', 22, 0, NULL, '2026-08-17 03:37:26'),
(1266, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service EGR AND INTAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱3,500.00).', 'service', 22, 0, NULL, '2026-08-17 03:37:26'),
(1268, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service EGR AND INTAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱3,500.00).', 'service', 22, 0, NULL, '2026-08-17 03:37:26'),
(1271, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service EGR AND INTAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 22, 0, NULL, '2026-08-17 03:37:41'),
(1272, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service EGR AND INTAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 22, 0, NULL, '2026-08-17 03:37:41'),
(1274, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service EGR AND INTAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 22, 0, NULL, '2026-08-17 03:37:41'),
(1277, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service STEERING RACK REPAIR - PULL OUT/INSTALL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 17, 0, NULL, '2026-08-17 03:38:11'),
(1278, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service STEERING RACK REPAIR - PULL OUT/INSTALL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 17, 0, NULL, '2026-08-17 03:38:11'),
(1280, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service STEERING RACK REPAIR - PULL OUT/INSTALL (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 17, 0, NULL, '2026-08-17 03:38:11'),
(1283, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service TIE ROD REPLACEMENT (Price: ₱0.00, Status: ACTIVE).', 'service', 35, 0, NULL, '2026-08-17 03:38:40'),
(1284, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service TIE ROD REPLACEMENT (Price: ₱0.00, Status: ACTIVE).', 'service', 35, 0, NULL, '2026-08-17 03:38:40'),
(1286, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service TIE ROD REPLACEMENT (Price: ₱0.00, Status: ACTIVE).', 'service', 35, 0, NULL, '2026-08-17 03:38:40'),
(1289, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service WHEEL BALANCING (Price: ₱0.00, Status: ACTIVE).', 'service', 36, 0, NULL, '2026-08-17 03:39:36'),
(1290, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service WHEEL BALANCING (Price: ₱0.00, Status: ACTIVE).', 'service', 36, 0, NULL, '2026-08-17 03:39:36'),
(1292, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service WHEEL BALANCING (Price: ₱0.00, Status: ACTIVE).', 'service', 36, 0, NULL, '2026-08-17 03:39:36'),
(1295, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service CHECK/CORRECT LEAK COMING INSIDE (Price: ₱0.00, Status: ACTIVE).', 'service', 37, 0, NULL, '2026-08-17 03:40:15'),
(1296, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service CHECK/CORRECT LEAK COMING INSIDE (Price: ₱0.00, Status: ACTIVE).', 'service', 37, 0, NULL, '2026-08-17 03:40:15'),
(1298, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service CHECK/CORRECT LEAK COMING INSIDE (Price: ₱0.00, Status: ACTIVE).', 'service', 37, 0, NULL, '2026-08-17 03:40:15'),
(1301, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service PULL OUT CAR MATTING (CLEAN AND DRY) (Price: ₱0.00, Status: ACTIVE).', 'service', 38, 0, NULL, '2026-08-17 03:40:52'),
(1302, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service PULL OUT CAR MATTING (CLEAN AND DRY) (Price: ₱0.00, Status: ACTIVE).', 'service', 38, 0, NULL, '2026-08-17 03:40:52'),
(1304, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service PULL OUT CAR MATTING (CLEAN AND DRY) (Price: ₱0.00, Status: ACTIVE).', 'service', 38, 0, NULL, '2026-08-17 03:40:52'),
(1307, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service OXYGEN SENSOR CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 39, 0, NULL, '2026-08-17 03:41:27'),
(1308, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service OXYGEN SENSOR CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 39, 0, NULL, '2026-08-17 03:41:27'),
(1310, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service OXYGEN SENSOR CLEANING (Price: ₱0.00, Status: ACTIVE).', 'service', 39, 0, NULL, '2026-08-17 03:41:27'),
(1313, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REPLACE SPARK PLUG (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 11, 0, NULL, '2026-08-17 03:42:13'),
(1314, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REPLACE SPARK PLUG (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 11, 0, NULL, '2026-08-17 03:42:13'),
(1316, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REPLACE SPARK PLUG (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 11, 0, NULL, '2026-08-17 03:42:13'),
(1319, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REFACE ROTO DISC (BOTH SIDES) - SEDAN (Price: ₱0.00, Status: ACTIVE).', 'service', 40, 0, NULL, '2026-08-17 03:43:07'),
(1320, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REFACE ROTO DISC (BOTH SIDES) - SEDAN (Price: ₱0.00, Status: ACTIVE).', 'service', 40, 0, NULL, '2026-08-17 03:43:07'),
(1322, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REFACE ROTO DISC (BOTH SIDES) - SEDAN (Price: ₱0.00, Status: ACTIVE).', 'service', 40, 0, NULL, '2026-08-17 03:43:07'),
(1325, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REFACE ROTO DISC (BOTH SIDES) - PICK UP, SUV (Price: ₱0.00, Status: ACTIVE).', 'service', 41, 0, NULL, '2026-08-17 03:43:33'),
(1326, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REFACE ROTO DISC (BOTH SIDES) - PICK UP, SUV (Price: ₱0.00, Status: ACTIVE).', 'service', 41, 0, NULL, '2026-08-17 03:43:33'),
(1328, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REFACE ROTO DISC (BOTH SIDES) - PICK UP, SUV (Price: ₱0.00, Status: ACTIVE).', 'service', 41, 0, NULL, '2026-08-17 03:43:33'),
(1331, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 41, 0, NULL, '2026-08-17 03:43:45'),
(1332, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 41, 0, NULL, '2026-08-17 03:43:45'),
(1334, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 41, 0, NULL, '2026-08-17 03:43:45'),
(1337, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REFACE ROTOR DISC (BOTH SIDES) - SEDAN (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 40, 0, NULL, '2026-08-17 03:43:58'),
(1338, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REFACE ROTOR DISC (BOTH SIDES) - SEDAN (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 40, 0, NULL, '2026-08-17 03:43:58'),
(1340, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REFACE ROTOR DISC (BOTH SIDES) - SEDAN (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 40, 0, NULL, '2026-08-17 03:43:58'),
(1343, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REPLACE LOWER BALL JOINT (BOTH SIDES) (Price: ₱0.00, Status: ACTIVE).', 'service', 42, 0, NULL, '2026-08-17 03:44:23'),
(1344, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REPLACE LOWER BALL JOINT (BOTH SIDES) (Price: ₱0.00, Status: ACTIVE).', 'service', 42, 0, NULL, '2026-08-17 03:44:23'),
(1346, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service REPLACE LOWER BALL JOINT (BOTH SIDES) (Price: ₱0.00, Status: ACTIVE).', 'service', 42, 0, NULL, '2026-08-17 03:44:23'),
(1349, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REPLACE SPARK PLUG (Status: ACTIVE -> ACTIVE; Price: ₱600.00).', 'service', 11, 0, NULL, '2026-08-17 03:44:52'),
(1350, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REPLACE SPARK PLUG (Status: ACTIVE -> ACTIVE; Price: ₱600.00).', 'service', 11, 0, NULL, '2026-08-17 03:44:52'),
(1352, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REPLACE SPARK PLUG (Status: ACTIVE -> ACTIVE; Price: ₱600.00).', 'service', 11, 0, NULL, '2026-08-17 03:44:52'),
(1355, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service LIGHT PMS GAS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 18, 0, NULL, '2026-08-17 03:56:28'),
(1356, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service LIGHT PMS GAS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 18, 0, NULL, '2026-08-17 03:56:28'),
(1358, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service LIGHT PMS GAS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 18, 0, NULL, '2026-08-17 03:56:28'),
(1361, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service LIGHT PMS GAS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 18, 0, NULL, '2026-08-17 03:59:12'),
(1362, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service LIGHT PMS GAS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 18, 0, NULL, '2026-08-17 03:59:12'),
(1364, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service LIGHT PMS GAS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 18, 0, NULL, '2026-08-17 03:59:12'),
(1367, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service LIGHT PMS DIESEL (Price: ₱0.00, Status: ACTIVE).', 'service', 43, 0, NULL, '2026-08-17 04:00:51'),
(1368, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service LIGHT PMS DIESEL (Price: ₱0.00, Status: ACTIVE).', 'service', 43, 0, NULL, '2026-08-17 04:00:51'),
(1370, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service LIGHT PMS DIESEL (Price: ₱0.00, Status: ACTIVE).', 'service', 43, 0, NULL, '2026-08-17 04:00:51'),
(1373, 4, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Aian P. Alderite (Role: SERVICE_ADVISER -> SERVICE_ADVISER; Status: ACTIVE -> ACTIVE).', 'staff', 8, 0, NULL, '2026-08-31 12:43:31'),
(1374, 16, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Aian P. Alderite (Role: SERVICE_ADVISER -> SERVICE_ADVISER; Status: ACTIVE -> ACTIVE).', 'staff', 8, 0, NULL, '2026-08-31 12:43:31'),
(1375, 5, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Aian P. Alderite (Role: SERVICE_ADVISER -> SERVICE_ADVISER; Status: ACTIVE -> ACTIVE).', 'staff', 8, 0, NULL, '2026-08-31 12:43:31'),
(1376, 7, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Aian P. Alderite (Role: SERVICE_ADVISER -> SERVICE_ADVISER; Status: ACTIVE -> ACTIVE).', 'staff', 8, 0, NULL, '2026-08-31 12:43:31'),
(1378, 4, NULL, 'staff_update', 'Staff Status Updated', 'Lovely Joyce Gambong updated status for staff Jan Carlo Padios (Status: ACTIVE -> INACTIVE).', 'staff', 14, 0, NULL, '2026-08-31 12:43:50'),
(1379, 16, NULL, 'staff_update', 'Staff Status Updated', 'Lovely Joyce Gambong updated status for staff Jan Carlo Padios (Status: ACTIVE -> INACTIVE).', 'staff', 14, 0, NULL, '2026-08-31 12:43:50'),
(1380, 5, NULL, 'staff_update', 'Staff Status Updated', 'Lovely Joyce Gambong updated status for staff Jan Carlo Padios (Status: ACTIVE -> INACTIVE).', 'staff', 14, 0, NULL, '2026-08-31 12:43:50'),
(1381, 7, NULL, 'staff_update', 'Staff Status Updated', 'Lovely Joyce Gambong updated status for staff Jan Carlo Padios (Status: ACTIVE -> INACTIVE).', 'staff', 14, 0, NULL, '2026-08-31 12:43:50'),
(1383, 4, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service BRAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 26, 0, NULL, '2026-08-31 23:36:34'),
(1384, 16, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service BRAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 26, 0, NULL, '2026-08-31 23:36:34'),
(1385, 5, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service BRAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 26, 0, NULL, '2026-08-31 23:36:34'),
(1386, 6, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service BRAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 26, 0, NULL, '2026-08-31 23:36:34'),
(1387, 7, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service BRAKE CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 26, 0, NULL, '2026-08-31 23:36:34'),
(1389, 4, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service EGR, INTAKE AND TURBO CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 21, 0, NULL, '2026-08-31 23:37:32'),
(1390, 16, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service EGR, INTAKE AND TURBO CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 21, 0, NULL, '2026-08-31 23:37:32'),
(1391, 5, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service EGR, INTAKE AND TURBO CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 21, 0, NULL, '2026-08-31 23:37:32'),
(1392, 6, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service EGR, INTAKE AND TURBO CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 21, 0, NULL, '2026-08-31 23:37:32'),
(1393, 7, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service EGR, INTAKE AND TURBO CLEANING (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 21, 0, NULL, '2026-08-31 23:37:32'),
(1395, 4, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 41, 0, NULL, '2026-08-31 23:39:12'),
(1396, 16, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 41, 0, NULL, '2026-08-31 23:39:12'),
(1397, 5, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 41, 0, NULL, '2026-08-31 23:39:12'),
(1398, 6, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 41, 0, NULL, '2026-08-31 23:39:12'),
(1399, 7, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 41, 0, NULL, '2026-08-31 23:39:12'),
(1401, 4, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service REFACE ROTOR DISC (BOTH SIDES) - SEDAN (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 40, 0, NULL, '2026-08-31 23:39:28'),
(1402, 16, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service REFACE ROTOR DISC (BOTH SIDES) - SEDAN (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 40, 0, NULL, '2026-08-31 23:39:28'),
(1403, 5, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service REFACE ROTOR DISC (BOTH SIDES) - SEDAN (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 40, 0, NULL, '2026-08-31 23:39:28'),
(1404, 6, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service REFACE ROTOR DISC (BOTH SIDES) - SEDAN (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 40, 0, NULL, '2026-08-31 23:39:28'),
(1405, 7, NULL, 'system', 'Service Updated', 'Aian P. Alderite updated service REFACE ROTOR DISC (BOTH SIDES) - SEDAN (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 40, 0, NULL, '2026-08-31 23:39:28'),
(1407, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO001.', 'job_order', 6, 0, NULL, '2026-08-31 23:49:27'),
(1408, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO001.', 'job_order', 6, 0, NULL, '2026-08-31 23:49:27'),
(1409, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO001.', 'job_order', 6, 0, NULL, '2026-08-31 23:49:27'),
(1410, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO001.', 'job_order', 6, 0, NULL, '2026-08-31 23:49:27'),
(1411, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO001.', 'job_order', 6, 0, NULL, '2026-08-31 23:49:27'),
(1412, 15, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO001.', 'job_order', 6, 0, NULL, '2026-08-31 23:49:27'),
(1413, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO001.', 'job_order', 6, 0, NULL, '2026-08-31 23:49:27'),
(1415, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO001 (Status: Cancelled).', 'job_order', 6, 0, NULL, '2026-08-31 23:52:54'),
(1416, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO001 (Status: Cancelled).', 'job_order', 6, 0, NULL, '2026-08-31 23:52:54'),
(1417, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO001 (Status: Cancelled).', 'job_order', 6, 0, NULL, '2026-08-31 23:52:54'),
(1418, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO001 (Status: Cancelled).', 'job_order', 6, 0, NULL, '2026-08-31 23:52:54'),
(1419, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO001 (Status: Cancelled).', 'job_order', 6, 0, NULL, '2026-08-31 23:52:54'),
(1420, 15, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO001 (Status: Cancelled).', 'job_order', 6, 0, NULL, '2026-08-31 23:52:54'),
(1421, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO001 (Status: Cancelled).', 'job_order', 6, 0, NULL, '2026-08-31 23:52:54'),
(1423, 4, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock ENGINE OIL 5W-30 (Quantity: +10).', 'inventory_transaction', 6, 0, NULL, '2026-09-01 01:20:08'),
(1424, 16, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock ENGINE OIL 5W-30 (Quantity: +10).', 'inventory_transaction', 6, 0, NULL, '2026-09-01 01:20:08'),
(1425, 5, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock ENGINE OIL 5W-30 (Quantity: +10).', 'inventory_transaction', 6, 0, NULL, '2026-09-01 01:20:08'),
(1426, 6, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock ENGINE OIL 5W-30 (Quantity: +10).', 'inventory_transaction', 6, 0, NULL, '2026-09-01 01:20:08'),
(1427, 7, NULL, 'system', 'Inventory Stock In', 'Lovely Joyce Gambong added stock ENGINE OIL 5W-30 (Quantity: +10).', 'inventory_transaction', 6, 0, NULL, '2026-09-01 01:20:08'),
(1429, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO002.', 'job_order', 7, 0, NULL, '2026-09-01 01:20:27'),
(1430, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO002.', 'job_order', 7, 0, NULL, '2026-09-01 01:20:27'),
(1431, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO002.', 'job_order', 7, 0, NULL, '2026-09-01 01:20:27'),
(1432, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO002.', 'job_order', 7, 0, NULL, '2026-09-01 01:20:27'),
(1433, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO002.', 'job_order', 7, 0, NULL, '2026-09-01 01:20:27'),
(1434, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO002.', 'job_order', 7, 0, NULL, '2026-09-01 01:20:27'),
(1436, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Ongoing).', 'job_order', 7, 0, NULL, '2026-09-01 01:23:08'),
(1437, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Ongoing).', 'job_order', 7, 0, NULL, '2026-09-01 01:23:08'),
(1438, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Ongoing).', 'job_order', 7, 0, NULL, '2026-09-01 01:23:08'),
(1439, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Ongoing).', 'job_order', 7, 0, NULL, '2026-09-01 01:23:08'),
(1440, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Ongoing).', 'job_order', 7, 0, NULL, '2026-09-01 01:23:08'),
(1441, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Ongoing).', 'job_order', 7, 0, NULL, '2026-09-01 01:23:08'),
(1442, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Ongoing).', 'job_order', 7, 0, NULL, '2026-09-01 01:23:08'),
(1444, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-09-01 01:27:46'),
(1445, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-09-01 01:27:46'),
(1446, 5, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-09-01 01:27:46'),
(1447, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-09-01 01:27:46'),
(1448, 8, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-09-01 01:27:46'),
(1450, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-09-01 01:28:22'),
(1451, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-09-01 01:28:22'),
(1452, 5, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-09-01 01:28:22'),
(1453, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-09-01 01:28:22'),
(1454, 8, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service REGULAR PMS (Status: ACTIVE -> ACTIVE; Price: ₱0.00).', 'service', 6, 0, NULL, '2026-09-01 01:28:22'),
(1456, 4, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 01:35:00'),
(1457, 16, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 01:35:00'),
(1458, 5, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 01:35:00'),
(1459, 7, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 01:35:00'),
(1461, 4, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 01:35:17'),
(1462, 16, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 01:35:17'),
(1463, 5, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 01:35:17'),
(1464, 7, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 01:35:17'),
(1466, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO003.', 'job_order', 8, 0, NULL, '2026-09-01 02:20:23'),
(1467, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO003.', 'job_order', 8, 0, NULL, '2026-09-01 02:20:23'),
(1468, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO003.', 'job_order', 8, 0, NULL, '2026-09-01 02:20:23'),
(1469, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO003.', 'job_order', 8, 0, NULL, '2026-09-01 02:20:23'),
(1470, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO003.', 'job_order', 8, 0, NULL, '2026-09-01 02:20:23'),
(1471, 9, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO003.', 'job_order', 8, 0, NULL, '2026-09-01 02:20:23'),
(1472, 10, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO003.', 'job_order', 8, 0, NULL, '2026-09-01 02:20:23'),
(1473, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO003.', 'job_order', 8, 0, NULL, '2026-09-01 02:20:23'),
(1475, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Ongoing).', 'job_order', 8, 0, NULL, '2026-09-01 02:22:59'),
(1476, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Ongoing).', 'job_order', 8, 0, NULL, '2026-09-01 02:22:59'),
(1477, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Ongoing).', 'job_order', 8, 0, NULL, '2026-09-01 02:22:59'),
(1478, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Ongoing).', 'job_order', 8, 0, NULL, '2026-09-01 02:22:59'),
(1479, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Ongoing).', 'job_order', 8, 0, NULL, '2026-09-01 02:22:59'),
(1480, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Ongoing).', 'job_order', 8, 0, NULL, '2026-09-01 02:22:59'),
(1481, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Ongoing).', 'job_order', 8, 0, NULL, '2026-09-01 02:22:59'),
(1482, 11, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Ongoing).', 'job_order', 8, 0, NULL, '2026-09-01 02:22:59'),
(1483, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Ongoing).', 'job_order', 8, 0, NULL, '2026-09-01 02:22:59'),
(1485, 4, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIR FILTER 039 (Price: ₱0.00, Status: ACTIVE).', 'service', 44, 0, NULL, '2026-09-01 02:28:30'),
(1486, 16, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIR FILTER 039 (Price: ₱0.00, Status: ACTIVE).', 'service', 44, 0, NULL, '2026-09-01 02:28:30'),
(1487, 5, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIR FILTER 039 (Price: ₱0.00, Status: ACTIVE).', 'service', 44, 0, NULL, '2026-09-01 02:28:30'),
(1488, 7, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIR FILTER 039 (Price: ₱0.00, Status: ACTIVE).', 'service', 44, 0, NULL, '2026-09-01 02:28:30');
INSERT INTO `notifications` (`id`, `user_id`, `staff_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `read_at`, `created_at`) VALUES
(1489, 8, NULL, 'system', 'Service Added', 'Lovely Joyce Gambong added service AIR FILTER 039 (Price: ₱0.00, Status: ACTIVE).', 'service', 44, 0, NULL, '2026-09-01 02:28:30'),
(1491, 4, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service (Status: ACTIVE -> INACTIVE; Price: ₱0.00).', 'service', 44, 0, NULL, '2026-09-01 02:31:46'),
(1492, 16, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service (Status: ACTIVE -> INACTIVE; Price: ₱0.00).', 'service', 44, 0, NULL, '2026-09-01 02:31:46'),
(1493, 5, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service (Status: ACTIVE -> INACTIVE; Price: ₱0.00).', 'service', 44, 0, NULL, '2026-09-01 02:31:46'),
(1494, 7, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service (Status: ACTIVE -> INACTIVE; Price: ₱0.00).', 'service', 44, 0, NULL, '2026-09-01 02:31:46'),
(1495, 8, NULL, 'system', 'Service Updated', 'Lovely Joyce Gambong updated service (Status: ACTIVE -> INACTIVE; Price: ₱0.00).', 'service', 44, 0, NULL, '2026-09-01 02:31:46'),
(1497, 4, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 02:42:55'),
(1498, 16, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 02:42:55'),
(1499, 5, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 02:42:55'),
(1500, 7, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 02:42:55'),
(1502, 4, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 02:44:24'),
(1503, 16, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 02:44:24'),
(1504, 5, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 02:44:24'),
(1505, 7, NULL, 'system', 'Print Template Updated', 'Lovely Joyce Gambong updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-01 02:44:24'),
(1507, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Completed).', 'job_order', 7, 0, NULL, '2026-09-01 05:40:32'),
(1508, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Completed).', 'job_order', 7, 0, NULL, '2026-09-01 05:40:32'),
(1509, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Completed).', 'job_order', 7, 0, NULL, '2026-09-01 05:40:32'),
(1510, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Completed).', 'job_order', 7, 0, NULL, '2026-09-01 05:40:32'),
(1511, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Completed).', 'job_order', 7, 0, NULL, '2026-09-01 05:40:32'),
(1512, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Completed).', 'job_order', 7, 0, NULL, '2026-09-01 05:40:32'),
(1513, 11, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Completed).', 'job_order', 7, 0, NULL, '2026-09-01 05:40:32'),
(1514, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO002 (Status: Completed).', 'job_order', 7, 0, NULL, '2026-09-01 05:40:32'),
(1516, 4, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Pam-pam (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 9, 0, NULL, '2026-09-01 05:46:44'),
(1517, 16, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Pam-pam (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 9, 0, NULL, '2026-09-01 05:46:44'),
(1518, 5, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Pam-pam (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 9, 0, NULL, '2026-09-01 05:46:44'),
(1519, 7, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Pam-pam (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 9, 0, NULL, '2026-09-01 05:46:44'),
(1521, 4, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Kineth (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 10, 0, NULL, '2026-09-01 05:46:56'),
(1522, 16, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Kineth (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 10, 0, NULL, '2026-09-01 05:46:56'),
(1523, 5, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Kineth (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 10, 0, NULL, '2026-09-01 05:46:56'),
(1524, 7, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Kineth (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 10, 0, NULL, '2026-09-01 05:46:56'),
(1526, 4, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Nexander G. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 9, 0, NULL, '2026-09-01 05:47:19'),
(1527, 16, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Nexander G. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 9, 0, NULL, '2026-09-01 05:47:19'),
(1528, 5, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Nexander G. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 9, 0, NULL, '2026-09-01 05:47:19'),
(1529, 7, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Nexander G. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 9, 0, NULL, '2026-09-01 05:47:19'),
(1531, 4, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Kineth P. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 10, 0, NULL, '2026-09-01 05:47:29'),
(1532, 16, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Kineth P. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 10, 0, NULL, '2026-09-01 05:47:29'),
(1533, 5, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Kineth P. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 10, 0, NULL, '2026-09-01 05:47:29'),
(1534, 7, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Kineth P. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 10, 0, NULL, '2026-09-01 05:47:29'),
(1536, 4, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Jerald C. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 11, 0, NULL, '2026-09-01 05:47:43'),
(1537, 16, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Jerald C. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 11, 0, NULL, '2026-09-01 05:47:43'),
(1538, 5, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Jerald C. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 11, 0, NULL, '2026-09-01 05:47:43'),
(1539, 7, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Jerald C. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 11, 0, NULL, '2026-09-01 05:47:43'),
(1541, 4, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Legario M. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 13, 0, NULL, '2026-09-01 05:47:55'),
(1542, 16, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Legario M. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 13, 0, NULL, '2026-09-01 05:47:55'),
(1543, 5, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Legario M. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 13, 0, NULL, '2026-09-01 05:47:55'),
(1544, 7, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Legario M. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 13, 0, NULL, '2026-09-01 05:47:55'),
(1546, 4, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff John Paul V. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 12, 0, NULL, '2026-09-01 05:48:07'),
(1547, 16, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff John Paul V. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 12, 0, NULL, '2026-09-01 05:48:07'),
(1548, 5, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff John Paul V. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 12, 0, NULL, '2026-09-01 05:48:07'),
(1549, 7, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff John Paul V. (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 12, 0, NULL, '2026-09-01 05:48:07'),
(1551, 4, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Artemio B. Jr (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 15, 0, NULL, '2026-09-01 05:48:22'),
(1552, 16, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Artemio B. Jr (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 15, 0, NULL, '2026-09-01 05:48:22'),
(1553, 5, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Artemio B. Jr (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 15, 0, NULL, '2026-09-01 05:48:22'),
(1554, 7, NULL, 'staff_update', 'Staff Updated', 'Lovely Joyce Gambong updated staff Artemio B. Jr (Role: TECHNICIAN -> TECHNICIAN; Status: ACTIVE -> ACTIVE).', 'staff', 15, 0, NULL, '2026-09-01 05:48:22'),
(1556, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 06:43:28'),
(1557, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 06:43:28'),
(1558, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 06:43:28'),
(1559, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 06:43:28'),
(1560, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 06:43:28'),
(1561, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 06:43:28'),
(1562, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 06:43:28'),
(1563, 11, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 06:43:28'),
(1564, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 06:43:28'),
(1566, 4, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO003 (Amount: ₱12,500.00).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:13'),
(1567, 16, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO003 (Amount: ₱12,500.00).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:13'),
(1568, 5, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO003 (Amount: ₱12,500.00).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:13'),
(1569, 6, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO003 (Amount: ₱12,500.00).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:13'),
(1570, 7, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO003 (Amount: ₱12,500.00).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:13'),
(1571, 8, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO003 (Amount: ₱12,500.00).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:13'),
(1573, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:18'),
(1574, 5, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:18'),
(1575, 6, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:18'),
(1576, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:18'),
(1577, 8, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:18'),
(1578, 9, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:18'),
(1579, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:18'),
(1580, 11, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:18'),
(1581, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-01 06:47:18'),
(1583, 4, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 07:01:34'),
(1584, 5, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 07:01:34'),
(1585, 6, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 07:01:34'),
(1586, 7, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 07:01:34'),
(1587, 8, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 07:01:34'),
(1588, 9, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 07:01:34'),
(1589, 10, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 07:01:34'),
(1590, 11, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 07:01:34'),
(1591, 16, NULL, 'job_status', 'Job Order Status Updated', 'Lovely Joyce Gambong updated job order #JO003 (Status: Completed).', 'job_order', 8, 0, NULL, '2026-09-01 07:01:34'),
(1593, 4, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO002 (Amount: ₱5,800.00).', 'job_order', 7, 0, NULL, '2026-09-01 07:44:55'),
(1594, 16, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO002 (Amount: ₱5,800.00).', 'job_order', 7, 0, NULL, '2026-09-01 07:44:55'),
(1595, 5, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO002 (Amount: ₱5,800.00).', 'job_order', 7, 0, NULL, '2026-09-01 07:44:55'),
(1596, 6, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO002 (Amount: ₱5,800.00).', 'job_order', 7, 0, NULL, '2026-09-01 07:44:55'),
(1597, 7, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO002 (Amount: ₱5,800.00).', 'job_order', 7, 0, NULL, '2026-09-01 07:44:55'),
(1598, 8, NULL, 'payment', 'Job Order Paid', 'Lovely Joyce Gambong marked as paid job order #JO002 (Amount: ₱5,800.00).', 'job_order', 7, 0, NULL, '2026-09-01 07:44:55'),
(1600, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO004.', 'job_order', 9, 0, NULL, '2026-09-01 08:07:46'),
(1601, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO004.', 'job_order', 9, 0, NULL, '2026-09-01 08:07:46'),
(1602, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO004.', 'job_order', 9, 0, NULL, '2026-09-01 08:07:46'),
(1603, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO004.', 'job_order', 9, 0, NULL, '2026-09-01 08:07:46'),
(1604, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO004.', 'job_order', 9, 0, NULL, '2026-09-01 08:07:46'),
(1605, 11, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO004.', 'job_order', 9, 0, NULL, '2026-09-01 08:07:46'),
(1606, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO004.', 'job_order', 9, 0, NULL, '2026-09-01 08:07:46'),
(1608, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Ongoing).', 'job_order', 9, 0, NULL, '2026-09-01 08:08:39'),
(1609, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Ongoing).', 'job_order', 9, 0, NULL, '2026-09-01 08:08:39'),
(1610, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Ongoing).', 'job_order', 9, 0, NULL, '2026-09-01 08:08:39'),
(1611, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Ongoing).', 'job_order', 9, 0, NULL, '2026-09-01 08:08:39'),
(1612, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Ongoing).', 'job_order', 9, 0, NULL, '2026-09-01 08:08:39'),
(1613, 11, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Ongoing).', 'job_order', 9, 0, NULL, '2026-09-01 08:08:39'),
(1614, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Ongoing).', 'job_order', 9, 0, NULL, '2026-09-01 08:08:39'),
(1616, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱450.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 08:33:37'),
(1617, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱450.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 08:33:37'),
(1618, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱450.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 08:33:37'),
(1619, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱450.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 08:33:37'),
(1620, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱450.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 08:33:37'),
(1622, 4, NULL, 'system', 'Product Added', 'LOVELY JOYCE added product BEARING (Code: PRD36, Status: ACTIVE).', 'product', 37, 0, NULL, '2026-09-01 10:01:02'),
(1623, 16, NULL, 'system', 'Product Added', 'LOVELY JOYCE added product BEARING (Code: PRD36, Status: ACTIVE).', 'product', 37, 0, NULL, '2026-09-01 10:01:02'),
(1624, 5, NULL, 'system', 'Product Added', 'LOVELY JOYCE added product BEARING (Code: PRD36, Status: ACTIVE).', 'product', 37, 0, NULL, '2026-09-01 10:01:02'),
(1625, 7, NULL, 'system', 'Product Added', 'LOVELY JOYCE added product BEARING (Code: PRD36, Status: ACTIVE).', 'product', 37, 0, NULL, '2026-09-01 10:01:02'),
(1627, 4, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product BEARING (Status: ACTIVE -> INACTIVE).', 'product', 37, 0, NULL, '2026-09-01 10:02:58'),
(1628, 16, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product BEARING (Status: ACTIVE -> INACTIVE).', 'product', 37, 0, NULL, '2026-09-01 10:02:58'),
(1629, 5, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product BEARING (Status: ACTIVE -> INACTIVE).', 'product', 37, 0, NULL, '2026-09-01 10:02:58'),
(1630, 7, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product BEARING (Status: ACTIVE -> INACTIVE).', 'product', 37, 0, NULL, '2026-09-01 10:02:58'),
(1632, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,960.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 10:03:38'),
(1633, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,960.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 10:03:38'),
(1634, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,960.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 10:03:38'),
(1635, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,960.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 10:03:38'),
(1636, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,960.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 10:03:38'),
(1638, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,891.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 10:08:02'),
(1639, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,891.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 10:08:02'),
(1640, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,891.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 10:08:02'),
(1641, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,891.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 10:08:02'),
(1642, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,891.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 10:08:02'),
(1644, 4, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:18:12'),
(1645, 16, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:18:12'),
(1646, 5, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:18:12'),
(1647, 6, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:18:12'),
(1648, 7, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:18:12'),
(1650, 4, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:20:58'),
(1651, 16, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:20:58'),
(1652, 5, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:20:58'),
(1653, 6, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:20:58'),
(1654, 7, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:20:58'),
(1656, 4, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:25:51'),
(1657, 16, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:25:51'),
(1658, 5, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:25:51'),
(1659, 6, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:25:51'),
(1660, 7, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:25:51'),
(1662, 4, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:26:42'),
(1663, 16, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:26:42'),
(1664, 5, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:26:42'),
(1665, 6, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:26:42'),
(1666, 7, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:26:42'),
(1668, 4, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:45:14'),
(1669, 16, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:45:14'),
(1670, 5, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:45:14'),
(1671, 6, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:45:14'),
(1672, 7, NULL, 'system', 'Expense Added', 'Dj Guingue Cortez added an expense (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:45:14'),
(1674, 4, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:45:53'),
(1675, 16, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:45:53'),
(1676, 5, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:45:53'),
(1677, 6, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:45:53'),
(1678, 7, NULL, 'system', 'Expense Deleted', 'Dj Guingue Cortez deleted expense entry (Amount: ₱100.00, Date: Sep 02, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-01 17:45:53'),
(1680, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO005.', 'job_order', 10, 0, NULL, '2026-09-02 00:50:35'),
(1681, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO005.', 'job_order', 10, 0, NULL, '2026-09-02 00:50:35'),
(1682, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO005.', 'job_order', 10, 0, NULL, '2026-09-02 00:50:35'),
(1683, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO005.', 'job_order', 10, 0, NULL, '2026-09-02 00:50:35'),
(1684, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO005.', 'job_order', 10, 0, NULL, '2026-09-02 00:50:35'),
(1685, 9, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO005.', 'job_order', 10, 0, NULL, '2026-09-02 00:50:35'),
(1686, 11, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO005.', 'job_order', 10, 0, NULL, '2026-09-02 00:50:35'),
(1687, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO005.', 'job_order', 10, 0, NULL, '2026-09-02 00:50:35'),
(1689, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Ongoing).', 'job_order', 10, 0, NULL, '2026-09-02 00:50:43'),
(1690, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Ongoing).', 'job_order', 10, 0, NULL, '2026-09-02 00:50:43'),
(1691, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Ongoing).', 'job_order', 10, 0, NULL, '2026-09-02 00:50:43'),
(1692, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Ongoing).', 'job_order', 10, 0, NULL, '2026-09-02 00:50:43'),
(1693, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Ongoing).', 'job_order', 10, 0, NULL, '2026-09-02 00:50:43'),
(1694, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Ongoing).', 'job_order', 10, 0, NULL, '2026-09-02 00:50:43'),
(1695, 11, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Ongoing).', 'job_order', 10, 0, NULL, '2026-09-02 00:50:43'),
(1696, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Ongoing).', 'job_order', 10, 0, NULL, '2026-09-02 00:50:43'),
(1698, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱450.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 03:57:23'),
(1699, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱450.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 03:57:23'),
(1700, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱450.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 03:57:23'),
(1701, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱450.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 03:57:23'),
(1702, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱450.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 03:57:23'),
(1704, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,788.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 03:57:54'),
(1705, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,788.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 03:57:54'),
(1706, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,788.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 03:57:54'),
(1707, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,788.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 03:57:54'),
(1708, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,788.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 03:57:54'),
(1710, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO006.', 'job_order', 11, 0, NULL, '2026-09-02 03:59:20'),
(1711, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO006.', 'job_order', 11, 0, NULL, '2026-09-02 03:59:20'),
(1712, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO006.', 'job_order', 11, 0, NULL, '2026-09-02 03:59:20'),
(1713, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO006.', 'job_order', 11, 0, NULL, '2026-09-02 03:59:20'),
(1714, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO006.', 'job_order', 11, 0, NULL, '2026-09-02 03:59:20'),
(1715, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO006.', 'job_order', 11, 0, NULL, '2026-09-02 03:59:20'),
(1717, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,960.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 04:01:03'),
(1718, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,960.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 04:01:03'),
(1719, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,960.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 04:01:03'),
(1720, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,960.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 04:01:03'),
(1721, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,960.00, Date: Sep 01, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-02 04:01:03'),
(1723, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Ongoing).', 'job_order', 11, 0, NULL, '2026-09-02 06:46:25'),
(1724, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Ongoing).', 'job_order', 11, 0, NULL, '2026-09-02 06:46:25'),
(1725, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Ongoing).', 'job_order', 11, 0, NULL, '2026-09-02 06:46:25'),
(1726, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Ongoing).', 'job_order', 11, 0, NULL, '2026-09-02 06:46:25'),
(1727, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Ongoing).', 'job_order', 11, 0, NULL, '2026-09-02 06:46:25'),
(1728, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Ongoing).', 'job_order', 11, 0, NULL, '2026-09-02 06:46:25'),
(1729, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Ongoing).', 'job_order', 11, 0, NULL, '2026-09-02 06:46:25'),
(1731, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Completed).', 'job_order', 11, 0, NULL, '2026-09-03 08:21:31'),
(1732, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Completed).', 'job_order', 11, 0, NULL, '2026-09-03 08:21:31'),
(1733, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Completed).', 'job_order', 11, 0, NULL, '2026-09-03 08:21:31'),
(1734, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Completed).', 'job_order', 11, 0, NULL, '2026-09-03 08:21:31'),
(1735, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Completed).', 'job_order', 11, 0, NULL, '2026-09-03 08:21:31'),
(1736, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Completed).', 'job_order', 11, 0, NULL, '2026-09-03 08:21:31'),
(1737, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO006 (Status: Completed).', 'job_order', 11, 0, NULL, '2026-09-03 08:21:31'),
(1739, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Completed).', 'job_order', 9, 0, NULL, '2026-09-03 08:28:35'),
(1740, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Completed).', 'job_order', 9, 0, NULL, '2026-09-03 08:28:35'),
(1741, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Completed).', 'job_order', 9, 0, NULL, '2026-09-03 08:28:35'),
(1742, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Completed).', 'job_order', 9, 0, NULL, '2026-09-03 08:28:35'),
(1743, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Completed).', 'job_order', 9, 0, NULL, '2026-09-03 08:28:35'),
(1744, 11, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Completed).', 'job_order', 9, 0, NULL, '2026-09-03 08:28:35'),
(1745, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO004 (Status: Completed).', 'job_order', 9, 0, NULL, '2026-09-03 08:28:35'),
(1747, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO006 (Amount: ₱5,700.00).', 'job_order', 11, 0, NULL, '2026-09-03 09:56:57'),
(1748, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO006 (Amount: ₱5,700.00).', 'job_order', 11, 0, NULL, '2026-09-03 09:56:57'),
(1749, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO006 (Amount: ₱5,700.00).', 'job_order', 11, 0, NULL, '2026-09-03 09:56:57'),
(1750, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO006 (Amount: ₱5,700.00).', 'job_order', 11, 0, NULL, '2026-09-03 09:56:57'),
(1751, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO006 (Amount: ₱5,700.00).', 'job_order', 11, 0, NULL, '2026-09-03 09:56:57'),
(1752, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO006 (Amount: ₱5,700.00).', 'job_order', 11, 0, NULL, '2026-09-03 09:56:57'),
(1754, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO004 (Amount: ₱4,000.00).', 'job_order', 9, 0, NULL, '2026-09-03 09:57:12'),
(1755, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO004 (Amount: ₱4,000.00).', 'job_order', 9, 0, NULL, '2026-09-03 09:57:12'),
(1756, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO004 (Amount: ₱4,000.00).', 'job_order', 9, 0, NULL, '2026-09-03 09:57:12'),
(1757, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO004 (Amount: ₱4,000.00).', 'job_order', 9, 0, NULL, '2026-09-03 09:57:12'),
(1758, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO004 (Amount: ₱4,000.00).', 'job_order', 9, 0, NULL, '2026-09-03 09:57:12'),
(1759, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO004 (Amount: ₱4,000.00).', 'job_order', 9, 0, NULL, '2026-09-03 09:57:12'),
(1761, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-03 09:57:37'),
(1762, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-03 09:57:37'),
(1763, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-03 09:57:37'),
(1764, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-03 09:57:37'),
(1765, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-03 09:57:37'),
(1767, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,320.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-03 09:57:51'),
(1768, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,320.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-03 09:57:51'),
(1769, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,320.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-03 09:57:51'),
(1770, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,320.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-03 09:57:51'),
(1771, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,320.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-03 09:57:51'),
(1773, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Completed).', 'job_order', 10, 0, NULL, '2026-09-04 00:23:46'),
(1774, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Completed).', 'job_order', 10, 0, NULL, '2026-09-04 00:23:46'),
(1775, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Completed).', 'job_order', 10, 0, NULL, '2026-09-04 00:23:46'),
(1776, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Completed).', 'job_order', 10, 0, NULL, '2026-09-04 00:23:46'),
(1777, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Completed).', 'job_order', 10, 0, NULL, '2026-09-04 00:23:46'),
(1778, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Completed).', 'job_order', 10, 0, NULL, '2026-09-04 00:23:46'),
(1779, 11, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Completed).', 'job_order', 10, 0, NULL, '2026-09-04 00:23:46'),
(1780, 13, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Completed).', 'job_order', 10, 0, NULL, '2026-09-04 00:23:46'),
(1781, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO005 (Status: Completed).', 'job_order', 10, 0, NULL, '2026-09-04 00:23:46'),
(1783, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO005 (Amount: ₱11,800.00).', 'job_order', 10, 0, NULL, '2026-09-04 00:50:04'),
(1784, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO005 (Amount: ₱11,800.00).', 'job_order', 10, 0, NULL, '2026-09-04 00:50:04'),
(1785, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO005 (Amount: ₱11,800.00).', 'job_order', 10, 0, NULL, '2026-09-04 00:50:04'),
(1786, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO005 (Amount: ₱11,800.00).', 'job_order', 10, 0, NULL, '2026-09-04 00:50:04'),
(1787, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO005 (Amount: ₱11,800.00).', 'job_order', 10, 0, NULL, '2026-09-04 00:50:04'),
(1788, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO005 (Amount: ₱11,800.00).', 'job_order', 10, 0, NULL, '2026-09-04 00:50:04'),
(1790, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO007.', 'job_order', 12, 0, NULL, '2026-09-04 01:52:57'),
(1791, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO007.', 'job_order', 12, 0, NULL, '2026-09-04 01:52:57'),
(1792, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO007.', 'job_order', 12, 0, NULL, '2026-09-04 01:52:57'),
(1793, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO007.', 'job_order', 12, 0, NULL, '2026-09-04 01:52:57'),
(1794, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO007.', 'job_order', 12, 0, NULL, '2026-09-04 01:52:57'),
(1795, 9, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO007.', 'job_order', 12, 0, NULL, '2026-09-04 01:52:57'),
(1796, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO007.', 'job_order', 12, 0, NULL, '2026-09-04 01:52:57'),
(1798, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-04 01:53:02'),
(1799, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-04 01:53:02'),
(1800, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-04 01:53:02'),
(1801, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-04 01:53:02'),
(1802, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-04 01:53:02'),
(1803, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-04 01:53:02'),
(1804, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-04 01:53:02'),
(1806, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO008.', 'job_order', 13, 0, NULL, '2026-09-04 03:59:14'),
(1807, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO008.', 'job_order', 13, 0, NULL, '2026-09-04 03:59:14'),
(1808, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO008.', 'job_order', 13, 0, NULL, '2026-09-04 03:59:14'),
(1809, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO008.', 'job_order', 13, 0, NULL, '2026-09-04 03:59:14'),
(1810, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO008.', 'job_order', 13, 0, NULL, '2026-09-04 03:59:14'),
(1811, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO008.', 'job_order', 13, 0, NULL, '2026-09-04 03:59:14'),
(1813, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:36:53'),
(1814, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:36:53'),
(1815, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:36:53'),
(1816, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:36:53'),
(1817, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:36:53'),
(1819, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱10.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:04'),
(1820, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱10.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:04'),
(1821, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱10.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:04'),
(1822, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱10.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:04'),
(1823, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱10.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:04'),
(1825, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱600.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:19'),
(1826, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱600.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:19'),
(1827, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱600.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:19'),
(1828, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱600.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:19');
INSERT INTO `notifications` (`id`, `user_id`, `staff_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `read_at`, `created_at`) VALUES
(1829, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱600.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:19'),
(1831, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱86.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:36'),
(1832, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱86.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:36'),
(1833, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱86.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:36'),
(1834, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱86.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:36'),
(1835, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱86.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:37:36'),
(1837, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,733.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:40:39'),
(1838, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,733.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:40:39'),
(1839, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,733.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:40:39'),
(1840, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,733.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:40:39'),
(1841, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,733.00, Date: Sep 03, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 05:40:39'),
(1843, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Ongoing).', 'job_order', 13, 0, NULL, '2026-09-04 07:36:50'),
(1844, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Ongoing).', 'job_order', 13, 0, NULL, '2026-09-04 07:36:50'),
(1845, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Ongoing).', 'job_order', 13, 0, NULL, '2026-09-04 07:36:50'),
(1846, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Ongoing).', 'job_order', 13, 0, NULL, '2026-09-04 07:36:50'),
(1847, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Ongoing).', 'job_order', 13, 0, NULL, '2026-09-04 07:36:50'),
(1848, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Ongoing).', 'job_order', 13, 0, NULL, '2026-09-04 07:36:50'),
(1850, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,100.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 09:32:10'),
(1851, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,100.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 09:32:10'),
(1852, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,100.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 09:32:10'),
(1853, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,100.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 09:32:10'),
(1854, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,100.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-04 09:32:10'),
(1856, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱6,395.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 01:33:49'),
(1857, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱6,395.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 01:33:49'),
(1858, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱6,395.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 01:33:49'),
(1859, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱6,395.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 01:33:49'),
(1860, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱6,395.00, Date: Sep 04, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 01:33:49'),
(1862, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO009.', 'job_order', 14, 0, NULL, '2026-09-05 01:37:01'),
(1863, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO009.', 'job_order', 14, 0, NULL, '2026-09-05 01:37:01'),
(1864, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO009.', 'job_order', 14, 0, NULL, '2026-09-05 01:37:01'),
(1865, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO009.', 'job_order', 14, 0, NULL, '2026-09-05 01:37:01'),
(1866, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO009.', 'job_order', 14, 0, NULL, '2026-09-05 01:37:01'),
(1867, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO009.', 'job_order', 14, 0, NULL, '2026-09-05 01:37:01'),
(1869, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Ongoing).', 'job_order', 14, 0, NULL, '2026-09-05 01:37:05'),
(1870, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Ongoing).', 'job_order', 14, 0, NULL, '2026-09-05 01:37:05'),
(1871, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Ongoing).', 'job_order', 14, 0, NULL, '2026-09-05 01:37:05'),
(1872, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Ongoing).', 'job_order', 14, 0, NULL, '2026-09-05 01:37:05'),
(1873, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Ongoing).', 'job_order', 14, 0, NULL, '2026-09-05 01:37:05'),
(1874, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Ongoing).', 'job_order', 14, 0, NULL, '2026-09-05 01:37:05'),
(1876, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Completed).', 'job_order', 14, 0, NULL, '2026-09-05 04:29:04'),
(1877, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Completed).', 'job_order', 14, 0, NULL, '2026-09-05 04:29:04'),
(1878, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Completed).', 'job_order', 14, 0, NULL, '2026-09-05 04:29:04'),
(1879, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Completed).', 'job_order', 14, 0, NULL, '2026-09-05 04:29:04'),
(1880, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Completed).', 'job_order', 14, 0, NULL, '2026-09-05 04:29:04'),
(1881, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Completed).', 'job_order', 14, 0, NULL, '2026-09-05 04:29:04'),
(1882, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO009 (Status: Completed).', 'job_order', 14, 0, NULL, '2026-09-05 04:29:04'),
(1884, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO009 (Amount: ₱8,000.00).', 'job_order', 14, 0, NULL, '2026-09-05 06:33:22'),
(1885, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO009 (Amount: ₱8,000.00).', 'job_order', 14, 0, NULL, '2026-09-05 06:33:22'),
(1886, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO009 (Amount: ₱8,000.00).', 'job_order', 14, 0, NULL, '2026-09-05 06:33:22'),
(1887, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO009 (Amount: ₱8,000.00).', 'job_order', 14, 0, NULL, '2026-09-05 06:33:22'),
(1888, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO009 (Amount: ₱8,000.00).', 'job_order', 14, 0, NULL, '2026-09-05 06:33:22'),
(1889, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO009 (Amount: ₱8,000.00).', 'job_order', 14, 0, NULL, '2026-09-05 06:33:22'),
(1891, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,499.86, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 06:38:26'),
(1892, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,499.86, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 06:38:26'),
(1893, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,499.86, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 06:38:26'),
(1894, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,499.86, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 06:38:26'),
(1895, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,499.86, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 06:38:26'),
(1897, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO010.', 'job_order', 15, 0, NULL, '2026-09-05 06:42:24'),
(1898, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO010.', 'job_order', 15, 0, NULL, '2026-09-05 06:42:24'),
(1899, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO010.', 'job_order', 15, 0, NULL, '2026-09-05 06:42:24'),
(1900, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO010.', 'job_order', 15, 0, NULL, '2026-09-05 06:42:24'),
(1901, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO010.', 'job_order', 15, 0, NULL, '2026-09-05 06:42:24'),
(1902, 12, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO010.', 'job_order', 15, 0, NULL, '2026-09-05 06:42:24'),
(1903, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO010.', 'job_order', 15, 0, NULL, '2026-09-05 06:42:24'),
(1905, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Ongoing).', 'job_order', 15, 0, NULL, '2026-09-05 06:42:27'),
(1906, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Ongoing).', 'job_order', 15, 0, NULL, '2026-09-05 06:42:27'),
(1907, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Ongoing).', 'job_order', 15, 0, NULL, '2026-09-05 06:42:27'),
(1908, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Ongoing).', 'job_order', 15, 0, NULL, '2026-09-05 06:42:27'),
(1909, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Ongoing).', 'job_order', 15, 0, NULL, '2026-09-05 06:42:27'),
(1910, 12, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Ongoing).', 'job_order', 15, 0, NULL, '2026-09-05 06:42:27'),
(1911, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Ongoing).', 'job_order', 15, 0, NULL, '2026-09-05 06:42:27'),
(1913, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Completed).', 'job_order', 15, 0, NULL, '2026-09-05 07:53:26'),
(1914, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Completed).', 'job_order', 15, 0, NULL, '2026-09-05 07:53:26'),
(1915, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Completed).', 'job_order', 15, 0, NULL, '2026-09-05 07:53:26'),
(1916, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Completed).', 'job_order', 15, 0, NULL, '2026-09-05 07:53:26'),
(1917, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Completed).', 'job_order', 15, 0, NULL, '2026-09-05 07:53:26'),
(1918, 12, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Completed).', 'job_order', 15, 0, NULL, '2026-09-05 07:53:26'),
(1919, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO010 (Status: Completed).', 'job_order', 15, 0, NULL, '2026-09-05 07:53:26'),
(1921, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO008 (Status: Completed).', 'job_order', 13, 0, NULL, '2026-09-05 09:13:11'),
(1922, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO008 (Status: Completed).', 'job_order', 13, 0, NULL, '2026-09-05 09:13:11'),
(1923, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO008 (Status: Completed).', 'job_order', 13, 0, NULL, '2026-09-05 09:13:11'),
(1924, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO008 (Status: Completed).', 'job_order', 13, 0, NULL, '2026-09-05 09:13:11'),
(1925, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO008 (Status: Completed).', 'job_order', 13, 0, NULL, '2026-09-05 09:13:11'),
(1926, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO008 (Status: Completed).', 'job_order', 13, 0, NULL, '2026-09-05 09:13:11'),
(1927, 13, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO008 (Status: Completed).', 'job_order', 13, 0, NULL, '2026-09-05 09:13:11'),
(1928, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO008 (Status: Completed).', 'job_order', 13, 0, NULL, '2026-09-05 09:13:11'),
(1930, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:36'),
(1931, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:36'),
(1932, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:36'),
(1933, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:36'),
(1934, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:36'),
(1935, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:36'),
(1936, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:36'),
(1938, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:51'),
(1939, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:51'),
(1940, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:51'),
(1941, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:51'),
(1942, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:51'),
(1943, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:51'),
(1944, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Ongoing).', 'job_order', 12, 0, NULL, '2026-09-05 09:47:51'),
(1946, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO008 (Amount: ₱36,720.00).', 'job_order', 13, 0, NULL, '2026-09-05 09:48:33'),
(1947, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO008 (Amount: ₱36,720.00).', 'job_order', 13, 0, NULL, '2026-09-05 09:48:33'),
(1948, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO008 (Amount: ₱36,720.00).', 'job_order', 13, 0, NULL, '2026-09-05 09:48:33'),
(1949, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO008 (Amount: ₱36,720.00).', 'job_order', 13, 0, NULL, '2026-09-05 09:48:33'),
(1950, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO008 (Amount: ₱36,720.00).', 'job_order', 13, 0, NULL, '2026-09-05 09:48:33'),
(1951, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO008 (Amount: ₱36,720.00).', 'job_order', 13, 0, NULL, '2026-09-05 09:48:33'),
(1953, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱18,070.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 09:49:45'),
(1954, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱18,070.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 09:49:45'),
(1955, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱18,070.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 09:49:45'),
(1956, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱18,070.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 09:49:45'),
(1957, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱18,070.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 09:49:45'),
(1959, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱100.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 09:51:36'),
(1960, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱100.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 09:51:36'),
(1961, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱100.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 09:51:36'),
(1962, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱100.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 09:51:36'),
(1963, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱100.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 09:51:36'),
(1965, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,761.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 13:30:00'),
(1966, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,761.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 13:30:00'),
(1967, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,761.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 13:30:00'),
(1968, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,761.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 13:30:00'),
(1969, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,761.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 13:30:00'),
(1970, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱2,761.00, Date: Sep 05, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-05 13:30:00'),
(1971, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO010 (Amount: ₱7,950.00).', 'job_order', 15, 0, NULL, '2026-09-07 01:47:30'),
(1972, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO010 (Amount: ₱7,950.00).', 'job_order', 15, 0, NULL, '2026-09-07 01:47:30'),
(1973, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO010 (Amount: ₱7,950.00).', 'job_order', 15, 0, NULL, '2026-09-07 01:47:30'),
(1974, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO010 (Amount: ₱7,950.00).', 'job_order', 15, 0, NULL, '2026-09-07 01:47:30'),
(1975, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO010 (Amount: ₱7,950.00).', 'job_order', 15, 0, NULL, '2026-09-07 01:47:30'),
(1976, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO010 (Amount: ₱7,950.00).', 'job_order', 15, 0, NULL, '2026-09-07 01:47:30'),
(1977, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO010 (Amount: ₱7,950.00).', 'job_order', 15, 0, NULL, '2026-09-07 01:47:30'),
(1978, 4, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-40 (Quantity: -4).', 'inventory_transaction', 13, 0, NULL, '2026-09-07 01:48:40'),
(1979, 16, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-40 (Quantity: -4).', 'inventory_transaction', 13, 0, NULL, '2026-09-07 01:48:40'),
(1980, 5, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-40 (Quantity: -4).', 'inventory_transaction', 13, 0, NULL, '2026-09-07 01:48:40'),
(1981, 6, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-40 (Quantity: -4).', 'inventory_transaction', 13, 0, NULL, '2026-09-07 01:48:40'),
(1982, 7, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-40 (Quantity: -4).', 'inventory_transaction', 13, 0, NULL, '2026-09-07 01:48:40'),
(1983, 2, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-40 (Quantity: -4).', 'inventory_transaction', 13, 0, NULL, '2026-09-07 01:48:40'),
(1984, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +19).', 'inventory_transaction', 12, 0, NULL, '2026-09-07 01:50:00'),
(1985, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +19).', 'inventory_transaction', 12, 0, NULL, '2026-09-07 01:50:00'),
(1986, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +19).', 'inventory_transaction', 12, 0, NULL, '2026-09-07 01:50:00'),
(1987, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +19).', 'inventory_transaction', 12, 0, NULL, '2026-09-07 01:50:00'),
(1988, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +19).', 'inventory_transaction', 12, 0, NULL, '2026-09-07 01:50:00'),
(1989, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +19).', 'inventory_transaction', 12, 0, NULL, '2026-09-07 01:50:00'),
(1990, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO011.', 'job_order', 16, 0, NULL, '2026-09-07 02:14:38'),
(1991, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO011.', 'job_order', 16, 0, NULL, '2026-09-07 02:14:38'),
(1992, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO011.', 'job_order', 16, 0, NULL, '2026-09-07 02:14:38'),
(1993, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO011.', 'job_order', 16, 0, NULL, '2026-09-07 02:14:38'),
(1994, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO011.', 'job_order', 16, 0, NULL, '2026-09-07 02:14:38'),
(1995, 11, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO011.', 'job_order', 16, 0, NULL, '2026-09-07 02:14:38'),
(1996, 13, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO011.', 'job_order', 16, 0, NULL, '2026-09-07 02:14:38'),
(1997, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO011.', 'job_order', 16, 0, NULL, '2026-09-07 02:14:38'),
(1998, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO011.', 'job_order', 16, 0, NULL, '2026-09-07 02:14:38'),
(1999, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO011 (Status: Ongoing).', 'job_order', 16, 0, NULL, '2026-09-07 02:15:11'),
(2000, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO011 (Status: Ongoing).', 'job_order', 16, 0, NULL, '2026-09-07 02:15:11'),
(2001, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO011 (Status: Ongoing).', 'job_order', 16, 0, NULL, '2026-09-07 02:15:11'),
(2002, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO011 (Status: Ongoing).', 'job_order', 16, 0, NULL, '2026-09-07 02:15:11'),
(2003, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO011 (Status: Ongoing).', 'job_order', 16, 0, NULL, '2026-09-07 02:15:11'),
(2004, 11, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO011 (Status: Ongoing).', 'job_order', 16, 0, NULL, '2026-09-07 02:15:11'),
(2005, 13, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO011 (Status: Ongoing).', 'job_order', 16, 0, NULL, '2026-09-07 02:15:11'),
(2006, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO011 (Status: Ongoing).', 'job_order', 16, 0, NULL, '2026-09-07 02:15:11'),
(2007, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO011 (Status: Ongoing).', 'job_order', 16, 0, NULL, '2026-09-07 02:15:11'),
(2008, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO011 (Status: Completed).', 'job_order', 16, 0, NULL, '2026-09-07 03:35:14'),
(2009, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO011 (Status: Completed).', 'job_order', 16, 0, NULL, '2026-09-07 03:35:14'),
(2010, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO011 (Status: Completed).', 'job_order', 16, 0, NULL, '2026-09-07 03:35:14'),
(2011, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO011 (Status: Completed).', 'job_order', 16, 0, NULL, '2026-09-07 03:35:14'),
(2012, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO011 (Status: Completed).', 'job_order', 16, 0, NULL, '2026-09-07 03:35:14'),
(2013, 11, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO011 (Status: Completed).', 'job_order', 16, 0, NULL, '2026-09-07 03:35:14'),
(2014, 13, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO011 (Status: Completed).', 'job_order', 16, 0, NULL, '2026-09-07 03:35:14'),
(2015, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO011 (Status: Completed).', 'job_order', 16, 0, NULL, '2026-09-07 03:35:14'),
(2016, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO011 (Status: Completed).', 'job_order', 16, 0, NULL, '2026-09-07 03:35:14'),
(2017, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO011 (Amount: ₱1,200.00).', 'job_order', 16, 0, NULL, '2026-09-07 03:39:02'),
(2018, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO011 (Amount: ₱1,200.00).', 'job_order', 16, 0, NULL, '2026-09-07 03:39:02'),
(2019, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO011 (Amount: ₱1,200.00).', 'job_order', 16, 0, NULL, '2026-09-07 03:39:02'),
(2020, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO011 (Amount: ₱1,200.00).', 'job_order', 16, 0, NULL, '2026-09-07 03:39:02'),
(2021, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO011 (Amount: ₱1,200.00).', 'job_order', 16, 0, NULL, '2026-09-07 03:39:02'),
(2022, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO011 (Amount: ₱1,200.00).', 'job_order', 16, 0, NULL, '2026-09-07 03:39:02'),
(2023, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO011 (Amount: ₱1,200.00).', 'job_order', 16, 0, NULL, '2026-09-07 03:39:02'),
(2024, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱20.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 03:39:28'),
(2025, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱20.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 03:39:28'),
(2026, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱20.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 03:39:28'),
(2027, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱20.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 03:39:28'),
(2028, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱20.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 03:39:28'),
(2029, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱20.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 03:39:28'),
(2030, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱3,409.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 09:26:08'),
(2031, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱3,409.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 09:26:08'),
(2032, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱3,409.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 09:26:08'),
(2033, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱3,409.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 09:26:08'),
(2034, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱3,409.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 09:26:08'),
(2035, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱3,409.00, Date: Sep 07, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-07 09:26:08'),
(2036, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO012.', 'job_order', 17, 0, NULL, '2026-09-08 00:24:35'),
(2037, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO012.', 'job_order', 17, 0, NULL, '2026-09-08 00:24:35'),
(2038, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO012.', 'job_order', 17, 0, NULL, '2026-09-08 00:24:35'),
(2039, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO012.', 'job_order', 17, 0, NULL, '2026-09-08 00:24:35'),
(2040, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO012.', 'job_order', 17, 0, NULL, '2026-09-08 00:24:35'),
(2041, 11, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO012.', 'job_order', 17, 0, NULL, '2026-09-08 00:24:35'),
(2042, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO012.', 'job_order', 17, 0, NULL, '2026-09-08 00:24:35'),
(2043, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO012.', 'job_order', 17, 0, NULL, '2026-09-08 00:24:35'),
(2044, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO012 (Status: Ongoing).', 'job_order', 17, 0, NULL, '2026-09-08 00:25:02'),
(2045, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO012 (Status: Ongoing).', 'job_order', 17, 0, NULL, '2026-09-08 00:25:02'),
(2046, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO012 (Status: Ongoing).', 'job_order', 17, 0, NULL, '2026-09-08 00:25:02'),
(2047, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO012 (Status: Ongoing).', 'job_order', 17, 0, NULL, '2026-09-08 00:25:02'),
(2048, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO012 (Status: Ongoing).', 'job_order', 17, 0, NULL, '2026-09-08 00:25:02'),
(2049, 11, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO012 (Status: Ongoing).', 'job_order', 17, 0, NULL, '2026-09-08 00:25:02'),
(2050, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO012 (Status: Ongoing).', 'job_order', 17, 0, NULL, '2026-09-08 00:25:02'),
(2051, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO012 (Status: Ongoing).', 'job_order', 17, 0, NULL, '2026-09-08 00:25:02'),
(2052, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO013.', 'job_order', 18, 0, NULL, '2026-09-08 04:58:09'),
(2053, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO013.', 'job_order', 18, 0, NULL, '2026-09-08 04:58:09'),
(2054, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO013.', 'job_order', 18, 0, NULL, '2026-09-08 04:58:09'),
(2055, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO013.', 'job_order', 18, 0, NULL, '2026-09-08 04:58:09'),
(2056, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO013.', 'job_order', 18, 0, NULL, '2026-09-08 04:58:09'),
(2057, 9, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO013.', 'job_order', 18, 0, NULL, '2026-09-08 04:58:09'),
(2058, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO013.', 'job_order', 18, 0, NULL, '2026-09-08 04:58:09'),
(2059, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO013.', 'job_order', 18, 0, NULL, '2026-09-08 04:58:09'),
(2060, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Ongoing).', 'job_order', 18, 0, NULL, '2026-09-08 04:58:15'),
(2061, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Ongoing).', 'job_order', 18, 0, NULL, '2026-09-08 04:58:15'),
(2062, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Ongoing).', 'job_order', 18, 0, NULL, '2026-09-08 04:58:15'),
(2063, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Ongoing).', 'job_order', 18, 0, NULL, '2026-09-08 04:58:15'),
(2064, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Ongoing).', 'job_order', 18, 0, NULL, '2026-09-08 04:58:15'),
(2065, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Ongoing).', 'job_order', 18, 0, NULL, '2026-09-08 04:58:15'),
(2066, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Ongoing).', 'job_order', 18, 0, NULL, '2026-09-08 04:58:15'),
(2067, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Ongoing).', 'job_order', 18, 0, NULL, '2026-09-08 04:58:15'),
(2068, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Completed).', 'job_order', 18, 0, NULL, '2026-09-08 05:40:07'),
(2069, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Completed).', 'job_order', 18, 0, NULL, '2026-09-08 05:40:07'),
(2070, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Completed).', 'job_order', 18, 0, NULL, '2026-09-08 05:40:07'),
(2071, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Completed).', 'job_order', 18, 0, NULL, '2026-09-08 05:40:07'),
(2072, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Completed).', 'job_order', 18, 0, NULL, '2026-09-08 05:40:07'),
(2073, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Completed).', 'job_order', 18, 0, NULL, '2026-09-08 05:40:07'),
(2074, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Completed).', 'job_order', 18, 0, NULL, '2026-09-08 05:40:07'),
(2075, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO013 (Status: Completed).', 'job_order', 18, 0, NULL, '2026-09-08 05:40:07'),
(2076, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO013 (Amount: ₱5,500.00).', 'job_order', 18, 0, NULL, '2026-09-08 06:03:20'),
(2077, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO013 (Amount: ₱5,500.00).', 'job_order', 18, 0, NULL, '2026-09-08 06:03:20'),
(2078, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO013 (Amount: ₱5,500.00).', 'job_order', 18, 0, NULL, '2026-09-08 06:03:21'),
(2079, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO013 (Amount: ₱5,500.00).', 'job_order', 18, 0, NULL, '2026-09-08 06:03:21'),
(2080, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO013 (Amount: ₱5,500.00).', 'job_order', 18, 0, NULL, '2026-09-08 06:03:21'),
(2081, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO013 (Amount: ₱5,500.00).', 'job_order', 18, 0, NULL, '2026-09-08 06:03:21'),
(2082, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO013 (Amount: ₱5,500.00).', 'job_order', 18, 0, NULL, '2026-09-08 06:03:21'),
(2083, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱400.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-08 06:19:17'),
(2084, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱400.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-08 06:19:17'),
(2085, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱400.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-08 06:19:17'),
(2086, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱400.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-08 06:19:17'),
(2087, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱400.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-08 06:19:17'),
(2088, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱400.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-08 06:19:17'),
(2089, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO014.', 'job_order', 19, 0, NULL, '2026-09-08 06:34:37'),
(2090, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO014.', 'job_order', 19, 0, NULL, '2026-09-08 06:34:37'),
(2091, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO014.', 'job_order', 19, 0, NULL, '2026-09-08 06:34:37'),
(2092, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO014.', 'job_order', 19, 0, NULL, '2026-09-08 06:34:37'),
(2093, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO014.', 'job_order', 19, 0, NULL, '2026-09-08 06:34:37'),
(2094, 9, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO014.', 'job_order', 19, 0, NULL, '2026-09-08 06:34:37'),
(2095, 13, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO014.', 'job_order', 19, 0, NULL, '2026-09-08 06:34:37'),
(2096, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO014.', 'job_order', 19, 0, NULL, '2026-09-08 06:34:37'),
(2097, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO014.', 'job_order', 19, 0, NULL, '2026-09-08 06:34:37'),
(2098, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO014 (Status: Ongoing).', 'job_order', 19, 0, NULL, '2026-09-08 06:34:43'),
(2099, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO014 (Status: Ongoing).', 'job_order', 19, 0, NULL, '2026-09-08 06:34:43'),
(2100, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO014 (Status: Ongoing).', 'job_order', 19, 0, NULL, '2026-09-08 06:34:43'),
(2101, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO014 (Status: Ongoing).', 'job_order', 19, 0, NULL, '2026-09-08 06:34:43'),
(2102, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO014 (Status: Ongoing).', 'job_order', 19, 0, NULL, '2026-09-08 06:34:43'),
(2103, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO014 (Status: Ongoing).', 'job_order', 19, 0, NULL, '2026-09-08 06:34:43'),
(2104, 13, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO014 (Status: Ongoing).', 'job_order', 19, 0, NULL, '2026-09-08 06:34:43'),
(2105, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO014 (Status: Ongoing).', 'job_order', 19, 0, NULL, '2026-09-08 06:34:43'),
(2106, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO014 (Status: Ongoing).', 'job_order', 19, 0, NULL, '2026-09-08 06:34:43'),
(2107, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱533.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 00:58:05'),
(2108, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱533.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 00:58:05'),
(2109, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱533.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 00:58:05'),
(2110, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱533.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 00:58:05'),
(2111, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱533.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 00:58:05'),
(2112, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱533.00, Date: Sep 08, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 00:58:05'),
(2113, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO012 (Status: Completed).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:16'),
(2114, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO012 (Status: Completed).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:16'),
(2115, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO012 (Status: Completed).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:16'),
(2116, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO012 (Status: Completed).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:16'),
(2117, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO012 (Status: Completed).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:16'),
(2118, 11, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO012 (Status: Completed).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:16'),
(2119, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO012 (Status: Completed).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:16'),
(2120, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO012 (Status: Completed).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:16'),
(2121, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO012 (Amount: ₱3,000.00).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:39'),
(2122, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO012 (Amount: ₱3,000.00).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:39'),
(2123, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO012 (Amount: ₱3,000.00).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:39'),
(2124, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO012 (Amount: ₱3,000.00).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:39'),
(2125, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO012 (Amount: ₱3,000.00).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:39'),
(2126, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO012 (Amount: ₱3,000.00).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:39'),
(2127, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO012 (Amount: ₱3,000.00).', 'job_order', 17, 0, NULL, '2026-09-09 00:58:39'),
(2128, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO002 (Status: Released).', 'job_order', 7, 0, NULL, '2026-09-09 00:58:47'),
(2129, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO002 (Status: Released).', 'job_order', 7, 0, NULL, '2026-09-09 00:58:47'),
(2130, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO002 (Status: Released).', 'job_order', 7, 0, NULL, '2026-09-09 00:58:47'),
(2131, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO002 (Status: Released).', 'job_order', 7, 0, NULL, '2026-09-09 00:58:47'),
(2132, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO002 (Status: Released).', 'job_order', 7, 0, NULL, '2026-09-09 00:58:47'),
(2133, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO002 (Status: Released).', 'job_order', 7, 0, NULL, '2026-09-09 00:58:47'),
(2134, 11, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO002 (Status: Released).', 'job_order', 7, 0, NULL, '2026-09-09 00:58:47'),
(2135, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO002 (Status: Released).', 'job_order', 7, 0, NULL, '2026-09-09 00:58:47'),
(2136, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO002 (Status: Released).', 'job_order', 7, 0, NULL, '2026-09-09 00:58:47'),
(2137, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-09 00:58:51'),
(2138, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-09 00:58:51'),
(2139, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-09 00:58:51'),
(2140, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-09 00:58:51'),
(2141, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-09 00:58:51'),
(2142, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-09 00:58:51'),
(2143, 10, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-09 00:58:51'),
(2144, 11, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-09 00:58:51'),
(2145, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-09 00:58:51'),
(2146, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO003 (Status: Released).', 'job_order', 8, 0, NULL, '2026-09-09 00:58:51'),
(2147, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO004 (Status: Released).', 'job_order', 9, 0, NULL, '2026-09-09 00:58:53'),
(2148, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO004 (Status: Released).', 'job_order', 9, 0, NULL, '2026-09-09 00:58:53');
INSERT INTO `notifications` (`id`, `user_id`, `staff_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `read_at`, `created_at`) VALUES
(2149, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO004 (Status: Released).', 'job_order', 9, 0, NULL, '2026-09-09 00:58:53'),
(2150, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO004 (Status: Released).', 'job_order', 9, 0, NULL, '2026-09-09 00:58:53'),
(2151, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO004 (Status: Released).', 'job_order', 9, 0, NULL, '2026-09-09 00:58:53'),
(2152, 11, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO004 (Status: Released).', 'job_order', 9, 0, NULL, '2026-09-09 00:58:53'),
(2153, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO004 (Status: Released).', 'job_order', 9, 0, NULL, '2026-09-09 00:58:53'),
(2154, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO004 (Status: Released).', 'job_order', 9, 0, NULL, '2026-09-09 00:58:53'),
(2155, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO005 (Status: Released).', 'job_order', 10, 0, NULL, '2026-09-09 00:58:57'),
(2156, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO005 (Status: Released).', 'job_order', 10, 0, NULL, '2026-09-09 00:58:57'),
(2157, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO005 (Status: Released).', 'job_order', 10, 0, NULL, '2026-09-09 00:58:57'),
(2158, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO005 (Status: Released).', 'job_order', 10, 0, NULL, '2026-09-09 00:58:57'),
(2159, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO005 (Status: Released).', 'job_order', 10, 0, NULL, '2026-09-09 00:58:57'),
(2160, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO005 (Status: Released).', 'job_order', 10, 0, NULL, '2026-09-09 00:58:57'),
(2161, 11, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO005 (Status: Released).', 'job_order', 10, 0, NULL, '2026-09-09 00:58:57'),
(2162, 13, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO005 (Status: Released).', 'job_order', 10, 0, NULL, '2026-09-09 00:58:57'),
(2163, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO005 (Status: Released).', 'job_order', 10, 0, NULL, '2026-09-09 00:58:57'),
(2164, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO005 (Status: Released).', 'job_order', 10, 0, NULL, '2026-09-09 00:58:57'),
(2165, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO006 (Status: Released).', 'job_order', 11, 0, NULL, '2026-09-09 00:59:00'),
(2166, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO006 (Status: Released).', 'job_order', 11, 0, NULL, '2026-09-09 00:59:00'),
(2167, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO006 (Status: Released).', 'job_order', 11, 0, NULL, '2026-09-09 00:59:00'),
(2168, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO006 (Status: Released).', 'job_order', 11, 0, NULL, '2026-09-09 00:59:00'),
(2169, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO006 (Status: Released).', 'job_order', 11, 0, NULL, '2026-09-09 00:59:00'),
(2170, 10, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO006 (Status: Released).', 'job_order', 11, 0, NULL, '2026-09-09 00:59:00'),
(2171, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO006 (Status: Released).', 'job_order', 11, 0, NULL, '2026-09-09 00:59:00'),
(2172, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO006 (Status: Released).', 'job_order', 11, 0, NULL, '2026-09-09 00:59:00'),
(2173, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Released).', 'job_order', 13, 0, NULL, '2026-09-09 00:59:02'),
(2174, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Released).', 'job_order', 13, 0, NULL, '2026-09-09 00:59:02'),
(2175, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Released).', 'job_order', 13, 0, NULL, '2026-09-09 00:59:02'),
(2176, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Released).', 'job_order', 13, 0, NULL, '2026-09-09 00:59:02'),
(2177, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Released).', 'job_order', 13, 0, NULL, '2026-09-09 00:59:02'),
(2178, 10, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Released).', 'job_order', 13, 0, NULL, '2026-09-09 00:59:02'),
(2179, 13, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Released).', 'job_order', 13, 0, NULL, '2026-09-09 00:59:02'),
(2180, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Released).', 'job_order', 13, 0, NULL, '2026-09-09 00:59:02'),
(2181, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO008 (Status: Released).', 'job_order', 13, 0, NULL, '2026-09-09 00:59:02'),
(2182, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO009 (Status: Released).', 'job_order', 14, 0, NULL, '2026-09-09 00:59:05'),
(2183, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO009 (Status: Released).', 'job_order', 14, 0, NULL, '2026-09-09 00:59:05'),
(2184, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO009 (Status: Released).', 'job_order', 14, 0, NULL, '2026-09-09 00:59:05'),
(2185, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO009 (Status: Released).', 'job_order', 14, 0, NULL, '2026-09-09 00:59:05'),
(2186, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO009 (Status: Released).', 'job_order', 14, 0, NULL, '2026-09-09 00:59:05'),
(2187, 10, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO009 (Status: Released).', 'job_order', 14, 0, NULL, '2026-09-09 00:59:05'),
(2188, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO009 (Status: Released).', 'job_order', 14, 0, NULL, '2026-09-09 00:59:05'),
(2189, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO009 (Status: Released).', 'job_order', 14, 0, NULL, '2026-09-09 00:59:05'),
(2190, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO010 (Status: Released).', 'job_order', 15, 0, NULL, '2026-09-09 00:59:12'),
(2191, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO010 (Status: Released).', 'job_order', 15, 0, NULL, '2026-09-09 00:59:12'),
(2192, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO010 (Status: Released).', 'job_order', 15, 0, NULL, '2026-09-09 00:59:12'),
(2193, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO010 (Status: Released).', 'job_order', 15, 0, NULL, '2026-09-09 00:59:12'),
(2194, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO010 (Status: Released).', 'job_order', 15, 0, NULL, '2026-09-09 00:59:12'),
(2195, 12, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO010 (Status: Released).', 'job_order', 15, 0, NULL, '2026-09-09 00:59:12'),
(2196, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO010 (Status: Released).', 'job_order', 15, 0, NULL, '2026-09-09 00:59:12'),
(2197, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO010 (Status: Released).', 'job_order', 15, 0, NULL, '2026-09-09 00:59:12'),
(2198, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-09 02:32:26'),
(2199, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-09 02:32:26'),
(2200, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-09 02:32:26'),
(2201, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-09 02:32:26'),
(2202, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-09 02:32:26'),
(2203, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-09 02:32:26'),
(2204, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-09 02:32:26'),
(2205, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO007 (Status: Completed).', 'job_order', 12, 0, NULL, '2026-09-09 02:32:26'),
(2206, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO007 (Amount: ₱10,200.00).', 'job_order', 12, 0, NULL, '2026-09-09 02:33:18'),
(2207, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO007 (Amount: ₱10,200.00).', 'job_order', 12, 0, NULL, '2026-09-09 02:33:18'),
(2208, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO007 (Amount: ₱10,200.00).', 'job_order', 12, 0, NULL, '2026-09-09 02:33:18'),
(2209, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO007 (Amount: ₱10,200.00).', 'job_order', 12, 0, NULL, '2026-09-09 02:33:18'),
(2210, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO007 (Amount: ₱10,200.00).', 'job_order', 12, 0, NULL, '2026-09-09 02:33:18'),
(2211, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO007 (Amount: ₱10,200.00).', 'job_order', 12, 0, NULL, '2026-09-09 02:33:18'),
(2212, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO007 (Amount: ₱10,200.00).', 'job_order', 12, 0, NULL, '2026-09-09 02:33:18'),
(2213, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:41'),
(2214, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:41'),
(2215, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:41'),
(2216, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:41'),
(2217, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:41'),
(2218, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:41'),
(2219, 4, NULL, 'system', 'Expense Deleted', 'LOVELY JOYCE deleted expense entry (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:57'),
(2220, 16, NULL, 'system', 'Expense Deleted', 'LOVELY JOYCE deleted expense entry (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:57'),
(2221, 5, NULL, 'system', 'Expense Deleted', 'LOVELY JOYCE deleted expense entry (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:57'),
(2222, 6, NULL, 'system', 'Expense Deleted', 'LOVELY JOYCE deleted expense entry (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:57'),
(2223, 7, NULL, 'system', 'Expense Deleted', 'LOVELY JOYCE deleted expense entry (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:57'),
(2224, 2, NULL, 'system', 'Expense Deleted', 'LOVELY JOYCE deleted expense entry (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:33:57'),
(2225, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:20'),
(2226, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:20'),
(2227, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:20'),
(2228, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:20'),
(2229, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:20'),
(2230, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,000.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:20'),
(2231, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱150.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:35'),
(2232, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱150.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:35'),
(2233, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱150.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:35'),
(2234, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱150.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:35'),
(2235, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱150.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:35'),
(2236, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱150.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 02:34:35'),
(2237, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO015.', 'job_order', 20, 0, NULL, '2026-09-09 06:31:30'),
(2238, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO015.', 'job_order', 20, 0, NULL, '2026-09-09 06:31:30'),
(2239, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO015.', 'job_order', 20, 0, NULL, '2026-09-09 06:31:30'),
(2240, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO015.', 'job_order', 20, 0, NULL, '2026-09-09 06:31:30'),
(2241, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO015.', 'job_order', 20, 0, NULL, '2026-09-09 06:31:30'),
(2242, 9, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO015.', 'job_order', 20, 0, NULL, '2026-09-09 06:31:30'),
(2243, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO015.', 'job_order', 20, 0, NULL, '2026-09-09 06:31:30'),
(2244, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO015.', 'job_order', 20, 0, NULL, '2026-09-09 06:31:30'),
(2245, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO015 (Status: Ongoing).', 'job_order', 20, 0, NULL, '2026-09-09 06:31:48'),
(2246, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO015 (Status: Ongoing).', 'job_order', 20, 0, NULL, '2026-09-09 06:31:48'),
(2247, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO015 (Status: Ongoing).', 'job_order', 20, 0, NULL, '2026-09-09 06:31:48'),
(2248, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO015 (Status: Ongoing).', 'job_order', 20, 0, NULL, '2026-09-09 06:31:48'),
(2249, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO015 (Status: Ongoing).', 'job_order', 20, 0, NULL, '2026-09-09 06:31:48'),
(2250, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO015 (Status: Ongoing).', 'job_order', 20, 0, NULL, '2026-09-09 06:31:48'),
(2251, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO015 (Status: Ongoing).', 'job_order', 20, 0, NULL, '2026-09-09 06:31:48'),
(2252, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO015 (Status: Ongoing).', 'job_order', 20, 0, NULL, '2026-09-09 06:31:48'),
(2253, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO015 (Status: Completed).', 'job_order', 20, 0, NULL, '2026-09-09 08:00:00'),
(2254, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO015 (Status: Completed).', 'job_order', 20, 0, NULL, '2026-09-09 08:00:00'),
(2255, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO015 (Status: Completed).', 'job_order', 20, 0, NULL, '2026-09-09 08:00:00'),
(2256, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO015 (Status: Completed).', 'job_order', 20, 0, NULL, '2026-09-09 08:00:00'),
(2257, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO015 (Status: Completed).', 'job_order', 20, 0, NULL, '2026-09-09 08:00:00'),
(2258, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO015 (Status: Completed).', 'job_order', 20, 0, NULL, '2026-09-09 08:00:00'),
(2259, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO015 (Status: Completed).', 'job_order', 20, 0, NULL, '2026-09-09 08:00:00'),
(2260, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO015 (Status: Completed).', 'job_order', 20, 0, NULL, '2026-09-09 08:00:00'),
(2261, 4, NULL, 'system', 'Product Added', 'LOVELY JOYCE added product CVT VALVOLINE (Code: PRD37, Status: ACTIVE).', 'product', 38, 0, NULL, '2026-09-09 08:42:25'),
(2262, 16, NULL, 'system', 'Product Added', 'LOVELY JOYCE added product CVT VALVOLINE (Code: PRD37, Status: ACTIVE).', 'product', 38, 0, NULL, '2026-09-09 08:42:25'),
(2263, 5, NULL, 'system', 'Product Added', 'LOVELY JOYCE added product CVT VALVOLINE (Code: PRD37, Status: ACTIVE).', 'product', 38, 0, NULL, '2026-09-09 08:42:25'),
(2264, 7, NULL, 'system', 'Product Added', 'LOVELY JOYCE added product CVT VALVOLINE (Code: PRD37, Status: ACTIVE).', 'product', 38, 0, NULL, '2026-09-09 08:42:25'),
(2265, 2, NULL, 'system', 'Product Added', 'LOVELY JOYCE added product CVT VALVOLINE (Code: PRD37, Status: ACTIVE).', 'product', 38, 0, NULL, '2026-09-09 08:42:25'),
(2266, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CVT VALVOLINE (Quantity: +4).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:43:37'),
(2267, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CVT VALVOLINE (Quantity: +4).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:43:37'),
(2268, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CVT VALVOLINE (Quantity: +4).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:43:37'),
(2269, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CVT VALVOLINE (Quantity: +4).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:43:37'),
(2270, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CVT VALVOLINE (Quantity: +4).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:43:37'),
(2271, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CVT VALVOLINE (Quantity: +4).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:43:37'),
(2272, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO015 (Amount: ₱4,650.00).', 'job_order', 20, 0, NULL, '2026-09-09 08:44:30'),
(2273, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO015 (Amount: ₱4,650.00).', 'job_order', 20, 0, NULL, '2026-09-09 08:44:30'),
(2274, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO015 (Amount: ₱4,650.00).', 'job_order', 20, 0, NULL, '2026-09-09 08:44:30'),
(2275, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO015 (Amount: ₱4,650.00).', 'job_order', 20, 0, NULL, '2026-09-09 08:44:30'),
(2276, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO015 (Amount: ₱4,650.00).', 'job_order', 20, 0, NULL, '2026-09-09 08:44:30'),
(2277, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO015 (Amount: ₱4,650.00).', 'job_order', 20, 0, NULL, '2026-09-09 08:44:30'),
(2278, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO015 (Amount: ₱4,650.00).', 'job_order', 20, 0, NULL, '2026-09-09 08:44:30'),
(2279, 4, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock CVT VALVOLINE (Quantity: -1).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:47:51'),
(2280, 16, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock CVT VALVOLINE (Quantity: -1).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:47:51'),
(2281, 5, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock CVT VALVOLINE (Quantity: -1).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:47:51'),
(2282, 6, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock CVT VALVOLINE (Quantity: -1).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:47:51'),
(2283, 7, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock CVT VALVOLINE (Quantity: -1).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:47:51'),
(2284, 2, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock CVT VALVOLINE (Quantity: -1).', 'inventory_transaction', 38, 0, NULL, '2026-09-09 08:47:51'),
(2285, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,600.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 09:02:25'),
(2286, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,600.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 09:02:25'),
(2287, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,600.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 09:02:25'),
(2288, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,600.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 09:02:25'),
(2289, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,600.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 09:02:25'),
(2290, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,600.00, Date: Sep 09, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-09 09:02:25'),
(2291, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO014 (Status: Completed).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:39'),
(2292, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO014 (Status: Completed).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:39'),
(2293, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO014 (Status: Completed).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:39'),
(2294, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO014 (Status: Completed).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:39'),
(2295, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO014 (Status: Completed).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:39'),
(2296, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO014 (Status: Completed).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:39'),
(2297, 13, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO014 (Status: Completed).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:39'),
(2298, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO014 (Status: Completed).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:39'),
(2299, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO014 (Status: Completed).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:39'),
(2300, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO014 (Amount: ₱9,300.00).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:57'),
(2301, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO014 (Amount: ₱9,300.00).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:57'),
(2302, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO014 (Amount: ₱9,300.00).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:57'),
(2303, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO014 (Amount: ₱9,300.00).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:57'),
(2304, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO014 (Amount: ₱9,300.00).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:57'),
(2305, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO014 (Amount: ₱9,300.00).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:57'),
(2306, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO014 (Amount: ₱9,300.00).', 'job_order', 19, 0, NULL, '2026-09-10 03:51:57'),
(2307, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,180.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:19'),
(2308, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,180.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:19'),
(2309, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,180.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:19'),
(2310, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,180.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:19'),
(2311, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,180.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:19'),
(2312, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,180.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:19'),
(2313, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:41'),
(2314, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:41'),
(2315, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:41'),
(2316, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:41'),
(2317, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:41'),
(2318, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:52:41'),
(2319, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,120.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:53:05'),
(2320, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,120.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:53:05'),
(2321, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,120.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:53:05'),
(2322, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,120.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:53:05'),
(2323, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,120.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:53:05'),
(2324, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,120.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 03:53:05'),
(2325, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO016.', 'job_order', 21, 0, NULL, '2026-09-10 07:22:06'),
(2326, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO016.', 'job_order', 21, 0, NULL, '2026-09-10 07:22:06'),
(2327, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO016.', 'job_order', 21, 0, NULL, '2026-09-10 07:22:06'),
(2328, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO016.', 'job_order', 21, 0, NULL, '2026-09-10 07:22:06'),
(2329, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO016.', 'job_order', 21, 0, NULL, '2026-09-10 07:22:06'),
(2330, 13, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO016.', 'job_order', 21, 0, NULL, '2026-09-10 07:22:06'),
(2331, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO016.', 'job_order', 21, 0, NULL, '2026-09-10 07:22:06'),
(2332, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO016.', 'job_order', 21, 0, NULL, '2026-09-10 07:22:06'),
(2333, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO016 (Status: Ongoing).', 'job_order', 21, 0, NULL, '2026-09-10 07:22:12'),
(2334, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO016 (Status: Ongoing).', 'job_order', 21, 0, NULL, '2026-09-10 07:22:12'),
(2335, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO016 (Status: Ongoing).', 'job_order', 21, 0, NULL, '2026-09-10 07:22:12'),
(2336, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO016 (Status: Ongoing).', 'job_order', 21, 0, NULL, '2026-09-10 07:22:12'),
(2337, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO016 (Status: Ongoing).', 'job_order', 21, 0, NULL, '2026-09-10 07:22:12'),
(2338, 13, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO016 (Status: Ongoing).', 'job_order', 21, 0, NULL, '2026-09-10 07:22:12'),
(2339, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO016 (Status: Ongoing).', 'job_order', 21, 0, NULL, '2026-09-10 07:22:12'),
(2340, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO016 (Status: Ongoing).', 'job_order', 21, 0, NULL, '2026-09-10 07:22:12'),
(2341, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 09:01:01'),
(2342, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 09:01:01'),
(2343, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 09:01:01'),
(2344, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 09:01:01'),
(2345, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 09:01:01'),
(2346, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-10 09:01:01'),
(2347, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO016 (Status: Completed).', 'job_order', 21, 0, NULL, '2026-09-11 01:16:48'),
(2348, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO016 (Status: Completed).', 'job_order', 21, 0, NULL, '2026-09-11 01:16:48'),
(2349, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO016 (Status: Completed).', 'job_order', 21, 0, NULL, '2026-09-11 01:16:48'),
(2350, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO016 (Status: Completed).', 'job_order', 21, 0, NULL, '2026-09-11 01:16:48'),
(2351, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO016 (Status: Completed).', 'job_order', 21, 0, NULL, '2026-09-11 01:16:48'),
(2352, 13, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO016 (Status: Completed).', 'job_order', 21, 0, NULL, '2026-09-11 01:16:48'),
(2353, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO016 (Status: Completed).', 'job_order', 21, 0, NULL, '2026-09-11 01:16:48'),
(2354, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO016 (Status: Completed).', 'job_order', 21, 0, NULL, '2026-09-11 01:16:48'),
(2355, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱350.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 02:38:18'),
(2356, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱350.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 02:38:18'),
(2357, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱350.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 02:38:18'),
(2358, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱350.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 02:38:18'),
(2359, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱350.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 02:38:18'),
(2360, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱350.00, Date: Sep 10, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 02:38:18'),
(2361, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 11, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 09:54:49'),
(2362, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 11, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 09:54:49'),
(2363, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 11, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 09:54:49'),
(2364, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 11, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 09:54:49'),
(2365, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 11, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 09:54:49'),
(2366, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱500.00, Date: Sep 11, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-11 09:54:49'),
(2367, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO016 (Amount: ₱1,200.00).', 'job_order', 21, 0, NULL, '2026-09-11 09:55:11'),
(2368, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO016 (Amount: ₱1,200.00).', 'job_order', 21, 0, NULL, '2026-09-11 09:55:11'),
(2369, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO016 (Amount: ₱1,200.00).', 'job_order', 21, 0, NULL, '2026-09-11 09:55:11'),
(2370, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO016 (Amount: ₱1,200.00).', 'job_order', 21, 0, NULL, '2026-09-11 09:55:11'),
(2371, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO016 (Amount: ₱1,200.00).', 'job_order', 21, 0, NULL, '2026-09-11 09:55:11'),
(2372, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO016 (Amount: ₱1,200.00).', 'job_order', 21, 0, NULL, '2026-09-11 09:55:11'),
(2373, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO016 (Amount: ₱1,200.00).', 'job_order', 21, 0, NULL, '2026-09-11 09:55:11'),
(2374, 4, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO017.', 'job_order', 22, 0, NULL, '2026-09-12 03:10:10'),
(2375, 5, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO017.', 'job_order', 22, 0, NULL, '2026-09-12 03:10:10'),
(2376, 6, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO017.', 'job_order', 22, 0, NULL, '2026-09-12 03:10:10'),
(2377, 7, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO017.', 'job_order', 22, 0, NULL, '2026-09-12 03:10:10'),
(2378, 8, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO017.', 'job_order', 22, 0, NULL, '2026-09-12 03:10:10'),
(2379, 9, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO017.', 'job_order', 22, 0, NULL, '2026-09-12 03:10:10'),
(2380, 16, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO017.', 'job_order', 22, 0, NULL, '2026-09-12 03:10:10'),
(2381, 2, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO017.', 'job_order', 22, 0, NULL, '2026-09-12 03:10:10'),
(2382, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Ongoing).', 'job_order', 22, 0, NULL, '2026-09-12 03:18:00'),
(2383, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Ongoing).', 'job_order', 22, 0, NULL, '2026-09-12 03:18:00'),
(2384, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Ongoing).', 'job_order', 22, 0, NULL, '2026-09-12 03:18:00'),
(2385, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Ongoing).', 'job_order', 22, 0, NULL, '2026-09-12 03:18:00'),
(2386, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Ongoing).', 'job_order', 22, 0, NULL, '2026-09-12 03:18:00'),
(2387, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Ongoing).', 'job_order', 22, 0, NULL, '2026-09-12 03:18:00'),
(2388, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Ongoing).', 'job_order', 22, 0, NULL, '2026-09-12 03:18:00'),
(2389, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Ongoing).', 'job_order', 22, 0, NULL, '2026-09-12 03:18:00'),
(2390, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Completed).', 'job_order', 22, 0, NULL, '2026-09-12 07:02:15'),
(2391, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Completed).', 'job_order', 22, 0, NULL, '2026-09-12 07:02:15'),
(2392, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Completed).', 'job_order', 22, 0, NULL, '2026-09-12 07:02:15'),
(2393, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Completed).', 'job_order', 22, 0, NULL, '2026-09-12 07:02:15'),
(2394, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Completed).', 'job_order', 22, 0, NULL, '2026-09-12 07:02:15'),
(2395, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Completed).', 'job_order', 22, 0, NULL, '2026-09-12 07:02:15'),
(2396, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Completed).', 'job_order', 22, 0, NULL, '2026-09-12 07:02:15'),
(2397, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO017 (Status: Completed).', 'job_order', 22, 0, NULL, '2026-09-12 07:02:15'),
(2398, 4, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO018.', 'job_order', 23, 0, NULL, '2026-09-12 07:06:00'),
(2399, 5, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO018.', 'job_order', 23, 0, NULL, '2026-09-12 07:06:00'),
(2400, 6, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO018.', 'job_order', 23, 0, NULL, '2026-09-12 07:06:00'),
(2401, 7, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO018.', 'job_order', 23, 0, NULL, '2026-09-12 07:06:00'),
(2402, 8, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO018.', 'job_order', 23, 0, NULL, '2026-09-12 07:06:00'),
(2403, 16, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO018.', 'job_order', 23, 0, NULL, '2026-09-12 07:06:00'),
(2404, 2, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO018.', 'job_order', 23, 0, NULL, '2026-09-12 07:06:00'),
(2405, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (TRANSFORMER) (Quantity: +1).', 'inventory_transaction', 4, 0, NULL, '2026-09-12 07:06:31'),
(2406, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (TRANSFORMER) (Quantity: +1).', 'inventory_transaction', 4, 0, NULL, '2026-09-12 07:06:31'),
(2407, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (TRANSFORMER) (Quantity: +1).', 'inventory_transaction', 4, 0, NULL, '2026-09-12 07:06:31'),
(2408, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (TRANSFORMER) (Quantity: +1).', 'inventory_transaction', 4, 0, NULL, '2026-09-12 07:06:31'),
(2409, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (TRANSFORMER) (Quantity: +1).', 'inventory_transaction', 4, 0, NULL, '2026-09-12 07:06:31'),
(2410, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (TRANSFORMER) (Quantity: +1).', 'inventory_transaction', 4, 0, NULL, '2026-09-12 07:06:31'),
(2411, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CABIN FILTER (87139-0N010) (Quantity: +1).', 'inventory_transaction', 30, 0, NULL, '2026-09-12 07:08:26'),
(2412, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CABIN FILTER (87139-0N010) (Quantity: +1).', 'inventory_transaction', 30, 0, NULL, '2026-09-12 07:08:26'),
(2413, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CABIN FILTER (87139-0N010) (Quantity: +1).', 'inventory_transaction', 30, 0, NULL, '2026-09-12 07:08:26'),
(2414, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CABIN FILTER (87139-0N010) (Quantity: +1).', 'inventory_transaction', 30, 0, NULL, '2026-09-12 07:08:26'),
(2415, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CABIN FILTER (87139-0N010) (Quantity: +1).', 'inventory_transaction', 30, 0, NULL, '2026-09-12 07:08:26'),
(2416, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock CABIN FILTER (87139-0N010) (Quantity: +1).', 'inventory_transaction', 30, 0, NULL, '2026-09-12 07:08:26'),
(2417, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO017 (Amount: ₱800.00).', 'job_order', 22, 0, NULL, '2026-09-12 09:15:45'),
(2418, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO017 (Amount: ₱800.00).', 'job_order', 22, 0, NULL, '2026-09-12 09:15:45'),
(2419, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO017 (Amount: ₱800.00).', 'job_order', 22, 0, NULL, '2026-09-12 09:15:45'),
(2420, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO017 (Amount: ₱800.00).', 'job_order', 22, 0, NULL, '2026-09-12 09:15:45'),
(2421, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO017 (Amount: ₱800.00).', 'job_order', 22, 0, NULL, '2026-09-12 09:15:45'),
(2422, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO017 (Amount: ₱800.00).', 'job_order', 22, 0, NULL, '2026-09-12 09:15:45'),
(2423, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO017 (Amount: ₱800.00).', 'job_order', 22, 0, NULL, '2026-09-12 09:15:45'),
(2424, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO018 (Status: Ongoing).', 'job_order', 23, 0, NULL, '2026-09-12 09:15:52'),
(2425, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO018 (Status: Ongoing).', 'job_order', 23, 0, NULL, '2026-09-12 09:15:52'),
(2426, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO018 (Status: Ongoing).', 'job_order', 23, 0, NULL, '2026-09-12 09:15:52'),
(2427, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO018 (Status: Ongoing).', 'job_order', 23, 0, NULL, '2026-09-12 09:15:52'),
(2428, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO018 (Status: Ongoing).', 'job_order', 23, 0, NULL, '2026-09-12 09:15:52'),
(2429, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO018 (Status: Ongoing).', 'job_order', 23, 0, NULL, '2026-09-12 09:15:52'),
(2430, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO018 (Status: Ongoing).', 'job_order', 23, 0, NULL, '2026-09-12 09:15:52'),
(2431, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (MULTI-VEHICLE) (Quantity: +1).', 'inventory_transaction', 5, 0, NULL, '2026-09-12 10:06:24'),
(2432, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (MULTI-VEHICLE) (Quantity: +1).', 'inventory_transaction', 5, 0, NULL, '2026-09-12 10:06:24'),
(2433, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (MULTI-VEHICLE) (Quantity: +1).', 'inventory_transaction', 5, 0, NULL, '2026-09-12 10:06:24'),
(2434, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (MULTI-VEHICLE) (Quantity: +1).', 'inventory_transaction', 5, 0, NULL, '2026-09-12 10:06:24'),
(2435, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (MULTI-VEHICLE) (Quantity: +1).', 'inventory_transaction', 5, 0, NULL, '2026-09-12 10:06:24'),
(2436, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock AIR FILTER (MULTI-VEHICLE) (Quantity: +1).', 'inventory_transaction', 5, 0, NULL, '2026-09-12 10:06:24'),
(2437, 4, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product CABIN FILTER (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-09-12 10:36:54'),
(2438, 16, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product CABIN FILTER (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-09-12 10:36:54'),
(2439, 5, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product CABIN FILTER (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-09-12 10:36:54'),
(2440, 7, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product CABIN FILTER (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-09-12 10:36:54'),
(2441, 2, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product CABIN FILTER (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-09-12 10:36:54'),
(2442, 4, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product CABIN FILTER (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-09-12 10:37:22'),
(2443, 16, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product CABIN FILTER (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-09-12 10:37:22'),
(2444, 5, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product CABIN FILTER (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-09-12 10:37:22'),
(2445, 7, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product CABIN FILTER (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-09-12 10:37:22'),
(2446, 2, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product CABIN FILTER (Status: ACTIVE -> ACTIVE).', 'product', 30, 0, NULL, '2026-09-12 10:37:22'),
(2447, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock COOLANT BLUE (Quantity: +3).', 'inventory_transaction', 19, 0, NULL, '2026-09-12 10:38:14');
INSERT INTO `notifications` (`id`, `user_id`, `staff_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `read_at`, `created_at`) VALUES
(2448, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock COOLANT BLUE (Quantity: +3).', 'inventory_transaction', 19, 0, NULL, '2026-09-12 10:38:14'),
(2449, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock COOLANT BLUE (Quantity: +3).', 'inventory_transaction', 19, 0, NULL, '2026-09-12 10:38:14'),
(2450, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock COOLANT BLUE (Quantity: +3).', 'inventory_transaction', 19, 0, NULL, '2026-09-12 10:38:14'),
(2451, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock COOLANT BLUE (Quantity: +3).', 'inventory_transaction', 19, 0, NULL, '2026-09-12 10:38:14'),
(2452, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock COOLANT BLUE (Quantity: +3).', 'inventory_transaction', 19, 0, NULL, '2026-09-12 10:38:14'),
(2453, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock GEAR OIL (Quantity: +3).', 'inventory_transaction', 17, 0, NULL, '2026-09-12 10:38:31'),
(2454, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock GEAR OIL (Quantity: +3).', 'inventory_transaction', 17, 0, NULL, '2026-09-12 10:38:31'),
(2455, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock GEAR OIL (Quantity: +3).', 'inventory_transaction', 17, 0, NULL, '2026-09-12 10:38:31'),
(2456, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock GEAR OIL (Quantity: +3).', 'inventory_transaction', 17, 0, NULL, '2026-09-12 10:38:31'),
(2457, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock GEAR OIL (Quantity: +3).', 'inventory_transaction', 17, 0, NULL, '2026-09-12 10:38:31'),
(2458, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock GEAR OIL (Quantity: +3).', 'inventory_transaction', 17, 0, NULL, '2026-09-12 10:38:31'),
(2459, 4, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product GEAR OIL (Status: ACTIVE -> ACTIVE).', 'product', 17, 0, NULL, '2026-09-12 10:39:09'),
(2460, 16, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product GEAR OIL (Status: ACTIVE -> ACTIVE).', 'product', 17, 0, NULL, '2026-09-12 10:39:09'),
(2461, 5, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product GEAR OIL (Status: ACTIVE -> ACTIVE).', 'product', 17, 0, NULL, '2026-09-12 10:39:09'),
(2462, 7, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product GEAR OIL (Status: ACTIVE -> ACTIVE).', 'product', 17, 0, NULL, '2026-09-12 10:39:09'),
(2463, 2, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product GEAR OIL (Status: ACTIVE -> ACTIVE).', 'product', 17, 0, NULL, '2026-09-12 10:39:09'),
(2464, 4, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-12 12:06:34'),
(2465, 16, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-12 12:06:34'),
(2466, 5, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-12 12:06:34'),
(2467, 6, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-12 12:06:34'),
(2468, 7, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-12 12:06:34'),
(2469, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO018 (Status: Completed).', 'job_order', 23, 0, NULL, '2026-09-14 23:26:57'),
(2470, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO018 (Status: Completed).', 'job_order', 23, 0, NULL, '2026-09-14 23:26:57'),
(2471, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO018 (Status: Completed).', 'job_order', 23, 0, NULL, '2026-09-14 23:26:57'),
(2472, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO018 (Status: Completed).', 'job_order', 23, 0, NULL, '2026-09-14 23:26:57'),
(2473, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO018 (Status: Completed).', 'job_order', 23, 0, NULL, '2026-09-14 23:26:57'),
(2474, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO018 (Status: Completed).', 'job_order', 23, 0, NULL, '2026-09-14 23:26:57'),
(2475, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO018 (Status: Completed).', 'job_order', 23, 0, NULL, '2026-09-14 23:26:57'),
(2476, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO019.', 'job_order', 24, 0, NULL, '2026-09-15 00:37:54'),
(2477, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO019.', 'job_order', 24, 0, NULL, '2026-09-15 00:37:54'),
(2478, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO019.', 'job_order', 24, 0, NULL, '2026-09-15 00:37:54'),
(2479, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO019.', 'job_order', 24, 0, NULL, '2026-09-15 00:37:54'),
(2480, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO019.', 'job_order', 24, 0, NULL, '2026-09-15 00:37:54'),
(2481, 9, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO019.', 'job_order', 24, 0, NULL, '2026-09-15 00:37:54'),
(2482, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO019.', 'job_order', 24, 0, NULL, '2026-09-15 00:37:54'),
(2483, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO019.', 'job_order', 24, 0, NULL, '2026-09-15 00:37:54'),
(2484, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Ongoing).', 'job_order', 24, 0, NULL, '2026-09-15 00:37:58'),
(2485, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Ongoing).', 'job_order', 24, 0, NULL, '2026-09-15 00:37:58'),
(2486, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Ongoing).', 'job_order', 24, 0, NULL, '2026-09-15 00:37:58'),
(2487, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Ongoing).', 'job_order', 24, 0, NULL, '2026-09-15 00:37:58'),
(2488, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Ongoing).', 'job_order', 24, 0, NULL, '2026-09-15 00:37:58'),
(2489, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Ongoing).', 'job_order', 24, 0, NULL, '2026-09-15 00:37:58'),
(2490, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Ongoing).', 'job_order', 24, 0, NULL, '2026-09-15 00:37:58'),
(2491, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Ongoing).', 'job_order', 24, 0, NULL, '2026-09-15 00:37:58'),
(2492, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO018 (Amount: ₱8,500.00).', 'job_order', 23, 0, NULL, '2026-09-15 00:44:01'),
(2493, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO018 (Amount: ₱8,500.00).', 'job_order', 23, 0, NULL, '2026-09-15 00:44:01'),
(2494, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO018 (Amount: ₱8,500.00).', 'job_order', 23, 0, NULL, '2026-09-15 00:44:01'),
(2495, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO018 (Amount: ₱8,500.00).', 'job_order', 23, 0, NULL, '2026-09-15 00:44:01'),
(2496, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO018 (Amount: ₱8,500.00).', 'job_order', 23, 0, NULL, '2026-09-15 00:44:01'),
(2497, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO018 (Amount: ₱8,500.00).', 'job_order', 23, 0, NULL, '2026-09-15 00:44:01'),
(2498, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO018 (Amount: ₱8,500.00).', 'job_order', 23, 0, NULL, '2026-09-15 00:44:01'),
(2499, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,830.00, Date: Sep 14, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-15 00:50:17'),
(2500, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,830.00, Date: Sep 14, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-15 00:50:17'),
(2501, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,830.00, Date: Sep 14, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-15 00:50:17'),
(2502, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,830.00, Date: Sep 14, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-15 00:50:17'),
(2503, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,830.00, Date: Sep 14, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-15 00:50:17'),
(2504, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,830.00, Date: Sep 14, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-15 00:50:17'),
(2505, 4, NULL, 'system', 'Print Template Updated', 'LOVELY JOYCE updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 01:16:24'),
(2506, 16, NULL, 'system', 'Print Template Updated', 'LOVELY JOYCE updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 01:16:24'),
(2507, 5, NULL, 'system', 'Print Template Updated', 'LOVELY JOYCE updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 01:16:24'),
(2508, 7, NULL, 'system', 'Print Template Updated', 'LOVELY JOYCE updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 01:16:24'),
(2509, 2, NULL, 'system', 'Print Template Updated', 'LOVELY JOYCE updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 01:16:24'),
(2510, 4, NULL, 'system', 'Print Template Updated', 'LOVELY JOYCE updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 01:16:48'),
(2511, 16, NULL, 'system', 'Print Template Updated', 'LOVELY JOYCE updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 01:16:48'),
(2512, 5, NULL, 'system', 'Print Template Updated', 'LOVELY JOYCE updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 01:16:48'),
(2513, 7, NULL, 'system', 'Print Template Updated', 'LOVELY JOYCE updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 01:16:48'),
(2514, 2, NULL, 'system', 'Print Template Updated', 'LOVELY JOYCE updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 01:16:48'),
(2515, 4, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 02:12:44'),
(2516, 16, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 02:12:44'),
(2517, 5, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 02:12:44'),
(2518, 6, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 02:12:44'),
(2519, 7, NULL, 'system', 'Print Template Updated', 'Dj Guingue Cortez updated print template for Autodok Prime Auto Services.', 'settings', NULL, 0, NULL, '2026-09-15 02:12:44'),
(2520, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Cancelled).', 'job_order', 24, 0, NULL, '2026-09-15 23:36:46'),
(2521, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Cancelled).', 'job_order', 24, 0, NULL, '2026-09-15 23:36:46'),
(2522, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Cancelled).', 'job_order', 24, 0, NULL, '2026-09-15 23:36:46'),
(2523, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Cancelled).', 'job_order', 24, 0, NULL, '2026-09-15 23:36:46'),
(2524, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Cancelled).', 'job_order', 24, 0, NULL, '2026-09-15 23:36:46'),
(2525, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Cancelled).', 'job_order', 24, 0, NULL, '2026-09-15 23:36:46'),
(2526, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Cancelled).', 'job_order', 24, 0, NULL, '2026-09-15 23:36:46'),
(2527, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO019 (Status: Cancelled).', 'job_order', 24, 0, NULL, '2026-09-15 23:36:46'),
(2528, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO020.', 'job_order', 25, 0, NULL, '2026-09-16 05:08:16'),
(2529, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO020.', 'job_order', 25, 0, NULL, '2026-09-16 05:08:16'),
(2530, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO020.', 'job_order', 25, 0, NULL, '2026-09-16 05:08:16'),
(2531, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO020.', 'job_order', 25, 0, NULL, '2026-09-16 05:08:16'),
(2532, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO020.', 'job_order', 25, 0, NULL, '2026-09-16 05:08:16'),
(2533, 10, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO020.', 'job_order', 25, 0, NULL, '2026-09-16 05:08:16'),
(2534, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO020.', 'job_order', 25, 0, NULL, '2026-09-16 05:08:16'),
(2535, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO020.', 'job_order', 25, 0, NULL, '2026-09-16 05:08:16'),
(2536, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Ongoing).', 'job_order', 25, 0, NULL, '2026-09-16 05:08:22'),
(2537, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Ongoing).', 'job_order', 25, 0, NULL, '2026-09-16 05:08:22'),
(2538, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Ongoing).', 'job_order', 25, 0, NULL, '2026-09-16 05:08:22'),
(2539, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Ongoing).', 'job_order', 25, 0, NULL, '2026-09-16 05:08:22'),
(2540, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Ongoing).', 'job_order', 25, 0, NULL, '2026-09-16 05:08:22'),
(2541, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Ongoing).', 'job_order', 25, 0, NULL, '2026-09-16 05:08:22'),
(2542, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Ongoing).', 'job_order', 25, 0, NULL, '2026-09-16 05:08:22'),
(2543, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Ongoing).', 'job_order', 25, 0, NULL, '2026-09-16 05:08:22'),
(2544, 4, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-30 (Quantity: -3).', 'inventory_transaction', 6, 0, NULL, '2026-09-16 06:07:53'),
(2545, 16, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-30 (Quantity: -3).', 'inventory_transaction', 6, 0, NULL, '2026-09-16 06:07:53'),
(2546, 5, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-30 (Quantity: -3).', 'inventory_transaction', 6, 0, NULL, '2026-09-16 06:07:53'),
(2547, 6, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-30 (Quantity: -3).', 'inventory_transaction', 6, 0, NULL, '2026-09-16 06:07:53'),
(2548, 7, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-30 (Quantity: -3).', 'inventory_transaction', 6, 0, NULL, '2026-09-16 06:07:53'),
(2549, 2, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ENGINE OIL 5W-30 (Quantity: -3).', 'inventory_transaction', 6, 0, NULL, '2026-09-16 06:07:53'),
(2550, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +2).', 'inventory_transaction', 13, 0, NULL, '2026-09-16 06:08:05'),
(2551, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +2).', 'inventory_transaction', 13, 0, NULL, '2026-09-16 06:08:05'),
(2552, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +2).', 'inventory_transaction', 13, 0, NULL, '2026-09-16 06:08:05'),
(2553, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +2).', 'inventory_transaction', 13, 0, NULL, '2026-09-16 06:08:05'),
(2554, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +2).', 'inventory_transaction', 13, 0, NULL, '2026-09-16 06:08:05'),
(2555, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +2).', 'inventory_transaction', 13, 0, NULL, '2026-09-16 06:08:05'),
(2556, 4, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT BLUE (Quantity: -2).', 'inventory_transaction', 19, 0, NULL, '2026-09-16 06:08:22'),
(2557, 16, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT BLUE (Quantity: -2).', 'inventory_transaction', 19, 0, NULL, '2026-09-16 06:08:22'),
(2558, 5, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT BLUE (Quantity: -2).', 'inventory_transaction', 19, 0, NULL, '2026-09-16 06:08:22'),
(2559, 6, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT BLUE (Quantity: -2).', 'inventory_transaction', 19, 0, NULL, '2026-09-16 06:08:22'),
(2560, 7, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT BLUE (Quantity: -2).', 'inventory_transaction', 19, 0, NULL, '2026-09-16 06:08:22'),
(2561, 2, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT BLUE (Quantity: -2).', 'inventory_transaction', 19, 0, NULL, '2026-09-16 06:08:22'),
(2562, 4, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT GREEN (Quantity: -3).', 'inventory_transaction', 20, 0, NULL, '2026-09-16 06:08:28'),
(2563, 16, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT GREEN (Quantity: -3).', 'inventory_transaction', 20, 0, NULL, '2026-09-16 06:08:28'),
(2564, 5, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT GREEN (Quantity: -3).', 'inventory_transaction', 20, 0, NULL, '2026-09-16 06:08:28'),
(2565, 6, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT GREEN (Quantity: -3).', 'inventory_transaction', 20, 0, NULL, '2026-09-16 06:08:28'),
(2566, 7, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT GREEN (Quantity: -3).', 'inventory_transaction', 20, 0, NULL, '2026-09-16 06:08:28'),
(2567, 2, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock COOLANT GREEN (Quantity: -3).', 'inventory_transaction', 20, 0, NULL, '2026-09-16 06:08:28'),
(2568, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ATF LV MV (STOCKS) (Quantity: +2).', 'inventory_transaction', 9, 0, NULL, '2026-09-16 06:08:50'),
(2569, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ATF LV MV (STOCKS) (Quantity: +2).', 'inventory_transaction', 9, 0, NULL, '2026-09-16 06:08:50'),
(2570, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ATF LV MV (STOCKS) (Quantity: +2).', 'inventory_transaction', 9, 0, NULL, '2026-09-16 06:08:50'),
(2571, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ATF LV MV (STOCKS) (Quantity: +2).', 'inventory_transaction', 9, 0, NULL, '2026-09-16 06:08:50'),
(2572, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ATF LV MV (STOCKS) (Quantity: +2).', 'inventory_transaction', 9, 0, NULL, '2026-09-16 06:08:50'),
(2573, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ATF LV MV (STOCKS) (Quantity: +2).', 'inventory_transaction', 9, 0, NULL, '2026-09-16 06:08:50'),
(2574, 4, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product PENETRATING OIL (Status: ACTIVE -> ACTIVE).', 'product', 15, 0, NULL, '2026-09-16 06:09:08'),
(2575, 16, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product PENETRATING OIL (Status: ACTIVE -> ACTIVE).', 'product', 15, 0, NULL, '2026-09-16 06:09:08'),
(2576, 5, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product PENETRATING OIL (Status: ACTIVE -> ACTIVE).', 'product', 15, 0, NULL, '2026-09-16 06:09:08'),
(2577, 7, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product PENETRATING OIL (Status: ACTIVE -> ACTIVE).', 'product', 15, 0, NULL, '2026-09-16 06:09:08'),
(2578, 2, NULL, 'system', 'Product Updated', 'LOVELY JOYCE updated product PENETRATING OIL (Status: ACTIVE -> ACTIVE).', 'product', 15, 0, NULL, '2026-09-16 06:09:08'),
(2579, 4, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock PENETRATING OIL (Quantity: -1).', 'inventory_transaction', 15, 0, NULL, '2026-09-16 06:09:18'),
(2580, 16, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock PENETRATING OIL (Quantity: -1).', 'inventory_transaction', 15, 0, NULL, '2026-09-16 06:09:18'),
(2581, 5, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock PENETRATING OIL (Quantity: -1).', 'inventory_transaction', 15, 0, NULL, '2026-09-16 06:09:18'),
(2582, 6, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock PENETRATING OIL (Quantity: -1).', 'inventory_transaction', 15, 0, NULL, '2026-09-16 06:09:18'),
(2583, 7, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock PENETRATING OIL (Quantity: -1).', 'inventory_transaction', 15, 0, NULL, '2026-09-16 06:09:18'),
(2584, 2, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock PENETRATING OIL (Quantity: -1).', 'inventory_transaction', 15, 0, NULL, '2026-09-16 06:09:18'),
(2585, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +2).', 'inventory_transaction', 12, 0, NULL, '2026-09-16 06:09:53'),
(2586, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +2).', 'inventory_transaction', 12, 0, NULL, '2026-09-16 06:09:53'),
(2587, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +2).', 'inventory_transaction', 12, 0, NULL, '2026-09-16 06:09:53'),
(2588, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +2).', 'inventory_transaction', 12, 0, NULL, '2026-09-16 06:09:53'),
(2589, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +2).', 'inventory_transaction', 12, 0, NULL, '2026-09-16 06:09:53'),
(2590, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock BRAKE CLEANER (Quantity: +2).', 'inventory_transaction', 12, 0, NULL, '2026-09-16 06:09:53'),
(2591, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Completed).', 'job_order', 25, 0, NULL, '2026-09-16 08:00:38'),
(2592, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Completed).', 'job_order', 25, 0, NULL, '2026-09-16 08:00:38'),
(2593, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Completed).', 'job_order', 25, 0, NULL, '2026-09-16 08:00:38'),
(2594, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Completed).', 'job_order', 25, 0, NULL, '2026-09-16 08:00:38'),
(2595, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Completed).', 'job_order', 25, 0, NULL, '2026-09-16 08:00:38'),
(2596, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Completed).', 'job_order', 25, 0, NULL, '2026-09-16 08:00:38'),
(2597, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Completed).', 'job_order', 25, 0, NULL, '2026-09-16 08:00:38'),
(2598, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO020 (Status: Completed).', 'job_order', 25, 0, NULL, '2026-09-16 08:00:38'),
(2599, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO021.', 'job_order', 26, 0, NULL, '2026-09-17 01:53:34'),
(2600, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO021.', 'job_order', 26, 0, NULL, '2026-09-17 01:53:34'),
(2601, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO021.', 'job_order', 26, 0, NULL, '2026-09-17 01:53:34'),
(2602, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO021.', 'job_order', 26, 0, NULL, '2026-09-17 01:53:34'),
(2603, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO021.', 'job_order', 26, 0, NULL, '2026-09-17 01:53:34'),
(2604, 9, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO021.', 'job_order', 26, 0, NULL, '2026-09-17 01:53:34'),
(2605, 13, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO021.', 'job_order', 26, 0, NULL, '2026-09-17 01:53:34'),
(2606, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO021.', 'job_order', 26, 0, NULL, '2026-09-17 01:53:34'),
(2607, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO021.', 'job_order', 26, 0, NULL, '2026-09-17 01:53:34'),
(2608, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Ongoing).', 'job_order', 26, 0, NULL, '2026-09-17 01:53:46'),
(2609, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Ongoing).', 'job_order', 26, 0, NULL, '2026-09-17 01:53:46'),
(2610, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Ongoing).', 'job_order', 26, 0, NULL, '2026-09-17 01:53:46'),
(2611, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Ongoing).', 'job_order', 26, 0, NULL, '2026-09-17 01:53:46'),
(2612, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Ongoing).', 'job_order', 26, 0, NULL, '2026-09-17 01:53:46'),
(2613, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Ongoing).', 'job_order', 26, 0, NULL, '2026-09-17 01:53:46'),
(2614, 13, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Ongoing).', 'job_order', 26, 0, NULL, '2026-09-17 01:53:46'),
(2615, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Ongoing).', 'job_order', 26, 0, NULL, '2026-09-17 01:53:46'),
(2616, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Ongoing).', 'job_order', 26, 0, NULL, '2026-09-17 01:53:46'),
(2617, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO022.', 'job_order', 27, 0, NULL, '2026-09-17 02:01:52'),
(2618, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO022.', 'job_order', 27, 0, NULL, '2026-09-17 02:01:52'),
(2619, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO022.', 'job_order', 27, 0, NULL, '2026-09-17 02:01:52'),
(2620, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO022.', 'job_order', 27, 0, NULL, '2026-09-17 02:01:52'),
(2621, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO022.', 'job_order', 27, 0, NULL, '2026-09-17 02:01:52'),
(2622, 10, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO022.', 'job_order', 27, 0, NULL, '2026-09-17 02:01:52'),
(2623, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO022.', 'job_order', 27, 0, NULL, '2026-09-17 02:01:52'),
(2624, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO022.', 'job_order', 27, 0, NULL, '2026-09-17 02:01:52'),
(2625, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Ongoing).', 'job_order', 27, 0, NULL, '2026-09-17 02:01:56'),
(2626, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Ongoing).', 'job_order', 27, 0, NULL, '2026-09-17 02:01:56'),
(2627, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Ongoing).', 'job_order', 27, 0, NULL, '2026-09-17 02:01:56'),
(2628, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Ongoing).', 'job_order', 27, 0, NULL, '2026-09-17 02:01:56'),
(2629, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Ongoing).', 'job_order', 27, 0, NULL, '2026-09-17 02:01:56'),
(2630, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Ongoing).', 'job_order', 27, 0, NULL, '2026-09-17 02:01:56'),
(2631, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Ongoing).', 'job_order', 27, 0, NULL, '2026-09-17 02:01:56'),
(2632, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Ongoing).', 'job_order', 27, 0, NULL, '2026-09-17 02:01:56'),
(2633, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Completed).', 'job_order', 27, 0, NULL, '2026-09-17 08:57:49'),
(2634, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Completed).', 'job_order', 27, 0, NULL, '2026-09-17 08:57:49'),
(2635, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Completed).', 'job_order', 27, 0, NULL, '2026-09-17 08:57:49'),
(2636, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Completed).', 'job_order', 27, 0, NULL, '2026-09-17 08:57:49'),
(2637, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Completed).', 'job_order', 27, 0, NULL, '2026-09-17 08:57:49'),
(2638, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Completed).', 'job_order', 27, 0, NULL, '2026-09-17 08:57:49'),
(2639, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Completed).', 'job_order', 27, 0, NULL, '2026-09-17 08:57:49'),
(2640, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO022 (Status: Completed).', 'job_order', 27, 0, NULL, '2026-09-17 08:57:49'),
(2641, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Completed).', 'job_order', 26, 0, NULL, '2026-09-18 01:20:09'),
(2642, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Completed).', 'job_order', 26, 0, NULL, '2026-09-18 01:20:09'),
(2643, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Completed).', 'job_order', 26, 0, NULL, '2026-09-18 01:20:09'),
(2644, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Completed).', 'job_order', 26, 0, NULL, '2026-09-18 01:20:09'),
(2645, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Completed).', 'job_order', 26, 0, NULL, '2026-09-18 01:20:09'),
(2646, 9, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Completed).', 'job_order', 26, 0, NULL, '2026-09-18 01:20:09'),
(2647, 13, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Completed).', 'job_order', 26, 0, NULL, '2026-09-18 01:20:09'),
(2648, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Completed).', 'job_order', 26, 0, NULL, '2026-09-18 01:20:09'),
(2649, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO021 (Status: Completed).', 'job_order', 26, 0, NULL, '2026-09-18 01:20:09'),
(2650, 4, NULL, 'payment', 'Job Order Paid', 'Aian P. Alderite marked as paid job order #JO021 (Amount: ₱4,000.00).', 'job_order', 26, 0, NULL, '2026-09-18 01:58:50'),
(2651, 16, NULL, 'payment', 'Job Order Paid', 'Aian P. Alderite marked as paid job order #JO021 (Amount: ₱4,000.00).', 'job_order', 26, 0, NULL, '2026-09-18 01:58:50'),
(2652, 5, NULL, 'payment', 'Job Order Paid', 'Aian P. Alderite marked as paid job order #JO021 (Amount: ₱4,000.00).', 'job_order', 26, 0, NULL, '2026-09-18 01:58:50'),
(2653, 6, NULL, 'payment', 'Job Order Paid', 'Aian P. Alderite marked as paid job order #JO021 (Amount: ₱4,000.00).', 'job_order', 26, 0, NULL, '2026-09-18 01:58:50'),
(2654, 7, NULL, 'payment', 'Job Order Paid', 'Aian P. Alderite marked as paid job order #JO021 (Amount: ₱4,000.00).', 'job_order', 26, 0, NULL, '2026-09-18 01:58:50'),
(2655, 8, NULL, 'payment', 'Job Order Paid', 'Aian P. Alderite marked as paid job order #JO021 (Amount: ₱4,000.00).', 'job_order', 26, 0, NULL, '2026-09-18 01:58:50'),
(2656, 2, NULL, 'payment', 'Job Order Paid', 'Aian P. Alderite marked as paid job order #JO021 (Amount: ₱4,000.00).', 'job_order', 26, 0, NULL, '2026-09-18 01:58:50'),
(2657, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO023.', 'job_order', 28, 0, NULL, '2026-09-20 23:23:15'),
(2658, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO023.', 'job_order', 28, 0, NULL, '2026-09-20 23:23:15'),
(2659, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO023.', 'job_order', 28, 0, NULL, '2026-09-20 23:23:15'),
(2660, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO023.', 'job_order', 28, 0, NULL, '2026-09-20 23:23:15'),
(2661, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO023.', 'job_order', 28, 0, NULL, '2026-09-20 23:23:15'),
(2662, 10, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO023.', 'job_order', 28, 0, NULL, '2026-09-20 23:23:15'),
(2663, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO023.', 'job_order', 28, 0, NULL, '2026-09-20 23:23:15'),
(2664, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO023.', 'job_order', 28, 0, NULL, '2026-09-20 23:23:15'),
(2665, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO024.', 'job_order', 29, 0, NULL, '2026-09-20 23:23:15'),
(2666, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO024.', 'job_order', 29, 0, NULL, '2026-09-20 23:23:15'),
(2667, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO024.', 'job_order', 29, 0, NULL, '2026-09-20 23:23:15'),
(2668, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO024.', 'job_order', 29, 0, NULL, '2026-09-20 23:23:15'),
(2669, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO024.', 'job_order', 29, 0, NULL, '2026-09-20 23:23:15'),
(2670, 10, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO024.', 'job_order', 29, 0, NULL, '2026-09-20 23:23:15'),
(2671, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO024.', 'job_order', 29, 0, NULL, '2026-09-20 23:23:15'),
(2672, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO024.', 'job_order', 29, 0, NULL, '2026-09-20 23:23:15'),
(2673, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Ongoing).', 'job_order', 29, 0, NULL, '2026-09-20 23:25:01'),
(2674, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Ongoing).', 'job_order', 29, 0, NULL, '2026-09-20 23:25:01'),
(2675, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Ongoing).', 'job_order', 29, 0, NULL, '2026-09-20 23:25:01'),
(2676, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Ongoing).', 'job_order', 29, 0, NULL, '2026-09-20 23:25:01'),
(2677, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Ongoing).', 'job_order', 29, 0, NULL, '2026-09-20 23:25:01'),
(2678, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Ongoing).', 'job_order', 29, 0, NULL, '2026-09-20 23:25:01'),
(2679, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Ongoing).', 'job_order', 29, 0, NULL, '2026-09-20 23:25:01'),
(2680, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Ongoing).', 'job_order', 29, 0, NULL, '2026-09-20 23:25:01'),
(2681, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-21 00:45:15'),
(2682, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-21 00:45:15'),
(2683, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-21 00:45:15'),
(2684, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-21 00:45:15'),
(2685, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-21 00:45:15'),
(2686, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-21 00:45:15'),
(2687, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-21 00:45:15'),
(2688, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-21 00:45:15'),
(2689, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO025.', 'job_order', 30, 0, NULL, '2026-09-21 07:21:04'),
(2690, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO025.', 'job_order', 30, 0, NULL, '2026-09-21 07:21:04'),
(2691, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO025.', 'job_order', 30, 0, NULL, '2026-09-21 07:21:04'),
(2692, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO025.', 'job_order', 30, 0, NULL, '2026-09-21 07:21:05'),
(2693, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO025.', 'job_order', 30, 0, NULL, '2026-09-21 07:21:05'),
(2694, 10, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO025.', 'job_order', 30, 0, NULL, '2026-09-21 07:21:05'),
(2695, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO025.', 'job_order', 30, 0, NULL, '2026-09-21 07:21:05'),
(2696, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO025.', 'job_order', 30, 0, NULL, '2026-09-21 07:21:05'),
(2697, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO025 (Status: Ongoing).', 'job_order', 30, 0, NULL, '2026-09-21 07:21:10'),
(2698, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO025 (Status: Ongoing).', 'job_order', 30, 0, NULL, '2026-09-21 07:21:10'),
(2699, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO025 (Status: Ongoing).', 'job_order', 30, 0, NULL, '2026-09-21 07:21:10'),
(2700, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO025 (Status: Ongoing).', 'job_order', 30, 0, NULL, '2026-09-21 07:21:10'),
(2701, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO025 (Status: Ongoing).', 'job_order', 30, 0, NULL, '2026-09-21 07:21:10'),
(2702, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO025 (Status: Ongoing).', 'job_order', 30, 0, NULL, '2026-09-21 07:21:10'),
(2703, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO025 (Status: Ongoing).', 'job_order', 30, 0, NULL, '2026-09-21 07:21:10'),
(2704, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO025 (Status: Ongoing).', 'job_order', 30, 0, NULL, '2026-09-21 07:21:10'),
(2705, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO025 (Status: Completed).', 'job_order', 30, 0, NULL, '2026-09-21 07:53:51'),
(2706, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO025 (Status: Completed).', 'job_order', 30, 0, NULL, '2026-09-21 07:53:51'),
(2707, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO025 (Status: Completed).', 'job_order', 30, 0, NULL, '2026-09-21 07:53:51'),
(2708, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO025 (Status: Completed).', 'job_order', 30, 0, NULL, '2026-09-21 07:53:51'),
(2709, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO025 (Status: Completed).', 'job_order', 30, 0, NULL, '2026-09-21 07:53:51'),
(2710, 10, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO025 (Status: Completed).', 'job_order', 30, 0, NULL, '2026-09-21 07:53:51'),
(2711, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO025 (Status: Completed).', 'job_order', 30, 0, NULL, '2026-09-21 07:53:51'),
(2712, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO025 (Status: Completed).', 'job_order', 30, 0, NULL, '2026-09-21 07:53:51'),
(2713, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO025 (Amount: ₱1,350.00).', 'job_order', 30, 0, NULL, '2026-09-21 08:00:28'),
(2714, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO025 (Amount: ₱1,350.00).', 'job_order', 30, 0, NULL, '2026-09-21 08:00:28'),
(2715, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO025 (Amount: ₱1,350.00).', 'job_order', 30, 0, NULL, '2026-09-21 08:00:28'),
(2716, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO025 (Amount: ₱1,350.00).', 'job_order', 30, 0, NULL, '2026-09-21 08:00:28'),
(2717, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO025 (Amount: ₱1,350.00).', 'job_order', 30, 0, NULL, '2026-09-21 08:00:28'),
(2718, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO025 (Amount: ₱1,350.00).', 'job_order', 30, 0, NULL, '2026-09-21 08:00:28'),
(2719, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO025 (Amount: ₱1,350.00).', 'job_order', 30, 0, NULL, '2026-09-21 08:00:28'),
(2720, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Completed).', 'job_order', 29, 0, NULL, '2026-09-21 09:23:10'),
(2721, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Completed).', 'job_order', 29, 0, NULL, '2026-09-21 09:23:10'),
(2722, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Completed).', 'job_order', 29, 0, NULL, '2026-09-21 09:23:10'),
(2723, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Completed).', 'job_order', 29, 0, NULL, '2026-09-21 09:23:10'),
(2724, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Completed).', 'job_order', 29, 0, NULL, '2026-09-21 09:23:10'),
(2725, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Completed).', 'job_order', 29, 0, NULL, '2026-09-21 09:23:10'),
(2726, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Completed).', 'job_order', 29, 0, NULL, '2026-09-21 09:23:10'),
(2727, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO024 (Status: Completed).', 'job_order', 29, 0, NULL, '2026-09-21 09:23:10'),
(2728, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱389.00, Date: Sep 21, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-21 09:50:26'),
(2729, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱389.00, Date: Sep 21, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-21 09:50:26'),
(2730, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱389.00, Date: Sep 21, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-21 09:50:26'),
(2731, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱389.00, Date: Sep 21, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-21 09:50:26'),
(2732, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱389.00, Date: Sep 21, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-21 09:50:26'),
(2733, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱389.00, Date: Sep 21, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-21 09:50:26'),
(2734, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO024 (Amount: ₱5,500.00).', 'job_order', 29, 0, NULL, '2026-09-21 09:51:02'),
(2735, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO024 (Amount: ₱5,500.00).', 'job_order', 29, 0, NULL, '2026-09-21 09:51:02'),
(2736, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO024 (Amount: ₱5,500.00).', 'job_order', 29, 0, NULL, '2026-09-21 09:51:02'),
(2737, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO024 (Amount: ₱5,500.00).', 'job_order', 29, 0, NULL, '2026-09-21 09:51:02'),
(2738, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO024 (Amount: ₱5,500.00).', 'job_order', 29, 0, NULL, '2026-09-21 09:51:02'),
(2739, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO024 (Amount: ₱5,500.00).', 'job_order', 29, 0, NULL, '2026-09-21 09:51:02'),
(2740, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO024 (Amount: ₱5,500.00).', 'job_order', 29, 0, NULL, '2026-09-21 09:51:02'),
(2741, 4, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO026.', 'job_order', 31, 0, NULL, '2026-09-23 07:12:23'),
(2742, 5, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO026.', 'job_order', 31, 0, NULL, '2026-09-23 07:12:23'),
(2743, 6, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO026.', 'job_order', 31, 0, NULL, '2026-09-23 07:12:23'),
(2744, 7, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO026.', 'job_order', 31, 0, NULL, '2026-09-23 07:12:23'),
(2745, 8, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO026.', 'job_order', 31, 0, NULL, '2026-09-23 07:12:23'),
(2746, 10, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO026.', 'job_order', 31, 0, NULL, '2026-09-23 07:12:23'),
(2747, 16, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO026.', 'job_order', 31, 0, NULL, '2026-09-23 07:12:23'),
(2748, 2, NULL, 'job_status', 'New Job Order Created', 'Aian P. Alderite created job order #JO026.', 'job_order', 31, 0, NULL, '2026-09-23 07:12:23');
INSERT INTO `notifications` (`id`, `user_id`, `staff_id`, `type`, `title`, `message`, `reference_type`, `reference_id`, `is_read`, `read_at`, `created_at`) VALUES
(2749, 4, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO026 (Status: Ongoing).', 'job_order', 31, 0, NULL, '2026-09-23 07:12:31'),
(2750, 5, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO026 (Status: Ongoing).', 'job_order', 31, 0, NULL, '2026-09-23 07:12:31'),
(2751, 6, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO026 (Status: Ongoing).', 'job_order', 31, 0, NULL, '2026-09-23 07:12:31'),
(2752, 7, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO026 (Status: Ongoing).', 'job_order', 31, 0, NULL, '2026-09-23 07:12:31'),
(2753, 8, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO026 (Status: Ongoing).', 'job_order', 31, 0, NULL, '2026-09-23 07:12:31'),
(2754, 10, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO026 (Status: Ongoing).', 'job_order', 31, 0, NULL, '2026-09-23 07:12:31'),
(2755, 16, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO026 (Status: Ongoing).', 'job_order', 31, 0, NULL, '2026-09-23 07:12:31'),
(2756, 2, NULL, 'job_status', 'Job Order Status Updated', 'Aian P. Alderite updated job order #JO026 (Status: Ongoing).', 'job_order', 31, 0, NULL, '2026-09-23 07:12:31'),
(2757, 4, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ATF LV MV (STOCKS) (Quantity: -1).', 'inventory_transaction', 9, 0, NULL, '2026-09-23 08:32:55'),
(2758, 16, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ATF LV MV (STOCKS) (Quantity: -1).', 'inventory_transaction', 9, 0, NULL, '2026-09-23 08:32:55'),
(2759, 5, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ATF LV MV (STOCKS) (Quantity: -1).', 'inventory_transaction', 9, 0, NULL, '2026-09-23 08:32:55'),
(2760, 6, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ATF LV MV (STOCKS) (Quantity: -1).', 'inventory_transaction', 9, 0, NULL, '2026-09-23 08:32:55'),
(2761, 7, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ATF LV MV (STOCKS) (Quantity: -1).', 'inventory_transaction', 9, 0, NULL, '2026-09-23 08:32:55'),
(2762, 2, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock ATF LV MV (STOCKS) (Quantity: -1).', 'inventory_transaction', 9, 0, NULL, '2026-09-23 08:32:55'),
(2763, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱384.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:33:49'),
(2764, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱384.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:33:49'),
(2765, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱384.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:33:49'),
(2766, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱384.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:33:49'),
(2767, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱384.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:33:49'),
(2768, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱384.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:33:49'),
(2769, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:34:21'),
(2770, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:34:21'),
(2771, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:34:21'),
(2772, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:34:21'),
(2773, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:34:21'),
(2774, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱250.00, Date: Sep 23, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-23 08:34:21'),
(2775, 4, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO027.', 'job_order', 32, 0, NULL, '2026-09-24 03:24:11'),
(2776, 5, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO027.', 'job_order', 32, 0, NULL, '2026-09-24 03:24:11'),
(2777, 6, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO027.', 'job_order', 32, 0, NULL, '2026-09-24 03:24:11'),
(2778, 7, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO027.', 'job_order', 32, 0, NULL, '2026-09-24 03:24:11'),
(2779, 8, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO027.', 'job_order', 32, 0, NULL, '2026-09-24 03:24:11'),
(2780, 9, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO027.', 'job_order', 32, 0, NULL, '2026-09-24 03:24:11'),
(2781, 16, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO027.', 'job_order', 32, 0, NULL, '2026-09-24 03:24:11'),
(2782, 2, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO027.', 'job_order', 32, 0, NULL, '2026-09-24 03:24:11'),
(2783, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO027 (Status: Ongoing).', 'job_order', 32, 0, NULL, '2026-09-24 03:24:17'),
(2784, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO027 (Status: Ongoing).', 'job_order', 32, 0, NULL, '2026-09-24 03:24:17'),
(2785, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO027 (Status: Ongoing).', 'job_order', 32, 0, NULL, '2026-09-24 03:24:17'),
(2786, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO027 (Status: Ongoing).', 'job_order', 32, 0, NULL, '2026-09-24 03:24:17'),
(2787, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO027 (Status: Ongoing).', 'job_order', 32, 0, NULL, '2026-09-24 03:24:17'),
(2788, 9, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO027 (Status: Ongoing).', 'job_order', 32, 0, NULL, '2026-09-24 03:24:17'),
(2789, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO027 (Status: Ongoing).', 'job_order', 32, 0, NULL, '2026-09-24 03:24:17'),
(2790, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO027 (Status: Ongoing).', 'job_order', 32, 0, NULL, '2026-09-24 03:24:17'),
(2791, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO026 (Status: Completed).', 'job_order', 31, 0, NULL, '2026-09-24 05:17:37'),
(2792, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO026 (Status: Completed).', 'job_order', 31, 0, NULL, '2026-09-24 05:17:37'),
(2793, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO026 (Status: Completed).', 'job_order', 31, 0, NULL, '2026-09-24 05:17:37'),
(2794, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO026 (Status: Completed).', 'job_order', 31, 0, NULL, '2026-09-24 05:17:37'),
(2795, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO026 (Status: Completed).', 'job_order', 31, 0, NULL, '2026-09-24 05:17:37'),
(2796, 10, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO026 (Status: Completed).', 'job_order', 31, 0, NULL, '2026-09-24 05:17:37'),
(2797, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO026 (Status: Completed).', 'job_order', 31, 0, NULL, '2026-09-24 05:17:37'),
(2798, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO026 (Status: Completed).', 'job_order', 31, 0, NULL, '2026-09-24 05:17:37'),
(2799, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Released).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:45'),
(2800, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Released).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:45'),
(2801, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Released).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:45'),
(2802, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Released).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:45'),
(2803, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Released).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:45'),
(2804, 10, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Released).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:45'),
(2805, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Released).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:45'),
(2806, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Released).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:45'),
(2807, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:48'),
(2808, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:48'),
(2809, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:48'),
(2810, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:48'),
(2811, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:48'),
(2812, 10, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:48'),
(2813, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:48'),
(2814, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO023 (Status: Cancelled).', 'job_order', 28, 0, NULL, '2026-09-24 05:23:48'),
(2815, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO026 (Amount: ₱1,200.00).', 'job_order', 31, 0, NULL, '2026-09-24 06:54:26'),
(2816, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO026 (Amount: ₱1,200.00).', 'job_order', 31, 0, NULL, '2026-09-24 06:54:26'),
(2817, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO026 (Amount: ₱1,200.00).', 'job_order', 31, 0, NULL, '2026-09-24 06:54:26'),
(2818, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO026 (Amount: ₱1,200.00).', 'job_order', 31, 0, NULL, '2026-09-24 06:54:26'),
(2819, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO026 (Amount: ₱1,200.00).', 'job_order', 31, 0, NULL, '2026-09-24 06:54:26'),
(2820, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO026 (Amount: ₱1,200.00).', 'job_order', 31, 0, NULL, '2026-09-24 06:54:26'),
(2821, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO026 (Amount: ₱1,200.00).', 'job_order', 31, 0, NULL, '2026-09-24 06:54:26'),
(2822, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱290.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 07:04:14'),
(2823, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱290.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 07:04:14'),
(2824, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱290.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 07:04:14'),
(2825, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱290.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 07:04:14'),
(2826, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱290.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 07:04:14'),
(2827, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱290.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 07:04:14'),
(2828, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱790.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 09:33:29'),
(2829, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱790.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 09:33:29'),
(2830, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱790.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 09:33:29'),
(2831, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱790.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 09:33:29'),
(2832, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱790.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 09:33:29'),
(2833, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱790.00, Date: Sep 24, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-24 09:33:29'),
(2834, 4, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +24).', 'inventory_transaction', 13, 0, NULL, '2026-09-26 11:07:27'),
(2835, 16, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +24).', 'inventory_transaction', 13, 0, NULL, '2026-09-26 11:07:27'),
(2836, 5, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +24).', 'inventory_transaction', 13, 0, NULL, '2026-09-26 11:07:27'),
(2837, 6, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +24).', 'inventory_transaction', 13, 0, NULL, '2026-09-26 11:07:27'),
(2838, 7, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +24).', 'inventory_transaction', 13, 0, NULL, '2026-09-26 11:07:27'),
(2839, 2, NULL, 'system', 'Inventory Stock In', 'LOVELY JOYCE added stock ENGINE OIL 5W-40 (Quantity: +24).', 'inventory_transaction', 13, 0, NULL, '2026-09-26 11:07:27'),
(2840, 4, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 415 (Quantity: -6).', 'inventory_transaction', 7, 0, NULL, '2026-09-26 11:08:29'),
(2841, 16, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 415 (Quantity: -6).', 'inventory_transaction', 7, 0, NULL, '2026-09-26 11:08:29'),
(2842, 5, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 415 (Quantity: -6).', 'inventory_transaction', 7, 0, NULL, '2026-09-26 11:08:29'),
(2843, 6, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 415 (Quantity: -6).', 'inventory_transaction', 7, 0, NULL, '2026-09-26 11:08:29'),
(2844, 7, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 415 (Quantity: -6).', 'inventory_transaction', 7, 0, NULL, '2026-09-26 11:08:29'),
(2845, 2, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 415 (Quantity: -6).', 'inventory_transaction', 7, 0, NULL, '2026-09-26 11:08:29'),
(2846, 4, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FITER 110 (Quantity: -4).', 'inventory_transaction', 8, 0, NULL, '2026-09-26 11:08:52'),
(2847, 16, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FITER 110 (Quantity: -4).', 'inventory_transaction', 8, 0, NULL, '2026-09-26 11:08:52'),
(2848, 5, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FITER 110 (Quantity: -4).', 'inventory_transaction', 8, 0, NULL, '2026-09-26 11:08:52'),
(2849, 6, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FITER 110 (Quantity: -4).', 'inventory_transaction', 8, 0, NULL, '2026-09-26 11:08:52'),
(2850, 7, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FITER 110 (Quantity: -4).', 'inventory_transaction', 8, 0, NULL, '2026-09-26 11:08:52'),
(2851, 2, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FITER 110 (Quantity: -4).', 'inventory_transaction', 8, 0, NULL, '2026-09-26 11:08:52'),
(2852, 4, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 111 (Quantity: -2).', 'inventory_transaction', 10, 0, NULL, '2026-09-26 11:09:54'),
(2853, 16, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 111 (Quantity: -2).', 'inventory_transaction', 10, 0, NULL, '2026-09-26 11:09:54'),
(2854, 5, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 111 (Quantity: -2).', 'inventory_transaction', 10, 0, NULL, '2026-09-26 11:09:54'),
(2855, 6, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 111 (Quantity: -2).', 'inventory_transaction', 10, 0, NULL, '2026-09-26 11:09:54'),
(2856, 7, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 111 (Quantity: -2).', 'inventory_transaction', 10, 0, NULL, '2026-09-26 11:09:54'),
(2857, 2, NULL, 'system', 'Inventory Stock Out', 'LOVELY JOYCE deducted stock OIL FILTER 111 (Quantity: -2).', 'inventory_transaction', 10, 0, NULL, '2026-09-26 11:09:54'),
(2858, 4, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO028.', 'job_order', 33, 0, NULL, '2026-09-26 11:33:21'),
(2859, 5, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO028.', 'job_order', 33, 0, NULL, '2026-09-26 11:33:21'),
(2860, 6, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO028.', 'job_order', 33, 0, NULL, '2026-09-26 11:33:21'),
(2861, 7, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO028.', 'job_order', 33, 0, NULL, '2026-09-26 11:33:21'),
(2862, 8, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO028.', 'job_order', 33, 0, NULL, '2026-09-26 11:33:21'),
(2863, 11, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO028.', 'job_order', 33, 0, NULL, '2026-09-26 11:33:21'),
(2864, 16, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO028.', 'job_order', 33, 0, NULL, '2026-09-26 11:33:21'),
(2865, 2, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO028.', 'job_order', 33, 0, NULL, '2026-09-26 11:33:21'),
(2866, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO028 (Amount: ₱8,000.00).', 'job_order', 33, 0, NULL, '2026-09-26 11:37:55'),
(2867, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO028 (Amount: ₱8,000.00).', 'job_order', 33, 0, NULL, '2026-09-26 11:37:55'),
(2868, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO028 (Amount: ₱8,000.00).', 'job_order', 33, 0, NULL, '2026-09-26 11:37:55'),
(2869, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO028 (Amount: ₱8,000.00).', 'job_order', 33, 0, NULL, '2026-09-26 11:37:55'),
(2870, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO028 (Amount: ₱8,000.00).', 'job_order', 33, 0, NULL, '2026-09-26 11:37:55'),
(2871, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO028 (Amount: ₱8,000.00).', 'job_order', 33, 0, NULL, '2026-09-26 11:37:55'),
(2872, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO028 (Amount: ₱8,000.00).', 'job_order', 33, 0, NULL, '2026-09-26 11:37:55'),
(2873, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO028 (Status: Completed).', 'job_order', 33, 0, NULL, '2026-09-26 11:38:03'),
(2874, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO028 (Status: Completed).', 'job_order', 33, 0, NULL, '2026-09-26 11:38:03'),
(2875, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO028 (Status: Completed).', 'job_order', 33, 0, NULL, '2026-09-26 11:38:03'),
(2876, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO028 (Status: Completed).', 'job_order', 33, 0, NULL, '2026-09-26 11:38:03'),
(2877, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO028 (Status: Completed).', 'job_order', 33, 0, NULL, '2026-09-26 11:38:03'),
(2878, 11, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO028 (Status: Completed).', 'job_order', 33, 0, NULL, '2026-09-26 11:38:03'),
(2879, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO028 (Status: Completed).', 'job_order', 33, 0, NULL, '2026-09-26 11:38:03'),
(2880, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO028 (Status: Completed).', 'job_order', 33, 0, NULL, '2026-09-26 11:38:03'),
(2881, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱995.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:39:25'),
(2882, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱995.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:39:25'),
(2883, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱995.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:39:25'),
(2884, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱995.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:39:25'),
(2885, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱995.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:39:25'),
(2886, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱995.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:39:25'),
(2887, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,720.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:40:41'),
(2888, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,720.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:40:41'),
(2889, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,720.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:40:41'),
(2890, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,720.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:40:41'),
(2891, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,720.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:40:41'),
(2892, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱1,720.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 11:40:41'),
(2893, 4, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO029.', 'job_order', 34, 0, NULL, '2026-09-26 11:47:58'),
(2894, 5, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO029.', 'job_order', 34, 0, NULL, '2026-09-26 11:47:58'),
(2895, 6, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO029.', 'job_order', 34, 0, NULL, '2026-09-26 11:47:58'),
(2896, 7, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO029.', 'job_order', 34, 0, NULL, '2026-09-26 11:47:58'),
(2897, 8, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO029.', 'job_order', 34, 0, NULL, '2026-09-26 11:47:58'),
(2898, 16, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO029.', 'job_order', 34, 0, NULL, '2026-09-26 11:47:58'),
(2899, 2, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO029.', 'job_order', 34, 0, NULL, '2026-09-26 11:47:58'),
(2900, 4, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO029 (Status: Completed).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:11'),
(2901, 5, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO029 (Status: Completed).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:11'),
(2902, 6, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO029 (Status: Completed).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:11'),
(2903, 7, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO029 (Status: Completed).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:11'),
(2904, 8, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO029 (Status: Completed).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:11'),
(2905, 16, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO029 (Status: Completed).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:11'),
(2906, 2, NULL, 'job_status', 'Job Order Status Updated', 'LOVELY JOYCE updated job order #JO029 (Status: Completed).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:11'),
(2907, 4, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO029 (Amount: ₱5,550.00).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:27'),
(2908, 16, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO029 (Amount: ₱5,550.00).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:27'),
(2909, 5, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO029 (Amount: ₱5,550.00).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:27'),
(2910, 6, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO029 (Amount: ₱5,550.00).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:27'),
(2911, 7, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO029 (Amount: ₱5,550.00).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:27'),
(2912, 8, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO029 (Amount: ₱5,550.00).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:27'),
(2913, 2, NULL, 'payment', 'Job Order Paid', 'LOVELY JOYCE marked as paid job order #JO029 (Amount: ₱5,550.00).', 'job_order', 34, 0, NULL, '2026-09-26 11:48:27'),
(2914, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:10:52'),
(2915, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:10:52'),
(2916, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:10:52'),
(2917, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:10:52'),
(2918, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:10:52'),
(2919, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱200.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:10:52'),
(2920, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱28,253.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:18'),
(2921, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱28,253.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:18'),
(2922, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱28,253.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:18'),
(2923, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱28,253.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:18'),
(2924, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱28,253.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:18'),
(2925, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱28,253.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:18'),
(2926, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,185.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:54'),
(2927, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,185.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:54'),
(2928, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,185.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:54'),
(2929, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,185.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:54'),
(2930, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,185.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:54'),
(2931, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱5,185.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:21:54'),
(2932, 4, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱46.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:22:59'),
(2933, 16, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱46.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:22:59'),
(2934, 5, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱46.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:22:59'),
(2935, 6, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱46.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:22:59'),
(2936, 7, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱46.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:22:59'),
(2937, 2, NULL, 'system', 'Expense Added', 'LOVELY JOYCE added an expense (Amount: ₱46.00, Date: Sep 26, 2026).', 'report_expense', NULL, 0, NULL, '2026-09-26 12:22:59'),
(2938, 4, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO030.', 'job_order', 35, 0, NULL, '2026-09-28 03:39:50'),
(2939, 5, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO030.', 'job_order', 35, 0, NULL, '2026-09-28 03:39:50'),
(2940, 6, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO030.', 'job_order', 35, 0, NULL, '2026-09-28 03:39:50'),
(2941, 7, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO030.', 'job_order', 35, 0, NULL, '2026-09-28 03:39:50'),
(2942, 8, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO030.', 'job_order', 35, 0, NULL, '2026-09-28 03:39:50'),
(2943, 16, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO030.', 'job_order', 35, 0, NULL, '2026-09-28 03:39:50'),
(2944, 2, NULL, 'job_status', 'New Job Order Created', 'LOVELY JOYCE created job order #JO030.', 'job_order', 35, 0, NULL, '2026-09-28 03:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `job_order_id` int(11) NOT NULL,
  `payment_date` datetime NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','bank_transfer','gcash','paymaya') NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `permission_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock_level` int(11) DEFAULT 10,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_code`, `product_name`, `category_id`, `brand_id`, `unit_id`, `description`, `supplier_id`, `supplier`, `cost_price`, `selling_price`, `quantity`, `min_stock_level`, `status`, `created_at`, `updated_at`) VALUES
(2, 'PRD01', 'FRONT HUB BEARING (MIRAGE)', NULL, NULL, 1, '', NULL, NULL, 0.00, 0.00, 1, 10, 'active', '2026-08-13 08:52:32', '2026-08-17 02:38:23'),
(3, 'PRD02', 'RELAY', NULL, NULL, NULL, '', NULL, NULL, 120.00, 0.00, 20, 10, 'active', '2026-08-13 08:53:18', '2026-08-13 08:53:18'),
(4, 'PRD03', 'AIR FILTER (TRANSFORMER)', 3, NULL, 1, '', NULL, NULL, 0.00, 0.00, 1, 10, 'active', '2026-08-13 08:53:50', '2026-09-12 07:06:31'),
(5, 'PRD04', 'AIR FILTER (MULTI-VEHICLE)', 3, NULL, 1, 'MIRAGE', NULL, NULL, 230.00, 1800.00, 1, 10, 'active', '2026-08-13 08:54:29', '2026-09-12 10:06:24'),
(6, 'PRD05', 'ENGINE OIL 5W-30', 1, NULL, 8, '', NULL, NULL, 420.00, 600.00, 3, 10, 'active', '2026-08-13 08:55:03', '2026-09-16 06:07:53'),
(7, 'PRD06', 'OIL FILTER 415', 2, NULL, 1, '', NULL, NULL, 140.00, 500.00, 4, 10, 'active', '2026-08-13 08:56:04', '2026-09-26 11:08:29'),
(8, 'PRD07', 'OIL FITER 110', 2, NULL, 1, '', NULL, NULL, 85.00, 500.00, 6, 10, 'active', '2026-08-13 08:56:47', '2026-09-26 11:08:52'),
(9, 'PRD08', 'ATF LV MV (STOCKS)', 9, NULL, 8, '', NULL, NULL, 388.00, 950.00, 2, 10, 'active', '2026-08-13 08:57:11', '2026-09-23 08:32:55'),
(10, 'PRD09', 'OIL FILTER 111', 2, NULL, 1, '', NULL, NULL, 115.00, 500.00, 4, 10, 'active', '2026-08-13 08:57:31', '2026-09-26 11:48:08'),
(11, 'PRD10', 'PETRON ATF SAE-20', NULL, NULL, NULL, '', NULL, NULL, 0.00, 0.00, 20, 10, 'inactive', '2026-08-13 08:57:52', '2026-08-17 02:51:23'),
(12, 'PRD11', 'BRAKE CLEANER', 5, NULL, 7, '', NULL, NULL, 200.00, 450.00, 22, 10, 'active', '2026-08-13 09:00:17', '2026-09-26 11:38:03'),
(13, 'PRD12', 'ENGINE OIL 5W-40', 1, NULL, 8, '', NULL, NULL, 420.00, 600.00, 24, 10, 'active', '2026-08-13 09:06:02', '2026-09-26 11:48:08'),
(14, 'PRD13', 'REAR HUB BEARING (MIRAGE)', NULL, NULL, 1, '', NULL, NULL, 950.00, 0.00, 2, 10, 'active', '2026-08-13 09:11:27', '2026-08-17 02:52:34'),
(15, 'PRD14', 'PENETRATING OIL', NULL, NULL, 1, '', NULL, NULL, 160.00, 500.00, 2, 10, 'active', '2026-08-13 09:12:26', '2026-09-16 06:09:18'),
(16, 'PRD15', 'THROTTLE/CARB CLEANER', NULL, NULL, 1, '', NULL, NULL, 160.00, 0.00, 3, 10, 'active', '2026-08-13 09:12:35', '2026-08-17 02:58:20'),
(17, 'PRD16', 'GEAR OIL', 1, NULL, 8, '', NULL, NULL, 150.00, 350.00, 3, 10, 'active', '2026-08-13 09:12:42', '2026-09-12 10:39:09'),
(18, 'PRD17', 'GREASE', NULL, NULL, NULL, '', NULL, NULL, 0.00, 0.00, 0, 10, 'active', '2026-08-13 09:12:52', '2026-08-13 09:12:52'),
(19, 'PRD18', 'COOLANT BLUE', 9, NULL, 8, '', NULL, NULL, 145.00, 350.00, 1, 10, 'active', '2026-08-13 09:13:00', '2026-09-16 06:08:22'),
(20, 'PRD19', 'COOLANT GREEN', 9, NULL, 8, '', NULL, NULL, 145.00, 350.00, 1, 10, 'active', '2026-08-13 09:13:06', '2026-09-16 06:08:28'),
(21, 'PRD20', 'BATTERY (IMARFLEX)', 7, NULL, 1, '', NULL, NULL, 6530.00, 0.00, 0, 10, 'active', '2026-08-13 09:13:15', '2026-08-17 02:12:52'),
(22, 'PRD21', 'BRAKE PADS (MIRAGE)', 5, NULL, 1, '', NULL, NULL, 0.00, 0.00, 0, 10, 'active', '2026-08-13 09:13:22', '2026-08-17 02:17:43'),
(23, 'PRD22', 'STAB. LINK (TRANSFORMER)', NULL, NULL, 1, '', NULL, NULL, 0.00, 0.00, 3, 10, 'active', '2026-08-13 09:13:28', '2026-08-17 02:57:24'),
(24, 'PRD23', 'STAB. CLAMP (TRANSFORMER)', NULL, NULL, 1, '', NULL, NULL, 0.00, 0.00, 1, 10, 'active', '2026-08-13 09:13:35', '2026-08-17 02:57:12'),
(25, 'PRD24', 'VALVE COVER GASKET (TRANSFORMER)', NULL, NULL, 1, '', NULL, NULL, 250.00, 0.00, 0, 10, 'active', '2026-08-13 09:13:45', '2026-08-17 02:59:22'),
(26, 'PRD25', 'OIL FILTER (GEELY COOLRAY)', NULL, NULL, NULL, '', NULL, NULL, 0.00, 0.00, 0, 10, 'active', '2026-08-13 09:14:00', '2026-08-13 09:14:00'),
(27, 'PRD26', 'FLUSHING', NULL, NULL, 1, '', NULL, NULL, 430.00, 0.00, 0, 10, 'active', '2026-08-13 09:14:08', '2026-08-17 02:37:21'),
(28, 'PRD27', 'BRAKE FLUID DOT-3', 5, NULL, 7, '', NULL, NULL, 210.00, 350.00, 8, 10, 'active', '2026-08-13 09:16:56', '2026-08-17 02:15:57'),
(29, 'PRD28', 'ROBERLO SILTEX 8000', NULL, NULL, 1, '', NULL, NULL, 585.00, 0.00, 4, 10, 'active', '2026-08-13 09:17:03', '2026-08-17 02:55:15'),
(30, 'PRD29', 'CABIN FILTER', 3, NULL, 1, '', NULL, NULL, 180.00, 1200.00, 1, 10, 'active', '2026-08-13 09:17:10', '2026-09-12 10:37:22'),
(31, 'PRD30', 'WIRE', NULL, NULL, 18, '', NULL, NULL, 0.00, 0.00, 0, 10, 'active', '2026-08-13 09:17:17', '2026-08-17 02:59:58'),
(32, 'PRD31', 'STAB. LINK (TRANSFORMER)', NULL, NULL, 1, '', NULL, NULL, 500.00, 0.00, 0, 10, 'inactive', '2026-08-13 09:17:32', '2026-08-17 02:57:45'),
(33, 'PRD32', 'BRAKE PADS (TRANSORMER)', 5, NULL, 1, '', NULL, NULL, 0.00, 0.00, 0, 10, 'active', '2026-08-13 09:17:40', '2026-08-17 02:18:11'),
(34, 'PRD33', 'OIL FILTER-NAVARA 231', 2, NULL, 1, '', NULL, NULL, 950.00, 0.00, 1, 10, 'active', '2026-08-13 09:17:58', '2026-08-17 02:47:43'),
(35, 'PRD34', 'GEAR OIL -PETRON NEXUS', 1, NULL, 8, '', NULL, NULL, 0.00, 0.00, 1, 10, 'active', '2026-08-13 09:18:07', '2026-08-17 02:41:13'),
(36, 'PRD35', 'ATF SAE-20', 9, NULL, 8, '', NULL, NULL, 252.00, 950.00, 0, 10, 'active', '2026-08-17 01:49:49', '2026-08-17 02:39:13'),
(37, 'PRD36', 'BEARING', NULL, NULL, NULL, '', NULL, NULL, 980.00, 0.00, 2, 10, 'inactive', '2026-09-01 10:01:02', '2026-09-01 10:02:58'),
(38, 'PRD37', 'CVT VALVOLINE', 16, NULL, 8, '', NULL, NULL, 613.00, 950.00, 3, 4, 'active', '2026-09-09 08:42:25', '2026-09-09 08:47:51');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `category_name`, `description`, `status`, `created_at`) VALUES
(1, 'Engine Oil', 'Motor oils and engine lubricants', 'active', '2026-08-14 08:41:54'),
(2, 'Oil Filter', 'Oil filters for various engine types', 'active', '2026-08-14 08:41:54'),
(3, 'Air Filter', 'Air intake filters', 'active', '2026-08-14 08:41:54'),
(4, 'Fuel Filter', 'Fuel line and injection filters', 'active', '2026-08-14 08:41:54'),
(5, 'Brake Parts', 'Brake pads, rotors, calipers, and fluid', 'active', '2026-08-14 08:41:54'),
(6, 'Spark Plug', 'Ignition spark plugs', 'active', '2026-08-14 08:41:54'),
(7, 'Battery', 'Car batteries and terminals', 'active', '2026-08-14 08:41:54'),
(8, 'Belts & Hoses', 'Drive belts, timing belts, radiator hoses', 'active', '2026-08-14 08:41:54'),
(9, 'Coolant & Fluids', 'Coolant, transmission fluid, power steering fluid', 'active', '2026-08-14 08:41:54'),
(10, 'Suspension', 'Shocks, struts, bushings, ball joints', 'active', '2026-08-14 08:41:54'),
(11, 'Electrical', 'Bulbs, fuses, wiring, alternators, starters', 'active', '2026-08-14 08:41:54'),
(12, 'Tires & Wheels', 'Tires, rims, valve stems, wheel weights', 'active', '2026-08-14 08:41:54'),
(13, 'Wiper & Wash', 'Wiper blades, washer fluid', 'active', '2026-08-14 08:41:54'),
(14, 'Gaskets & Seals', 'Head gaskets, O-rings, valve seals', 'active', '2026-08-14 08:41:54'),
(15, 'Exhaust', 'Mufflers, catalytic converters, pipes', 'active', '2026-08-14 08:41:54'),
(16, 'Transmission', 'Clutch, gears, CV joints, axles', 'active', '2026-08-14 08:41:54'),
(17, 'Steering', 'Tie rods, rack and pinion, power steering pump', 'active', '2026-08-14 08:41:54'),
(18, 'Body Parts', 'Mirrors, bumpers, fenders, trim', 'active', '2026-08-14 08:41:54'),
(19, 'Interior', 'Upholstery, floor mats, accessories', 'active', '2026-08-14 08:41:54'),
(20, 'Adhesives & Sealants', 'Gasket maker, thread locker, body filler', 'active', '2026-08-14 08:41:54'),
(21, 'Cleaning & Detailing', 'Car wash, wax, polish, interior cleaner', 'active', '2026-08-14 08:41:54'),
(22, 'Tools & Equipment', 'Hand tools, diagnostic tools, shop supplies', 'active', '2026-08-14 08:41:54'),
(23, 'Nuts & Bolts', 'Fasteners, clips, screws, washers', 'active', '2026-08-14 08:41:54'),
(24, 'Lubricants & Grease', 'WD-40, chassis grease, bearing grease', 'active', '2026-08-14 08:41:54'),
(25, 'Others', 'Miscellaneous parts and supplies', 'active', '2026-08-14 08:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `service_code` varchar(20) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `labor_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `service_code`, `category_id`, `description`, `base_price`, `labor_cost`, `estimated_duration`, `status`, `created_at`, `updated_at`) VALUES
(3, 'CHANGE/FLUSH BRAKE FLUID', 'SVC01', NULL, 'LABOR', 0.00, 800.00, NULL, 'active', '2026-08-12 00:19:31', '2026-08-17 03:36:55'),
(4, 'CHANGE OIL', 'SVC02', NULL, 'PACKAGE', 0.00, 500.00, NULL, 'active', '2026-08-12 13:20:29', '2026-08-17 03:21:28'),
(5, 'HEAVY PMS', 'SVC03', NULL, 'PACKAGE\n- CHANGE OIL\n- CHANGE OIL FILTER\n- BRAKE CLEANING AND ADJUST\n- CLEANING THROTTLE BODY\n- CLEANING INTAKE MANIFOLD\n- CLEANING OXYGEN SENSOR\n- CLEANING MAF SENSOR\n- FLUSHING BRAKE FLUID\n- FLUSHING COOLANT\n- REPLACE AIR FILTER\n- REPLACE SPARK PLUG\n- REPLACE CABIN FILTER\n- CHECK LIGHTS\n- CHECK UNDER CHASSIS\n- SCANNING\n- FREE CARWASH', 0.00, 3800.00, NULL, 'active', '2026-08-12 13:21:50', '2026-08-17 01:26:21'),
(6, 'REGULAR PMS', 'SVC04', NULL, 'PACKAGE\n- CHANGE OIL\n- CHANGE OIL FILTER\n- BRAKE CLEANING/ADJUST\n- CLEANING AIR/CABIN FILTER\n- CLEANING SPARK PLUG\n- BOLT AND NUT TIGHTENING\n- CHECK UNDERCHASSIS\n- CHECK FLUID\n- CHECK LIGHTS\n- CHECK TIRES\n- CHECK BATTERY CONDITION\n- CHECK BELTS\n- FREE SCANNING', 0.00, 2800.00, NULL, 'active', '2026-08-12 13:23:28', '2026-09-01 01:27:46'),
(7, 'CHARGE FREON', 'SVC05', NULL, '', 0.00, 1500.00, NULL, 'active', '2026-08-13 06:27:04', '2026-08-13 06:27:04'),
(8, 'RADIATOR CLEANING', 'SVC06', NULL, '', 0.00, 6500.00, NULL, 'active', '2026-08-13 06:27:27', '2026-08-13 06:27:27'),
(9, 'REPLACE DRIVE BELT', 'SVC07', NULL, 'LABOR', 0.00, 600.00, NULL, 'active', '2026-08-13 06:28:04', '2026-08-13 06:28:49'),
(10, 'REPLACE DRIVE BELT (FORD)', 'SVC08', NULL, 'LABOR', 0.00, 1800.00, NULL, 'active', '2026-08-13 06:28:35', '2026-08-13 06:28:35'),
(11, 'REPLACE SPARK PLUG', 'SVC09', NULL, 'LABOR', 600.00, 0.00, NULL, 'active', '2026-08-13 06:29:13', '2026-08-17 03:44:52'),
(12, 'REPLACE AUXILIARY FAN MOTOR', 'SVC10', NULL, 'LABOR', 0.00, 1400.00, NULL, 'active', '2026-08-13 06:29:50', '2026-08-13 06:30:10'),
(13, 'PULL OUT/INSTALL FRT. LOWER SUSPENSION ASSY RH/LH', 'SVC11', NULL, '', 0.00, 1800.00, NULL, 'active', '2026-08-13 06:30:32', '2026-08-13 06:30:50'),
(14, 'FUEL INJECTOR CLEANING', 'SVC12', NULL, 'LABOR', 0.00, 2500.00, NULL, 'active', '2026-08-13 07:56:49', '2026-08-17 03:28:19'),
(15, 'WHEEL ALIGNMENT (TOE IN/TOE OUT)', 'SVC13', NULL, 'LABOR', 0.00, 1200.00, NULL, 'active', '2026-08-13 08:46:49', '2026-08-17 03:26:03'),
(16, 'WHEEL ALIGNMENT (COMPLETE)', 'SVC14', NULL, 'LABOR', 0.00, 2200.00, NULL, 'active', '2026-08-13 08:47:04', '2026-08-17 03:25:35'),
(17, 'STEERING RACK REPAIR - PULL OUT/INSTALL', 'SVC15', NULL, 'LABOR', 0.00, 3500.00, NULL, 'active', '2026-08-13 08:49:31', '2026-08-17 03:38:11'),
(18, 'LIGHT PMS GAS', 'SVC16', NULL, 'LABOR/PACKAGE\n- TOP UP ENGINE OIL\n- REPLACE OIL FILTER\n- CHECK FLUIDS\n- CHECK BELTS\n- CHECK LIGHTS\n- CHECK UNDERCHASSIS\n- BOLT AND NUT TIGHTENING\n- SCANNING', 0.00, 1800.00, NULL, 'active', '2026-08-17 01:37:34', '2026-08-17 03:59:12'),
(19, 'AIRCON CLEANING (SINGLE EVAPORATOR)', 'SVC17', NULL, 'PACKAGE', 0.00, 5500.00, NULL, 'active', '2026-08-17 03:18:12', '2026-08-17 03:18:12'),
(20, 'AIRCON CLEANING (DUAL EVAPORATOR)', 'SVC18', NULL, 'PACKAGE', 0.00, 6500.00, NULL, 'active', '2026-08-17 03:19:12', '2026-08-17 03:19:26'),
(21, 'EGR, INTAKE AND TURBO CLEANING', 'SVC19', NULL, 'LABOR', 0.00, 10500.00, NULL, 'active', '2026-08-17 03:22:13', '2026-08-31 23:37:32'),
(22, 'EGR AND INTAKE CLEANING', 'SVC20', NULL, 'LABOR', 0.00, 5500.00, NULL, 'active', '2026-08-17 03:22:59', '2026-08-17 03:37:41'),
(23, 'TURBO CLEANING', 'SVC21', NULL, 'LABOR', 0.00, 4500.00, NULL, 'active', '2026-08-17 03:23:33', '2026-08-17 03:23:33'),
(24, 'EGR, INTAKE, AND TURBO CLEANING', 'SVC22', NULL, 'PACKAGE', 0.00, 10500.00, NULL, 'active', '2026-08-17 03:24:26', '2026-08-17 03:24:26'),
(25, 'CARWASH', 'SVC23', NULL, 'LABOR', 0.00, 150.00, NULL, 'active', '2026-08-17 03:25:02', '2026-08-17 03:25:02'),
(26, 'BRAKE CLEANING', 'SVC24', NULL, 'LABOR', 0.00, 1200.00, NULL, 'active', '2026-08-17 03:27:21', '2026-08-31 23:36:34'),
(27, 'DRIVE BELT REPLACEMENT', 'SVC25', NULL, 'LABOR', 0.00, 1800.00, NULL, 'active', '2026-08-17 03:30:05', '2026-08-17 03:30:05'),
(28, 'THROTTLE BODY CLEANING', 'SVC26', NULL, 'LABOR', 0.00, 800.00, NULL, 'active', '2026-08-17 03:30:35', '2026-08-17 03:30:35'),
(29, 'REPLACE AUX. FAN MOTOR', 'SVC27', NULL, 'LABOR', 0.00, 1400.00, NULL, 'active', '2026-08-17 03:31:12', '2026-08-17 03:31:12'),
(30, 'PULL OUT / INSTALL FRONT LOWER SUSP. ASSY (RH/LH)', 'SVC28', NULL, 'LABOR', 0.00, 1800.00, NULL, 'active', '2026-08-17 03:32:13', '2026-08-17 03:32:13'),
(31, 'REPLACE AIR FILTER AND CABIN FILTER', 'SVC29', NULL, 'LABOR', 0.00, 2500.00, NULL, 'active', '2026-08-17 03:33:06', '2026-08-17 03:33:06'),
(32, 'RADIATOR CLEANING', 'SVC30', NULL, 'LABOR', 0.00, 6500.00, NULL, 'active', '2026-08-17 03:33:31', '2026-08-17 03:33:31'),
(33, 'RESCUE', 'SVC31', NULL, 'LABOR', 1500.00, 0.00, NULL, 'active', '2026-08-17 03:34:07', '2026-08-17 03:34:42'),
(34, 'TOWING', 'SVC32', NULL, 'LABOR', 2500.00, 0.00, NULL, 'active', '2026-08-17 03:35:05', '2026-08-17 03:35:05'),
(35, 'TIE ROD REPLACEMENT', 'SVC33', NULL, 'LABOR', 0.00, 800.00, NULL, 'active', '2026-08-17 03:38:40', '2026-08-17 03:38:40'),
(36, 'WHEEL BALANCING', 'SVC34', NULL, 'LABOR', 0.00, 1800.00, NULL, 'active', '2026-08-17 03:39:36', '2026-08-17 03:39:36'),
(37, 'CHECK/CORRECT LEAK COMING INSIDE', 'SVC35', NULL, 'LABOR', 0.00, 500.00, NULL, 'active', '2026-08-17 03:40:15', '2026-08-17 03:40:15'),
(38, 'PULL OUT CAR MATTING (CLEAN AND DRY)', 'SVC36', NULL, 'LABOR', 0.00, 1200.00, NULL, 'active', '2026-08-17 03:40:52', '2026-08-17 03:40:52'),
(39, 'OXYGEN SENSOR CLEANING', 'SVC37', NULL, 'LABOR', 0.00, 800.00, NULL, 'active', '2026-08-17 03:41:27', '2026-08-17 03:41:27'),
(40, 'REFACE ROTOR DISC (BOTH SIDES) - SEDAN', 'SVC38', NULL, 'LABOR', 0.00, 3500.00, NULL, 'active', '2026-08-17 03:43:07', '2026-08-31 23:39:28'),
(41, 'REFACE ROTOR DISC (BOTH SIDES) - PICK UP, SUV', 'SVC39', NULL, '', 0.00, 3500.00, NULL, 'active', '2026-08-17 03:43:33', '2026-08-31 23:39:12'),
(42, 'REPLACE LOWER BALL JOINT (BOTH SIDES)', 'SVC40', NULL, 'LABOR', 0.00, 3800.00, NULL, 'active', '2026-08-17 03:44:23', '2026-08-17 03:44:23'),
(43, 'LIGHT PMS DIESEL', 'SVC41', NULL, 'LABOR/PACKAGE', 0.00, 2200.00, NULL, 'active', '2026-08-17 04:00:51', '2026-08-17 04:00:51'),
(44, '', 'SVC42', NULL, '', 0.00, 600.00, NULL, 'inactive', '2026-09-01 02:28:30', '2026-09-01 02:31:46');

-- --------------------------------------------------------

--
-- Table structure for table `service_bundles`
--

CREATE TABLE `service_bundles` (
  `id` int(11) NOT NULL,
  `bundle_name` varchar(100) NOT NULL,
  `bundle_code` varchar(20) NOT NULL,
  `bundle_type` enum('light_pms','regular_pms','heavy_pms','custom') NOT NULL,
  `description` text DEFAULT NULL,
  `package_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `full_name` varchar(100) GENERATED ALWAYS AS (concat(`first_name`,' ',`last_name`)) STORED,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','cashier','chief_mechanic','service_adviser','technician','lead_man','stockman') NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `status` enum('active','inactive','on_leave') NOT NULL DEFAULT 'active',
  `team_id` int(11) DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `staff_id`, `first_name`, `last_name`, `email`, `phone`, `address`, `role`, `username`, `password`, `profile_photo`, `hire_date`, `status`, `team_id`, `supervisor_id`, `hourly_rate`, `created_at`, `updated_at`) VALUES
(4, '92178', 'Danilo', 'Guingue Cortez Jr.', 'theautodok@gmail.com', '09094008398', '', 'admin', '92178', '$2y$12$5aGnqjYhQawuKO8/5GMtlOq5D1Z5LSx56Q.bPdgQSZY1QDJL/wfQ.', NULL, '2026-08-10', 'active', NULL, NULL, 0.00, '2026-08-10 11:59:36', '2026-08-10 11:59:36'),
(5, '47311', 'Erin', 'Martinez', 'ptrciaerin.m@gmail.com', '09953653158', NULL, 'cashier', '47311', '$2y$12$xmEqUn18LzwrM7kDhqR5qOvvowcQP8cjVUpx.yaWlPhkSWMlQBau2', '6a9a168d6eb99_1788483213.jpg', '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 11:19:51', '2026-09-04 00:53:33'),
(6, '53468', 'LOVELY', 'JOYCE', 'lovelyjoycegambong@gmail.com', '09186497454', NULL, 'cashier', '53468', '$2y$12$Y9DWyxnZ7xAuORuziv2Q9uP./CkGtNvIvEfUP5US9Nkk.sm2KIS1G', '6a968da400555_1788251556.jpg', '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 11:22:26', '2026-09-01 08:32:36'),
(7, '65275', 'Iloisa', 'Joy P. Mejias', 'iloisajoym@gmail.com', '09973578954', '', 'cashier', '65275', '$2y$12$lLmQRB76imc4IxpMrFZmQusqBmIIAbJyiTNsWKROeiMDq83k/eN62', NULL, '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 11:24:05', '2026-08-12 11:24:05'),
(8, '72001', 'Aian', 'P. Alderite', 'aianalderite@gmail.com', '0936 340 8302', '', 'service_adviser', '72001', '$2y$12$cCEGDNbrfh3/pv8zcn6tpOXaZzgAm5/.WQ5Gr/i8FZsX7t14qPDh.', NULL, '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 11:46:14', '2026-08-12 11:46:14'),
(9, '15460', 'Nexander', 'G.', 'nexandergayan10@gmail.com', '0905 581 5476', '', 'technician', '15460', '$2y$12$f/6Q/Y.nVD8SAWFsSkd.iOZuyyv.2eVUI2TsmW.727aOBG/B5IP.m', NULL, '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 11:52:42', '2026-09-01 05:47:19'),
(10, '12086', 'Kineth', 'P.', 'kinethpandian23@gmail.com', '0951 188 7810', '', 'technician', '12086', '$2y$12$bRM585rqMBtdMqrybwj4r.fgeYMuVrxWmj63vVti49TzF3ioXz6cS', NULL, '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 11:58:18', '2026-09-01 05:47:29'),
(11, '95775', 'Jerald', 'C.', 'jeraldchangco@gmail.com', '0915 400 4423', '', 'technician', '95775', '$2y$12$SWHJqWyT5S7Jup2z8vOMcOrUaFpF8DxbWAqgMu7EVtBU2OxUpDWru', NULL, '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 12:04:23', '2026-09-01 05:47:43'),
(12, '93576', 'John', 'Paul V.', 'johnpaulvillamente@gmail.com', '0938 173 9226', '', 'technician', '93576', '$2y$12$LobxPJUh8dneBSnRkspzW.BmVZLuhvIvxbmIXRCxSsG60R0fgnjhC', NULL, '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 12:07:03', '2026-09-01 05:48:07'),
(13, '97893', 'Legario', 'M.', 'legariomosaso@gmail.com', '-', '', 'technician', '97893', '$2y$12$FHDB2T4Wmndkhc8uhOq.LemC1/SI5tLNwxsBlWLkHUfmfwmCIKWZ6', NULL, '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 12:26:30', '2026-09-01 05:47:55'),
(14, '96784', 'Jan', 'Carlo Padios', 'jancarlopadios@gmail.com', '0991 651 1233', '', 'technician', '96784', '$2y$12$u8MngbuXvLI.KM6qAVBHbOkmXzhvLrleoxdasds9K.hi.bLqxQ5y6', NULL, '2026-08-12', 'inactive', NULL, NULL, 0.00, '2026-08-12 12:34:01', '2026-08-31 12:43:50'),
(15, '32925', 'Artemio', 'B. Jr', 'artemiobaquirel@gmail.com', '0956 033 4033', '', 'technician', '32925', '$2y$12$6dl/tk7rM2QJHGhIzNsPI.kUQZqW6uThR/sE8Q./.e67B6ZGQosfu', NULL, '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 12:42:50', '2026-09-01 05:48:22'),
(16, '76319', 'Gracesilyn', 'Pelvira Chen', 'sample@gmail.com', '09000000000', '', 'admin', '76319', '$2y$12$uqzyzOJAl0t4amOC0kex3ueHdsWyc1vr8XKNfoxRZM2nCmj9/IOEK', NULL, '2026-08-12', 'active', NULL, NULL, 0.00, '2026-08-12 13:23:16', '2026-08-12 13:23:16');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `total_purchases` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_payments` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_transactions`
--

CREATE TABLE `supplier_transactions` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `transaction_type` enum('purchase','payment','adjustment','return') NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
(1, 'user_profile_photo_admin_2', '6a788e290344e_1786285609.jpg', 'string', 'Admin profile photo filename', '2026-08-09 14:26:49'),
(3, 'user_phone_admin_2', '09943275040', 'string', 'Admin profile phone number', '2026-08-09 06:37:27');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `team_leader_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `technician_points`
--

CREATE TABLE `technician_points` (
  `id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `job_order_id` int(11) DEFAULT NULL,
  `reason` varchar(100) NOT NULL,
  `points` decimal(6,1) NOT NULL DEFAULT 0.0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `technician_points`
--

INSERT INTO `technician_points` (`id`, `technician_id`, `job_order_id`, `reason`, `points`, `created_at`) VALUES
(1, 9, 7, 'JO Completed (Lead)', 10.0, '2026-09-01 13:40:32'),
(2, 9, 7, 'Revenue Bonus', 5.8, '2026-09-01 13:40:32'),
(3, 9, 7, 'Speed Bonus', 3.0, '2026-09-01 13:40:32'),
(4, 9, 7, 'Clean Completion', 3.0, '2026-09-01 13:40:32'),
(5, 11, 7, 'JO Completed (Assistant)', 5.0, '2026-09-01 13:40:32'),
(6, 11, 7, 'Revenue Bonus', 2.9, '2026-09-01 13:40:32'),
(7, 11, 7, 'Speed Bonus', 3.0, '2026-09-01 13:40:32'),
(8, 11, 7, 'Clean Completion', 3.0, '2026-09-01 13:40:32'),
(9, 10, 8, 'JO Completed (Lead)', 10.0, '2026-09-01 14:43:28'),
(10, 10, 8, 'Revenue Bonus', 12.5, '2026-09-01 14:43:28'),
(11, 10, 8, 'Speed Bonus', 1.0, '2026-09-01 14:43:28'),
(12, 10, 8, 'Clean Completion', 3.0, '2026-09-01 14:43:28'),
(13, 9, 8, 'JO Completed (Lead)', 10.0, '2026-09-01 14:43:28'),
(14, 9, 8, 'Revenue Bonus', 12.5, '2026-09-01 14:43:28'),
(15, 9, 8, 'Speed Bonus', 1.0, '2026-09-01 14:43:28'),
(16, 9, 8, 'Clean Completion', 3.0, '2026-09-01 14:43:28'),
(17, 11, 8, 'JO Completed (Assistant)', 5.0, '2026-09-01 14:43:28'),
(18, 11, 8, 'Revenue Bonus', 6.3, '2026-09-01 14:43:28'),
(19, 11, 8, 'Speed Bonus', 1.0, '2026-09-01 14:43:28'),
(20, 11, 8, 'Clean Completion', 3.0, '2026-09-01 14:43:28'),
(21, 10, 11, 'JO Completed (Lead)', 10.0, '2026-09-03 16:21:31'),
(22, 10, 11, 'Revenue Bonus', 5.7, '2026-09-03 16:21:31'),
(23, 10, 11, 'Speed Bonus', 3.0, '2026-09-03 16:21:31'),
(24, 10, 11, 'Clean Completion', 3.0, '2026-09-03 16:21:31'),
(25, 11, 9, 'JO Completed (Lead)', 10.0, '2026-09-03 16:28:35'),
(26, 11, 9, 'Revenue Bonus', 4.0, '2026-09-03 16:28:35'),
(27, 11, 9, 'Speed Bonus', 3.0, '2026-09-03 16:28:35'),
(28, 11, 9, 'Clean Completion', 3.0, '2026-09-03 16:28:35'),
(29, 11, 10, 'JO Completed (Lead)', 10.0, '2026-09-04 08:23:46'),
(30, 11, 10, 'Revenue Bonus', 12.3, '2026-09-04 08:23:46'),
(31, 11, 10, 'Speed Bonus', 1.0, '2026-09-04 08:23:46'),
(32, 11, 10, 'Clean Completion', 3.0, '2026-09-04 08:23:46'),
(33, 9, 10, 'JO Completed (Lead)', 10.0, '2026-09-04 08:23:46'),
(34, 9, 10, 'Revenue Bonus', 12.3, '2026-09-04 08:23:46'),
(35, 9, 10, 'Speed Bonus', 1.0, '2026-09-04 08:23:46'),
(36, 9, 10, 'Clean Completion', 3.0, '2026-09-04 08:23:46'),
(37, 13, 10, 'JO Completed (Assistant)', 5.0, '2026-09-04 08:23:46'),
(38, 13, 10, 'Revenue Bonus', 6.2, '2026-09-04 08:23:46'),
(39, 13, 10, 'Speed Bonus', 1.0, '2026-09-04 08:23:46'),
(40, 13, 10, 'Clean Completion', 3.0, '2026-09-04 08:23:46'),
(41, 10, 14, 'JO Completed (Lead)', 10.0, '2026-09-05 12:29:04'),
(42, 10, 14, 'Revenue Bonus', 8.3, '2026-09-05 12:29:04'),
(43, 10, 14, 'Speed Bonus', 4.0, '2026-09-05 12:29:04'),
(44, 10, 14, 'Clean Completion', 3.0, '2026-09-05 12:29:04'),
(45, 12, 15, 'JO Completed (Lead)', 10.0, '2026-09-05 15:53:26'),
(46, 12, 15, 'Revenue Bonus', 8.0, '2026-09-05 15:53:26'),
(47, 12, 15, 'Speed Bonus', 5.0, '2026-09-05 15:53:26'),
(48, 12, 15, 'Clean Completion', 3.0, '2026-09-05 15:53:26'),
(49, 10, 13, 'JO Completed (Lead)', 10.0, '2026-09-05 17:13:11'),
(50, 10, 13, 'Revenue Bonus', 36.7, '2026-09-05 17:13:11'),
(51, 10, 13, 'Speed Bonus', 3.0, '2026-09-05 17:13:11'),
(52, 10, 13, 'Clean Completion', 3.0, '2026-09-05 17:13:11'),
(53, 13, 13, 'JO Completed (Lead)', 10.0, '2026-09-05 17:13:11'),
(54, 13, 13, 'Revenue Bonus', 36.7, '2026-09-05 17:13:11'),
(55, 13, 13, 'Speed Bonus', 3.0, '2026-09-05 17:13:11'),
(56, 13, 13, 'Clean Completion', 3.0, '2026-09-05 17:13:11'),
(57, 9, 12, 'JO Completed (Lead)', 10.0, '2026-09-05 17:47:36'),
(58, 9, 12, 'Revenue Bonus', 12.4, '2026-09-05 17:47:36'),
(59, 9, 12, 'Speed Bonus', 5.0, '2026-09-05 17:47:36'),
(60, 9, 12, 'Clean Completion', 3.0, '2026-09-05 17:47:36'),
(61, 11, 16, 'JO Completed (Lead)', 10.0, '2026-09-07 11:35:13'),
(62, 11, 16, 'Revenue Bonus', 1.2, '2026-09-07 11:35:13'),
(63, 11, 16, 'Speed Bonus', 5.0, '2026-09-07 11:35:13'),
(64, 11, 16, 'Clean Completion', 3.0, '2026-09-07 11:35:13'),
(65, 13, 16, 'JO Completed (Lead)', 10.0, '2026-09-07 11:35:13'),
(66, 13, 16, 'Revenue Bonus', 1.2, '2026-09-07 11:35:13'),
(67, 13, 16, 'Speed Bonus', 5.0, '2026-09-07 11:35:13'),
(68, 13, 16, 'Clean Completion', 3.0, '2026-09-07 11:35:13'),
(69, 9, 18, 'JO Completed (Lead)', 10.0, '2026-09-08 13:40:06'),
(70, 9, 18, 'Revenue Bonus', 5.8, '2026-09-08 13:40:06'),
(71, 9, 18, 'Speed Bonus', 5.0, '2026-09-08 13:40:06'),
(72, 9, 18, 'Clean Completion', 3.0, '2026-09-08 13:40:06'),
(73, 11, 17, 'JO Completed (Lead)', 10.0, '2026-09-09 08:58:16'),
(74, 11, 17, 'Revenue Bonus', 3.0, '2026-09-09 08:58:16'),
(75, 11, 17, 'Speed Bonus', 4.0, '2026-09-09 08:58:16'),
(76, 11, 17, 'Clean Completion', 3.0, '2026-09-09 08:58:16'),
(77, 9, 20, 'JO Completed (Lead)', 10.0, '2026-09-09 15:59:59'),
(78, 9, 20, 'Revenue Bonus', 4.7, '2026-09-09 15:59:59'),
(79, 9, 20, 'Speed Bonus', 5.0, '2026-09-09 15:59:59'),
(80, 9, 20, 'Clean Completion', 3.0, '2026-09-09 15:59:59'),
(81, 13, 19, 'JO Completed (Lead)', 10.0, '2026-09-10 11:51:39'),
(82, 13, 19, 'Revenue Bonus', 9.3, '2026-09-10 11:51:39'),
(83, 13, 19, 'Speed Bonus', 2.0, '2026-09-10 11:51:39'),
(84, 13, 19, 'Clean Completion', 3.0, '2026-09-10 11:51:39'),
(85, 9, 19, 'JO Completed (Lead)', 10.0, '2026-09-10 11:51:39'),
(86, 9, 19, 'Revenue Bonus', 9.3, '2026-09-10 11:51:39'),
(87, 9, 19, 'Speed Bonus', 2.0, '2026-09-10 11:51:39'),
(88, 9, 19, 'Clean Completion', 3.0, '2026-09-10 11:51:39'),
(89, 13, 21, 'JO Completed (Lead)', 10.0, '2026-09-11 09:16:48'),
(90, 13, 21, 'Revenue Bonus', 1.2, '2026-09-11 09:16:48'),
(91, 13, 21, 'Speed Bonus', 1.0, '2026-09-11 09:16:48'),
(92, 13, 21, 'Clean Completion', 3.0, '2026-09-11 09:16:48'),
(93, 9, 22, 'JO Completed (Lead)', 10.0, '2026-09-12 15:02:14'),
(94, 9, 22, 'Revenue Bonus', 0.8, '2026-09-12 15:02:14'),
(95, 9, 22, 'Speed Bonus', 3.0, '2026-09-12 15:02:14'),
(96, 9, 22, 'Clean Completion', 3.0, '2026-09-12 15:02:14'),
(97, 10, 25, 'JO Completed (Lead)', 10.0, '2026-09-16 16:00:38'),
(98, 10, 25, 'Revenue Bonus', 13.8, '2026-09-16 16:00:38'),
(99, 10, 25, 'Speed Bonus', 4.0, '2026-09-16 16:00:38'),
(100, 10, 25, 'Clean Completion', 3.0, '2026-09-16 16:00:38'),
(101, 10, 27, 'JO Completed (Lead)', 10.0, '2026-09-17 16:57:49'),
(102, 10, 27, 'Revenue Bonus', 11.5, '2026-09-17 16:57:49'),
(103, 10, 27, 'Speed Bonus', 5.0, '2026-09-17 16:57:49'),
(104, 10, 27, 'Clean Completion', 3.0, '2026-09-17 16:57:49'),
(105, 13, 26, 'JO Completed (Lead)', 10.0, '2026-09-18 09:20:09'),
(106, 13, 26, 'Revenue Bonus', 4.0, '2026-09-18 09:20:09'),
(107, 13, 26, 'Speed Bonus', 5.0, '2026-09-18 09:20:09'),
(108, 13, 26, 'Clean Completion', 3.0, '2026-09-18 09:20:09'),
(109, 9, 26, 'JO Completed (Lead)', 10.0, '2026-09-18 09:20:09'),
(110, 9, 26, 'Revenue Bonus', 4.0, '2026-09-18 09:20:09'),
(111, 9, 26, 'Speed Bonus', 5.0, '2026-09-18 09:20:09'),
(112, 9, 26, 'Clean Completion', 3.0, '2026-09-18 09:20:09'),
(113, 10, 30, 'JO Completed (Lead)', 10.0, '2026-09-21 15:53:50'),
(114, 10, 30, 'Revenue Bonus', 1.4, '2026-09-21 15:53:50'),
(115, 10, 30, 'Speed Bonus', 5.0, '2026-09-21 15:53:50'),
(116, 10, 30, 'Clean Completion', 3.0, '2026-09-21 15:53:50'),
(117, 10, 29, 'JO Completed (Lead)', 10.0, '2026-09-21 17:23:09'),
(118, 10, 29, 'Revenue Bonus', 6.5, '2026-09-21 17:23:09'),
(119, 10, 29, 'Speed Bonus', 1.0, '2026-09-21 17:23:09'),
(120, 10, 29, 'Clean Completion', 3.0, '2026-09-21 17:23:09'),
(121, 10, 31, 'JO Completed (Lead)', 10.0, '2026-09-24 13:17:36'),
(122, 10, 31, 'Revenue Bonus', 1.2, '2026-09-24 13:17:36'),
(123, 10, 31, 'Speed Bonus', 1.0, '2026-09-24 13:17:36'),
(124, 10, 31, 'Clean Completion', 3.0, '2026-09-24 13:17:36'),
(125, 11, 33, 'JO Completed (Lead)', 10.0, '2026-09-26 19:38:03'),
(126, 11, 33, 'Revenue Bonus', 8.0, '2026-09-26 19:38:03'),
(127, 11, 33, 'Clean Completion', 3.0, '2026-09-26 19:38:03');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `unit_symbol` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `unit_name`, `unit_symbol`, `created_at`) VALUES
(1, 'Piece', 'pc', '2026-08-14 08:38:49'),
(2, 'Pieces', 'pcs', '2026-08-14 08:38:49'),
(3, 'Set', 'set', '2026-08-14 08:38:49'),
(4, 'Pair', 'pr', '2026-08-14 08:38:49'),
(5, 'Box', 'box', '2026-08-14 08:38:49'),
(6, 'Pack', 'pk', '2026-08-14 08:38:49'),
(7, 'Bottle', 'btl', '2026-08-14 08:38:49'),
(8, 'Liter', 'L', '2026-08-14 08:38:49'),
(9, 'Milliliter', 'mL', '2026-08-14 08:38:49'),
(10, 'Gallon', 'gal', '2026-08-14 08:38:49'),
(11, 'Quart', 'qt', '2026-08-14 08:38:49'),
(12, 'Kilogram', 'kg', '2026-08-14 08:38:49'),
(13, 'Gram', 'g', '2026-08-14 08:38:49'),
(14, 'Meter', 'm', '2026-08-14 08:38:49'),
(15, 'Centimeter', 'cm', '2026-08-14 08:38:49'),
(16, 'Inch', 'in', '2026-08-14 08:38:49'),
(17, 'Foot', 'ft', '2026-08-14 08:38:49'),
(18, 'Roll', 'roll', '2026-08-14 08:38:49'),
(19, 'Tube', 'tube', '2026-08-14 08:38:49'),
(20, 'Can', 'can', '2026-08-14 08:38:49'),
(21, 'Drum', 'drum', '2026-08-14 08:38:49'),
(22, 'Sachet', 'scht', '2026-08-14 08:38:49'),
(23, 'Sheet', 'sht', '2026-08-14 08:38:49'),
(24, 'Unit', 'unit', '2026-08-14 08:38:49'),
(25, 'Length', 'len', '2026-08-14 08:38:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','cashier','chief_mechanic','service_adviser','technician','lead_man','stockman') NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `password_changed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `status`, `last_login`, `last_login_ip`, `password_changed_at`, `created_at`, `updated_at`) VALUES
(2, '00000', '$2y$12$O9ar7m6BMLS3ta0p1NyzpeWM4AAd6KKKIQXXRgzT80Yx3ZJe6xykS', 'Dj Guingue Cortez', 'owwkxi@gmail.com', 'admin', 'active', NULL, NULL, NULL, '2026-08-09 06:10:49', '2026-08-09 14:26:49');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `vehicle_owner` varchar(100) DEFAULT NULL,
  `vehicle_type` varchar(50) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `year_model` varchar(4) DEFAULT NULL,
  `plate_number` varchar(20) DEFAULT NULL,
  `engine_type` varchar(50) DEFAULT NULL,
  `mileage` varchar(20) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `customer_id`, `vehicle_owner`, `vehicle_type`, `brand`, `model`, `year_model`, `plate_number`, `engine_type`, `mileage`, `color`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, '', '', '', '', NULL, '', '', '2026-08-09 13:00:00', '2026-08-09 13:00:00'),
(2, 2, NULL, NULL, '', '', '', '', NULL, '', '', '2026-08-09 13:00:23', '2026-08-09 13:00:23'),
(3, 3, NULL, NULL, '', '', '', '', NULL, '', '', '2026-08-09 13:10:32', '2026-08-09 13:10:32'),
(4, 4, NULL, NULL, '', '', '', '', NULL, '', '', '2026-08-09 14:31:06', '2026-08-09 14:31:06'),
(5, 5, NULL, NULL, '', '', '', '', NULL, '', '', '2026-08-13 01:45:49', '2026-08-13 01:45:49'),
(6, 6, NULL, NULL, 'MONTERO', 'MITSUBISHI', '2010', 'LGT291', NULL, '238,976', 'SILVER GRAY', '2026-08-31 23:49:27', '2026-08-31 23:49:27'),
(7, 7, NULL, NULL, 'MG5', 'MG', '2024', 'LAP2625', NULL, '18,327 KM', 'SILVER', '2026-09-01 01:20:27', '2026-09-01 01:20:27'),
(8, 8, NULL, NULL, 'MITSUBISHI', 'MIRAGE G4', '2023', 'LAM1046', NULL, '57,577 KM', 'SILVER', '2026-09-01 02:20:23', '2026-09-01 02:20:23'),
(9, 9, NULL, NULL, 'CHEVROLET', 'COLORADO', '2017', 'LAA9625', NULL, '201,386', 'BLUE', '2026-09-01 08:07:46', '2026-09-01 08:09:36'),
(10, 10, NULL, NULL, 'FORD', 'RANGER', '2018', 'LAC1086', NULL, '70,897', 'GRAY', '2026-09-02 00:50:35', '2026-09-02 00:50:35'),
(11, 11, NULL, NULL, 'TOYOTA', 'VIOS', '2012', 'UPQ833', NULL, '602,224', 'WHITE', '2026-09-02 03:59:20', '2026-09-02 03:59:20'),
(12, 12, NULL, NULL, 'TOYOTA', 'VIOS/MT', '2006', 'LFW628', NULL, '52935', 'RED', '2026-09-04 01:52:57', '2026-09-07 06:32:05'),
(13, 13, NULL, NULL, 'FORD', 'EVEREST', '2005', 'XND876', NULL, '245,853', 'BEIGE', '2026-09-04 03:59:14', '2026-09-04 03:59:14'),
(14, 14, NULL, NULL, 'SUZUKI', 'WAGON', '2019', 'MAT1756', NULL, '168,746', 'GRAY', '2026-09-05 01:37:01', '2026-09-05 01:37:01'),
(15, 15, NULL, NULL, 'TOYOTA', 'HILUX VIGO', '2013', 'LHG921', NULL, '205,640', 'BLUE', '2026-09-05 06:42:24', '2026-09-05 06:42:24'),
(16, 16, NULL, NULL, 'TOYOTA', 'WIGO/CVT', '2021', 'LAH7218', NULL, '41684', 'BLACK', '2026-09-07 02:14:38', '2026-09-07 02:15:34'),
(17, 17, NULL, NULL, 'TOYOTA', 'VIOS', '2012', 'UPQ833', NULL, '602,324', 'WHITE', '2026-09-08 00:24:35', '2026-09-08 00:24:35'),
(18, 18, NULL, NULL, 'SUZUKI', 'STINGRAY', '2017', 'MBE8875', NULL, '176,354', 'BROWN', '2026-09-08 04:58:09', '2026-09-08 04:58:09'),
(19, 19, NULL, NULL, 'SUZUKI', 'MINI VAN AT', '2019', 'MAV5446', NULL, '238,296', 'WHITE', '2026-09-08 06:34:37', '2026-09-08 06:34:37'),
(20, 20, NULL, NULL, 'SUZUKI', 'STINGRAY', '2017', 'MBE8875', NULL, '176378', 'BLACK', '2026-09-09 06:31:30', '2026-09-09 06:32:29'),
(21, 21, NULL, NULL, 'TOYOTA', 'HILUX VIGO', '2013', 'LHG921', NULL, '205645', 'BLUE', '2026-09-10 07:22:06', '2026-09-10 07:22:06'),
(22, 22, NULL, NULL, 'CHEVROLET', 'TRAILBLAZER', '2015', 'AHA 1465', NULL, '', '', '2026-09-12 03:10:10', '2026-09-12 03:15:05'),
(23, 23, NULL, NULL, 'MINIVAN', '', '', 'KBK 9932', NULL, '', 'WHITE', '2026-09-12 07:06:00', '2026-09-12 07:06:00'),
(24, 24, NULL, NULL, 'TOYOTA', 'REVO AT', '2002', 'LEV544', NULL, '171,812', 'BEIGE', '2026-09-15 00:37:54', '2026-09-15 00:37:54'),
(25, 25, NULL, NULL, 'TOYOTA', 'FORTUNER AT', '2022', 'LAL9191', NULL, '82,232', 'WHITE', '2026-09-16 05:08:16', '2026-09-16 05:08:16'),
(26, 26, NULL, NULL, 'SUZUKI', 'MINI VAN AT', '2019', 'MBM8164', NULL, '256398', 'GRAY', '2026-09-17 01:53:34', '2026-09-17 01:53:34'),
(27, 27, NULL, NULL, 'HYUNDAI', 'TUCSON AT', '2017', 'LAC5081', NULL, '66449', 'WHITE', '2026-09-17 02:01:52', '2026-09-17 02:01:52'),
(28, 28, NULL, NULL, 'MITSUBISHI', 'STRADA MT 4X4', '2014', 'AAG1208', NULL, '174,000', 'BROWN', '2026-09-20 23:23:15', '2026-09-20 23:23:15'),
(29, 29, NULL, NULL, 'MITSUBISHI', 'STRADA MT 4X4', '2014', 'AAG1208', NULL, '174,000', 'BROWN', '2026-09-20 23:23:15', '2026-09-20 23:23:15'),
(30, 30, NULL, NULL, 'TOYOTA', 'REVO AT', '2002', 'LEV544', NULL, '70,897', 'BEIGE', '2026-09-21 07:21:04', '2026-09-21 07:21:04'),
(31, 31, NULL, NULL, 'SUZUKI', 'EVERY LANDY', '2003', 'AGA3043', NULL, '158,070', 'BLACK', '2026-09-23 07:12:23', '2026-09-23 07:12:23'),
(32, 32, NULL, NULL, 'TOYOTA', 'WIGO', '', 'LAN7419', NULL, '', 'WHITE', '2026-09-24 03:24:11', '2026-09-24 03:24:30'),
(33, 33, NULL, NULL, 'TOYOTA', 'COROLLA ALTIS CTV', '', 'LGZ 518', NULL, '84466', 'BLACK', '2026-09-26 11:33:21', '2026-09-26 11:33:21'),
(34, 34, NULL, NULL, 'NISSAN', 'NAVARA', '', 'LAC 4956', NULL, '103,761', '', '2026-09-26 11:47:58', '2026-09-26 11:47:58'),
(35, 35, NULL, NULL, 'SUZUKI', 'MINI VAN', '', 'KBG 9742', NULL, '', '', '2026-09-28 03:39:50', '2026-09-28 03:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `work_sessions`
--

CREATE TABLE `work_sessions` (
  `id` int(11) NOT NULL,
  `job_order_technician_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_sessions`
--

INSERT INTO `work_sessions` (`id`, `job_order_technician_id`, `start_time`, `end_time`, `duration`, `notes`, `created_at`) VALUES
(19, 9, '2026-09-01 09:23:08', '2026-09-01 10:39:36', 4588, 'WAITING TROUBLESHOOTING SA SWEITCH', '2026-09-01 01:23:08'),
(20, 10, '2026-09-01 09:33:13', '2026-09-01 10:39:36', 3983, 'WAITING TROUBLESHOOTING SA SWEITCH', '2026-09-01 01:33:13'),
(21, 12, '2026-09-01 10:22:59', '2026-09-01 12:02:09', 5950, 'LUNCH', '2026-09-01 02:22:59'),
(22, 13, '2026-09-01 10:22:59', '2026-09-01 12:02:09', 5950, 'LUNCH', '2026-09-01 02:22:59'),
(23, 12, '2026-09-01 13:15:56', '2026-09-01 14:43:16', 11190, NULL, '2026-09-01 05:15:56'),
(24, 13, '2026-09-01 13:15:56', '2026-09-01 14:43:16', 11190, NULL, '2026-09-01 05:15:56'),
(25, 9, '2026-09-01 13:17:42', '2026-09-01 13:28:21', 5227, NULL, '2026-09-01 05:17:42'),
(26, 10, '2026-09-01 13:17:42', '2026-09-01 13:28:21', 4622, NULL, '2026-09-01 05:17:42'),
(27, 14, '2026-09-01 16:08:39', '2026-09-01 16:25:27', 1008, NULL, '2026-09-01 08:08:39'),
(28, 15, '2026-09-02 08:50:43', '2026-09-02 12:02:02', 11479, 'LUNCH', '2026-09-02 00:50:43'),
(29, 16, '2026-09-02 08:50:43', '2026-09-02 12:02:02', 11479, 'LUNCH', '2026-09-02 00:50:43'),
(30, 15, '2026-09-02 13:07:54', '2026-09-02 16:19:23', 22968, NULL, '2026-09-02 05:07:54'),
(31, 16, '2026-09-02 13:07:54', '2026-09-02 16:19:23', 22968, NULL, '2026-09-02 05:07:54'),
(32, 17, '2026-09-02 14:46:25', '2026-09-02 16:19:19', 5574, NULL, '2026-09-02 06:46:25'),
(33, 14, '2026-09-02 14:47:16', '2026-09-02 15:22:09', 3101, NULL, '2026-09-02 06:47:16'),
(34, 15, '2026-09-02 16:27:07', '2026-09-02 16:39:48', 23729, NULL, '2026-09-02 08:27:07'),
(35, 16, '2026-09-02 16:27:07', '2026-09-02 16:39:48', 23729, NULL, '2026-09-02 08:27:07'),
(36, 15, '2026-09-03 11:19:02', '2026-09-03 13:01:13', 29860, NULL, '2026-09-03 03:19:02'),
(37, 16, '2026-09-03 11:19:02', '2026-09-03 13:01:13', 29860, NULL, '2026-09-03 03:19:02'),
(38, 18, '2026-09-03 11:19:02', '2026-09-03 13:01:13', 6131, NULL, '2026-09-03 03:19:02'),
(39, 17, '2026-09-03 11:19:04', '2026-09-03 13:01:10', 11700, NULL, '2026-09-03 03:19:04'),
(40, 15, '2026-09-03 13:17:25', '2026-09-03 16:21:51', 40926, NULL, '2026-09-03 05:17:25'),
(41, 16, '2026-09-03 13:17:25', '2026-09-03 16:21:51', 40926, NULL, '2026-09-03 05:17:25'),
(42, 18, '2026-09-03 13:17:25', '2026-09-03 16:21:51', 17197, NULL, '2026-09-03 05:17:25'),
(43, 17, '2026-09-03 13:17:27', '2026-09-03 13:23:39', 12072, NULL, '2026-09-03 05:17:27'),
(44, 14, '2026-09-03 13:48:57', '2026-09-03 16:28:35', 12679, NULL, '2026-09-03 05:48:57'),
(45, 19, '2026-09-04 09:53:02', '2026-09-04 10:32:35', 2373, NULL, '2026-09-04 01:53:02'),
(46, 19, '2026-09-04 14:48:06', '2026-09-04 15:39:04', 5431, NULL, '2026-09-04 06:48:06'),
(47, 20, '2026-09-04 15:52:39', '2026-09-04 17:26:06', 5607, NULL, '2026-09-04 07:52:39'),
(48, 21, '2026-09-04 15:52:39', '2026-09-04 17:26:06', 5607, NULL, '2026-09-04 07:52:39'),
(49, 22, '2026-09-05 09:37:16', '2026-09-05 12:17:57', 9641, NULL, '2026-09-05 01:37:16'),
(50, 20, '2026-09-05 11:06:45', '2026-09-05 11:44:35', 7877, NULL, '2026-09-05 03:06:45'),
(51, 21, '2026-09-05 11:06:45', '2026-09-05 11:44:35', 7877, NULL, '2026-09-05 03:06:45'),
(52, 23, '2026-09-05 14:42:27', '2026-09-05 15:26:21', 2634, NULL, '2026-09-05 06:42:27'),
(53, 20, '2026-09-05 14:45:51', '2026-09-05 15:31:29', 10615, NULL, '2026-09-05 06:45:51'),
(54, 21, '2026-09-05 14:45:51', '2026-09-05 15:31:29', 10615, NULL, '2026-09-05 06:45:51'),
(55, 19, '2026-09-05 17:47:51', '2026-09-05 17:47:58', 5438, NULL, '2026-09-05 09:47:51'),
(56, 24, '2026-09-07 10:15:11', '2026-09-07 11:27:24', 4333, NULL, '2026-09-07 02:15:11'),
(57, 25, '2026-09-07 10:15:11', '2026-09-07 11:27:24', 4333, NULL, '2026-09-07 02:15:11'),
(58, 26, '2026-09-08 08:25:02', '2026-09-08 10:16:56', 6714, NULL, '2026-09-08 00:25:02'),
(59, 19, '2026-09-08 08:31:33', '2026-09-08 10:39:03', 13088, NULL, '2026-09-08 00:31:33'),
(60, 27, '2026-09-08 12:58:15', '2026-09-08 13:40:04', 2509, NULL, '2026-09-08 04:58:15'),
(61, 28, '2026-09-08 14:34:43', '2026-09-08 15:36:49', 3726, NULL, '2026-09-08 06:34:43'),
(62, 29, '2026-09-08 14:34:43', '2026-09-08 15:36:49', 3726, NULL, '2026-09-08 06:34:43'),
(63, 28, '2026-09-09 11:20:44', '2026-09-09 13:24:36', 11158, NULL, '2026-09-09 03:20:44'),
(64, 29, '2026-09-09 11:20:44', '2026-09-09 13:24:36', 11158, NULL, '2026-09-09 03:20:44'),
(65, 30, '2026-09-09 14:31:48', '2026-09-09 15:59:59', 5291, NULL, '2026-09-09 06:31:48'),
(66, 31, '2026-09-10 15:22:12', '2026-09-11 09:16:19', 64447, NULL, '2026-09-10 07:22:12'),
(67, 32, '2026-09-12 11:18:00', '2026-09-12 15:02:14', 13454, NULL, '2026-09-12 03:18:00'),
(68, 33, '2026-09-15 08:37:58', '2026-09-15 11:20:43', 9765, NULL, '2026-09-15 00:37:58'),
(69, 34, '2026-09-16 13:08:21', '2026-09-16 15:38:54', 9033, NULL, '2026-09-16 05:08:22'),
(70, 35, '2026-09-17 09:53:46', '2026-09-17 11:38:42', 6296, NULL, '2026-09-17 01:53:46'),
(71, 36, '2026-09-17 09:53:46', '2026-09-17 11:38:42', 6296, NULL, '2026-09-17 01:53:46'),
(72, 37, '2026-09-17 10:01:56', '2026-09-17 11:38:39', 5803, NULL, '2026-09-17 02:01:56'),
(73, 39, '2026-09-21 07:25:01', '2026-09-21 15:23:20', 28699, NULL, '2026-09-20 23:25:01'),
(74, 40, '2026-09-21 15:21:10', '2026-09-21 15:53:47', 1957, NULL, '2026-09-21 07:21:10'),
(75, 41, '2026-09-23 15:12:31', '2026-09-24 11:24:43', 72732, NULL, '2026-09-23 07:12:31'),
(76, 42, '2026-09-24 11:24:17', '2026-09-24 13:24:02', 7185, NULL, '2026-09-24 03:24:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_staff` (`staff_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_staff_id` (`staff_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bundle_products`
--
ALTER TABLE `bundle_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bundle_product_unique` (`bundle_id`,`product_id`),
  ADD KEY `bundle_id` (`bundle_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `bundle_services`
--
ALTER TABLE `bundle_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bundle_service` (`bundle_id`,`service_id`),
  ADD KEY `idx_service` (`service_id`);

--
-- Indexes for table `csrf_tokens`
--
ALTER TABLE `csrf_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_code` (`customer_code`),
  ADD KEY `idx_phone` (`phone`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_type` (`transaction_type`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`);

--
-- Indexes for table `job_estimates`
--
ALTER TABLE `job_estimates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `estimate_number` (`estimate_number`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `job_orders`
--
ALTER TABLE `job_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_order_number` (`job_order_number`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_vehicle` (`vehicle_id`),
  ADD KEY `idx_adviser` (`service_adviser_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `job_order_approvals`
--
ALTER TABLE `job_order_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_order` (`job_order_id`),
  ADD KEY `idx_reviewer` (`reviewer_id`);

--
-- Indexes for table `job_order_inspections`
--
ALTER TABLE `job_order_inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jo_id` (`job_order_id`),
  ADD KEY `idx_result` (`result`);

--
-- Indexes for table `job_order_payments`
--
ALTER TABLE `job_order_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_order` (`job_order_id`);

--
-- Indexes for table `job_order_products`
--
ALTER TABLE `job_order_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_order` (`job_order_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `job_order_services`
--
ALTER TABLE `job_order_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_order` (`job_order_id`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_bundle` (`bundle_id`);

--
-- Indexes for table `job_order_status_history`
--
ALTER TABLE `job_order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jo_id` (`job_order_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `job_order_technicians`
--
ALTER TABLE `job_order_technicians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_order` (`job_order_id`),
  ADD KEY `idx_technician` (`technician_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_staff` (`staff_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_order` (`job_order_id`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_received_by` (`received_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_code` (`permission_code`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_brand` (`brand_id`),
  ADD KEY `idx_unit` (`unit_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_supplier` (`supplier_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission` (`role`,`permission_id`),
  ADD KEY `idx_permission` (`permission_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_code` (`service_code`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `service_bundles`
--
ALTER TABLE `service_bundles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bundle_code` (`bundle_code`),
  ADD KEY `idx_type` (`bundle_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_code` (`category_code`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_id` (`staff_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_team` (`team_id`),
  ADD KEY `idx_supervisor` (`supervisor_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_transactions`
--
ALTER TABLE `supplier_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_date` (`transaction_date`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leader` (`team_leader_id`);

--
-- Indexes for table `technician_points`
--
ALTER TABLE `technician_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tech_id` (`technician_id`),
  ADD KEY `idx_jo_id` (`job_order_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_plate` (`plate_number`);

--
-- Indexes for table `work_sessions`
--
ALTER TABLE `work_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assignment` (`job_order_technician_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=980;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bundle_products`
--
ALTER TABLE `bundle_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bundle_services`
--
ALTER TABLE `bundle_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `csrf_tokens`
--
ALTER TABLE `csrf_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `job_estimates`
--
ALTER TABLE `job_estimates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `job_orders`
--
ALTER TABLE `job_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `job_order_approvals`
--
ALTER TABLE `job_order_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_order_inspections`
--
ALTER TABLE `job_order_inspections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_order_payments`
--
ALTER TABLE `job_order_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `job_order_products`
--
ALTER TABLE `job_order_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT for table `job_order_services`
--
ALTER TABLE `job_order_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=294;

--
-- AUTO_INCREMENT for table `job_order_status_history`
--
ALTER TABLE `job_order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `job_order_technicians`
--
ALTER TABLE `job_order_technicians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2945;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `service_bundles`
--
ALTER TABLE `service_bundles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_transactions`
--
ALTER TABLE `supplier_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `technician_points`
--
ALTER TABLE `technician_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `work_sessions`
--
ALTER TABLE `work_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bundle_services`
--
ALTER TABLE `bundle_services`
  ADD CONSTRAINT `fk_bundle_services_bundle` FOREIGN KEY (`bundle_id`) REFERENCES `service_bundles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bundle_services_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `fk_inventory_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_orders`
--
ALTER TABLE `job_orders`
  ADD CONSTRAINT `fk_jo_adviser` FOREIGN KEY (`service_adviser_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_jo_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jo_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_order_approvals`
--
ALTER TABLE `job_order_approvals`
  ADD CONSTRAINT `fk_joa_job_order` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_joa_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_order_products`
--
ALTER TABLE `job_order_products`
  ADD CONSTRAINT `fk_jop_job_order` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jop_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_order_services`
--
ALTER TABLE `job_order_services`
  ADD CONSTRAINT `fk_jos_bundle` FOREIGN KEY (`bundle_id`) REFERENCES `service_bundles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_jos_job_order` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jos_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_order_technicians`
--
ALTER TABLE `job_order_technicians`
  ADD CONSTRAINT `fk_jot_job_order` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jot_technician` FOREIGN KEY (`technician_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_job_order` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_service_category` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `fk_staff_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `fk_team_leader` FOREIGN KEY (`team_leader_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicle_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_sessions`
--
ALTER TABLE `work_sessions`
  ADD CONSTRAINT `fk_ws_assignment` FOREIGN KEY (`job_order_technician_id`) REFERENCES `job_order_technicians` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
