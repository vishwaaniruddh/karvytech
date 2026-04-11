-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2025 at 09:17 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `site_installation_management`
--

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

--
-- Dumping data for table `daily_material_usage`
--

INSERT INTO `daily_material_usage` (`id`, `installation_id`, `material_id`, `day_number`, `work_date`, `engineer_name`, `quantity_used`, `remarks`, `work_report`, `is_checked_out`, `checked_out_at`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 1, '2025-11-04', '', 1.00, 'some', 'something', 1, '2025-11-04 07:30:41', '2025-11-04 07:30:39', '2025-11-04 07:30:41'),
(2, 2, 6, 1, '2025-11-04', '', 30.00, 'some', 'something', 1, '2025-11-04 07:30:41', '2025-11-04 07:30:39', '2025-11-04 07:30:41'),
(3, 2, 2, 2, '2025-11-04', '', 1.00, '', '', 1, '2025-11-04 07:55:54', '2025-11-04 07:55:53', '2025-11-04 07:55:54');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daily_material_usage`
--
ALTER TABLE `daily_material_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_material_usage`
--
ALTER TABLE `daily_material_usage`
  ADD CONSTRAINT `daily_material_usage_ibfk_1` FOREIGN KEY (`installation_id`) REFERENCES `installation_delegations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_material_usage_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `installation_materials` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
