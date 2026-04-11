-- Create BOQ table with proper structure
CREATE TABLE IF NOT EXISTS `boq_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) DEFAULT 'Nos',
  `category` varchar(100) DEFAULT NULL,
  `status` enum('active', 'inactive') DEFAULT 'active',
  `need_serial_number` tinyint(1) DEFAULT 0,
  `icon_class` varchar(60) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert BOQ data from the original table
INSERT INTO `boq_items` (`id`, `item_code`, `item_name`, `description`, `unit`, `category`, `status`, `need_serial_number`, `icon_class`) VALUES
(2, 'RACK-6U-001', '6U Rack with One Additional Tray', '6U Network Rack with Additional Tray for Equipment', 'Nos', 'Racks & Enclosures', 'active', 0, 'fas fa-server'),
(3, 'RACK-12U-001', '12U Rack with One Additional Tray', '12U Network Rack with Additional Tray for Equipment', 'Nos', 'Racks & Enclosures', 'active', 0, 'fas fa-server'),
(4, 'PP-24P-001', '24 Port Patch Panels', '24 Port Network Patch Panel', 'Nos', 'Network Components', 'active', 0, 'fas fa-plug'),
(5, 'LBL-PP-001', 'Patch Panel Labeling', 'Labeling for Patch Panels', 'Set', 'Accessories', 'active', 0, 'fas fa-tags'),
(6, 'CM-001', 'Cable Manager', 'Cable Management System', 'Nos', 'Accessories', 'active', 0, 'fas fa-project-diagram'),
(7, 'PC-1M-001', '1m Patch Cord', '1 Meter Patch Cord Cable', 'Nos', 'Cables', 'active', 0, 'fas fa-ethernet'),
(8, 'PC-5M-001', '5m Patch Cord', '5 Meter Patch Cord Cable', 'Nos', 'Cables', 'active', 0, 'fas fa-ethernet'),
(9, 'IO-FP-001', 'I/O Box Kit - Face Plate', 'Input/Output Box Face Plate', 'Nos', 'I/O Components', 'active', 0, 'fas fa-square'),
(10, 'IO-BB-001', 'I/O Box Kit - Back Box', 'Input/Output Box Back Box', 'Nos', 'I/O Components', 'active', 0, 'fas fa-cube'),
(11, 'IO-SOC-001', 'I/O Box Kit - IO Socket', 'Input/Output Socket', 'Nos', 'I/O Components', 'active', 0, 'fas fa-plug'),
(12, 'CAT6-UTP-001', 'Cat6 23 AWG UTP Cable (per Meter)', 'Category 6 UTP Cable 23 AWG', 'Meter', 'Cables', 'active', 0, 'fas fa-ethernet'),
(13, 'PVC-25MM-001', '25 mm Conducting PVC Pipes (White)', '25mm White PVC Conducting Pipes', 'Meter', 'Conduits', 'active', 0, 'fas fa-grip-lines'),
(14, 'FLEX-20MM-001', 'Flexible Pipe (20mm)', '20mm Flexible Conduit Pipe', 'Meter', 'Conduits', 'active', 0, 'fas fa-grip-lines'),
(15, 'CT-200MM-001', 'Cable Ties (200 mm)', '200mm Cable Ties', 'Pcs', 'Accessories', 'active', 0, 'fas fa-link'),
(16, 'CT-400MM-001', 'Cable Ties (400 mm)', '400mm Cable Ties', 'Pcs', 'Accessories', 'active', 0, 'fas fa-link'),
(17, 'SCR-0.5-001', 'Screws 1/2', '1/2 inch Screws', 'Pcs', 'Hardware', 'active', 0, 'fas fa-tools'),
(18, 'SCR-3.5-001', 'Screws 3.5', '3.5mm Screws', 'Pcs', 'Hardware', 'active', 0, 'fas fa-tools'),
(19, 'PVC-SAD-001', 'PVC Pipe Saddles', 'PVC Pipe Mounting Saddles', 'Pcs', 'Hardware', 'active', 0, 'fas fa-grip-horizontal'),
(20, 'ANG-L-001', 'L Slotted Angle', 'L-shaped Slotted Angle', 'Meter', 'Hardware', 'active', 0, 'fas fa-ruler-combined'),
(21, 'BEND-25MM-001', '25mm Bend', '25mm Pipe Bend', 'Nos', 'Conduits', 'active', 0, 'fas fa-share'),
(22, 'JUN-3W-001', '3 Way Junction', '3 Way Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-project-diagram'),
(23, 'JUN-4W-001', '4 Way Junction', '4 Way Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-project-diagram'),
(24, 'BOX-6X6-001', 'PVC Square Box 6x6', '6x6 PVC Square Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-square'),
(25, 'BOX-5X5-001', 'PVC Square Box 5x5', '5x5 PVC Square Junction Box', 'Nos', 'Conduits', 'active', 0, 'fas fa-square'),
(26, 'POLE-1M-001', '1m Pole', '1 Meter Mounting Pole', 'Nos', 'Mounting', 'active', 0, 'fas fa-grip-vertical'),
(27, 'POLE-2M-001', '2m Pole', '2 Meter Mounting Pole', 'Nos', 'Mounting', 'active', 0, 'fas fa-grip-vertical'),
(28, 'POLE-3M-001', '3m Pole', '3 Meter Mounting Pole', 'Nos', 'Mounting', 'active', 0, 'fas fa-grip-vertical'),
(29, 'CAM-JIO-001', 'Jio Cameras', 'Jio Security Cameras', 'Nos', 'Cameras', 'active', 1, 'fas fa-video'),
(30, 'BRG-JIO-001', 'Jio Bridge Devices', 'Jio Network Bridge Devices', 'Nos', 'Network Devices', 'active', 1, 'fas fa-wifi'),
(31, 'SW-CIS-24P-001', '24 Port CISCO Meraki POE Switches', '24 Port CISCO Meraki POE Network Switch', 'Nos', 'Network Devices', 'active', 1, 'fas fa-network-wired');

-- Update AUTO_INCREMENT to continue from 32
ALTER TABLE `boq_items` AUTO_INCREMENT = 32;