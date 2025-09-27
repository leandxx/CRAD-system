-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Sep 23, 2025 at 07:31 AM
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
(261, 48, 12, 'chair', '2025-09-16 14:57:05'),
(262, 48, 11, 'member', '2025-09-16 14:57:05'),
(263, 48, 10, 'member', '2025-09-16 14:57:05'),
(264, 49, 12, 'chair', '2025-09-16 15:16:21'),
(265, 49, 11, 'member', '2025-09-16 15:16:21'),
(266, 49, 10, 'member', '2025-09-16 15:16:21'),
(267, 50, 12, 'chair', '2025-09-16 15:18:33'),
(268, 50, 11, 'member', '2025-09-16 15:18:33'),
(269, 50, 10, 'member', '2025-09-16 15:18:33'),
(302, 68, 12, 'chair', '2025-09-23 05:22:12'),
(303, 68, 11, 'member', '2025-09-23 05:22:12'),
(304, 68, 10, 'member', '2025-09-23 05:22:12');

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
  `status` enum('scheduled','completed','cancelled','failed','passed','pre_completed') DEFAULT 'scheduled',
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
(68, 16, '2025-09-23', '09:00:00', '10:00:00', 1, 'scheduled', '2025-09-23 05:22:12', 'pre_oral', 'pending', NULL, NULL, 0, NULL, '2025-09-23 05:22:12');

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
(40, 7, 'research_forum', 0.00, NULL, 'approved', 1, NULL, '2025-09-16 15:24:03', '[\"../assets/uploads/receipts/1758036243_0_Untitled.png\"]', '[{\"status\":\"approved\",\"feedback\":\"\",\"updated_at\":\"2025-09-16T23:24:26+08:00\"}]'),
(41, 7, 'pre_oral_defense', 0.00, NULL, 'approved', 1, NULL, '2025-09-16 15:24:10', '[\"../assets/uploads/receipts/1758036250_0_betw.png\"]', '[{\"status\":\"approved\",\"feedback\":\"\",\"updated_at\":\"2025-09-16T23:24:27+08:00\"}]'),
(42, 7, 'pre_oral_redefense', 0.00, NULL, 'approved', 1, NULL, '2025-09-16 15:25:26', '[\"../assets/uploads/receipts/1758036326_0_alerting and monitoring.jpg\"]', '{\"redefense\":true,\"0\":{\"status\":\"approved\",\"feedback\":\"\",\"updated_at\":\"2025-09-17T13:34:53+02:00\"}}');

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
(18, 16, 'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration', 'This study aims to design and develop a campus navigation system that uses QR codes placed at key locations inside school buildings. The system will be integrated with a cloud database to provide real-time directions and facility information accessible through a mobile application. The project addresses the challenges of new students and visitors in locating rooms, offices, and facilities within the campus, thereby improving efficiency and user experience.', '../assets/uploads/proposals/Cap101-reviewer-Prelim.pdf', '2025-09-01 15:21:40', 'Completed', '2025-09-23 05:21:47', 0),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=305;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1655;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `defense_schedules` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-08-23 11:13:12' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE defense_schedules
  SET status = 'passed'
  WHERE end_time < CURTIME() 
    AND defense_date <= CURDATE()
    AND status = 'scheduled'$$

CREATE DEFINER=`root`@`localhost` EVENT `update_defense_status` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-08-24 03:57:24' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE defense_schedules
  SET status = 'passed'
  WHERE status = 'scheduled'
    AND TIMESTAMP(defense_date, end_time) < NOW()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
