-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2025 at 05:11 PM
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
(1, 1, 'CREATE_USER', 'users', 2, NULL, '{\"username\":\"Vikas Pal\",\"email\":\"vikas@gmail.com\",\"password\":\"vikas@123\",\"role\":\"vendor\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 15:05:45'),
(2, 1, 'CREATE_USER', 'users', 3, NULL, '{\"username\":\"bobby bagga\",\"email\":\"bobby@email.com\",\"phone\":\"8736263545\",\"password\":\"something@123\",\"role\":\"vendor\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 15:19:56'),
(3, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"SQLSTATE[42S02]: Base table or view not found: 1146 Table \'site_installation_management.menu_items\' doesn\'t exist\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\models\\\\Menu.php\",\"line\":27,\"request_uri\":\"\\/project\\/admin\\/users\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:27:16'),
(4, 1, 'UPDATE_customers', 'customers', 11, '{\"id\":11,\"name\":\"Asian Paints\",\"status\":\"active\",\"created_at\":\"2025-11-02 00:17:44\",\"updated_at\":\"2025-11-02 00:17:44\"}', '{\"name\":\"american paints\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 18:59:14'),
(5, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Class \\\"CountriesController\\\" not found\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":26,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:37:56'),
(6, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"require_once(C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:38:15'),
(7, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Failed opening required \'C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php\' (include_path=\'.;C:\\\\php\\\\pear\')\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:38:15'),
(8, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"require_once(C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:38:16'),
(9, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Failed opening required \'C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php\' (include_path=\'.;C:\\\\php\\\\pear\')\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:38:17'),
(10, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"require_once(C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:38:55'),
(11, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Failed opening required \'C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php\' (include_path=\'.;C:\\\\php\\\\pear\')\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:38:55'),
(12, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"require_once(C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:38:56'),
(13, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Failed opening required \'C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php\' (include_path=\'.;C:\\\\php\\\\pear\')\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:38:56'),
(14, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"require_once(C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:40:08'),
(15, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Failed opening required \'C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php\' (include_path=\'.;C:\\\\php\\\\pear\')\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:40:08'),
(16, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"require_once(C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:40:11'),
(17, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Failed opening required \'C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php\' (include_path=\'.;C:\\\\php\\\\pear\')\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:40:11'),
(18, 1, 'UPDATE_countries', 'countries', 3, '{\"id\":3,\"name\":\"United Kingdom\",\"status\":\"active\",\"created_at\":\"2025-11-02 00:17:44\",\"updated_at\":\"2025-11-02 00:17:44\"}', '{\"name\":\"UK\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 19:13:59'),
(19, 1, 'UPDATE_countries', 'countries', 2, '{\"id\":2,\"name\":\"United States\",\"status\":\"active\",\"created_at\":\"2025-11-02 00:17:44\",\"updated_at\":\"2025-11-02 00:17:44\"}', '{\"name\":\"US\",\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 19:14:10'),
(20, 1, 'TOGGLE_countries_STATUS', 'countries', 3, '{\"status\":\"active\"}', '{\"status\":\"inactive\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 19:14:22'),
(21, 1, 'TOGGLE_countries_STATUS', 'countries', 3, '{\"status\":\"inactive\"}', '{\"status\":\"active\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 19:14:27'),
(22, 1, 'DELETE_countries', 'countries', 5, '{\"id\":5,\"name\":\"Australia\",\"status\":\"active\",\"created_at\":\"2025-11-02 00:17:44\",\"updated_at\":\"2025-11-02 00:17:44\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 19:14:36'),
(23, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"require_once(C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:45:17'),
(24, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Failed opening required \'C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php\' (include_path=\'.;C:\\\\php\\\\pear\')\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:45:17'),
(25, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"require_once(C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:45:18'),
(26, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Failed opening required \'C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php\' (include_path=\'.;C:\\\\php\\\\pear\')\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:45:18'),
(27, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"require_once(C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=customers\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:46:00'),
(28, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Failed opening required \'C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\/..\\/..\\/controllers\\/ZonesController.php\' (include_path=\'.;C:\\\\php\\\\pear\')\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":4,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=customers\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:46:00'),
(29, 1, 'ERROR: EXCEPTION', NULL, NULL, '{\"message\":\"Call to undefined method Zone::getAll()\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\controllers\\\\ZonesController.php\",\"line\":15,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=zones\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:58:18'),
(30, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"file_put_contents(logs\\/error.log): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\includes\\\\error_handler.php\",\"line\":123,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:03:32'),
(31, 1, 'ERROR: FATAL ERROR', NULL, NULL, '{\"message\":\"Cannot redeclare City::getAllWithRelations()\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\models\\\\City.php\",\"line\":91,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:03:32'),
(32, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"file_put_contents(logs\\/error.log): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\includes\\\\error_handler.php\",\"line\":123,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=banks\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:03:41'),
(33, 1, 'ERROR: FATAL ERROR', NULL, NULL, '{\"message\":\"Cannot redeclare City::getAllWithRelations()\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\models\\\\City.php\",\"line\":91,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=banks\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:03:41'),
(34, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"file_put_contents(logs\\/error.log): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\includes\\\\error_handler.php\",\"line\":123,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:05:06'),
(35, 1, 'ERROR: FATAL ERROR', NULL, NULL, '{\"message\":\"Cannot redeclare City::getAllWithRelations()\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\models\\\\City.php\",\"line\":91,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:05:06'),
(36, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"file_put_contents(logs\\/error.log): Failed to open stream: No such file or directory\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\includes\\\\error_handler.php\",\"line\":123,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:07:54'),
(37, 1, 'ERROR: FATAL ERROR', NULL, NULL, '{\"message\":\"Cannot redeclare City::getAllWithRelations()\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\models\\\\City.php\",\"line\":91,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=countries\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:07:54'),
(38, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(39, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(40, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(41, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(42, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(43, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(44, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(45, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(46, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(47, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(48, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(49, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(50, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(51, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(52, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(53, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(54, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(55, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(56, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(57, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(58, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(59, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(60, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(61, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(62, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(63, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(64, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(65, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(66, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(67, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(68, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(69, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(70, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(71, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(72, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:37'),
(73, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(74, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(75, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(76, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(77, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(78, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(79, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(80, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(81, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(82, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(83, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(84, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(85, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(86, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(87, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(88, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(89, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(90, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(91, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(92, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(93, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(94, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(95, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(96, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(97, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(98, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(99, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(100, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(101, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(102, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(103, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(104, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(105, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(106, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(107, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:50'),
(108, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(109, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(110, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(111, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(112, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(113, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(114, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(115, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(116, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(117, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(118, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(119, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:51'),
(120, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(121, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(122, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(123, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(124, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(125, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(126, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(127, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(128, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(129, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(130, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(131, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(132, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(133, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(134, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(135, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(136, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"name\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(137, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":152,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(138, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":153,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(139, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":165,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(140, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":171,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(141, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":176,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(142, 1, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Undefined array key \\\"id\\\"\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\masters\\\\index.php\",\"line\":187,\"request_uri\":\"\\/project\\/admin\\/masters\\/?type=boq\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:52'),
(143, 1, 'CREATE_SITE', 'sites', 1, NULL, '{\"site_id\":\"abcd123\",\"store_id\":\"123abcd\",\"location\":\"mumbai\",\"country_id\":1,\"state_id\":1,\"city_id\":1,\"branch\":\"mumbai\",\"remarks\":\"\",\"po_number\":\"123\",\"po_date\":\"2025-12-31\",\"customer_id\":11,\"bank_id\":4,\"vendor\":\"\",\"activity_status\":\"\",\"is_delegate\":0,\"delegated_vendor\":\"\",\"survey_status\":0,\"installation_status\":0,\"is_material_request_generated\":0,\"survey_submission_date\":null,\"installation_date\":null,\"created_by\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 20:15:57'),
(144, 1, 'UPDATE_SITE', 'sites', 1, '{\"id\":1,\"site_id\":\"abcd123\",\"store_id\":\"123abcd\",\"location\":\"mumbai\",\"city\":null,\"city_id\":1,\"state\":null,\"state_id\":1,\"country\":null,\"country_id\":1,\"branch\":\"mumbai\",\"remarks\":\"\",\"po_number\":\"123\",\"po_date\":\"2025-12-31\",\"customer\":null,\"customer_id\":11,\"bank\":null,\"bank_id\":4,\"vendor\":\"\",\"activity_status\":\"\",\"is_delegate\":0,\"delegated_vendor\":\"\",\"survey_status\":0,\"installation_status\":0,\"is_material_request_generated\":0,\"survey_submission_date\":null,\"installation_date\":null,\"created_at\":\"2025-11-02 01:45:57\",\"created_by\":\"admin\",\"updated_at\":\"2025-11-02 01:45:57\",\"updated_by\":null}', '{\"site_id\":\"abcd123\",\"store_id\":\"123abcd\",\"location\":\"mumbai\",\"country_id\":1,\"state_id\":1,\"city_id\":1,\"branch\":\"mumbai\",\"remarks\":\"\",\"po_number\":\"123\",\"po_date\":\"2025-12-31\",\"customer_id\":11,\"bank_id\":4,\"vendor\":\"\",\"activity_status\":\"Pending\",\"is_delegate\":0,\"delegated_vendor\":\"\",\"survey_status\":0,\"installation_status\":0,\"is_material_request_generated\":0,\"survey_submission_date\":null,\"installation_date\":null,\"updated_by\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 20:35:03'),
(145, NULL, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Cannot modify header information - headers already sent by (output started at C:\\\\xampp\\\\htdocs\\\\project\\\\test_bulk_direct.php:18)\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\bulk_upload.php\",\"line\":10,\"request_uri\":\"Unknown\"}', NULL, 'Unknown', 'Unknown', '2025-11-01 18:24:00'),
(146, NULL, 'ERROR: WARNING', NULL, NULL, '{\"message\":\"Cannot modify header information - headers already sent by (output started at C:\\\\xampp\\\\htdocs\\\\project\\\\test_bulk_direct.php:18)\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\bulk_upload.php\",\"line\":10,\"request_uri\":\"Unknown\"}', NULL, 'Unknown', 'Unknown', '2025-11-01 18:24:21'),
(147, NULL, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"fgetcsv(): the $escape parameter must be provided as its default value will change\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\bulk_upload.php\",\"line\":183,\"request_uri\":\"Unknown\"}', NULL, 'Unknown', 'Unknown', '2025-11-01 18:24:21'),
(148, NULL, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"fgetcsv(): the $escape parameter must be provided as its default value will change\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\bulk_upload.php\",\"line\":184,\"request_uri\":\"Unknown\"}', NULL, 'Unknown', 'Unknown', '2025-11-01 18:24:21'),
(149, NULL, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"fgetcsv(): the $escape parameter must be provided as its default value will change\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\bulk_upload.php\",\"line\":184,\"request_uri\":\"Unknown\"}', NULL, 'Unknown', 'Unknown', '2025-11-01 18:24:21'),
(150, NULL, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"fgetcsv(): the $escape parameter must be provided as its default value will change\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\bulk_upload.php\",\"line\":184,\"request_uri\":\"Unknown\"}', NULL, 'Unknown', 'Unknown', '2025-11-01 18:24:21'),
(151, 1, 'DELETE_SITE', 'sites', 2, '{\"id\":2,\"site_id\":\"TEST001\",\"store_id\":\"STORE001\",\"location\":\"Test Location 1\",\"city\":\"Mumbai\",\"city_id\":1,\"state\":\"Maharashtra\",\"state_id\":1,\"country\":\"India\",\"country_id\":1,\"branch\":\"Test Branch\",\"remarks\":\"Test upload\",\"po_number\":\"PO2024001\",\"po_date\":\"2024-01-15\",\"customer\":\"Reliance Industries Ltd\",\"customer_id\":1,\"bank\":\"HDFC Bank\",\"bank_id\":2,\"vendor\":null,\"activity_status\":null,\"is_delegate\":0,\"delegated_vendor\":null,\"survey_status\":0,\"installation_status\":0,\"is_material_request_generated\":0,\"survey_submission_date\":null,\"installation_date\":null,\"created_at\":\"2025-11-02 05:24:21\",\"created_by\":\"bulk_upload\",\"updated_at\":\"2025-11-02 05:24:21\",\"updated_by\":null}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 00:29:57'),
(152, 1, 'DELETE_SITE', 'sites', 3, '{\"id\":3,\"site_id\":\"TEST002\",\"store_id\":\"STORE002\",\"location\":\"Test Location 2\",\"city\":\"Bangalore\",\"city_id\":4,\"state\":\"Karnataka\",\"state_id\":2,\"country\":\"India\",\"country_id\":1,\"branch\":\"Test Branch 2\",\"remarks\":\"Test upload 2\",\"po_number\":\"PO2024002\",\"po_date\":\"2024-01-20\",\"customer\":\"Tata Consultancy Services\",\"customer_id\":2,\"bank\":\"ICICI Bank\",\"bank_id\":3,\"vendor\":null,\"activity_status\":null,\"is_delegate\":0,\"delegated_vendor\":null,\"survey_status\":0,\"installation_status\":0,\"is_material_request_generated\":0,\"survey_submission_date\":null,\"installation_date\":null,\"created_at\":\"2025-11-02 05:24:21\",\"created_by\":\"bulk_upload\",\"updated_at\":\"2025-11-02 05:24:21\",\"updated_by\":null}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 00:30:02'),
(153, 1, 'DELETE_SITE', 'sites', 1, '{\"id\":1,\"site_id\":\"abcd123\",\"store_id\":\"123abcd\",\"location\":\"mumbai\",\"city\":null,\"city_id\":1,\"state\":null,\"state_id\":1,\"country\":null,\"country_id\":1,\"branch\":\"mumbai\",\"remarks\":\"\",\"po_number\":\"123\",\"po_date\":\"2025-12-31\",\"customer\":null,\"customer_id\":11,\"bank\":null,\"bank_id\":4,\"vendor\":\"\",\"activity_status\":\"Pending\",\"is_delegate\":0,\"delegated_vendor\":\"\",\"survey_status\":0,\"installation_status\":0,\"is_material_request_generated\":0,\"survey_submission_date\":null,\"installation_date\":null,\"created_at\":\"2025-11-02 01:45:57\",\"created_by\":\"admin\",\"updated_at\":\"2025-11-02 02:05:03\",\"updated_by\":\"admin\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 00:30:06'),
(154, 1, 'CREATE_SITE', 'sites', 8, NULL, '{\"site_id\":\"RX34\",\"store_id\":\"RX34\",\"location\":\"Reliance Digital, Reliance Retail Limited. The Hive Kandivali the Next To Raghu Leela Mall,.. Reliance Retail Limited, Situated at Icon Next To Raghu Leela Mall, Boisar Gymkhana Road Kandivali West\",\"country_id\":1,\"state_id\":25,\"city_id\":541,\"branch\":\"Kandivali\",\"remarks\":\"\",\"po_number\":\"NA\",\"po_date\":\"2025-12-31\",\"customer_id\":17,\"bank_id\":12,\"vendor\":\"\",\"activity_status\":\"\",\"is_delegate\":0,\"delegated_vendor\":\"\",\"survey_status\":0,\"installation_status\":0,\"is_material_request_generated\":0,\"survey_submission_date\":null,\"installation_date\":null,\"created_by\":\"admin\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-03 09:28:02'),
(155, 1, 'CREATE_USER', 'users', 11, NULL, '{\"username\":\"Ganesh Panchal\",\"email\":\"ganesh@gmail.com\",\"phone\":\"3846736767\",\"password\":\"ganesh@email.com\",\"role\":\"vendor\",\"status\":\"active\",\"vendor_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-03 11:53:40'),
(156, 1, 'DELETE_USER', 'users', 11, '{\"id\":11,\"username\":\"Ganesh Panchal\",\"email\":\"ganesh@gmail.com\",\"phone\":\"3846736767\",\"password_hash\":\"$2y$12$BHOcB6Lw.\\/yeluy8MKL9i.ltbFAVzW7pRJ5XC4G8KbfPPqcgdiNk2\",\"plain_password\":\"ganesh@email.com\",\"jwt_token\":null,\"role\":\"vendor\",\"vendor_id\":3,\"status\":\"active\",\"created_at\":\"2025-11-03 17:23:40\",\"updated_at\":\"2025-11-03 17:23:40\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-03 14:54:15'),
(157, 1, 'CREATE_USER', 'users', 12, NULL, '{\"username\":\"Ganesh Panchal\",\"email\":\"ganesh@gmail.com\",\"phone\":\"8475847832\",\"password\":\"ganesh@123\",\"role\":\"vendor\",\"status\":\"active\",\"vendor_id\":5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-03 14:55:11'),
(158, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:44'),
(159, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:44'),
(160, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:44'),
(161, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:44'),
(162, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:44'),
(163, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:44'),
(164, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:44'),
(165, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:44'),
(166, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(167, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(168, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(169, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(170, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(171, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(172, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(173, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(174, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(175, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(176, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(177, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(178, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(179, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(180, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(181, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(182, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(183, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(184, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(185, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(186, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(187, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(188, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(189, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(190, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(191, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(192, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(193, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(194, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(195, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(196, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(197, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(198, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(199, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(200, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(201, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(202, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:43:45'),
(203, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(204, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(205, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(206, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(207, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(208, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(209, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(210, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(211, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(212, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(213, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(214, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(215, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(216, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(217, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(218, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(219, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(220, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(221, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(222, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(223, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(224, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(225, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(226, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(227, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(228, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(229, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(230, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(231, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(232, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(233, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(234, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(235, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(236, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(237, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(238, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(239, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(240, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(241, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(242, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(243, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(244, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(245, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(246, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(247, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:48'),
(248, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:50'),
(249, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:50'),
(250, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:50'),
(251, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:50'),
(252, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(253, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(254, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(255, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(256, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(257, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(258, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(259, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(260, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(261, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(262, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(263, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(264, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(265, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(266, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(267, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(268, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(269, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(270, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(271, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(272, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(273, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(274, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(275, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(276, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(277, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(278, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(279, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(280, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(281, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(282, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(283, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(284, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(285, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(286, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(287, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(288, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(289, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(290, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(291, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(292, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:51'),
(293, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(294, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(295, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(296, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(297, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(298, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(299, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(300, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(301, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(302, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(303, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(304, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(305, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(306, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(307, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(308, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(309, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(310, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(311, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(312, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(313, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(314, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(315, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(316, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(317, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(318, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(319, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(320, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(321, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(322, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(323, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(324, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(325, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(326, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(327, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(328, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(329, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(330, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(331, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(332, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(333, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(334, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(335, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(336, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(337, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:47:56'),
(338, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(339, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(340, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(341, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(342, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(343, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(344, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(345, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(346, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(347, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(348, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(349, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(350, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(351, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(352, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(353, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(354, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(355, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(356, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(357, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(358, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(359, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(360, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(361, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(362, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(363, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(364, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(365, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(366, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(367, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(368, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(369, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(370, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(371, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(372, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(373, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(374, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(375, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(376, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(377, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(378, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(379, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(380, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(381, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(382, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:02'),
(383, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(384, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(385, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(386, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(387, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(388, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(389, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(390, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(391, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(392, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(393, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(394, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(395, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(396, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(397, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(398, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(399, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(400, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(401, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(402, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(403, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(404, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(405, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(406, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(407, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(408, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(409, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(410, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(411, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(412, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(413, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(414, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(415, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(416, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(417, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(418, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(419, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(420, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(421, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(422, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(423, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(424, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(425, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(426, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(427, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:16'),
(428, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(429, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(430, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(431, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(432, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(433, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(434, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(435, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(436, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(437, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(438, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(439, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(440, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(441, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(442, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(443, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(444, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(445, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(446, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(447, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(448, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(449, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(450, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(451, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(452, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(453, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(454, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(455, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(456, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(457, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(458, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(459, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(460, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(461, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(462, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(463, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(464, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(465, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(466, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(467, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(468, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(469, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(470, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(471, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(472, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=3\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:22'),
(473, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(474, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(475, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(476, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(477, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(478, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(479, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(480, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(481, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(482, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(483, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(484, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(485, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(486, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(487, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(488, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(489, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(490, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(491, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(492, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(493, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(494, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(495, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(496, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(497, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(498, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(499, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(500, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(501, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(502, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(503, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(504, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(505, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(506, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(507, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(508, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(509, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(510, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(511, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(512, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(513, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(514, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(515, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(516, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(517, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(518, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(519, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(520, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(521, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(522, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(523, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(524, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(525, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(526, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(527, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(528, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(529, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(530, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(531, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(532, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/?page=2\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 06:54:25'),
(533, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(534, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(535, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(536, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(537, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(538, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(539, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(540, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(541, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(542, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(543, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(544, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(545, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(546, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(547, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(548, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(549, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(550, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(551, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(552, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(553, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(554, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(555, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(556, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(557, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(558, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(559, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(560, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(561, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(562, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(563, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(564, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(565, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(566, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(567, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(568, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(569, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(570, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(571, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(572, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(573, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(574, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(575, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(576, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(577, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:06:29'),
(578, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(579, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(580, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(581, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(582, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(583, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(584, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(585, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(586, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(587, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(588, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(589, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(590, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(591, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(592, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(593, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(594, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(595, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(596, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(597, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(598, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(599, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(600, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(601, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(602, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(603, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(604, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(605, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(606, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(607, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(608, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(609, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(610, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(611, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(612, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(613, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(614, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(615, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(616, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(617, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(618, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(619, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(620, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(621, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(622, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 07:55:11'),
(623, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(624, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(625, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(626, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(627, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(628, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(629, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(630, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(631, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(632, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(633, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(634, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(635, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(636, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(637, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(638, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(639, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(640, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(641, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(642, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(643, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(644, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(645, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(646, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(647, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(648, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(649, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(650, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(651, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(652, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(653, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(654, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(655, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(656, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(657, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(658, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(659, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(660, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(661, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(662, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(663, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(664, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(665, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(666, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(667, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:06'),
(668, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(669, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(670, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(671, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(672, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(673, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(674, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(675, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(676, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(677, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(678, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(679, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(680, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(681, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(682, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(683, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(684, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(685, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(686, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(687, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(688, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(689, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(690, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(691, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(692, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(693, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":133,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47'),
(694, 1, 'ERROR: DEPRECATED', NULL, NULL, '{\"message\":\"htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated\",\"file\":\"C:\\\\xampp\\\\htdocs\\\\project\\\\admin\\\\sites\\\\index.php\",\"line\":134,\"request_uri\":\"\\/project\\/admin\\/sites\\/\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:14:47');

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
(1, 'State Bank of India', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(2, 'HDFC Bank', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(3, 'ICICI Bank', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(4, 'Axis Bank', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(5, 'Punjab National Bank', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(6, 'Bank of Baroda', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(7, 'Canara Bank', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(8, 'Union Bank of India', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(9, 'Indian Bank', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(10, 'Central Bank of India', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(11, 'IDFC First Bank', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(12, 'IndusInd Bank', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(13, 'Kotak Mahindra Bank', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44'),
(14, 'Yes', 'active', '2025-11-01 18:47:44', '2025-11-01 19:54:15'),
(15, 'Federal Bank', 'active', '2025-11-01 18:47:44', '2025-11-01 18:47:44');

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
(541, 'Mumbai', 25, 1, 4, 'active', '2025-07-02 12:48:28', '2025-11-04 13:12:08'),
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
(595, 'Bangalore', 12, 1, NULL, 'active', '2025-11-04 13:25:04', '2025-11-04 13:25:04');

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
(17, 'A', 'active', '2025-11-03 08:50:29', '2025-11-03 08:50:29'),
(18, 'B', 'active', '2025-11-03 08:57:48', '2025-11-03 08:57:48'),
(19, 'Customer A', 'active', '2025-11-04 13:11:13', '2025-11-04 13:11:13');

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

--
-- Dumping data for table `installation_delegations`
--

INSERT INTO `installation_delegations` (`id`, `survey_id`, `site_id`, `vendor_id`, `delegated_by`, `delegation_date`, `expected_start_date`, `expected_completion_date`, `actual_start_date`, `installation_start_time`, `actual_completion_date`, `priority`, `installation_type`, `status`, `special_instructions`, `notes`, `hold_reason`, `updated_by`, `created_at`, `updated_at`) VALUES
(2, 4, 4, 1, 1, '2025-11-03 01:22:16', '2024-12-01', '2024-12-15', '2025-11-04 08:15:41', '2025-11-04 06:49:00', NULL, 'medium', 'standard', 'in_progress', 'Test direct delegation', 'do somet/hing', NULL, 4, '2025-11-03 06:52:16', '2025-11-04 08:15:41'),
(3, 2, 5, 1, 1, '2025-10-27 04:53:04', '2025-10-31', '2025-11-17', NULL, NULL, NULL, 'medium', 'standard', 'assigned', NULL, 'Installation delegated to vendor for completion.', NULL, NULL, '2025-11-01 04:53:04', '2025-11-04 10:23:04'),
(4, 4, 4, 4, 1, '2025-10-23 04:53:04', '2025-10-21', '2025-11-17', NULL, NULL, NULL, 'low', 'standard', 'on_hold', NULL, 'Installation delegated to vendor for completion.', NULL, NULL, '2025-10-13 04:53:04', '2025-11-04 10:23:04'),
(5, 17, 26, 6, 1, '2025-10-21 04:53:04', '2025-10-28', '2025-11-23', NULL, NULL, NULL, 'high', 'standard', 'in_progress', NULL, 'Installation delegated to vendor for completion.', NULL, NULL, '2025-10-19 04:53:04', '2025-11-04 10:23:04'),
(6, 19, 28, 5, 1, '2025-10-17 04:53:04', '2025-11-01', '2025-11-26', NULL, NULL, NULL, 'low', 'standard', 'completed', NULL, 'Installation delegated to vendor for completion.', NULL, NULL, '2025-10-28 04:53:04', '2025-11-04 10:23:04'),
(7, 23, 34, 4, 1, '2025-10-24 04:53:04', '2025-10-26', '2025-11-15', NULL, NULL, NULL, 'high', 'standard', 'in_progress', NULL, 'Installation delegated to vendor for completion.', NULL, NULL, '2025-10-30 04:53:04', '2025-11-04 10:23:04'),
(8, 24, 35, 5, 1, '2025-10-06 04:53:04', '2025-10-20', '2025-11-13', NULL, NULL, NULL, 'medium', 'standard', 'assigned', NULL, 'Installation delegated to vendor for completion.', NULL, NULL, '2025-10-15 04:53:04', '2025-11-04 10:23:04'),
(9, 27, 38, 1, 1, '2025-10-27 04:53:04', '2025-10-24', '2025-11-28', NULL, NULL, NULL, 'high', 'standard', 'completed', NULL, 'Installation delegated to vendor for completion.', NULL, NULL, '2025-10-15 04:53:04', '2025-11-04 10:23:04');

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

--
-- Dumping data for table `inventory_dispatches`
--

INSERT INTO `inventory_dispatches` (`id`, `dispatch_number`, `dispatch_date`, `material_request_id`, `site_id`, `vendor_id`, `contact_person_name`, `contact_person_phone`, `delivery_address`, `courier_name`, `tracking_number`, `expected_delivery_date`, `actual_delivery_date`, `dispatch_status`, `total_items`, `total_value`, `dispatched_by`, `received_by_name`, `received_by_signature`, `delivery_remarks`, `created_at`, `updated_at`, `delivery_date`, `delivery_time`, `received_by`, `received_by_phone`, `actual_delivery_address`, `delivery_notes`, `lr_copy_path`, `additional_documents`, `item_confirmations`, `confirmed_by`, `confirmation_date`) VALUES
(1, 'DSP20251102001', '2025-11-02', 1, 5, 1, 'Aniruddh', '83927847374', 'Mumbai', 'bluedart', '84738KJKFH', '2025-11-05', NULL, 'dispatched', 2, 0.00, 1, NULL, NULL, 'First material dispatch Request Notes\r\n', '2025-11-02 15:53:20', '2025-11-02 19:44:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'DSP20251102002', '2025-11-02', 3, 4, 4, 'Vikas Pal', '8435845639', 'Delhi', 'Bluedart', '8958457868', '2025-11-06', NULL, 'delivered', 2, 0.00, 1, NULL, NULL, 'please send these materials we will start the installation.', '2025-11-02 19:27:15', '2025-11-02 20:06:05', '2025-11-02', '20:06:05', 'fasttrack_installation', '', 'Delhi', 'Request accepted by vendor on 2025-11-02 20:06:05', NULL, NULL, '[{\"boq_item_id\":27,\"received_quantity\":\"2.00\",\"condition\":\"good\",\"notes\":\"Accepted via bulk request acceptance\"},{\"boq_item_id\":23,\"received_quantity\":\"2.00\",\"condition\":\"good\",\"notes\":\"Accepted via bulk request acceptance\"}]', 7, '2025-11-02 14:36:05'),
(3, 'DSP20251102003', '2025-11-02', 3, 4, 4, 'Vikas Pal', '88473657', 'Delhi', 'bluedart', '34783756784', '2025-11-06', NULL, 'prepared', 2, 0.00, 1, NULL, NULL, 'please send these materials we will start the installation.', '2025-11-02 19:52:57', '2025-11-02 19:52:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'DSP20251102004', '2025-11-02', 5, 7, 3, 'Ashok Patel', '327863756', 'some address', 'fedex', '8986645GGH', '2025-12-31', NULL, 'dispatched', 8, 1468.00, 1, NULL, NULL, 'something', '2025-11-02 23:31:17', '2025-11-02 23:31:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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

--
-- Dumping data for table `inventory_dispatch_items`
--

INSERT INTO `inventory_dispatch_items` (`id`, `dispatch_id`, `inventory_stock_id`, `boq_item_id`, `unit_cost`, `item_condition`, `dispatch_notes`, `warranty_period`, `created_at`) VALUES
(1, 4, 17, 22, 100.00, 'new', '', NULL, '2025-11-02 23:31:17'),
(2, 4, 18, 22, 100.00, 'new', '', NULL, '2025-11-02 23:31:17'),
(3, 4, 19, 22, 100.00, 'new', '', NULL, '2025-11-02 23:31:17'),
(4, 4, 27, 12, 234.00, 'new', 'some', NULL, '2025-11-02 23:31:17'),
(5, 4, 29, 12, 234.00, 'new', 'some', NULL, '2025-11-02 23:31:17'),
(6, 4, 31, 12, 234.00, 'new', 'some', NULL, '2025-11-02 23:31:17'),
(7, 4, 33, 12, 234.00, 'new', 'some', NULL, '2025-11-02 23:31:17'),
(8, 4, 36, 12, 232.00, 'new', 'some', NULL, '2025-11-02 23:31:17');

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

--
-- Dumping data for table `inventory_stock`
--

INSERT INTO `inventory_stock` (`id`, `boq_item_id`, `serial_number`, `batch_number`, `unit_cost`, `purchase_date`, `expiry_date`, `warranty_period`, `location_type`, `location_id`, `location_name`, `item_status`, `quality_status`, `supplier_name`, `purchase_order_number`, `invoice_number`, `dispatch_id`, `dispatched_at`, `delivered_at`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'CM001', NULL, 150.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(2, 1, 'CM002', NULL, 150.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(3, 1, 'CM003', NULL, 150.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(4, 3, 'RACK001', NULL, 2500.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(5, 3, 'RACK002', NULL, 2500.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(6, 7, 'PC001', NULL, 25.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(7, 7, 'PC002', NULL, 25.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(8, 7, 'PC003', NULL, 25.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(9, 7, 'PC004', NULL, 25.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(10, 7, 'PC005', NULL, 25.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(11, 8, 'POLE001', NULL, 75.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(12, 8, 'POLE002', NULL, 75.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(13, 8, 'POLE003', NULL, 75.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(14, 9, 'JUN001', NULL, 45.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(15, 9, 'JUN002', NULL, 45.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(16, 9, 'JUN003', NULL, 45.00, NULL, NULL, NULL, 'warehouse', NULL, NULL, 'available', 'good', 'Sample Supplier', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-02 21:55:32', '2025-11-02 21:55:32'),
(17, 22, '-001', '1way1', 100.00, '2025-12-30', '2027-10-30', 2, 'warehouse', NULL, 'Andheri', 'dispatched', 'good', 'Anirudh', NULL, NULL, 4, '2025-11-02 23:31:17', NULL, 'something', 1, 1, '2025-11-02 23:17:30', '2025-11-02 23:31:17'),
(18, 22, '-002', '1way1', 100.00, '2025-12-30', '2027-10-30', 2, 'warehouse', NULL, 'Andheri', 'dispatched', 'good', 'Anirudh', NULL, NULL, 4, '2025-11-02 23:31:17', NULL, 'something', 1, 1, '2025-11-02 23:17:30', '2025-11-02 23:31:17'),
(19, 22, '-003', '1way1', 100.00, '2025-12-30', '2027-10-30', 2, 'warehouse', NULL, 'Andheri', 'dispatched', 'good', 'Anirudh', NULL, NULL, 4, '2025-11-02 23:31:17', NULL, 'something', 1, 1, '2025-11-02 23:17:30', '2025-11-02 23:31:17'),
(20, 22, '-004', '1way1', 100.00, '2025-12-30', '2027-10-30', 2, 'warehouse', NULL, 'Andheri', 'available', 'good', 'Anirudh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:17:30', '2025-11-02 23:17:30'),
(21, 22, '-005', '1way1', 100.00, '2025-12-30', '2027-10-30', 2, 'warehouse', NULL, 'Andheri', 'available', 'good', 'Anirudh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:17:30', '2025-11-02 23:17:30'),
(22, 22, '-006', '1way1', 100.00, '2025-12-30', '2027-10-30', 2, 'warehouse', NULL, 'Andheri', 'available', 'good', 'Anirudh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:17:30', '2025-11-02 23:17:30'),
(23, 22, '-007', '1way1', 100.00, '2025-12-30', '2027-10-30', 2, 'warehouse', NULL, 'Andheri', 'available', 'good', 'Anirudh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:17:30', '2025-11-02 23:17:30'),
(24, 22, '-008', '1way1', 100.00, '2025-12-30', '2027-10-30', 2, 'warehouse', NULL, 'Andheri', 'available', 'good', 'Anirudh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:17:30', '2025-11-02 23:17:30'),
(25, 22, '-009', '1way1', 100.00, '2025-12-30', '2027-10-30', 2, 'warehouse', NULL, 'Andheri', 'available', 'good', 'Anirudh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:17:30', '2025-11-02 23:17:30'),
(26, 22, '-010', '1way1', 100.00, '2025-12-30', '2027-10-30', 2, 'warehouse', NULL, 'Andheri', 'available', 'good', 'Anirudh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:17:30', '2025-11-02 23:17:30'),
(27, 12, 'CAT_0_0', 'cat6_u', 234.00, '2025-10-30', '2029-12-31', 2, 'warehouse', NULL, 'Mumbai', 'dispatched', 'good', 'Aniruddh', NULL, NULL, 4, '2025-11-02 23:31:17', NULL, 'Something', 1, 1, '2025-11-02 23:20:52', '2025-11-02 23:31:17'),
(29, 12, 'CAT6_0', 'cat6_u', 234.00, '2025-10-30', '2029-12-31', 2, 'warehouse', NULL, 'Mumbai', 'dispatched', 'good', 'Aniruddh', NULL, NULL, 4, '2025-11-02 23:31:17', NULL, 'Something', 1, 1, '2025-11-02 23:21:13', '2025-11-02 23:31:17'),
(31, 12, 'CAT6Cable', 'cat6_u', 234.00, '2025-10-30', '2029-12-31', 2, 'warehouse', NULL, 'Mumbai', 'dispatched', 'good', 'Aniruddh', NULL, NULL, 4, '2025-11-02 23:31:17', NULL, 'Something', 1, 1, '2025-11-02 23:21:27', '2025-11-02 23:31:17'),
(33, 12, 'some_radon', 'cat6_u', 234.00, '2025-10-30', '2029-12-31', 2, 'warehouse', NULL, 'Mumbai', 'dispatched', 'good', 'Aniruddh', NULL, NULL, 4, '2025-11-02 23:31:17', NULL, 'Something', 1, 1, '2025-11-02 23:21:40', '2025-11-02 23:31:17'),
(36, 12, 'ITM12_20251102232918-001', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'dispatched', 'good', 'ANiruddh', NULL, NULL, 4, '2025-11-02 23:31:17', NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:31:17'),
(37, 12, 'ITM12_20251102232918-002', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(38, 12, 'ITM12_20251102232918-003', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(39, 12, 'ITM12_20251102232918-004', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(40, 12, 'ITM12_20251102232918-005', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(41, 12, 'ITM12_20251102232918-006', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(42, 12, 'ITM12_20251102232918-007', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(43, 12, 'ITM12_20251102232918-008', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(44, 12, 'ITM12_20251102232918-009', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(45, 12, 'ITM12_20251102232918-010', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(46, 12, 'ITM12_20251102232918-011', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(47, 12, 'ITM12_20251102232918-012', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(48, 12, 'ITM12_20251102232918-013', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(49, 12, 'ITM12_20251102232918-014', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(50, 12, 'ITM12_20251102232918-015', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(51, 12, 'ITM12_20251102232918-016', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(52, 12, 'ITM12_20251102232918-017', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(53, 12, 'ITM12_20251102232918-018', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(54, 12, 'ITM12_20251102232918-019', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18'),
(55, 12, 'ITM12_20251102232918-020', 'cat6batch', 232.00, '2025-12-31', '2027-12-31', 2, 'warehouse', NULL, 'mumbai', 'available', 'good', 'ANiruddh', NULL, NULL, NULL, NULL, NULL, 'something', 1, 1, '2025-11-02 23:29:18', '2025-11-02 23:29:18');

-- --------------------------------------------------------

--
-- Stand-in structure for view `inventory_stock_summary`
-- (See below for the actual view)
--
CREATE TABLE `inventory_stock_summary` (
);

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

--
-- Dumping data for table `inventory_tracking`
--

INSERT INTO `inventory_tracking` (`id`, `boq_item_id`, `batch_number`, `serial_number`, `current_location_type`, `current_location_id`, `current_location_name`, `site_id`, `vendor_id`, `dispatch_id`, `inward_id`, `quantity`, `status`, `last_movement_date`, `movement_remarks`, `updated_by`) VALUES
(1, 3, 'q', 'q', 'in_transit', NULL, 'In Transit to SITE002', 5, 1, 1, NULL, 1.00, 'dispatched', '2025-11-02 15:53:20', 'Dispatched via bluedart', 1),
(2, 3, 'w', 'w', 'in_transit', NULL, 'In Transit to SITE002', 5, 1, 1, NULL, 1.00, 'dispatched', '2025-11-02 15:53:20', 'Dispatched via bluedart', 1),
(3, 3, 'e', 'e', 'in_transit', NULL, 'In Transit to SITE002', 5, 1, 1, NULL, 1.00, 'dispatched', '2025-11-02 15:53:20', 'Dispatched via bluedart', 1),
(4, 7, 'a', 'a', 'in_transit', NULL, 'In Transit to SITE002', 5, 1, 1, NULL, 1.00, 'dispatched', '2025-11-02 15:53:20', 'Dispatched via bluedart', 1),
(5, 7, 's', 's', 'in_transit', NULL, 'In Transit to SITE002', 5, 1, 1, NULL, 1.00, 'dispatched', '2025-11-02 15:53:20', 'Dispatched via bluedart', 1),
(6, 7, 'd', 'd', 'in_transit', NULL, 'In Transit to SITE002', 5, 1, 1, NULL, 1.00, 'dispatched', '2025-11-02 15:53:20', 'Dispatched via bluedart', 1),
(7, 7, 'f', 'f', 'in_transit', NULL, 'In Transit to SITE002', 5, 1, 1, NULL, 1.00, 'dispatched', '2025-11-02 15:53:20', 'Dispatched via bluedart', 1),
(8, 7, 'g', 'g', 'in_transit', NULL, 'In Transit to SITE002', 5, 1, 1, NULL, 1.00, 'dispatched', '2025-11-02 15:53:20', 'Dispatched via bluedart', 1),
(9, 27, 'h', 'ajku', 'in_transit', NULL, 'In Transit to SITE001', 4, 4, 2, NULL, 1.00, 'dispatched', '2025-11-02 19:27:15', 'Dispatched via Bluedart', 1),
(10, 27, 'hj', 'jhjh', 'in_transit', NULL, 'In Transit to SITE001', 4, 4, 2, NULL, 1.00, 'dispatched', '2025-11-02 19:27:15', 'Dispatched via Bluedart', 1),
(11, 23, 'hj', 'bjhj', 'in_transit', NULL, 'In Transit to SITE001', 4, 4, 2, NULL, 1.00, 'dispatched', '2025-11-02 19:27:15', 'Dispatched via Bluedart', 1),
(12, 23, 'hh', 'hjhj', 'in_transit', NULL, 'In Transit to SITE001', 4, 4, 2, NULL, 1.00, 'dispatched', '2025-11-02 19:27:15', 'Dispatched via Bluedart', 1),
(13, 27, 'hh', 'hjk', 'in_transit', NULL, 'In Transit to SITE001', 4, 4, 3, NULL, 1.00, 'dispatched', '2025-11-02 19:52:57', 'Dispatched via bluedart', 1),
(14, 27, 'hkh', 'khj', 'in_transit', NULL, 'In Transit to SITE001', 4, 4, 3, NULL, 1.00, 'dispatched', '2025-11-02 19:52:57', 'Dispatched via bluedart', 1),
(15, 23, 'hkh', 'hkh', 'in_transit', NULL, 'In Transit to SITE001', 4, 4, 3, NULL, 1.00, 'dispatched', '2025-11-02 19:52:57', 'Dispatched via bluedart', 1),
(16, 23, 'hgf', 'khghg', 'in_transit', NULL, 'In Transit to SITE001', 4, 4, 3, NULL, 1.00, 'dispatched', '2025-11-02 19:52:57', 'Dispatched via bluedart', 1),
(17, 27, 'fsdkh', NULL, '', NULL, 'Vendor Site - Site ID 4', 4, 4, 2, NULL, 2.00, '', '2025-11-02 20:06:05', 'Delivered and accepted by vendor', 7),
(18, 23, 'jh', NULL, '', NULL, 'Vendor Site - Site ID 4', 4, 4, 2, NULL, 2.00, '', '2025-11-02 20:06:05', 'Delivered and accepted by vendor', 7),
(19, 22, 'h', 'g', 'in_transit', NULL, 'In Transit to SITE0029027', 7, 3, 4, NULL, 1.00, 'dispatched', '2025-11-02 23:31:17', 'Dispatched via fedex', 1),
(20, 22, 'gh', 'ghgh', 'in_transit', NULL, 'In Transit to SITE0029027', 7, 3, 4, NULL, 1.00, 'dispatched', '2025-11-02 23:31:17', 'Dispatched via fedex', 1),
(21, 22, 'kl', 'ghr', 'in_transit', NULL, 'In Transit to SITE0029027', 7, 3, 4, NULL, 1.00, 'dispatched', '2025-11-02 23:31:17', 'Dispatched via fedex', 1),
(22, 12, 'j', 'j', 'in_transit', NULL, 'In Transit to SITE0029027', 7, 3, 4, NULL, 1.00, 'dispatched', '2025-11-02 23:31:17', 'Dispatched via fedex', 1),
(23, 12, 'g', 'l', 'in_transit', NULL, 'In Transit to SITE0029027', 7, 3, 4, NULL, 1.00, 'dispatched', '2025-11-02 23:31:17', 'Dispatched via fedex', 1),
(24, 12, 'f', 'f', 'in_transit', NULL, 'In Transit to SITE0029027', 7, 3, 4, NULL, 1.00, 'dispatched', '2025-11-02 23:31:17', 'Dispatched via fedex', 1),
(25, 12, 'n', 'b', 'in_transit', NULL, 'In Transit to SITE0029027', 7, 3, 4, NULL, 1.00, 'dispatched', '2025-11-02 23:31:17', 'Dispatched via fedex', 1),
(26, 12, 'y', 't', 'in_transit', NULL, 'In Transit to SITE0029027', 7, 3, 4, NULL, 1.00, 'dispatched', '2025-11-02 23:31:17', 'Dispatched via fedex', 1);

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

--
-- Dumping data for table `material_requests`
--

INSERT INTO `material_requests` (`id`, `site_id`, `vendor_id`, `survey_id`, `request_date`, `required_date`, `request_notes`, `items`, `status`, `processed_by`, `processed_date`, `dispatch_details`, `created_date`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 2, '2025-11-02', '2025-11-05', 'Request Notes\r\n', '[{\"boq_item_id\":3,\"item_code\":\"RACK-12U-001\",\"quantity\":3,\"unit\":\"Nos\",\"notes\":\"12U rack\"},{\"boq_item_id\":7,\"item_code\":\"PC-1M-001\",\"quantity\":5,\"unit\":\"Nos\",\"notes\":\"Request NotesRequest Notes\"}]', 'dispatched', 1, '2025-11-02 15:53:20', NULL, '2025-11-02 13:05:44', '2025-11-02 13:05:44', '2025-11-02 19:34:18'),
(3, 4, 4, 4, '2025-11-02', '2025-11-06', 'please send these materials we will start the installation.', '[{\"boq_item_id\":27,\"item_code\":\"POLE-2M-001\",\"quantity\":2,\"unit\":\"Nos\",\"notes\":\"2 Meter Pole needed\"},{\"boq_item_id\":23,\"item_code\":\"JUN-4W-001\",\"quantity\":2,\"unit\":\"Nos\",\"notes\":\"Four Way Junction needed\"}]', '', 7, '2025-11-02 20:06:05', NULL, '2025-11-02 19:13:36', '2025-11-02 19:13:36', '2025-11-02 20:06:05'),
(4, 7, 3, 5, '2025-11-02', '0000-00-00', '', '[]', 'draft', NULL, NULL, NULL, '2025-11-02 22:49:14', '2025-11-02 22:49:14', '2025-11-02 22:49:14'),
(5, 7, 3, 5, '2025-11-02', '2025-12-31', 'something', '[{\"boq_item_id\":22,\"item_code\":\"JUN-3W-001\",\"quantity\":3,\"unit\":\"Nos\",\"notes\":\"\"},{\"boq_item_id\":12,\"item_code\":\"CAT6-UTP-001\",\"quantity\":5,\"unit\":\"Meter\",\"notes\":\"some\"}]', 'dispatched', 1, '2025-11-02 23:31:17', NULL, '2025-11-02 22:50:15', '2025-11-02 22:50:15', '2025-11-02 23:31:17'),
(6, 9, 2, NULL, '2025-10-11', '2025-11-12', 'Materials required for site installation project.', '[{\"material_name\":\"IP Camera\",\"quantity\":17,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Hard Disk\",\"quantity\":6,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Network Cable\",\"quantity\":2,\"unit\":\"meter\",\"reason\":\"Required for installation\"},{\"material_name\":\"Network Cable\",\"quantity\":5,\"unit\":\"meter\",\"reason\":\"Required for installation\"}]', 'completed', NULL, NULL, NULL, '2025-10-27 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(7, 11, 2, NULL, '2025-10-09', '2025-11-11', 'Materials required for site installation project.', '[{\"material_name\":\"POE Switch\",\"quantity\":7,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Network Cable\",\"quantity\":16,\"unit\":\"meter\",\"reason\":\"Required for installation\"},{\"material_name\":\"Mounting Bracket\",\"quantity\":15,\"unit\":\"piece\",\"reason\":\"Required for installation\"}]', 'dispatched', NULL, NULL, NULL, '2025-10-26 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(8, 15, 2, NULL, '2025-10-08', '2025-11-16', 'Materials required for site installation project.', '[{\"material_name\":\"Hard Disk\",\"quantity\":18,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"POE Switch\",\"quantity\":2,\"unit\":\"piece\",\"reason\":\"Required for installation\"}]', 'pending', NULL, NULL, NULL, '2025-10-12 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(9, 20, 1, NULL, '2025-10-16', '2025-11-06', 'Materials required for site installation project.', '[{\"material_name\":\"Hard Disk\",\"quantity\":1,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Hard Disk\",\"quantity\":11,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Mounting Bracket\",\"quantity\":18,\"unit\":\"piece\",\"reason\":\"Required for installation\"}]', 'pending', NULL, NULL, NULL, '2025-10-15 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(10, 21, 6, NULL, '2025-10-25', '2025-11-18', 'Materials required for site installation project.', '[{\"material_name\":\"Network Cable\",\"quantity\":20,\"unit\":\"meter\",\"reason\":\"Required for installation\"},{\"material_name\":\"Mounting Bracket\",\"quantity\":10,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"POE Switch\",\"quantity\":13,\"unit\":\"piece\",\"reason\":\"Required for installation\"}]', 'dispatched', NULL, NULL, NULL, '2025-10-18 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(11, 22, 1, NULL, '2025-10-29', '2025-11-19', 'Materials required for site installation project.', '[{\"material_name\":\"Monitor\",\"quantity\":18,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Network Cable\",\"quantity\":17,\"unit\":\"meter\",\"reason\":\"Required for installation\"},{\"material_name\":\"POE Switch\",\"quantity\":1,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"IP Camera\",\"quantity\":19,\"unit\":\"piece\",\"reason\":\"Required for installation\"}]', 'dispatched', NULL, NULL, NULL, '2025-10-30 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(12, 23, 1, NULL, '2025-11-01', '2025-11-18', 'Materials required for site installation project.', '[{\"material_name\":\"Mounting Bracket\",\"quantity\":3,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Hard Disk\",\"quantity\":13,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Monitor\",\"quantity\":11,\"unit\":\"piece\",\"reason\":\"Required for installation\"}]', 'pending', NULL, NULL, NULL, '2025-10-17 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(13, 25, 6, NULL, '2025-10-07', '2025-11-05', 'Materials required for site installation project.', '[{\"material_name\":\"Monitor\",\"quantity\":3,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"IP Camera\",\"quantity\":3,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"IP Camera\",\"quantity\":13,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Mounting Bracket\",\"quantity\":6,\"unit\":\"piece\",\"reason\":\"Required for installation\"}]', 'dispatched', NULL, NULL, NULL, '2025-10-20 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(14, 26, 1, NULL, '2025-10-10', '2025-11-11', 'Materials required for site installation project.', '[{\"material_name\":\"Hard Disk\",\"quantity\":7,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Mounting Bracket\",\"quantity\":20,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"POE Switch\",\"quantity\":15,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"IP Camera\",\"quantity\":1,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Mounting Bracket\",\"quantity\":2,\"unit\":\"piece\",\"reason\":\"Required for installation\"}]', 'pending', NULL, NULL, NULL, '2025-10-17 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(15, 29, 6, NULL, '2025-10-16', '2025-11-09', 'Materials required for site installation project.', '[{\"material_name\":\"IP Camera\",\"quantity\":6,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"POE Switch\",\"quantity\":15,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"POE Switch\",\"quantity\":16,\"unit\":\"piece\",\"reason\":\"Required for installation\"}]', 'pending', NULL, NULL, NULL, '2025-10-10 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(16, 30, 5, NULL, '2025-10-15', '2025-11-19', 'Materials required for site installation project.', '[{\"material_name\":\"IP Camera\",\"quantity\":3,\"unit\":\"piece\",\"reason\":\"Required for installation\"},{\"material_name\":\"Mounting Bracket\",\"quantity\":17,\"unit\":\"piece\",\"reason\":\"Required for installation\"}]', 'dispatched', NULL, NULL, NULL, '2025-10-06 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04'),
(17, 31, 5, NULL, '2025-10-09', '2025-11-10', 'Materials required for site installation project.', '[{\"material_name\":\"Network Cable\",\"quantity\":13,\"unit\":\"meter\",\"reason\":\"Required for installation\"},{\"material_name\":\"Network Cable\",\"quantity\":13,\"unit\":\"meter\",\"reason\":\"Required for installation\"}]', 'approved', NULL, NULL, NULL, '2025-10-30 10:23:04', '2025-11-04 10:23:04', '2025-11-04 10:23:04');

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
(1, NULL, 'Dashboard', 'dashboard', '/admin/dashboard.php', 1, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(2, NULL, 'Sites', 'location', '/admin/sites/', 2, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(10, NULL, 'Administration', 'settings', NULL, 10, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(11, 10, 'Users', 'users', '/admin/users/', 1, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(12, 10, 'Vendors', 'vendor', '/admin/vendors/', 2, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(13, 10, 'Masters', 'settings', '/admin/masters/', 3, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(14, 10, 'BOQ Management', 'boq', '/admin/boq/', 4, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(20, NULL, 'Inventory', 'inventory', NULL, 20, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(21, 20, 'All Stocks', 'inventory', '/admin/inventory/', 1, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(22, 20, 'Material Requests', 'requests', '/admin/requests/', 2, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(23, 20, 'Material Received', 'inventory', '/admin/inventory/inwards/', 3, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(24, 20, 'Material Dispatches', 'inventory', '/admin/inventory/dispatches/', 4, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(30, NULL, 'Operations', 'settings', NULL, 30, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(31, 30, 'Surveys', 'reports', '/admin/surveys/', 1, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(32, 30, 'Installations', 'installation', '/admin/installations/', 2, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36'),
(33, 30, 'Reports', 'reports', '/admin/reports/', 3, 'active', '2025-11-03 21:11:36', '2025-11-03 21:11:36');

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
(4, 'SITE001', 'STORE001', '123 Main Street, Business District', 'Mumbai', 1, 'Maharashtra', 1, 'India', 1, 'Andheri Branch', 'High priority installation', 'PO2024001', '2024-01-15', 'Asian paints', NULL, 'HDFC Bank', 2, NULL, NULL, 1, 'FastTrack Installation', 1, 0, 0, '2025-11-02 19:02:32', NULL, '2025-11-02 06:39:53', 'bulk_upload', '2025-11-03 00:32:32', NULL),
(5, 'SITE002', 'STORE002', '456 Commercial Road, IT Park', 'Bangalore', 4, 'Karnataka', 2, 'India', 1, 'Whitefield Branch', 'Standard installation', 'PO2024002', '2024-01-20', 'Bajaj Finance', NULL, 'ICICI Bank', 3, NULL, NULL, 1, 'TechInstall Solutions', 0, 0, 0, NULL, NULL, '2025-11-02 06:39:53', 'bulk_upload', '2025-11-02 07:31:35', NULL),
(6, 'SITE09010', 'STORE00111', '123 Main Street, Business District', 'Mumbai', 1, 'Maharashtra', 1, 'India', 1, 'Andheri Branch', 'High priority installation', 'PO20240011', '2024-01-15', 'ITC Limited', NULL, 'HDFC Bank', 2, NULL, NULL, 1, 'Elite Services', 0, 0, 0, NULL, NULL, '2025-11-03 03:55:25', 'bulk_upload', '2025-11-03 04:13:27', NULL),
(7, 'SITE0029027', 'STORE00222', '456 Commercial Road, IT Park', 'Bangalore', 4, 'Karnataka', 2, 'India', 1, 'Whitefield Branch', 'Standard installation', 'PO20240022', '2024-01-20', 'Maruti Suzuki India', NULL, 'ICICI Bank', 3, NULL, NULL, 1, 'ProInstall Corp', 1, 0, 0, '2025-11-02 22:47:23', NULL, '2025-11-03 03:55:25', 'bulk_upload', '2025-11-03 04:17:23', NULL),
(8, 'RX34', 'RX34', 'Reliance Digital, Reliance Retail Limited. The Hive Kandivali the Next To Raghu Leela Mall,.. Reliance Retail Limited, Situated at Icon Next To Raghu Leela Mall, Boisar Gymkhana Road Kandivali West', NULL, 541, NULL, 25, NULL, 1, 'Kandivali', '', 'NA', '2025-12-31', NULL, 17, NULL, 12, '', '', 1, 'QuickFix Services', 0, 0, 0, NULL, NULL, '2025-11-03 14:58:02', 'admin', '2025-11-03 14:58:23', NULL),
(9, 'SITE0001', NULL, 'Delhi Store 1', 'Delhi', NULL, 'Delhi', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'ICICI Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-12 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(10, 'SITE0002', NULL, 'Hyderabad Store 2', 'Hyderabad', NULL, 'Telangana', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'ICICI Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-07 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(11, 'SITE0003', NULL, 'Bangalore Store 3', 'Bangalore', NULL, 'Karnataka', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'SBI', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-17 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(12, 'SITE0004', NULL, 'Delhi Store 4', 'Delhi', NULL, 'Delhi', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'ITC Limited', NULL, 'Kotak Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-06 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(13, 'SITE0005', NULL, 'Pune Store 5', 'Pune', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'SBI', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-21 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(14, 'SITE0006', NULL, 'Hyderabad Store 6', 'Hyderabad', NULL, 'Telangana', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'HDFC Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-27 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(15, 'SITE0007', NULL, 'Ahmedabad Store 7', 'Ahmedabad', NULL, 'Gujarat', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'ICICI Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-11 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(16, 'SITE0008', NULL, 'Kolkata Store 8', 'Kolkata', NULL, 'West Bengal', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'Kotak Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-07 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(17, 'SITE0009', NULL, 'Mumbai Store 9', 'Mumbai', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'SBI', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-21 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(18, 'SITE0010', NULL, 'Chennai Store 10', 'Chennai', NULL, 'Tamil Nadu', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Tata Group', NULL, 'HDFC Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-06 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(19, 'SITE0011', NULL, 'Mumbai Store 11', 'Mumbai', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'ITC Limited', NULL, 'ICICI Bank', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-01 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(20, 'SITE0012', NULL, 'Bangalore Store 12', 'Bangalore', NULL, 'Karnataka', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'ITC Limited', NULL, 'ICICI Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-23 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(21, 'SITE0013', NULL, 'Bangalore Store 13', 'Bangalore', NULL, 'Karnataka', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Tata Group', NULL, 'SBI', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-24 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(22, 'SITE0014', NULL, 'Kolkata Store 14', 'Kolkata', NULL, 'West Bengal', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'SBI', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-18 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(23, 'SITE0015', NULL, 'Ahmedabad Store 15', 'Ahmedabad', NULL, 'Gujarat', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Future Group', NULL, 'HDFC Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-17 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(24, 'SITE0016', NULL, 'Hyderabad Store 16', 'Hyderabad', NULL, 'Telangana', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Tata Group', NULL, 'ICICI Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-06 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(25, 'SITE0017', NULL, 'Ahmedabad Store 17', 'Ahmedabad', NULL, 'Gujarat', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'ITC Limited', NULL, 'Kotak Bank', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-20 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(26, 'SITE0018', NULL, 'Kolkata Store 18', 'Kolkata', NULL, 'West Bengal', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Future Group', NULL, 'ICICI Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-23 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(27, 'SITE0019', NULL, 'Delhi Store 19', 'Delhi', NULL, 'Delhi', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Future Group', NULL, 'SBI', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-11 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(28, 'SITE0020', NULL, 'Ahmedabad Store 20', 'Ahmedabad', NULL, 'Gujarat', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'HDFC Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-13 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(29, 'SITE0021', NULL, 'Pune Store 21', 'Pune', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'Kotak Bank', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-16 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(30, 'SITE0022', NULL, 'Chennai Store 22', 'Chennai', NULL, 'Tamil Nadu', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Tata Group', NULL, 'Axis Bank', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-17 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(31, 'SITE0023', NULL, 'Delhi Store 23', 'Delhi', NULL, 'Delhi', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Tata Group', NULL, 'Axis Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-12 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(32, 'SITE0024', NULL, 'Ahmedabad Store 24', 'Ahmedabad', NULL, 'Gujarat', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'HDFC Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-13 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(33, 'SITE0025', NULL, 'Kolkata Store 25', 'Kolkata', NULL, 'West Bengal', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'ITC Limited', NULL, 'Axis Bank', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-20 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(34, 'SITE0026', NULL, 'Pune Store 26', 'Pune', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'ITC Limited', NULL, 'HDFC Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-20 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(35, 'SITE0027', NULL, 'Kolkata Store 27', 'Kolkata', NULL, 'West Bengal', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'Axis Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-18 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(36, 'SITE0028', NULL, 'Kolkata Store 28', 'Kolkata', NULL, 'West Bengal', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'ICICI Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-17 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(37, 'SITE0029', NULL, 'Pune Store 29', 'Pune', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Tata Group', NULL, 'Kotak Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-20 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(38, 'SITE0030', NULL, 'Pune Store 30', 'Pune', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'ITC Limited', NULL, 'Axis Bank', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-28 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(39, 'SITE0031', NULL, 'Mumbai Store 31', 'Mumbai', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Tata Group', NULL, 'ICICI Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-12 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(40, 'SITE0032', NULL, 'Mumbai Store 32', 'Mumbai', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'Axis Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-18 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(41, 'SITE0033', NULL, 'Pune Store 33', 'Pune', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'ICICI Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-11 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(42, 'SITE0034', NULL, 'Delhi Store 34', 'Delhi', NULL, 'Delhi', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'Kotak Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-07 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(43, 'SITE0035', NULL, 'Pune Store 35', 'Pune', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'SBI', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-02 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(44, 'SITE0036', NULL, 'Kolkata Store 36', 'Kolkata', NULL, 'West Bengal', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'Kotak Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-23 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(45, 'SITE0037', NULL, 'Delhi Store 37', 'Delhi', NULL, 'Delhi', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'SBI', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-18 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(46, 'SITE0038', NULL, 'Chennai Store 38', 'Chennai', NULL, 'Tamil Nadu', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Tata Group', NULL, 'Kotak Bank', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-19 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(47, 'SITE0039', NULL, 'Kolkata Store 39', 'Kolkata', NULL, 'West Bengal', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Aditya Birla', NULL, 'HDFC Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-13 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(48, 'SITE0040', NULL, 'Hyderabad Store 40', 'Hyderabad', NULL, 'Telangana', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'SBI', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-08-31 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(49, 'SITE0041', NULL, 'Kolkata Store 41', 'Kolkata', NULL, 'West Bengal', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'ICICI Bank', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-12 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(50, 'SITE0042', NULL, 'Mumbai Store 42', 'Mumbai', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'Kotak Bank', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-02 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(51, 'SITE0043', NULL, 'Ahmedabad Store 43', 'Ahmedabad', NULL, 'Gujarat', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Future Group', NULL, 'SBI', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-30 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(52, 'SITE0044', NULL, 'Pune Store 44', 'Pune', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'SBI', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-04 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(53, 'SITE0045', NULL, 'Kolkata Store 45', 'Kolkata', NULL, 'West Bengal', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'ITC Limited', NULL, 'Axis Bank', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-14 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(54, 'SITE0046', NULL, 'Pune Store 46', 'Pune', NULL, 'Maharashtra', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'ITC Limited', NULL, 'Kotak Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-09 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(55, 'SITE0047', NULL, 'Bangalore Store 47', 'Bangalore', NULL, 'Karnataka', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'Axis Bank', NULL, NULL, 'completed', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-11 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(56, 'SITE0048', NULL, 'Delhi Store 48', 'Delhi', NULL, 'Delhi', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Tata Group', NULL, 'ICICI Bank', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-20 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(57, 'SITE0049', NULL, 'Bangalore Store 49', 'Bangalore', NULL, 'Karnataka', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Tata Group', NULL, 'SBI', NULL, NULL, 'pending', 0, NULL, 0, 0, 0, NULL, NULL, '2025-10-15 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(58, 'SITE0050', NULL, 'Chennai Store 50', 'Chennai', NULL, 'Tamil Nadu', NULL, 'India', NULL, NULL, NULL, NULL, NULL, 'Reliance Retail', NULL, 'SBI', NULL, NULL, 'active', 0, NULL, 0, 0, 0, NULL, NULL, '2025-09-17 10:23:04', NULL, '2025-11-04 15:53:04', NULL),
(59, 'A', 'A', 'A', 'Mumbai', 541, 'Maharashtra', 25, 'India', 1, 'Andheri Branch', 'High priority installation', 'PO2024001', '2024-01-15', 'B', 18, 'Axis Bank', 4, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 20:14:36', 'bulk_upload', '2025-11-04 20:14:36', NULL),
(60, 'B', 'B', 'B', 'Bangalore', 595, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'Standard installation', 'PO2024002', '2024-01-20', 'A', 17, 'Axis Bank', 4, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 20:14:36', 'bulk_upload', '2025-11-04 20:14:36', NULL),
(61, 'C', 'C', 'C', 'Bangalore', 595, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'A', 'PO2024003', '2024-01-21', 'A', 17, 'Axis Bank', 4, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 20:14:36', 'bulk_upload', '2025-11-04 20:14:36', NULL),
(62, 'D', 'D', 'D', 'Bangalore', 595, 'Karnataka', 12, 'India', 1, 'Andheri Branch', 'B', 'PO2024004', '2024-01-22', 'A', 17, 'Axis Bank', 4, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 20:14:36', 'bulk_upload', '2025-11-04 20:14:36', NULL),
(63, 'E', 'E', 'E', 'Bangalore', 595, 'Karnataka', 12, 'India', 1, 'Whitefield Branch', 'C', 'PO2024005', '2024-01-23', 'A', 17, 'Axis Bank', 4, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 20:14:36', 'bulk_upload', '2025-11-04 20:14:36', NULL),
(64, 'F', 'F', 'F', 'Bangalore', 595, 'Karnataka', 12, 'India', 1, 'Andheri Branch', 'D', 'PO2024006', '2024-01-24', 'A', 17, 'Axis Bank', 4, NULL, NULL, 0, NULL, 0, 0, 0, NULL, NULL, '2025-11-04 20:14:36', 'bulk_upload', '2025-11-04 20:14:36', NULL);

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
(1, 4, 1, 1, '2025-11-02 01:50:33', 'cancelled', 'some', '2025-11-02 01:50:33', '2025-11-02 01:55:07'),
(2, 4, 2, 1, '2025-11-02 01:55:23', 'completed', 'som', '2025-11-02 01:55:23', '2025-11-02 01:58:52'),
(3, 4, 4, 1, '2025-11-02 01:59:14', 'cancelled', 'op', '2025-11-02 01:59:14', '2025-11-02 19:01:30'),
(4, 5, 1, 1, '2025-11-02 02:01:35', 'active', 'techInstall ', '2025-11-02 02:01:35', '2025-11-02 02:01:35'),
(5, 4, 4, 1, '2025-11-02 19:02:03', 'active', 'do site survey again', '2025-11-02 19:02:03', '2025-11-02 19:02:03'),
(6, 6, 5, 1, '2025-11-02 22:41:21', 'completed', 'something', '2025-11-02 22:41:21', '2025-11-02 22:41:36'),
(7, 6, 5, 1, '2025-11-02 22:43:27', 'active', 'some2', '2025-11-02 22:43:27', '2025-11-02 22:43:27'),
(8, 7, 3, 1, '2025-11-02 22:44:06', 'active', '', '2025-11-02 22:44:06', '2025-11-02 22:44:06'),
(9, 8, 2, 1, '2025-11-03 09:28:23', 'active', 'someting', '2025-11-03 09:28:23', '2025-11-03 09:28:23');

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

--
-- Dumping data for table `site_surveys`
--

INSERT INTO `site_surveys` (`id`, `site_id`, `vendor_id`, `delegation_id`, `survey_data`, `survey_date`, `submitted_date`, `survey_status`, `installation_status`, `installation_id`, `created_at`, `updated_at`, `approved_by`, `approved_date`, `approval_remarks`, `checkin_datetime`, `checkout_datetime`, `working_hours`, `store_model`, `floor_height`, `floor_height_photos`, `ceiling_type`, `ceiling_photos`, `total_cameras`, `analytic_cameras`, `analytic_photos`, `existing_poe_rack`, `existing_poe_photos`, `space_new_rack`, `space_new_rack_photos`, `new_poe_rack`, `new_poe_photos`, `zones_recommended`, `rrl_delivery_status`, `rrl_photos`, `kptl_space`, `kptl_photos`, `site_accessibility`, `power_availability`, `network_connectivity`, `space_adequacy`, `technical_remarks`, `challenges_identified`, `recommendations`, `estimated_completion_days`) VALUES
(2, 5, 1, 4, NULL, NULL, '2025-11-02 10:49:01', 'approved', 'not_delegated', NULL, '2025-11-02 10:49:01', '2025-11-03 06:50:14', 1, '2025-11-02 16:58:18', 'some approve remark', '2025-11-02 16:18:00', '2025-11-02 22:18:00', '6 hours', 'Standard Retail Store Model A', 12.50, '[\"assets\\/uploads\\/surveys\\/6907371dd38fa_1762080541.png\"]', 'False', '[\"assets\\/uploads\\/surveys\\/6907371dd3b84_1762080541.png\"]', 8, 4, '[\"assets\\/uploads\\/surveys\\/6907371dd3dee_1762080541.png\"]', 1, '[\"assets\\/uploads\\/surveys\\/6907371dd40ac_1762080541.png\"]', 'Yes', '[\"assets\\/uploads\\/surveys\\/6907371dd4323_1762080541.png\"]', 2, '[\"assets\\/uploads\\/surveys\\/6907371dd45ab_1762080541.png\"]', 3, 'Yes', '[\"assets\\/uploads\\/surveys\\/6907371dd489c_1762080541.png\"]', 'Yes', '[\"assets\\/uploads\\/surveys\\/6907371dd4b07_1762080541.png\"]', 'good', 'available', 'excellent', 'adequate', 'Site has excellent infrastructure with adequate power supply and network connectivity. Floor height is suitable for camera installation. Existing POE rack can accommodate additional equipment. The location provides good visibility for security cameras with minimal obstructions.', 'Minor cable management improvements needed in server room. Some areas may require additional lighting for optimal camera performance. Network switch may need upgrade to handle increased bandwidth from additional cameras.', 'Recommend installing 2 additional POE racks for optimal coverage. Suggest upgrading network switch to handle increased bandwidth. Consider installing backup power supply for critical equipment. Implement proper cable management system in server room.', 5),
(3, 4, 4, 3, NULL, NULL, '2025-11-02 18:28:56', 'rejected', 'not_delegated', NULL, '2025-11-02 18:28:56', '2025-11-02 18:30:03', 1, '2025-11-03 00:00:03', 'something demo reject', '2025-11-02 23:57:00', '2025-11-03 05:57:00', '6 hours', 'Standard Retail Store Model A', 12.50, '[\"assets\\/uploads\\/surveys\\/6907a2e8b45c7_1762108136.png\"]', 'False', '[\"assets\\/uploads\\/surveys\\/6907a2e8b4b0d_1762108136.png\"]', 8, 4, '[\"assets\\/uploads\\/surveys\\/6907a2e8b50e7_1762108136.png\"]', 1, '[\"assets\\/uploads\\/surveys\\/6907a2e8b5ae2_1762108136.png\"]', 'Yes', '[\"assets\\/uploads\\/surveys\\/6907a2e8b607f_1762108136.png\"]', 2, '[\"assets\\/uploads\\/surveys\\/6907a2e8b65ff_1762108136.png\"]', 3, 'Yes', '[\"assets\\/uploads\\/surveys\\/6907a2e8b6bc9_1762108136.png\"]', 'Yes', '[\"assets\\/uploads\\/surveys\\/6907a2e8b711e_1762108136.png\"]', 'good', 'available', 'excellent', 'adequate', 'Site has excellent infrastructure with adequate power supply and network connectivity. Floor height is suitable for camera installation. Existing POE rack can accommodate additional equipment. The location provides good visibility for security cameras with minimal obstructions.', 'Minor cable management improvements needed in server room. Some areas may require additional lighting for optimal camera performance. Network switch may need upgrade to handle increased bandwidth from additional cameras.', 'Recommend installing 2 additional POE racks for optimal coverage. Suggest upgrading network switch to handle increased bandwidth. Consider installing backup power supply for critical equipment. Implement proper cable management system in server room.', 5),
(4, 4, 4, 5, NULL, NULL, '2025-11-02 19:02:32', 'approved', 'delegated', 2, '2025-11-02 19:02:32', '2025-11-03 06:52:16', 1, '2025-11-03 00:41:58', 'approving', '2025-11-03 00:32:00', '2025-11-03 06:32:00', '6 hours', 'Standard Retail Store Model A', 12.50, '[\"assets\\/uploads\\/surveys\\/6907aac835203_1762110152.png\"]', 'False', '[\"assets\\/uploads\\/surveys\\/6907aac8361b4_1762110152.png\"]', 8, 4, '[\"assets\\/uploads\\/surveys\\/6907aac8366c9_1762110152.png\"]', 1, '[\"assets\\/uploads\\/surveys\\/6907aac836bad_1762110152.png\"]', 'Yes', '[\"assets\\/uploads\\/surveys\\/6907aac8372e0_1762110152.png\"]', 2, '[\"assets\\/uploads\\/surveys\\/6907aac83873d_1762110152.png\"]', 3, 'Yes', '[\"assets\\/uploads\\/surveys\\/6907aac838d7d_1762110152.png\"]', 'Yes', '[\"assets\\/uploads\\/surveys\\/6907aac8393b7_1762110152.png\"]', 'good', 'available', 'excellent', 'adequate', 'Site has excellent infrastructure with adequate power supply and network connectivity. Floor height is suitable for camera installation. Existing POE rack can accommodate additional equipment. The location provides good visibility for security cameras with minimal obstructions.', 'Minor cable management improvements needed in server room. Some areas may require additional lighting for optimal camera performance. Network switch may need upgrade to handle increased bandwidth from additional cameras.', 'Recommend installing 2 additional POE racks for optimal coverage. Suggest upgrading network switch to handle increased bandwidth. Consider installing backup power supply for critical equipment. Implement proper cable management system in server room.', 5),
(5, 7, 3, 8, NULL, NULL, '2025-11-02 22:47:23', 'approved', 'not_delegated', NULL, '2025-11-02 22:47:23', '2025-11-02 22:48:03', 1, '2025-11-03 04:18:03', 'approving', '2025-11-03 04:17:00', '2025-11-03 10:17:00', '6 hours', 'Standard Retail Store Model A', 12.50, '[\"assets\\/uploads\\/surveys\\/6907df7b8d684_1762123643.png\"]', 'False', '[\"assets\\/uploads\\/surveys\\/6907df7b8dc23_1762123643.png\"]', 8, 4, '[\"assets\\/uploads\\/surveys\\/6907df7b8e196_1762123643.png\"]', 1, '[\"assets\\/uploads\\/surveys\\/6907df7b8e71d_1762123643.png\"]', 'Yes', '[\"assets\\/uploads\\/surveys\\/6907df7b8eca7_1762123643.png\"]', 2, '[\"assets\\/uploads\\/surveys\\/6907df7b8f293_1762123643.png\"]', 3, 'Yes', '[\"assets\\/uploads\\/surveys\\/6907df7b8f832_1762123643.png\"]', 'Yes', '[\"assets\\/uploads\\/surveys\\/6907df7b8fdef_1762123643.png\"]', 'good', 'available', 'excellent', 'adequate', 'Site has excellent infrastructure with adequate power supply and network connectivity. Floor height is suitable for camera installation. Existing POE rack can accommodate additional equipment. The location provides good visibility for security cameras with minimal obstructions.', 'Minor cable management improvements needed in server room. Some areas may require additional lighting for optimal camera performance. Network switch may need upgrade to handle increased bandwidth from additional cameras.', 'Recommend installing 2 additional POE racks for optimal coverage. Suggest upgrading network switch to handle increased bandwidth. Consider installing backup power supply for critical equipment. Implement proper cable management system in server room.', 5),
(6, 9, 5, NULL, NULL, NULL, '2025-10-24 10:23:04', 'rejected', 'not_delegated', NULL, '2025-10-12 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 4', 8.00, NULL, '', NULL, 44, 10, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(7, 10, 1, NULL, NULL, NULL, '2025-09-18 10:23:04', 'rejected', 'not_delegated', NULL, '2025-10-15 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 5', 12.00, NULL, '', NULL, 39, 19, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(8, 11, 5, NULL, NULL, NULL, '2025-10-05 10:23:04', 'rejected', 'not_delegated', NULL, '2025-10-30 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 4', 11.00, NULL, '', NULL, 42, 13, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(9, 13, 4, NULL, NULL, NULL, '2025-11-01 10:23:04', 'pending', 'not_delegated', NULL, '2025-10-24 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 1', 15.00, NULL, '', NULL, 44, 19, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(10, 16, 4, NULL, NULL, NULL, '2025-09-18 10:23:04', 'rejected', 'not_delegated', NULL, '2025-10-30 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 2', 14.00, NULL, '', NULL, 33, 12, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(11, 17, 4, NULL, NULL, NULL, '2025-09-06 10:23:04', 'rejected', 'not_delegated', NULL, '2025-09-30 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 5', 11.00, NULL, '', NULL, 40, 8, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(12, 18, 4, NULL, NULL, NULL, '2025-10-30 10:23:04', 'pending', 'not_delegated', NULL, '2025-10-07 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 4', 12.00, NULL, '', NULL, 50, 7, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(13, 19, 5, NULL, NULL, NULL, '2025-11-03 10:23:04', 'rejected', 'not_delegated', NULL, '2025-10-24 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 5', 13.00, NULL, '', NULL, 25, 10, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(14, 20, 2, NULL, NULL, NULL, '2025-10-24 10:23:04', 'rejected', 'not_delegated', NULL, '2025-10-20 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 2', 11.00, NULL, '', NULL, 28, 19, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(15, 23, 5, NULL, NULL, NULL, '2025-09-21 10:23:04', 'pending', 'not_delegated', NULL, '2025-09-26 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 2', 15.00, NULL, '', NULL, 34, 15, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(16, 24, 5, NULL, NULL, NULL, '2025-11-02 10:23:04', 'pending', 'not_delegated', NULL, '2025-09-29 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 2', 15.00, NULL, '', NULL, 13, 11, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(17, 26, 6, NULL, NULL, NULL, '2025-10-30 10:23:04', 'approved', 'not_delegated', NULL, '2025-10-19 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 2', 15.00, NULL, '', NULL, 24, 9, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(18, 27, 4, NULL, NULL, NULL, '2025-09-14 10:23:04', 'pending', 'not_delegated', NULL, '2025-10-09 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 3', 9.00, NULL, '', NULL, 25, 9, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(19, 28, 5, NULL, NULL, NULL, '2025-10-01 10:23:04', 'approved', 'not_delegated', NULL, '2025-09-11 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 5', 13.00, NULL, '', NULL, 48, 10, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(20, 29, 6, NULL, NULL, NULL, '2025-09-11 10:23:04', 'rejected', 'not_delegated', NULL, '2025-10-21 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 3', 14.00, NULL, '', NULL, 25, 13, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(21, 30, 2, NULL, NULL, NULL, '2025-10-10 10:23:04', 'rejected', 'not_delegated', NULL, '2025-10-27 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 3', 15.00, NULL, '', NULL, 34, 19, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(22, 31, 6, NULL, NULL, NULL, '2025-09-27 10:23:04', 'rejected', 'not_delegated', NULL, '2025-09-27 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 4', 12.00, NULL, '', NULL, 35, 8, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(23, 34, 4, NULL, NULL, NULL, '2025-09-11 10:23:04', 'approved', 'not_delegated', NULL, '2025-09-07 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 2', 11.00, NULL, '', NULL, 18, 12, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(24, 35, 5, NULL, NULL, NULL, '2025-09-20 10:23:04', 'approved', 'not_delegated', NULL, '2025-09-11 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 4', 8.00, NULL, '', NULL, 44, 11, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(25, 36, 6, NULL, NULL, NULL, '2025-09-11 10:23:04', 'pending', 'not_delegated', NULL, '2025-10-14 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 1', 8.00, NULL, '', NULL, 24, 11, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(26, 37, 6, NULL, NULL, NULL, '2025-09-19 10:23:04', 'rejected', 'not_delegated', NULL, '2025-09-26 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 4', 11.00, NULL, '', NULL, 16, 13, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL),
(27, 38, 1, NULL, NULL, NULL, '2025-09-30 10:23:04', 'approved', 'not_delegated', NULL, '2025-10-08 04:53:04', '2025-11-04 10:23:04', NULL, NULL, NULL, NULL, NULL, NULL, 'Model 4', 8.00, NULL, '', NULL, 24, 16, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Survey completed for site. All requirements noted.', NULL, NULL, NULL);

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
(1, 'admin', 'admin@example.com', '+1234567890', '$2y$12$qIltu8UH/9JQTVv2XeP9deprEPyd8vWdMs7QttIUNFcMoPtoIvfUS', 'admin123', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxNzYyMjcxMDU4LCJleHAiOjE3NjIzNTc0NTh9.pgR3gkdY14xTynjxpPPWogAYEUCCsnvDu4NcjMCMnWY', 'admin', NULL, 'active', '2025-11-01 14:33:15', '2025-11-04 15:44:18'),
(2, 'vikas', 'vikas@gmail.com', NULL, '$2y$12$qIltu8UH/9JQTVv2XeP9deprEPyd8vWdMs7QttIUNFcMoPtoIvfUS', NULL, NULL, 'vendor', NULL, 'active', '2025-11-01 15:05:45', '2025-11-01 15:08:51'),
(3, 'bobby bagga', 'bobby@email.com', '8736263545', '$2y$12$o.yCqH9FtTFvpL/rRFg7nu2kVio.Sv0zWR.S4izg8vBXTqQF/8GbW', 'something@123', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjozLCJ1c2VybmFtZSI6ImJvYmJ5IGJhZ2dhIiwicm9sZSI6InZlbmRvciIsImlhdCI6MTc2MjAyMzY0NiwiZXhwIjoxNzYyMTEwMDQ2fQ.WRsZuOCJTv7F5vHfqZOrZJHHcFwh2iNb-_0Fssm8dwY', 'vendor', NULL, 'active', '2025-11-01 15:19:56', '2025-11-01 19:00:46'),
(4, 'techinstall_solutions', 'contact@techinstall.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'password', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo0LCJ1c2VybmFtZSI6InRlY2hpbnN0YWxsX3NvbHV0aW9ucyIsInJvbGUiOiJ2ZW5kb3IiLCJpYXQiOjE3NjIyNDA3MjcsImV4cCI6MTc2MjMyNzEyN30.2rf-wMh8wWwgacVZ4SDQmPlgvu_Vy9z3Fw4gCYY60fU', 'vendor', 1, 'active', '2025-11-02 01:40:29', '2025-11-04 07:18:47'),
(5, 'quickfix_services', 'info@quickfix.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'password', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo1LCJ1c2VybmFtZSI6InF1aWNrZml4X3NlcnZpY2VzIiwicm9sZSI6InZlbmRvciIsImlhdCI6MTc2MjE2MjAxMiwiZXhwIjoxNzYyMjQ4NDEyfQ.4CEMhspbjG9WBeyL62A2BKHgiPhJ-ZPEdpGe9MMhmnM', 'vendor', 2, 'active', '2025-11-02 01:40:29', '2025-11-03 09:26:52'),
(6, 'proinstall_corp', 'support@proinstall.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'password', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo2LCJ1c2VybmFtZSI6InByb2luc3RhbGxfY29ycCIsInJvbGUiOiJ2ZW5kb3IiLCJpYXQiOjE3NjIxMjM2MTYsImV4cCI6MTc2MjIxMDAxNn0.wh6P_Ra5-cRNQW7GcpZI9bOIL9ZHSnkap-e-9OjZr1E', 'vendor', 3, 'active', '2025-11-02 01:40:29', '2025-11-02 22:46:56'),
(7, 'fasttrack_installation', 'hello@fasttrack.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'password', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo3LCJ1c2VybmFtZSI6ImZhc3R0cmFja19pbnN0YWxsYXRpb24iLCJyb2xlIjoidmVuZG9yIiwiaWF0IjoxNzYyMTExNjgwLCJleHAiOjE3NjIxOTgwODB9.NS7p1FlvvqPKHTwgdJjspSulwCXnK5uXe7T3iDxVUZE', 'vendor', 4, 'active', '2025-11-02 01:40:29', '2025-11-02 19:28:00'),
(8, 'elite_services', 'contact@eliteservices.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'password', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjo4LCJ1c2VybmFtZSI6ImVsaXRlX3NlcnZpY2VzIiwicm9sZSI6InZlbmRvciIsImlhdCI6MTc2MjEyMzMzNSwiZXhwIjoxNzYyMjA5NzM1fQ.oUDkv4EDgdzE3KAydVjDRmd3ZwPlpoYno4B6DePYx7U', 'vendor', 5, 'active', '2025-11-02 01:40:29', '2025-11-02 22:42:15'),
(12, 'Ganesh Panchal', 'ganesh@gmail.com', '8475847832', '$2y$12$T2Y9ySTxbY2.UC9aFAnD3O7.k8OpWCw2U37Cq0UGKeaaO5NifdSmu', 'ganesh@123', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxMiwidXNlcm5hbWUiOiJHYW5lc2ggUGFuY2hhbCIsInJvbGUiOiJ2ZW5kb3IiLCJpYXQiOjE3NjIxODE5MDIsImV4cCI6MTc2MjI2ODMwMn0.VUeInf-FuhdzudUqrNaq5M7iVDBs5nYqByXAmfCtE2U', 'vendor', 5, 'active', '2025-11-03 14:55:11', '2025-11-03 14:58:22'),
(13, 'admin_test', 'admin@test.com', NULL, '$2y$12$q16Wvfn0nHZqI6jcvgK5tOQ8imAa5yrszYrTEoriRNWBKgmgUh9b6', NULL, NULL, 'admin', NULL, 'active', '2025-11-04 10:21:46', '2025-11-04 10:21:46'),
(14, 'vendor_test1', 'vendor1@test.com', NULL, '$2y$12$GGVMwwJ/IfeM9AearmSBtexW1v.blKenTWpSdB1VbRNyu59iBqXhK', NULL, NULL, 'vendor', NULL, 'active', '2025-11-04 10:21:46', '2025-11-04 10:21:46'),
(15, 'vendor_test2', 'vendor2@test.com', NULL, '$2y$12$4UUayn//HgB.VtwQ6pXxP.dcdE2Sl24zTveqX7w4iHspJSmeFRkUC', NULL, NULL, 'vendor', NULL, 'active', '2025-11-04 10:21:46', '2025-11-04 10:21:46'),
(16, 'vendor_test3', 'vendor3@test.com', NULL, '$2y$12$awjrjX0UfGJmjc.3Nt3HBuKxma7OSdyWqMPOx8oN08qRT/YNZIXCq', NULL, NULL, 'vendor', NULL, 'active', '2025-11-04 10:21:46', '2025-11-04 10:21:46');

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
(63, 1, 1, 1, '2025-11-04 08:57:50'),
(64, 1, 2, 1, '2025-11-04 08:57:50'),
(65, 1, 10, 1, '2025-11-04 08:57:50'),
(66, 1, 20, 1, '2025-11-04 08:57:50'),
(67, 1, 30, 1, '2025-11-04 08:57:50'),
(68, 1, 11, 1, '2025-11-04 08:57:50'),
(69, 1, 12, 1, '2025-11-04 08:57:50'),
(70, 1, 13, 1, '2025-11-04 08:57:50'),
(71, 1, 14, 1, '2025-11-04 08:57:50'),
(72, 1, 21, 1, '2025-11-04 08:57:50'),
(73, 1, 22, 1, '2025-11-04 08:57:50'),
(74, 1, 23, 1, '2025-11-04 08:57:50'),
(75, 1, 24, 1, '2025-11-04 08:57:50'),
(76, 1, 31, 1, '2025-11-04 08:57:50'),
(77, 1, 32, 1, '2025-11-04 08:57:50'),
(78, 1, 33, 1, '2025-11-04 08:57:50');

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
(1, 'VND0001', NULL, NULL, 'TechInstall Solutions', NULL, 'contact@techinstall.com', '+91-9876543210', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'No', NULL, NULL, 'Rajesh Kumar', 'active', '2025-11-02 01:23:34', '2025-11-03 06:02:42'),
(2, 'VND0002', NULL, NULL, 'QuickFix Services', NULL, 'info@quickfix.com', '+91-9876543211', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'No', NULL, NULL, 'Priya Sharma', 'active', '2025-11-02 01:23:34', '2025-11-03 06:02:42'),
(3, 'VND0003', '', '$2y$12$SV1Rc7yA5ecA.OUB4RcAt.nt1z868SYB/PRK44X44wwiBYt1dpls2', 'ProInstall Corp', 'ProInstall Corp', 'support@proinstall.com', '+91-9876543212', 'Mumbai', '', '', '', '', '', '', '', '', '', 'No', 'uploads/vendors/3/experience_letter_1762149823.jpg', 'uploads/vendors/3/photograph_1762149823.jpg', 'ProInstall Corp', 'active', '2025-11-02 01:23:34', '2025-11-03 06:03:44'),
(4, 'VND0004', NULL, NULL, 'FastTrack Installation', NULL, 'hello@fasttrack.com', '+91-9876543213', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'No', NULL, NULL, 'Neha Gupta', 'active', '2025-11-02 01:23:34', '2025-11-03 06:02:42'),
(5, 'VND0005', NULL, NULL, 'Elite Services', NULL, 'contact@eliteservices.com', '+91-9876543214', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'No', NULL, NULL, 'Vikram Patel', 'active', '2025-11-02 01:23:34', '2025-11-03 06:02:42'),
(6, NULL, NULL, NULL, 'QuickFix Systems', NULL, 'info@quickfix.com', '9876543211', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'No', NULL, NULL, 'Jane Doe', 'active', '2025-11-04 10:21:46', '2025-11-04 10:21:46'),
(7, NULL, NULL, NULL, 'EliteSetup Ltd', NULL, 'hello@elitesetup.com', '9876543213', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'No', NULL, NULL, 'Sarah Wilson', 'active', '2025-11-04 10:21:46', '2025-11-04 10:21:46'),
(8, NULL, NULL, NULL, 'FastTrack Installations', NULL, 'team@fasttrack.com', '9876543214', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'No', NULL, NULL, 'David Brown', 'active', '2025-11-04 10:21:46', '2025-11-04 10:21:46');

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

-- --------------------------------------------------------

--
-- Structure for view `inventory_stock_summary`
--
DROP TABLE IF EXISTS `inventory_stock_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`reporting`@`%` SQL SECURITY DEFINER VIEW `inventory_stock_summary`  AS SELECT `inventory_stock`.`boq_item_id` AS `boq_item_id`, sum(`inventory_stock`.`current_stock`) AS `total_stock`, sum(`inventory_stock`.`reserved_stock`) AS `total_reserved`, sum(`inventory_stock`.`available_stock`) AS `total_available`, min(`inventory_stock`.`minimum_stock`) AS `minimum_stock`, max(`inventory_stock`.`maximum_stock`) AS `maximum_stock`, avg(`inventory_stock`.`unit_cost`) AS `avg_unit_cost`, sum(`inventory_stock`.`total_value`) AS `total_value`, count(0) AS `entry_count`, max(`inventory_stock`.`last_updated`) AS `last_updated` FROM `inventory_stock` GROUP BY `inventory_stock`.`boq_item_id` ;

-- --------------------------------------------------------

--
-- Structure for view `inventory_summary`
--
DROP TABLE IF EXISTS `inventory_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`reporting`@`%` SQL SECURITY DEFINER VIEW `inventory_summary`  AS SELECT `bi`.`id` AS `boq_item_id`, `bi`.`item_name` AS `item_name`, `bi`.`item_code` AS `item_code`, `bi`.`unit` AS `unit`, `bi`.`category` AS `category`, `bi`.`icon_class` AS `icon_class`, count(case when `ist`.`item_status` = 'available' then 1 end) AS `available_stock`, count(case when `ist`.`item_status` = 'dispatched' then 1 end) AS `dispatched_stock`, count(case when `ist`.`item_status` = 'delivered' then 1 end) AS `delivered_stock`, count(case when `ist`.`item_status` = 'returned' then 1 end) AS `returned_stock`, count(case when `ist`.`item_status` = 'damaged' then 1 end) AS `damaged_stock`, count(0) AS `total_stock`, avg(`ist`.`unit_cost`) AS `avg_unit_cost`, sum(case when `ist`.`item_status` = 'available' then `ist`.`unit_cost` else 0 end) AS `available_value`, sum(`ist`.`unit_cost`) AS `total_value`, count(case when `ist`.`location_type` = 'warehouse' then 1 end) AS `warehouse_stock`, count(case when `ist`.`location_type` = 'vendor_site' then 1 end) AS `vendor_site_stock`, count(case when `ist`.`location_type` = 'in_transit' then 1 end) AS `in_transit_stock` FROM (`boq_items` `bi` left join `inventory_stock` `ist` on(`bi`.`id` = `ist`.`boq_item_id`)) WHERE `bi`.`status` = 'active' GROUP BY `bi`.`id`, `bi`.`item_name`, `bi`.`item_code`, `bi`.`unit`, `bi`.`category`, `bi`.`icon_class` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_audit` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_table_record` (`table_name`,`record_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `boq_items`
--
ALTER TABLE `boq_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_code` (`item_code`),
  ADD KEY `idx_item_name` (`item_name`),
  ADD KEY `idx_item_code` (`item_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_city_state` (`name`,`state_id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_state` (`state_id`),
  ADD KEY `idx_country` (`country_id`),
  ADD KEY `idx_zone` (`zone_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_status` (`status`);

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
-- Indexes for table `installation_materials`
--
ALTER TABLE `installation_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_installation_id` (`installation_id`);

--
-- Indexes for table `installation_notifications`
--
ALTER TABLE `installation_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `idx_installation_id` (`installation_id`),
  ADD KEY `idx_recipient` (`recipient_type`,`recipient_id`),
  ADD KEY `idx_is_read` (`is_read`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_inventory_item` (`boq_item_id`),
  ADD KEY `idx_stock_levels` (`current_stock`,`reserved_stock`);

--
-- Indexes for table `inventory_dispatches`
--
ALTER TABLE `inventory_dispatches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dispatch_number` (`dispatch_number`),
  ADD KEY `idx_dispatch_date` (`dispatch_date`),
  ADD KEY `idx_site_vendor` (`site_id`,`vendor_id`),
  ADD KEY `idx_status` (`dispatch_status`),
  ADD KEY `idx_dispatch_status` (`dispatch_status`),
  ADD KEY `idx_delivery_date` (`delivery_date`),
  ADD KEY `idx_confirmed_by` (`confirmed_by`);

--
-- Indexes for table `inventory_dispatch_items`
--
ALTER TABLE `inventory_dispatch_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_stock_dispatch` (`inventory_stock_id`),
  ADD KEY `idx_dispatch` (`dispatch_id`),
  ADD KEY `idx_stock` (`inventory_stock_id`),
  ADD KEY `idx_boq_item` (`boq_item_id`);

--
-- Indexes for table `inventory_inwards`
--
ALTER TABLE `inventory_inwards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `idx_receipt_date` (`receipt_date`),
  ADD KEY `idx_supplier` (`supplier_name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `inventory_inward_items`
--
ALTER TABLE `inventory_inward_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inward_boq` (`inward_id`,`boq_item_id`),
  ADD KEY `idx_batch` (`batch_number`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_movement_type` (`movement_type`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_movement_date` (`movement_date`),
  ADD KEY `idx_boq_date` (`boq_item_id`,`movement_date`);

--
-- Indexes for table `inventory_reconciliation`
--
ALTER TABLE `inventory_reconciliation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reconciliation_number` (`reconciliation_number`),
  ADD KEY `idx_reconciliation_date` (`reconciliation_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `inventory_reconciliation_items`
--
ALTER TABLE `inventory_reconciliation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reconciliation_boq` (`reconciliation_id`,`boq_item_id`),
  ADD KEY `idx_discrepancy` (`difference_quantity`);

--
-- Indexes for table `inventory_stock`
--
ALTER TABLE `inventory_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `idx_boq_item` (`boq_item_id`),
  ADD KEY `idx_serial_number` (`serial_number`),
  ADD KEY `idx_status` (`item_status`),
  ADD KEY `idx_location` (`location_type`,`location_id`),
  ADD KEY `idx_dispatch` (`dispatch_id`);

--
-- Indexes for table `inventory_tracking`
--
ALTER TABLE `inventory_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_location` (`current_location_type`,`current_location_id`),
  ADD KEY `idx_site_vendor` (`site_id`,`vendor_id`),
  ADD KEY `idx_serial` (`serial_number`),
  ADD KEY `idx_batch` (`batch_number`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_location` (`country`,`state`,`city`),
  ADD KEY `idx_country` (`country`),
  ADD KEY `idx_state` (`state`),
  ADD KEY `idx_city` (`city`);

--
-- Indexes for table `material_dispatches`
--
ALTER TABLE `material_dispatches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_dispatch` (`material_request_id`),
  ADD KEY `idx_tracking` (`tracking_number`),
  ADD KEY `idx_dispatch_date` (`dispatch_date`);

--
-- Indexes for table `material_requests`
--
ALTER TABLE `material_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_site_request` (`site_id`),
  ADD KEY `idx_vendor_request` (`vendor_id`),
  ADD KEY `idx_request_status` (`status`),
  ADD KEY `fk_material_requests_processed_by` (`processed_by`),
  ADD KEY `idx_material_requests_survey_id` (`survey_id`),
  ADD KEY `idx_material_requests_request_date` (`request_date`),
  ADD KEY `idx_material_requests_created_date` (`created_date`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `role_menu_permissions`
--
ALTER TABLE `role_menu_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_menu` (`role`,`menu_item_id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_menu` (`menu_item_id`);

--
-- Indexes for table `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_country_id` (`country_id`),
  ADD KEY `idx_state_id` (`state_id`),
  ADD KEY `idx_city_id` (`city_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_bank_id` (`bank_id`);

--
-- Indexes for table `site_delegations`
--
ALTER TABLE `site_delegations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delegated_by` (`delegated_by`),
  ADD KEY `idx_site_id` (`site_id`),
  ADD KEY `idx_vendor_id` (`vendor_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `site_surveys`
--
ALTER TABLE `site_surveys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_site_survey` (`site_id`),
  ADD KEY `idx_vendor_survey` (`vendor_id`),
  ADD KEY `idx_survey_date` (`survey_date`),
  ADD KEY `idx_site_surveys_delegation_id` (`delegation_id`),
  ADD KEY `idx_site_surveys_checkout` (`checkout_datetime`),
  ADD KEY `idx_site_surveys_store_model` (`store_model`),
  ADD KEY `idx_site_surveys_total_cameras` (`total_cameras`),
  ADD KEY `idx_installation_status` (`installation_status`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_state_country` (`name`,`country_id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_country` (`country_id`),
  ADD KEY `idx_zone` (`zone_id`),
  ADD KEY `idx_status` (`status`);

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
-- Indexes for table `user_menu_permissions`
--
ALTER TABLE `user_menu_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_menu` (`user_id`,`menu_item_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_menu` (`menu_item_id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vendor_code` (`vendor_code`),
  ADD KEY `idx_vendor_code` (`vendor_code`),
  ADD KEY `idx_gst_number` (`gst_number`),
  ADD KEY `idx_pan_card` (`pan_card_number`);

--
-- Indexes for table `vendor_permissions`
--
ALTER TABLE `vendor_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vendor_permission` (`vendor_id`,`permission_key`),
  ADD KEY `granted_by` (`granted_by`),
  ADD KEY `idx_vendor_id` (`vendor_id`),
  ADD KEY `idx_permission_key` (`permission_key`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=695;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `boq_items`
--
ALTER TABLE `boq_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=596;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `daily_material_usage`
--
ALTER TABLE `daily_material_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `daily_work_photos`
--
ALTER TABLE `daily_work_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `installation_delegations`
--
ALTER TABLE `installation_delegations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `installation_materials`
--
ALTER TABLE `installation_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `inventory_dispatches`
--
ALTER TABLE `inventory_dispatches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory_dispatch_items`
--
ALTER TABLE `inventory_dispatch_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `inventory_tracking`
--
ALTER TABLE `inventory_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `site_delegations`
--
ALTER TABLE `site_delegations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `site_surveys`
--
ALTER TABLE `site_surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user_menu_permissions`
--
ALTER TABLE `user_menu_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `vendor_permissions`
--
ALTER TABLE `vendor_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`),
  ADD CONSTRAINT `cities_ibfk_2` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  ADD CONSTRAINT `cities_ibfk_3` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `installation_delegations`
--
ALTER TABLE `installation_delegations`
  ADD CONSTRAINT `installation_delegations_ibfk_1` FOREIGN KEY (`survey_id`) REFERENCES `site_surveys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_delegations_ibfk_2` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_delegations_ibfk_3` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_delegations_ibfk_4` FOREIGN KEY (`delegated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_delegations_ibfk_5` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `installation_materials`
--
ALTER TABLE `installation_materials`
  ADD CONSTRAINT `installation_materials_ibfk_1` FOREIGN KEY (`installation_id`) REFERENCES `installation_delegations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `installation_notifications`
--
ALTER TABLE `installation_notifications`
  ADD CONSTRAINT `installation_notifications_ibfk_1` FOREIGN KEY (`installation_id`) REFERENCES `installation_delegations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_notifications_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `installation_progress`
--
ALTER TABLE `installation_progress`
  ADD CONSTRAINT `installation_progress_ibfk_1` FOREIGN KEY (`installation_id`) REFERENCES `installation_delegations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installation_progress_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`boq_item_id`) REFERENCES `boq_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `material_dispatches`
--
ALTER TABLE `material_dispatches`
  ADD CONSTRAINT `material_dispatches_ibfk_1` FOREIGN KEY (`material_request_id`) REFERENCES `material_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `material_requests`
--
ALTER TABLE `material_requests`
  ADD CONSTRAINT `fk_material_requests_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_material_requests_survey_id` FOREIGN KEY (`survey_id`) REFERENCES `site_surveys` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `material_requests_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_requests_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_menu_permissions`
--
ALTER TABLE `role_menu_permissions`
  ADD CONSTRAINT `role_menu_permissions_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sites`
--
ALTER TABLE `sites`
  ADD CONSTRAINT `sites_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sites_ibfk_2` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sites_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sites_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sites_ibfk_5` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `site_delegations`
--
ALTER TABLE `site_delegations`
  ADD CONSTRAINT `site_delegations_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `site_delegations_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `site_delegations_ibfk_3` FOREIGN KEY (`delegated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `site_surveys`
--
ALTER TABLE `site_surveys`
  ADD CONSTRAINT `fk_site_surveys_delegation` FOREIGN KEY (`delegation_id`) REFERENCES `site_delegations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `site_surveys_ibfk_1` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `site_surveys_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  ADD CONSTRAINT `states_ibfk_2` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_menu_permissions`
--
ALTER TABLE `user_menu_permissions`
  ADD CONSTRAINT `user_menu_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_menu_permissions_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendor_permissions`
--
ALTER TABLE `vendor_permissions`
  ADD CONSTRAINT `vendor_permissions_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vendor_permissions_ibfk_2` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
