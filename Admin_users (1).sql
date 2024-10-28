-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2024 at 12:24 AM
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
(3, 1, 'admin1 annoucement', 'admin1 annoucement', './uploaded_files/1804847dfc37dcb00eb2df0bd08439e0.png', '2024-10-27 05:13:40'),
(4, 1, 'admin2 annoucement', 'admin2 annoucement', './uploaded_files/c05c9827039cf159c12ae94d6503523d.png', '2024-10-27 05:19:52'),
(5, 1, 'test3 admin2', 'test3 admin2', './uploaded_files/maricel SABELLO - Quiz # 1.pdf', '2024-10-27 06:05:37'),
(6, 1, 'test4', 'dasfdsaffdf,dfgalgnddkfgmdfpg;jdkfgnfdlgjifgdfhgbdf.ghdfgf.kdgsaf\r\ndasfdsaffdf,dfgalgnddkfgmdfpg;jdkfgnfdlgjifgdfhgbdf.ghdfgf.kdgsaf\r\ndasfdsaffdf,dfgalgnddkfgmdfpg;jdkfgnfdlgjifgdfhgbdf.ghdfgf.kdgsaf', './uploaded_files/436335911_457539703276308_6121920634856150492_n.jpg', '2024-10-27 06:12:16'),
(7, 1, 'test4', 'dsadas\r\ndasddasd\r\nasfdsf\r\nfafsdfsd\r\nfa\r\ndsadas\r\ndasddasd\r\nasfdsf\r\nfafsdfsd\r\nfa', './uploaded_files/BACK_ICON.png', '2024-10-27 06:14:22'),
(8, 1, 'test1 with pdf', 'test1 with pdf', './uploaded_files/test2.pdf', '2024-10-27 15:27:56'),
(9, 1, 'gaco anouce', 'gaco anouce', './uploaded_files/gaco.pdf', '2024-10-27 19:57:44'),
(10, 1, 'test2 ni gaco', 'metting at lobby', './uploaded_files/gaco.pdf', '2024-10-27 19:59:09'),
(11, 1, 'test3with', 'test3with', './uploaded_files/gaco.pdf', '2024-10-27 21:13:22'),
(12, 1, 'Annoucment1', 'Annoucment1 text', './uploaded_files/gaco.pdf', '2024-10-27 21:59:33');

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

INSERT INTO `facacc` (`faciID`, `faciPass`) VALUES
('67896', '67896'),
('inter', 'inter');

-- --------------------------------------------------------

--
-- Table structure for table `intacc`
--

CREATE TABLE `intacc` (
  `internID` varchar(10) NOT NULL,
  `InternPass` varchar(10) NOT NULL,
  `adminID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `intacc`
--

INSERT INTO `intacc` (`internID`, `InternPass`, `adminID`) VALUES
('123456', '123456', 2),
('intern1', 'intern1', 1),
('qwerty', 'qwerty', 2);

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
  MODIFY `announcementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
