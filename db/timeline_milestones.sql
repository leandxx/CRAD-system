-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Aug 17, 2025 at 03:26 PM
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
(1, 1, 'Proposal Submission', '', '2023-12-23 00:23:00', 'pending', '2025-08-17 11:05:59', '2025-08-17 11:05:59'),
(2, 2, 'Proposal Submission', '', '2025-12-23 12:00:00', 'pending', '2025-08-17 11:11:02', '2025-08-17 11:11:02'),
(3, 3, 'Proposal Submission', '', '2025-08-18 12:00:00', 'pending', '2025-08-17 11:20:39', '2025-08-17 11:20:39'),
(4, 4, 'Pre-Submission Stage', 'Week 1–2\r\n\r\nOrientation for research guidelines & policies\r\n\r\nDistribution of research handbook/templates\r\n\r\nInitial consultation with faculty/adviser', '2025-08-19 12:00:00', 'pending', '2025-08-17 12:14:02', '2025-08-17 12:14:02'),
(5, 4, 'Submission & Review', 'Week 3–5\r\n\r\nSubmission of Research Titles (students submit at least 3 suggested titles)\r\n\r\nTitle Screening by CRAD / Department Committee\r\n\r\nApproved title forwarded for adviser assignment', '2025-08-19 12:00:00', 'pending', '2025-08-17 12:14:02', '2025-08-17 12:14:02'),
(6, 4, 'Defense Stage', 'Week 9–10\r\n\r\nPanel Assignment by CRAD/Department\r\n\r\nProposal defense schedule finalized\r\n\r\nStudents defend proposal before panel\r\n\r\nPanel recommendations documented', '2025-08-28 12:00:00', 'pending', '2025-08-17 12:14:02', '2025-08-17 12:14:02'),
(7, 5, 'Proposal Submission', 'gawin mo na ', '2025-08-17 00:00:00', 'pending', '2025-08-17 12:29:16', '2025-08-17 12:29:16'),
(8, 5, 'Revision', 'Gawin mo na ', '2025-08-18 12:00:00', 'pending', '2025-08-17 12:29:16', '2025-08-17 12:29:16'),
(9, 5, 'Defense ', 'Sheesh', '2025-08-20 12:00:00', 'pending', '2025-08-17 12:29:16', '2025-08-17 12:29:16'),
(10, 6, 'Proposal Submission', '', '2025-08-26 12:00:00', 'pending', '2025-08-17 12:39:58', '2025-08-17 12:39:58'),
(11, 7, 'Proposal Submission', '', '2025-08-27 12:00:00', 'pending', '2025-08-17 13:14:03', '2025-08-17 13:14:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `timeline_milestones`
--
ALTER TABLE `timeline_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timeline_id` (`timeline_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `timeline_milestones`
--
ALTER TABLE `timeline_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `timeline_milestones`
--
ALTER TABLE `timeline_milestones`
  ADD CONSTRAINT `timeline_milestones_ibfk_1` FOREIGN KEY (`timeline_id`) REFERENCES `submission_timelines` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
