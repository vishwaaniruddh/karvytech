-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2025 at 09:07 PM
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
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

INSERT INTO `users` (`id`, `username`, `last_name`, `first_name`, `email`, `phone`, `profile_picture`, `bio`, `password_hash`, `plain_password`, `jwt_token`, `role`, `vendor_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', NULL, NULL, 'admin@example.com', '+1234567890', NULL, NULL, '$2y$12$qIltu8UH/9JQTVv2XeP9deprEPyd8vWdMs7QttIUNFcMoPtoIvfUS', 'admin123', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxNzYyMjg3MTY0LCJleHAiOjE3NjIzNzM1NjR9.iL-8eKqnN_xHAmUI64qDk1BJfUdsuMTKq6whDdCqu0Y', 'admin', NULL, 'active', '2025-11-01 09:03:15', '2025-11-04 20:12:44'),
(9, 'Ganesh Panchal', NULL, NULL, 'ganesh@gmail.com', '8888888888', NULL, NULL, '$2y$12$jB/RCc457pNn02q0wx7pRekgnXDASyJ9JltGXNUsYOia9r5d.tn1O', 'password', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo5LCJ1c2VybmFtZSI6IkdhbmVzaCBQYW5jaGFsIiwicm9sZSI6InZlbmRvciIsImlhdCI6MTc2MjI3NzEyNSwiZXhwIjoxNzYyMzYzNTI1fQ.rrPNLJkkDoLtAWdA-EzYK7mJc_cUM33ERnDre4Rn0O0', 'vendor', 1, 'active', '2025-11-04 11:31:20', '2025-11-04 17:25:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
