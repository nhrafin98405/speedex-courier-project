-- SpeedEx Courier Service Database (v2 - Realtime + Email Edition)
-- Created: 2026-05-08

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+06:00";

CREATE DATABASE IF NOT EXISTS `speedex_courier` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `speedex_courier`;

DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `email_logs`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `parcel_tracking`;
DROP TABLE IF EXISTS `parcels`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `hubs`;

-- HUBS
CREATE TABLE `hubs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `district` VARCHAR(100) NOT NULL,
  `area` VARCHAR(100) NOT NULL,
  `address` TEXT NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `manager_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_hubs_district` (`district`),
  INDEX `idx_hubs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- USERS
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','hub_manager') NOT NULL DEFAULT 'hub_manager',
  `hub_id` INT UNSIGNED DEFAULT NULL,
  `district` VARCHAR(100) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_hub` (`hub_id`),
  FOREIGN KEY (`hub_id`) REFERENCES `hubs`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PARCELS
CREATE TABLE `parcels` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tracking_id` VARCHAR(20) NOT NULL UNIQUE,
  `sender_name` VARCHAR(100) NOT NULL,
  `sender_phone` VARCHAR(20) NOT NULL,
  `sender_email` VARCHAR(255) DEFAULT NULL,
  `sender_address` TEXT NOT NULL,
  `sender_hub_id` INT UNSIGNED NOT NULL,
  `receiver_name` VARCHAR(100) NOT NULL,
  `receiver_phone` VARCHAR(20) NOT NULL,
  `receiver_email` VARCHAR(255) DEFAULT NULL,
  `receiver_address` TEXT NOT NULL,
  `receiver_hub_id` INT UNSIGNED NOT NULL,
  `parcel_type` ENUM('document','small_parcel','medium_parcel','large_parcel','fragile') NOT NULL DEFAULT 'small_parcel',
  `weight` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `description` TEXT DEFAULT NULL,
  `delivery_type` ENUM('standard','express','same_day') NOT NULL DEFAULT 'standard',
  `payment_method` ENUM('sender_pay','receiver_pay','cod') NOT NULL DEFAULT 'sender_pay',
  `cod_amount` DECIMAL(10,2) DEFAULT 0.00,
  `delivery_charge` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('pending','picked_up','in_transit','at_hub','out_for_delivery','delivered','returned','cancelled') NOT NULL DEFAULT 'pending',
  `booked_by` INT UNSIGNED DEFAULT NULL,
  `delivered_at` DATETIME DEFAULT NULL,
  `estimated_delivery` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_parcels_tracking` (`tracking_id`),
  INDEX `idx_parcels_status` (`status`),
  FOREIGN KEY (`sender_hub_id`) REFERENCES `hubs`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`receiver_hub_id`) REFERENCES `hubs`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`booked_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PARCEL TRACKING
CREATE TABLE `parcel_tracking` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `parcel_id` INT UNSIGNED NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `hub_id` INT UNSIGNED DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `updated_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_tracking_parcel` (`parcel_id`),
  FOREIGN KEY (`parcel_id`) REFERENCES `parcels`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- BOOKINGS
CREATE TABLE `bookings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id` VARCHAR(20) NOT NULL UNIQUE,
  `parcel_id` INT UNSIGNED NOT NULL,
  `customer_email` VARCHAR(255) DEFAULT NULL,
  `pickup_date` DATE NOT NULL,
  `status` ENUM('pending','confirmed','picked_up','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parcel_id`) REFERENCES `parcels`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PAYMENTS
CREATE TABLE `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `payment_id` VARCHAR(20) NOT NULL UNIQUE,
  `parcel_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `method` ENUM('cash','bkash','nagad','rocket','bank_transfer','card') NOT NULL DEFAULT 'cash',
  `status` ENUM('pending','completed','refunded','failed') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`parcel_id`) REFERENCES `parcels`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTIFICATIONS
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'system',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notifications_user` (`user_id`),
  INDEX `idx_notifications_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- EMAIL LOGS
CREATE TABLE `email_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `to_email` VARCHAR(255) NOT NULL,
  `to_name` VARCHAR(150) DEFAULT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `template` VARCHAR(50) DEFAULT NULL,
  `parcel_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('sent','failed','queued') NOT NULL DEFAULT 'queued',
  `error` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email_status` (`status`),
  INDEX `idx_email_parcel` (`parcel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ACTIVITY LOGS
