-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Aug 19, 2025 at 06:04 PM
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
-- Table structure for table `panel_members`
--

CREATE TABLE `panel_members` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `panel_members`
--

INSERT INTO `panel_members` (`id`, `first_name`, `last_name`, `email`, `specialization`, `status`, `created_at`, `updated_at`) VALUES
(4, 'DR JOHN MARVIC', 'GIRAY', 'leanlojero23@gmail.com', 'Chainperson', 'active', '2025-08-19 08:11:43', '2025-08-19 08:11:43'),
(5, 'Dr. Maria', 'Santos', 'jho090402@gmail.com', 'Artificial Intelligence (AI)', 'active', '2025-08-19 10:18:09', '2025-08-19 10:18:09'),
(6, 'Prof. John Dela', 'Cruz', 'jdelacruz@bestlink.edu.ph', 'Data Privacy & Cybersecurity', 'active', '2025-08-19 10:19:59', '2025-08-19 10:19:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `panel_members`
--
ALTER TABLE `panel_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `panel_members`
--
ALTER TABLE `panel_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
