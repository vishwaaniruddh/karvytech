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
-- Table structure for table `sparesComponent`
--

CREATE TABLE `sparesComponent` (
  `id` int(6) NOT NULL,
  `spareid` varchar(100) DEFAULT NULL,
  `spareComponentName` varchar(360) DEFAULT NULL,
  `cost` varchar(10) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sparesComponent`
--

INSERT INTO `sparesComponent` (`id`, `spareid`, `spareComponentName`, `cost`, `status`) VALUES
(2, 'Adopter', '12 v Adapter', '150', 1),
(3, 'Adopter', '5V Power Adapter with USB Cable', NULL, 1),
(4, 'Adopter', '5V Power Adaptor_Gigatek', NULL, 1),
(5, 'Adopter', 'C type Adaptor', NULL, 1),
(6, 'Adopter', 'UNV NVR POWER ADAPTOR', '900', 1),
(7, 'AI', 'RASPBERRY PI', '4700', 1),
(8, 'AUX Cable', 'AUX CABLE 10 MTR', NULL, 1),
(9, 'Battery', '2 BATTERY 12AH 12V', NULL, 1),
(10, 'Camera', 'BULLET  CAMERA UNV', NULL, 1),
(11, 'Camera', 'BULLET CAMERA _ CPPLUS', NULL, 1),
(12, 'Camera', 'BULLET CAMERA _ DAHUA', NULL, 1),
(13, 'Camera', 'BULLET CAMERA _ HIKVISON', NULL, 1),
(14, 'Camera', 'BULLET IP CAMERA', NULL, 1),
(15, 'Camera', 'Dahuva Dome_IP Camera', NULL, 1),
(16, 'Camera', 'DOME CAMERA _ CPPLUS', NULL, 1),
(17, 'Camera', 'DOME CAMERA _ HIKVISON', NULL, 1),
(18, 'Camera', 'Dome Camera UNV', NULL, 1),
(19, 'Camera', 'DOME IP CAMERA', NULL, 1),
(20, 'Camera', 'HIKVISION BULLET IP CAMERA', '1200', 1),
(21, 'Camera', 'Hikvision Dome_IP Camera', NULL, 1),
(22, 'Camera', 'HOLE CAMERA _ CPPLUS', NULL, 1),
(23, 'Camera', 'HOLE CAMERA _ HIKVISON', NULL, 1),
(24, 'Camera', 'New IP Camera', NULL, 1),
(25, 'Camera', 'PIN HOLE CAMREA WITH MEMORY CARD', NULL, 1),
(26, 'Camera', 'PINHOLE CAMERA', NULL, 1),
(27, 'Camera', 'UNV Dome_IP Camera', NULL, 1),
(28, 'Camera Wire', '90 MTR CAMERA CABLE', NULL, 1),
(29, 'Camera Wire', 'CAMERA CABLE', NULL, 1),
(30, 'Camera Wire', 'CAMERA WIRE REQUIREMENT', NULL, 1),
(31, 'Connector', 'BNC & DC Connector', NULL, 1),
(32, 'Connector', 'CON11 Connector Rass', NULL, 1),
(33, 'Connector', 'CON2_RASS POWER CONNECTOR', NULL, 1),
(34, 'Connector', 'FRC CABLE_RASS', NULL, 1),
(35, 'Connector', 'frc cable_Smart I', '350', 1),
(36, 'D-Link', 'D-Link Switch', NULL, 1),
(37, 'DVR & NVR', 'Dahuva NVR', NULL, 1),
(38, 'DVR & NVR', 'DVR _ CPPLUS', NULL, 1),
(39, 'DVR & NVR', 'DVR _ CPPLUS INDIGO', NULL, 1),
(41, 'DVR & NVR', 'MIC', NULL, 1),
(42, 'DVR & NVR', 'New NVR', NULL, 1),
(43, 'DVR & NVR', 'NEW TWO-WAY SET', NULL, 1),
(44, 'DVR & NVR', 'NVR_UNV', NULL, 1),
(45, 'DVR & NVR', 'SPEAKER', NULL, 1),
(46, 'DVR & NVR', 'XVR_Dahuva', NULL, 1),
(47, 'DVR & NVR', 'XVR_UNV', NULL, 1),
(48, 'Panel', 'EML L Bracket with Screw', NULL, 1),
(49, 'Panel', 'EML LOCK_ SECURICO GX4816', NULL, 1),
(50, 'Panel', 'EML LOCK_RASS', NULL, 1),
(51, 'Panel', 'EML LOCK_SECURICO', NULL, 1),
(52, 'Panel', 'EML LOCK_SMART I', NULL, 1),
(53, 'Panel', 'EML LOCK_SMART IN', NULL, 1),
(54, 'Panel', 'EML LOCK_UTOPIA', NULL, 1),
(55, 'Panel', 'EML RELAY_SMART I', NULL, 1),
(56, 'HDD', '1 TB HDD', NULL, 1),
(57, 'HDD', '128 SD Card', NULL, 1),
(58, 'HDD', '2 TB HDD', '2400', 1),
(59, 'HDD', '4 TB HDD', NULL, 1),
(60, 'Lan Cable', '1 MTR LAN CABLE', NULL, 1),
(61, 'Lan Cable', '10 MTR LAN  CABLE', NULL, 1),
(62, 'Camera Wire', '15 MTR CAMERA CABLE', NULL, 1),
(63, 'Lan Cable', '15 MTR LAN CABLE', NULL, 1),
(64, 'Lan Cable', '5 MTR LAN CABLE', NULL, 1),
(65, 'Panel', 'KEY PAID _ RASS', NULL, 1),
(66, 'Panel', 'KEY PAID _ SECURICO GX4816', NULL, 1),
(67, 'Panel', 'KEY PAID _ SMART I', NULL, 1),
(68, 'Panel', 'KEY PAID _SECURICO', NULL, 1),
(69, 'Panel', 'MINI_UPS_AXIS BANK', NULL, 1),
(70, 'Panel', 'MOTHER BOARD _ RASS', NULL, 1),
(71, 'Panel', 'MOTHER BOARD _ SECURICO', NULL, 1),
(72, 'Panel', 'MOTHER BOARD _ SECURICO GX4816', NULL, 1),
(73, 'Panel', 'MOTHER BOARD _ SMART IN', NULL, 1),
(74, 'Panel', 'MOTHER BOARD 300_ RASS', NULL, 1),
(75, 'Panel', 'MOTHER BOARD 500_ RASS', NULL, 1),
(76, 'Panel', 'MOTHER BOARD_ SMART I', NULL, 1),
(77, 'Panel', 'MOTHER BOARD_ SMART I_OLD', NULL, 1),
(78, 'Panel', 'MOTHERBOARD_COMFORT-HDFC PANEL', NULL, 1),
(79, 'Panel', 'MOTHERBOARD_COMFORT-PNB PANEL', NULL, 1),
(80, 'Panel', 'MTC_SMPS', NULL, 1),
(81, 'Panel', 'New Comfort Panel Set', NULL, 1),
(82, 'Panel', 'New SMART-I 32 Zone', NULL, 1),
(83, 'Panel', 'OUT SIDE HOOTER', NULL, 1),
(84, 'Panel', 'Panel Set With Sensor Kit', NULL, 1),
(85, 'Panel', 'Panel Set Without Sensor Kit', NULL, 1),
(86, 'Panel', 'PANIC SWITCH', NULL, 1),
(87, 'Panel', 'PCB Card', NULL, 1),
(88, 'Panel', 'POWER CABLE', NULL, 1),
(89, 'Panel', 'POWER FILTER CARD _ RASS', NULL, 1),
(90, 'Panel', 'POWER FILTER CARD _ SMART-I', NULL, 1),
(91, 'Panel', 'POWER FILTER CARD _ SMART-IN', NULL, 1),
(92, 'Panel', 'Power FIlter Card Securico 1616', NULL, 1),
(93, 'Panel', 'POWER FILTER CARD SECURICO 4816', NULL, 1),
(94, 'Panel', 'Push Button for Backroom', NULL, 1),
(95, 'Panel', 'RABBO CARD _ RASS', NULL, 1),
(96, 'Panel', 'RASS_ENERGY METER', NULL, 1),
(97, 'Panel', 'RASS_MICRO POWER FILTER', NULL, 1),
(98, 'Panel', 'RASS_RELAY CONNECTOR', NULL, 1),
(99, 'Panel', 'RCA TO RCA AUDIO CABLE', NULL, 1),
(100, 'Panel', 'REALY _ RASS', NULL, 1),
(101, 'Panel', 'REALY _ SECURICO', NULL, 1),
(102, 'Panel', 'REALY _ SECURICO GX4816', NULL, 1),
(103, 'Panel', 'REALY _ SMART I', NULL, 1),
(104, 'Panel', 'REALY _ SMART IN', NULL, 1),
(105, 'Panel', 'SAP500_RABBO CARD', NULL, 1),
(106, 'Panel', 'SATA CABLE AND POWER CABLE  FOR HDD', NULL, 1),
(107, 'Panel', 'silence key Butten', NULL, 1),
(108, 'Panel', 'SIREN', NULL, 1),
(109, 'Panel', 'SMART-I_Motherboard_BOI', NULL, 1),
(110, 'Panel', 'Utopia - Keypad', NULL, 1),
(111, 'Panel', 'UTOPIA  MOTHERBOARD_BOI', NULL, 1),
(112, 'Panel', 'UTOPIA COMFORT  MOTHERBOARD', NULL, 1),
(113, 'Panel', 'Utopia Comfort Kit_HDFC', NULL, 1),
(114, 'Panel', 'utopia Comfort Panel SMPS', NULL, 1),
(115, 'Panel', 'Utopia Zone Board with FRC Cable', NULL, 1),
(116, 'Panel', 'UTOPIA_Energy Meter', NULL, 1),
(117, 'Panel', 'UTOPIA_FRC CABLE', NULL, 1),
(118, 'Panel', 'UTOPIA_POWER FILTER CARD', NULL, 1),
(119, 'Panel', 'UTOPIA_ZONE BOARD', NULL, 1),
(120, 'Panel', 'ZONE BOARD _ SECURICO GX4816', NULL, 1),
(121, 'Panel', 'ZONE BOARD _ SMART IN', NULL, 1),
(122, 'Panel', 'ZONE BOARD 1 TO 24 _ RASS', NULL, 1),
(123, 'Panel', 'ZONE BOARD 1 TO 52 _ RASS', NULL, 1),
(124, 'Panel', 'ZONE BOARD 25 TO 52_ RASS', NULL, 1),
(125, 'Panel', 'ZONE BOARD 9 To 17 _ SMART IN', NULL, 1),
(126, 'Panel', 'ZONE BOARD_ SMART I', NULL, 1),
(127, 'Panel', 'ZONE BOARD_ SMART I 9 TO 24', NULL, 1),
(128, 'Panel', 'ZONEBOARD _ SECURICO', NULL, 1),
(129, 'POE Switch', 'POE SWITCH', NULL, 1),
(130, 'Router', '4G Router', NULL, 1),
(131, 'Router', 'Gigatak Indoor Device', NULL, 1),
(132, 'Router', 'GIGATEK WITH OD SET', NULL, 1),
(133, 'Router', 'New 4G Router with SIM Card', NULL, 1),
(134, 'Router', 'OUT DOOR UNIT', NULL, 1),
(135, 'Router', 'OUTDOOR UNIT WITH 5 MTR LAN CABLE', NULL, 1),
(136, 'Router', 'OUTDOOR UNIT WITH OPEN SIM', NULL, 1),
(137, 'Router', 'Patch Antenna', NULL, 1),
(138, 'Router', 'TECHROUTE 3G', NULL, 1),
(139, 'Router', 'Yagi Antenna', NULL, 1),
(140, 'Sensor', 'GLASS BERACK  SENSOR_ RASS', NULL, 1),
(141, 'Sensor', 'GLASS BERACK SENSOR_ SMART I', NULL, 1),
(142, 'Sensor', 'Glass Break Sensor', NULL, 1),
(143, 'Sensor', 'HEAT DETECTOR SENSOR', NULL, 1),
(144, 'Sensor', 'Magnetic Contact Sensors', NULL, 1),
(145, 'Sensor', 'PIR  OR  MOTION SENSOR', NULL, 1),
(146, 'Sensor', 'Shutter Sensor', NULL, 1),
(147, 'Sensor', 'SMOKE  SENSOR', NULL, 1),
(148, 'Sensor', 'SMPS _ SECURICO', NULL, 1),
(149, 'Sensor', 'SMPS _ SECURICO GX4816', NULL, 1),
(150, 'Sensor', 'SMPS _ SMART I', NULL, 1),
(151, 'Sensor', 'SMPS _ SMART IN', NULL, 1),
(152, 'Sensor', 'SMPS Comfort Panel', NULL, 1),
(153, 'Sensor', 'SMPS CONNECTOR_RASS', NULL, 1),
(154, 'Sensor', 'SMPS CONNECTOR_UTOPIA', NULL, 1),
(155, 'Sensor', 'SMPS RASS Panel', NULL, 1),
(156, 'Sensor', 'Temperature Sensor', NULL, 1),
(157, 'Sensor', 'THERMAL SENSOR', NULL, 1),
(158, 'Sensor', 'VIBRATION  SENSOR _ SECURICO', NULL, 1),
(159, 'Sensor', 'Vibration Sensor', NULL, 1),
(160, 'Sensor', 'VIBRATION SENSOR _ RASS', NULL, 1),
(161, 'Sensor', 'VIBRATION SENSOR _ SMART I', NULL, 1),
(162, 'Sensor Wire', '2.5 Wire 12 mtr', NULL, 1),
(163, 'Sensor Wire', '2.5 Wire 20 mtr', NULL, 1),
(164, 'Sensor Wire', '90 MTR SENSOR CABLE', NULL, 1),
(165, 'Sensor Wire', 'SENSOR WIRE REQUIREMENT', NULL, 1),
(166, 'SIM', 'AIRTEL OPEN SIM CARD', NULL, 1),
(167, 'SIM', 'VI APN Sim Card', NULL, 1),
(168, 'SIM', 'VI Open Sim Card', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sparesComponent`
--
ALTER TABLE `sparesComponent`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sparesComponent`
--
ALTER TABLE `sparesComponent`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
