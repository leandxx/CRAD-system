-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Sep 10, 2025 at 10:48 AM
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
(222, 'BSIT', '41006', '2025-2026', 2, '2025-09-08', 7, 50, 'assigned', '2025-09-08 14:45:02');

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
(150, 26, 12, 'chair', '2025-09-10 07:52:38'),
(151, 26, 11, 'member', '2025-09-10 07:52:38'),
(152, 26, 10, 'member', '2025-09-10 07:52:38');

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
  `defense_type` enum('pre_oral','final') DEFAULT 'pre_oral',
  `defense_result` enum('pending','passed','failed','redefense') DEFAULT 'pending',
  `parent_defense_id` int(11) DEFAULT NULL,
  `redefense_reason` text DEFAULT NULL,
  `is_redefense` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `defense_schedules`
--

INSERT INTO `defense_schedules` (`id`, `group_id`, `defense_date`, `start_time`, `end_time`, `room_id`, `status`, `created_at`, `defense_type`, `defense_result`, `parent_defense_id`, `redefense_reason`, `is_redefense`, `completed_at`) VALUES
(26, 16, '2025-09-11', '10:00:00', '10:30:00', 5, 'scheduled', '2025-09-10 07:52:38', 'pre_oral', 'pending', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `document_submissions`
--

CREATE TABLE `document_submissions` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `document_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(46, 16, 7),
(44, 16, 9);

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
(1, 1, 'Defense Scheduled', 'Your thesis defense has been scheduled for August 30, 2025 at 8:00 AM.', 'success', 1, '2025-08-28 18:36:47', '2025-08-28 18:38:11'),
(2, 11, 'Defense Scheduled', 'Your thesis defense has been scheduled for August 30, 2025 at 8:00 AM.', 'success', 0, '2025-08-28 18:36:47', '2025-08-28 18:36:47'),
(3, 12, 'Defense Scheduled', 'Your thesis defense has been scheduled for August 30, 2025 at 8:00 AM.', 'success', 0, '2025-08-28 18:36:47', '2025-08-28 18:36:47'),
(4, 13, 'Defense Scheduled', 'Your thesis defense has been scheduled for August 30, 2025 at 8:00 AM.', 'success', 0, '2025-08-28 18:36:47', '2025-08-28 18:36:47'),
(5, 14, 'Defense Scheduled', 'Your thesis defense has been scheduled for August 30, 2025 at 8:00 AM.', 'success', 0, '2025-08-28 18:36:47', '2025-08-28 18:36:47'),
(6, 1, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41002.', 'success', 1, '2025-08-29 05:17:00', '2025-08-30 09:58:58'),
(7, 12, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41002.', 'success', 0, '2025-08-29 05:17:00', '2025-08-29 05:17:00'),
(8, 13, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41002.', 'success', 0, '2025-08-29 05:17:00', '2025-08-29 05:17:00'),
(9, 14, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41002.', 'success', 0, '2025-08-29 05:17:00', '2025-08-29 05:17:00'),
(10, 2, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41002.', 'success', 0, '2025-08-29 05:17:00', '2025-08-29 05:17:00'),
(11, 5, 'Panel Invitation Sent', 'Panel invitation has been sent to John Marvic Giray (girayjohnmarvic09@gmail.com).', 'info', 1, '2025-08-30 09:40:50', '2025-08-30 09:57:44'),
(12, 6, 'Panel Invitation Sent', 'Panel invitation has been sent to John Marvic Giray (girayjohnmarvic09@gmail.com).', 'info', 1, '2025-08-30 09:40:50', '2025-09-06 15:35:24'),
(13, 1, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 1, '2025-08-30 09:46:43', '2025-08-30 09:58:58'),
(14, 12, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 09:46:43', '2025-08-30 09:46:43'),
(15, 13, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 09:46:43', '2025-08-30 09:46:43'),
(16, 14, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 09:46:43', '2025-08-30 09:46:43'),
(17, 2, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 09:46:43', '2025-08-30 09:46:43'),
(18, 1, 'Proposal Submitted Successfully', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been submitted and is under review.', 'success', 1, '2025-08-30 10:19:56', '2025-08-30 10:28:32'),
(19, 5, 'New Proposal Submitted', 'John Marvic Giray submitted a new research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' for review.', 'info', 1, '2025-08-30 10:19:56', '2025-08-30 10:21:29'),
(20, 6, 'New Proposal Submitted', 'John Marvic Giray submitted a new research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' for review.', 'info', 1, '2025-08-30 10:19:56', '2025-09-06 15:35:24'),
(21, 1, 'Proposal Approved', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been approved!', 'success', 1, '2025-08-30 10:20:52', '2025-08-30 10:28:32'),
(22, 2, 'Proposal Approved', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been approved!', 'success', 0, '2025-08-30 10:20:52', '2025-08-30 10:20:52'),
(23, 12, 'Proposal Approved', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been approved!', 'success', 0, '2025-08-30 10:20:52', '2025-08-30 10:20:52'),
(24, 13, 'Proposal Approved', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been approved!', 'success', 0, '2025-08-30 10:20:52', '2025-08-30 10:20:52'),
(25, 14, 'Proposal Approved', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been approved!', 'success', 0, '2025-08-30 10:20:52', '2025-08-30 10:20:52'),
(26, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 1, '2025-08-30 10:26:41', '2025-08-30 10:28:32'),
(27, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
(28, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 1, '2025-08-30 10:26:41', '2025-08-30 10:28:24'),
(29, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 1, '2025-08-30 10:26:41', '2025-09-06 15:35:24'),
(30, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 1, '2025-08-30 10:26:41', '2025-09-05 07:10:51'),
(31, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
(32, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
(33, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
(34, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
(35, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
(36, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
(37, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
(38, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 1, '2025-08-30 10:34:16', '2025-08-30 17:18:46'),
(39, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 0, '2025-08-30 10:34:16', '2025-08-30 10:34:16'),
(40, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 1, '2025-08-30 10:34:16', '2025-08-30 10:46:00'),
(41, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 1, '2025-08-30 10:34:16', '2025-09-06 15:35:24'),
(42, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 1, '2025-08-30 10:34:16', '2025-09-05 07:10:51'),
(43, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 0, '2025-08-30 10:34:16', '2025-08-30 10:34:16'),
(44, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 0, '2025-08-30 10:34:16', '2025-08-30 10:34:16'),
(45, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 0, '2025-08-30 10:34:16', '2025-08-30 10:34:16'),
(46, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 0, '2025-08-30 10:34:16', '2025-08-30 10:34:16'),
(47, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 0, '2025-08-30 10:34:16', '2025-08-30 10:34:16'),
(48, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 0, '2025-08-30 10:34:16', '2025-08-30 10:34:16'),
(49, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 0, '2025-08-30 10:34:16', '2025-08-30 10:34:16'),
(50, 1, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSCS - Cluster 41001.', 'success', 1, '2025-08-30 18:47:33', '2025-08-30 18:51:14'),
(51, 12, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSCS - Cluster 41001.', 'success', 0, '2025-08-30 18:47:33', '2025-08-30 18:47:33'),
(52, 13, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSCS - Cluster 41001.', 'success', 0, '2025-08-30 18:47:33', '2025-08-30 18:47:33'),
(53, 14, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSCS - Cluster 41001.', 'success', 0, '2025-08-30 18:47:33', '2025-08-30 18:47:33'),
(54, 2, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSCS - Cluster 41001.', 'success', 0, '2025-08-30 18:47:33', '2025-08-30 18:47:33'),
(55, 1, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 1, '2025-08-30 18:47:43', '2025-08-30 18:51:14'),
(56, 12, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 18:47:43', '2025-08-30 18:47:43'),
(57, 13, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 18:47:43', '2025-08-30 18:47:43'),
(58, 14, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 18:47:43', '2025-08-30 18:47:43'),
(59, 2, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 18:47:43', '2025-08-30 18:47:43'),
(60, 1, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 1, '2025-08-30 19:25:15', '2025-08-30 19:25:26'),
(61, 12, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 19:25:15', '2025-08-30 19:25:15'),
(62, 13, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 19:25:15', '2025-08-30 19:25:15'),
(63, 14, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 19:25:15', '2025-08-30 19:25:15'),
(64, 2, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 19:25:15', '2025-08-30 19:25:15'),
(65, 1, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(66, 2, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(67, 5, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(68, 6, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 1, '2025-09-01 08:26:53', '2025-09-06 15:35:24'),
(69, 7, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 1, '2025-09-01 08:26:53', '2025-09-05 07:10:51'),
(70, 8, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(71, 9, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(72, 10, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(73, 11, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(74, 12, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(75, 13, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(76, 14, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(77, 1, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(78, 2, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(79, 5, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(80, 6, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 1, '2025-09-01 08:27:21', '2025-09-06 15:35:24'),
(81, 7, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 1, '2025-09-01 08:27:21', '2025-09-05 07:10:51'),
(82, 8, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(83, 9, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(84, 10, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(85, 11, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(86, 12, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(87, 13, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(88, 14, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(89, 1, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(90, 2, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(91, 5, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(92, 6, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 1, '2025-09-01 08:27:38', '2025-09-06 15:35:24'),
(93, 7, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 1, '2025-09-01 08:27:38', '2025-09-05 07:10:51'),
(94, 8, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(95, 9, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(96, 10, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(97, 11, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(98, 12, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(99, 13, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(100, 14, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(101, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(102, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(103, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(104, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 1, '2025-09-01 08:30:47', '2025-09-06 15:35:24'),
(105, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 1, '2025-09-01 08:30:47', '2025-09-05 07:10:51'),
(106, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(107, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(108, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(109, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(110, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(111, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(112, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(113, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(114, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(115, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(116, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 1, '2025-09-01 08:33:29', '2025-09-06 15:35:24'),
(117, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 1, '2025-09-01 08:33:29', '2025-09-05 07:10:51'),
(118, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(119, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(120, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(121, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(122, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(123, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(124, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(125, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(126, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(127, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(128, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 1, '2025-09-01 09:37:12', '2025-09-06 15:35:24'),
(129, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 1, '2025-09-01 09:37:12', '2025-09-05 07:10:51'),
(130, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(131, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(132, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(133, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(134, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(135, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(136, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(137, 7, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 1, '2025-09-01 10:28:06', '2025-09-05 07:10:51'),
(138, 9, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-01 10:28:06', '2025-09-01 10:28:06'),
(139, 7, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 1, '2025-09-01 10:28:06', '2025-09-05 07:10:51'),
(140, 9, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-01 10:28:06', '2025-09-01 10:28:06'),
(141, 7, 'Proposal Submitted Successfully', 'Your research proposal \'The Impact of Technology Integration on Student Engagement in Secondary Classrooms\' has been submitted and is under review.', 'success', 1, '2025-09-01 10:48:45', '2025-09-05 07:10:51'),
(142, 5, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'The Impact of Technology Integration on Student Engagement in Secondary Classrooms\' for review.', 'info', 0, '2025-09-01 10:48:45', '2025-09-01 10:48:45'),
(143, 6, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'The Impact of Technology Integration on Student Engagement in Secondary Classrooms\' for review.', 'info', 1, '2025-09-01 10:48:45', '2025-09-06 15:35:24'),
(144, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(145, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(146, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(147, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 1, '2025-09-01 11:18:17', '2025-09-06 15:35:24'),
(148, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 1, '2025-09-01 11:18:17', '2025-09-05 07:10:51'),
(149, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(150, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(151, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(152, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(153, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(154, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(155, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(156, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(157, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(158, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(159, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 1, '2025-09-01 11:19:34', '2025-09-06 15:35:24'),
(160, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 1, '2025-09-01 11:19:34', '2025-09-05 07:10:51'),
(161, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(162, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(163, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(164, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(165, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(166, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(167, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(168, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(169, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(170, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(171, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 1, '2025-09-01 11:22:45', '2025-09-06 15:35:24'),
(172, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 1, '2025-09-01 11:22:45', '2025-09-05 07:10:51'),
(173, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(174, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(175, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(176, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(177, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(178, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(179, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(180, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(181, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(182, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(183, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 1, '2025-09-01 11:23:11', '2025-09-06 15:35:24'),
(184, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 1, '2025-09-01 11:23:11', '2025-09-05 07:10:51'),
(185, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(186, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(187, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(188, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(189, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(190, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(191, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(192, 7, 'Proposal Submitted Successfully', 'Your research proposal \'Social Media and Mental Health\' has been submitted and is under review.', 'success', 1, '2025-09-01 11:55:14', '2025-09-05 07:10:51'),
(193, 5, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Social Media and Mental Health\' for review.', 'info', 0, '2025-09-01 11:55:14', '2025-09-01 11:55:14'),
(194, 6, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Social Media and Mental Health\' for review.', 'info', 1, '2025-09-01 11:55:14', '2025-09-06 15:35:24'),
(195, 7, 'Proposal Submitted Successfully', 'Your research proposal \'Social Media and Mental Health\' has been submitted and is under review.', 'success', 1, '2025-09-01 11:55:14', '2025-09-05 07:10:51'),
(196, 5, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Social Media and Mental Health\' for review.', 'info', 0, '2025-09-01 11:55:14', '2025-09-01 11:55:14'),
(197, 6, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Social Media and Mental Health\' for review.', 'info', 1, '2025-09-01 11:55:14', '2025-09-06 15:35:24'),
(198, 7, 'Proposal Submitted Successfully', 'Your research proposal \'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration\' has been submitted and is under review.', 'success', 1, '2025-09-01 15:21:40', '2025-09-05 07:10:51'),
(199, 5, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration\' for review.', 'info', 0, '2025-09-01 15:21:40', '2025-09-01 15:21:40'),
(200, 6, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration\' for review.', 'info', 1, '2025-09-01 15:21:40', '2025-09-06 15:35:24'),
(201, 1, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(202, 12, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(203, 13, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(204, 14, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(205, 2, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(206, 7, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 1, '2025-09-01 15:48:08', '2025-09-05 07:10:51'),
(207, 9, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(208, 12, 'Proposal Submitted Successfully', 'Your research proposal \'The Impact of Social Media on Adolescent Mental Health: A Case Study Approach\' has been submitted and is under review.', 'success', 0, '2025-09-01 15:49:14', '2025-09-01 15:49:14'),
(209, 5, 'New Proposal Submitted', 'Angelito Pampanga submitted a new research proposal \'The Impact of Social Media on Adolescent Mental Health: A Case Study Approach\' for review.', 'info', 0, '2025-09-01 15:49:14', '2025-09-01 15:49:14'),
(210, 6, 'New Proposal Submitted', 'Angelito Pampanga submitted a new research proposal \'The Impact of Social Media on Adolescent Mental Health: A Case Study Approach\' for review.', 'info', 1, '2025-09-01 15:49:14', '2025-09-06 15:35:24'),
(211, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(212, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(213, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(214, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 1, '2025-09-01 16:06:24', '2025-09-01 16:19:02'),
(215, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 1, '2025-09-01 16:06:24', '2025-09-05 07:10:51'),
(216, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(217, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(218, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(219, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(220, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(221, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(222, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(223, 7, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 1, '2025-09-01 16:10:45', '2025-09-05 07:10:51'),
(224, 9, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 16:10:45', '2025-09-01 16:10:45'),
(225, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(226, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(227, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(228, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 1, '2025-09-01 16:26:27', '2025-09-06 15:35:24'),
(229, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 1, '2025-09-01 16:26:27', '2025-09-05 07:10:51'),
(230, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(231, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(232, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(233, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(234, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(235, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(236, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(237, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 0, '2025-09-01 16:37:40', '2025-09-01 16:37:40'),
(238, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 0, '2025-09-01 16:37:40', '2025-09-01 16:37:40'),
(239, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 0, '2025-09-01 16:37:40', '2025-09-01 16:37:40'),
(240, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 1, '2025-09-01 16:37:40', '2025-09-06 15:35:24'),
(241, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 1, '2025-09-01 16:37:40', '2025-09-05 07:10:51'),
(242, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 0, '2025-09-01 16:37:40', '2025-09-01 16:37:40'),
(243, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 0, '2025-09-01 16:37:40', '2025-09-01 16:37:40'),
(244, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 0, '2025-09-01 16:37:40', '2025-09-01 16:37:40'),
(245, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 0, '2025-09-01 16:37:40', '2025-09-01 16:37:40'),
(246, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 0, '2025-09-01 16:37:40', '2025-09-01 16:37:40'),
(247, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 0, '2025-09-01 16:37:40', '2025-09-01 16:37:40'),
(248, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:23', 'info', 0, '2025-09-01 16:37:40', '2025-09-01 16:37:40'),
(249, 1, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 16:52:34', '2025-09-01 16:52:34'),
(250, 2, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 16:52:34', '2025-09-01 16:52:34'),
(251, 5, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 16:52:34', '2025-09-01 16:52:34'),
(252, 6, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 1, '2025-09-01 16:52:34', '2025-09-01 16:57:46'),
(253, 7, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 1, '2025-09-01 16:52:34', '2025-09-05 07:10:51'),
(254, 8, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 16:52:34', '2025-09-01 16:52:34'),
(255, 9, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 16:52:34', '2025-09-01 16:52:34'),
(256, 10, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 16:52:34', '2025-09-01 16:52:34'),
(257, 11, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 16:52:34', '2025-09-01 16:52:34'),
(258, 12, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 16:52:34', '2025-09-01 16:52:34'),
(259, 13, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 16:52:34', '2025-09-01 16:52:34'),
(260, 14, 'New Panel Member Added', 'A new panel member has been added: John Cruz (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 16:52:34', '2025-09-01 16:52:34'),
(261, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 0, '2025-09-01 17:28:06', '2025-09-01 17:28:06'),
(262, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 0, '2025-09-01 17:28:06', '2025-09-01 17:28:06'),
(263, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 0, '2025-09-01 17:28:06', '2025-09-01 17:28:06'),
(264, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 1, '2025-09-01 17:28:06', '2025-09-06 15:35:24'),
(265, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 1, '2025-09-01 17:28:06', '2025-09-05 07:10:51'),
(266, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 0, '2025-09-01 17:28:06', '2025-09-01 17:28:06'),
(267, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 0, '2025-09-01 17:28:06', '2025-09-01 17:28:06'),
(268, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 0, '2025-09-01 17:28:06', '2025-09-01 17:28:06'),
(269, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 0, '2025-09-01 17:28:06', '2025-09-01 17:28:06'),
(270, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 0, '2025-09-01 17:28:06', '2025-09-01 17:28:06'),
(271, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 0, '2025-09-01 17:28:06', '2025-09-01 17:28:06'),
(272, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 01:27', 'info', 0, '2025-09-01 17:28:06', '2025-09-01 17:28:06'),
(273, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:39:46', '2025-09-01 17:39:46'),
(274, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:39:46', '2025-09-01 17:39:46'),
(275, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:39:46', '2025-09-01 17:39:46'),
(276, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 1, '2025-09-01 17:39:46', '2025-09-06 15:35:24'),
(277, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 1, '2025-09-01 17:39:46', '2025-09-05 07:10:51'),
(278, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:39:46', '2025-09-01 17:39:46'),
(279, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:39:46', '2025-09-01 17:39:46'),
(280, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:39:46', '2025-09-01 17:39:46'),
(281, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:39:46', '2025-09-01 17:39:46'),
(282, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:39:46', '2025-09-01 17:39:46'),
(283, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:39:46', '2025-09-01 17:39:46'),
(284, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:39:46', '2025-09-01 17:39:46'),
(285, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:53:20', '2025-09-01 17:53:20'),
(286, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:53:20', '2025-09-01 17:53:20'),
(287, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:53:20', '2025-09-01 17:53:20'),
(288, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 1, '2025-09-01 17:53:20', '2025-09-06 15:35:24'),
(289, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 1, '2025-09-01 17:53:20', '2025-09-05 07:10:51'),
(290, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:53:20', '2025-09-01 17:53:20'),
(291, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:53:20', '2025-09-01 17:53:20'),
(292, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:53:20', '2025-09-01 17:53:20'),
(293, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:53:20', '2025-09-01 17:53:20'),
(294, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:53:20', '2025-09-01 17:53:20'),
(295, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:53:20', '2025-09-01 17:53:20');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`, `updated_at`) VALUES
(296, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:53:20', '2025-09-01 17:53:20'),
(297, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:57:01', '2025-09-01 17:57:01'),
(298, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:57:01', '2025-09-01 17:57:01'),
(299, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:57:01', '2025-09-01 17:57:01'),
(300, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 1, '2025-09-01 17:57:01', '2025-09-06 15:35:24'),
(301, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 1, '2025-09-01 17:57:01', '2025-09-05 07:10:51'),
(302, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:57:01', '2025-09-01 17:57:01'),
(303, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:57:01', '2025-09-01 17:57:01'),
(304, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:57:01', '2025-09-01 17:57:01'),
(305, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:57:01', '2025-09-01 17:57:01'),
(306, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:57:01', '2025-09-01 17:57:01'),
(307, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:57:01', '2025-09-01 17:57:01'),
(308, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:00', 'info', 0, '2025-09-01 17:57:01', '2025-09-01 17:57:01'),
(309, 1, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 0, '2025-09-01 18:16:56', '2025-09-01 18:16:56'),
(310, 2, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 0, '2025-09-01 18:16:56', '2025-09-01 18:16:56'),
(311, 5, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 0, '2025-09-01 18:16:56', '2025-09-01 18:16:56'),
(312, 6, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 1, '2025-09-01 18:16:56', '2025-09-06 15:35:24'),
(313, 7, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 1, '2025-09-01 18:16:56', '2025-09-05 07:10:51'),
(314, 8, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 0, '2025-09-01 18:16:56', '2025-09-01 18:16:56'),
(315, 9, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 0, '2025-09-01 18:16:56', '2025-09-01 18:16:56'),
(316, 10, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 0, '2025-09-01 18:16:56', '2025-09-01 18:16:56'),
(317, 11, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 0, '2025-09-01 18:16:56', '2025-09-01 18:16:56'),
(318, 12, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 0, '2025-09-01 18:16:56', '2025-09-01 18:16:56'),
(319, 13, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 0, '2025-09-01 18:16:56', '2025-09-01 18:16:56'),
(320, 14, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-03 at 12:00:00', 'info', 0, '2025-09-01 18:16:56', '2025-09-01 18:16:56'),
(321, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 08:54:46', '2025-09-02 08:54:46'),
(322, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 08:54:46', '2025-09-02 08:54:46'),
(323, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 08:54:46', '2025-09-02 08:54:46'),
(324, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 1, '2025-09-02 08:54:46', '2025-09-06 15:35:24'),
(325, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 1, '2025-09-02 08:54:46', '2025-09-05 07:10:51'),
(326, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 08:54:46', '2025-09-02 08:54:46'),
(327, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 08:54:46', '2025-09-02 08:54:46'),
(328, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 08:54:46', '2025-09-02 08:54:46'),
(329, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 08:54:46', '2025-09-02 08:54:46'),
(330, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 08:54:46', '2025-09-02 08:54:46'),
(331, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 08:54:46', '2025-09-02 08:54:46'),
(332, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 08:54:46', '2025-09-02 08:54:46'),
(333, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 09:37:23', '2025-09-02 09:37:23'),
(334, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 09:37:23', '2025-09-02 09:37:23'),
(335, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 09:37:23', '2025-09-02 09:37:23'),
(336, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 1, '2025-09-02 09:37:23', '2025-09-06 15:35:24'),
(337, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 1, '2025-09-02 09:37:23', '2025-09-05 07:10:51'),
(338, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 09:37:23', '2025-09-02 09:37:23'),
(339, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 09:37:23', '2025-09-02 09:37:23'),
(340, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 09:37:23', '2025-09-02 09:37:23'),
(341, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 09:37:23', '2025-09-02 09:37:23'),
(342, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 09:37:23', '2025-09-02 09:37:23'),
(343, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 09:37:23', '2025-09-02 09:37:23'),
(344, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:30', 'info', 0, '2025-09-02 09:37:23', '2025-09-02 09:37:23'),
(345, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 0, '2025-09-02 10:10:30', '2025-09-02 10:10:30'),
(346, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 0, '2025-09-02 10:10:30', '2025-09-02 10:10:30'),
(347, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 0, '2025-09-02 10:10:30', '2025-09-02 10:10:30'),
(348, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 1, '2025-09-02 10:10:30', '2025-09-06 15:35:24'),
(349, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 1, '2025-09-02 10:10:30', '2025-09-05 07:10:51'),
(350, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 0, '2025-09-02 10:10:30', '2025-09-02 10:10:30'),
(351, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 0, '2025-09-02 10:10:30', '2025-09-02 10:10:30'),
(352, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 0, '2025-09-02 10:10:30', '2025-09-02 10:10:30'),
(353, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 0, '2025-09-02 10:10:30', '2025-09-02 10:10:30'),
(354, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 0, '2025-09-02 10:10:30', '2025-09-02 10:10:30'),
(355, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 0, '2025-09-02 10:10:30', '2025-09-02 10:10:30'),
(356, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:00', 'info', 0, '2025-09-02 10:10:30', '2025-09-02 10:10:30'),
(357, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 0, '2025-09-02 10:13:23', '2025-09-02 10:13:23'),
(358, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 0, '2025-09-02 10:13:23', '2025-09-02 10:13:23'),
(359, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 0, '2025-09-02 10:13:23', '2025-09-02 10:13:23'),
(360, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 1, '2025-09-02 10:13:23', '2025-09-06 15:35:24'),
(361, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 1, '2025-09-02 10:13:23', '2025-09-05 07:10:51'),
(362, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 0, '2025-09-02 10:13:23', '2025-09-02 10:13:23'),
(363, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 0, '2025-09-02 10:13:23', '2025-09-02 10:13:23'),
(364, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 0, '2025-09-02 10:13:23', '2025-09-02 10:13:23'),
(365, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 0, '2025-09-02 10:13:23', '2025-09-02 10:13:23'),
(366, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 0, '2025-09-02 10:13:23', '2025-09-02 10:13:23'),
(367, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 0, '2025-09-02 10:13:23', '2025-09-02 10:13:23'),
(368, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 12:30', 'info', 0, '2025-09-02 10:13:23', '2025-09-02 10:13:23'),
(369, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:17:43', '2025-09-02 10:17:43'),
(370, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:17:43', '2025-09-02 10:17:43'),
(371, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:17:43', '2025-09-02 10:17:43'),
(372, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 1, '2025-09-02 10:17:43', '2025-09-06 15:35:24'),
(373, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 1, '2025-09-02 10:17:43', '2025-09-05 07:10:51'),
(374, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:17:43', '2025-09-02 10:17:43'),
(375, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:17:43', '2025-09-02 10:17:43'),
(376, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:17:43', '2025-09-02 10:17:43'),
(377, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:17:43', '2025-09-02 10:17:43'),
(378, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:17:43', '2025-09-02 10:17:43'),
(379, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:17:43', '2025-09-02 10:17:43'),
(380, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:17:43', '2025-09-02 10:17:43'),
(381, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 0, '2025-09-02 10:18:46', '2025-09-02 10:18:46'),
(382, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 0, '2025-09-02 10:18:46', '2025-09-02 10:18:46'),
(383, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 0, '2025-09-02 10:18:46', '2025-09-02 10:18:46'),
(384, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 1, '2025-09-02 10:18:46', '2025-09-06 15:35:24'),
(385, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 1, '2025-09-02 10:18:46', '2025-09-05 07:10:51'),
(386, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 0, '2025-09-02 10:18:46', '2025-09-02 10:18:46'),
(387, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 0, '2025-09-02 10:18:46', '2025-09-02 10:18:46'),
(388, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 0, '2025-09-02 10:18:46', '2025-09-02 10:18:46'),
(389, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 0, '2025-09-02 10:18:46', '2025-09-02 10:18:46'),
(390, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 0, '2025-09-02 10:18:46', '2025-09-02 10:18:46'),
(391, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 0, '2025-09-02 10:18:46', '2025-09-02 10:18:46'),
(392, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 16:00', 'info', 0, '2025-09-02 10:18:46', '2025-09-02 10:18:46'),
(393, 1, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 0, '2025-09-02 10:21:08', '2025-09-02 10:21:08'),
(394, 2, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 0, '2025-09-02 10:21:08', '2025-09-02 10:21:08'),
(395, 5, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 0, '2025-09-02 10:21:08', '2025-09-02 10:21:08'),
(396, 6, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 1, '2025-09-02 10:21:08', '2025-09-06 15:35:24'),
(397, 7, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 1, '2025-09-02 10:21:08', '2025-09-05 07:10:51'),
(398, 8, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 0, '2025-09-02 10:21:08', '2025-09-02 10:21:08'),
(399, 9, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 0, '2025-09-02 10:21:08', '2025-09-02 10:21:08'),
(400, 10, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 0, '2025-09-02 10:21:08', '2025-09-02 10:21:08'),
(401, 11, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 0, '2025-09-02 10:21:08', '2025-09-02 10:21:08'),
(402, 12, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 0, '2025-09-02 10:21:08', '2025-09-02 10:21:08'),
(403, 13, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 0, '2025-09-02 10:21:08', '2025-09-02 10:21:08'),
(404, 14, 'Defense Schedule Updated', 'Defense schedule has been updated for group: GRP 1 on 2025-09-02 at 15:30', 'info', 0, '2025-09-02 10:21:08', '2025-09-02 10:21:08'),
(405, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 0, '2025-09-02 10:23:12', '2025-09-02 10:23:12'),
(406, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 0, '2025-09-02 10:23:12', '2025-09-02 10:23:12'),
(407, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 0, '2025-09-02 10:23:12', '2025-09-02 10:23:12'),
(408, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 1, '2025-09-02 10:23:12', '2025-09-06 15:35:24'),
(409, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 1, '2025-09-02 10:23:12', '2025-09-05 07:10:51'),
(410, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 0, '2025-09-02 10:23:12', '2025-09-02 10:23:12'),
(411, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 0, '2025-09-02 10:23:12', '2025-09-02 10:23:12'),
(412, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 0, '2025-09-02 10:23:12', '2025-09-02 10:23:12'),
(413, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 0, '2025-09-02 10:23:12', '2025-09-02 10:23:12'),
(414, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 0, '2025-09-02 10:23:12', '2025-09-02 10:23:12'),
(415, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 0, '2025-09-02 10:23:12', '2025-09-02 10:23:12'),
(416, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 11:30', 'info', 0, '2025-09-02 10:23:12', '2025-09-02 10:23:12'),
(417, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 0, '2025-09-02 10:28:22', '2025-09-02 10:28:22'),
(418, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 0, '2025-09-02 10:28:22', '2025-09-02 10:28:22'),
(419, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 0, '2025-09-02 10:28:22', '2025-09-02 10:28:22'),
(420, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 1, '2025-09-02 10:28:22', '2025-09-06 15:35:24'),
(421, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 1, '2025-09-02 10:28:22', '2025-09-05 07:10:51'),
(422, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 0, '2025-09-02 10:28:22', '2025-09-02 10:28:22'),
(423, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 0, '2025-09-02 10:28:22', '2025-09-02 10:28:22'),
(424, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 0, '2025-09-02 10:28:22', '2025-09-02 10:28:22'),
(425, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 0, '2025-09-02 10:28:22', '2025-09-02 10:28:22'),
(426, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 0, '2025-09-02 10:28:22', '2025-09-02 10:28:22'),
(427, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 0, '2025-09-02 10:28:22', '2025-09-02 10:28:22'),
(428, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-03 at 16:30', 'info', 0, '2025-09-02 10:28:22', '2025-09-02 10:28:22'),
(429, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:29:03', '2025-09-02 10:29:03'),
(430, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:29:03', '2025-09-02 10:29:03'),
(431, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:29:03', '2025-09-02 10:29:03'),
(432, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 1, '2025-09-02 10:29:03', '2025-09-06 15:35:24'),
(433, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 1, '2025-09-02 10:29:03', '2025-09-05 07:10:51'),
(434, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:29:03', '2025-09-02 10:29:03'),
(435, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:29:03', '2025-09-02 10:29:03'),
(436, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:29:03', '2025-09-02 10:29:03'),
(437, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:29:03', '2025-09-02 10:29:03'),
(438, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:29:03', '2025-09-02 10:29:03'),
(439, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:29:03', '2025-09-02 10:29:03'),
(440, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-02 at 13:00', 'info', 0, '2025-09-02 10:29:03', '2025-09-02 10:29:03'),
(441, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 0, '2025-09-02 10:29:45', '2025-09-02 10:29:45'),
(442, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 0, '2025-09-02 10:29:45', '2025-09-02 10:29:45'),
(443, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 0, '2025-09-02 10:29:45', '2025-09-02 10:29:45'),
(444, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 1, '2025-09-02 10:29:45', '2025-09-06 15:35:24'),
(445, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 1, '2025-09-02 10:29:45', '2025-09-05 07:10:51'),
(446, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 0, '2025-09-02 10:29:45', '2025-09-02 10:29:45'),
(447, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 0, '2025-09-02 10:29:45', '2025-09-02 10:29:45'),
(448, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 0, '2025-09-02 10:29:45', '2025-09-02 10:29:45'),
(449, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 0, '2025-09-02 10:29:45', '2025-09-02 10:29:45'),
(450, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 0, '2025-09-02 10:29:45', '2025-09-02 10:29:45'),
(451, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 0, '2025-09-02 10:29:45', '2025-09-02 10:29:45'),
(452, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-03 at 15:30', 'info', 0, '2025-09-02 10:29:45', '2025-09-02 10:29:45'),
(453, 1, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(454, 12, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(455, 13, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(456, 14, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(457, 2, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(458, 7, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(459, 9, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(460, 1, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(461, 12, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(462, 13, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(463, 14, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(464, 2, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(465, 7, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(466, 9, 'Adviser Assigned', 'Prof. Prof. James Wilson has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-08 10:11:10', '2025-09-08 10:11:10'),
(467, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(468, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(469, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(470, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 1, '2025-09-08 10:15:03', '2025-09-08 14:41:28'),
(471, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(472, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(473, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(474, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(475, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(476, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(477, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(478, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:15:03', '2025-09-08 10:15:03'),
(479, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(480, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(481, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(482, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 1, '2025-09-08 10:15:22', '2025-09-08 14:41:28'),
(483, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(484, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(485, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(486, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(487, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(488, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(489, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(490, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 10:00', 'info', 0, '2025-09-08 10:15:22', '2025-09-08 10:15:22'),
(491, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(492, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(493, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(494, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 1, '2025-09-08 10:15:52', '2025-09-08 14:41:28'),
(495, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(496, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(497, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(498, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(499, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(500, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(501, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(502, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-26 at 09:00', 'info', 0, '2025-09-08 10:15:52', '2025-09-08 10:15:52'),
(503, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(504, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(505, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(506, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 1, '2025-09-08 10:16:39', '2025-09-08 14:41:28'),
(507, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(508, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(509, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(510, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(511, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(512, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(513, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(514, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:30', 'info', 0, '2025-09-08 10:16:39', '2025-09-08 10:16:39'),
(515, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(516, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(517, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(518, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 1, '2025-09-08 10:21:32', '2025-09-08 14:41:28'),
(519, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(520, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(521, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(522, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(523, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(524, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(525, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(526, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:32', '2025-09-08 10:21:32'),
(527, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(528, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(529, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(530, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 1, '2025-09-08 10:21:58', '2025-09-08 14:41:28'),
(531, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(532, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(533, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(534, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(535, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(536, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(537, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(538, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 2 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:21:58', '2025-09-08 10:21:58'),
(539, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(540, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(541, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(542, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 1, '2025-09-08 10:22:33', '2025-09-08 14:41:28'),
(543, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(544, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(545, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(546, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(547, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(548, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(549, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(550, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:22:33', '2025-09-08 10:22:33'),
(551, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(552, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(553, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(554, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 1, '2025-09-08 10:25:15', '2025-09-08 14:41:28'),
(555, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(556, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(557, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(558, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(559, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(560, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(561, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(562, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:25:15', '2025-09-08 10:25:15'),
(563, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(564, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(565, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(566, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 1, '2025-09-08 10:25:33', '2025-09-08 14:41:28'),
(567, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(568, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(569, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(570, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(571, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(572, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(573, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(574, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 10:00', 'info', 0, '2025-09-08 10:25:33', '2025-09-08 10:25:33'),
(575, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(576, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(577, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(578, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 1, '2025-09-08 10:29:21', '2025-09-08 14:41:28'),
(579, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(580, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(581, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(582, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(583, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(584, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(585, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(586, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:29:21', '2025-09-08 10:29:21'),
(587, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(588, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(589, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(590, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 1, '2025-09-08 10:29:39', '2025-09-08 14:41:28'),
(591, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(592, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(593, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(594, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(595, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(596, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(597, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(598, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-10 at 09:00', 'info', 0, '2025-09-08 10:29:39', '2025-09-08 10:29:39'),
(599, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00'),
(600, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00'),
(601, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00'),
(602, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 1, '2025-09-08 10:34:00', '2025-09-08 14:41:28'),
(603, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00'),
(604, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00'),
(605, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00');
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `created_at`, `updated_at`) VALUES
(606, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00'),
(607, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00'),
(608, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00'),
(609, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00'),
(610, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:00', 'info', 0, '2025-09-08 10:34:00', '2025-09-08 10:34:00'),
(611, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(612, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(613, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(614, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 1, '2025-09-08 10:34:17', '2025-09-08 14:41:28'),
(615, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(616, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(617, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(618, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(619, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(620, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(621, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(622, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:34:17', '2025-09-08 10:34:17'),
(623, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(624, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(625, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(626, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 1, '2025-09-08 10:37:44', '2025-09-08 14:41:28'),
(627, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(628, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(629, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(630, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(631, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(632, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(633, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(634, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 09:30', 'info', 0, '2025-09-08 10:37:44', '2025-09-08 10:37:44'),
(635, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(636, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(637, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(638, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 1, '2025-09-08 10:38:02', '2025-09-08 14:41:28'),
(639, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(640, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(641, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(642, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(643, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(644, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(645, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(646, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 10:38:02', '2025-09-08 10:38:02'),
(647, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(648, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(649, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(650, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(651, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(652, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(653, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(654, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(655, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(656, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(657, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(658, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-09 at 09:00', 'info', 0, '2025-09-08 15:40:02', '2025-09-08 15:40:02'),
(659, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(660, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(661, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(662, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(663, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(664, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(665, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(666, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(667, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(668, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(669, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38'),
(670, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-11 at 10:00', 'info', 0, '2025-09-10 07:52:38', '2025-09-10 07:52:38');

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
  `payment_type` enum('research_forum','pre_oral_defense','final_defense') NOT NULL,
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
(83, 7, 'research_forum', 100.00, NULL, 'approved', 1, NULL, '2025-09-08 19:24:38', '[\"../assets/uploads/receipts/1757359478_0_IPO.jpg\"]', '[{\"status\":\"approved\",\"feedback\":\"\",\"updated_at\":\"2025-09-09T03:46:42+08:00\"}]'),
(84, 9, 'research_forum', 100.00, NULL, 'approved', 1, NULL, '2025-09-08 19:24:38', '[\"../assets/uploads/receipts/1757359478_0_IPO.jpg\"]', NULL),
(85, 7, 'pre_oral_defense', 100.00, NULL, 'approved', 1, NULL, '2025-09-08 19:31:12', '[\"../assets/uploads/receipts/1757359872_0_microservice.jpg\"]', '[{\"status\":\"approved\",\"feedback\":\"\",\"updated_at\":\"2025-09-09T03:55:05+08:00\"}]'),
(86, 9, 'pre_oral_defense', 100.00, NULL, 'approved', 1, NULL, '2025-09-08 19:31:12', '[\"../assets/uploads/receipts/1757359872_0_microservice.jpg\"]', '[{\"status\":\"approved\",\"feedback\":\"\",\"updated_at\":\"2025-09-09T03:55:05+08:00\"}]');

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
(18, 16, 'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration', 'This study aims to design and develop a campus navigation system that uses QR codes placed at key locations inside school buildings. The system will be integrated with a cloud database to provide real-time directions and facility information accessible through a mobile application. The project addresses the challenges of new students and visitors in locating rooms, offices, and facilities within the campus, thereby improving efficiency and user experience.', '../assets/uploads/proposals/Cap101-reviewer-Prelim.pdf', '2025-09-01 15:21:40', 'Completed', '2025-09-10 07:52:12', 0),
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
(1, 'Room 101', 'Main Building', 40),
(2, 'Room 102', 'Main Building', 35),
(3, 'Room 201', 'Science Building', 50),
(4, 'Room 202', 'Science Building', 45),
(5, 'Room 301', 'Engineering Building', 60),
(6, 'Room 302', 'Engineering Building', 55),
(7, 'Auditorium', 'Main Building', 200),
(8, 'Library Conference Room', 'Library Building', 25),
(9, 'IT Lab 1', 'Tech Building', 40),
(10, 'IT Lab 2', 'Tech Building', 40);

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
(21, 9, '22014823', 'Kang Haerin', 'BSIT', '41006', 2, '2025-2026', '2025-09-01 10:12:00', '2025-09-08 10:11:10');

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
-- Indexes for table `document_submissions`
--
ALTER TABLE `document_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `document_submissions`
--
ALTER TABLE `document_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=671;

--
-- AUTO_INCREMENT for table `panel_members`
--
ALTER TABLE `panel_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `required_documents`
--
ALTER TABLE `required_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `submission_timelines`
--
ALTER TABLE `submission_timelines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `timeline_milestones`
--
ALTER TABLE `timeline_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_tbl`
--
ALTER TABLE `user_tbl`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clusters`
--
ALTER TABLE `clusters`
  ADD CONSTRAINT `clusters_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`),
  ADD CONSTRAINT `fk_clusters_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  ADD CONSTRAINT `fk_parent_defense` FOREIGN KEY (`parent_defense_id`) REFERENCES `defense_schedules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `document_submissions`
--
ALTER TABLE `document_submissions`
  ADD CONSTRAINT `document_submissions_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `fk_groups_cluster` FOREIGN KEY (`cluster_id`) REFERENCES `clusters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `proposals`
--
ALTER TABLE `proposals`
  ADD CONSTRAINT `proposals_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `fk_student_profiles_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE SET NULL;

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
