-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 26, 2025 at 09:47 AM
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
-- Table structure for table `spare_funds`
--

CREATE TABLE `spare_funds` (
  `id` int(10) NOT NULL,
  `mis_id` int(10) NOT NULL,
  `raisedFundId` int(10) DEFAULT NULL,
  `raisedFundComponentId` int(10) NOT NULL,
  `spares_component` varchar(100) NOT NULL,
  `spares_subcomponent` varchar(100) NOT NULL,
  `spares_cost` decimal(10,2) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `spare_required_reason` varchar(100) DEFAULT NULL,
  `atmid` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `spare_funds`
--

INSERT INTO `spare_funds` (`id`, `mis_id`, `raisedFundId`, `raisedFundComponentId`, `spares_component`, `spares_subcomponent`, `spares_cost`, `status`, `spare_required_reason`, `atmid`) VALUES
(1, 71059, 1, 2, 'BACKROOM', 'KEYPAD MISSING ', 1000.00, 1, NULL, 'P3ENFZ03'),
(2, 71059, 1, 2, 'CAMERA', 'BACKROOM CAMERA MISSING', 2000.00, 1, NULL, 'P3ENFZ03'),
(3, 71059, 2, 4, 'BACKROOM', 'KEYPAD MISSING ', 1000.00, 1, NULL, 'P3ENFZ03'),
(4, 71059, 2, 4, 'CAMERA', 'BACKROOM CAMERA MISSING', 2000.00, 1, NULL, 'P3ENFZ03'),
(5, 62234, 3, 6, 'DVR', 'DVR MISSING', 2150.00, 1, NULL, 'P3ENLK82'),
(6, 71357, 17, 21, 'PANEL', 'PANEL BATTERY ISSUE', 2100.00, 1, NULL, 'P3ENLK82'),
(7, 71357, 17, 21, 'Network', 'DVR DOWN & PANEL DOWN', 1000.00, 1, NULL, 'P3ENLK82'),
(8, 71357, 18, 23, 'PANEL', 'PANEL BATTERY ISSUE', 2100.00, 1, NULL, 'P3ENLK82'),
(9, 71357, 18, 23, 'Network', 'DVR DOWN & PANEL DOWN', 1000.00, 1, NULL, 'P1ECFA03'),
(10, 71357, 19, 25, 'PANEL', 'PANEL BATTERY ISSUE', 2100.00, 1, NULL, 'P1ECFA03'),
(11, 71357, 19, 25, 'Network', 'DVR DOWN & PANEL DOWN', 1000.00, 1, NULL, 'P3ECFZ01'),
(12, 71357, 20, 27, 'PANEL', 'PANEL BATTERY ISSUE', 2100.00, 1, NULL, 'P3ECFZ01'),
(13, 71357, 20, 27, 'Network', 'DVR DOWN & PANEL DOWN', 1000.00, 1, NULL, 'P3ENFZ02'),
(14, 71357, 21, 29, 'PANEL', 'PANEL BATTERY ISSUE', 2100.00, 1, NULL, 'P3ECBR01'),
(15, 71357, 21, 29, 'Network', 'DVR DOWN & PANEL DOWN', 1000.00, 1, NULL, 'P3ENAP31'),
(16, 71357, 22, 31, 'PANEL', 'PANEL BATTERY ISSUE', 2100.00, 1, NULL, 'P3ECMT05'),
(17, 71357, 22, 31, 'Network', 'DVR DOWN & PANEL DOWN', 1000.00, 1, NULL, 'P1ECFA03'),
(18, 39031, 0, 49, 'Network', 'DVR DOWN', 450.00, 1, NULL, 'P3ECCT02'),
(19, 39031, 0, 52, 'Network', 'DVR DOWN', 450.00, 1, NULL, 'P3ECCT02'),
(20, 39031, 0, 54, 'Network', 'DVR DOWN', 450.00, 1, NULL, 'P3ENMH01'),
(21, 39031, 0, 56, 'Network', 'DVR DOWN', 450.00, 1, NULL, 'P3ENMH01'),
(22, 39031, 0, 58, 'Network', 'DVR DOWN', 450.00, 1, NULL, 'P3ECOI01'),
(23, 39031, 0, 60, 'Network', 'DVR DOWN', 450.00, 1, NULL, 'P3ENJH29'),
(24, 39031, 0, 62, 'Network', 'DVR DOWN', 450.00, 1, NULL, 'P3ENJH30'),
(25, 39031, 41, 64, 'Network', 'DVR DOWN', 450.00, 1, NULL, 'P3ENJH30'),
(26, 39031, 42, 66, 'Network', 'DVR DOWN', 450.00, 1, NULL, 'P3ENJH29'),
(27, 72724, 51, 76, 'Network', 'DVR DOWN', 1200.00, 1, NULL, 'P3ENBU69'),
(28, 72963, 61, 87, 'DVR & NVR', 'DVR _ CPPLUS', 2200.00, 1, NULL, 'P3ENMM54'),
(29, 0, 62, 90, 'Adopter', '12 v Adapter', 500.00, 1, NULL, 'P3ENMM17'),
(30, 74920, 63, 92, 'Panel', 'Utopia Zone Board with FRC Cable', 2100.00, 1, NULL, 'P3ENPT85'),
(31, 74920, 63, 92, 'Sensor', 'Shutter Sensor', 2340.00, 1, NULL, 'P3ENPT85'),
(32, 45777, 64, 94, 'HDD', '1 TB HDD', 550.00, 1, NULL, 'P3ENPT85'),
(33, 0, 68, 99, 'Camera', 'PINHOLE CAMERA', 500.00, 1, NULL, 'P3ENPT76'),
(34, 0, 69, 100, 'Panel', 'utopia Comfort Panel SMPS', 350.00, 1, NULL, 'P3ENPT76'),
(35, 77827, 107, 139, 'Adopter', 'C type Adaptor', 2100.00, 1, NULL, 'P3ENPT76'),
(36, 81165, 128, 161, 'Adopter', 'UNV NVR POWER ADAPTOR', 1200.00, 1, NULL, 'P3ENPT81'),
(37, 81165, 129, 163, 'Adopter', '', 2100.00, 1, NULL, 'P3ENPT81'),
(38, 81176, 132, 167, 'AI', 'RASPBERRY PI', 2000.00, 1, NULL, 'P3ENPT91'),
(39, 81176, 132, 167, 'Camera Wire', 'CAMERA WIRE REQUIREMENT', 1200.00, 1, NULL, 'P3ENPT91'),
(40, 60865, 133, 168, 'Lan Cable', '10 MTR LAN  CABLE', 300.00, 1, NULL, 'P3ENBP18'),
(41, 81176, 134, 169, 'AI', 'RASPBERRY PI', 2000.00, 1, NULL, 'P3ENBP18'),
(42, 81176, 134, 169, 'Camera', 'Hikvision Dome_IP Camera', 2500.00, 1, NULL, 'P3ENPT89'),
(43, 81176, 135, 170, 'Connector', 'frc cable_Smart I', 21.00, 1, NULL, 'P3ENKJ01'),
(44, 81176, 136, 171, 'Connector', 'frc cable_Smart I', 21.00, 1, NULL, 'P3ENKJ01'),
(45, 81217, 138, 174, 'POE Switch', 'POE SWITCH', 2100.00, 1, NULL, 'P3ENPT38'),
(46, 81218, 139, 176, 'Adopter', 'UNV NVR POWER ADAPTOR', 1000.00, 1, NULL, 'P3ENPT38'),
(47, 81233, 141, 180, 'Camera', 'New IP Camera', 3200.00, 1, NULL, 'P3ENPT34'),
(48, 81236, 142, 182, 'AI', 'RASPBERRY PI', 5000.00, 1, NULL, 'P3ENND14'),
(49, 81806, 147, 187, 'Adopter', '12 v Adapter', 500.00, 1, NULL, 'P3ENND14'),
(50, 83409, 151, 192, 'POE Switch', 'POE SWITCH', 200.00, 1, NULL, 'P3ENND14'),
(51, 80926, 187, 228, 'HDD', '1 TB HDD', 3900.00, 1, NULL, 'P1ENMU16'),
(52, 86856, 193, 235, 'Battery', '2 BATTERY 12AH 12V', 950.00, 1, NULL, 'P1ENOD04 '),
(53, 0, 195, 237, 'Sensor Wire', '90 MTR SENSOR CABLE', 1200.00, 1, NULL, 'P1ENPU41'),
(54, 0, 195, 237, 'Lan Cable', '10 MTR LAN  CABLE', 250.00, 1, NULL, 'P1ENPU47'),
(55, 0, 195, 237, 'Lan Cable', '10 MTR LAN  CABLE', 250.00, 1, NULL, 'P1EWDL15'),
(56, 0, 195, 237, 'Lan Cable', '5 MTR LAN CABLE', 150.00, 1, NULL, 'P1EWDL15'),
(57, 0, 195, 237, 'Lan Cable', '5 MTR LAN CABLE', 150.00, 1, NULL, 'P1DCHY41'),
(58, 0, 195, 237, 'Lan Cable', '5 MTR LAN CABLE', 150.00, 1, NULL, 'P3ECCT02'),
(59, 0, 195, 237, 'Panel', 'RCA TO RCA AUDIO CABLE', 300.00, 1, NULL, 'P3ECCT02'),
(60, 68977, 231, 274, 'Battery', '2 BATTERY 12AH 12V', 1900.00, 1, NULL, 'P3ECDE08'),
(61, 65408, 248, 292, 'Adopter', '12 v Adapter', 450.00, 1, NULL, 'P3ECHY18'),
(62, 87037, 251, 295, 'DVR & NVR', 'XVR_UNV', 2500.00, 1, NULL, 'P3ECHY18'),
(63, 87064, 277, 321, 'DVR & NVR', 'NVR_UNV', 2500.00, 1, NULL, 'P1DCJM26'),
(64, 87088, 282, 326, 'POE Switch', 'POE SWITCH', 2100.00, 1, 'Faulty', 'P3ECHY22'),
(65, 87088, 283, 327, 'Panel', 'UTOPIA_POWER FILTER CARD', 2111.00, 1, 'New Requirement', 'P3ECHY22'),
(66, 87088, 283, 327, 'SIM', 'VI APN Sim Card', 1200.00, 1, 'Faulty', 'P3ECHY22'),
(67, 72365, 300, 344, 'Adopter', '12 v Adapter', 450.00, 1, NULL, 'P3ECMI11'),
(68, 87095, 302, 346, 'Sensor', 'SMOKE  SENSOR', 1200.00, 1, 'Faulty', 'P3ECND12'),
(69, 87095, 302, 346, 'Sensor Wire', '90 MTR SENSOR CABLE', 2100.00, 1, 'Missing', 'P3ECND12'),
(70, 87095, 303, 347, 'Sensor Wire', 'SENSOR WIRE REQUIREMENT', 21.00, 1, 'Faulty', 'P3ECND12'),
(71, 87095, 304, 348, 'Sensor Wire', 'SENSOR WIRE REQUIREMENT', 21.00, 1, 'Faulty', 'P3ECNK07'),
(72, 87095, 305, 349, 'Sensor Wire', 'SENSOR WIRE REQUIREMENT', 21.00, 1, 'Faulty', 'P3ECNK08'),
(73, 87095, 306, 350, 'Sensor Wire', 'SENSOR WIRE REQUIREMENT', 21.00, 1, 'Faulty', 'P3ECNK08'),
(74, 87095, 307, 351, 'Sensor Wire', 'SENSOR WIRE REQUIREMENT', 21.00, 1, 'Faulty', 'MC056358'),
(75, 87274, 318, 362, 'Battery', '2 BATTERY 12AH 12V', 850.00, 1, 'Faulty', 'P3ENMM09'),
(76, 89245, 333, 377, 'Battery', '2 BATTERY 12AH 12V', 1000.00, 1, 'Missing', 'abcd'),
(77, 0, 395, 440, 'Battery', '2 BATTERY 12AH 12V', 1050.00, 1, NULL, NULL),
(78, 0, 396, 441, 'Battery', '2 BATTERY 12AH 12V', 1050.00, 1, NULL, NULL),
(79, 79596, 399, 444, 'Battery', '2 BATTERY 12AH 12V', 1000.00, 1, NULL, NULL),
(80, 0, 408, 453, 'Adopter', '12 v Adapter', 450.00, 1, NULL, NULL),
(81, 91757, 438, 483, 'Adopter', '12 v Adapter', 250.00, 1, 'Faulty', 'CECN84535'),
(82, 92523, 441, 486, 'Sensor', 'Magnetic Contact Sensors', 210.00, 1, 'Not Installed', 'ZAG9064'),
(83, 74938, 448, 493, 'Battery', '2 BATTERY 12AH 12V', 1800.00, 1, 'Faulty', 'D4397800'),
(84, 92226, 451, 496, 'Adopter', '12 v Adapter', 480.00, 1, 'Missing', 'N2059300'),
(85, 92755, 452, 497, 'Sensor Wire', 'SENSOR WIRE REQUIREMENT', 200.00, 1, 'Not Installed', 'N3852300'),
(86, 92756, 453, 498, 'Panel', 'utopia Comfort Panel SMPS', 750.00, 1, 'Faulty', 'A2825400'),
(87, 83275, 489, 534, 'Battery', '2 BATTERY 12AH 12V', 950.00, 1, 'Faulty', 'ZVN9071'),
(88, 92814, 491, 536, 'Panel', 'utopia Comfort Panel SMPS', 300.00, 1, 'Faulty', 'ZAG8027'),
(89, 92814, 491, 536, 'Adopter', '12 v Adapter', 250.00, 1, 'Faulty', 'ZAG8027'),
(90, 0, 552, 598, 'Battery', '2 BATTERY 12AH 12V', 1050.00, 1, NULL, NULL),
(91, 0, 553, 599, 'Battery', '2 BATTERY 12AH 12V', 1800.00, 1, NULL, NULL),
(92, 93970, 570, 616, 'HDD', '2 TB HDD', 5350.00, 1, 'Faulty', 'P3ENND93 '),
(93, 83341, 571, 617, 'Battery', '2 BATTERY 12AH 12V', 3500.00, 1, 'Not Installed', 'P3ennd93'),
(94, 93326, 637, 683, 'Panel', 'utopia Comfort Panel SMPS', 480.00, 1, 'Faulty', 'D9012420'),
(95, 94948, 649, 695, 'Battery', '2 BATTERY 12AH 12V', 13600.00, 1, 'Faulty', 'N8657100'),
(96, 95669, 660, 709, 'Router', '4G Router', 300.00, 1, 'Faulty', 'ZBG9034'),
(97, 1309, 693, 744, 'Lan Cable', '15 MTR LAN CABLE', 200.00, 1, 'Faulty', 'ZLC9100'),
(98, 1309, 695, 746, 'Battery', '2 BATTERY 12AH 12V', 1100.00, 1, 'Faulty', 'ZLC9100'),
(99, 2796, 697, 748, 'Adopter', '12 v Adapter', 150.00, 1, 'New Requirement', 'mhhao055'),
(100, 3018, 698, 749, 'Battery', '2 BATTERY 12AH 12V', 900.00, 1, 'New Requirement', 'P3ENGG35'),
(101, 3231, 725, 776, 'Battery', '2 BATTERY 12AH 12V', 1800.00, 1, 'Faulty', 'B1150900'),
(102, 3273, 727, 778, 'Panel', 'utopia Comfort Panel SMPS', 450.00, 1, 'Faulty', 'Nl071200'),
(103, 2033, 750, 801, 'DVR & NVR', 'DVR _ CPPLUS', 2279.00, 1, 'Faulty', 'BPCN108408'),
(104, 1013, 754, 805, 'Adopter', '12 v Adapter', 450.00, 1, NULL, NULL),
(105, 0, 755, 806, 'Adopter', '12 v Adapter', 450.00, 1, NULL, NULL),
(106, 0, 756, 807, 'Adopter', '12 v Adapter', 450.00, 1, NULL, NULL),
(107, 0, 757, 808, 'Adopter', '12 v Adapter', 450.00, 1, NULL, NULL),
(108, 0, 758, 809, 'Adopter', '12 v Adapter', 450.00, 1, NULL, NULL),
(109, 0, 759, 810, 'Adopter', '12 v Adapter', 450.00, 1, NULL, NULL),
(110, 0, 760, 811, 'Adopter', '12 v Adapter', 450.00, 1, NULL, NULL),
(111, 2495, 769, 820, 'Battery', '2 BATTERY 12AH 12V', 1200.00, 1, 'Faulty', 'P3ENPT82'),
(112, 5364, 770, 821, 'Battery', '2 BATTERY 12AH 12V', 800.00, 1, 'Faulty', 'N3415800'),
(113, 5386, 794, 845, 'Battery', '2 BATTERY 12AH 12V', 1000.00, 1, NULL, NULL),
(114, 5387, 795, 846, 'Battery', '2 BATTERY 12AH 12V', 1000.00, 1, NULL, NULL),
(115, 5391, 796, 847, 'Battery', '2 BATTERY 12AH 12V', 1000.00, 1, NULL, NULL),
(116, 5399, 797, 848, 'Battery', '2 BATTERY 12AH 12V', 1000.00, 1, NULL, NULL),
(117, 3345, 801, 852, 'Lan Cable', '15 MTR LAN CABLE', 2500.00, 1, NULL, NULL),
(118, 2921, 806, 857, 'Sensor Wire', '2.5 Wire 20 mtr', 2700.00, 1, NULL, NULL),
(119, 3345, 807, 858, 'Sensor Wire', '2.5 Wire 20 mtr', 2500.00, 1, NULL, NULL),
(120, 17254, 843, 894, 'Battery', '2 BATTERY 12AH 12V', 1000.00, 1, 'Faulty', 'P3ENJH59'),
(121, 29099, 905, 956, 'Sensor Wire', '2.5 Wire 20 mtr', 450.00, 1, NULL, NULL),
(122, 29100, 906, 957, 'Camera Wire', '15 MTR CAMERA CABLE', 450.00, 1, NULL, NULL),
(123, 5806, 1087, 1139, 'Sensor Wire', '2.5 Wire 12 mtr', 380.00, 1, NULL, NULL),
(124, 5806, 1088, 1140, 'Sensor Wire', '2.5 Wire 12 mtr', 380.00, 1, NULL, NULL),
(125, 46732, 1171, 1223, 'Battery', '2 BATTERY 12AH 12V', 1000.00, 1, NULL, NULL),
(126, 46732, 1172, 1224, 'Battery', '2 BATTERY 12AH 12V', 1000.00, 1, NULL, NULL),
(127, 40484, 1381, 1433, 'Adopter', '5V Power Adapter with USB Cable', 400.00, 1, NULL, NULL),
(128, 6120, 1382, 1434, 'Adopter', '5V Power Adapter with USB Cable', 400.00, 1, NULL, NULL),
(129, 68632, 1566, 1618, 'Adopter', '5V Power Adaptor_Gigatek', 150.00, 1, NULL, NULL),
(130, 68632, 1575, 1627, 'POE Switch', 'POE SWITCH', 500.00, 1, 'Faulty', 'p3enmm09'),
(131, 69044, 1577, 1629, 'HDD', '2 TB HDD', 5000.00, 1, 'Faulty', 'p3enmm09'),
(132, 67032, 1594, 1646, 'SIM', 'AIRTEL OPEN SIM CARD', 600.00, 1, NULL, NULL),
(133, 69760, 1595, 1647, 'Panel', 'MTC_SMPS', 300.00, 1, NULL, NULL),
(134, 72691, 1616, 1668, 'POE Switch', 'POE SWITCH', 2500.00, 1, NULL, NULL),
(135, 72691, 1616, 1668, 'D-Link', 'D-Link Switch', 800.00, 1, NULL, NULL),
(136, 72691, 1617, 1669, 'POE Switch', 'POE SWITCH', 2500.00, 1, 'Faulty', 'p1dcmu75'),
(137, 72691, 1620, 1672, 'HDD', '2 TB HDD', 5000.00, 1, NULL, NULL),
(138, 72705, 1621, 1673, 'D-Link', 'D-Link Switch', 500.00, 1, 'Faulty', 'p1dcmu75'),
(139, 70662, 1622, 1674, 'Adopter', '12 v Adapter', 350.00, 1, NULL, NULL),
(140, 73021, 1663, 1715, 'POE Switch', 'POE SWITCH', 1200.00, 1, 'Missing', 'P3AWWX05'),
(141, 73420, 1682, 1734, 'Adopter', 'C type Adaptor', 150.00, 1, 'Faulty', 'p1dcmu75');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `spare_funds`
--
ALTER TABLE `spare_funds`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `spare_funds`
--
ALTER TABLE `spare_funds`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
