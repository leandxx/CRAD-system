-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Sep 16, 2025 at 04:47 PM
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
-- Table structure for table `clusters`
--

CREATE TABLE `clusters` (
  `id` int(11) NOT NULL,
  `program` varchar(100) NOT NULL,
  `cluster` varchar(50) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `assigned_date` date DEFAULT NULL,
  `student_count` int(11) DEFAULT 0,
  `capacity` int(11) DEFAULT 50,
  `status` enum('assigned','pending','unassigned') DEFAULT 'unassigned',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clusters`
--

INSERT INTO `clusters` (`id`, `program`, `cluster`, `school_year`, `faculty_id`, `assigned_date`, `student_count`, `capacity`, `status`, `created_date`) VALUES
(222, 'BSIT', '41006', '2025-2026', 2, '2025-09-08', 6, 50, 'assigned', '2025-09-16 09:08:27');

-- --------------------------------------------------------

--
-- Table structure for table `defense_panel`
--

CREATE TABLE `defense_panel` (
  `id` int(11) NOT NULL,
  `defense_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `role` enum('chair','member') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `defense_panel`
--

INSERT INTO `defense_panel` (`id`, `defense_id`, `faculty_id`, `role`, `created_at`) VALUES
(45, 0, 8, 'chair', '2025-09-01 16:06:24'),
(46, 0, 11, 'member', '2025-09-01 16:06:24'),
(47, 0, 10, 'member', '2025-09-01 16:06:24'),
(51, 3, 8, 'chair', '2025-09-01 16:37:40'),
(52, 3, 11, 'member', '2025-09-01 16:37:40'),
(53, 3, 10, 'member', '2025-09-01 16:37:40'),
(192, 36, 12, 'chair', '2025-09-16 10:23:04'),
(193, 36, 11, 'member', '2025-09-16 10:23:04'),
(194, 36, 10, 'member', '2025-09-16 10:23:04'),
(243, 44, 12, 'chair', '2025-09-16 14:16:03'),
(244, 44, 11, 'member', '2025-09-16 14:16:03'),
(245, 44, 10, 'member', '2025-09-16 14:16:03'),
(246, 45, 12, 'chair', '2025-09-16 14:16:49'),
(247, 45, 11, 'member', '2025-09-16 14:16:49'),
(248, 45, 10, 'member', '2025-09-16 14:16:49'),
(252, 46, 12, 'chair', '2025-09-16 14:18:37'),
(253, 46, 11, 'member', '2025-09-16 14:18:37'),
(254, 46, 10, 'member', '2025-09-16 14:18:37'),
(255, 47, 12, 'chair', '2025-09-16 14:21:11'),
(256, 47, 11, 'member', '2025-09-16 14:21:11'),
(257, 47, 10, 'member', '2025-09-16 14:21:11'),
(258, 48, 12, 'chair', '2025-09-16 14:46:10'),
(259, 48, 11, 'member', '2025-09-16 14:46:10'),
(260, 48, 10, 'member', '2025-09-16 14:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `defense_schedules`
--

CREATE TABLE `defense_schedules` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `defense_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled','failed','passed') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `defense_type` enum('pre_oral','final','pre_oral_redefense','final_redefense') DEFAULT NULL,
  `defense_result` enum('pending','passed','failed','redefense') DEFAULT 'pending',
  `parent_defense_id` int(11) DEFAULT NULL,
  `redefense_reason` text DEFAULT NULL,
  `is_redefense` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `defense_schedules`
--

INSERT INTO `defense_schedules` (`id`, `group_id`, `defense_date`, `start_time`, `end_time`, `room_id`, `status`, `created_at`, `defense_type`, `defense_result`, `parent_defense_id`, `redefense_reason`, `is_redefense`, `completed_at`, `updated_at`) VALUES
(47, 16, '2025-09-16', '09:00:00', '10:00:00', 1, 'failed', '2025-09-16 14:21:11', 'pre_oral', 'pending', NULL, NULL, 0, NULL, '2025-09-16 14:21:25'),
(48, 16, '2025-09-17', '09:00:00', '10:00:00', 1, 'scheduled', '2025-09-16 14:46:10', 'pre_oral_redefense', 'pending', 47, NULL, 1, NULL, '2025-09-16 14:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `expertise` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `fullname`, `department`, `expertise`) VALUES
(1, 'Dr. Maria Santos', 'Accounting', 'Financial Accounting and Auditing'),
(2, 'Prof. James Wilson', 'Information Technology', 'Web Development and Database Systems'),
(3, 'Dr. Lisa Chen', 'Hospitality Management', 'Hotel Operations and Management'),
(4, 'Prof. Robert Garcia', 'Criminology', 'Forensic Science and Criminal Investigation'),
(5, 'Dr. Sarah Johnson', 'Tourism', 'Eco-Tourism and Travel Management'),
(6, 'Dr. Michael Brown', 'Accounting', 'Taxation and Business Law'),
(7, 'Prof. Emily Williams', 'Information Technology', 'Cybersecurity and Network Administration'),
(8, 'Dr. David Lee', 'Hospitality Management', 'Food and Beverage Management'),
(9, 'Prof. Amanda Rodriguez', 'Criminology', 'Criminal Psychology and Behavior'),
(10, 'Dr. Jennifer Kim', 'Tourism', 'Tourism Planning and Development'),
(11, 'Dr. Justene Jean Siarez', 'Psychology', 'Medical Office Administration');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `program` varchar(50) NOT NULL,
  `cluster_id` int(11) DEFAULT NULL,
  `join_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `program`, `cluster_id`, `join_code`, `created_at`) VALUES
(15, 'GRP 2', 'BSIT', 222, '5B97C1', '2025-08-29 05:08:49'),
(16, 'GRP 1', 'BSIT', 222, 'C7E820', '2025-09-01 10:10:40');

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`id`, `group_id`, `student_id`) VALUES
(32, 15, 1),
(36, 15, 2),
(33, 15, 12),
(35, 15, 13),
(34, 15, 14),
(46, 16, 7);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`, `updated_at`) VALUES
(839, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(840, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(841, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(842, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(843, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(844, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(845, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(846, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(847, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(848, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(849, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(850, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(851, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(852, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(853, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(854, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(855, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(856, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(857, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(858, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(859, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(860, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(861, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(862, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 09:21:52', '2025-09-16 09:21:52'),
(863, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(864, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(865, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(866, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(867, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(868, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(869, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(870, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(871, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(872, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(873, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(874, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(875, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(876, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(877, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(878, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(879, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(880, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(881, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(882, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(883, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(884, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(885, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(886, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:23:04', '2025-09-16 10:23:04'),
(887, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(888, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(889, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(890, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(891, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(892, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(893, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(894, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(895, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(896, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(897, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(898, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(899, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(900, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(901, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(902, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(903, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(904, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(905, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(906, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(907, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(908, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(909, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(910, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 10:46:37', '2025-09-16 10:46:37'),
(911, 1, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(912, 2, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(913, 5, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(914, 6, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(915, 7, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(916, 8, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(917, 9, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(918, 10, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(919, 11, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(920, 12, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(921, 13, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(922, 14, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(923, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(924, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(925, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(926, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(927, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(928, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(929, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(930, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(931, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(932, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(933, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(934, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:05:24', '2025-09-16 11:05:24'),
(935, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(936, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(937, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(938, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(939, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(940, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(941, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(942, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(943, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(944, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(945, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(946, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(947, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(948, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(949, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(950, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(951, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(952, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(953, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(954, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(955, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(956, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(957, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(958, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:07:13', '2025-09-16 11:07:13'),
(959, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(960, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(961, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(962, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(963, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(964, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(965, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(966, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(967, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(968, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(969, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(970, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(971, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(972, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(973, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(974, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(975, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(976, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(977, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(978, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(979, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(980, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(981, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(982, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:18:10', '2025-09-16 11:18:10'),
(983, 1, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(984, 2, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(985, 5, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(986, 6, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(987, 7, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(988, 8, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(989, 9, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(990, 10, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(991, 11, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(992, 12, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(993, 13, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(994, 14, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(995, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(996, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(997, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(998, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(999, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(1000, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(1001, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(1002, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(1003, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(1004, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(1005, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(1006, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:20:27', '2025-09-16 11:20:27'),
(1007, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1008, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1009, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1010, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1011, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1012, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1013, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1014, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1015, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1016, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1017, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1018, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1019, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1020, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1021, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1022, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1023, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1024, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1025, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1026, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1027, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1028, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1029, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1030, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:26:36', '2025-09-16 11:26:36'),
(1031, 1, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1032, 2, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1033, 5, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1034, 6, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1035, 7, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1036, 8, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1037, 9, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1038, 10, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1039, 11, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1040, 12, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1041, 13, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1042, 14, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1043, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1044, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1045, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1046, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1047, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1048, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1049, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1050, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1051, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1052, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1053, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1054, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:52:17', '2025-09-16 11:52:17'),
(1055, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1056, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1057, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1058, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1059, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1060, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1061, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1062, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1063, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1064, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1065, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1066, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1067, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1068, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1069, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1070, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1071, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1072, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1073, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1074, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1075, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1076, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1077, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1078, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 11:57:46', '2025-09-16 11:57:46'),
(1079, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1080, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1081, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1082, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1083, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1084, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1085, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1086, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1087, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1088, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1089, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1090, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 12:00:09', '2025-09-16 12:00:09'),
(1091, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1092, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1093, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1094, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1095, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1096, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1097, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1098, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1099, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1100, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1101, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1102, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1103, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1104, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1105, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1106, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1107, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1108, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1109, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1110, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1111, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1112, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1113, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1114, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 12:02:45', '2025-09-16 12:02:45'),
(1115, 1, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1116, 2, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1117, 5, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1118, 6, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1119, 7, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1120, 8, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1121, 9, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1122, 10, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1123, 11, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1124, 12, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1125, 13, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1126, 14, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:13:30', '2025-09-16 13:13:30'),
(1127, 1, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1128, 2, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1129, 5, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1130, 6, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1131, 7, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1132, 8, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1133, 9, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1134, 10, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1135, 11, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1136, 12, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1137, 13, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24'),
(1138, 14, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00', 'info', 0, '2025-09-16 13:45:24', '2025-09-16 13:45:24');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`, `updated_at`) VALUES
(1139, 1, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1140, 2, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1141, 5, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1142, 6, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1143, 7, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1144, 8, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1145, 9, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1146, 10, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1147, 11, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1148, 12, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1149, 13, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1150, 14, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 10:00:00', 'info', 0, '2025-09-16 13:45:41', '2025-09-16 13:45:41'),
(1151, 1, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1152, 2, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1153, 5, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1154, 6, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1155, 7, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1156, 8, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1157, 9, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1158, 10, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1159, 11, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1160, 12, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1161, 13, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1162, 14, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 13:51:47', '2025-09-16 13:51:47'),
(1163, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1164, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1165, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1166, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1167, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1168, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1169, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1170, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1171, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1172, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1173, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1174, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1175, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1176, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1177, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1178, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1179, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1180, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1181, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1182, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1183, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1184, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1185, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1186, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 13:54:10', '2025-09-16 13:54:10'),
(1187, 1, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1188, 2, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1189, 5, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1190, 6, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1191, 7, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1192, 8, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1193, 9, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1194, 10, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1195, 11, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1196, 12, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1197, 13, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1198, 14, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:10:18', '2025-09-16 14:10:18'),
(1199, 1, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1200, 2, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1201, 5, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1202, 6, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1203, 7, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1204, 8, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1205, 9, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1206, 10, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1207, 11, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1208, 12, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1209, 13, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1210, 14, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1211, 1, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1212, 2, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1213, 5, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1214, 6, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1215, 7, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1216, 8, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1217, 9, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1218, 10, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1219, 11, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1220, 12, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1221, 13, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1222, 14, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-17 at 09:00:00', 'info', 0, '2025-09-16 14:16:03', '2025-09-16 14:16:03'),
(1223, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1224, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1225, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1226, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1227, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1228, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1229, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1230, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1231, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1232, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1233, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1234, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 10:00', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1235, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1236, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1237, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1238, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1239, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1240, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1241, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1242, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1243, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1244, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1245, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1246, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:16:49', '2025-09-16 14:16:49'),
(1247, 1, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1248, 2, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1249, 5, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1250, 6, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1251, 7, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1252, 8, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1253, 9, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1254, 10, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1255, 11, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1256, 12, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1257, 13, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1258, 14, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:17:47', '2025-09-16 14:17:47'),
(1259, 1, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1260, 2, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1261, 5, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1262, 6, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1263, 7, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1264, 8, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1265, 9, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1266, 10, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1267, 11, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1268, 12, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1269, 13, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1270, 14, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-16 at 09:40', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1271, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1272, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1273, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1274, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1275, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1276, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1277, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1278, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1279, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1280, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1281, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1282, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:18:37', '2025-09-16 14:18:37'),
(1283, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1284, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1285, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1286, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1287, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1288, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1289, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1290, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1291, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1292, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1293, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1294, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-16 at 09:00', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1295, 1, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1296, 2, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1297, 5, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1298, 6, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1299, 7, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1300, 8, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1301, 9, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1302, 10, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1303, 11, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1304, 12, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1305, 13, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1306, 14, 'Defense Ready for Evaluation', 'The defense for group GRP 1 has concluded and is ready for evaluation.', 'info', 0, '2025-09-16 14:21:11', '2025-09-16 14:21:11'),
(1307, 1, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1308, 2, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1309, 5, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1310, 6, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1311, 7, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1312, 8, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1313, 9, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1314, 10, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1315, 11, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1316, 12, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1317, 13, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10'),
(1318, 14, 'Redefense Scheduled', 'A redefense has been scheduled for group: GRP 1 on 2025-09-17 at 09:00', 'info', 0, '2025-09-16 14:46:10', '2025-09-16 14:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `panel_invitations`
--

CREATE TABLE `panel_invitations` (
  `id` int(11) NOT NULL,
  `panel_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `invited_at` datetime NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `panel_invitations`
--

INSERT INTO `panel_invitations` (`id`, `panel_id`, `token`, `status`, `invited_at`, `responded_at`) VALUES
(0, 8, 'f02432185b3a80a66691cd67336e800e32c170c099ec03d8c7e90f8549cd4fa6', 'rejected', '2025-08-25 22:31:46', '2025-08-25 22:33:52'),
(0, 8, '2befe2d3c3c74a7b237e31e5cc78245b4d4a97b7bc3d536f6c41e82c3274a076', 'accepted', '2025-08-25 22:37:54', '2025-08-25 22:38:55'),
(0, 9, '728ecda4f4f6fa58174a6e9f0e42be8aadf06823072c56df5ea92a5df1c31458', 'accepted', '2025-08-27 18:47:31', '2025-08-27 18:48:43'),
(0, 8, 'b0c46e117a244b46d3e794218855b126ee3d202a32410d0e0e25ee3d0779f829', 'accepted', '2025-08-30 17:40:43', '2025-08-30 17:41:37');

-- --------------------------------------------------------

--
-- Table structure for table `panel_members`
--

CREATE TABLE `panel_members` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `program` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `role` enum('chairperson','member') NOT NULL DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `panel_members`
--

INSERT INTO `panel_members` (`id`, `first_name`, `last_name`, `email`, `specialization`, `program`, `status`, `role`, `created_at`, `updated_at`) VALUES
(8, 'John Marvic', 'Giray', 'girayjohnmarvic09@gmail.com', 'Information Technology', 'bsit', 'active', 'chairperson', '2025-08-25 14:31:40', '2025-08-25 14:31:40'),
(9, 'Justene Jean', 'Siarez', 'justenesiarez@gmail.com', 'Medical Office Admin', 'general', 'active', 'chairperson', '2025-08-27 10:47:12', '2025-08-27 10:47:12'),
(10, 'Prof. John Dela', 'Santos', 'leanlojero23@gmail.com', 'AI Expert', 'bsit', 'active', 'member', '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(11, 'John', 'HOOD', 'Haeri12n@gmail.com', 'Data Privacy & Cybersecurity', 'bsit', 'active', 'member', '2025-09-01 08:27:20', '2025-09-01 08:27:38'),
(12, 'John', 'Cruz', 'Haerin@gmail.com', 'Data Privacy & Cybersecurity', 'bsit', 'active', 'chairperson', '2025-09-01 16:52:34', '2025-09-01 16:52:34');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `payment_type` enum('research_forum','pre_oral_defense','final_defense','pre_oral_redefense','final_redefense') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `pdf_receipt` varchar(255) DEFAULT NULL COMMENT 'Legacy PDF receipt field',
  `status` enum('pending','approved','rejected','completed','failed') NOT NULL DEFAULT 'pending',
  `admin_approved` tinyint(1) DEFAULT 0,
  `review_feedback` text DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_receipts` text DEFAULT NULL COMMENT 'JSON array of image file paths',
  `image_review` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `payment_type`, `amount`, `pdf_receipt`, `status`, `admin_approved`, `review_feedback`, `payment_date`, `image_receipts`, `image_review`) VALUES
(34, 7, 'research_forum', 0.00, NULL, 'approved', 1, NULL, '2025-09-16 14:20:25', '[\"../assets/uploads/receipts/1758032425_0_betw.png\"]', '[{\"status\":\"approved\",\"feedback\":\"\",\"updated_at\":\"2025-09-16T22:20:42+08:00\"}]'),
(35, 7, 'pre_oral_defense', 0.00, NULL, 'approved', 1, NULL, '2025-09-16 14:20:33', '[\"../assets/uploads/receipts/1758032433_0_5c7031fefd3701bfffff7a335de4a7da.jpg\"]', '[{\"status\":\"approved\",\"feedback\":\"\",\"updated_at\":\"2025-09-16T22:20:43+08:00\"}]'),
(38, 7, 'pre_oral_redefense', 0.00, NULL, 'approved', 1, NULL, '2025-09-16 14:42:58', '[\"../assets/uploads/receipts/1758033778_0_workflow diagram.jpg\"]', '{\"redefense\":true,\"0\":{\"status\":\"approved\",\"feedback\":\"\",\"updated_at\":\"2025-09-16T16:45:46+02:00\"}}');

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Completed') NOT NULL DEFAULT 'Pending',
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `final_defense_open` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `group_id`, `title`, `description`, `file_path`, `submitted_at`, `status`, `reviewed_at`, `final_defense_open`) VALUES
(18, 16, 'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration', 'This study aims to design and develop a campus navigation system that uses QR codes placed at key locations inside school buildings. The system will be integrated with a cloud database to provide real-time directions and facility information accessible through a mobile application. The project addresses the challenges of new students and visitors in locating rooms, offices, and facilities within the campus, thereby improving efficiency and user experience.', '../assets/uploads/proposals/Cap101-reviewer-Prelim.pdf', '2025-09-01 15:21:40', 'Completed', '2025-09-16 14:20:49', 0),
(19, 15, 'The Impact of Social Media on Adolescent Mental Health: A Case Study Approach', 'This thesis investigates the relationship between social media usage and mental health outcomes in adolescents. Through qualitative interviews and quantitative surveys, it aims to identify patterns of social media behavior that correlate with anxiety, depression, and self-esteem levels. The study offers recommendations for healthier online habits and policy suggestions for social media platforms.', '../assets/uploads/proposals/Sample-Interview-Questions (1).pdf', '2025-09-01 15:49:14', 'Pending', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `required_documents`
--

CREATE TABLE `required_documents` (
  `id` int(11) NOT NULL,
  `document_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `required_for_defense` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(50) DEFAULT NULL,
  `building` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `building`, `capacity`) VALUES
(1, 'Defense Room 1', 'CRAD Office', NULL),
(2, 'Defense Room 2', 'CRAD Office', NULL),
(3, 'Defense Room 3', 'CRAD Office', NULL),
(4, 'Defense Room 4', 'CRAD Office', NULL),
(5, 'Defense Room 5', 'CRAD Office', NULL);

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
(14, 1, '21016692', 'John Marvic Giray', 'BSIT', '41006', 2, '2025-2026', '2025-08-25 13:16:56', '2025-09-08 10:11:10'),
(15, 11, '22101234', 'Leandro Lojero', 'BSIT', 'Not Assigned', NULL, '2025-2026', '2025-08-28 05:26:12', '2025-08-28 05:26:12'),
(16, 12, '22105678', 'Angelito Pampanga', 'BSIT', '41006', 2, '2025-2026', '2025-08-28 05:28:03', '2025-09-08 10:11:10'),
(17, 13, '22107890', 'Geo Caranza', 'BSIT', '41006', 2, '2025-2026', '2025-08-28 05:29:36', '2025-09-08 10:11:10'),
(18, 14, '22010987', 'Erico Golay', 'BSIT', '41006', 2, '2025-2026', '2025-08-28 05:31:02', '2025-09-08 10:11:10'),
(19, 2, '12345678', 'Coby Bryant Giray', 'BSIT', '41006', 2, '2025-2026', '2025-08-29 05:15:18', '2025-09-08 10:11:10'),
(20, 7, '22011941', 'Hanni Pham', 'BSIT', '41006', 2, '2025-2026', '2025-09-01 10:05:10', '2025-09-08 14:45:02'),
(21, 9, '22014823', 'Kang Haerin', 'BSIT', NULL, NULL, '2025-2026', '2025-09-01 10:12:00', '2025-09-16 09:08:27');

-- --------------------------------------------------------

--
-- Table structure for table `submission_timelines`
--

CREATE TABLE `submission_timelines` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submission_timelines`
--

INSERT INTO `submission_timelines` (`id`, `title`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'Capstone', 'Timeline', 0, '2025-08-25 13:15:48', '2025-08-27 19:37:22'),
(4, 'Capstone', 'Timeline', 0, '2025-08-28 18:25:22', '2025-08-29 04:59:15'),
(5, 'Capstone', '', 0, '2025-08-29 05:01:08', '2025-09-10 07:34:23');

-- --------------------------------------------------------

--
-- Table structure for table `timeline_milestones`
--

CREATE TABLE `timeline_milestones` (
  `id` int(11) NOT NULL,
  `timeline_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `deadline` datetime NOT NULL,
  `status` enum('pending','active','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timeline_milestones`
--

INSERT INTO `timeline_milestones` (`id`, `timeline_id`, `title`, `description`, `deadline`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Chapter 1 Week 1', '', '2025-08-20 23:45:00', 'pending', '2025-08-20 15:43:53', '2025-08-20 15:43:53'),
(2, 1, 'Chapter 2 Week 2', '', '2025-08-20 23:46:00', 'pending', '2025-08-20 15:43:53', '2025-08-20 15:43:53'),
(3, 2, 'Phase 1 – Proposal Stage', 'Week 1–2 → Topic Selection & Title Defense\r\n\r\nWeek 3–4 → Submission of Research Proposal (Chapters 1–3 draft)\r\n\r\nWeek 5 → Proposal Defense & Panel Feedback\r\n\r\nWeek 6 → Proposal Revision & Final Approval', '2025-08-23 12:00:00', 'pending', '2025-08-22 07:56:25', '2025-08-22 07:57:02'),
(4, 2, 'Phase 2 – Research Development', 'Week 7–8 → Data Gathering / System Development (if IT capstone)\r\n\r\nWeek 9–10 → Progress Report Submission (50% completion)\r\n\r\nWeek 11–12 → Second Progress Report Submission (80% completion)', '2025-08-25 12:00:00', 'pending', '2025-08-22 07:56:25', '2025-08-22 07:56:25'),
(5, 2, 'Phase 3 – Pre-Final Stage', 'Week 13 → Draft Submission of Full Manuscript (Chapters 1–5)\r\n\r\nWeek 14 → Pre-Oral Defense\r\n\r\nWeek 15 → Revision & Final Manuscript Submission', '2025-08-26 12:00:00', 'pending', '2025-08-22 07:56:25', '2025-08-22 07:56:25'),
(6, 2, 'Phase 4 – Final Stage', 'Week 16 → Final Defense / System Demonstration\r\n\r\nWeek 17 → Incorporation of Panel Revisions\r\n\r\nWeek 18 → Final Book Binding / System Deployment / Submission to Library', '2025-08-29 12:00:00', 'pending', '2025-08-22 07:56:25', '2025-08-22 07:56:25'),
(7, 3, 'Chapter 1 Week 1', 'Pasa kayo mga kupal', '2025-08-25 12:00:00', 'pending', '2025-08-25 13:15:48', '2025-08-25 13:15:48'),
(8, 3, 'Chapter 2 Week 2', 'Ito rin papahirapan ko kayo', '2025-08-26 12:00:00', 'pending', '2025-08-25 13:15:48', '2025-08-25 13:15:48'),
(9, 3, 'Chapter 3 Week 3', 'Yan 1 day lang deadlines n\'yo haha', '2025-08-27 12:00:00', 'pending', '2025-08-25 13:15:48', '2025-08-25 13:15:48'),
(10, 4, 'Chapter 1 Week 1', '', '2025-08-30 08:00:00', 'pending', '2025-08-28 18:25:22', '2025-08-28 18:25:22'),
(11, 4, 'Chapter 2 Week 2', '', '2025-08-31 08:00:00', 'pending', '2025-08-28 18:25:22', '2025-08-28 18:25:22'),
(12, 4, 'Chapter 3 Week 3', '', '2025-09-01 08:00:00', 'pending', '2025-08-28 18:25:22', '2025-08-28 18:25:22'),
(13, 5, 'Chapter 1 Week 1', '', '2025-08-30 08:00:00', 'pending', '2025-08-29 05:01:08', '2025-08-29 05:01:08'),
(14, 5, 'Chapter 2 Week 2', '', '2025-08-31 08:00:00', 'pending', '2025-08-29 05:01:08', '2025-08-29 05:01:08'),
(15, 5, 'Chapter 3 Week 3', '', '2025-09-01 08:00:00', 'pending', '2025-08-29 05:01:08', '2025-08-29 05:01:08');

-- --------------------------------------------------------

--
-- Table structure for table `user_tbl`
--

CREATE TABLE `user_tbl` (
  `user_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Faculty','Student') NOT NULL DEFAULT 'Student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_tbl`
--

INSERT INTO `user_tbl` (`user_id`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'girayjohnmarvic09@gmail.com', '$2y$10$HwoUxxl3T4A3LRnOiI4sTuxu0jcqX7PusDTDgAoiVf8VnVGWDUWli', 'Student', '2025-08-20 14:48:00'),
(2, 'coby@gmail.com', '$2y$10$dzFqqrbC2NTeO.Vn/8FtVOUuhQDDdIgdIKxSe4sJfUIfaKQnP5gwu', 'Student', '2025-08-20 15:18:56'),
(5, 'admin@gmail.com', '$2y$10$qfM4wrFY47klhpxEDSr0N.5KewyovnP62Qpt7tNXkGApVh/kTHzCy', 'Admin', '2025-08-20 15:40:21'),
(6, 'Hyein@gmail.com', '$2y$10$qjO0RfgNB/lBD2X5kHdJh.Nro9zl3Z8rxWIKv5yXnaieKmCn8x1Im', 'Admin', '2025-08-22 07:21:05'),
(7, 'leandro.lojero23@gmail.com', '$2y$10$U7UwMa8yFtv8jCCtgB8dW.Q949HIYS496xxESF9rpbwWzUVJEqaPy', 'Student', '2025-08-22 07:23:21'),
(8, 'Hanni@gmail.com', '$2y$10$gFZtjV5ywm5IBUiai9vFxeM3AgckDKkzIFbyLaGKWij19ILL88kn.', 'Student', '2025-08-22 07:30:46'),
(9, 'Haerin@gmail.com', '$2y$10$IFk3eicV9LTUxvINl6fzJezed50.6A6w.OKzVzFarlWy8H/D/CDeW', 'Student', '2025-08-24 09:19:19'),
(10, 'estardo@gmail.com', '$2y$10$pPBylJ7rtgHRHslCugRNzufDid11X/qtkidole72Zf2GCc5aXQvn.', 'Student', '2025-08-25 08:57:23'),
(11, 'leandro@gmail.com', '$2y$10$NoVF.NQOwk9to1HWOKc9yuIbcMUzLbrohqN33LEc7bsiXxT3p.ALW', 'Student', '2025-08-28 05:25:18'),
(12, 'angelito@gmail.com', '$2y$10$30N/ebHbkhWCL9m9NJ0l0u2Hc6wljG10EazPta/ymGzLzO8MapSUK', 'Student', '2025-08-28 05:27:20'),
(13, 'geo@gmail.com', '$2y$10$5msQtohhqAozmI7NSkqz6.EH49aK9q.pl9Ryo2j1bXMXWJ0uCStsC', 'Student', '2025-08-28 05:28:55'),
(14, 'erico@gmail.com', '$2y$10$YlPdn0CdG5dofql9ToTJheb01UnUXgXuhF8iRiVYPtx4dZ2T0XGmO', 'Student', '2025-08-28 05:30:19');

-- --------------------------------------------------------

--
-- Table structure for table `_tmp_check`
--

CREATE TABLE `_tmp_check` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clusters`
--
ALTER TABLE `clusters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faculty_id` (`faculty_id`),
  ADD KEY `idx_clusters_program_year` (`program`,`school_year`);

--
-- Indexes for table `defense_panel`
--
ALTER TABLE `defense_panel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `defense_id` (`defense_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_parent_defense` (`parent_defense_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `join_code` (`join_code`),
  ADD KEY `fk_groups_cluster` (`cluster_id`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_id` (`group_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `panel_invitations`
--
ALTER TABLE `panel_invitations`
  ADD PRIMARY KEY (`invited_at`) USING BTREE,
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `unique_invitation` (`invited_at`) USING BTREE,
  ADD KEY `panel_id` (`panel_id`);

--
-- Indexes for table `panel_members`
--
ALTER TABLE `panel_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `required_documents`
--
ALTER TABLE `required_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `submission_timelines`
--
ALTER TABLE `submission_timelines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timeline_milestones`
--
ALTER TABLE `timeline_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timeline_id` (`timeline_id`);

--
-- Indexes for table `user_tbl`
--
ALTER TABLE `user_tbl`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `_tmp_check`
--
ALTER TABLE `_tmp_check`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clusters`
--
ALTER TABLE `clusters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=224;

--
-- AUTO_INCREMENT for table `defense_panel`
--
ALTER TABLE `defense_panel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1319;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `defense_schedules` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-08-23 11:13:12' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE defense_schedules
  SET status = 'completed'
  WHERE end_time < CURTIME() 
    AND defense_date <= CURDATE()
    AND status = 'scheduled'$$

CREATE DEFINER=`root`@`localhost` EVENT `update_defense_status` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-08-24 03:57:24' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE defense_schedules
  SET status = 'completed'
  WHERE status = 'scheduled'
    AND TIMESTAMP(defense_date, end_time) < NOW()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
