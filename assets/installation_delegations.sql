-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2025 at 09:25 AM
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

--
-- Dumping data for table `installation_delegations`
--

INSERT INTO `installation_delegations` (`id`, `survey_id`, `site_id`, `vendor_id`, `delegated_by`, `delegation_date`, `expected_start_date`, `expected_completion_date`, `actual_start_date`, `installation_start_time`, `actual_completion_date`, `priority`, `installation_type`, `status`, `special_instructions`, `notes`, `hold_reason`, `updated_by`, `created_at`, `updated_at`) VALUES
(2, 4, 4, 1, 1, '2025-11-03 01:22:16', '2024-12-01', '2024-12-15', '2025-11-04 08:15:41', '2025-11-04 06:49:00', NULL, 'medium', 'standard', 'in_progress', 'Test direct delegation', 'do somet/hing', NULL, 4, '2025-11-03 06:52:16', '2025-11-04 08:15:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `installation_delegations`
--
ALTER TABLE `installation_delegations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delegated_by` (`delegated_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_survey_id` (`survey_id`),
  ADD KEY `idx_site_id` (`site_id`),
  ADD KEY `idx_vendor_id` (`vendor_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_delegation_date` (`delegation_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `installation_delegations`
--
ALTER TABLE `installation_delegations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `installation_delegations`
--
ALTER TABLE `installation_delegations`
  ADD CONSTRAINT `installation_delegations_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `site_surveys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_delegations_ibfk_2` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_delegations_ibfk_3` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_delegations_ibfk_4` FOREIGN KEY (`delegated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_delegations_ibfk_5` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
