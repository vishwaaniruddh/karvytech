-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 26, 2025 at 09:41 AM
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
-- Database: `u444388293_cncindia`
--

-- --------------------------------------------------------

--
-- Table structure for table `spares`
--

CREATE TABLE `spares` (
  `id` int(6) NOT NULL,
  `spareName` varchar(60) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `spares`
--

INSERT INTO `spares` (`id`, `spareName`, `status`) VALUES
(2, 'Adopter', 1),
(3, 'AI', 1),
(4, 'AUX Cable', 1),
(5, 'Battery', 1),
(6, 'Camera', 1),
(7, 'Camera Wire', 1),
(8, 'Connector', 1),
(9, 'D-Link', 1),
(10, 'DVR & NVR', 1),
(11, 'Panel', 1),
(12, 'HDD', 1),
(13, 'Lan Cable', 1),
(14, 'POE Switch', 1),
(15, 'Router', 1),
(16, 'Sensor', 1),
(17, 'Sensor Wire', 1),
(18, 'SIM', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `spares`
--
ALTER TABLE `spares`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `spares`
--
ALTER TABLE `spares`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
