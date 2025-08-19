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
-- Table structure for table `panel_availability_requests`
--

CREATE TABLE `panel_availability_requests` (
  `id` int(11) NOT NULL,
  `panel_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sent_at` datetime NOT NULL,
  `response_received` tinyint(1) DEFAULT 0,
  `response_text` text DEFAULT NULL,
  `response_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `panel_availability_requests`
--
ALTER TABLE `panel_availability_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `panel_id` (`panel_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `panel_availability_requests`
--
ALTER TABLE `panel_availability_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `panel_availability_requests`
--
ALTER TABLE `panel_availability_requests`
  ADD CONSTRAINT `panel_availability_requests_ibfk_1` FOREIGN KEY (`panel_id`) REFERENCES `panel_members` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
