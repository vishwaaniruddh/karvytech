-- User Access Audit System
-- Tracks user access, API calls, and status codes

-- Drop existing tables if they exist
DROP TABLE IF EXISTS `user_audit_logs`;
DROP TABLE IF EXISTS `user_audit_sessions`;
DROP TABLE IF EXISTS `user_audit_api_stats`;

-- Create user_audit_logs table
CREATE TABLE `user_audit_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `username` VARCHAR(100) DEFAULT NULL,
  `user_role` VARCHAR(50) DEFAULT NULL,
  `action_type` VARCHAR(50) NOT NULL COMMENT 'login, logout, page_access, api_call, etc',
  `endpoint` VARCHAR(255) NOT NULL COMMENT 'URL or API endpoint accessed',
  `http_method` VARCHAR(10) DEFAULT NULL COMMENT 'GET, POST, PUT, DELETE, etc',
  `status_code` INT(11) DEFAULT NULL COMMENT 'HTTP status code',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `request_data` TEXT DEFAULT NULL COMMENT 'JSON encoded request parameters',
  `response_data` TEXT DEFAULT NULL COMMENT 'JSON encoded response data',
  `error_message` TEXT DEFAULT NULL,
  `execution_time` DECIMAL(10,4) DEFAULT NULL COMMENT 'Execution time in seconds',
  `session_id` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_username` (`username`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_endpoint` (`endpoint`),
  KEY `idx_status_code` (`status_code`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_audit_sessions table for tracking user sessions
CREATE TABLE `user_audit_sessions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `session_id` VARCHAR(100) NOT NULL,
  `login_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `logout_time` TIMESTAMP NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_activity` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_audit_api_stats table for API statistics
CREATE TABLE `user_audit_api_stats` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `endpoint` VARCHAR(255) NOT NULL,
  `http_method` VARCHAR(10) NOT NULL,
  `total_calls` INT(11) DEFAULT 0,
  `success_calls` INT(11) DEFAULT 0,
  `error_calls` INT(11) DEFAULT 0,
  `avg_execution_time` DECIMAL(10,4) DEFAULT NULL,
  `last_called` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_endpoint_method` (`endpoint`, `http_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
