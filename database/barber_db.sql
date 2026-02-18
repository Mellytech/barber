-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2026 at 05:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barber_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `appointment_datetime` datetime NOT NULL,
  `appointment_number` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `service`, `price`, `appointment_datetime`, `appointment_number`, `created_at`) VALUES
(1, 1, 'Haircut', 50.00, '2026-02-19 00:05:00', 'APP-260218-2180', '2026-02-18 00:05:31'),
(2, 1, 'Beard Trim', 30.00, '2026-02-27 00:08:00', 'APP-260218-3049', '2026-02-18 00:08:47'),
(3, 1, 'Haircut & Beard', 70.00, '2026-02-19 00:17:00', 'APP-260218-5967', '2026-02-18 00:17:53'),
(4, 1, 'Haircut & Beard', 70.00, '2026-02-19 00:17:00', 'APP-260218-BC33', '2026-02-18 00:19:38'),
(5, 1, 'Kids Cut', 35.00, '2026-02-18 04:20:00', 'APP-260218-D42A', '2026-02-18 00:21:04'),
(6, 1, 'Haircut', 50.00, '2026-02-20 00:24:00', 'APP-260218-2C73', '2026-02-18 00:24:36'),
(7, 1, 'Kids Cut', 35.00, '2026-02-28 00:29:00', 'APP-260218-E657', '2026-02-18 00:36:04'),
(8, 1, 'Kids Cut', 35.00, '2026-02-28 00:29:00', 'APP-260218-A1B6', '2026-02-18 00:37:07'),
(9, 1, 'Haircut & Beard', 70.00, '2026-02-27 05:50:00', 'APP-260218-E0D8', '2026-02-18 00:45:38'),
(10, 1, 'Beard Trim', 30.00, '2026-02-28 02:44:00', 'APP-260218-DB1B', '2026-02-18 02:44:20'),
(11, 5, 'Beard Trim', 30.00, '2026-02-19 06:20:00', 'APP-260218-D28D', '2026-02-18 03:20:48');

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_codes`
--

CREATE TABLE `email_verification_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` char(6) NOT NULL,
  `purpose` enum('register','login','admin_login') NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verification_codes`
--

