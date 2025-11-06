-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2025 at 11:58 AM
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
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(200) NOT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `date_needed` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `posted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL,
  `starting_location` varchar(200) DEFAULT NULL,
  `ending_location` varchar(200) DEFAULT NULL,
  `urgency` varchar(20) DEFAULT NULL,
  `time_preference` varchar(20) DEFAULT NULL,
  `specific_time` varchar(20) DEFAULT NULL,
  `time_range_start` varchar(20) DEFAULT NULL,
  `time_range_end` varchar(20) DEFAULT NULL,
  `payment_type` varchar(20) DEFAULT NULL,
  `estimated_hours` decimal(5,2) DEFAULT NULL,
  `additional_cost` decimal(10,2) DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `helpers_needed` int(11) DEFAULT 1,
  `screening_questions` text DEFAULT NULL,
  `make_mandatory` tinyint(1) DEFAULT 0,
  `is_negotiable` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `user_id`, `title`, `category`, `description`, `location`, `budget`, `date_needed`, `status`, `posted_at`, `image_path`, `starting_location`, `ending_location`, `urgency`, `time_preference`, `specific_time`, `time_range_start`, `time_range_end`, `payment_type`, `estimated_hours`, `additional_cost`, `requirements`, `helpers_needed`, `screening_questions`, `make_mandatory`, `is_negotiable`) VALUES
(22, 18, 'need cuddles asap', 'Care services', 'bfdkjsbdfjbkjasbkjbsdgkjlbdkj;abg;kjdbafk;jgbad;fbg;kjarfgkjlbasdhjfbgjdsabfkdsfjbkjwebfklwelig', 'diyan lang sa tabi tabi', 3024.00, '2025-11-06', 'open', '2025-11-06 15:38:16', NULL, 'Philippines', 'Philippines', 'flexible', 'no-preference', '', '', '', '0', 8.00, 0.00, '', 1, NULL, 0, 0),
(23, 18, 'lf project maker', 'Business & admin', 'df,hjsgjlhawgefhulawevjlhfjhbglsjhergjuqegkjfberkjgbe', 'diyan lang sa tabi tabi', 3325266.00, '2025-11-06', 'open', '2025-11-06 16:26:45', NULL, 'Philippines', 'Philippines', 'flexible', 'no-preference', '', '', '', '0', 999.99, 0.00, '', 1, NULL, 0, 0),
(24, 18, 'need cuddles asap haha', 'Talent', 'dsagfkjhadbsjhkbdsajfbkjdsbafkjbdsjanvkjsdnakvjnkjdsnbfjknsdfsdafsadfsda', 'diyan lang sa tabi tabi', 756.00, '2025-11-06', 'open', '2025-11-06 18:20:46', NULL, 'Philippines', 'USA', 'flexible', 'no-preference', '', '', '', '0', 2.00, 0.00, '', 1, NULL, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jobs_status` (`status`),
  ADD KEY `idx_jobs_posted_at` (`posted_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
