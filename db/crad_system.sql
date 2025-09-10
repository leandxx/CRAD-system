-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1: 3308
-- Generation Time: Sep 10, 2025 at 08:31 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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
(225, 'BSIT', '41001', '2025-2026', NULL, NULL, 7, 50, 'unassigned', '2025-09-10 13:59:32'),
(226, 'BSIT', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(227, 'BSIT', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(228, 'BSIT', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(229, 'BSIT', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(230, 'BSIT', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(231, 'BSIT', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(232, 'BSIT', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(233, 'BSIT', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(234, 'BSIT', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(235, 'BSHM', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(236, 'BSHM', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(237, 'BSHM', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(238, 'BSHM', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(239, 'BSHM', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(240, 'BSHM', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(241, 'BSHM', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(242, 'BSHM', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(243, 'BSHM', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(244, 'BSHM', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(245, 'BSOA', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(246, 'BSOA', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(247, 'BSOA', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(248, 'BSOA', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(249, 'BSOA', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(250, 'BSOA', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(251, 'BSOA', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(252, 'BSOA', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(253, 'BSOA', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(254, 'BSOA', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(255, 'BSBA', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(256, 'BSBA', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(257, 'BSBA', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(258, 'BSBA', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(259, 'BSBA', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(260, 'BSBA', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(261, 'BSBA', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(262, 'BSBA', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(263, 'BSBA', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(264, 'BSBA', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(265, 'BSED', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(266, 'BSED', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(267, 'BSED', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(268, 'BSED', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(269, 'BSED', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(270, 'BSED', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(271, 'BSED', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(272, 'BSED', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(273, 'BSED', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(274, 'BSED', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(275, 'BSCRIM', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(276, 'BSCRIM', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(277, 'BSCRIM', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(278, 'BSCRIM', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(279, 'BSCRIM', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(280, 'BSCRIM', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(281, 'BSCRIM', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(282, 'BSCRIM', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(283, 'BSCRIM', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(284, 'BSCRIM', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(285, 'BEED', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(286, 'BEED', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(287, 'BEED', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(288, 'BEED', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(289, 'BEED', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(290, 'BEED', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(291, 'BEED', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(292, 'BEED', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(293, 'BEED', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(294, 'BEED', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(295, 'BSCpE', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(296, 'BSCpE', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(297, 'BSCpE', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(298, 'BSCpE', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(299, 'BSCpE', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(300, 'BSCpE', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(301, 'BSCpE', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(302, 'BSCpE', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(303, 'BSCpE', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(304, 'BSCpE', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(305, 'BSTM', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(306, 'BSTM', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(307, 'BSTM', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(308, 'BSTM', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(309, 'BSTM', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(310, 'BSTM', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(311, 'BSTM', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(312, 'BSTM', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(313, 'BSTM', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(314, 'BSTM', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(315, 'BSEntrep', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(316, 'BSEntrep', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(317, 'BSEntrep', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(318, 'BSEntrep', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(319, 'BSEntrep', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(320, 'BSEntrep', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(321, 'BSEntrep', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(322, 'BSEntrep', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(323, 'BSEntrep', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(324, 'BSEntrep', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(325, 'BSAIS', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(326, 'BSAIS', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(327, 'BSAIS', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(328, 'BSAIS', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(329, 'BSAIS', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(330, 'BSAIS', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(331, 'BSAIS', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(332, 'BSAIS', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(333, 'BSAIS', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(334, 'BSAIS', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(335, 'BSPsych', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(336, 'BSPsych', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(337, 'BSPsych', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(338, 'BSPsych', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(339, 'BSPsych', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(340, 'BSPsych', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(341, 'BSPsych', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(342, 'BSPsych', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(343, 'BSPsych', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(344, 'BSPsych', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(345, 'BLIS', '41001', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(346, 'BLIS', '41002', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(347, 'BLIS', '41003', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(348, 'BLIS', '41004', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(349, 'BLIS', '41005', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(350, 'BLIS', '41006', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(351, 'BLIS', '41007', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(352, 'BLIS', '41008', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(353, 'BLIS', '41009', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07'),
(354, 'BLIS', '41010', '2025-2026', NULL, NULL, 0, 25, 'unassigned', '2025-09-06 11:21:07');

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
(166, 24, 8, 'chair', '2025-09-10 18:26:02'),
(167, 24, 11, 'member', '2025-09-10 18:26:02'),
(168, 24, 10, 'member', '2025-09-10 18:26:02');

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
  `status` enum('scheduled','confirmed','completed','cancelled','failed','passed') DEFAULT 'scheduled',
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
(24, 16, '2025-09-10', '09:00:00', '09:30:00', 1, 'completed', '2025-09-10 18:24:54', '', 'pending', NULL, NULL, 0, NULL);

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
  `expertise` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `fullname`, `department`, `expertise`, `email`) VALUES
(1, 'Dr. Maria Santos', 'Accounting Information System', 'Financial Accounting and Auditing', ''),
(2, 'Prof. James Wilson', 'Information Technology', 'Web Development and Database Systems', ''),
(3, 'Dr. Lisa Chen', 'Hospitality Management', 'Hotel Operations and Management', ''),
(4, 'Prof. Robert Garcia', 'Criminology', 'Forensic Science and Criminal Investigation', ''),
(5, 'Dr. Sarah Johnson', 'Tourism Management', 'Eco-Tourism and Travel Management', ''),
(6, 'Dr. Michael Brown', 'Accounting Information System', 'Taxation and Business Law', ''),
(7, 'Prof. Emily Williams', 'Information Technology', 'Cybersecurity and Network Administration', ''),
(8, 'Dr. David Lee', 'Hospitality Management', 'Food and Beverage Management', ''),
(9, 'Prof. Amanda Rodriguez', 'Criminology', 'Criminal Psychology and Behavior', ''),
(10, 'Dr. Jennifer Kim', 'Tourism Management', 'Tourism Planning and Development', ''),
(11, 'Dr. Justene Jean Siarez', 'Psychology', 'Medical Office Administration', '');

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
(15, 'GRP 2', 'BSIT', 225, '5B97C1', '2025-08-29 05:08:49'),
(16, 'GRP 1', 'BSIT', 225, 'C7E820', '2025-09-01 10:10:40');

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
(0, 8, 'b0c46e117a244b46d3e794218855b126ee3d202a32410d0e0e25ee3d0779f829', 'accepted', '2025-08-30 17:40:43', '2025-08-30 17:41:37'),
(0, 12, '1ce27ec05dd010f7aec8bb1b526d87016d403edebffe94eba3b5c2007b3c1a91', 'pending', '2025-09-03 17:14:50', NULL);

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
(10, 'Prof. John Dela', 'Santos', 'leanlojero23@gmail.com', 'AI Expert', 'bsit', 'active', 'member', '2025-09-01 08:26:53', '2025-09-01 08:26:53'),
(11, 'John', 'Hood', 'Haeri12n@gmail.com', 'Data Privacy & Cybersecurity', 'bsit', 'active', 'member', '2025-09-01 08:27:20', '2025-09-08 18:20:15'),
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
(42, 9, 'pre_oral_defense', 100.00, '../assets/uploads/receipts/tbls crad.pdf', 'approved', 0, '2025-09-01 16:14:35'),
(43, 7, 'final_defense', 100.00, '../assets/uploads/receipts/tbls crad.pdf', 'approved', 0, '2025-09-01 17:33:24'),
(44, 9, 'final_defense', 100.00, '../assets/uploads/receipts/tbls crad.pdf', 'approved', 0, '2025-09-01 17:33:24'),
(45, 1, 'final_defense', 100.00, '../assets/uploads/receipts/coc-cor.pdf', 'approved', 0, '2025-09-03 08:35:33'),
(46, 2, 'final_defense', 100.00, '../assets/uploads/receipts/coc-cor.pdf', 'approved', 0, '2025-09-03 08:35:33'),
(47, 12, 'final_defense', 100.00, '../assets/uploads/receipts/coc-cor.pdf', 'approved', 0, '2025-09-03 08:35:33'),
(48, 13, 'final_defense', 100.00, '../assets/uploads/receipts/coc-cor.pdf', 'approved', 0, '2025-09-03 08:35:33'),
(49, 14, 'final_defense', 100.00, '../assets/uploads/receipts/coc-cor.pdf', 'approved', 0, '2025-09-03 08:35:33'),
(50, 1, 'pre_oral_defense', 100.00, '../assets/uploads/receipts/coc-cor.pdf', 'approved', 0, '2025-09-03 08:49:04'),
(51, 2, 'pre_oral_defense', 100.00, '../assets/uploads/receipts/coc-cor.pdf', 'approved', 0, '2025-09-03 08:49:04'),
(52, 12, 'pre_oral_defense', 100.00, '../assets/uploads/receipts/coc-cor.pdf', 'approved', 0, '2025-09-03 08:49:04'),
(53, 13, 'pre_oral_defense', 100.00, '../assets/uploads/receipts/coc-cor.pdf', 'approved', 0, '2025-09-03 08:49:04'),
(54, 14, 'pre_oral_defense', 100.00, '../assets/uploads/receipts/coc-cor.pdf', 'approved', 0, '2025-09-03 08:49:04');

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
(18, 16, 'Smart Campus Navigator: An Indoor Positioning System Using QR Codes and Cloud Integration', 'This study aims to design and develop a campus navigation system that uses QR codes placed at key locations inside school buildings. The system will be integrated with a cloud database to provide real-time directions and facility information accessible through a mobile application. The project addresses the challenges of new students and visitors in locating rooms, offices, and facilities within the campus, thereby improving efficiency and user experience.', '../assets/uploads/proposals/Cap101-reviewer-Prelim.pdf', '2025-09-01 15:21:40', 'Completed', '2025-09-01 17:49:23'),
(19, 15, 'Intelligent Progressive Research Submission ', 'This thesis investigates the relationship between social media usage and mental health outcomes in adolescents. Through qualitative interviews and quantitative surveys, it aims to identify patterns of social media behavior that correlate with anxiety, depression, and self-esteem levels. The study offers recommendations for healthier online habits and policy suggestions for social media platforms.', '../assets/uploads/proposals/Sample-Interview-Questions (1).pdf', '2025-09-01 15:49:14', 'Completed', '2025-09-03 09:21:24');

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
  `building` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `building`) VALUES
(1, 'Defense Room 1', 'CRAD Office'),
(2, 'Defense Room 2', 'CRAD Office'),
(3, 'Defense Room 3', 'CRAD Office'),
(4, 'Defense Room 4', 'CRAD Office'),
(5, 'Defense Room 5', 'CRAD Office');

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
(14, 1, '21016692', 'John Marvic Giray', 'BSIT', '41001', NULL, '2025-2026', '2025-08-25 13:16:56', '2025-09-06 16:52:21'),
(15, 11, '22101234', 'Leandro Lojero', 'BSIT', 'Not Assigned', NULL, '2025-2026', '2025-08-28 05:26:12', '2025-08-28 05:26:12'),
(16, 12, '22105678', 'Angelito Pampanga', 'BSIT', '41001', NULL, '2025-2026', '2025-08-28 05:28:03', '2025-09-06 16:52:21'),
(17, 13, '22107890', 'Geo Caranza', 'BSIT', '41001', NULL, '2025-2026', '2025-08-28 05:29:36', '2025-09-06 16:52:21'),
(18, 14, '22010987', 'Erico Golay', 'BSIT', '41001', NULL, '2025-2026', '2025-08-28 05:31:02', '2025-09-06 16:52:21'),
(19, 2, '12345678', 'Coby Bryant Giray', 'BSIT', '41001', NULL, '2025-2026', '2025-08-29 05:15:18', '2025-09-06 16:52:21'),
(20, 7, '22011941', 'Hanni Pham', 'BSIT', '41001', NULL, '2025-2026', '2025-09-01 10:05:10', '2025-09-06 16:52:29'),
(21, 9, '22014823', 'Kang Haerin', 'BSIT', '41001', NULL, '2025-2026', '2025-09-01 10:12:00', '2025-09-06 16:52:29');

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
(5, 'Capstone', '', 0, '2025-08-29 05:01:08', '2025-09-03 09:37:22'),
(6, 'SUBMISSION ', '', 1, '2025-09-03 09:37:42', '2025-09-03 09:37:42');

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
(14, 5, 'Chapter 2 Week 2', 'DAPAT TA{PS NA', '2025-08-31 08:00:00', 'pending', '2025-08-29 05:01:08', '2025-09-03 09:36:47'),
(15, 5, 'Chapter 3 Week 3', '', '2025-09-01 08:00:00', 'pending', '2025-08-29 05:01:08', '2025-08-29 05:01:08'),
(16, 6, 'PROPOSAL', 'dsds', '2025-09-25 12:00:00', 'pending', '2025-09-03 09:37:42', '2025-09-03 09:37:42');

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

