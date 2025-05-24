-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 06:05 PM
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
-- Database: `mypetakom`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendancelist`
--

CREATE TABLE `attendancelist` (
  `listID` int(10) NOT NULL,
  `slotID` int(10) NOT NULL,
  `studentID` varchar(7) NOT NULL,
  `checkInTime` time(6) NOT NULL,
  `geolocation` varchar(100) NOT NULL,
  `listStatus` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendanceslot`
--

CREATE TABLE `attendanceslot` (
  `slotID` int(10) NOT NULL,
  `eventID` varchar(10) NOT NULL,
  `slotTime` time(6) NOT NULL,
  `slotDate` date NOT NULL,
  `QRCode` int(11) NOT NULL,
  `coordinate` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `eventID` varchar(10) NOT NULL,
  `staffID` varchar(10) NOT NULL,
  `eventName` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  `eventDate` date NOT NULL,
  `eventLocation` varchar(100) NOT NULL,
  `eventStatus` tinyint(2) NOT NULL,
  `approvalLetter` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eventcommittee`
--

CREATE TABLE `eventcommittee` (
  `committeeID` int(7) NOT NULL,
  `eventID` varchar(10) NOT NULL,
  `studentID` varchar(7) NOT NULL,
  `committeePosition` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `membershipID` int(10) NOT NULL,
  `studentID` varchar(7) NOT NULL,
  `staffID` varchar(10) NOT NULL,
  `memberStatus` tinyint(1) NOT NULL,
  `appliedDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meritclaim`
--

CREATE TABLE `meritclaim` (
  `claimID` int(10) NOT NULL,
  `eventID` varchar(10) NOT NULL,
  `role` varchar(10) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staffID` varchar(10) NOT NULL,
  `staffName` varchar(100) NOT NULL,
  `staffEmail` varchar(100) NOT NULL,
  `staffPassword` varchar(50) NOT NULL,
  `staffRole` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studentID` varchar(7) NOT NULL,
  `studentName` varchar(100) NOT NULL,
  `studentEmail` varchar(100) NOT NULL,
  `studentCard` varchar(200) NOT NULL,
  `studentPhoneNum` varchar(15) NOT NULL,
  `password` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendancelist`
--
ALTER TABLE `attendancelist`
  ADD PRIMARY KEY (`listID`),
  ADD KEY `slotID` (`slotID`),
  ADD KEY `studentID` (`studentID`);

--
-- Indexes for table `attendanceslot`
--
ALTER TABLE `attendanceslot`
  ADD PRIMARY KEY (`slotID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`eventID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `eventcommittee`
--
ALTER TABLE `eventcommittee`
  ADD PRIMARY KEY (`committeeID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `studentID` (`studentID`);

--
-- Indexes for table `membership`
--
ALTER TABLE `membership`
  ADD PRIMARY KEY (`membershipID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `meritclaim`
--
ALTER TABLE `meritclaim`
  ADD PRIMARY KEY (`claimID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staffID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`studentID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendancelist`
--
ALTER TABLE `attendancelist`
  ADD CONSTRAINT `attendancelist_ibfk_1` FOREIGN KEY (`slotID`) REFERENCES `attendanceslot` (`slotID`),
  ADD CONSTRAINT `attendancelist_ibfk_2` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`);

--
-- Constraints for table `attendanceslot`
--
ALTER TABLE `attendanceslot`
  ADD CONSTRAINT `attendanceslot_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `event` (`eventID`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

--
-- Constraints for table `eventcommittee`
--
ALTER TABLE `eventcommittee`
  ADD CONSTRAINT `eventcommittee_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `event` (`eventID`),
  ADD CONSTRAINT `eventcommittee_ibfk_2` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`);

--
-- Constraints for table `membership`
--
ALTER TABLE `membership`
  ADD CONSTRAINT `membership_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`),
  ADD CONSTRAINT `membership_ibfk_2` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

--
-- Constraints for table `meritclaim`
--
ALTER TABLE `meritclaim`
  ADD CONSTRAINT `meritclaim_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `event` (`eventID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
