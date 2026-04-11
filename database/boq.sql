-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 02, 2025 at 11:36 AM
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
-- Database: `u444388293_advantage`
--

-- --------------------------------------------------------

--
-- Table structure for table `boq`
--

CREATE TABLE `boq` (
  `id` int(11) NOT NULL,
  `attribute` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `count` int(6) DEFAULT NULL,
  `status` int(6) NOT NULL,
  `needSerialNumber` varchar(10) DEFAULT NULL,
  `iconImageClass` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `boq`
--

INSERT INTO `boq` (`id`, `attribute`, `value`, `count`, `status`, `needSerialNumber`, `iconImageClass`) VALUES
(2, '6U Rack with One Additional Tray', '6U Rack with One Additional Tray', 1, 1, '0', ''),
(3, '12U Rack with One Additional Tray', '12U Rack with One Additional Tray', 1, 1, '0', ''),
(4, '24 Port Patch Panels', '24 Port Patch Panels', 1, 1, '0', ''),
(5, 'Patch Panel Labeling', 'Patch Panel Labeling', 1, 1, '0', ''),
(6, 'Cable Manager', 'Cable Manager', 1, 1, '0', ''),
(7, '1m Patch Cord', '1m Patch Cord', 1, 1, '0', ''),
(8, '5m Patch Cord', '5m Patch Cord', 1, 1, '0', ''),
(9, 'I/O Box Kit - Face Plate', 'I/O Box Kit - Face Plate', 1, 1, '0', ''),
(10, 'I/O Box Kit - Back Box', 'I/O Box Kit - Back Box', 1, 1, '0', ''),
(11, 'I/O Box Kit - IO Socket', 'I/O Box Kit - IO Socket', 1, 1, '0', ''),
(12, 'Cat6 23 AWG UTP Cable (per Meter)', 'Cat6 23 AWG UTP Cable (per Meter)', 1, 1, '0', ''),
(13, '25 mm Conducting PVC Pipes (White)', '25 mm Conducting PVC Pipes (White)', 1, 1, '0', ''),
(14, 'Flexible Pipe (20mm)', 'Flexible Pipe (20mm)', 1, 1, '0', ''),
(15, 'Cable Ties (200 mm)', 'Cable Ties (200 mm)', 1, 1, '0', ''),
(16, 'Cable Ties (400 mm)', 'Cable Ties (400 mm)', 1, 1, '0', ''),
(17, 'Screws 1/2', 'Screws 1/2', 1, 1, '0', ''),
(18, 'Screws 3.5', 'Screws 3.5', 1, 1, '0', ''),
(19, 'PVC Pipe Saddles', 'PVC Pipe Saddles', 1, 1, '0', ''),
(20, 'L Slotted Angle', 'L Slotted Angle', 1, 1, '0', ''),
(21, '25mm Bend', '25mm Bend', 1, 1, '0', ''),
(22, '3 Way Junction', '3 Way Junction', 1, 1, '0', ''),
(23, '4 Way Junction', '4 Way Junction', 1, 1, '0', ''),
(24, 'PVC Square Box 6x6', 'PVC Square Box 6x6', 1, 1, '0', ''),
(25, 'PVC Square Box 5x5', 'PVC Square Box 5x5', 1, 1, '0', ''),
(26, '1m Pole', '1m Pole', 1, 1, '0', ''),
(27, '2m Pole', '2m Pole', 1, 1, '0', ''),
(28, '3m Pole', '3m Pole', 1, 1, '0', ''),
(29, 'Jio Cameras', 'Jio Cameras', 1, 1, '0', ''),
(30, 'Jio Bridge Devices', 'Jio Bridge Devices', 1, 1, '0', ''),
(31, '24 Port CISCO Meraki POE Switches', '24 Port CISCO Meraki POE Switches', 1, 1, '0', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `boq`
--
ALTER TABLE `boq`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `boq`
--
ALTER TABLE `boq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
