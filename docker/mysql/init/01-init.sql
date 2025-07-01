-- ============================================================================
-- MySQL Database Initialization Script
-- Sets up the database with proper settings for Laravel
-- ============================================================================

-- Set SQL mode for consistent behavior
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

-- Create the application database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `rachmat` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Create application user if it doesn't exist
CREATE USER IF NOT EXISTS 'rachmat_user'@'%' IDENTIFIED BY 'secure_password_change_me';

-- Grant all privileges on the application database
GRANT ALL PRIVILEGES ON `rachmat`.* TO 'rachmat_user'@'%';

-- Grant SELECT privilege on information_schema for Laravel schema inspection
GRANT SELECT ON `information_schema`.* TO 'rachmat_user'@'%';

-- Grant SELECT privilege on performance_schema for monitoring (optional)
GRANT SELECT ON `performance_schema`.* TO 'rachmat_user'@'%';

-- Flush privileges to ensure changes take effect
FLUSH PRIVILEGES;

-- Use the application database
USE `rachmat`;

-- Set default time zone (adjust as needed)
SET time_zone = '+00:00';

-- Create a health check table for monitoring
CREATE TABLE IF NOT EXISTS `health_check` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `status` varchar(50) NOT NULL DEFAULT 'healthy',
    `last_check` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial health check record
INSERT INTO `health_check` (`status`) VALUES ('healthy') 
ON DUPLICATE KEY UPDATE `last_check` = CURRENT_TIMESTAMP;

-- Show databases and users for verification
SELECT 'Database setup completed successfully' as message;
SHOW DATABASES;
SELECT User, Host FROM mysql.user WHERE User = 'rachmat_user'; 