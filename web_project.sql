-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2025 at 10:20 AM
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
-- Database: `web_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `adminID` int(10) NOT NULL,
  `staffID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advisor`
--

CREATE TABLE `advisor` (
  `advisorID` int(10) NOT NULL,
  `staffID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendancelist`
--

CREATE TABLE `attendancelist` (
  `listID` int(10) NOT NULL,
  `slotID` int(10) NOT NULL,
  `studentID` int(10) NOT NULL,
  `checkInTime` time(6) NOT NULL,
  `geolocation` varchar(10) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendanceslot`
--

CREATE TABLE `attendanceslot` (
  `slotID` int(10) NOT NULL,
  `eventID` int(10) NOT NULL,
  `advisorID` int(10) NOT NULL,
  `slotTime` time(6) NOT NULL,
  `QRCode` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `eventID` int(11) NOT NULL,
  `eventName` varchar(100) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `eventDate` date NOT NULL,
  `eventLocation` varchar(100) NOT NULL,
  `eventStatus` varchar(100) NOT NULL,
  `approvalLetter` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eventcommittee`
--

CREATE TABLE `eventcommittee` (
  `committeeID` int(7) NOT NULL,
  `advisorID` int(10) NOT NULL,
  `eventID` int(10) NOT NULL,
  `committeePosition` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `membershipID` int(10) NOT NULL,
  `studentID` int(7) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `appliedDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meritclaim`
--

CREATE TABLE `meritclaim` (
  `claimID` int(10) NOT NULL,
  `eventID` int(10) NOT NULL,
  `role` varchar(10) NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staffID` int(10) NOT NULL,
  `staffName` varchar(100) NOT NULL,
  `staffEmail` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studentID` int(7) NOT NULL,
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
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`adminID`),
  ADD KEY `staffID` (`staffID`);

--
-- Indexes for table `advisor`
--
ALTER TABLE `advisor`
  ADD PRIMARY KEY (`advisorID`),
  ADD KEY `staffID` (`staffID`);

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
  ADD KEY `eventID` (`eventID`),
  ADD KEY `advisorID` (`advisorID`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`eventID`);

--
-- Indexes for table `eventcommittee`
--
ALTER TABLE `eventcommittee`
  ADD PRIMARY KEY (`committeeID`),
  ADD KEY `advisorID` (`advisorID`),
  ADD KEY `eventID` (`eventID`);

--
-- Indexes for table `membership`
--
ALTER TABLE `membership`
  ADD PRIMARY KEY (`membershipID`),
  ADD KEY `membership_ibfk_1` (`studentID`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `adminID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `advisor`
--
ALTER TABLE `advisor`
  MODIFY `advisorID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendancelist`
--
ALTER TABLE `attendancelist`
  MODIFY `listID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendanceslot`
--
ALTER TABLE `attendanceslot`
  MODIFY `slotID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `eventID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eventcommittee`
--
ALTER TABLE `eventcommittee`
  MODIFY `committeeID` int(7) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membership`
--
ALTER TABLE `membership`
  MODIFY `membershipID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meritclaim`
--
ALTER TABLE `meritclaim`
  MODIFY `claimID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staffID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `studentID` int(7) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administrator`
--
ALTER TABLE `administrator`
  ADD CONSTRAINT `administrator_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

--
-- Constraints for table `advisor`
--
ALTER TABLE `advisor`
  ADD CONSTRAINT `advisor_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`);

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
  ADD CONSTRAINT `attendanceslot_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `event` (`eventID`),
  ADD CONSTRAINT `attendanceslot_ibfk_2` FOREIGN KEY (`advisorID`) REFERENCES `advisor` (`advisorID`);

--
-- Constraints for table `eventcommittee`
--
ALTER TABLE `eventcommittee`
  ADD CONSTRAINT `eventcommittee_ibfk_1` FOREIGN KEY (`advisorID`) REFERENCES `advisor` (`advisorID`),
  ADD CONSTRAINT `eventcommittee_ibfk_2` FOREIGN KEY (`eventID`) REFERENCES `event` (`eventID`);

--
-- Constraints for table `membership`
--
ALTER TABLE `membership`
  ADD CONSTRAINT `membership_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `membership` (`membershipID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `meritclaim`
--
ALTER TABLE `meritclaim`
  ADD CONSTRAINT `meritclaim_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `event` (`eventID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
