-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 09:47 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `login`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `mobile`, `username`, `first_name`, `last_name`, `email`, `address`, `avatar`, `password`, `created_at`) VALUES
(8, '09239956047', NULL, NULL, NULL, NULL, NULL, NULL, 'Kali_1291', '2025-10-18 16:13:13'),
(11, '09333189573', NULL, NULL, NULL, NULL, NULL, NULL, 'Kiyaned#18', '2025-10-18 16:46:51'),
(12, '09171234567', NULL, NULL, NULL, NULL, NULL, NULL, 'Kiyaned#18', '2025-10-18 17:11:21'),
(13, '09542675621', NULL, NULL, NULL, NULL, NULL, NULL, 'Franco#23', '2025-10-18 17:20:52'),
(17, '09070950988', NULL, NULL, NULL, NULL, NULL, NULL, 'Kiyaned#18', '2025-11-04 07:09:23'),
(18, '09888888888', 'pogi', 'Franco', 'Ramos', 'francoramos483@gmail.com', '', 'assets/uploads/avatars/avatar_u18_1762330997.jpg', 'FrancoRamos123!', '2025-11-05 08:27:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
