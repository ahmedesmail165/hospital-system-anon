-- Drop existing tables if they exist
DROP TABLE IF EXISTS `radiology_appointments`;
DROP TABLE IF EXISTS `radiology_schedule`;
DROP TABLE IF EXISTS `radiology_types`;
DROP TABLE IF EXISTS `radiology_technicians`;

-- Create radiology_types table
CREATE TABLE `radiology_types` (
  `radiology_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`radiology_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create radiology_technicians table (matching existing structure)
CREATE TABLE `radiology_technicians` (
  `techid` varchar(255) NOT NULL,
  `techname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tele` varchar(20) DEFAULT NULL,
  `specialization` enum('xray','ct','mri','all') NOT NULL DEFAULT 'all',
  PRIMARY KEY (`techid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create radiology_schedule table (matching existing structure)
CREATE TABLE `radiology_schedule` (
  `scheduleid` int(11) NOT NULL AUTO_INCREMENT,
  `techid` varchar(255) DEFAULT NULL COMMENT 'ID of Radiology Technician',
  `title` varchar(255) DEFAULT NULL COMMENT 'Session title (X-ray, CT, MRI)',
  `scheduledate` date DEFAULT NULL,
  `scheduletime` time DEFAULT NULL,
  `nop` int(4) DEFAULT NULL COMMENT 'Number of patients',
  `session_type` enum('xray','ct','mri') NOT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `preparation_instructions` text DEFAULT NULL,
  PRIMARY KEY (`scheduleid`),
  KEY `techid` (`techid`),
  CONSTRAINT `radiology_schedule_ibfk_1` FOREIGN KEY (`techid`) REFERENCES `radiology_technicians` (`techid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create radiology_appointments table
CREATE TABLE `radiology_appointments` (
  `appoid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `apponum` int(11) NOT NULL,
  `scheduleid` int(11) NOT NULL,
  `appodate` date NOT NULL,
  `status` enum('Pending','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `type` enum('doctor','radiology','lab') DEFAULT 'radiology',
  PRIMARY KEY (`appoid`),
  KEY `pid` (`pid`),
  KEY `scheduleid` (`scheduleid`),
  CONSTRAINT `radiology_appointments_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `patient` (`pid`),
  CONSTRAINT `radiology_appointments_ibfk_2` FOREIGN KEY (`scheduleid`) REFERENCES `radiology_schedule` (`scheduleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample radiology types
INSERT INTO `radiology_types` (`name`, `description`) VALUES
('X-Ray', 'Standard X-ray imaging'),
('MRI', 'Magnetic Resonance Imaging'),
('CT Scan', 'Computed Tomography Scan'),
('Ultrasound', 'Ultrasound imaging'),
('Mammography', 'Breast X-ray imaging');

-- Insert sample technicians (matching existing format)
INSERT INTO `radiology_technicians` (`techid`, `techname`, `email`, `tele`, `specialization`) VALUES
('RT5', 'John Smith', 'john.smith@hospital.com', '1234567890', 'all'),
('RT6', 'Sarah Johnson', 'sarah.j@hospital.com', '0987654321', 'mri'),
('RT7', 'Michael Brown', 'michael.b@hospital.com', '1122334455', 'ct');

-- Insert sample radiology schedules
INSERT INTO `radiology_schedule` (`techid`, `title`, `scheduledate`, `scheduletime`, `nop`, `session_type`, `duration`, `preparation_instructions`) VALUES
('RT5', 'General X-ray Session', '2025-05-01', '09:00:00', 15, 'xray', 30, 'No special preparation needed'),
('RT6', 'MRI Brain Scan', '2025-05-01', '10:00:00', 8, 'mri', 60, 'Remove all metal objects'),
('RT7', 'CT Chest Scan', '2025-05-01', '11:00:00', 10, 'ct', 45, 'Fasting required for 4 hours'); 