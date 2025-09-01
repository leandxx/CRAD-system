-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Sep 01, 2025 at 06:29 PM
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
(172, 'BSCS', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(173, 'BSCS', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(174, 'BSCS', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(175, 'BSCS', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(176, 'BSCS', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(177, 'BSCS', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(178, 'BSCS', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(179, 'BSCS', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(180, 'BSCS', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(181, 'BSCS', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(182, 'BSBA', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(183, 'BSBA', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(184, 'BSBA', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(185, 'BSBA', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(186, 'BSBA', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(187, 'BSBA', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(188, 'BSBA', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(189, 'BSBA', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(190, 'BSBA', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(191, 'BSBA', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(192, 'BSED', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(193, 'BSED', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(194, 'BSED', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(195, 'BSED', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(196, 'BSED', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(197, 'BSED', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(198, 'BSED', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(199, 'BSED', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(200, 'BSED', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(201, 'BSED', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(202, 'BSIT', '41001', '2025-2026', 7, '2025-09-02', 2, 25, 'assigned', '2025-09-01 16:10:45'),
(203, 'BSIT', '41002', '2025-2026', NULL, NULL, 5, 25, 'unassigned', '2025-09-01 15:51:25'),
(204, 'BSIT', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(205, 'BSIT', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(206, 'BSIT', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(207, 'BSIT', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(208, 'BSIT', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(209, 'BSIT', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(210, 'BSIT', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(211, 'BSIT', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(212, 'BSCRIM', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(213, 'BSCRIM', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(214, 'BSCRIM', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(215, 'BSCRIM', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(216, 'BSCRIM', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(217, 'BSCRIM', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(218, 'BSCRIM', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(219, 'BSCRIM', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(220, 'BSCRIM', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10'),
(221, 'BSCRIM', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-01 15:51:10');

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
(14, 7, 8, 'member', '2025-08-28 18:36:47'),
(15, 7, 9, 'member', '2025-08-28 18:36:47'),
(45, 0, 8, '', '2025-09-01 16:06:24'),
(46, 0, 11, 'member', '2025-09-01 16:06:24'),
(47, 0, 10, 'member', '2025-09-01 16:06:24'),
(48, 2, 8, '', '2025-09-01 16:26:27'),
(49, 2, 11, 'member', '2025-09-01 16:26:27'),
(50, 2, 10, 'member', '2025-09-01 16:26:27');

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
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `defense_schedules`
--

INSERT INTO `defense_schedules` (`id`, `group_id`, `defense_date`, `start_time`, `end_time`, `room_id`, `status`, `created_at`) VALUES
(2, 16, '2025-09-02', '00:25:00', '00:55:00', 5, 'scheduled', '2025-09-01 16:26:27');

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
(15, 'GRP 2', 'BSIT', 203, '5B97C1', '2025-08-29 05:08:49'),
(16, 'GRP 1', 'BSIT', 202, 'C7E820', '2025-09-01 10:10:40');

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
(45, 16, 7),
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
(12, 6, 'Panel Invitation Sent', 'Panel invitation has been sent to John Marvic Giray (girayjohnmarvic09@gmail.com).', 'info', 0, '2025-08-30 09:40:50', '2025-08-30 09:40:50'),
(13, 1, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 1, '2025-08-30 09:46:43', '2025-08-30 09:58:58'),
(14, 12, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 09:46:43', '2025-08-30 09:46:43'),
(15, 13, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 09:46:43', '2025-08-30 09:46:43'),
(16, 14, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 09:46:43', '2025-08-30 09:46:43'),
(17, 2, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-08-30 09:46:43', '2025-08-30 09:46:43'),
(18, 1, 'Proposal Submitted Successfully', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been submitted and is under review.', 'success', 1, '2025-08-30 10:19:56', '2025-08-30 10:28:32'),
(19, 5, 'New Proposal Submitted', 'John Marvic Giray submitted a new research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' for review.', 'info', 1, '2025-08-30 10:19:56', '2025-08-30 10:21:29'),
(20, 6, 'New Proposal Submitted', 'John Marvic Giray submitted a new research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' for review.', 'info', 0, '2025-08-30 10:19:56', '2025-08-30 10:19:56'),
(21, 1, 'Proposal Approved', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been approved!', 'success', 1, '2025-08-30 10:20:52', '2025-08-30 10:28:32'),
(22, 2, 'Proposal Approved', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been approved!', 'success', 0, '2025-08-30 10:20:52', '2025-08-30 10:20:52'),
(23, 12, 'Proposal Approved', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been approved!', 'success', 0, '2025-08-30 10:20:52', '2025-08-30 10:20:52'),
(24, 13, 'Proposal Approved', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been approved!', 'success', 0, '2025-08-30 10:20:52', '2025-08-30 10:20:52'),
(25, 14, 'Proposal Approved', 'Your research proposal \'Intelligent Progressive Research Submission and Tracking System Using OpenAi\' has been approved!', 'success', 0, '2025-08-30 10:20:52', '2025-08-30 10:20:52'),
(26, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 1, '2025-08-30 10:26:41', '2025-08-30 10:28:32'),
(27, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
(28, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 1, '2025-08-30 10:26:41', '2025-08-30 10:28:24'),
(29, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
(30, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-03-31 at 09:00', 'info', 0, '2025-08-30 10:26:41', '2025-08-30 10:26:41'),
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
(41, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 0, '2025-08-30 10:34:16', '2025-08-30 10:34:16'),
(42, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-08-31 at 09:00', 'info', 0, '2025-08-30 10:34:16', '2025-08-30 10:34:16'),
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
(68, 6, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(69, 7, 'New Panel Member Added', 'A new panel member has been added: Prof. John Dela Santos (AI Expert)', 'info', 0, '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
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
(80, 6, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
(81, 7, 'New Panel Member Added', 'A new panel member has been added: John HOOD (Data Privacy & Cybersecurity)', 'info', 0, '2025-09-01 08:27:21', '2025-09-01 08:27:21'),
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
(92, 6, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
(93, 7, 'Panel Member Updated', 'Panel member information has been updated: John HOOD', 'info', 0, '2025-09-01 08:27:38', '2025-09-01 08:27:38'),
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
(104, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
(105, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:30:47', '2025-09-01 08:30:47'),
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
(116, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
(117, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 08:33:29', '2025-09-01 08:33:29'),
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
(128, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(129, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(130, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(131, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(132, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(133, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(134, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(135, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(136, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 09:37:12', '2025-09-01 09:37:12'),
(137, 7, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-01 10:28:06', '2025-09-01 10:28:06'),
(138, 9, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-01 10:28:06', '2025-09-01 10:28:06'),
(139, 7, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-01 10:28:06', '2025-09-01 10:28:06'),
(140, 9, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41006.', 'success', 0, '2025-09-01 10:28:06', '2025-09-01 10:28:06'),
(141, 7, 'Proposal Submitted Successfully', 'Your research proposal \'The Impact of Technology Integration on Student Engagement in Secondary Classrooms\' has been submitted and is under review.', 'success', 0, '2025-09-01 10:48:45', '2025-09-01 10:48:45'),
(142, 5, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'The Impact of Technology Integration on Student Engagement in Secondary Classrooms\' for review.', 'info', 0, '2025-09-01 10:48:45', '2025-09-01 10:48:45'),
(143, 6, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'The Impact of Technology Integration on Student Engagement in Secondary Classrooms\' for review.', 'info', 0, '2025-09-01 10:48:45', '2025-09-01 10:48:45'),
(144, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(145, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(146, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(147, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
(148, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:18:17', '2025-09-01 11:18:17'),
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
(159, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
(160, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:19:34', '2025-09-01 11:19:34'),
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
(171, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
(172, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GROUP 100 on 2025-09-08 at 12:00', 'info', 0, '2025-09-01 11:22:45', '2025-09-01 11:22:45'),
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
(183, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(184, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(185, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(186, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(187, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(188, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(189, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(190, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(191, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-08 at 12:30', 'info', 0, '2025-09-01 11:23:11', '2025-09-01 11:23:11'),
(192, 7, 'Proposal Submitted Successfully', 'Your research proposal \'Social Media and Mental Health\' has been submitted and is under review.', 'success', 0, '2025-09-01 11:55:14', '2025-09-01 11:55:14'),
(193, 5, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Social Media and Mental Health\' for review.', 'info', 0, '2025-09-01 11:55:14', '2025-09-01 11:55:14'),
(194, 6, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Social Media and Mental Health\' for review.', 'info', 0, '2025-09-01 11:55:14', '2025-09-01 11:55:14'),
(195, 7, 'Proposal Submitted Successfully', 'Your research proposal \'Social Media and Mental Health\' has been submitted and is under review.', 'success', 0, '2025-09-01 11:55:14', '2025-09-01 11:55:14'),
(196, 5, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Social Media and Mental Health\' for review.', 'info', 0, '2025-09-01 11:55:14', '2025-09-01 11:55:14'),
(197, 6, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Social Media and Mental Health\' for review.', 'info', 0, '2025-09-01 11:55:14', '2025-09-01 11:55:14'),
(198, 7, 'Proposal Submitted Successfully', 'Your research proposal \'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration\' has been submitted and is under review.', 'success', 0, '2025-09-01 15:21:40', '2025-09-01 15:21:40'),
(199, 5, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration\' for review.', 'info', 0, '2025-09-01 15:21:40', '2025-09-01 15:21:40'),
(200, 6, 'New Proposal Submitted', 'Hanni Pham submitted a new research proposal \'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration\' for review.', 'info', 0, '2025-09-01 15:21:40', '2025-09-01 15:21:40'),
(201, 1, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(202, 12, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(203, 13, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(204, 14, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(205, 2, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(206, 7, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(207, 9, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 15:48:08', '2025-09-01 15:48:08'),
(208, 12, 'Proposal Submitted Successfully', 'Your research proposal \'The Impact of Social Media on Adolescent Mental Health: A Case Study Approach\' has been submitted and is under review.', 'success', 0, '2025-09-01 15:49:14', '2025-09-01 15:49:14'),
(209, 5, 'New Proposal Submitted', 'Angelito Pampanga submitted a new research proposal \'The Impact of Social Media on Adolescent Mental Health: A Case Study Approach\' for review.', 'info', 0, '2025-09-01 15:49:14', '2025-09-01 15:49:14'),
(210, 6, 'New Proposal Submitted', 'Angelito Pampanga submitted a new research proposal \'The Impact of Social Media on Adolescent Mental Health: A Case Study Approach\' for review.', 'info', 0, '2025-09-01 15:49:14', '2025-09-01 15:49:14'),
(211, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(212, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(213, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(214, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 1, '2025-09-01 16:06:24', '2025-09-01 16:19:02'),
(215, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(216, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(217, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(218, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(219, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(220, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(221, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(222, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:00', 'info', 0, '2025-09-01 16:06:24', '2025-09-01 16:06:24'),
(223, 7, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 16:10:45', '2025-09-01 16:10:45'),
(224, 9, 'Adviser Assigned', 'Prof. Prof. Emily Williams has been assigned as your thesis adviser for BSIT - Cluster 41001.', 'success', 0, '2025-09-01 16:10:45', '2025-09-01 16:10:45'),
(225, 1, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(226, 2, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(227, 5, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(228, 6, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(229, 7, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(230, 8, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(231, 9, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(232, 10, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(233, 11, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(234, 12, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(235, 13, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27'),
(236, 14, 'Defense Scheduled', 'A defense has been scheduled for group: GRP 1 on 2025-09-02 at 00:25', 'info', 0, '2025-09-01 16:26:27', '2025-09-01 16:26:27');

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
(11, 'John', 'HOOD', 'Haeri12n@gmail.com', 'Data Privacy & Cybersecurity', 'bsit', 'active', 'member', '2025-09-01 08:27:20', '2025-09-01 08:27:38');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `payment_type` enum('research_forum','pre_oral_defense','final_defense') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `pdf_receipt` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed','failed') NOT NULL DEFAULT 'pending',
  `admin_approved` tinyint(1) DEFAULT 0,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `payment_type`, `amount`, `pdf_receipt`, `status`, `admin_approved`, `payment_date`) VALUES
(29, 7, 'research_forum', 100.00, '../assets/uploads/receipts/tbls crad.pdf', 'approved', 0, '2025-09-01 15:21:17'),
(30, 9, 'research_forum', 100.00, '../assets/uploads/receipts/tbls crad.pdf', 'approved', 0, '2025-09-01 15:21:17'),
(31, 1, 'research_forum', 100.00, '../assets/uploads/receipts/Cap101-reviewer-Prelim.pdf', 'approved', 0, '2025-09-01 15:48:22'),
(32, 2, 'research_forum', 100.00, '../assets/uploads/receipts/Cap101-reviewer-Prelim.pdf', 'approved', 0, '2025-09-01 15:48:22'),
(33, 12, 'research_forum', 100.00, '../assets/uploads/receipts/Cap101-reviewer-Prelim.pdf', 'approved', 0, '2025-09-01 15:48:22'),
(34, 13, 'research_forum', 100.00, '../assets/uploads/receipts/Cap101-reviewer-Prelim.pdf', 'approved', 0, '2025-09-01 15:48:22'),
(35, 14, 'research_forum', 100.00, '../assets/uploads/receipts/Cap101-reviewer-Prelim.pdf', 'approved', 0, '2025-09-01 15:48:22'),
(36, 1, 'research_forum', 100.00, '../assets/uploads/receipts/Sample-Interview-Questions (1).pdf', 'approved', 0, '2025-09-01 15:49:02'),
(37, 2, 'research_forum', 100.00, '../assets/uploads/receipts/Sample-Interview-Questions (1).pdf', 'approved', 0, '2025-09-01 15:49:02'),
(38, 12, 'research_forum', 100.00, '../assets/uploads/receipts/Sample-Interview-Questions (1).pdf', 'approved', 0, '2025-09-01 15:49:02'),
(39, 13, 'research_forum', 100.00, '../assets/uploads/receipts/Sample-Interview-Questions (1).pdf', 'approved', 0, '2025-09-01 15:49:02'),
(40, 14, 'research_forum', 100.00, '../assets/uploads/receipts/Sample-Interview-Questions (1).pdf', 'approved', 0, '2025-09-01 15:49:02'),
(41, 7, 'pre_oral_defense', 100.00, '../assets/uploads/receipts/tbls crad.pdf', 'approved', 0, '2025-09-01 16:14:35'),
(42, 9, 'pre_oral_defense', 100.00, '../assets/uploads/receipts/tbls crad.pdf', 'approved', 0, '2025-09-01 16:14:35');

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
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `group_id`, `title`, `description`, `file_path`, `submitted_at`, `status`, `reviewed_at`) VALUES
(18, 16, 'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration', 'This study aims to design and develop a campus navigation system that uses QR codes placed at key locations inside school buildings. The system will be integrated with a cloud database to provide real-time directions and facility information accessible through a mobile application. The project addresses the challenges of new students and visitors in locating rooms, offices, and facilities within the campus, thereby improving efficiency and user experience.', '../assets/uploads/proposals/Cap101-reviewer-Prelim.pdf', '2025-09-01 15:21:40', 'Completed', '2025-09-01 16:25:47'),
(19, 15, 'The Impact of Social Media on Adolescent Mental Health: A Case Study Approach', 'This thesis investigates the relationship between social media usage and mental health outcomes in adolescents. Through qualitative interviews and quantitative surveys, it aims to identify patterns of social media behavior that correlate with anxiety, depression, and self-esteem levels. The study offers recommendations for healthier online habits and policy suggestions for social media platforms.', '../assets/uploads/proposals/Sample-Interview-Questions (1).pdf', '2025-09-01 15:49:14', 'Pending', NULL);

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
(14, 1, '21016692', 'John Marvic Giray', 'BSIT', '41002', NULL, '2025-2026', '2025-08-25 13:16:56', '2025-09-01 15:51:25'),
(15, 11, '22101234', 'Leandro Lojero', 'BSIT', 'Not Assigned', NULL, '2025-2026', '2025-08-28 05:26:12', '2025-08-28 05:26:12'),
(16, 12, '22105678', 'Angelito Pampanga', 'BSIT', '41002', NULL, '2025-2026', '2025-08-28 05:28:03', '2025-09-01 15:51:25'),
(17, 13, '22107890', 'Geo Caranza', 'BSIT', '41002', NULL, '2025-2026', '2025-08-28 05:29:36', '2025-09-01 15:51:25'),
(18, 14, '22010987', 'Erico Golay', 'BSIT', '41002', NULL, '2025-2026', '2025-08-28 05:31:02', '2025-09-01 15:51:25'),
(19, 2, '12345678', 'Coby Bryant Giray', 'BSIT', '41002', NULL, '2025-2026', '2025-08-29 05:15:18', '2025-09-01 15:51:25'),
(20, 7, '22011941', 'Hanni Pham', 'BSIT', '41001', 7, '2025-2026', '2025-09-01 10:05:10', '2025-09-01 16:10:45'),
(21, 9, '22014823', 'Kang Haerin', 'BSIT', '41001', 7, '2025-2026', '2025-09-01 10:12:00', '2025-09-01 16:10:45');

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
(5, 'Capstone', '', 1, '2025-08-29 05:01:08', '2025-08-29 05:01:08');

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
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clusters`
--
ALTER TABLE `clusters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `defense_panel`
--
ALTER TABLE `defense_panel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- AUTO_INCREMENT for table `panel_members`
--
ALTER TABLE `panel_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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
