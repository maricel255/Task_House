-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2024 at 04:19 PM
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
-- Database: `taskhouse_users`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcementID` int(11) NOT NULL,
  `adminID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `imagePath` varchar(255) DEFAULT NULL,
  `datePosted` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcementID`, `adminID`, `title`, `content`, `imagePath`, `datePosted`) VALUES
(1, 2, 'test1', 'test1', './uploaded_files/3fb6e30169011dbfa03657f1c2f27ee4.png', '2024-10-27 05:00:42'),
(2, 2, 'test2', 'test2', './uploaded_files/1804847dfc37dcb00eb2df0bd08439e0.png', '2024-10-27 05:00:53'),
(11, 1, 'test3with', 'test3with', './uploaded_files/gaco.pdf', '2024-10-27 21:13:22'),
(13, 1, 'annouce', 'aouncement testing', './uploaded_files/gaco (1).pdf', '2024-11-13 18:00:05'),
(28, 1, 'admin1 is posting', 'admin1 is annoucement text', './uploaded_files/admin1.pdf', '2024-11-14 12:40:08');

-- --------------------------------------------------------

--
-- Table structure for table `facacc`
--

CREATE TABLE `facacc` (
  `faciID` varchar(10) NOT NULL,
  `faciPass` varchar(10) NOT NULL,
  `adminID` int(11) NOT NULL,
  `faci_image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facacc`
--

INSERT INTO `facacc` (`faciID`, `faciPass`, `adminID`, `faci_image`) VALUES
('bshmf', 'bshmf', 2, ''),
('cotfa', 'asdfgh', 1, 'faci_cotfa_1731553149.jpg'),
('dfg', 'dfgdfg', 2, ''),
('fahm1', 'fahm1', 2, ''),
('fahm2', 'fahm2', 2, ''),
('fahmt', 'fahmt', 2, ''),
('fahmt1', 'fahmt1', 2, ''),
('fahmt3', 'fahmt3', 2, ''),
('fahmt5', 'fahmt5', 2, ''),
('inter', 'inter', 0, ''),
('poi', 'poipoi', 2, '');

-- --------------------------------------------------------

--
-- Table structure for table `intacc`
--

CREATE TABLE `intacc` (
  `internID` varchar(10) NOT NULL,
  `InternPass` varchar(255) NOT NULL,
  `adminID` int(11) DEFAULT NULL,
  `profile_image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `intacc`
--

INSERT INTO `intacc` (`internID`, `InternPass`, `adminID`, `profile_image`) VALUES
('', 'e4266470', 2, ''),
('123456', 'ntern1', 1, 'profile_123456_1731549888.png'),
('678905', '678905', 1, 'profile_678905_1731830210.jpg'),
('cot234', 'cot234', 1, ''),
('cot345', 'cot345', 1, ''),
('cotin3', 'cotin3', 1, ''),
('inhmt7', 'inhmt7', 2, ''),
('inthm1', 'inthm1', 2, ''),
('kyle', 'fuckyu', 2, ''),
('qwe', 'qweqwe', 2, ''),
('qwerty', 'qwerty', 2, ''),
('rty', 'rtyrty', 2, ''),
('tgh', 'tghtgh', 2, ''),
('wer', 'werwer', 2, '');

-- --------------------------------------------------------

--
-- Table structure for table `profile_information`
--

