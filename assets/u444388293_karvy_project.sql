-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 04, 2025 at 05:11 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u444388293_karvy_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'DELETE_USER', 'users', 4, '{\"id\":4,\"username\":\"techinstall_solutions\",\"email\":\"contact@techinstall.com\",\"phone\":null,\"password_hash\":\"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC\\/.og\\/at2.uheWG\\/igi\",\"plain_password\":\"password\",\"jwt_token\":\"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo0LCJ1c2VybmFtZSI6InRlY2hpbnN0YWxsX3NvbHV0aW9ucyIsInJvbGUiOiJ2ZW5kb3IiLCJpYXQiOjE3NjIxMDczMzMsImV4cCI6MTc2MjE5MzczM30.i0ZS_dlCJ68QJCIc4GPKUjVRn413XHajpMPOpNZlmG4\",\"role\":\"vendor\",\"vendor_id\":1,\"status\":\"active\",\"created_at\":\"2025-11-02 01:40:29\",\"updated_at\":\"2025-11-02 18:15:33\"}', NULL, '106.216.247.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.77 Mobile/15E148 Safari/604.1', '2025-11-04 16:08:11'),
(2, 1, 'DELETE_USER', 'users', 6, '{\"id\":6,\"username\":\"proinstall_corp\",\"email\":\"support@proinstall.com\",\"phone\":null,\"password_hash\":\"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC\\/.og\\/at2.uheWG\\/igi\",\"plain_password\":\"password\",\"jwt_token\":\"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo2LCJ1c2VybmFtZSI6InByb2luc3RhbGxfY29ycCIsInJvbGUiOiJ2ZW5kb3IiLCJpYXQiOjE3NjIxMjM2MTYsImV4cCI6MTc2MjIxMDAxNn0.wh6P_Ra5-cRNQW7GcpZI9bOIL9ZHSnkap-e-9OjZr1E\",\"role\":\"vendor\",\"vendor_id\":3,\"status\":\"active\",\"created_at\":\"2025-11-02 01:40:29\",\"updated_at\":\"2025-11-02 22:46:56\"}', NULL, '106.216.247.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.77 Mobile/15E148 Safari/604.1', '2025-11-04 16:08:17'),
(3, 1, 'DELETE_USER', 'users', 5, '{\"id\":5,\"username\":\"quickfix_services\",\"email\":\"info@quickfix.com\",\"phone\":null,\"password_hash\":\"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC\\/.og\\/at2.uheWG\\/igi\",\"plain_password\":\"password\",\"jwt_token\":\"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo1LCJ1c2VybmFtZSI6InF1aWNrZml4X3NlcnZpY2VzIiwicm9sZSI6InZlbmRvciIsImlhdCI6MTc2MjE3MTkyMywiZXhwIjoxNzYyMjU4MzIzfQ.ELtX50t-m1LoWuiVQrhBakHoQYUbPZwm11jAn48jxK8\",\"role\":\"vendor\",\"vendor_id\":2,\"status\":\"active\",\"created_at\":\"2025-11-02 01:40:29\",\"updated_at\":\"2025-11-03 12:12:03\"}', NULL, '106.216.247.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.77 Mobile/15E148 Safari/604.1', '2025-11-04 16:08:27'),
(4, 1, 'DELETE_USER', 'users', 7, '{\"id\":7,\"username\":\"fasttrack_installation\",\"email\":\"hello@fasttrack.com\",\"phone\":null,\"password_hash\":\"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC\\/.og\\/at2.uheWG\\/igi\",\"plain_password\":\"password\",\"jwt_token\":\"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo3LCJ1c2VybmFtZSI6ImZhc3R0cmFja19pbnN0YWxsYXRpb24iLCJyb2xlIjoidmVuZG9yIiwiaWF0IjoxNzYyMTExNjgwLCJleHAiOjE3NjIxOTgwODB9.NS7p1FlvvqPKHTwgdJjspSulwCXnK5uXe7T3iDxVUZE\",\"role\":\"vendor\",\"vendor_id\":4,\"status\":\"active\",\"created_at\":\"2025-11-02 01:40:29\",\"updated_at\":\"2025-11-02 19:28:00\"}', NULL, '106.216.247.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.77 Mobile/15E148 Safari/604.1', '2025-11-04 16:08:33'),
(5, 1, 'DELETE_USER', 'users', 8, '{\"id\":8,\"username\":\"elite_services\",\"email\":\"contact@eliteservices.com\",\"phone\":null,\"password_hash\":\"$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC\\/.og\\/at2.uheWG\\/igi\",\"plain_password\":\"password\",\"jwt_token\":\"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo4LCJ1c2VybmFtZSI6ImVsaXRlX3NlcnZpY2VzIiwicm9sZSI6InZlbmRvciIsImlhdCI6MTc2MjEyMzMzNSwiZXhwIjoxNzYyMjA5NzM1fQ.oUDkv4EDgdzE3KAydVjDRmd3ZwPlpoYno4B6DePYx7U\",\"role\":\"vendor\",\"vendor_id\":5,\"status\":\"active\",\"created_at\":\"2025-11-02 01:40:29\",\"updated_at\":\"2025-11-02 22:42:15\"}', NULL, '106.216.247.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.77 Mobile/15E148 Safari/604.1', '2025-11-04 16:08:38'),
(6, 1, 'DELETE_USER', 'users', 3, '{\"id\":3,\"username\":\"bobby bagga\",\"email\":\"bobby@email.com\",\"phone\":\"8736263545\",\"password_hash\":\"$2y$12$o.yCqH9FtTFvpL\\/rRFg7nu2kVio.Sv0zWR.S4izg8vBXTqQF\\/8GbW\",\"plain_password\":\"something@123\",\"jwt_token\":\"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjozLCJ1c2VybmFtZSI6ImJvYmJ5IGJhZ2dhIiwicm9sZSI6InZlbmRvciIsImlhdCI6MTc2MjAyMzY0NiwiZXhwIjoxNzYyMTEwMDQ2fQ.WRsZuOCJTv7F5vHfqZOrZJHHcFwh2iNb-_0Fssm8dwY\",\"role\":\"vendor\",\"vendor_id\":null,\"status\":\"active\",\"created_at\":\"2025-11-01 15:19:56\",\"updated_at\":\"2025-11-01 19:00:46\"}', NULL, '106.216.247.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.77 Mobile/15E148 Safari/604.1', '2025-11-04 16:08:47'),
(7, 1, 'DELETE_USER', 'users', 2, '{\"id\":2,\"username\":\"vikas\",\"email\":\"vikas@gmail.com\",\"phone\":null,\"password_hash\":\"$2y$12$qIltu8UH\\/9JQTVv2XeP9deprEPyd8vWdMs7QttIUNFcMoPtoIvfUS\",\"plain_password\":null,\"jwt_token\":null,\"role\":\"vendor\",\"vendor_id\":null,\"status\":\"active\",\"created_at\":\"2025-11-01 15:05:45\",\"updated_at\":\"2025-11-01 15:08:51\"}', NULL, '106.216.247.175', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/142.0.7444.77 Mobile/15E148 Safari/604.1', '2025-11-04 16:08:52'),
(8, 1, 'CREATE_USER', 'users', 9, NULL, '{\"username\":\"Ganesh Panchal\",\"email\":\"ganesh@gmail.com\",\"phone\":\"8888888888\",\"password\":\"password\",\"role\":\"vendor\",\"status\":\"active\",\"vendor_id\":1}', '2401:4900:8fca:61ff:481a:6762:bb75:49e4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 17:01:20');

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Canara Bank', 'active', '2025-11-03 09:02:27', '2025-11-03 09:02:27'),
(2, 'No Bank', 'active', '2025-11-03 09:07:02', '2025-11-03 09:07:02');

-- --------------------------------------------------------

--
-- Table structure for table `boq_items`
--

CREATE TABLE `boq_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(20) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `need_serial_number` tinyint(1) DEFAULT 0,
  `icon_class` varchar(60) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boq_items`
--

INSERT INTO `boq_items` (`id`, `item_name`, `item_code`, `description`, `unit`, `category`, `status`, `need_serial_number`, `icon_class`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(2, '6U Rack with One Additional Tray', 'RACK-6U-001', '6U Network Rack with Additional Tray for Equipment', 'Nos', 'Racks & Enclosures', 'active', 0, 'fas fa-server', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(3, '12U Rack with One Additional Tray', 'RACK-12U-001', '12U Network Rack with Additional Tray for Equipment', 'Nos', 'Racks & Enclosures', 'active', 0, 'fas fa-server', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(4, '24 Port Patch Panels', 'PP-24P-001', '24 Port Network Patch Panel', 'Nos', 'Network Components', 'active', 0, 'fas fa-plug', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(5, 'Patch Panel Labeling', 'LBL-PP-001', 'Labeling for Patch Panels', 'Set', 'Accessories', 'active', 0, 'fas fa-tags', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(6, 'Cable Manager', 'CM-001', 'Cable Management System', 'Nos', 'Accessories', 'active', 0, 'fas fa-project-diagram', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(7, '1m Patch Cord', 'PC-1M-001', '1 Meter Patch Cord Cable', 'Nos', 'Cables', 'active', 0, 'fas fa-ethernet', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(8, '5m Patch Cord', 'PC-5M-001', '5 Meter Patch Cord Cable', 'Nos', 'Cables', 'active', 0, 'fas fa-ethernet', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(9, 'I/O Box Kit - Face Plate', 'IO-FP-001', 'Input/Output Box Face Plate', 'Nos', 'I/O Components', 'active', 0, 'fas fa-square', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(10, 'I/O Box Kit - Back Box', 'IO-BB-001', 'Input/Output Box Back Box', 'Nos', 'I/O Components', 'active', 0, 'fas fa-cube', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(11, 'I/O Box Kit - IO Socket', 'IO-SOC-001', 'Input/Output Socket', 'Nos', 'I/O Components', 'active', 0, 'fas fa-plug', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(12, 'Cat6 23 AWG UTP Cable (per Meter)', 'CAT6-UTP-001', 'Category 6 UTP Cable 23 AWG', 'Meter', 'Cables', 'active', 0, 'fas fa-ethernet', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(13, '25 mm Conducting PVC Pipes (White)', 'PVC-25MM-001', '25mm White PVC Conducting Pipes', 'Meter', 'Conduits', 'active', 0, 'fas fa-grip-lines', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(14, 'Flexible Pipe (20mm)', 'FLEX-20MM-001', '20mm Flexible Conduit Pipe', 'Meter', 'Conduits', 'active', 0, 'fas fa-grip-lines', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(15, 'Cable Ties (200 mm)', 'CT-200MM-001', '200mm Cable Ties', 'Pcs', 'Accessories', 'active', 0, 'fas fa-link', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(16, 'Cable Ties (400 mm)', 'CT-400MM-001', '400mm Cable Ties', 'Pcs', 'Accessories', 'active', 0, 'fas fa-link', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(17, 'Screws 1/2', 'SCR-0.5-001', '1/2 inch Screws', 'Pcs', 'Hardware', 'active', 0, 'fas fa-tools', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(18, 'Screws 3.5', 'SCR-3.5-001', '3.5mm Screws', 'Pcs', 'Hardware', 'active', 0, 'fas fa-tools', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(19, 'PVC Pipe Saddles', 'PVC-SAD-001', 'PVC Pipe Mounting Saddles', 'Pcs', 'Hardware', 'active', 0, 'fas fa-grip-horizontal', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(20, 'L Slotted Angle', 'ANG-L-001', 'L-shaped Slotted Angle', 'Meter', 'Hardware', 'active', 0, 'fas fa-ruler-combined', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(21, '25mm Bend', 'BEND-25MM-001', '25mm Pipe Bend', 'Nos', 'Conduits', 'active', 0, 'fas fa-share', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(22, '3 Way Junction', 'JUN-3W-001', '3 Way Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-project-diagram', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(23, '4 Way Junction', 'JUN-4W-001', '4 Way Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-project-diagram', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(24, 'PVC Square Box 6x6', 'BOX-6X6-001', '6x6 PVC Square Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-square', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(25, 'PVC Square Box 5x5', 'BOX-5X5-001', '5x5 PVC Square Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-square', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(26, '1m Pole', 'POLE-1M-001', '1 Meter Mounting Pole', 'Nos', 'Mounting', 'active', 0, 'fas fa-grip-vertical', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(27, '2m Pole', 'POLE-2M-001', '2 Meter Mounting Pole', 'Nos', 'Mounting', 'active', 0, 'fas fa-grip-vertical', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(28, '3m Pole', 'POLE-3M-001', '3 Meter Mounting Pole', 'Nos', 'Mounting', 'active', 0, 'fas fa-grip-vertical', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(29, 'Jio Cameras', 'CAM-JIO-001', 'Jio Security Cameras', 'Nos', 'Cameras', 'active', 1, 'fas fa-video', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(30, 'Jio Bridge Devices', 'BRG-JIO-001', 'Jio Network Bridge Devices', 'Nos', 'Network Devices', 'active', 1, 'fas fa-wifi', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35'),
(31, '24 Port CISCO Meraki POE Switches', 'SW-CIS-24P-001', '24 Port CISCO Meraki POE Network Switch', 'Nos', 'Network Devices', 'active', 1, 'fas fa-network-wired', NULL, NULL, '2025-11-02 11:55:35', '2025-11-02 11:55:35');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `state_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `state_id`, `country_id`, `zone_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Bilaspur', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(2, 'Chamba', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(3, 'Hamirpur', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(4, 'Kangra', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(5, 'Kinnaur', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(6, 'Kullu', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(7, 'Lahaul and Spiti', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(8, 'Mandi', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(9, 'Shimla', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(10, 'Sirmaur', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(11, 'Solan', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(12, 'Una', 1, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(13, 'Amritsar', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(14, 'Barnala', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(15, 'Bathinda', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(16, 'Faridkot', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(17, 'Fatehgarh Sahib', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(18, 'Fazilka', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(19, 'Ferozepur', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(20, 'Gurdaspur', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(21, 'Hoshiarpur', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(22, 'Jalandhar', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(23, 'Kapurthala', 2, 1, 1, 'active', '2025-07-02 12:48:26', '2025-07-02 12:48:26'),
(24, 'Ludhiana', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(25, 'Mansa', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(26, 'Moga', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(27, 'Muktsar', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(28, 'Pathankot', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(29, 'Patiala', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(30, 'Rupnagar', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(31, 'Sangrur', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(32, 'Shaheed Bhagat Singh Nagar (Nawanshahr)', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(33, 'Sri Muktsar Sahib', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(34, 'Tarn Taran', 2, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(35, 'Ambala', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(36, 'Bhiwani', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(37, 'Charkhi Dadri', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(38, 'Faridabad', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(39, 'Fatehabad', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(40, 'Gurugram', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(41, 'Hisar', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(42, 'Jhajjar', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(43, 'Jind', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(44, 'Kaithal', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(45, 'Karnal', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(46, 'Kurukshetra', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(47, 'Mahendragarh', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(48, 'Nuh', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(49, 'Palwal', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(50, 'Panchkula', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(51, 'Panipat', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(52, 'Rewari', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(53, 'Rohtak', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(54, 'Sirsa', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(55, 'Sonipat', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(56, 'Yamunanagar', 3, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(57, 'Ajmer', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(58, 'Alwar', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(59, 'Balotra', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(60, 'Banswara', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(61, 'Baran', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(62, 'Barmer', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(63, 'Beawar', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(64, 'Bharatpur', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(65, 'Bhilwara', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(66, 'Bikaner', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(67, 'Bundi', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(68, 'Chittorgarh', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(69, 'Churu', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(70, 'Dausa', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(71, 'Deeg', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(72, 'Didwana-Kuchaman', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(73, 'Dholpur', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(74, 'Dungarpur', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(75, 'Ganganagar', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(76, 'Gangapur City', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(77, 'Hanumangarh', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(78, 'Jaipur', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(79, 'Jaipur Rural', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(80, 'Jaisalmer', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(81, 'Jalore', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(82, 'Jhalawar', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(83, 'Jhunjhunu', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(84, 'Jodhpur', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(85, 'Jodhpur Rural', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(86, 'Karauli', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(87, 'Kekri', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(88, 'Khairthal-Tijara', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(89, 'Kota', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(90, 'Kotputli-Behror', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(91, 'Nagaur', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(92, 'Pali', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(93, 'Phalodi', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(94, 'Pratapgarh', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(95, 'Rajsamand', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(96, 'Salumbar', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(97, 'Sanchore', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(98, 'Sawai Madhopur', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(99, 'Sikar', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(100, 'Sirohi', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(101, 'Shahpura', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(102, 'Tonk', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(103, 'Udaipur', 4, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(104, 'Agra', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(105, 'Aligarh', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(106, 'Ambedkar Nagar', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(107, 'Amethi', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(108, 'Amroha', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(109, 'Auraiya', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(110, 'Ayodhya', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(111, 'Azamgarh', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(112, 'Baghpat', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(113, 'Bahraich', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(114, 'Ballia', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(115, 'Balrampur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(116, 'Banda', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(117, 'Barabanki', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(118, 'Bareilly', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(119, 'Basti', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(120, 'Bhadohi', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(121, 'Bijnor', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(122, 'Budaun', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(123, 'Bulandshahr', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(124, 'Chandauli', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(125, 'Chitrakoot', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(126, 'Deoria', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(127, 'Etah', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(128, 'Etawah', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(129, 'Farrukhabad', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(130, 'Fatehpur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(131, 'Firozabad', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(132, 'Gautam Buddha Nagar', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(133, 'Ghaziabad', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(134, 'Ghazipur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(135, 'Gonda', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(136, 'Gorakhpur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(137, 'Hamirpur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(138, 'Hapur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(139, 'Hardoi', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(140, 'Hathras', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(141, 'Jalaun', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(142, 'Jaunpur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(143, 'Jhansi', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(144, 'Kannauj', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(145, 'Kanpur Dehat', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(146, 'Kanpur Nagar', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(147, 'Kasganj', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(148, 'Kaushambi', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(149, 'Kushinagar', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(150, 'Lakhimpur Kheri', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(151, 'Lalitpur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(152, 'Lucknow', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(153, 'Maharajganj', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(154, 'Mahoba', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(155, 'Mainpuri', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(156, 'Mathura', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(157, 'Mau', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(158, 'Meerut', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(159, 'Mirzapur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(160, 'Moradabad', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(161, 'Muzaffarnagar', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(162, 'Pilibhit', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(163, 'Pratapgarh', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(164, 'Prayagraj', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(165, 'Rae Bareli', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(166, 'Rampur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(167, 'Saharanpur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(168, 'Sambhal', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(169, 'Sant Kabir Nagar', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(170, 'Sant Ravidas Nagar (Bhadohi)', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(171, 'Shahjahanpur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(172, 'Shamli', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(173, 'Shravasti', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(174, 'Siddharthnagar', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(175, 'Sitapur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(176, 'Sonbhadra', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(177, 'Sultanpur', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(178, 'Unnao', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(179, 'Varanasi', 5, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(180, 'Almora', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(181, 'Bageshwar', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(182, 'Chamoli', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(183, 'Champawat', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(184, 'Dehradun', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(185, 'Haridwar', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(186, 'Nainital', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(187, 'Pauri Garhwal', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(188, 'Pithoragarh', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(189, 'Rudraprayag', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(190, 'Tehri Garhwal', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(191, 'Udham Singh Nagar', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(192, 'Uttarkashi', 6, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(193, 'Anantnag', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(194, 'Bandipora', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(195, 'Baramulla', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(196, 'Budgam', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(197, 'Doda', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(198, 'Ganderbal', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(199, 'Jammu', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(200, 'Kathua', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(201, 'Kishtwar', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(202, 'Kulgam', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(203, 'Kupwara', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(204, 'Poonch', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(205, 'Pulwama', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(206, 'Rajouri', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(207, 'Ramban', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(208, 'Reasi', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(209, 'Samba', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(210, 'Shopian', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(211, 'Udhampur', 7, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(212, 'Kargil', 8, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(213, 'Leh', 8, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(214, 'Chandigarh', 9, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(215, 'Central Delhi', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(216, 'East Delhi', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(217, 'New Delhi', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(218, 'North Delhi', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(219, 'North East Delhi', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(220, 'North West Delhi', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(221, 'Shahdara', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(222, 'South Delhi', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(223, 'South East Delhi', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(224, 'South West Delhi', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(225, 'West Delhi', 10, 1, 1, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(226, 'Alluri Sitharama Raju', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(227, 'Anakapalli', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(228, 'Anantapur', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(229, 'Annamayya', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(230, 'Bapatla', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(231, 'Chittoor', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(232, 'Dr. B.R. Ambedkar Konaseema', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(233, 'East Godavari', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(234, 'Eluru', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(235, 'Guntur', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(236, 'Kakinada', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(237, 'Krishna', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(238, 'Kurnool', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(239, 'Nandyal', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(240, 'Nellore', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(241, 'NTR', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(242, 'Palnadu', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(243, 'Parvathipuram Manyam', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(244, 'Prakasam', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(245, 'Srikakulam', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(246, 'Sri Sathya Sai', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(247, 'Tirupati', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(248, 'Visakhapatnam', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(249, 'Vizianagaram', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(250, 'West Godavari', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(251, 'YSR Kadapa', 11, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(252, 'Bagalkot', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(253, 'Ballari', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(254, 'Belagavi', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(255, 'Bengaluru Rural', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(256, 'Bengaluru Urban', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(257, 'Bidar', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(258, 'Chamarajanagar', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(259, 'Chikkaballapur', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(260, 'Chikkamagaluru', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(261, 'Chitradurga', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(262, 'Dakshina Kannada', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(263, 'Davangere', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(264, 'Dharwad', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(265, 'Gadag', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(266, 'Kalaburagi', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(267, 'Hassan', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(268, 'Haveri', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(269, 'Kodagu', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(270, 'Kolar', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(271, 'Koppal', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(272, 'Mandya', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(273, 'Mysuru', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(274, 'Raichur', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(275, 'Ramanagara', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(276, 'Shivamogga', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(277, 'Tumakuru', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(278, 'Udupi', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(279, 'Uttara Kannada', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(280, 'Vijayapura', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(281, 'Yadgir', 12, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(282, 'Alappuzha', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(283, 'Ernakulam', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(284, 'Idukki', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(285, 'Kannur', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(286, 'Kasaragod', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(287, 'Kollam', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(288, 'Kottayam', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(289, 'Kozhikode', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(290, 'Malappuram', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(291, 'Palakkad', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(292, 'Pathanamthitta', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(293, 'Thiruvananthapuram', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(294, 'Thrissur', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(295, 'Wayanad', 13, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(296, 'Ariyalur', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(297, 'Chengalpattu', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(298, 'Chennai', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(299, 'Coimbatore', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(300, 'Cuddalore', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(301, 'Dharmapuri', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(302, 'Dindigul', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(303, 'Erode', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(304, 'Kallakurichi', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(305, 'Kancheepuram', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(306, 'Kanyakumari', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(307, 'Karur', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(308, 'Krishnagiri', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(309, 'Madurai', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(310, 'Mayiladuthurai', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(311, 'Nagapattinam', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(312, 'Namakkal', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(313, 'Nilgiris', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(314, 'Perambalur', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(315, 'Pudukkottai', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(316, 'Ramanathapuram', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(317, 'Ranipet', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(318, 'Salem', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(319, 'Sivaganga', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(320, 'Tenkasi', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(321, 'Thanjavur', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(322, 'Theni', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(323, 'Thoothukudi', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(324, 'Tiruchirappalli', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(325, 'Tirunelveli', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(326, 'Tirupattur', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(327, 'Tiruppur', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(328, 'Tiruvallur', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(329, 'Tiruvannamalai', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(330, 'Tiruvarur', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(331, 'Vellore', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(332, 'Viluppuram', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(333, 'Virudhunagar', 14, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(334, 'Adilabad', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(335, 'Bhadradri Kothagudem', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(336, 'Hanamkonda', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(337, 'Hyderabad', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(338, 'Jagtial', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(339, 'Jangaon', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(340, 'Jayashankar Bhupalpally', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(341, 'Jogulamba Gadwal', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(342, 'Kamareddy', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(343, 'Karimnagar', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(344, 'Khammam', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(345, 'Komaram Bheem Asifabad', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(346, 'Mahabubabad', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(347, 'Mahabubnagar', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(348, 'Mancherial', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(349, 'Medak', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(350, 'Medchal-Malkajgiri', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(351, 'Mulugu', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(352, 'Nagarkurnool', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(353, 'Nalgonda', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(354, 'Narayanpet', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(355, 'Nirmal', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(356, 'Nizamabad', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(357, 'Peddapalli', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(358, 'Rajanna Sircilla', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(359, 'Rangareddy', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(360, 'Sangareddy', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(361, 'Siddipet', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(362, 'Suryapet', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(363, 'Vikarabad', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(364, 'Wanaparthy', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(365, 'Warangal', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(366, 'Yadadri Bhuvanagiri', 15, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(367, 'Karaikal', 16, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(368, 'Mahe', 16, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(369, 'Puducherry', 16, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(370, 'Yanam', 16, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(371, 'Lakshadweep', 17, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(372, 'Nicobar', 18, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(373, 'North and Middle Andaman', 18, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(374, 'South Andaman', 18, 1, 2, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(375, 'Araria', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(376, 'Arwal', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(377, 'Aurangabad', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(378, 'Banka', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(379, 'Begusarai', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(380, 'Bhagalpur', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(381, 'Bhojpur', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(382, 'Buxar', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(383, 'Darbhanga', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(384, 'East Champaran', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(385, 'Gaya', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(386, 'Gopalganj', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(387, 'Jamui', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(388, 'Jehanabad', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(389, 'Kaimur (Bhabua)', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(390, 'Katihar', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(391, 'Khagaria', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(392, 'Kishanganj', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(393, 'Lakhisarai', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(394, 'Madhepura', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(395, 'Madhubani', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(396, 'Munger', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(397, 'Muzaffarpur', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(398, 'Nalanda', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(399, 'Nawada', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(400, 'Patna', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(401, 'Purnia', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(402, 'Rohtas', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(403, 'Saharsa', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(404, 'Samastipur', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(405, 'Saran', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(406, 'Sheikhpura', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(407, 'Sheohar', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(408, 'Sitamarhi', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(409, 'Siwan', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(410, 'Supaul', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(411, 'Vaishali', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(412, 'West Champaran', 19, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(413, 'Bokaro', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(414, 'Chatra', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(415, 'Deoghar', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(416, 'Dhanbad', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(417, 'Dumka', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(418, 'East Singhbhum', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(419, 'Garhwa', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(420, 'Giridih', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(421, 'Godda', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(422, 'Gumla', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(423, 'Hazaribagh', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(424, 'Jamtara', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(425, 'Khunti', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(426, 'Koderma', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(427, 'Latehar', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(428, 'Lohardaga', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(429, 'Pakur', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(430, 'Palamu', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(431, 'Ramgarh', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(432, 'Ranchi', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(433, 'Sahebganj', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(434, 'Seraikela Kharsawan', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(435, 'Simdega', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(436, 'West Singhbhum', 20, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(437, 'Angul', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(438, 'Balangir', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(439, 'Balasore', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(440, 'Bargarh', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(441, 'Bhadrak', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(442, 'Boudh', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(443, 'Cuttack', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(444, 'Deogarh', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(445, 'Dhenkanal', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(446, 'Gajapati', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(447, 'Ganjam', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(448, 'Jagatsinghpur', 21, 1, 3, 'active', '2025-07-02 12:48:27', '2025-07-02 12:48:27'),
(449, 'Jajpur', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(450, 'Jharsuguda', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(451, 'Kalahandi', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(452, 'Kandhamal', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(453, 'Kendrapara', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(454, 'Kendujhar (Keonjhar)', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(455, 'Khordha', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(456, 'Koraput', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(457, 'Malkangiri', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(458, 'Mayurbhanj', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(459, 'Nabarangpur', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(460, 'Nayagarh', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(461, 'Nuapada', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(462, 'Puri', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(463, 'Rayagada', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(464, 'Sambalpur', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(465, 'Subarnapur (Sonepur)', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(466, 'Sundargarh', 21, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(467, 'Alipurduar', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(468, 'Bankura', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(469, 'Birbhum', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(470, 'Cooch Behar', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(471, 'Dakshin Dinajpur', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(472, 'Darjeeling', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(473, 'Hooghly', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(474, 'Howrah', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(475, 'Jalpaiguri', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(476, 'Jhargram', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(477, 'Kalimpong', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(478, 'Kolkata', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(479, 'Malda', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(480, 'Murshidabad', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(481, 'Nadia', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(482, 'North 24 Parganas', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(483, 'Paschim Bardhaman', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(484, 'Paschim Medinipur', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(485, 'Purba Bardhaman', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(486, 'Purba Medinipur', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(487, 'Purulia', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(488, 'South 24 Parganas', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(489, 'Uttar Dinajpur', 22, 1, 3, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(490, 'North Goa', 23, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(491, 'South Goa', 23, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(492, 'Ahmedabad', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(493, 'Amreli', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(494, 'Anand', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(495, 'Aravalli', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(496, 'Banaskantha', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(497, 'Bharuch', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(498, 'Bhavnagar', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(499, 'Botad', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(500, 'Chhota Udaipur', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(501, 'Dahod', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(502, 'Dang', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(503, 'Devbhoomi Dwarka', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(504, 'Gandhinagar', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(505, 'Gir Somnath', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(506, 'Jamnagar', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(507, 'Junagadh', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(508, 'Kheda', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(509, 'Kutch', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(510, 'Mahisagar', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(511, 'Mehsana', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(512, 'Morbi', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(513, 'Narmada', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(514, 'Navsari', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(515, 'Panchmahal', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(516, 'Patan', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(517, 'Porbandar', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(518, 'Rajkot', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(519, 'Sabarkantha', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(520, 'Surat', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(521, 'Surendranagar', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(522, 'Tapi', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(523, 'Vadodara', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(524, 'Valsad', 24, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(525, 'Ahmednagar', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(526, 'Akola', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(527, 'Amravati', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(528, 'Aurangabad (Chhatrapati Sambhajinagar)', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(529, 'Beed', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(530, 'Bhandara', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(531, 'Buldhana', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(532, 'Chandrapur', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(533, 'Dhule', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(534, 'Gadchiroli', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(535, 'Gondia', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(536, 'Hingoli', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(537, 'Jalgaon', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(538, 'Jalna', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(539, 'Kolhapur', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(540, 'Latur', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(541, 'Mumbai City', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(542, 'Mumbai Suburban', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(543, 'Nagpur', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(544, 'Nanded', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(545, 'Nandurbar', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(546, 'Nashik', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(547, 'Osmanabad (Dharashiv)', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(548, 'Palghar', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(549, 'Parbhani', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(550, 'Pune', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(551, 'Raigad', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(552, 'Ratnagiri', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(553, 'Sangli', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(554, 'Satara', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(555, 'Sindhudurg', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(556, 'Solapur', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(557, 'Thane', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(558, 'Wardha', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(559, 'Washim', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(560, 'Yavatmal', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(561, 'Dadra and Nagar Haveli', 26, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(562, 'Daman', 26, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(563, 'Diu', 26, 1, 4, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(564, 'Agar Malwa', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(565, 'Alirajpur', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(566, 'Anuppur', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(567, 'Ashoknagar', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(568, 'Balaghat', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(569, 'Barwani', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(570, 'Betul', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(571, 'Bhind', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(572, 'Bhopal', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(573, 'Burhanpur', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(574, 'Chhatarpur', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(575, 'Chhindwara', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(576, 'Damoh', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(577, 'Datia', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(578, 'Dewas', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(579, 'Dhar', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(580, 'Dindori', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(581, 'Guna', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(582, 'Gwalior', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(583, 'Harda', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(584, 'Indore', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(585, 'Jabalpur', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(586, 'Jhabua', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(587, 'Katni', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(588, 'Khandwa', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(589, 'Khargone', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(590, 'Maihar', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(591, 'Mandla', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(592, 'Mandsaur', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(593, 'Mauganj', 27, 1, 5, 'active', '2025-07-02 12:48:28', '2025-07-02 12:48:28'),
(594, 'Bangalore', 12, 1, NULL, 'active', '2025-11-04 15:48:47', '2025-11-04 15:48:47');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'India', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(4, 'Reliance', 'active', '2025-11-03 09:05:08', '2025-11-03 09:05:08');

-- --------------------------------------------------------

--
-- Table structure for table `daily_material_usage`
--

CREATE TABLE `daily_material_usage` (
  `id` int(11) NOT NULL,
  `installation_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `day_number` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `engineer_name` varchar(255) DEFAULT NULL,
  `quantity_used` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `work_report` text DEFAULT NULL,
  `is_checked_out` tinyint(1) DEFAULT 0,
  `checked_out_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_work_photos`
--

CREATE TABLE `daily_work_photos` (
  `id` int(11) NOT NULL,
  `installation_id` int(11) NOT NULL,
  `day_number` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` enum('image','video') NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `installation_delegations`
--

CREATE TABLE `installation_delegations` (
  `id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `delegated_by` int(11) NOT NULL,
  `delegation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expected_start_date` date DEFAULT NULL,
  `expected_completion_date` date DEFAULT NULL,
  `actual_start_date` timestamp NULL DEFAULT NULL,
  `installation_start_time` timestamp NULL DEFAULT NULL,
  `actual_completion_date` timestamp NULL DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `installation_type` enum('standard','complex','maintenance','upgrade') DEFAULT 'standard',
  `status` enum('assigned','acknowledged','in_progress','on_hold','completed','cancelled') DEFAULT 'assigned',
  `special_instructions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `hold_reason` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `installation_materials`
--

CREATE TABLE `installation_materials` (
  `id` int(11) NOT NULL,
  `installation_id` int(11) NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `material_unit` varchar(50) NOT NULL DEFAULT 'Nos',
  `total_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `used_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remaining_quantity` decimal(10,2) GENERATED ALWAYS AS (`total_quantity` - `used_quantity`) STORED,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `installation_notifications`
--

CREATE TABLE `installation_notifications` (
  `id` int(11) NOT NULL,
  `installation_id` int(11) NOT NULL,
  `notification_type` enum('assignment','status_update','overdue','completion') NOT NULL,
  `recipient_type` enum('vendor','admin','all') NOT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `installation_progress`
--

CREATE TABLE `installation_progress` (
  `id` int(11) NOT NULL,
  `installation_id` int(11) NOT NULL,
  `progress_date` date NOT NULL,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `work_description` text DEFAULT NULL,
  `photos` text DEFAULT NULL,
  `issues_faced` text DEFAULT NULL,
  `next_steps` text DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `boq_item_id` int(11) NOT NULL,
  `current_stock` decimal(10,2) DEFAULT 0.00,
  `reserved_stock` decimal(10,2) DEFAULT 0.00,
  `unit_cost` decimal(10,2) DEFAULT 0.00,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_dispatches`
--

CREATE TABLE `inventory_dispatches` (
  `id` int(11) NOT NULL,
  `dispatch_number` varchar(50) NOT NULL,
  `dispatch_date` date NOT NULL,
  `material_request_id` int(11) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `contact_person_name` varchar(255) NOT NULL,
  `contact_person_phone` varchar(20) DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `courier_name` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `dispatch_status` enum('prepared','dispatched','in_transit','delivered','returned') DEFAULT 'prepared',
  `total_items` int(11) DEFAULT 0,
  `total_value` decimal(12,2) DEFAULT 0.00,
  `dispatched_by` int(11) NOT NULL,
  `received_by_name` varchar(255) DEFAULT NULL,
  `received_by_signature` varchar(500) DEFAULT NULL,
  `delivery_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `delivery_date` date DEFAULT NULL,
  `delivery_time` time DEFAULT NULL,
  `received_by` varchar(255) DEFAULT NULL,
  `received_by_phone` varchar(20) DEFAULT NULL,
  `actual_delivery_address` text DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `lr_copy_path` varchar(500) DEFAULT NULL,
  `additional_documents` text DEFAULT NULL,
  `item_confirmations` text DEFAULT NULL,
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmation_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_dispatch_items`
--

CREATE TABLE `inventory_dispatch_items` (
  `id` int(11) NOT NULL,
  `dispatch_id` int(11) NOT NULL,
  `inventory_stock_id` int(11) NOT NULL,
  `boq_item_id` int(11) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `item_condition` enum('new','used','refurbished') DEFAULT 'new',
  `dispatch_notes` text DEFAULT NULL,
  `warranty_period` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_inwards`
--

CREATE TABLE `inventory_inwards` (
  `id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `receipt_date` date NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `supplier_contact` varchar(100) DEFAULT NULL,
  `purchase_order_number` varchar(100) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT 0.00,
  `received_by` int(11) NOT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_inward_items`
--

CREATE TABLE `inventory_inward_items` (
  `id` int(11) NOT NULL,
  `inward_id` int(11) NOT NULL,
  `boq_item_id` int(11) NOT NULL,
  `quantity_received` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(12,2) DEFAULT 0.00,
  `batch_number` varchar(100) DEFAULT NULL,
  `serial_numbers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`serial_numbers`)),
  `expiry_date` date DEFAULT NULL,
  `quality_status` enum('good','damaged','rejected') DEFAULT 'good',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL,
  `boq_item_id` int(11) NOT NULL,
  `movement_type` enum('inward','outward','adjustment','transfer','return') NOT NULL,
  `reference_type` enum('inward_receipt','dispatch','adjustment','transfer','return') NOT NULL,
  `reference_id` int(11) NOT NULL,
  `reference_number` varchar(100) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT 0.00,
  `total_value` decimal(12,2) DEFAULT 0.00,
  `from_location` varchar(255) DEFAULT NULL,
  `to_location` varchar(255) DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `serial_numbers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`serial_numbers`)),
  `movement_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_reconciliation`
--

CREATE TABLE `inventory_reconciliation` (
  `id` int(11) NOT NULL,
  `reconciliation_number` varchar(50) NOT NULL,
  `reconciliation_date` date NOT NULL,
  `reconciliation_type` enum('full','partial','cycle') DEFAULT 'partial',
  `status` enum('in_progress','completed','approved','rejected') DEFAULT 'in_progress',
  `total_items_checked` int(11) DEFAULT 0,
  `total_discrepancies` int(11) DEFAULT 0,
  `total_value_difference` decimal(12,2) DEFAULT 0.00,
  `conducted_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_reconciliation_items`
--

CREATE TABLE `inventory_reconciliation_items` (
  `id` int(11) NOT NULL,
  `reconciliation_id` int(11) NOT NULL,
  `boq_item_id` int(11) NOT NULL,
  `system_quantity` decimal(10,2) NOT NULL,
  `physical_quantity` decimal(10,2) NOT NULL,
  `difference_quantity` decimal(10,2) DEFAULT 0.00,
  `unit_cost` decimal(10,2) NOT NULL,
  `value_difference` decimal(12,2) DEFAULT 0.00,
  `discrepancy_reason` varchar(500) DEFAULT NULL,
  `action_taken` varchar(500) DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `location_checked` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_stock`
--

CREATE TABLE `inventory_stock` (
  `id` int(11) NOT NULL,
  `boq_item_id` int(11) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `unit_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `purchase_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `warranty_period` int(11) DEFAULT NULL,
  `location_type` enum('warehouse','vendor_site','in_transit','returned') DEFAULT 'warehouse',
  `location_id` int(11) DEFAULT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  `item_status` enum('available','dispatched','delivered','returned','damaged') DEFAULT 'available',
  `quality_status` enum('good','damaged','expired','under_repair') DEFAULT 'good',
  `supplier_name` varchar(255) DEFAULT NULL,
  `purchase_order_number` varchar(100) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `dispatch_id` int(11) DEFAULT NULL,
  `dispatched_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `inventory_summary`
-- (See below for the actual view)
--
CREATE TABLE `inventory_summary` (
`boq_item_id` int(11)
,`item_name` varchar(200)
,`item_code` varchar(50)
,`unit` varchar(20)
,`category` varchar(100)
,`icon_class` varchar(60)
,`available_stock` bigint(21)
,`dispatched_stock` bigint(21)
,`delivered_stock` bigint(21)
,`returned_stock` bigint(21)
,`damaged_stock` bigint(21)
,`total_stock` bigint(21)
,`avg_unit_cost` decimal(14,6)
,`available_value` decimal(32,2)
,`total_value` decimal(32,2)
,`warehouse_stock` bigint(21)
,`vendor_site_stock` bigint(21)
,`in_transit_stock` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_tracking`
--

CREATE TABLE `inventory_tracking` (
  `id` int(11) NOT NULL,
  `boq_item_id` int(11) NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `current_location_type` enum('warehouse','in_transit','site','vendor','returned','damaged') NOT NULL,
  `current_location_id` int(11) DEFAULT NULL,
  `current_location_name` varchar(255) DEFAULT NULL,
  `site_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `dispatch_id` int(11) DEFAULT NULL,
  `inward_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `status` enum('available','reserved','dispatched','installed','damaged','returned') DEFAULT 'available',
  `last_movement_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `movement_remarks` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `country` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `country`, `state`, `city`, `created_at`, `updated_at`) VALUES
(1, 'India', 'Maharashtra', 'Mumbai', '2025-11-01 14:33:15', '2025-11-01 14:33:15'),
(2, 'India', 'Maharashtra', 'Pune', '2025-11-01 14:33:15', '2025-11-01 14:33:15'),
(3, 'India', 'Karnataka', 'Bangalore', '2025-11-01 14:33:15', '2025-11-01 14:33:15'),
(4, 'India', 'Tamil Nadu', 'Chennai', '2025-11-01 14:33:15', '2025-11-01 14:33:15'),
(5, 'India', 'Delhi', 'New Delhi', '2025-11-01 14:33:15', '2025-11-01 14:33:15');

-- --------------------------------------------------------

--
-- Table structure for table `material_dispatches`
--

CREATE TABLE `material_dispatches` (
  `id` int(11) NOT NULL,
  `material_request_id` int(11) NOT NULL,
  `dispatch_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`dispatch_items`)),
  `courier_name` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `dispatch_date` date NOT NULL,
  `acknowledgment_status` enum('pending','received') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_requests`
--

CREATE TABLE `material_requests` (
  `id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `survey_id` int(11) DEFAULT NULL,
  `request_date` date NOT NULL,
  `required_date` date DEFAULT NULL,
  `request_notes` text DEFAULT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items`)),
  `status` enum('draft','pending','approved','dispatched','partially_dispatched','completed','rejected','cancelled') DEFAULT 'pending',
  `processed_by` int(11) DEFAULT NULL,
  `processed_date` datetime DEFAULT NULL,
  `dispatch_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dispatch_details`)),
  `created_date` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `parent_id`, `title`, `icon`, `url`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Dashboard', 'dashboard', '/admin/dashboard.php', 1, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(2, NULL, 'Sites', 'location', '/admin/sites/', 2, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(3, NULL, 'Admin', 'settings', NULL, 3, 'active', '2025-11-01 18:57:51', '2025-11-02 17:54:39'),
(4, NULL, 'Inventory', 'inventory', NULL, 4, 'active', '2025-11-01 18:57:51', '2025-11-02 17:54:39'),
(6, NULL, 'Reports', 'reports', '/admin/reports/', 6, 'active', '2025-11-01 18:57:51', '2025-11-02 17:54:39'),
(10, 3, 'Users', 'users', '/admin/users/', 1, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(11, 3, 'Location', 'location-sub', NULL, 2, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(12, 3, 'Business', 'business', NULL, 3, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(13, 3, 'BOQ', 'boq', '/admin/boq/', 4, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(20, 11, 'Countries', 'country', '/admin/masters/?type=countries', 1, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(21, 11, 'Zones', 'zone', '/admin/masters/?type=zones', 2, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(22, 11, 'States', 'state', '/admin/masters/?type=states', 3, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(23, 11, 'Cities', 'city', '/admin/masters/?type=cities', 4, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(30, 12, 'Banks', 'bank', '/admin/masters/?type=banks', 1, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(31, 12, 'Customers', 'customer', '/admin/masters/?type=customers', 2, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(32, 12, 'Vendors', 'vendor', '/admin/vendors/', 3, 'active', '2025-11-01 18:57:51', '2025-11-01 18:57:51'),
(41, 4, 'All Stocks', 'inventory', '/admin/inventory/', 1, 'active', '2025-11-02 17:23:19', '2025-11-02 17:23:19'),
(42, 4, 'Material Requests', 'requests', '/admin/requests/', 2, 'active', '2025-11-02 17:23:19', '2025-11-02 17:23:19'),
(43, 4, 'Material Received', 'location-sub', '/admin/inventory/inwards/', 3, 'active', '2025-11-02 17:23:19', '2025-11-02 17:23:19'),
(44, 4, 'Material Dispatches', 'business', '/admin/inventory/dispatches/', 4, 'active', '2025-11-02 17:23:19', '2025-11-02 17:23:19'),
(51, NULL, 'Surveys', 'reports', '/admin/surveys/', 5, 'active', '2025-11-02 17:23:55', '2025-11-02 17:54:39'),
(53, NULL, 'Installation', 'settings', '/admin/installations/', 60, 'active', '2025-11-03 06:56:01', '2025-11-03 06:56:01'),
(54, 53, 'All Installations', 'inventory', '/admin/installations/', 1, 'active', '2025-11-03 06:56:01', '2025-11-03 06:56:01'),
(55, 53, 'Active Installations', 'requests', '/admin/installations/?status=in_progress', 2, 'active', '2025-11-03 06:56:01', '2025-11-03 06:56:01'),
(56, 53, 'Completed Installations', 'reports', '/admin/installations/?status=completed', 3, 'active', '2025-11-03 06:56:01', '2025-11-03 06:56:01');

-- --------------------------------------------------------

--
-- Table structure for table `role_menu_permissions`
--

CREATE TABLE `role_menu_permissions` (
  `id` int(11) NOT NULL,
  `role` enum('admin','vendor') NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `can_access` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_menu_permissions`
--

INSERT INTO `role_menu_permissions` (`id`, `role`, `menu_item_id`, `can_access`, `created_at`) VALUES
(1, 'admin', 1, 1, '2025-11-01 18:57:51'),
(2, 'admin', 2, 1, '2025-11-01 18:57:51'),
(3, 'admin', 3, 1, '2025-11-01 18:57:51'),
(4, 'admin', 4, 1, '2025-11-01 18:57:51'),
(6, 'admin', 6, 1, '2025-11-01 18:57:51'),
(7, 'admin', 10, 1, '2025-11-01 18:57:51'),
(8, 'admin', 11, 1, '2025-11-01 18:57:51'),
(9, 'admin', 12, 1, '2025-11-01 18:57:51'),
(10, 'admin', 13, 1, '2025-11-01 18:57:51'),
(11, 'admin', 20, 1, '2025-11-01 18:57:51'),
(12, 'admin', 21, 1, '2025-11-01 18:57:51'),
(13, 'admin', 22, 1, '2025-11-01 18:57:51'),
(14, 'admin', 23, 1, '2025-11-01 18:57:51'),
(15, 'admin', 30, 1, '2025-11-01 18:57:51'),
(16, 'admin', 31, 1, '2025-11-01 18:57:51'),
(17, 'admin', 32, 1, '2025-11-01 18:57:51'),
(18, 'vendor', 1, 1, '2025-11-01 18:57:51'),
(19, 'vendor', 2, 1, '2025-11-01 18:57:51'),
(40, 'admin', 41, 1, '2025-11-02 17:23:19'),
(41, 'admin', 42, 1, '2025-11-02 17:23:19'),
(42, 'admin', 43, 1, '2025-11-02 17:23:19'),
(43, 'admin', 44, 1, '2025-11-02 17:23:19'),
(44, 'vendor', 41, 1, '2025-11-02 17:23:19'),
(45, 'vendor', 42, 1, '2025-11-02 17:23:19'),
(46, 'vendor', 43, 1, '2025-11-02 17:23:19'),
(47, 'vendor', 44, 1, '2025-11-02 17:23:19'),
(49, 'admin', 51, 1, '2025-11-02 17:23:55'),
(51, 'admin', 53, 1, '2025-11-03 06:56:01'),
(52, 'admin', 54, 1, '2025-11-03 06:56:01'),
(53, 'admin', 55, 1, '2025-11-03 06:56:01'),
(54, 'admin', 56, 1, '2025-11-03 06:56:01');

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE `sites` (
  `id` int(11) NOT NULL,
  `site_id` varchar(255) NOT NULL,
  `store_id` varchar(255) DEFAULT NULL,
  `location` text DEFAULT NULL,
  `city` varchar(60) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `state` varchar(60) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `country` varchar(60) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `branch` varchar(60) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `po_number` varchar(255) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `customer` varchar(255) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `bank` varchar(255) DEFAULT NULL,
  `bank_id` int(11) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `activity_status` varchar(100) DEFAULT NULL,
  `is_delegate` tinyint(1) DEFAULT 0,
  `delegated_vendor` varchar(255) DEFAULT NULL,
  `survey_status` tinyint(1) DEFAULT 0,
  `installation_status` tinyint(1) DEFAULT 0,
  `is_material_request_generated` tinyint(1) DEFAULT 0,
  `survey_submission_date` datetime DEFAULT NULL,
  `installation_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sites`
--

INSERT INTO `sites` (`id`, `site_id`, `store_id`, `location`, `city`, `city_id`, `state`, `state_id`, `country`, `country_id`, `branch`, `remarks`, `po_number`, `po_date`, `customer`, `customer_id`, `bank`, `bank_id`, `vendor`, `activity_status`, `is_delegate`, `delegated_vendor`, `survey_status`, `installation_status`, `is_material_request_generated`, `survey_submission_date`, `installation_date`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'SITE002', 'STORE002', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'Standard installation', 'PO2024002', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 1, 'Karvy Tech Solutions', 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 17:02:58', NULL),
(2, 'SITE003', 'STORE003', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024003', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(3, 'SITE004', 'STORE004', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024004', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(4, 'SITE005', 'STORE005', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024005', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(5, 'SITE006', 'STORE006', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024006', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(6, 'SITE007', 'STORE007', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024007', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(7, 'SITE008', 'STORE008', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024008', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(8, 'SITE009', 'STORE009', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024009', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(9, 'SITE010', 'STORE010', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024010', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(10, 'SITE011', 'STORE011', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024011', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(11, 'SITE012', 'STORE012', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024012', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(12, 'SITE013', 'STORE013', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024013', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(13, 'SITE014', 'STORE014', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024014', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(14, 'SITE015', 'STORE015', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024015', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(15, 'SITE016', 'STORE016', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024016', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(16, 'SITE017', 'STORE017', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024017', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL),
(17, 'SITE018', 'STORE018', '456 Commercial Road, IT Park', 'Bangalore', 594, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'High priority installation', 'PO2024018', '2024-01-20', 'Reliance', 4, 'Canara Bank', 1, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 15:49:35', 'bulk_upload', '2025-11-04 15:49:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `site_delegations`
--

CREATE TABLE `site_delegations` (
  `id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `delegated_by` int(11) NOT NULL,
  `delegation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_delegations`
--

INSERT INTO `site_delegations` (`id`, `site_id`, `vendor_id`, `delegated_by`, `delegation_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2025-11-04 17:02:58', 'active', 'some delegation note', '2025-11-04 17:02:58', '2025-11-04 17:02:58');

-- --------------------------------------------------------

--
-- Table structure for table `site_surveys`
--

CREATE TABLE `site_surveys` (
  `id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `delegation_id` int(11) DEFAULT NULL,
  `survey_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`survey_data`)),
  `survey_date` datetime DEFAULT NULL,
  `submitted_date` datetime DEFAULT current_timestamp(),
  `survey_status` enum('pending','completed','approved','rejected') DEFAULT 'pending',
  `installation_status` enum('not_delegated','delegated','in_progress','completed') DEFAULT 'not_delegated',
  `installation_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `approval_remarks` text DEFAULT NULL,
  `checkin_datetime` datetime DEFAULT NULL,
  `checkout_datetime` datetime DEFAULT NULL,
  `working_hours` varchar(50) DEFAULT NULL,
  `store_model` varchar(100) DEFAULT NULL,
  `floor_height` decimal(5,2) DEFAULT NULL,
  `floor_height_photos` text DEFAULT NULL,
  `ceiling_type` enum('False','Open') DEFAULT NULL,
  `ceiling_photos` text DEFAULT NULL,
  `total_cameras` int(11) DEFAULT NULL,
  `analytic_cameras` int(11) DEFAULT NULL,
  `analytic_photos` text DEFAULT NULL,
  `existing_poe_rack` int(11) DEFAULT NULL,
  `existing_poe_photos` text DEFAULT NULL,
  `space_new_rack` enum('Yes','No') DEFAULT NULL,
  `space_new_rack_photos` text DEFAULT NULL,
  `new_poe_rack` int(11) DEFAULT NULL,
  `new_poe_photos` text DEFAULT NULL,
  `zones_recommended` int(11) DEFAULT NULL,
  `rrl_delivery_status` enum('Yes','No') DEFAULT NULL,
  `rrl_photos` text DEFAULT NULL,
  `kptl_space` enum('Yes','No') DEFAULT NULL,
  `kptl_photos` text DEFAULT NULL,
  `site_accessibility` enum('good','moderate','poor') DEFAULT NULL,
  `power_availability` enum('available','partial','unavailable') DEFAULT NULL,
  `network_connectivity` enum('excellent','good','poor','none') DEFAULT NULL,
  `space_adequacy` enum('adequate','tight','inadequate') DEFAULT NULL,
  `technical_remarks` text DEFAULT NULL,
  `challenges_identified` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `estimated_completion_days` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `country_id` int(11) NOT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`, `country_id`, `zone_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Himachal Pradesh', 1, 1, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(2, 'Punjab', 1, 1, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(3, 'Haryana', 1, 1, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(4, 'Rajasthan', 1, 1, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(5, 'Uttar Pradesh', 1, 1, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(6, 'Uttarakhand', 1, 1, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(7, 'Jammu and Kashmir', 1, 1, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(8, 'Ladakh', 1, 1, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(9, 'Chandigarh', 1, 1, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(10, 'Delhi', 1, 1, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(11, 'Andhra Pradesh', 1, 2, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(12, 'Karnataka', 1, 2, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(13, 'Kerala', 1, 2, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(14, 'Tamil Nadu', 1, 2, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(15, 'Telangana', 1, 2, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(16, 'Puducherry', 1, 2, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(17, 'Lakshadweep', 1, 2, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(18, 'Andaman and Nicobar Islands', 1, 2, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(19, 'Bihar', 1, 3, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(20, 'Jharkhand', 1, 3, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(21, 'Odisha', 1, 3, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(22, 'West Bengal', 1, 3, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(23, 'Goa', 1, 4, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(24, 'Gujarat', 1, 4, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(25, 'Maharashtra', 1, 4, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(26, 'Dadra and Nagar Haveli and Daman and Diu', 1, 4, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25'),
(27, 'Madhya Pradesh', 1, 5, 'active', '2025-11-03 06:16:25', '2025-11-03 06:16:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `plain_password` varchar(255) DEFAULT NULL COMMENT 'For testing purposes only',
  `jwt_token` text DEFAULT NULL,
  `role` enum('admin','user','vendor') DEFAULT 'user',
  `vendor_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `password_hash`, `plain_password`, `jwt_token`, `role`, `vendor_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '+1234567890', '$2y$12$qIltu8UH/9JQTVv2XeP9deprEPyd8vWdMs7QttIUNFcMoPtoIvfUS', 'admin123', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxNzYyMjU4MzI0LCJleHAiOjE3NjIzNDQ3MjR9.lCv40JMJ6y27GgbqflyI8SVSt50SOte-TiNX28u9OuM', 'admin', NULL, 'active', '2025-11-01 14:33:15', '2025-11-04 12:12:04'),
(9, 'Ganesh Panchal', 'ganesh@gmail.com', '8888888888', '$2y$12$jB/RCc457pNn02q0wx7pRekgnXDASyJ9JltGXNUsYOia9r5d.tn1O', 'password', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo5LCJ1c2VybmFtZSI6IkdhbmVzaCBQYW5jaGFsIiwicm9sZSI6InZlbmRvciIsImlhdCI6MTc2MjI3NTc0MywiZXhwIjoxNzYyMzYyMTQzfQ.wFTLst2vKM_amRp0BsuYKaYanv-wYmmLA4m-q72kNhI', 'vendor', 1, 'active', '2025-11-04 17:01:20', '2025-11-04 17:02:23');

-- --------------------------------------------------------

--
-- Table structure for table `user_menu_permissions`
--

CREATE TABLE `user_menu_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `can_access` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_menu_permissions`
--

INSERT INTO `user_menu_permissions` (`id`, `user_id`, `menu_item_id`, `can_access`, `created_at`) VALUES
(25, 1, 1, 1, '2025-11-04 16:57:39'),
(26, 1, 2, 1, '2025-11-04 16:57:39'),
(27, 1, 3, 1, '2025-11-04 16:57:39'),
(28, 1, 4, 1, '2025-11-04 16:57:39'),
(29, 1, 51, 1, '2025-11-04 16:57:39'),
(30, 1, 6, 1, '2025-11-04 16:57:39'),
(31, 1, 53, 1, '2025-11-04 16:57:39'),
(32, 1, 10, 1, '2025-11-04 16:57:39'),
(33, 1, 11, 1, '2025-11-04 16:57:39'),
(34, 1, 12, 1, '2025-11-04 16:57:39'),
(35, 1, 13, 1, '2025-11-04 16:57:39'),
(36, 1, 41, 1, '2025-11-04 16:57:39'),
(37, 1, 42, 1, '2025-11-04 16:57:39'),
(38, 1, 43, 1, '2025-11-04 16:57:39'),
(39, 1, 44, 1, '2025-11-04 16:57:39'),
(40, 1, 20, 1, '2025-11-04 16:57:39'),
(41, 1, 21, 1, '2025-11-04 16:57:39'),
(42, 1, 22, 1, '2025-11-04 16:57:39'),
(43, 1, 23, 1, '2025-11-04 16:57:39'),
(44, 1, 30, 1, '2025-11-04 16:57:39'),
(45, 1, 31, 1, '2025-11-04 16:57:39'),
(46, 1, 32, 1, '2025-11-04 16:57:39'),
(47, 1, 54, 1, '2025-11-04 16:57:39'),
(48, 1, 55, 1, '2025-11-04 16:57:39'),
(49, 1, 56, 1, '2025-11-04 16:57:39');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `vendor_code` varchar(50) DEFAULT NULL,
  `mobility_id` varchar(100) DEFAULT NULL,
  `mobility_password` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `pan_card_number` varchar(20) DEFAULT NULL,
  `aadhaar_number` varchar(20) DEFAULT NULL,
  `msme_number` varchar(50) DEFAULT NULL,
  `esic_number` varchar(50) DEFAULT NULL,
  `pf_number` varchar(50) DEFAULT NULL,
  `pvc_status` enum('Yes','No') DEFAULT 'No',
  `experience_letter_path` varchar(500) DEFAULT NULL,
  `photograph_path` varchar(500) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `vendor_code`, `mobility_id`, `mobility_password`, `name`, `company_name`, `email`, `phone`, `address`, `bank_name`, `account_number`, `ifsc_code`, `gst_number`, `pan_card_number`, `aadhaar_number`, `msme_number`, `esic_number`, `pf_number`, `pvc_status`, `experience_letter_path`, `photograph_path`, `contact_person`, `status`, `created_at`, `updated_at`) VALUES
(1, 'VND0001', 'Karvy Tech', '$2y$12$yNmuDJ8YJwtTuQr4J/QX2OlKOxUfy2JdPx9zNFxNdUy040WkdoQ9q', 'Karvy Tech Solutions', 'Karvy Tech Solutions', 'karvy@gmail.com', '99999999999', 'Mumbai', 'NA', 'NA', 'NA', 'NA', 'NA', 'NA', 'NA', 'NA', 'NA', 'Yes', 'uploads/vendors/1/experience_letter_1762275608.jpg', 'uploads/vendors/1/photograph_1762275608.png', 'Karvy Tech Solutions', 'active', '2025-11-04 17:00:08', '2025-11-04 17:00:08');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_permissions`
--

CREATE TABLE `vendor_permissions` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `permission_value` tinyint(1) DEFAULT 0,
  `granted_by` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor_permissions`
--

INSERT INTO `vendor_permissions` (`id`, `vendor_id`, `permission_key`, `permission_value`, `granted_by`, `granted_at`, `updated_at`) VALUES
(1, 1, 'view_sites', 1, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(2, 2, 'view_sites', 1, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(3, 3, 'view_sites', 1, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(4, 4, 'view_sites', 1, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(5, 5, 'view_sites', 1, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(8, 1, 'update_progress', 1, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(9, 2, 'update_progress', 1, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(10, 3, 'update_progress', 1, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(11, 4, 'update_progress', 1, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(12, 5, 'update_progress', 1, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(15, 1, 'view_masters', 0, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(16, 2, 'view_masters', 0, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(17, 3, 'view_masters', 0, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(18, 4, 'view_masters', 0, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(19, 5, 'view_masters', 0, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(22, 1, 'view_reports', 0, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(23, 2, 'view_reports', 0, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(24, 3, 'view_reports', 0, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(25, 4, 'view_reports', 0, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44'),
(26, 5, 'view_reports', 0, 1, '2025-11-02 01:58:44', '2025-11-02 01:58:44');

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'North', 'active', '2025-11-01 18:47:44', '2025-11-03 06:12:10'),
(2, 'South', 'active', '2025-11-01 18:47:44', '2025-11-03 06:12:12'),
(3, 'East', 'active', '2025-11-01 18:47:44', '2025-11-03 06:12:20'),
(4, 'West', 'active', '2025-11-01 18:47:44', '2025-11-03 06:12:26'),
(5, 'Central', 'active', '2025-11-01 18:47:44', '2025-11-03 06:12:32'),
(6, 'Northeast', 'active', '2025-11-01 18:47:44', '2025-11-03 06:12:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `boq_items`
--
ALTER TABLE `boq_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_material_usage`
--
ALTER TABLE `daily_material_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_material_day` (`installation_id`,`material_id`,`day_number`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `idx_installation_day` (`installation_id`,`day_number`),
  ADD KEY `idx_work_date` (`work_date`);

--
-- Indexes for table `daily_work_photos`
--
ALTER TABLE `daily_work_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_installation_day` (`installation_id`,`day_number`);

--
-- Indexes for table `installation_materials`
--
ALTER TABLE `installation_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_installation_id` (`installation_id`);

--
-- Indexes for table `installation_notifications`
--
ALTER TABLE `installation_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `installation_progress`
--
ALTER TABLE `installation_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_installation_id` (`installation_id`),
  ADD KEY `idx_progress_date` (`progress_date`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_dispatches`
--
ALTER TABLE `inventory_dispatches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_dispatch_items`
--
ALTER TABLE `inventory_dispatch_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_inwards`
--
ALTER TABLE `inventory_inwards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_inward_items`
--
ALTER TABLE `inventory_inward_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_reconciliation`
--
ALTER TABLE `inventory_reconciliation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_reconciliation_items`
--
ALTER TABLE `inventory_reconciliation_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_stock`
--
ALTER TABLE `inventory_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_tracking`
--
ALTER TABLE `inventory_tracking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_dispatches`
--
ALTER TABLE `material_dispatches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_requests`
--
ALTER TABLE `material_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_menu_permissions`
--
ALTER TABLE `role_menu_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_delegations`
--
ALTER TABLE `site_delegations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_surveys`
--
ALTER TABLE `site_surveys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_menu_permissions`
--
ALTER TABLE `user_menu_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_permissions`
--
ALTER TABLE `vendor_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `boq_items`
--
ALTER TABLE `boq_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=595;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `daily_material_usage`
--
ALTER TABLE `daily_material_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_work_photos`
--
ALTER TABLE `daily_work_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `installation_materials`
--
ALTER TABLE `installation_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `installation_notifications`
--
ALTER TABLE `installation_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `installation_progress`
--
ALTER TABLE `installation_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_dispatches`
--
ALTER TABLE `inventory_dispatches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_dispatch_items`
--
ALTER TABLE `inventory_dispatch_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_inwards`
--
ALTER TABLE `inventory_inwards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_inward_items`
--
ALTER TABLE `inventory_inward_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_reconciliation`
--
ALTER TABLE `inventory_reconciliation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_reconciliation_items`
--
ALTER TABLE `inventory_reconciliation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_stock`
--
ALTER TABLE `inventory_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_tracking`
--
ALTER TABLE `inventory_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `material_dispatches`
--
ALTER TABLE `material_dispatches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `material_requests`
--
ALTER TABLE `material_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `role_menu_permissions`
--
ALTER TABLE `role_menu_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `sites`
--
ALTER TABLE `sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `site_delegations`
--
ALTER TABLE `site_delegations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `site_surveys`
--
ALTER TABLE `site_surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_menu_permissions`
--
ALTER TABLE `user_menu_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vendor_permissions`
--
ALTER TABLE `vendor_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

-- --------------------------------------------------------

--
-- Structure for view `inventory_summary`
--
DROP TABLE IF EXISTS `inventory_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u444388293_karvy_project`@`127.0.0.1` SQL SECURITY INVOKER VIEW `inventory_summary`  AS SELECT `bi`.`id` AS `boq_item_id`, `bi`.`item_name` AS `item_name`, `bi`.`item_code` AS `item_code`, `bi`.`unit` AS `unit`, `bi`.`category` AS `category`, `bi`.`icon_class` AS `icon_class`, count(case when `ist`.`item_status` = 'available' then 1 end) AS `available_stock`, count(case when `ist`.`item_status` = 'dispatched' then 1 end) AS `dispatched_stock`, count(case when `ist`.`item_status` = 'delivered' then 1 end) AS `delivered_stock`, count(case when `ist`.`item_status` = 'returned' then 1 end) AS `returned_stock`, count(case when `ist`.`item_status` = 'damaged' then 1 end) AS `damaged_stock`, count(0) AS `total_stock`, avg(`ist`.`unit_cost`) AS `avg_unit_cost`, sum(case when `ist`.`item_status` = 'available' then `ist`.`unit_cost` else 0 end) AS `available_value`, sum(`ist`.`unit_cost`) AS `total_value`, count(case when `ist`.`location_type` = 'warehouse' then 1 end) AS `warehouse_stock`, count(case when `ist`.`location_type` = 'vendor_site' then 1 end) AS `vendor_site_stock`, count(case when `ist`.`location_type` = 'in_transit' then 1 end) AS `in_transit_stock` FROM (`boq_items` `bi` left join `inventory_stock` `ist` on(`bi`.`id` = `ist`.`boq_item_id`)) WHERE `bi`.`status` = 'active' GROUP BY `bi`.`id`, `bi`.`item_name`, `bi`.`item_code`, `bi`.`unit`, `bi`.`category`, `bi`.`icon_class` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_material_usage`
--
ALTER TABLE `daily_material_usage`
  ADD CONSTRAINT `daily_material_usage_ibfk_1` FOREIGN KEY (`installation_id`) REFERENCES `installation_delegations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_material_usage_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `installation_materials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `daily_work_photos`
--
ALTER TABLE `daily_work_photos`
  ADD CONSTRAINT `daily_work_photos_ibfk_1` FOREIGN KEY (`installation_id`) REFERENCES `installation_delegations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `installation_materials`
--
ALTER TABLE `installation_materials`
  ADD CONSTRAINT `installation_materials_ibfk_1` FOREIGN KEY (`installation_id`) REFERENCES `installation_delegations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `installation_progress`
--
ALTER TABLE `installation_progress`
  ADD CONSTRAINT `installation_progress_ibfk_1` FOREIGN KEY (`installation_id`) REFERENCES `installation_delegations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_progress_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