CREATE TABLE `activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_logs_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEED HUBS
INSERT INTO `hubs` (`name`,`code`,`district`,`area`,`address`,`phone`,`email`) VALUES
('Dhaka Hub','DHK-001','Dhaka','Dhanmondi','House 12, Road 5, Dhanmondi, Dhaka-1205','01711111111','dhaka@speedex.com'),
('Mymensingh Hub','MYM-001','Mymensingh','Sadar','Road 3, Mymensingh Sadar-2200','01722222222','mymensingh@speedex.com'),
('Chittagong Hub','CTG-001','Chittagong','Agrabad','CDA Avenue, Agrabad-4100','01733333333','ctg@speedex.com'),
('Sylhet Hub','SYL-001','Sylhet','Zindabazar','Zindabazar, Sylhet-3100','01744444444','sylhet@speedex.com'),
('Khulna Hub','KHL-001','Khulna','Sadar','Khan Jahan Ali Road, Khulna-9100','01755555555','khulna@speedex.com'),
('Barisal Hub','BAR-001','Barisal','Sadar','Sadar Road, Barisal-8200','01766666666','barisal@speedex.com'),
('Rajshahi Hub','RAJ-001','Rajshahi','Sadar','Shaheb Bazaar, Rajshahi-6100','01777777777','rajshahi@speedex.com'),
('Rangpur Hub','RNG-001','Rangpur','Sadar','Station Road, Rangpur-5400','01788888888','rangpur@speedex.com');

-- Default Admin (password: password)
INSERT INTO `users` (`full_name`,`email`,`phone`,`password`,`role`) VALUES
('Super Admin','admin@speedex.com','01700000000','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin'),
('Dhaka Manager','dhaka.manager@speedex.com','01700000001','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','hub_manager');
UPDATE `users` SET `hub_id`=1 WHERE `email`='dhaka.manager@speedex.com';
UPDATE `hubs` SET `manager_id`=2 WHERE `id`=1;

-- Sample parcels
INSERT INTO `parcels` (`tracking_id`,`sender_name`,`sender_phone`,`sender_email`,`sender_address`,`sender_hub_id`,`receiver_name`,`receiver_phone`,`receiver_email`,`receiver_address`,`receiver_hub_id`,`parcel_type`,`weight`,`description`,`delivery_type`,`payment_method`,`delivery_charge`,`total_amount`,`status`,`booked_by`) VALUES
('SPX12345678901','Rafiq Ahmed','01712345678','rafiq@example.com','Dhanmondi, Dhaka',1,'Shakib Hasan','01898765432','shakib@example.com','Mymensingh Sadar',2,'document',2.50,'Books','standard','sender_pay',80.00,80.00,'in_transit',1),
('SPX18765432180','Hasan Mahmud','01812345678','hasan@example.com','Mirpur 10, Dhaka',1,'Rakibul Islam','01898123456','rakib@example.com','Sylhet Sadar',4,'small_parcel',3.00,'Electronics','express','sender_pay',120.00,120.00,'in_transit',1),
('SPX17223344180','Sadia Akter','01623456789','sadia@example.com','Uttara, Dhaka',1,'Farhana Jannat','01534567890','farhana@example.com','Chittagong',3,'medium_parcel',5.00,'Clothing','standard','receiver_pay',100.00,100.00,'out_for_delivery',1),
('SPX15068176880','Emran Hossain','01945678901','emran@example.com','Gulshan, Dhaka',1,'Jahid Hasan','01456789012','jahid@example.com','Khulna',5,'large_parcel',8.00,'Furniture','standard','sender_pay',200.00,200.00,'delivered',1),
('SPX19098776880','Nusrat Jahan','01567890123','nusrat@example.com','Banani, Dhaka',1,'Tanvir Akter','01678901234','tanvir@example.com','Barisal',6,'fragile',1.50,'Glass items','express','cod',150.00,300.00,'delivered',1);

INSERT INTO `parcel_tracking` (`parcel_id`,`status`,`location`,`hub_id`,`remarks`) VALUES
(1,'Parcel Booked','Dhaka Hub',1,'Booking confirmed'),
(1,'Picked Up','Dhanmondi, Dhaka',1,'Picked from sender'),
(1,'In Transit','Dhaka → Mymensingh',1,'On the way'),
(2,'Parcel Booked','Dhaka Hub',1,'Booking confirmed'),
(2,'In Transit','Dhaka → Sylhet',1,'On the way'),
(3,'Out for Delivery','Chittagong Hub',3,'Delivery agent assigned'),
(4,'Delivered','Khulna',5,'Received by Jahid Hasan'),
(5,'Delivered','Barisal',6,'Received successfully');

INSERT INTO `email_logs` (`to_email`,`to_name`,`subject`,`template`,`parcel_id`,`status`) VALUES
('rafiq@example.com','Rafiq Ahmed','Your SpeedEx Parcel Has Been Booked','parcel_booked',1,'sent'),
('shakib@example.com','Shakib Hasan','A SpeedEx Parcel Is On Its Way','parcel_in_transit',1,'sent'),
('jahid@example.com','Jahid Hasan','Your SpeedEx Parcel Has Been Delivered','delivered',4,'sent');

INSERT INTO `notifications` (`user_id`,`type`,`title`,`message`) VALUES
(1,'parcel','New Parcel Booked','SPX12345678901 booked from Dhaka to Mymensingh'),
(1,'parcel','Parcel Delivered','SPX15068176880 delivered to Jahid Hasan'),
(1,'email','Email Sent','Delivery confirmation email sent to jahid@example.com');

-- PASSWORD RESETS
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token_hash` CHAR(64) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_pr_user` (`user_id`),
  INDEX `idx_pr_exp` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
