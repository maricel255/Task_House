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
-- Table structure for table `facacc`
--

CREATE TABLE `facacc` (
  `faciID` varchar(5) NOT NULL,
  `faciPass` varchar(5) NOT NULL
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
-- Indexes for table `facacc`
--
ALTER TABLE `facacc`
  ADD PRIMARY KEY (`faciID`);

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
