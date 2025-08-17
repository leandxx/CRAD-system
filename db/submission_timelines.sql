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
(1, 'Research Submission', '', 0, '2025-08-17 11:05:59', '2025-08-17 11:06:15'),
(2, 'Research Submission', '', 0, '2025-08-17 11:11:02', '2025-08-17 11:19:42'),
(3, 'Research Submission', '', 0, '2025-08-17 11:20:39', '2025-08-17 12:09:37'),
(4, 'Research Submission ', '', 0, '2025-08-17 12:14:02', '2025-08-17 12:15:02'),
(5, 'Submission Timeline', '', 0, '2025-08-17 12:29:16', '2025-08-17 12:39:35'),
(6, 'Submission Timeline', '', 0, '2025-08-17 12:39:58', '2025-08-17 13:09:30'),
(7, 'Research Timeline', '', 1, '2025-08-17 13:14:03', '2025-08-17 13:14:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `submission_timelines`
--
ALTER TABLE `submission_timelines`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `submission_timelines`
--
ALTER TABLE `submission_timelines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
