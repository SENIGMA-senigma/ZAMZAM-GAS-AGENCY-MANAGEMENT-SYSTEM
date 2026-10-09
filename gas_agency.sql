-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 02:35 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gas_agency`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `points_redeemed` int(11) DEFAULT 0,
  `loyalty_points` int(11) DEFAULT 0,
  `referral_code` varchar(10) DEFAULT NULL,
  `referred_by` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_name`, `customer_phone`, `password`, `points_redeemed`, `loyalty_points`, `referral_code`, `referred_by`) VALUES
(4, 'Faith ', '0754327864', '$2y$10$YWtSZRe8aGGpGUwRJzMSr.fVA4d6LMYNpfXR3We3fNZCiaerhz8..', 0, 12, 'ZAM0004', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer_notifications`
--

CREATE TABLE `customer_notifications` (
  `id` int(11) NOT NULL,
  `customer_phone` varchar(15) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `cylinder_inventory`
--

CREATE TABLE `cylinder_inventory` (
  `id` int(11) NOT NULL,
  `cylinder_size` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT NULL,
  `min_threshold` int(11) DEFAULT 5,
  `cylinder_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cylinder_inventory`
--

INSERT INTO `cylinder_inventory` (`id`, `cylinder_size`, `unit_price`, `stock_quantity`, `min_threshold`, `cylinder_image`) VALUES
(6, 6, '1270.00', 70, 59, '1773504182_0la6.png'),
(7, 13, '3400.00', 30, 20, '1773504941_13kg.png'),
(8, 13, '3400.00', 55, 54, '1773505062_mpishi_13kg.png'),
(9, 6, '1300.00', 60, 55, '1773506151_6kg_total.png'),
(10, 13, '3400.00', 60, 66, '1773506257_k_gas.png');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_riders`
--

CREATE TABLE `delivery_riders` (
  `id` int(11) NOT NULL,
  `rider_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `current_status` enum('Available','Busy') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `delivery_riders`
--

INSERT INTO `delivery_riders` (`id`, `rider_name`, `phone_number`, `current_status`) VALUES
(1, 'Kelvin Macharia', '0711223344', 'Busy'),
(2, 'Brian Otieno', '0722334455', 'Busy'),
(3, 'kim', '0758329146', 'Available'),
(4, 'kim', '0114704687', 'Available'),
(5, 'joy(uber)', '0754346798', 'Busy');

-- --------------------------------------------------------

--
-- Table structure for table `order_records`
--

CREATE TABLE `order_records` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(15) DEFAULT NULL,
  `cylinder_type` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `approval_status` enum('Pending','Dispatched','Delivered') DEFAULT 'Pending',
  `assigned_rider_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_records`
--

INSERT INTO `order_records` (`id`, `customer_name`, `customer_phone`, `cylinder_type`, `total_amount`, `approval_status`, `assigned_rider_id`, `order_date`, `payment_proof`) VALUES
(199, 'Faith ', '0754327864', 6, '1200.00', 'Delivered', 3, '2026-03-14 15:29:01', 'PAY_1773502141_69b57ebd2b367.png'),
(200, 'John K', '0758329148', 12, '1300.00', 'Dispatched', 5, '2026-03-14 15:42:38', 'PAY_1773502958_69b581ee03dcc.png');

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `usage_limit` int(11) DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `system_announcements`
--

CREATE TABLE `system_announcements` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `system_announcements`
--

INSERT INTO `system_announcements` (`id`, `message`, `created_at`) VALUES
(2, 'thank you for making orders', '2026-03-14 15:37:11');

-- --------------------------------------------------------

--
-- Table structure for table `system_audit_logs`
--

CREATE TABLE `system_audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `action_performed` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `system_audit_logs`
--

INSERT INTO `system_audit_logs` (`id`, `user_id`, `username`, `action_performed`, `ip_address`, `timestamp`) VALUES
(16, 13, 'manager', 'Rejected/Deleted Order #194', '::1', '2026-03-14 13:20:27'),
(17, 13, 'manager', 'Rejected/Deleted Order #193', '::1', '2026-03-14 13:20:30'),
(18, 13, 'manager', 'Rejected/Deleted Order #192', '::1', '2026-03-14 13:20:32'),
(19, 13, 'manager', 'Rejected/Deleted Order #191', '::1', '2026-03-14 13:20:35'),
(20, 13, 'manager', 'Rejected/Deleted Order #190', '::1', '2026-03-14 13:20:37'),
(21, 13, 'manager', 'Rejected/Deleted Order #189', '::1', '2026-03-14 13:20:39'),
(22, 13, 'manager', 'Rejected/Deleted Order #188', '::1', '2026-03-14 13:20:42'),
(26, 13, 'manager', 'Rejected/Deleted Order #184', '::1', '2026-03-14 13:20:51'),
(27, 13, 'manager', 'Rejected/Deleted Order #183', '::1', '2026-03-14 13:20:53'),
(28, 13, 'manager', 'Rejected/Deleted Order #182', '::1', '2026-03-14 13:20:56'),
(29, 13, 'manager', 'Rejected/Deleted Order #181', '::1', '2026-03-14 13:20:58'),
(30, 13, 'manager', 'Rejected/Deleted Order #180', '::1', '2026-03-14 13:21:00'),
(31, 13, 'manager', 'Rejected/Deleted Order #179', '::1', '2026-03-14 13:21:02'),
(32, 13, 'manager', 'Rejected/Deleted Order #178', '::1', '2026-03-14 13:21:05'),
(33, 13, 'manager', 'Rejected/Deleted Order #177', '::1', '2026-03-14 13:21:07'),
(34, 13, 'manager', 'Rejected/Deleted Order #176', '::1', '2026-03-14 13:21:10'),
(35, 13, 'manager', 'Rejected/Deleted Order #175', '::1', '2026-03-14 13:21:12'),
(36, 13, 'manager', 'Rejected/Deleted Order #174', '::1', '2026-03-14 13:21:15'),
(37, 13, 'manager', 'Rejected/Deleted Order #173', '::1', '2026-03-14 13:21:17'),
(38, 13, 'manager', 'Rejected/Deleted Order #172', '::1', '2026-03-14 13:21:19'),
(39, 13, 'manager', 'Rejected/Deleted Order #171', '::1', '2026-03-14 13:21:22'),
(40, 13, 'manager', 'Rejected/Deleted Order #170', '::1', '2026-03-14 13:21:24'),
(41, 13, 'manager', 'Rejected/Deleted Order #169', '::1', '2026-03-14 13:21:26'),
(42, 13, 'manager', 'Rejected/Deleted Order #168', '::1', '2026-03-14 13:21:29'),
(43, 13, 'manager', 'Rejected/Deleted Order #167', '::1', '2026-03-14 13:21:31'),
(44, 13, 'manager', 'Rejected/Deleted Order #166', '::1', '2026-03-14 13:21:33'),
(45, 13, 'manager', 'Rejected/Deleted Order #165', '::1', '2026-03-14 13:21:36'),
(46, 13, 'manager', 'Rejected/Deleted Order #164', '::1', '2026-03-14 13:21:39'),
(47, 13, 'manager', 'Rejected/Deleted Order #52', '::1', '2026-03-14 13:21:48'),
(48, 13, 'manager', 'Rejected/Deleted Order #163', '::1', '2026-03-14 13:21:55'),
(49, 13, 'manager', 'Rejected/Deleted Order #162', '::1', '2026-03-14 13:21:59'),
(50, 13, 'manager', 'Rejected/Deleted Order #161', '::1', '2026-03-14 13:22:01'),
(51, 13, 'manager', 'Rejected/Deleted Order #160', '::1', '2026-03-14 13:22:04'),
(52, 13, 'manager', 'Rejected/Deleted Order #159', '::1', '2026-03-14 13:22:06'),
(53, 13, 'manager', 'Rejected/Deleted Order #158', '::1', '2026-03-14 13:22:09'),
(54, 13, 'manager', 'Rejected/Deleted Order #157', '::1', '2026-03-14 13:22:11'),
(55, 13, 'manager', 'Rejected/Deleted Order #156', '::1', '2026-03-14 13:22:14'),
(56, 13, 'manager', 'Rejected/Deleted Order #155', '::1', '2026-03-14 13:22:16'),
(57, 13, 'manager', 'Rejected/Deleted Order #154', '::1', '2026-03-14 13:22:18'),
(58, 13, 'manager', 'Rejected/Deleted Order #153', '::1', '2026-03-14 13:22:20'),
(59, 13, 'manager', 'Rejected/Deleted Order #152', '::1', '2026-03-14 13:22:23'),
(60, 13, 'manager', 'Rejected/Deleted Order #151', '::1', '2026-03-14 13:22:25'),
(61, 13, 'manager', 'Rejected/Deleted Order #150', '::1', '2026-03-14 13:22:28'),
(62, 13, 'manager', 'Rejected/Deleted Order #149', '::1', '2026-03-14 13:22:30'),
(63, 13, 'manager', 'Rejected/Deleted Order #148', '::1', '2026-03-14 13:22:33'),
(64, 13, 'manager', 'Rejected/Deleted Order #147', '::1', '2026-03-14 13:22:36'),
(65, 13, 'manager', 'Rejected/Deleted Order #146', '::1', '2026-03-14 13:22:38'),
(66, 13, 'manager', 'Approved Order #197', '::1', '2026-03-14 13:36:50'),
(67, 12, 'staff', 'Delivery Completed: Order #197 for joe. Awarded 13 pts.', '::1', '2026-03-14 13:40:58'),
(68, 12, 'staff', 'Delivery Completed: Order #195 for Joseph Kinuthia. Awarded 13 pts.', '::1', '2026-03-14 13:41:01'),
(69, 0, 'System/Guest', 'New Account Created: Faith ', '::1', '2026-03-14 18:26:56'),
(70, 4, 'Faith ', 'Client Logged In: Faith ', '::1', '2026-03-14 18:27:05'),
(71, 12, 'staff', 'Approved Order #199', '::1', '2026-03-14 18:30:34'),
(72, 12, 'staff', 'Approved Order #198', '::1', '2026-03-14 18:30:39'),
(73, 12, 'staff', 'Updated 12kg inventory. New Price: 1300, New Qty: 60', '::1', '2026-03-14 18:31:13'),
(74, 13, 'manager', 'Updated 12kg inventory. New Price: 1300, New Qty: 3', '::1', '2026-03-14 18:32:42'),
(75, 13, 'manager', 'Updated 12kg inventory. New Price: 1300, New Qty: 11', '::1', '2026-03-14 18:33:00'),
(76, 13, 'manager', 'Approved Order #196', '::1', '2026-03-14 18:34:05'),
(77, 13, 'manager', 'Delivery Completed: Order #199 for Faith . Awarded 12 pts.', '::1', '2026-03-14 18:34:20'),
(78, 13, 'manager', 'Delivery Completed: Order #198 for Rop  Nahasion. Awarded 13 pts.', '::1', '2026-03-14 18:34:58'),
(79, 12, 'staff', 'Delivery Completed: Order #196 for Joseph Kinuthia. Awarded 12 pts.', '::1', '2026-03-14 18:40:55'),
(80, 12, 'staff', 'Approved Order #200', '::1', '2026-03-14 18:43:52'),
(81, 13, 'manager', 'DELETED Inventory Item: 12kg', '::1', '2026-03-14 19:01:03'),
(82, 13, 'manager', 'Added new inventory: 6kg cylinder', '::1', '2026-03-14 19:03:02'),
(83, 13, 'manager', 'Updated 6kg inventory. New Price: 1250, New Qty: 70', '::1', '2026-03-14 19:07:43'),
(84, 13, 'manager', 'Updated 6kg inventory. New Price: 1250, New Qty: 70', '::1', '2026-03-14 19:10:22'),
(85, 12, 'staff', 'Added new inventory: 13kg cylinder', '::1', '2026-03-14 19:15:41'),
(86, 12, 'staff', 'Added new inventory: 13kg cylinder', '::1', '2026-03-14 19:17:42'),
(87, 13, 'manager', 'Updated 13kg inventory. New Price: 3400, New Qty: 55', '::1', '2026-03-14 19:19:32'),
(88, 12, 'staff', 'Added new inventory: 6kg cylinder', '::1', '2026-03-14 19:35:51'),
(89, 12, 'staff', 'Added new inventory: 13kg cylinder', '::1', '2026-03-14 19:37:37'),
(90, 13, 'manager', 'Updated 6kg inventory. New Price: 1270, New Qty: 70', '::1', '2026-03-26 14:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('Manager','Staff') DEFAULT 'Staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(2, 'joe', '$2y$10$PocSkW1l2kyJBe/CsZacJ.qD0LdzxR0WzKEpnFVX3N//50mrI91hW', 'Staff'),
(3, 'Faith', '$2y$10$hjLDtKWJKMy5KBNBVbMug.LfrZwr.eqNAiD9KD9wnzPaPprQiwI4a', 'Manager'),
(4, 'Faith2', '$2y$10$VqN.7vQBIzOyTE1wXZQZxO6CAVO8mvSv0/2GdahNOUAEZ99XnHiLC', 'Manager'),
(8, 'mary', '$2y$10$mFJoYF32Xxu.oNl84W1pqeWnIY6zOzEeA5oE29kOwZPMRMMr26OP6', 'Staff'),
(12, 'staff', '$2y$10$YFCUS9MDpbbima7wdYOIQuyTp6YKMePuq0mtiRJhvsYY/SmJtjXnK', 'Staff'),
(13, 'manager', '$2y$10$gfkpiFPxHhv.lc5ZATGXi.MaN0BuKzQBDLw7/TmkAUgCcGlCUr9RG', 'Manager');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_phone` (`customer_phone`),
  ADD UNIQUE KEY `referral_code` (`referral_code`);

--
-- Indexes for table `customer_notifications`
--
ALTER TABLE `customer_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cylinder_inventory`
--
ALTER TABLE `cylinder_inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_riders`
--
ALTER TABLE `delivery_riders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_records`
--
ALTER TABLE `order_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `system_announcements`
--
ALTER TABLE `system_announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_audit_logs`
--
ALTER TABLE `system_audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customer_notifications`
--
ALTER TABLE `customer_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cylinder_inventory`
--
ALTER TABLE `cylinder_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `delivery_riders`
--
ALTER TABLE `delivery_riders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_records`
--
ALTER TABLE `order_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_announcements`
--
ALTER TABLE `system_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `system_audit_logs`
--
ALTER TABLE `system_audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