CREATE TABLE `profile_information` (
  `id` int(11) NOT NULL,
  `internID` varchar(10) DEFAULT NULL,
  `adminID` int(11) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `course_year_sec` varchar(50) DEFAULT NULL,
  `school` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `current_address` varchar(255) DEFAULT NULL,
  `provincial_address` varchar(255) DEFAULT NULL,
  `tel_no` varchar(20) DEFAULT NULL,
  `mobile_no` varchar(20) DEFAULT NULL,
  `birth_place` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `citizenship` varchar(50) DEFAULT NULL,
  `hr_manager` varchar(100) DEFAULT NULL,
  `faciID` varchar(50) DEFAULT NULL,
  `facilitator_email` varchar(100) DEFAULT NULL,
  `start_shift` time DEFAULT NULL,
  `end_shift` time DEFAULT NULL,
  `required_hours` int(11) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `blood_type` varchar(10) DEFAULT NULL,
  `height` varchar(10) DEFAULT NULL,
  `weight` varchar(10) DEFAULT NULL,
  `health_problems` text DEFAULT NULL,
  `elementary_school` varchar(255) DEFAULT NULL,
  `elementary_year_graduated` int(11) DEFAULT NULL,
  `elementary_honors` text DEFAULT NULL,
  `secondary_school` varchar(255) DEFAULT NULL,
  `secondary_year_graduated` int(11) DEFAULT NULL,
  `secondary_honors` text DEFAULT NULL,
  `college` varchar(255) DEFAULT NULL,
  `college_year_graduated` int(11) DEFAULT NULL,
  `college_honors` text DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `inclusive_date` varchar(50) DEFAULT NULL,
  `company_address_work_experience` varchar(255) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `ref_name` varchar(100) DEFAULT NULL,
  `ref_position` varchar(100) DEFAULT NULL,
  `ref_address` varchar(255) DEFAULT NULL,
  `ref_contact` varchar(20) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_address` varchar(255) DEFAULT NULL,
  `emergency_contact_no` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile_information`
--

INSERT INTO `profile_information` (`id`, `internID`, `adminID`, `first_name`, `middle_name`, `last_name`, `course_year_sec`, `school`, `gender`, `age`, `current_address`, `provincial_address`, `tel_no`, `mobile_no`, `birth_place`, `birth_date`, `religion`, `email`, `civil_status`, `citizenship`, `hr_manager`, `faciID`, `facilitator_email`, `start_shift`, `end_shift`, `required_hours`, `date_start`, `date_end`, `company`, `company_address`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `blood_type`, `height`, `weight`, `health_problems`, `elementary_school`, `elementary_year_graduated`, `elementary_honors`, `secondary_school`, `secondary_year_graduated`, `secondary_honors`, `college`, `college_year_graduated`, `college_honors`, `company_name`, `position`, `inclusive_date`, `company_address_work_experience`, `skills`, `ref_name`, `ref_position`, `ref_address`, `ref_contact`, `emergency_name`, `emergency_address`, `emergency_contact_no`, `created_at`) VALUES
(12, 'cot234', 1, 'cot234', '', '', '', NULL, NULL, 0, '', '', '', '', '', '0000-00-00', '', '', '', '', 'JOAN SABELLO', 'cotfa', NULL, '08:00:00', '16:00:00', NULL, NULL, NULL, 'CONCENTRIC', 'SABANG', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-11-10 08:36:03'),
(13, 'cot345', 1, '', '', '', '', NULL, NULL, 0, '', '', '', '', '', '0000-00-00', '', '', '', '', 'JOAN SABELLO', 'faco12', NULL, NULL, NULL, NULL, NULL, NULL, 'CONCENTRIC', 'SABANG', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-11-10 08:54:18'),
(14, 'cotin3', 1, '', '', '', '', NULL, NULL, 0, '', '', '', '', '', '0000-00-00', '', '', '', '', 'JOAN SABELLO', 'faco12', NULL, NULL, NULL, NULL, NULL, NULL, 'CONCENTRIC', 'SABANG', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-11-10 10:15:19'),
(15, 'intcot', 1, 'intern1name', '', '', '', NULL, NULL, 0, '', '', '', '', '', '0000-00-00', '', '', '', '', 'JOAN SABELLO', 'faco12', NULL, NULL, NULL, NULL, NULL, NULL, 'CONCENTRIC', 'SABANG', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-11-10 14:47:28'),
(16, '678905', 1, 'atako', '', '', '', NULL, NULL, 0, '', '', '', '', '', '0000-00-00', '', '', '', '', 'JOAN SABELLO', 'cotfa', NULL, NULL, NULL, NULL, NULL, NULL, 'CONCENTRIC', 'SABANG', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-11-10 15:16:09'),
(17, 'kyle', 2, '', '', '', '', 'ctu', NULL, 0, '', '', '', '', '', '0000-00-00', '', '', '', '', '', 'bshmf', 'faci1@gmail.com', '00:36:00', '00:37:00', 700, '2024-11-12', '2024-11-13', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-11-11 16:36:58'),
(18, '123456', 1, 'joan', '', '', '', '', NULL, 0, '', '', '', '', '', '0000-00-00', '', '', '', '', 'JOAN SABELLO', 'cotfa', '', '00:00:00', '00:00:00', 0, '0000-00-00', '0000-00-00', 'CONCENTRIC', 'SABANG', '', '', '', '', '', '', '', '', '', 0, '', '', 0, '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '2024-11-13 09:37:51');

-- --------------------------------------------------------

--
-- Table structure for table `time_logs`
--

CREATE TABLE `time_logs` (
  `id` int(11) NOT NULL,
  `internID` varchar(255) NOT NULL,
  `adminID` int(11) NOT NULL,
  `faciID` varchar(50) NOT NULL,
  `login_time` datetime DEFAULT NULL,
  `break_time` datetime DEFAULT NULL,
  `back_to_work_time` datetime DEFAULT NULL,
  `task` varchar(255) DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `status` varchar(255) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_logs`
--

INSERT INTO `time_logs` (`id`, `internID`, `adminID`, `faciID`, `login_time`, `break_time`, `back_to_work_time`, `task`, `logout_time`, `status`) VALUES
(5, 'cot234', 1, 'cotfa', '2024-11-10 16:49:09', '2024-11-10 16:49:40', '2024-11-10 16:49:09', '2024-11-10 16:49:09', '2024-11-10 16:49:09', 'Approved'),
(33, 'cot345', 1, 'faco12', '2024-11-10 22:39:31', '2024-11-10 22:39:38', '2024-11-10 22:39:49', 'cotfatask', '2024-11-10 22:40:04', 'Pending'),
(34, 'intcot', 2, 'faco12', '2024-11-10 22:47:33', '2024-11-10 22:48:39', '2024-11-10 22:59:18', '', NULL, 'Pending'),
(40, '678905', 1, 'cotfa', '2024-11-10 23:47:23', '2024-11-10 23:47:25', '2024-11-10 23:47:27', 'cotfatask', '2024-11-10 23:47:31', 'Approved'),
(41, 'cot234', 1, 'cotfa', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 'Declined'),
(44, '123456', 1, 'cotfa', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 'Declined'),
(45, 'cot234', 1, 'cotfa', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 'Approved'),
(100, '123456', 1, 'cotfa', '2024-11-17 21:42:51', '2024-11-17 21:42:53', '2024-11-17 21:42:55', '', NULL, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `adminID` int(11) NOT NULL,
  `Uname` text NOT NULL,
  `Upass` text NOT NULL,
  `admin_profile` blob DEFAULT NULL,
  `Roles` varchar(191) DEFAULT NULL,
  `Firstname` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`adminID`, `Uname`, `Upass`, `admin_profile`, `Roles`, `Firstname`) VALUES
(1, 'admin1', 'admin1', 0x62386339623535633231326430663038363432636163386530333637666639632e6a7067, 'School Intern Coordinator', 'Gaco, Rodelyn'),
(2, 'admin2', 'duterte', 0x33666236653330313639303131646266613033363537663163326632376565342e706e67, 'BSHM Intern Coordinator', 'Duterte, Sarah');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcementID`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `facacc`
--
ALTER TABLE `facacc`
  ADD PRIMARY KEY (`faciID`),
  ADD UNIQUE KEY `faciID` (`faciID`);

--
-- Indexes for table `intacc`
--
ALTER TABLE `intacc`
  ADD PRIMARY KEY (`internID`),
  ADD UNIQUE KEY `internID` (`internID`);

--
-- Indexes for table `profile_information`
--
ALTER TABLE `profile_information`
  ADD PRIMARY KEY (`id`),
  ADD KEY `internID` (`internID`),
  ADD KEY `adminID` (`adminID`),
  ADD KEY `faciID` (`faciID`);

--
-- Indexes for table `time_logs`
--
ALTER TABLE `time_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_faciID` (`faciID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`adminID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `profile_information`
--
ALTER TABLE `profile_information`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `time_logs`
--
ALTER TABLE `time_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`adminID`) REFERENCES `users` (`adminID`);

--
-- Constraints for table `time_logs`
--
ALTER TABLE `time_logs`
  ADD CONSTRAINT `fk_faciID` FOREIGN KEY (`faciID`) REFERENCES `profile_information` (`faciID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
