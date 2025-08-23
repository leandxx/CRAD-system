-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 23, 2025 at 10:16 AM
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
(3, 6, 5, 'member', '2025-08-23 03:08:57');

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
(6, 4, '2025-08-23', '11:10:00', '11:11:00', 1, 'completed', '2025-08-22 05:48:48');

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
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `join_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `join_code`, `created_at`) VALUES
(4, 'Crad ', 'D5718Y', '2025-08-20 15:04:37');

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
(4, 4, 1),
(5, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `panel_invitations`
--

CREATE TABLE `panel_invitations` (
  `id` int(11) NOT NULL,
  `defense_id` int(11) NOT NULL,
  `panel_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `invited_at` datetime NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `panel_invitations`
--

INSERT INTO `panel_invitations` (`id`, `defense_id`, `panel_id`, `token`, `status`, `invited_at`, `responded_at`) VALUES
(1, 6, 1, '500b1c08ba8b155ea7b60c931c1304e078cde970c93437ba91774eb8e7621749', 'accepted', '2025-08-23 11:41:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `panel_members`
--

CREATE TABLE `panel_members` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `panel_members`
--

INSERT INTO `panel_members` (`id`, `first_name`, `last_name`, `email`, `specialization`, `status`, `created_at`, `updated_at`) VALUES
(1, 'John Marvic', 'Giray', 'girayjohnmarvic09@gmail.com', 'Information Technology', 'active', '2025-08-23 03:41:13', '2025-08-23 03:41:13');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `amount`, `status`, `payment_date`) VALUES
(1, 1, 100.00, 'completed', '2025-08-20 15:17:20'),
(2, 2, 100.00, 'completed', '2025-08-20 15:31:13');

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
  `status` enum('Pending','Approved','Rejected','Under Review','Revision Requested') NOT NULL DEFAULT 'Pending',
  `feedback` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `group_id`, `title`, `description`, `file_path`, `submitted_at`, `status`, `feedback`, `reviewed_at`) VALUES
(1, 4, 'SCHOOL MANAGEMENT SYSTEM 1: CENTER FOR RESEARCH AND DEVELOPMENT INTELLIGENT PROGRESSIVE RESEARCH SUBMISSION AND TRACKING SYSTEM USING OPENAI', 'The Center for Research and Development Intelligent Progressive Research Submission and Tracking System is a web-based platform designed to streamline the research submission, monitoring, and evaluation process for students and faculty members. Leveraging OpenAIâ€™s intelligent capabilities, the system provides an automated, efficient, and user-friendly solution to manage research proposals, ongoing projects, and final outputs.', 'assets/uploadsproposal_4_1755754752.pdf', '2025-08-21 05:39:12', 'Approved', 'goods', '2025-08-22 04:42:20');

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
  `department` varchar(100) NOT NULL,
  `section` varchar(50) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `school_id`, `full_name`, `department`, `section`, `school_year`, `created_at`, `updated_at`) VALUES
(1, 7, '22014879', 'Kang Haerin', 'Computer Science', 'BSIT-41005', '2024-2025', '2025-08-20 15:59:33', '2025-08-20 15:59:33'),
(2, 1, '21016692', 'John Marvic Giray', 'Computer Science', 'BSIT - 41006', '2025-2026', '2025-08-20 17:02:33', '2025-08-20 17:02:33'),
(3, 2, '12345678', 'Coby Bryant Giray', 'Education', 'BSeD - 41005', '2025-2026', '2025-08-20 17:15:02', '2025-08-20 17:15:02');

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
(1, 'Capstone Timeline', 'Submission', 0, '2025-08-20 15:43:53', '2025-08-20 15:46:07');

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
(2, 1, 'Chapter 2 Week 2', '', '2025-08-20 23:46:00', 'pending', '2025-08-20 15:43:53', '2025-08-20 15:43:53');

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
(5, 'admin@gmail.com', '$2y$10$qfM4wrFY47klhpxEDSr0N.5KewyovnP62Qpt7tNXkGApVh/kTHzCy', 'Admin', '2025-08-20 15:40:21');

--
-- Indexes for dumped tables
--

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
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `document_submissions`
--
ALTER TABLE `document_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `join_code` (`join_code`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_id` (`group_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `panel_invitations`
--
ALTER TABLE `panel_invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `unique_invitation` (`defense_id`,`panel_id`),
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
  ADD UNIQUE KEY `unique_user` (`user_id`);

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
-- AUTO_INCREMENT for table `defense_panel`
--
ALTER TABLE `defense_panel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `document_submissions`
--
ALTER TABLE `document_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `panel_invitations`
--
ALTER TABLE `panel_invitations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `panel_members`
--
ALTER TABLE `panel_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `submission_timelines`
--
ALTER TABLE `submission_timelines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `timeline_milestones`
--
ALTER TABLE `timeline_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_tbl`
--
ALTER TABLE `user_tbl`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `defense_schedules`
--
ALTER TABLE `defense_schedules`
  ADD CONSTRAINT `defense_schedules_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

--
-- Constraints for table `document_submissions`
--
ALTER TABLE `document_submissions`
  ADD CONSTRAINT `document_submissions_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `panel_invitations`
--
ALTER TABLE `panel_invitations`
  ADD CONSTRAINT `panel_invitations_ibfk_1` FOREIGN KEY (`defense_id`) REFERENCES `defense_schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `panel_invitations_ibfk_2` FOREIGN KEY (`panel_id`) REFERENCES `panel_members` (`id`) ON DELETE CASCADE;

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

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `defense_schedules` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-08-23 11:13:12' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE defense_schedules
  SET status = 'completed'
  WHERE end_time < CURTIME() 
    AND defense_date <= CURDATE()
    AND status = 'scheduled'$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
