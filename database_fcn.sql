-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2026 at 11:40 AM
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
-- Database: `database_fcn`
--

-- --------------------------------------------------------

--
-- Table structure for table `repair_tickets`
--

CREATE TABLE `repair_tickets` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL COMMENT 'รหัส Ticket อ้างอิง (เช่น ticket_66209010001)',
  `building` varchar(100) NOT NULL COMMENT 'ตึก/อาคารที่เกิดปัญหา',
  `floor` varchar(50) NOT NULL COMMENT 'ชั้น/ห้องที่เกิดปัญหา',
  `description` text NOT NULL COMMENT 'รายละเอียดปัญหา',
  `image_path` varchar(255) DEFAULT NULL COMMENT 'พาร์ทไฟล์รูปภาพ (เก็บในโฟลเดอร์ uploads/)',
  `status` varchar(50) DEFAULT 'รอดำเนินการ' COMMENT 'สถานะ: รอดำเนินการ, กำลังดำเนินการ, เสร็จสิ้น, ยกเลิก',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'วันที่แจ้งซ่อม'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_tickets`
--

INSERT INTO `repair_tickets` (`id`, `student_id`, `building`, `floor`, `description`, `image_path`, `status`, `created_at`) VALUES
(2, 'ticket_68319010001', 'อาคารหกล้ม', '67', 'ไฟดูด', 'uploads/repair_68319010001_1.png', 'รอดำเนินการ', '2026-09-11 05:03:15'),
(3, 'ticket_68319010002', 'ตึกอำนวยการ', 'ชั้น 2', 'เครื่องรีบูตเอง', 'uploads/repair_68319010002_1.jpg', 'รอดำเนินการ', '2026-09-11 09:12:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `student_id` varchar(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`student_id`, `name`, `lastname`, `password`, `department`, `role`) VALUES
('68319010001', 'สุวิจักขณ์', 'พฤทธิ์จิรัฐติกาล', '$2y$10$/Hkr47a5EziodYfVjBW6yeYyn1WjZrOD2fntj987VxpGAgQnHezua', 'Information Technology', 'admin'),
('68319010002', 'สุวิจักขณ์', 'พฤทธิ์จิรัฐติกาล', '$2y$10$gW3/zTF3gwgZWJ8LrOF8ce8hdPX/PiWpm/.3wSQ.7BVHxOHrPG4Wq', 'Computer Science', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `repair_tickets`
--
ALTER TABLE `repair_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `repair_tickets`
--
ALTER TABLE `repair_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
