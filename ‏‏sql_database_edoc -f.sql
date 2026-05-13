-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 05 يونيو 2025 الساعة 22:57
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sql_database_edoc`
--

-- --------------------------------------------------------

--
-- بنية الجدول `admin`
--

CREATE TABLE `admin` (
  `aemail` varchar(255) NOT NULL,
  `apassword` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admin`
--

INSERT INTO `admin` (`aemail`, `apassword`) VALUES
('admin@edoc.com', '123');

-- --------------------------------------------------------

--
-- بنية الجدول `ambulance_requests`
--

CREATE TABLE `ambulance_requests` (
  `id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `emergency_type` text NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `ambulance_requests`
--

INSERT INTO `ambulance_requests` (`id`, `user_email`, `patient_name`, `phone`, `address`, `location`, `emergency_type`, `request_time`) VALUES
(1, 'patient@edoc.com', 'mostfa hatem', '01014106072', 'alzarka', '', 'تعبان', '2025-03-22 09:12:37'),
(2, 'patient@edoc.com', 'mostfa hatem', '01014106072', 'alzarka', '', 'عيان', '2025-03-22 09:15:22'),
(3, 'patient@edoc.com', 'mostfa hatem', '01014106072', 'alzarka', '', 'عيان', '2025-03-22 10:42:03'),
(4, 'mostfahatem668@gmail.com', 'mostfa hatem fawze mohamed', '01014106072', 'alzarka', '', 'i have cold', '2025-05-13 23:55:20'),
(5, 'mostfahatem668@gmail.com', 'mostfa', '01014106072', 'zarka', 'zarka', 'تعبان', '2025-05-24 22:49:08');

-- --------------------------------------------------------

--
-- بنية الجدول `appointment`
--

CREATE TABLE `appointment` (
  `appoid` int(11) NOT NULL,
  `pid` int(10) DEFAULT NULL,
  `apponum` int(3) DEFAULT NULL,
  `scheduleid` int(10) DEFAULT NULL,
  `appodate` date DEFAULT NULL,
  `status` enum('pending','done') DEFAULT 'pending',
  `report` text DEFAULT NULL,
  `pdf_report` varchar(255) DEFAULT NULL,
  `type` enum('doctor','radiology') NOT NULL DEFAULT 'doctor',
  `report_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `report_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `diagnosis` varchar(255) DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `followup_date` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- إرجاع أو استيراد بيانات الجدول `appointment`
--

INSERT INTO `appointment` (`appoid`, `pid`, `apponum`, `scheduleid`, `appodate`, `status`, `report`, `pdf_report`, `type`, `report_created_at`, `report_updated_at`, `diagnosis`, `treatment`, `followup_date`) VALUES
(4, 1, 2, 1, '2024-12-21', 'done', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(3, 1, 1, 1, '2024-12-19', 'done', 'mostfa', NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:03:58', NULL, NULL, NULL),
(10, 1, 5, 1, '2025-02-27', 'done', 'wdc', NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 18:25:25', 'bard', 'wo', '2025-04-01'),
(6, 1, 4, 1, '2024-12-24', 'done', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(30, 1, 23, 1, '2025-04-15', 'done', 'csc', NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 18:23:29', 'bard', 'mas', '2025-04-01'),
(15, 15, 10, 1, '2025-02-28', 'done', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(16, 15, 11, 1, '2025-02-28', 'done', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-05-13 23:24:39', NULL, NULL, NULL),
(18, 15, 13, 1, '2025-02-28', 'done', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-05-24 22:59:21', NULL, NULL, NULL),
(19, 15, 14, 1, '2025-02-28', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(20, 15, 15, 1, '2025-02-28', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(22, 15, 17, 1, '2025-02-28', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(23, 15, 18, 1, '2025-02-28', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(24, 15, 19, 1, '2025-02-28', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(25, 1, 20, 1, '2025-03-05', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(26, 1, 20, 1, '2025-03-05', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(27, 1, 22, 1, '2025-03-06', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(28, 1, 23, 1, '2025-03-22', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(32, 1, 25, 1, '2025-04-15', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(33, 1, 25, 1, '2025-04-15', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(34, 1, 27, 1, '2025-04-15', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(35, 1, 28, 1, '2025-04-15', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(36, 1, 29, 1, '2025-04-15', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(37, 1, 30, 1, '2025-04-15', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(38, 14, 31, 1, '2025-04-15', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(39, 15, 32, 1, '2025-04-15', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(62, 16, 41, 1, '2025-05-14', 'pending', NULL, NULL, 'doctor', '2025-05-13 23:45:52', '2025-05-13 23:45:52', NULL, NULL, NULL),
(61, 16, 38, 1, '2025-05-14', 'done', NULL, 'report_61_1748213142.pdf', 'doctor', '2025-05-13 23:32:25', '2025-05-25 22:45:42', NULL, NULL, NULL),
(60, 16, 38, 1, '2025-05-14', 'done', NULL, NULL, 'doctor', '2025-05-13 23:32:21', '2025-05-13 23:36:27', NULL, NULL, NULL),
(43, 16, 1, 2, '2025-04-16', 'pending', NULL, NULL, 'doctor', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(44, 16, 1, 1, '2025-04-16', 'pending', NULL, NULL, 'radiology', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(45, 16, 1, 2, '2025-04-16', 'pending', NULL, NULL, 'radiology', '2025-04-28 00:01:59', '2025-04-28 00:01:59', NULL, NULL, NULL),
(47, 1, 30, 1, '2025-05-02', 'pending', NULL, NULL, 'doctor', '2025-05-02 14:46:00', '2025-05-02 14:46:00', NULL, NULL, NULL),
(48, 1, 30, 1, '2025-05-02', 'pending', NULL, NULL, 'doctor', '2025-05-02 14:46:36', '2025-05-02 14:46:36', NULL, NULL, NULL),
(49, 1, 32, 1, '2025-05-02', 'pending', NULL, NULL, 'doctor', '2025-05-02 14:54:27', '2025-05-02 14:54:27', NULL, NULL, NULL),
(50, 1, 33, 1, '2025-05-02', 'pending', NULL, NULL, 'doctor', '2025-05-02 14:57:24', '2025-05-02 14:57:24', NULL, NULL, NULL),
(51, 1, 34, 1, '2025-05-02', 'pending', NULL, NULL, 'doctor', '2025-05-02 15:43:36', '2025-05-02 15:43:36', NULL, NULL, NULL),
(52, 1, 35, 1, '2025-05-02', 'pending', NULL, NULL, 'doctor', '2025-05-02 15:44:24', '2025-05-02 15:44:24', NULL, NULL, NULL),
(59, 16, 38, 1, '2025-05-14', 'done', NULL, 'report_59_1748127838.pdf', 'doctor', '2025-05-13 23:31:58', '2025-05-24 23:03:58', NULL, NULL, NULL),
(54, 1, 37, 1, '2025-05-04', 'pending', NULL, NULL, 'doctor', '2025-05-04 00:24:44', '2025-05-04 00:24:44', NULL, NULL, NULL),
(55, 1, 38, 1, '2025-05-04', 'pending', NULL, NULL, 'doctor', '2025-05-04 01:08:11', '2025-05-04 01:08:11', NULL, NULL, NULL),
(56, 1, 39, 1, '2025-05-04', 'pending', NULL, NULL, 'doctor', '2025-05-04 01:36:26', '2025-05-04 01:36:26', NULL, NULL, NULL),
(57, 1, 40, 1, '2025-05-08', 'pending', NULL, NULL, 'doctor', '2025-05-08 10:38:34', '2025-05-08 10:38:34', NULL, NULL, NULL),
(58, 16, 41, 1, '2025-05-14', 'done', NULL, NULL, 'doctor', '2025-05-13 23:10:52', '2025-05-13 23:36:27', NULL, NULL, NULL),
(63, 16, 42, 1, '2025-05-14', 'done', NULL, NULL, 'doctor', '2025-05-13 23:46:48', '2025-05-13 23:48:13', NULL, NULL, NULL),
(64, 19, 43, 1, '2025-05-25', 'pending', NULL, NULL, 'doctor', '2025-05-24 22:33:34', '2025-05-24 22:33:34', NULL, NULL, NULL),
(65, 16, 44, 1, '2025-05-25', 'pending', NULL, NULL, 'doctor', '2025-05-24 22:34:16', '2025-05-24 22:34:16', NULL, NULL, NULL),
(66, 16, 3, 1, '2025-05-26', 'pending', NULL, NULL, 'radiology', '2025-05-25 19:58:34', '2025-05-25 19:58:34', NULL, NULL, NULL),
(67, 20, 45, 1, '2025-06-01', 'pending', NULL, NULL, 'doctor', '2025-05-26 00:01:00', '2025-05-26 00:01:00', NULL, NULL, NULL),
(68, 21, 46, 1, '2025-06-02', 'pending', NULL, NULL, 'doctor', '2025-05-26 00:01:00', '2025-05-26 00:01:00', NULL, NULL, NULL),
(69, 22, 47, 1, '2025-06-03', 'pending', NULL, NULL, 'doctor', '2025-05-26 00:01:00', '2025-05-26 00:01:00', NULL, NULL, NULL),
(70, 20, 48, 1, '2025-06-05', 'pending', NULL, NULL, 'radiology', '2025-05-26 00:01:00', '2025-05-26 00:01:00', NULL, NULL, NULL),
(71, 21, 49, 1, '2025-06-06', 'pending', NULL, NULL, '', '2025-05-26 00:01:00', '2025-05-26 00:01:00', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `doctor`
--

CREATE TABLE `doctor` (
  `docid` int(11) NOT NULL,
  `docemail` varchar(255) DEFAULT NULL,
  `docname` varchar(255) DEFAULT NULL,
  `docpassword` varchar(255) DEFAULT NULL,
  `docnic` varchar(15) DEFAULT NULL,
  `doctel` varchar(15) DEFAULT NULL,
  `specialties` int(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- إرجاع أو استيراد بيانات الجدول `doctor`
--

INSERT INTO `doctor` (`docid`, `docemail`, `docname`, `docpassword`, `docnic`, `doctel`, `specialties`) VALUES
(1, 'doctor@edoc.com', 'Test Doctor', '123', '000000000', '0110000000', 1),
(2, 'doctor1@edoc.com', 'mohamed ', '123', '443322', '01060283920', 5),
(3, 'doctor2@edoc.com', 'mostfa', '123', '443322', '01060283920', 6),
(4, 'doctor3@edoc.com', 'hatem', '123', '11223344', '01060283921', 29),
(5, 'dr.smith@edoc.com', 'Dr. John Smith', 'doc123', 'D12345678', '01112223334', 5),
(6, 'dr.jones@edoc.com', 'Dr. Sarah Jones', 'doc123', 'D23456789', '01112223335', 14),
(7, 'dr.wilson@edoc.com', 'Dr. Michael Wilson', 'doc123', 'D34567890', '01112223336', 29),
(8, 'dr.lee@edoc.com', 'Dr. Emily Lee', 'doc123', 'D45678901', '01112223337', 32),
(9, 'dr.khan@edoc.com', 'Dr. Ali Khan', 'doc123', 'D56789012', '01112223338', 16);

-- --------------------------------------------------------

--
-- بنية الجدول `drug_interactions`
--

CREATE TABLE `drug_interactions` (
  `interaction_id` int(11) NOT NULL,
  `med_id_1` decimal(6,0) NOT NULL,
  `med_id_2` decimal(6,0) NOT NULL,
  `severity` enum('mild','moderate','severe') NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- إرجاع أو استيراد بيانات الجدول `drug_interactions`
--

INSERT INTO `drug_interactions` (`interaction_id`, `med_id_1`, `med_id_2`, `severity`, `description`, `created_at`) VALUES
(1, 1, 2, 'severe', 'May cause severe bleeding when taken together', '2025-05-24 00:23:41'),
(2, 1, 3, 'moderate', 'May increase risk of stomach bleeding', '2025-05-24 00:23:41'),
(3, 2, 4, 'mild', 'May cause mild drowsiness', '2025-05-24 00:23:41'),
(4, 3, 5, 'severe', 'May cause serious heart problems', '2025-05-24 00:23:41'),
(5, 4, 6, 'moderate', 'May affect blood pressure', '2025-05-24 00:23:41');

-- --------------------------------------------------------

--
-- بنية الجدول `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('paid','unpaid','overdue') DEFAULT 'unpaid',
  `services` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `patient_id`, `appointment_id`, `amount`, `issue_date`, `due_date`, `status`, `services`) VALUES
