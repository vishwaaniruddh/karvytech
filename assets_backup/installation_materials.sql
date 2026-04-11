-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2025 at 09:01 AM
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

--
-- Dumping data for table `installation_materials`
--

INSERT INTO `installation_materials` (`id`, `installation_id`, `material_name`, `material_unit`, `total_quantity`, `used_quantity`, `created_at`, `updated_at`) VALUES
(1, 2, '6U Rack with One Additional Tray', 'Nos', 2.00, 0.00, '2025-11-04 07:25:52', '2025-11-04 07:25:52'),
(2, 2, '12U Rack with One Additional Tray', 'Nos', 1.00, 2.00, '2025-11-04 07:25:52', '2025-11-04 07:55:54'),
(3, 2, '24 Port Patch Panels', 'Nos', 4.00, 0.00, '2025-11-04 07:25:52', '2025-11-04 07:25:52'),
(4, 2, 'Patch Panel Labeling', 'Labels', 100.00, 0.00, '2025-11-04 07:25:52', '2025-11-04 07:25:52'),
(5, 2, 'Cable Manager', 'Nos', 6.00, 0.00, '2025-11-04 07:25:52', '2025-11-04 07:25:52'),
(6, 2, '1m Patch Cord', 'Nos', 50.00, 30.00, '2025-11-04 07:25:52', '2025-11-04 07:30:41'),
(7, 2, '5m Patch Cord', 'Nos', 20.00, 0.00, '2025-11-04 07:25:52', '2025-11-04 07:25:52'),
(8, 2, 'I/O Box Kit - Face Plate', 'Nos', 10.00, 0.00, '2025-11-04 07:25:52', '2025-11-04 07:25:52'),
(9, 2, 'Cat6 Cable (305m)', 'Boxes', 2.00, 0.00, '2025-11-04 07:25:52', '2025-11-04 07:25:52'),
(10, 2, 'RJ45 Connectors', 'Nos', 100.00, 0.00, '2025-11-04 07:25:52', '2025-11-04 07:25:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `installation_materials`
--
ALTER TABLE `installation_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_installation_id` (`installation_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `installation_materials`
--
ALTER TABLE `installation_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `installation_materials`
--
ALTER TABLE `installation_materials`
  ADD CONSTRAINT `installation_materials_ibfk_1` FOREIGN KEY (`installation_id`) REFERENCES `installation_delegations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
