-- RIETC Computer Repair Portal (MySQL 8.0+ / MariaDB 10.5+)
CREATE DATABASE IF NOT EXISTS `database_fcn`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `database_fcn`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` VARCHAR(32) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `lastname` VARCHAR(100) NOT NULL,
  `department` VARCHAR(120) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `repair_tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_code` CHAR(11) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `student_id` VARCHAR(32) NOT NULL,
  `building` VARCHAR(160) NOT NULL,
  `floor` VARCHAR(100) NOT NULL,
  `contact_phone` VARCHAR(40) NOT NULL,
  `description` TEXT NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(40) NOT NULL DEFAULT 'รอดำเนินการ',
  `admin_note` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_repair_tickets_code` (`ticket_code`),
  KEY `idx_repair_tickets_user` (`user_id`),
  KEY `idx_repair_tickets_student` (`student_id`),
  KEY `idx_repair_tickets_status` (`status`),
  CONSTRAINT `fk_repair_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
