-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2024 at 09:52 PM
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
(22, 1, 'Cot Announcement', 'Cot Announcement', './uploaded_files/maricel SABELLO - Quiz # 1.pdf', '2024-10-29 05:56:35'),
(24, 1, 'Cot Announcement3', 'Cot Announcement3', './uploaded_files/test2.pdf', '2024-10-29 06:00:20'),
(25, 1, 'Cot Announcement2', 'Cot Announcement2', './uploaded_files/maricel SABELLO - Quiz # 1.pdf', '2024-10-29 06:00:31'),
(26, 1, 'Cot Announcement4', 'Cot Announcement4', './uploaded_files/test2.pdf', '2024-10-29 06:01:07'),
(28, 2, 'test4', 'test4', './uploaded_files/maricel SABELLO - Quiz # 1.pdf', '2024-10-29 06:46:53'),
(29, 2, 'admin2 annoucement', 'admin2 annoucement text', './uploaded_files/Doc1.docx', '2024-11-01 06:48:12'),
(30, 2, 'admin2 annoucement', 'admin2 annoucement', './uploaded_files/maricel SABELLO - Quiz # 1.pdf', '2024-11-01 06:48:33'),
(31, 1, 'debebaranntitle', 'debebaranntext', './uploaded_files/24058497 (1).pdf', '2024-11-02 07:06:42'),
(32, 1, 'debebaranntitle', 'debebaranntext', './uploaded_files/24058497 (1).pdf', '2024-11-02 07:06:58'),
(33, 1, 'debebaranntitle1', 'debebaranntitle1', './uploaded_files/24058241 (2).pdf', '2024-11-02 07:20:40'),
(34, 1, 'debebaranntitle1', 'debebaranntitle1', './uploaded_files/24058241 (2).pdf', '2024-11-02 07:23:13'),
(35, 1, 'debebaranntitle1', 'debebaranntitle1', './uploaded_files/24058241 (2).pdf', '2024-11-02 07:23:47'),
(36, 1, 'debebaranntitle2', 'debebaranntitle2', './uploaded_files/24058241 (2).pdf', '2024-11-02 07:23:58'),
(37, 1, 'debebaranntitle2', 'debebaranntitle2', './uploaded_files/24058241 (2).pdf', '2024-11-02 07:25:38'),
(38, 1, 'debebaranntitle2', 'debebaranntitle2', './uploaded_files/24058241 (2).pdf', '2024-11-02 07:26:34'),
(39, 1, 'debebaranntitle2', 'debebaranntitle2', './uploaded_files/24058241 (2).pdf', '2024-11-02 07:26:47');

-- --------------------------------------------------------

--
-- Table structure for table `developers`
--

CREATE TABLE `developers` (
  `dev_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `developers`
--

INSERT INTO `developers` (`dev_id`, `username`, `password`) VALUES
(1, 'developer', 'developerako');

-- --------------------------------------------------------

--
-- Table structure for table `facacc`
--

CREATE TABLE `facacc` (
  `faciID` varchar(10) NOT NULL,
  `faciPass` varchar(10) NOT NULL,
  `adminID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facacc`
--

INSERT INTO `facacc` (`faciID`, `faciPass`, `adminID`) VALUES
('67896', '67896', 0),
('ert', 'ertert', 2),
('fac1', 'fac123', 1),
('fac134', 'fac134', 2),
('fac2', 'fac234', 1),
('inter', 'inter', 0);

-- --------------------------------------------------------

--
-- Table structure for table `intacc`
--

CREATE TABLE `intacc` (
  `internID` varchar(10) NOT NULL,
  `InternPass` varchar(255) NOT NULL,
  `adminID` int(11) DEFAULT NULL,
  `internName` varchar(255) DEFAULT NULL,
  `profileImage` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `requiredHours` int(11) DEFAULT NULL,
  `dateStarted` date DEFAULT NULL,
  `dateEnded` date DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `facilitatorName` varchar(255) DEFAULT NULL,
  `courseSection` varchar(255) DEFAULT NULL,
  `facilitatorEmail` varchar(255) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `companyName` varchar(255) DEFAULT NULL,
  `shiftStart` time DEFAULT NULL,
  `shiftEnd` time DEFAULT NULL,
  `facilitatorID` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `intacc`
--

INSERT INTO `intacc` (`internID`, `InternPass`, `adminID`, `internName`, `profileImage`, `email`, `requiredHours`, `dateStarted`, `dateEnded`, `dob`, `facilitatorName`, `courseSection`, `facilitatorEmail`, `gender`, `companyName`, `shiftStart`, `shiftEnd`, `facilitatorID`) VALUES
('Ad2int', 'Ad2int', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('cot123', 'cot123', 1, 'intern1', 'uploaded_files/1730376180_formalchoy.png', 'intern1@gmail.com', 700, '2024-09-30', '2024-10-31', '2001-10-31', 'faci1', 'BSIT 4A NIGHT', 'faci1@gmail.com', 'male', 'company1', '07:35:00', '19:35:00', 'faci0'),
('cot234', 'cot234', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('inthm1', 'inthm1', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `time_logs`
--

CREATE TABLE `time_logs` (
  `internID` int(11) NOT NULL,
  `adminID` int(11) NOT NULL,
  `task` varchar(255) NOT NULL,
  `login_time` datetime DEFAULT NULL,
  `break_time` datetime DEFAULT NULL,
  `back_to_work_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'admin1', 'debebar', 0x31383034383437646663333764636230306562326466306264303834333965302e706e67, 'School Intern Coordinator', 'Debebar, Maricel'),
(2, 'admin2', 'maricel', 0x32616666643235613832353462353030356561356337346665303933633034642e676966, 'BSHM Intern Coordinator', 'Duterte, Sarah');

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
-- Indexes for table `developers`
--
ALTER TABLE `developers`
  ADD PRIMARY KEY (`dev_id`),
  ADD UNIQUE KEY `username` (`username`);

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
  MODIFY `announcementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `developers`
--
ALTER TABLE `developers`
  MODIFY `dev_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