(1, 1, 3, 150.00, '2025-05-01', '2025-05-15', 'unpaid', 'Consultation fee'),
(2, 1, 10, 200.00, '2025-05-05', '2025-05-19', 'unpaid', 'Consultation + tests'),
(3, 2, NULL, 350.00, '2025-05-10', '2025-05-24', 'overdue', 'Lab tests package'),
(4, 3, 6, 120.00, '2025-05-12', '2025-05-26', 'paid', 'Follow-up consultation'),
(5, 15, 16, 180.00, '2025-05-15', '2025-05-29', 'unpaid', 'Specialist consultation'),
(6, 16, 61, 250.00, '2025-05-18', '2025-06-01', 'unpaid', 'MRI scan'),
(7, 1, NULL, 95.00, '2025-05-20', '2025-06-03', 'unpaid', 'Medication prescription'),
(8, 20, 67, 150.00, '2025-05-28', '2025-06-11', 'unpaid', 'Consultation fee'),
(9, 21, 68, 250.00, '2025-05-28', '2025-06-11', 'paid', 'Consultation + blood tests'),
(10, 22, 69, 180.00, '2025-05-29', '2025-06-12', 'unpaid', 'Follow-up consultation');

-- --------------------------------------------------------

--
-- بنية الجدول `lab_appointments`
--