INSERT INTO `email_verification_codes` (`id`, `user_id`, `code`, `purpose`, `is_used`, `created_at`, `expires_at`) VALUES
(1, 1, '519766', 'register', 0, '2026-02-17 15:39:21', '2026-02-17 16:49:21'),
(2, 1, '463922', 'login', 1, '2026-02-17 15:43:51', '2026-02-17 16:53:51'),
(3, 1, '445029', 'login', 1, '2026-02-17 16:09:45', '2026-02-17 17:19:45'),
(4, 1, '075649', 'login', 1, '2026-02-17 16:21:55', '2026-02-17 17:31:55'),
(5, 1, '661701', 'login', 1, '2026-02-17 16:28:36', '2026-02-17 17:38:36'),
(6, 2, '161759', 'register', 0, '2026-02-17 16:32:49', '2026-02-17 17:42:49'),
(7, 2, '565111', 'login', 1, '2026-02-17 16:39:01', '2026-02-17 17:49:01'),
(8, 2, '157590', '', 0, '2026-02-17 16:40:04', '2026-02-17 17:50:04'),
(9, 2, '725066', '', 0, '2026-02-17 16:42:13', '2026-02-17 17:52:13'),
(10, 2, '248511', '', 0, '2026-02-17 16:47:19', '2026-02-17 17:57:19'),
(11, 1, '816544', 'login', 1, '2026-02-17 16:57:22', '2026-02-17 18:07:22'),
(12, 4, '151726', '', 0, '2026-02-17 17:36:16', '2026-02-17 18:46:16'),
(13, 4, '334179', 'admin_login', 1, '2026-02-17 18:18:06', '2026-02-17 19:28:06'),
(14, 1, '524713', 'login', 1, '2026-02-17 18:36:27', '2026-02-17 19:46:27'),
(15, 1, '117558', 'login', 1, '2026-02-17 18:36:50', '2026-02-17 19:46:50'),
(16, 4, '864588', 'admin_login', 1, '2026-02-17 18:49:43', '2026-02-17 19:59:43'),
(17, 4, '079765', 'admin_login', 1, '2026-02-17 23:15:23', '2026-02-18 00:25:23'),
(18, 1, '959929', 'login', 1, '2026-02-17 23:35:53', '2026-02-18 00:45:53'),
(19, 1, '438221', 'login', 1, '2026-02-17 23:35:59', '2026-02-18 00:45:59'),
(20, 5, '794413', 'register', 0, '2026-02-18 01:47:12', '2026-02-18 02:57:12'),
(21, 1, '932113', 'login', 1, '2026-02-18 01:51:55', '2026-02-18 03:01:55'),
(22, 1, '048013', 'login', 1, '2026-02-18 01:52:15', '2026-02-18 03:02:15'),
(23, 1, '109314', 'login', 1, '2026-02-18 02:00:49', '2026-02-18 03:10:49'),
(24, 1, '091712', 'login', 1, '2026-02-18 02:32:14', '2026-02-18 02:42:14'),
(25, 1, '465405', 'login', 1, '2026-02-18 02:33:15', '2026-02-18 02:43:15'),
(26, 1, '015832', 'login', 1, '2026-02-18 02:33:19', '2026-02-18 02:43:19'),
(27, 1, '153829', 'login', 1, '2026-02-18 02:33:22', '2026-02-18 02:43:22'),
(28, 1, '891028', 'login', 1, '2026-02-18 02:42:36', '2026-02-18 02:52:36'),
(29, 1, '702197', 'login', 1, '2026-02-18 02:58:44', '2026-02-18 03:08:44'),
(30, 1, '321078', 'login', 1, '2026-02-18 03:02:02', '2026-02-18 03:12:02'),
(31, 1, '473381', 'login', 1, '2026-02-18 03:05:11', '2026-02-18 03:15:11'),
(32, 1, '870694', 'login', 1, '2026-02-18 03:09:29', '2026-02-18 03:19:29'),
(33, 1, '726105', 'login', 1, '2026-02-18 03:11:19', '2026-02-18 03:21:19'),
(34, 5, '005452', 'login', 1, '2026-02-18 03:18:36', '2026-02-18 03:28:36'),
(35, 4, '477262', 'admin_login', 1, '2026-02-18 03:25:58', '2026-02-18 03:35:58'),
(36, 4, '337701', 'admin_login', 1, '2026-02-18 03:29:01', '2026-02-18 03:39:01'),
(38, 7, '262615', 'register', 1, '2026-02-18 04:03:24', '2026-02-18 04:13:24'),
(39, 1, '080237', 'login', 1, '2026-02-18 04:37:51', '2026-02-18 04:47:51'),
(40, 1, '480126', 'login', 1, '2026-02-18 04:50:00', '2026-02-18 05:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `default_price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `default_price`, `is_active`, `created_at`) VALUES
(1, 'Haircut', 50.00, 1, '2026-02-17 15:38:01'),
(2, 'Beard Trim', 30.00, 1, '2026-02-17 15:38:01'),
(3, 'Haircut & Beard', 70.00, 1, '2026-02-17 15:38:01'),
(4, 'Kids Cut', 35.00, 1, '2026-02-17 15:38:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `is_admin`, `created_at`) VALUES
(1, 'Blessing Baidoo', 'baidoob7525@gmail.com', '$2y$10$micOaXVD0wOu/L.XXNv/xeWVvNiwpj6L49u1ed9lYXkCc3puK2gEa', 0, '2026-02-17 15:39:21'),
(2, 'Stephen', 'akuokostephen051@gmail.com', '$2y$10$MT3Srjd49eZgD4UXyO65ueWh6pql69/OOjgRDsu.m5zX3LnUwZpne', 0, '2026-02-17 16:32:49'),
(3, 'admin123', 'admin123@example.com', '$2y$10$wt87gi989lOfjRqRfinfk.jzblEjqvHTcZG6pZENKxiWBi/oUjXfG', 1, '2026-02-17 17:19:55'),
(4, 'Admin', 'blessingbaidoo71@gmail.com', '$2y$10$kB/YSULGnkvTjzNjvFou/.T.h3fFS8sA71hqTkdTfXnDRwNX8XlYO', 1, '2026-02-17 17:36:16'),
(5, 'martin', 'marwolf423@gmail.com', '$2y$10$w4Ro3sUOO5oCTfV/y6FHhuF3NLULzauV7yq5cMt4K41rrNVV6FHkS', 0, '2026-02-18 01:47:12'),
(7, 'vroy', 'ypee_baakop3@icloud.com', '$2y$10$VyoG0.wMxhMpt1j.D6aMI.Bt9grW3788p2RnYdPNMWJ3XBO1r0T1K', 0, '2026-02-18 04:03:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointment_number` (`appointment_number`),
  ADD KEY `idx_appointments_user` (`user_id`),
  ADD KEY `idx_appointment_number` (`appointment_number`);

--
-- Indexes for table `email_verification_codes`
--
ALTER TABLE `email_verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_verification_user_purpose` (`user_id`,`purpose`,`is_used`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `email_verification_codes`
--
ALTER TABLE `email_verification_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verification_codes`
--
ALTER TABLE `email_verification_codes`
  ADD CONSTRAINT `fk_verification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
