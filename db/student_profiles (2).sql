-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Aug 28, 2025 at 06:09 PM
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
-- Database: `crad_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `school_id` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `program` varchar(50) NOT NULL,
  `cluster` varchar(20) DEFAULT '0',
  `faculty_id` int(11) DEFAULT NULL,
  `school_year` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `school_id`, `full_name`, `program`, `cluster`, `faculty_id`, `school_year`, `created_at`, `updated_at`) VALUES
(14, 1, '21016692', 'John Marvic Giray', 'BSIT', '41006', 7, '2025-2026', '2025-08-25 13:16:56', '2025-08-28 15:48:46'),
(15, 11, '22101234', 'Leandro Lojero', 'BSIT', '41006', 7, '2025-2026', '2025-08-28 05:26:12', '2025-08-28 15:48:46'),
(16, 12, '22105678', 'Angelito Pampanga', 'BSIT', '41006', 7, '2025-2026', '2025-08-28 05:28:03', '2025-08-28 15:48:46'),
(17, 13, '22107890', 'Geo Caranza', 'BSIT', '41006', 7, '2025-2026', '2025-08-28 05:29:36', '2025-08-28 15:48:46'),
(18, 14, '22010987', 'Erico Golay', 'BSIT', '41006', 7, '2025-2026', '2025-08-28 05:31:02', '2025-08-28 15:48:46'),
(19, 9, '22014823', 'Kang Haerin', 'BSIT', '41006', 7, '2024-2025', '2025-08-28 15:12:40', '2025-08-28 15:48:46'),
(20, 7, '22014829', 'Bad Abbadon', 'BSIT', '41006', 7, '2024-2025', '2025-08-28 15:13:31', '2025-08-28 15:48:46'),
(21, 8, '22014821', 'Hanni Pham', 'BSIT', '41006', 7, '2024-2025', '2025-08-28 15:14:37', '2025-08-28 15:48:46'),
(22, 15, '22011948', 'Kim Minji', 'BSIT', '41006', 7, '2024-2025', '2025-08-28 15:15:46', '2025-08-28 15:48:46'),
(23, 16, '22014857', 'Danielle June', 'BSIT', '41006', 7, '2024-2025', '2025-08-28 15:17:08', '2025-08-28 15:48:46');

--
-- Indexes for dumped tables
--
-- Notifications table for CRAD system
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`),
  ADD KEY `fk_student_profiles_faculty` (`faculty_id`),
  ADD KEY `idx_student_profiles_cluster` (`cluster`),
  ADD KEY `idx_student_profiles_program_year` (`program`,`school_year`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `fk_student_profiles_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