CREATE TABLE `lab_appointments` (
  `appoid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `apponum` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `appodate` date NOT NULL,
  `status` enum('Pending','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `type` enum('doctor','radiology','lab') DEFAULT 'lab'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `lab_appointments`
--

INSERT INTO `lab_appointments` (`appoid`, `pid`, `apponum`, `schedule_id`, `appodate`, `status`, `type`) VALUES
(3, 16, 1, 1, '2025-04-18', 'Pending', 'lab'),
(4, 16, 2, 1, '2025-04-18', 'Pending', 'lab'),
(5, 16, 1, 3, '2025-04-18', 'Pending', 'lab'),
(6, 16, 1, 2, '2025-04-18', 'Pending', 'lab'),
(7, 16, 3, 1, '2025-04-19', 'Pending', 'lab'),
(8, 16, 3, 1, '2025-04-19', 'Pending', 'lab'),
(9, 16, 2, 2, '2025-04-19', 'Pending', 'lab');

-- --------------------------------------------------------

--
-- بنية الجدول `lab_schedule`
--

CREATE TABLE `lab_schedule` (
  `schedule_id` int(11) NOT NULL,
  `lab_type_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_appointments` int(11) DEFAULT 10,
  `booked_appointments` int(11) DEFAULT 0,
  `duration` int(11) DEFAULT NULL,
  `preparation_instructions` text DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `lab_schedule`
--

INSERT INTO `lab_schedule` (`schedule_id`, `lab_type_id`, `technician_id`, `title`, `available_date`, `start_time`, `end_time`, `max_appointments`, `booked_appointments`, `duration`, `preparation_instructions`, `is_available`) VALUES
(1, 1, 1, '', '2025-04-20', '09:00:00', '12:00:00', 20, 0, NULL, NULL, 1),
(2, 1, 1, '', '2025-04-20', '13:00:00', '16:00:00', 20, 0, NULL, NULL, 1),
(3, 2, 2, '', '2025-04-21', '10:00:00', '14:00:00', 15, 0, NULL, NULL, 1),
(4, 2, 3, '', '2025-05-26', '11:11:00', '11:11:00', 10, 0, NULL, NULL, 1),
(5, 1, 1, 'fee', '2025-05-27', '01:28:00', '01:58:00', 10, 0, 30, '', 1),
(6, 1, 1, '1', '2025-05-26', '11:11:00', '11:41:00', 10, 0, 30, '', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `lab_technicians`
--

CREATE TABLE `lab_technicians` (
  `technician_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `lab_technicians`
--

INSERT INTO `lab_technicians` (`technician_id`, `full_name`, `email`, `phone`, `specialization`, `is_active`) VALUES
(1, 'أحمد محمد', 'tech1@lab.com', '01012345678', 'تحاليل الدم', 1),
(2, 'مريم علي', 'tech2@lab.com', '01023456789', 'الأشعة', 1),
(3, 'خالد محمود', 'tech3@lab.com', '01034567890', 'الفحوصات العامة', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `lab_types`
--

CREATE TABLE `lab_types` (
  `lab_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `preparation_instructions` text DEFAULT NULL,
  `estimated_duration` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `lab_types`
--

INSERT INTO `lab_types` (`lab_type_id`, `name`, `description`, `preparation_instructions`, `estimated_duration`, `price`, `is_active`) VALUES
(1, 'تحليل الدم الكامل', 'فحص شامل لمكونات الدم', 'الصيام لمدة 8 ساعات', 15, 150.00, 1),
(2, 'أشعة سينية للصدر', 'فحص الرئتين والعظام', 'لا يوجد تحضير خاص', 30, 250.00, 1),
(3, 'تحليل السكر', 'قياس مستوى الجلوكوز', 'الصيام لمدة 12 ساعة', 10, 80.00, 1),
(4, 'Liver Function Test', 'Measures enzymes and proteins in the blood', 'Fast for 8 hours before test', 20, 200.00, 1),
(5, 'Thyroid Function Test', 'Measures thyroid hormone levels', 'No special preparation needed', 15, 180.00, 1),
(6, 'Urinalysis', 'Examines urine for various cells and chemicals', 'Drink normal amount of fluids', 10, 75.00, 1);

-- --------------------------------------------------------

--
-- بنية الجدول `medication`
--

CREATE TABLE `medication` (
  `medid` int(11) NOT NULL,
  `pid` int(11) DEFAULT NULL,
  `medname` varchar(255) DEFAULT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `intake_time` time DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `daily_doses` int(11) DEFAULT 1,
  `appoid` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('ongoing','completed','paused') DEFAULT 'ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `medication`
--

INSERT INTO `medication` (`medid`, `pid`, `medname`, `dosage`, `intake_time`, `start_date`, `end_date`, `daily_doses`, `appoid`, `notes`, `status`) VALUES
(1, 1, 'باراسيتامول', '500 مجم', '08:00:00', '2024-11-23', '2024-12-23', 3, NULL, 'تناول الدواء بعد الوجبات', 'ongoing'),
(2, 2, 'أموكسيسيلين', '250 مجم', '10:00:00', '2024-11-22', '2024-11-29', 2, NULL, 'إتمام الدورة كاملة', 'ongoing'),
(3, 3, 'ميتوبرولول', '50 مجم', '12:00:00', '2024-11-15', '2024-12-15', 1, NULL, 'يجب تناوله صباحاً قبل الطعام', 'ongoing'),
(4, 2, 'إيبوبروفين', '200 مجم', '16:00:00', '2024-11-20', '2024-12-01', 2, NULL, 'للصداع والألم العضلي', 'ongoing'),
(5, 1, 'كولشيسين', '0.5 مجم', '20:00:00', '2024-11-23', '2024-12-01', 1, NULL, 'لمدة 10 أيام', 'completed');

-- --------------------------------------------------------

--
-- بنية الجدول `meds`
--

CREATE TABLE `meds` (
  `med_id` decimal(6,0) NOT NULL,
  `med_name` varchar(50) NOT NULL,
  `active_ingredient` varchar(100) NOT NULL,
  `dosage_form` varchar(50) NOT NULL,
  `strength` varchar(50) NOT NULL,
  `med_qty` int(11) NOT NULL,
  `category` varchar(20) DEFAULT NULL,
  `med_price` decimal(6,2) NOT NULL,
  `location_rack` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `mfg_company` varchar(100) DEFAULT NULL,
  `requires_prescription` tinyint(1) DEFAULT 0,
  `side_effects` text DEFAULT NULL,
  `storage_conditions` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- إرجاع أو استيراد بيانات الجدول `meds`
--

INSERT INTO `meds` (`med_id`, `med_name`, `active_ingredient`, `dosage_form`, `strength`, `med_qty`, `category`, `med_price`, `location_rack`, `description`, `mfg_company`, `requires_prescription`, `side_effects`, `storage_conditions`, `expiry_date`, `added_by`, `added_date`) VALUES
(1, 'Paracetamol', 'Acetaminophen', 'Tablet', '500mg', 1000, 'Analgesic', 5.99, 'A-1', 'Pain reliever and fever reducer', 'Pharma Inc', 0, 'Rare: allergic reactions', 'Store below 30°C', '2026-12-31', 1, '2025-05-24 00:11:55'),
(2, 'Amoxicillin', 'Amoxicillin Trihydrate', 'Capsule', '250mg', 500, 'Antibiotic', 15.99, 'B-2', 'Antibiotic for bacterial infections', 'MedCorp', 1, 'Diarrhea, nausea', 'Store below 25°C', '2026-06-30', 1, '2025-05-24 00:11:55'),
(3, 'Metoprolol', 'Metoprolol Tartrate', 'Tablet', '50mg', 300, 'Beta Blocker', 12.99, 'C-3', 'For high blood pressure and heart conditions', 'CardioPharm', 1, 'Dizziness, fatigue', 'Store below 30°C', '2026-09-30', 1, '2025-05-24 00:11:55'),
(4, 'Ibuprofen', 'Ibuprofen', 'Tablet', '200mg', 800, 'NSAID', 8.99, 'A-2', 'Anti-inflammatory and pain reliever', 'PainRelief Inc', 0, 'Stomach upset, heartburn', 'Store below 25°C', '2026-08-31', 1, '2025-05-24 00:11:55'),
(5, 'Omeprazole', 'Omeprazole', 'Capsule', '20mg', 400, 'PPI', 18.99, 'D-1', 'For acid reflux and ulcers', 'GastroMed', 1, 'Headache, diarrhea', 'Store below 30°C', '2026-11-30', 1, '2025-05-24 00:11:55'),
(6, 'Cetirizine', 'Cetirizine Hydrochloride', 'Tablet', '10mg', 600, 'Antihistamine', 7.99, 'E-1', 'For allergies and hay fever', 'AllergyCare', 0, 'Drowsiness, dry mouth', 'Store below 25°C', '2026-07-31', 1, '2025-05-24 00:11:55'),
(7, 'Metformin', 'Metformin Hydrochloride', 'Tablet', '500mg', 450, 'Antidiabetic', 14.99, 'F-2', 'For type 2 diabetes', 'DiabeCare', 1, 'Nausea, diarrhea', 'Store below 30°C', '2026-10-31', 1, '2025-05-24 00:11:55'),
(8, 'Atorvastatin', 'Atorvastatin Calcium', 'Tablet', '20mg', 350, 'Statin', 22.99, 'G-1', 'For high cholesterol', 'CardioPharm', 1, 'Muscle pain, weakness', 'Store below 25°C', '2026-12-31', 1, '2025-05-24 00:11:55'),
(9, 'Azithromycin', 'Azithromycin Dihydrate', 'Tablet', '250mg', 200, 'Antibiotic', 25.99, 'B-3', 'For bacterial infections', 'MedCorp', 1, 'Nausea, diarrhea', 'Store below 30°C', '2026-08-31', 1, '2025-05-24 00:11:55'),
(10, 'Loratadine', 'Loratadine', 'Tablet', '10mg', 700, 'Antihistamine', 9.99, 'E-2', 'For allergies and hay fever', 'AllergyCare', 0, 'Headache, fatigue', 'Store below 25°C', '2026-09-30', 1, '2025-05-24 00:11:55'),
(11, 'Lisinopril', 'Lisinopril', 'Tablet', '10mg', 500, 'Antihypertensive', 12.99, 'H-1', 'For high blood pressure', 'CardioPharm', 1, 'Dizziness, cough', 'Store below 30°C', '2026-12-31', 1, '2025-05-26 00:01:34'),
(12, 'Metformin', 'Metformin HCl', 'Tablet', '500mg', 800, 'Antidiabetic', 8.99, 'H-2', 'For type 2 diabetes', 'DiabeCare', 1, 'Nausea, diarrhea', 'Store below 25°C', '2026-11-30', 1, '2025-05-26 00:01:34'),
(13, 'Atorvastatin', 'Atorvastatin Calcium', 'Tablet', '20mg', 600, 'Statin', 15.99, 'H-3', 'For high cholesterol', 'CardioPharm', 1, 'Muscle pain', 'Store below 25°C', '2026-10-31', 1, '2025-05-26 00:01:34');

-- --------------------------------------------------------

--
-- بنية الجدول `patient`
--

CREATE TABLE `patient` (
  `pid` int(11) NOT NULL,
  `pemail` varchar(255) DEFAULT NULL,
  `pname` varchar(255) DEFAULT NULL,
  `ppassword` varchar(255) DEFAULT NULL,
  `paddress` varchar(255) DEFAULT NULL,
  `pnic` varchar(15) DEFAULT NULL,
  `pdob` date DEFAULT NULL,
  `ptel` varchar(15) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `emergency_name` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(15) DEFAULT NULL,
  `emergency_relationship` varchar(255) DEFAULT NULL,
  `insurance_provider` varchar(255) DEFAULT NULL,
  `policy_number` varchar(255) DEFAULT NULL,
  `group_number` varchar(255) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `blood_type` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') DEFAULT NULL,
  `allergies` mediumtext DEFAULT NULL,
  `medications` mediumtext DEFAULT NULL,
  `chronic_conditions` mediumtext DEFAULT NULL,
  `medical_history` mediumtext DEFAULT NULL,
  `family_history` mediumtext DEFAULT NULL,
  `symptoms` mediumtext DEFAULT NULL,
  `smoking` enum('yes','no') DEFAULT NULL,
  `alcohol` enum('yes','no') DEFAULT NULL,
  `exercise` enum('daily','weekly','monthly','rarely') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `patient`
--

INSERT INTO `patient` (`pid`, `pemail`, `pname`, `ppassword`, `paddress`, `pnic`, `pdob`, `ptel`, `gender`, `emergency_name`, `emergency_phone`, `emergency_relationship`, `insurance_provider`, `policy_number`, `group_number`, `height`, `weight`, `blood_type`, `allergies`, `medications`, `chronic_conditions`, `medical_history`, `family_history`, `symptoms`, `smoking`, `alcohol`, `exercise`) VALUES
(1, 'patient@edoc.com', 'Test Patient', '123', 'Sri Lanka', '0000000000', '2000-01-01', '0120000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'emhashenudara@gmail.com', 'Hashen Udara', '123', 'Sri Lanka', '0110000000', '2022-06-03', '0700000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'patient2@edoc.com', 'mostfa hatem', '123', 'alzarka', '443322', '2025-02-07', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'patient3@edoc.com', 'mostfa hatem', '123', 'alzarka', '443322', '2025-02-02', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'patient4@edoc.com', 'mostfa hatem', '123', 'alzarka', '443322', '2025-02-01', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'patient8@edoc.com', 'mostfa hatem', '123', 'alzarka', '443322', '2025-02-02', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'patient9@edoc.com', 'mostfa hatem', '123', 'alzarka', '443322', '2025-02-02', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'patient10@edoc.com', 'mostfa hatem', '123', 'alzarka', '443322', NULL, NULL, 'male', 'mostfa', '01014106072', 'mostfa', 'mostfa', '010', '111', 180, 79, 'A+', 'mostfa', 'mostfa', 'yes', 'nmae', 'yes', 'yes', 'yes', 'yes', 'daily'),
(9, 'patient77@edoc.com', 'mostfa hatem', '123', 'alzarka', '11223344', '2025-02-01', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'patientiv@edoc.com', 'mostfa hatem', '123', 'alzarka', '11223344', '2025-02-01', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'patient7776@edoc.com', 'mostfa hatem', '123', 'alzarka', '11223344', '2025-02-01', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'patient8i84@edoc.com', 'mostfa hatem', '123', 'alzarka', '11223344', '2025-02-01', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'patigent@edoc.com', 'mostfa hatem', '123', 'alzarka', '11223344', '2025-02-01', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'Mostfa221100488@nmu.edu.eg', 'mostfa ha', 'mostfahatem12', 'zarka', '123456', '2025-02-03', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'mostfahatem669@edoc.com', 'mostfa hatem', '123 ', 'alzarka', '11223344', NULL, NULL, 'male', 'mostfa', '01014106072', 'mostfa', 'mostfa', '010', '111', 180, 79, 'A+', '', '', '', '', '', 'm', 'yes', 'yes', 'daily'),
(16, 'mostfahatem668@gmail.com', 'mostfa hatem', '123', 'zarka', '123456', NULL, NULL, 'male', 'm', '1', '1', '1', '1', '1', 1, 1, 'A+', '1', '1', '1', '1', '1', '1', 'yes', 'yes', 'daily'),
(17, 'mostfahatem667@gmail.com', 'mostfa hatem', '123', 'zarka', '123456', '2025-05-01', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'sayed@gmail.com', 'sayed  kara', '123', 'zarka', '123456', '2025-05-01', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'sayed1@gmail.com', 'sayed  kara', '123', 'zarka', '123456', NULL, NULL, 'male', 'm', '1', '1', '1', '1', '1', 160, 50, 'A+', '1', '1', '1', '1', '1', '1', 'yes', 'yes', 'weekly'),
(20, 'patient20@edoc.com', 'Robert Johnson', 'pat123', '123 Main St', 'P12345678', '1985-05-15', '0123456789', 'male', 'Mary Johnson', '0123456780', 'Spouse', 'MediCare', 'POL12345', 'GRP100', 175, 80, 'O+', 'Penicillin', 'Lisinopril', 'Hypertension', 'Appendectomy 2010', 'Father: Heart disease', 'Headache, fatigue', 'no', 'no', 'weekly'),
(21, 'patient21@edoc.com', 'Jennifer Williams', 'pat123', '456 Oak Ave', 'P23456789', '1990-08-22', '0123456788', 'female', 'David Williams', '0123456781', 'Spouse', 'HealthPlus', 'POL23456', 'GRP101', 165, 60, 'A-', 'None', 'Metformin', 'Type 2 Diabetes', 'None', 'Mother: Diabetes', 'Increased thirst', 'no', 'no', 'daily'),
(22, 'patient22@edoc.com', 'James Brown', 'pat123', '789 Pine Rd', 'P34567890', '1978-03-10', '0123456787', 'male', 'Lisa Brown', '0123456782', 'Spouse', 'BlueCross', 'POL34567', 'GRP102', 182, 90, 'B+', 'Shellfish', 'Atorvastatin', 'High Cholesterol', 'Gallbladder removal 2015', 'Both parents: High cholesterol', 'Chest pain', 'yes', 'no', 'rarely');

-- --------------------------------------------------------

--
-- بنية الجدول `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `status` enum('completed','refunded','failed') DEFAULT 'completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `payments`
--

INSERT INTO `payments` (`payment_id`, `patient_id`, `appointment_id`, `amount`, `payment_date`, `payment_method`, `service_type`, `status`) VALUES
(1, 1, 3, 150.00, '2025-05-01', 'Credit Card', 'Consultation', 'completed'),
(2, 3, 6, 120.00, '2025-05-03', 'Cash', 'Follow-up', 'completed'),
(3, 15, 15, 200.00, '2025-05-05', 'Insurance', 'Specialist Visit', 'completed'),
(4, 2, NULL, 100.00, '2025-05-05', 'Credit Card', 'Lab Test Deposit', 'completed'),
(5, 16, 44, 300.00, '2025-05-10', 'Bank Transfer', 'Radiology', 'completed'),
(6, 1, 10, 200.00, '2025-05-12', 'Credit Card', 'Consultation', 'completed'),
(7, 3, NULL, 80.00, '2025-05-15', 'Cash', 'Medication', 'completed'),
(8, 15, 16, 180.00, '2025-05-18', 'Insurance', 'Consultation', 'completed'),
(9, 16, 61, 150.00, '2025-05-20', 'Credit Card', 'MRI Scan Deposit', 'completed'),
(10, 1, NULL, 95.00, '2025-05-20', 'Cash', 'Medication', 'completed'),
(11, 2, NULL, 250.00, '2025-05-22', 'Bank Transfer', 'Lab Test Balance', 'completed'),
(12, 16, 61, 100.00, '2025-05-25', 'Credit Card', 'MRI Scan Balance', 'completed'),
(13, 1, NULL, 120.00, '2025-04-02', 'Credit Card', 'Consultation', 'completed'),
(14, 2, NULL, 180.00, '2025-04-05', 'Cash', 'Lab Tests', 'completed'),
(15, 3, NULL, 90.00, '2025-04-08', 'Credit Card', 'Follow-up', 'completed'),
(16, 15, NULL, 200.00, '2025-04-10', 'Insurance', 'Specialist Visit', 'completed'),
(17, 16, NULL, 150.00, '2025-04-15', 'Bank Transfer', 'X-ray', 'completed'),
(18, 1, NULL, 110.00, '2025-04-18', 'Credit Card', 'Consultation', 'completed'),
(19, 2, NULL, 220.00, '2025-04-20', 'Cash', 'Lab Tests', 'completed'),
(20, 3, NULL, 85.00, '2025-04-22', 'Credit Card', 'Medication', 'completed'),
(21, 15, NULL, 190.00, '2025-04-25', 'Insurance', 'Consultation', 'completed'),
(22, 16, NULL, 320.00, '2025-04-28', 'Bank Transfer', 'CT Scan', 'completed'),
(23, 1, NULL, 100.00, '2025-03-03', 'Credit Card', 'Consultation', 'completed'),
(24, 2, NULL, 160.00, '2025-03-07', 'Cash', 'Lab Tests', 'completed'),
(25, 3, NULL, 95.00, '2025-03-10', 'Credit Card', 'Follow-up', 'completed'),
(26, 15, NULL, 210.00, '2025-03-12', 'Insurance', 'Specialist Visit', 'completed'),
(27, 16, NULL, 280.00, '2025-03-18', 'Bank Transfer', 'MRI', 'completed'),
(28, 1, NULL, 105.00, '2025-03-20', 'Credit Card', 'Consultation', 'completed'),
(29, 2, NULL, 190.00, '2025-03-22', 'Cash', 'Lab Tests', 'completed'),
(30, 3, NULL, 75.00, '2025-03-25', 'Credit Card', 'Medication', 'completed'),
(31, 15, NULL, 180.00, '2025-03-27', 'Insurance', 'Consultation', 'completed'),
(32, 16, NULL, 300.00, '2025-03-30', 'Bank Transfer', 'X-ray', 'completed'),
(33, 21, 68, 250.00, '2025-05-28', 'Credit Card', 'Consultation', 'completed'),
(34, 20, NULL, 100.00, '2025-05-28', 'Insurance', 'Lab Test', 'completed'),
(35, 22, NULL, 80.00, '2025-05-29', 'Cash', 'Medication', 'completed');

-- --------------------------------------------------------

--
-- بنية الجدول `prescriptions`
--

CREATE TABLE `prescriptions` (
  `prescription_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `med_id` decimal(6,0) NOT NULL,
  `docid` int(11) NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `duration` int(11) NOT NULL,
  `instructions` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- إرجاع أو استيراد بيانات الجدول `prescriptions`
--

INSERT INTO `prescriptions` (`prescription_id`, `pid`, `med_id`, `docid`, `dosage`, `frequency`, `duration`, `instructions`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 16, 2, 1, '1', 'once daily', 7, 'take', '2025-05-24', '2025-05-31', 'active', '2025-05-24 00:17:59'),
(2, 16, 2, 1, '1', 'once daily', 1, '1', '2025-05-24', '2025-05-25', 'active', '2025-05-24 00:24:11'),
(3, 16, 2, 1, '1', 'once daily', 1, '', '2025-05-25', '2025-05-26', 'active', '2025-05-24 23:05:14');

-- --------------------------------------------------------

--
-- بنية الجدول `radiology_appointment`
--

CREATE TABLE `radiology_appointment` (
  `appoid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `apponum` int(11) NOT NULL,
  `scheduleid` int(11) NOT NULL,
  `appodate` date NOT NULL,
  `status` enum('Pending','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `type` enum('doctor','radiology','lab') DEFAULT 'radiology'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `radiology_appointment`
--

INSERT INTO `radiology_appointment` (`appoid`, `pid`, `apponum`, `scheduleid`, `appodate`, `status`, `type`) VALUES
(44, 0, 0, 1, '0000-00-00', 'Pending', 'radiology'),
(45, 0, 0, 2, '0000-00-00', 'Pending', 'radiology'),
(46, 0, 0, 1, '0000-00-00', 'Pending', 'radiology'),
(66, 0, 0, 1, '0000-00-00', 'Pending', 'radiology');

-- --------------------------------------------------------

--
-- بنية الجدول `radiology_appointments`
--

CREATE TABLE `radiology_appointments` (
  `appoid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `apponum` int(11) NOT NULL,
  `scheduleid` int(11) NOT NULL,
  `appodate` date NOT NULL,
  `status` enum('Pending','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `type` enum('doctor','radiology','lab') DEFAULT 'radiology'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `radiology_schedule`
--

CREATE TABLE `radiology_schedule` (
  `scheduleid` int(11) NOT NULL,
  `techid` varchar(255) DEFAULT NULL COMMENT 'ID of Radiology Technician',
  `title` varchar(255) DEFAULT NULL COMMENT 'Session title (X-ray, CT, MRI)',
  `scheduledate` date DEFAULT NULL,
  `scheduletime` time DEFAULT NULL,
  `nop` int(4) DEFAULT NULL COMMENT 'Number of patients',
  `session_type` enum('xray','ct','mri') NOT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `preparation_instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `radiology_schedule`
--

INSERT INTO `radiology_schedule` (`scheduleid`, `techid`, `title`, `scheduledate`, `scheduletime`, `nop`, `session_type`, `duration`, `preparation_instructions`) VALUES
(1, 'RT5', 'x-ray', '2025-05-26', '11:53:00', 10, 'xray', 30, ''),
(2, 'RT7', 'sw', '2025-05-26', '11:11:00', 10, 'ct', 100, 'dckdmc'),
(3, 'RT5', '11', '2025-05-25', '11:11:00', 10, 'xray', 30, '111');

-- --------------------------------------------------------

--
-- بنية الجدول `radiology_technicians`
--

CREATE TABLE `radiology_technicians` (
  `techid` varchar(255) NOT NULL,
  `techname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tele` varchar(20) DEFAULT NULL,
  `specialization` enum('xray','ct','mri','all') NOT NULL DEFAULT 'all'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `radiology_technicians`
--

INSERT INTO `radiology_technicians` (`techid`, `techname`, `email`, `tele`, `specialization`) VALUES
('RT5', 'John Smith', 'john.smith@hospital.com', '1234567890', 'all'),
('RT6', 'Sarah Johnson', 'sarah.j@hospital.com', '0987654321', 'mri'),
('RT7', 'Michael Brown', 'michael.b@hospital.com', '1122334455', 'ct');

-- --------------------------------------------------------

--
-- بنية الجدول `radiology_types`
--

CREATE TABLE `radiology_types` (
  `radiology_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `radiology_types`
--

INSERT INTO `radiology_types` (`radiology_type_id`, `name`, `description`, `is_active`) VALUES
(1, 'X-Ray', 'Standard X-ray imaging', 1),
(2, 'MRI', 'Magnetic Resonance Imaging', 1),
(3, 'CT Scan', 'Computed Tomography Scan', 1),
(4, 'Ultrasound', 'Ultrasound imaging', 1),
(5, 'Mammography', 'Breast X-ray imaging', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `service_rating` varchar(50) DEFAULT NULL,
  `need_support` varchar(50) DEFAULT NULL,
  `overall_experience` int(11) DEFAULT NULL,
  `wait_time` int(11) DEFAULT NULL,
  `staff_courtesy` int(11) DEFAULT NULL,
  `facilities_rating` int(11) DEFAULT NULL,
  `would_recommend` varchar(50) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `prediction` varchar(50) DEFAULT NULL,
  `confidence` float DEFAULT NULL,
  `doctor` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `results`
--

INSERT INTO `results` (`id`, `patient_id`, `service_rating`, `need_support`, `overall_experience`, `wait_time`, `staff_courtesy`, `facilities_rating`, `would_recommend`, `feedback`, `prediction`, `confidence`, `doctor`) VALUES
(15, 1, 'Excellent', 'Yes', 0, 0, 0, 0, 'Yes', 'gooood', 'neutral', 0.492817, 'Test Doctor'),
(16, 1, 'Excellent', 'Yes', 0, 0, 0, 0, 'Yes', 'goood', 'neutral', 0.470273, 'Test Doctor'),
(17, 123, 'Excellent', 'Yes', 10, 10, 10, 10, 'Yes', 'it was fantastic', 'sadness', 0.229418, 'Test Doctor'),
(20, 123, 'Excellent', 'Yes', 10, 10, 10, 10, 'Yes', 'i was fantastic', 'neutral', 0.317155, 'Test Doctor'),
(21, 1, 'Poor', 'No', 1, 1, 1, 1, 'No', 'no', 'joy', 0.302413, 'Test Doctor'),
(22, 1, 'Poor', 'No', 1, 1, 1, 1, 'No', 'im not happy', 'joy', 0.709044, 'Test Doctor'),
(23, 10, 'Excellent', 'Yes', 10, 10, 10, 10, 'Yes', 'I’ve had enough. After all the hard work I put in, they still ignored my suggestions and gave credit to someone else. It’s not just unfair — it’s disrespectful. I stayed late every night to meet the deadlines, and this is the thanks I get? I’m tired of being overlooked and treated like I don’t matter. I deserve better than this, and I won’t stay silent anymore.', 'sadness', 0.480446, 'Test Doctor'),
(24, 10, 'Excellent', 'Yes', 10, 10, 10, 10, 'Yes', 'lovely', 'neutral', 0.455702, 'Test Doctor'),
(25, 10, 'Excellent', 'Yes', 10, 10, 10, 10, 'Yes', 'lovely', 'neutral', 0.455702, 'Test Doctor');

-- --------------------------------------------------------

--
-- بنية الجدول `schedule`
--

CREATE TABLE `schedule` (
  `scheduleid` int(11) NOT NULL,
  `docid` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `scheduledate` date DEFAULT NULL,
  `scheduletime` time DEFAULT NULL,
  `nop` int(4) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- إرجاع أو استيراد بيانات الجدول `schedule`
--

INSERT INTO `schedule` (`scheduleid`, `docid`, `title`, `scheduledate`, `scheduletime`, `nop`) VALUES
(1, '1', 'Test Session', '2050-01-01', '18:00:00', 50),
(2, '1', '1', '2022-06-10', '20:36:00', 1),
(3, '1', '12', '2022-06-10', '20:33:00', 1),
(4, '1', '1', '2022-06-10', '12:32:00', 1),
(5, '1', '1', '2022-06-10', '20:35:00', 1),
(6, '1', '12', '2022-06-10', '20:35:00', 1),
(7, '1', '1', '2022-06-24', '20:36:00', 1),
(8, '1', '12', '2022-06-10', '13:33:00', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `specialties`
--

CREATE TABLE `specialties` (
  `id` int(2) NOT NULL,
  `sname` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- إرجاع أو استيراد بيانات الجدول `specialties`
--

INSERT INTO `specialties` (`id`, `sname`) VALUES
(1, 'Accident and emergency medicine'),
(2, 'Allergology'),
(3, 'Anaesthetics'),
(4, 'Biological hematology'),
(5, 'Cardiology'),
(6, 'Child psychiatry'),
(7, 'Clinical biology'),
(8, 'Clinical chemistry'),
(9, 'Clinical neurophysiology'),
(10, 'Clinical radiology'),
(11, 'Dental, oral and maxillo-facial surgery'),
(12, 'Dermato-venerology'),
(13, 'Dermatology'),
(14, 'Endocrinology'),
(15, 'Gastro-enterologic surgery'),
(16, 'Gastroenterology'),
(17, 'General hematology'),
(18, 'General Practice'),
(19, 'General surgery'),
(20, 'Geriatrics'),
(21, 'Immunology'),
(22, 'Infectious diseases'),
(23, 'Internal medicine'),
(24, 'Laboratory medicine'),
(25, 'Maxillo-facial surgery'),
(26, 'Microbiology'),
(27, 'Nephrology'),
(28, 'Neuro-psychiatry'),
(29, 'Neurology'),
(30, 'Neurosurgery'),
(31, 'Nuclear medicine'),
(32, 'Obstetrics and gynecology'),
(33, 'Occupational medicine'),
(34, 'Ophthalmology'),
(35, 'Orthopaedics'),
(36, 'Otorhinolaryngology'),
(37, 'Paediatric surgery'),
(38, 'Paediatrics'),
(39, 'Pathology'),
(40, 'Pharmacology'),
(41, 'Physical medicine and rehabilitation'),
(42, 'Plastic surgery'),
(43, 'Podiatric Medicine'),
(44, 'Podiatric Surgery'),
(45, 'Psychiatry'),
(46, 'Public health and Preventive Medicine'),
(47, 'Radiology'),
(48, 'Radiotherapy'),
(49, 'Respiratory medicine'),
(50, 'Rheumatology'),
(51, 'Stomatology'),
(52, 'Thoracic surgery'),
(53, 'Tropical medicine'),
(54, 'Urology'),
(55, 'Vascular surgery'),
(56, 'Venereology');

-- --------------------------------------------------------

--
-- بنية الجدول `suppliers`
--

CREATE TABLE `suppliers` (
  `sup_id` decimal(3,0) NOT NULL,
  `sup_name` varchar(25) NOT NULL,
  `sup_add` varchar(30) NOT NULL,
  `sup_phno` decimal(10,0) NOT NULL,
  `sup_mail` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `webuser`
--

CREATE TABLE `webuser` (
  `email` varchar(255) NOT NULL,
  `usertype` char(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- إرجاع أو استيراد بيانات الجدول `webuser`
--

INSERT INTO `webuser` (`email`, `usertype`) VALUES
('admin@edoc.com', 'a'),
('doctor@edoc.com', 'd'),
('patient@edoc.com', 'p'),
('emhashenudara@gmail.com', 'p'),
('doctor1@edoc.com', 'd'),
('doctor2@edoc.com', 'd'),
('doctor3@edoc.com', 'd'),
('patient2@edoc.com', 'p'),
('patient3@edoc.com', 'p'),
('patient4@edoc.com', 'p'),
('patient8@edoc.com', 'p'),
('patient9@edoc.com', 'p'),
('patient10@edoc.com', 'p'),
('patient77@edoc.com', 'p'),
('patientiv@edoc.com', 'p'),
('patient7776@edoc.com', 'p'),
('patient8i84@edoc.com', 'p'),
('patigent@edoc.com', 'p'),
('Mostfa221100488@nmu.edu.eg', 'p'),
('mostfahatem669@edoc.com', 'p'),
('mostfahatem668@gmail.com', 'p'),
('mostfahatem667@gmail.com', 'p'),
('sayed@gmail.com', 'p'),
('sayed1@gmail.com', 'p');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`aemail`);

--
-- Indexes for table `ambulance_requests`
--
ALTER TABLE `ambulance_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appoid`),
  ADD KEY `pid` (`pid`),
  ADD KEY `scheduleid` (`scheduleid`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`docid`),
  ADD KEY `specialties` (`specialties`);

--
-- Indexes for table `drug_interactions`
--
ALTER TABLE `drug_interactions`
  ADD PRIMARY KEY (`interaction_id`),
  ADD KEY `med_id_1` (`med_id_1`),
  ADD KEY `med_id_2` (`med_id_2`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`);

--
-- Indexes for table `lab_appointments`
--
ALTER TABLE `lab_appointments`
  ADD PRIMARY KEY (`appoid`),
  ADD KEY `pid` (`pid`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `lab_schedule`
--
ALTER TABLE `lab_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `lab_type_id` (`lab_type_id`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Indexes for table `lab_technicians`
--
ALTER TABLE `lab_technicians`
  ADD PRIMARY KEY (`technician_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `lab_types`
--
ALTER TABLE `lab_types`
  ADD PRIMARY KEY (`lab_type_id`);

--
-- Indexes for table `medication`
--
ALTER TABLE `medication`
  ADD PRIMARY KEY (`medid`);

--
-- Indexes for table `meds`
--
ALTER TABLE `meds`
  ADD PRIMARY KEY (`med_id`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`prescription_id`);

--
-- Indexes for table `radiology_appointment`
--
ALTER TABLE `radiology_appointment`
  ADD PRIMARY KEY (`appoid`),
  ADD KEY `pid` (`pid`),
  ADD KEY `scheduleid` (`scheduleid`);

--
-- Indexes for table `radiology_appointments`
--
ALTER TABLE `radiology_appointments`
  ADD PRIMARY KEY (`appoid`),
  ADD KEY `pid` (`pid`),
  ADD KEY `scheduleid` (`scheduleid`);

--
-- Indexes for table `radiology_schedule`
--
ALTER TABLE `radiology_schedule`
  ADD PRIMARY KEY (`scheduleid`),
  ADD KEY `techid` (`techid`);

--
-- Indexes for table `radiology_technicians`
--
ALTER TABLE `radiology_technicians`
  ADD PRIMARY KEY (`techid`);

--
-- Indexes for table `radiology_types`
--
ALTER TABLE `radiology_types`
  ADD PRIMARY KEY (`radiology_type_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`scheduleid`),
  ADD KEY `docid` (`docid`);

--
-- Indexes for table `specialties`
--
ALTER TABLE `specialties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`sup_id`);

--
-- Indexes for table `webuser`
--
ALTER TABLE `webuser`
  ADD PRIMARY KEY (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ambulance_requests`
--
ALTER TABLE `ambulance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appoid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `docid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `drug_interactions`
--
ALTER TABLE `drug_interactions`
  MODIFY `interaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `lab_appointments`
--
ALTER TABLE `lab_appointments`
  MODIFY `appoid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lab_schedule`
--
ALTER TABLE `lab_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `lab_technicians`
--
ALTER TABLE `lab_technicians`
  MODIFY `technician_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lab_types`
--
ALTER TABLE `lab_types`
  MODIFY `lab_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `medication`
--
ALTER TABLE `medication`
  MODIFY `medid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `radiology_appointment`
--
ALTER TABLE `radiology_appointment`
  MODIFY `appoid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `radiology_appointments`
--
ALTER TABLE `radiology_appointments`
  MODIFY `appoid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `radiology_schedule`
--
ALTER TABLE `radiology_schedule`
  MODIFY `scheduleid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `radiology_types`
--
ALTER TABLE `radiology_types`
  MODIFY `radiology_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `scheduleid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `lab_appointments`
--
ALTER TABLE `lab_appointments`
  ADD CONSTRAINT `lab_appointments_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `patient` (`pid`),
  ADD CONSTRAINT `lab_appointments_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `lab_schedule` (`schedule_id`);

--
-- قيود الجداول `lab_schedule`
--
ALTER TABLE `lab_schedule`
  ADD CONSTRAINT `lab_schedule_ibfk_1` FOREIGN KEY (`lab_type_id`) REFERENCES `lab_types` (`lab_type_id`),
  ADD CONSTRAINT `lab_schedule_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `lab_technicians` (`technician_id`);

--
-- قيود الجداول `radiology_appointments`
--
ALTER TABLE `radiology_appointments`
  ADD CONSTRAINT `radiology_appointments_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `patient` (`pid`),
  ADD CONSTRAINT `radiology_appointments_ibfk_2` FOREIGN KEY (`scheduleid`) REFERENCES `radiology_schedule` (`scheduleid`);

--
-- قيود الجداول `radiology_schedule`
--
ALTER TABLE `radiology_schedule`
  ADD CONSTRAINT `radiology_schedule_ibfk_1` FOREIGN KEY (`techid`) REFERENCES `radiology_technicians` (`techid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
