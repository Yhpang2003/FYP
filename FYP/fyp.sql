-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2025 at 04:21 PM
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
-- Database: `fyp`
--

-- --------------------------------------------------------

--
-- Table structure for table `browse_history`
--

CREATE TABLE `browse_history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `activity_type` enum('view','add_to_cart') NOT NULL,
  `viewed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `browse_history`
--

INSERT INTO `browse_history` (`history_id`, `user_id`, `product_id`, `activity_type`, `viewed_at`) VALUES
(1, 4, 24, 'view', '2025-11-23 16:15:54'),
(2, 4, 24, 'add_to_cart', '2025-11-23 16:16:07'),
(3, 4, 1, 'add_to_cart', '2025-11-23 16:27:57'),
(4, 4, 6, 'add_to_cart', '2025-11-23 16:27:57'),
(5, 4, 19, 'view', '2025-11-26 03:08:41'),
(6, 4, 23, 'view', '2025-11-26 03:10:12'),
(7, 4, 26, 'view', '2025-11-26 03:10:25'),
(8, 4, 5, 'view', '2025-11-26 03:10:47'),
(9, 4, 11, 'view', '2025-11-26 03:11:01'),
(10, 4, 13, 'view', '2025-11-26 03:11:15'),
(11, 4, 2, 'view', '2025-11-26 03:11:32'),
(12, 4, 2, 'view', '2025-11-26 03:13:04'),
(13, 3, 2, 'view', '2025-11-26 03:13:39'),
(14, 3, 2, 'add_to_cart', '2025-11-26 03:14:06'),
(15, 3, 2, 'view', '2025-11-26 03:18:33'),
(16, 3, 2, 'view', '2025-11-26 03:41:46'),
(17, 3, 2, 'view', '2025-11-26 03:42:02'),
(18, 3, 2, 'view', '2025-11-26 03:42:53'),
(19, 3, 16, 'view', '2025-11-26 03:43:02'),
(20, 3, 16, 'view', '2025-11-26 03:46:06'),
(21, 4, 2, 'view', '2025-11-28 22:07:15'),
(22, 4, 10, 'view', '2025-11-28 22:07:55'),
(23, 4, 27, 'view', '2025-11-28 22:09:24'),
(24, 4, 19, 'view', '2025-11-28 22:09:41'),
(25, 4, 23, 'view', '2025-11-28 22:09:53'),
(26, 4, 2, 'view', '2025-11-28 22:10:54'),
(27, 4, 27, 'view', '2025-11-28 22:11:03'),
(28, 4, 6, 'view', '2025-11-28 22:13:10'),
(29, 3, 6, 'view', '2025-11-28 22:13:37'),
(30, 3, 1, 'view', '2025-11-28 22:14:25'),
(31, 4, 1, 'view', '2025-11-28 22:15:26'),
(32, 4, 5, 'view', '2025-11-28 22:15:44'),
(33, 4, 11, 'view', '2025-11-28 22:15:55'),
(34, 4, 19, 'view', '2025-11-28 22:31:23'),
(35, 4, 26, 'view', '2025-11-28 22:31:52'),
(36, 4, 23, 'view', '2025-11-28 22:31:57'),
(37, 4, 25, 'view', '2025-11-28 22:32:02'),
(38, 4, 28, 'view', '2025-11-28 22:32:09'),
(39, 4, 27, 'view', '2025-11-28 22:32:13'),
(40, 4, 24, 'view', '2025-11-28 22:32:20'),
(41, 4, 19, 'view', '2025-11-28 22:32:34'),
(42, 4, 1, 'add_to_cart', '2025-11-28 22:36:12'),
(43, 4, 6, 'add_to_cart', '2025-11-28 22:36:12'),
(44, 4, 23, 'view', '2025-11-28 22:36:36'),
(45, 4, 23, 'add_to_cart', '2025-11-28 22:36:40'),
(46, 4, 25, 'view', '2025-11-28 22:37:07'),
(47, 4, 25, 'add_to_cart', '2025-11-28 22:37:15'),
(48, 4, 23, 'view', '2025-11-28 22:39:17'),
(49, 4, 23, 'view', '2025-11-28 22:39:35'),
(50, 4, 23, 'view', '2025-11-28 22:40:41'),
(51, 4, 25, 'view', '2025-11-28 22:41:44'),
(52, 4, 14, 'view', '2025-11-28 22:42:14'),
(53, 4, 22, 'view', '2025-11-29 06:28:20'),
(54, 4, 13, 'view', '2025-11-29 18:29:15'),
(55, 4, 18, 'view', '2025-12-01 22:19:59'),
(56, 4, 18, 'add_to_cart', '2025-12-01 22:20:25'),
(57, 4, 18, 'view', '2025-12-01 22:30:52'),
(58, 4, 18, 'add_to_cart', '2025-12-01 22:30:56'),
(59, 3, 23, 'view', '2025-12-02 20:58:11'),
(60, 3, 23, 'add_to_cart', '2025-12-02 20:59:57'),
(61, 3, 4, 'view', '2025-12-02 21:01:34'),
(62, 3, 4, 'add_to_cart', '2025-12-02 21:01:44'),
(63, 3, 2, 'view', '2025-12-02 21:01:58'),
(64, 3, 23, 'view', '2025-12-02 21:02:17'),
(65, 3, 26, 'view', '2025-12-02 21:02:45'),
(66, 3, 26, 'add_to_cart', '2025-12-02 21:02:55'),
(67, 3, 45, 'view', '2025-12-03 12:20:36'),
(68, 3, 45, 'add_to_cart', '2025-12-03 12:21:21'),
(69, 3, 29, 'add_to_cart', '2025-12-03 12:25:52'),
(70, 3, 30, 'add_to_cart', '2025-12-03 12:25:52'),
(71, 3, 31, 'add_to_cart', '2025-12-03 12:25:52'),
(72, 3, 32, 'add_to_cart', '2025-12-03 12:25:52'),
(73, 3, 33, 'add_to_cart', '2025-12-03 12:25:52'),
(74, 3, 34, 'add_to_cart', '2025-12-03 12:25:52'),
(75, 3, 35, 'add_to_cart', '2025-12-03 12:25:52'),
(76, 3, 27, 'add_to_cart', '2025-12-04 06:25:47'),
(77, 3, 42, 'add_to_cart', '2025-12-04 06:25:47'),
(78, 3, 43, 'add_to_cart', '2025-12-04 06:25:47'),
(79, 3, 44, 'add_to_cart', '2025-12-04 06:25:47'),
(80, 3, 45, 'add_to_cart', '2025-12-04 06:25:47'),
(81, 3, 46, 'add_to_cart', '2025-12-04 06:25:47'),
(82, 3, 1, 'view', '2025-12-06 01:09:30'),
(83, 3, 46, 'view', '2025-12-06 01:12:25'),
(84, 3, 45, 'view', '2025-12-06 01:12:37'),
(85, 3, 45, 'add_to_cart', '2025-12-06 01:13:02'),
(86, 3, 24, 'view', '2025-12-06 05:05:30'),
(87, 3, 24, 'add_to_cart', '2025-12-06 05:05:33'),
(88, 3, 19, 'view', '2025-12-06 05:21:22'),
(89, 3, 19, 'add_to_cart', '2025-12-06 05:21:25'),
(90, 3, 23, 'view', '2025-12-10 02:49:32'),
(91, 3, 23, 'add_to_cart', '2025-12-10 02:49:43'),
(92, 3, 16, 'view', '2025-12-10 03:39:34'),
(93, 3, 16, 'add_to_cart', '2025-12-10 03:39:38'),
(94, 3, 2, 'view', '2025-12-10 03:42:44'),
(95, 3, 2, 'add_to_cart', '2025-12-10 03:42:47'),
(96, 3, 2, 'view', '2025-12-10 03:44:11'),
(97, 3, 2, 'add_to_cart', '2025-12-10 03:44:14'),
(98, 3, 29, 'view', '2025-12-10 03:45:46'),
(99, 3, 29, 'add_to_cart', '2025-12-10 03:45:49'),
(100, 3, 2, 'view', '2025-12-10 04:06:41'),
(101, 3, 2, 'add_to_cart', '2025-12-10 04:06:48'),
(102, 3, 2, 'view', '2025-12-13 13:12:31'),
(103, 3, 2, 'add_to_cart', '2025-12-13 13:17:55'),
(104, 4, 31, 'view', '2025-12-14 15:10:09'),
(105, 4, 31, 'add_to_cart', '2025-12-14 15:10:27'),
(106, 3, 11, 'view', '2025-12-16 08:12:21'),
(107, 3, 11, 'add_to_cart', '2025-12-16 08:14:52'),
(108, 3, 11, 'view', '2025-12-16 08:17:03'),
(109, 3, 23, 'view', '2025-12-16 08:17:12'),
(110, 3, 11, 'view', '2025-12-16 08:17:15'),
(111, 3, 27, 'add_to_cart', '2025-12-16 08:17:48'),
(112, 3, 42, 'add_to_cart', '2025-12-16 08:17:48'),
(113, 3, 43, 'add_to_cart', '2025-12-16 08:17:48'),
(114, 3, 44, 'add_to_cart', '2025-12-16 08:17:48'),
(115, 3, 45, 'add_to_cart', '2025-12-16 08:17:48'),
(116, 3, 46, 'add_to_cart', '2025-12-16 08:17:48'),
(117, 3, 8, 'view', '2025-12-16 09:06:56'),
(118, 3, 8, 'add_to_cart', '2025-12-16 09:07:14'),
(119, 3, 1, 'view', '2025-12-16 09:20:14'),
(120, 3, 1, 'add_to_cart', '2025-12-16 09:20:19'),
(121, 3, 17, 'view', '2025-12-16 09:23:00'),
(122, 3, 17, 'add_to_cart', '2025-12-16 09:23:12');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `created_at`) VALUES
(1, 2, '2025-10-23 14:53:00'),
(2, 1, '2025-10-24 15:20:01'),
(3, 3, '2025-11-03 14:07:40'),
(4, 4, '2025-11-15 15:25:51');

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`cart_item_id`, `cart_id`, `product_id`, `quantity`) VALUES
(2, 2, 1, 4),
(9, 1, 3, 1),
(11, 1, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(100, 'Hand Tools'),
(105, 'Metal Product'),
(102, 'Paintings'),
(104, 'Pipes'),
(101, 'Powered Tools'),
(103, 'Wood Materials');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `payment_method` enum('CreditCard','BankTransfer','E-Wallet') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`order_id`, `user_id`, `order_date`, `total_amount`, `address`, `payment_method`) VALUES
(1, 2, '2025-10-25 17:36:42', 32.50, 'sadasdasdasdsadas', 'BankTransfer'),
(2, 2, '2025-10-26 15:39:29', 416.50, 'Jalan Malinja, Taman Bunga Raya, 53000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'CreditCard'),
(3, 2, '2025-10-30 14:09:53', 122.00, '23 Taman Pelangi\r\nLorong 6', 'BankTransfer'),
(4, 3, '2025-11-03 14:07:58', 80.00, '23 , Taman Pelangi', 'BankTransfer'),
(5, 3, '2025-12-02 13:00:17', 288.00, '23 Taman Pelangi\r\nLorong 6', 'BankTransfer'),
(6, 3, '2025-12-02 13:03:27', 75.00, 'Jalan Malinja, Taman Bunga Raya, 53000 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 'CreditCard'),
(7, 3, '2025-12-03 04:24:43', 54.00, '23 Taman Pelangi\r\nLorong 6', 'E-Wallet'),
(8, 3, '2025-12-03 22:24:56', 360.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'CreditCard'),
(9, 3, '2025-12-04 14:06:07', 79.00, '23 Taman Pelangi, 53300 Sibu, Sarawak', 'CreditCard'),
(10, 3, '2025-12-05 17:20:13', 28.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'CreditCard'),
(11, 3, '2025-12-05 21:06:27', 50.00, '23 Taman Pelangi, 27000 SIBU, Sabah', 'CreditCard'),
(12, 3, '2025-12-05 21:21:50', 42.50, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'CreditCard'),
(13, 3, '2025-12-09 19:22:32', 18.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'CreditCard'),
(14, 3, '2025-12-09 19:42:25', 55.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'CreditCard'),
(15, 3, '2025-12-09 19:43:05', 40.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'CreditCard'),
(16, 3, '2025-12-09 19:44:53', 40.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'CreditCard'),
(17, 3, '2025-12-09 19:46:05', 28.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'E-Wallet'),
(18, 3, '2025-12-09 20:07:02', 40.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'E-Wallet'),
(19, 4, '2025-12-14 07:11:22', 26.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'E-Wallet'),
(20, 3, '2025-12-16 00:16:34', 22010.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'CreditCard'),
(24, 3, '2025-12-16 01:13:36', 2040.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'E-Wallet'),
(25, 3, '2025-12-16 01:20:39', 60.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'E-Wallet'),
(26, 3, '2025-12-16 01:23:24', 556.00, '23 Taman Pelangi, 27000 JERANTUT, Pahang', 'E-Wallet');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 6, 1, 32.50),
(2, 2, 4, 30.50),
(2, 3, 5, 30.50),
(2, 5, 4, 30.50),
(2, 7, 1, 20.00),
(3, 4, 4, 30.50),
(4, 7, 4, 20.00),
(5, 1, 1, 50.00),
(5, 2, 1, 30.00),
(5, 6, 2, 100.00),
(5, 23, 1, 8.00),
(6, 4, 1, 50.00),
(6, 26, 1, 25.00),
(7, 45, 3, 18.00),
(8, 29, 2, 18.00),
(8, 30, 2, 12.00),
(8, 31, 2, 8.00),
(8, 32, 1, 45.00),
(8, 33, 1, 15.00),
(8, 34, 1, 189.00),
(8, 35, 1, 25.00),
(9, 27, 1, 12.00),
(9, 42, 1, 10.00),
(9, 43, 6, 1.50),
(9, 44, 2, 2.00),
(9, 45, 1, 18.00),
(9, 46, 1, 6.00),
(10, 45, 1, 18.00),
(11, 24, 1, 30.00),
(12, 19, 1, 32.50),
(13, 23, 1, 8.00),
(14, 16, 1, 45.00),
(15, 2, 1, 30.00),
(16, 2, 1, 30.00),
(17, 29, 1, 18.00),
(18, 2, 1, 30.00),
(19, 31, 2, 8.00),
(20, 11, 20, 1100.00),
(24, 8, 29, 70.00),
(25, 1, 1, 50.00),
(26, 17, 26, 21.00);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `status` enum('Available','Disabled') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `name`, `description`, `image`, `price`, `stock_quantity`, `category_id`, `status`) VALUES
(1, '20oz Steel Claw Hammer', 'Heavy-duty forged steel claw hammer with a comfortable anti-vibration rubber grip. Perfect for framing, roofing, and general construction.', 'uploads/product_images/691a016d5d155.jpg', 50.00, 1, 100, 'Available'),
(2, '10-Piece Magnetic Screwdriver Set', 'Precision screwdriver set with Phillips and flathead tips. Hardened, magnetic tips help hold small screws. Ideal for electronics, eyeglasses, and small repairs.', 'uploads/product_images/691a01287cc2e.jpg', 30.00, 12, 100, 'Available'),
(3, '5m/16ft Retracting Tape Measure', 'Durable, easy-to-read tape measure with a belt clip and reliable locking mechanism. Essential for any woodworking or home project.', 'uploads/product_images/691a007abf027.jpg', 30.50, 15, 100, 'Available'),
(4, '10-inch Adjustable Wrench', 'All-purpose adjustable wrench with a wide jaw capacity. Forged chrome-vanadium steel for strength. Great for plumbing and tightening bolts.', 'uploads/product_images/6919ffe7e9ea8.jpg', 50.00, 15, 100, 'Available'),
(5, 'Utility Knife with Snap-Off Blades', 'Retractable utility knife for all-purpose cutting. Comes with 10 replaceable snap-off blades. Perfect for drywall, carpet, and crafts.', 'uploads/product_images/6919ff93badd8.jpg', 7.00, 41, 100, 'Available'),
(6, '12-inch Combination Square', 'A versatile measuring tool for woodworking. Includes a spirit level vial and a metal scribe. Use it to find perfect 90-degree and 45-degree angles.', 'uploads/product_images/6919ff1969d3b.jpg', 100.00, 27, 100, 'Available'),
(7, '20V Cordless Power Drill Kit', 'High-performance 20-volt cordless drill with 2-speed settings. Kit includes two lithium-ion batteries, a fast charger, and a carrying case.', 'uploads/product_images/68f8ea656fa0d.jpg', 20.00, 15, 101, 'Available'),
(8, '5-inch Random Orbital Sander', 'Electric random orbital sander with a dust collection bag. Provides a smooth, swirl-free finish. Perfect for sanding wood furniture before painting.', 'uploads/product_images/6919f03b64ad4.jpg', 70.00, 1, 101, 'Available'),
(9, 'Variable Speed Jigsaw', ' Powerful 5-amp corded jigsaw for cutting intricate curves. Variable speed control from 0-3000 SPM. Includes wood and metal cutting T-shank blades.', 'uploads/product_images/6919f09b1784c.jpg', 150.00, 20, 101, 'Available'),
(10, '7-1/4 inch Circular Saw with Laser Guide', 'Lightweight circular saw with a powerful 13-amp motor. Features a red laser guide for straight, accurate cuts in lumber and wood sheets.', 'uploads/product_images/6919f14a67d41.jpg', 900.00, 20, 101, 'Available'),
(11, 'Rotary Tool Kit (Dremel-style)', 'Versatile high-speed rotary tool with a 40-piece accessory kit. Great for cutting, grinding, polishing, and engraving small craft projects.', 'uploads/product_images/6919f2129db24.jpg', 1100.00, 40, 101, 'Available'),
(12, 'Interior Latex Paint (Flat White, 1 Gallon)', 'One-gallon can of premium interior latex paint. Flat white finish with primer included. Low VOC, perfect for bedrooms and living rooms.', 'uploads/product_images/6919f2cb77052.jpg', 40.00, 30, 102, 'Available'),
(13, '9-inch Paint Roller Kit (5-Piece)', 'Complete paint roller kit. Includes one 9\" roller frame, two roller covers for semi-smooth walls, a metal paint tray, and a disposable liner.', 'uploads/product_images/6919f310b00a3.jpg', 14.50, 40, 102, 'Available'),
(14, '5-Piece Angled Paint Brush Set', 'Professional set of 5 paint brushes with angled bristles. Perfect for \"cutting in\" clean lines on edges, trim, and corners. For use with all paint types.', 'uploads/product_images/6919f36493eef.jpg', 30.00, 25, 102, 'Available'),
(15, 'Blue Painter\'s Tape (1.5-inch, 60-yd)', '1.5-inch wide blue painter\'s tape. Medium adhesion for crisp paint lines on walls and trim. Removes cleanly without residue for up to 14 days.', 'uploads/product_images/6919f3af35fcc.jpg', 5.00, 40, 102, 'Available'),
(16, '1-Quart Wood Stain (Dark Walnut)', 'Oil-based wood stain in a 1-quart can. Provides a rich, dark walnut finish for furniture, cabinets, and woodworking projects.', 'uploads/product_images/6919f4122b3ae.jpg', 45.00, 29, 102, 'Available'),
(17, '9x12 ft Canvas Drop Cloth', 'Heavy-duty, reusable canvas drop cloth. Protects floors and furniture from paint spills and drips. Machine washable.', 'uploads/product_images/6919f44c2b4ab.jpg', 21.00, 4, 102, 'Available'),
(18, 'Pine Plywood Sheet (4x8 ft, 1/2 in)', 'Half-inch thick (1/2\") furniture-grade pine plywood sheet. Measures 4 feet by 8 feet. Smoothly sanded surface, ready for staining or painting.', 'uploads/product_images/6919f4bcc2724.jpg', 45.00, 40, 103, 'Available'),
(19, '2x4 Lumber (8 ft length)', 'Standard 8-foot long 2x4 SPF lumber stud. Ideal for wall framing, shelving, and general construction or structural DIY projects.', 'uploads/product_images/6919f524e9424.jpg', 32.50, 19, 103, 'Available'),
(20, 'Oak Hardwood Board (1x6 in, 4 ft)', 'A 4-foot plank of solid Red Oak hardwood. Measures 1-inch thick by 6-inches wide. Beautiful grain, perfect for shelving or high-end furniture.\r\n\r\n', 'uploads/product_images/6919f58b61e1f.jpg', 40.00, 30, 103, 'Available'),
(21, 'MDF Sheet (Medium-Density Fiberboard)', '4x4 ft sheet of 3/4-inch Medium-Density Fiberboard (MDF). Stable and easy to machine. Ideal for building cabinets, speaker boxes, or painted projects.', 'uploads/product_images/6919f5e0188b1.jpg', 15.90, 35, 103, 'Available'),
(22, 'Wooden Dowel Rods (1-inch diameter)', '1-inch diameter solid wood dowel rod, 36 inches long. Made from poplar or birch. Great for craft projects, curtain rods, or small joinery.', 'uploads/product_images/6919f6aad1e5d.jpg', 24.00, 50, 103, 'Available'),
(23, '1/2-inch PVC Pipe (5 ft length)', '5-foot section of 1/2-inch diameter Schedule 40 rigid PVC pipe. Durable white pipe for plumbing, sprinkler irrigation, or DIY projects.', 'uploads/product_images/6919fbfec7668.jpg', 8.00, 28, 104, 'Available'),
(24, ' 3/4-inch Copper Pipe (2 ft length)', '2-foot section of 3/4-inch Type L rigid copper pipe. For domestic hot and cold water supply lines. Resists corrosion. Requires soldering.', 'uploads/product_images/6919fc677f296.jpg', 30.00, 19, 104, 'Available'),
(25, '1/2-inch PVC 90-Degree Elbow (4-pack)', '4-pack of 1/2-inch 90-degree elbow fittings. Slip-socket connections for cold water PVC pipe. Used to turn corners in plumbing projects.', 'uploads/product_images/6919fd5b68d1d.jpg', 5.00, 20, 104, 'Available'),
(26, '3/4-inch Brass T-Fitting', 'Lead-free 3/4-inch copper T-fitting. For splitting a copper pipe line into two directions. Requires solder for a permanent, leak-proof joint.', 'uploads/product_images/6919fdeeca289.jpg', 25.00, 29, 104, 'Available'),
(27, 'Ratcheting PVC Pipe Cutter', 'Heavy-duty ratcheting PVC pipe cutter. Easily cuts plastic pipes up to 1-1/4 inch in diameter. Clean, fast cuts for plumbing and DIY.', 'uploads/product_images/6919fe46ba4ac.jpg', 12.00, 19, 104, 'Available'),
(28, 'PTFE Thread Seal Tape (Plumber\'s Tape)', '1/2-inch wide white PTFE thread seal tape. Provides a watertight seal on threaded pipe joints for plumbing.', 'uploads/product_images/6919fe798e31d.jpg', 0.50, 50, 104, 'Available'),
(29, '3/4\" Black Iron Pipe (30cm)', 'Heavy-duty threaded steel pipe with a black industrial finish. Perfect for plumbing applications or creating rustic industrial furniture and shelving brackets. Durable and rigid.', 'uploads/product_images/692f29b755263.jpg', 18.00, 27, 104, 'Available'),
(30, 'Iron Pipe Flange (3/4\")', 'Malleable iron floor flange with four screw holes for secure mounting. Essential fitting for anchoring pipes to walls or floors in DIY furniture projects.', 'uploads/product_images/692f29f905096.jpg', 12.00, 28, 104, 'Available'),
(31, 'Iron Pipe Elbow (90 Degree)', '90-degree threaded elbow fitting for connecting two pipes at a right angle. Made of durable cast iron, suitable for steam, gas, or DIY pipe shelving structures.', 'uploads/product_images/692f2b1ab731f.jpg', 8.00, 26, 104, 'Available'),
(32, 'Solid Pine Plank (1 meter)', 'High-quality solid pine wood board with natural grain patterns. Unfinished and ready for staining, painting, or varnishing. Ideal for shelving, table tops, and carpentry.', 'uploads/product_images/692f2b499f624.jpg', 45.00, 29, 103, 'Available'),
(33, 'Matte Black Spray Paint', 'Fast-drying aerosol spray paint with a flat matte black finish. Provides excellent adhesion to metal, wood, and plastic. Rust-preventative formula for indoor and outdoor use.', 'uploads/product_images/692f2c7c490ba.jpg', 15.00, 39, 102, 'Available'),
(34, 'Impact Drill 12V', 'Cordless 12V power drill driver with variable speed and torque settings. Includes rechargeable battery. Perfect for drilling holes in wood, metal, and masonry or driving screws.', 'uploads/product_images/692f2cb6d7430.jpg', 189.00, 19, 101, 'Available'),
(35, 'Spirit Level (30cm)', 'Precision measuring tool with horizontal and vertical bubble vials. Ensures accuracy in construction, carpentry, and hanging shelves perfectly straight.', 'uploads/product_images/692f2d774c8eb.jpg', 25.00, 29, 100, 'Available'),
(36, 'Plywood Sheet (12mm)', 'Versatile 12mm thick plywood sheet. Strong, lightweight, and easy to cut. Ideal for making cabinets, boxes, furniture backing, and structural DIY projects.', 'uploads/product_images/692f2ec0a7ffe.jpg', 60.00, 30, 103, 'Available'),
(37, 'Wooden Dowel (25mm)', 'Smooth, round cylindrical wood rod. 25mm diameter. Commonly used as a handle for tote boxes, joinery pins, or craft applications. Easy to cut and sand.', 'uploads/product_images/692f2eef11250.jpg', 10.00, 50, 103, 'Available'),
(38, 'Hand Saw (Crosscut)', 'Sharp steel blade hand saw with an ergonomic grip. Designed for fast, clean cuts across the grain of wood, plywood, and plastic. Essential for carpentry.', 'uploads/product_images/692f2f18271a1.jpg', 35.00, 40, 100, 'Available'),
(39, 'Claw Hammer (16oz)', 'General-purpose claw hammer with a forged steel head and fiberglass handle for shock absorption. Features a curved claw for pulling nails and prying wood.', 'uploads/product_images/692f2f74540ff.jpg', 22.00, 35, 100, 'Available'),
(40, 'Wood Varnish (Clear)', 'Transparent protective wood finish. Seals timber against moisture, scratches, and stains while enhancing the natural wood grain. Glossy finish.', 'uploads/product_images/692f2fa94b8e5.jpg', 38.00, 30, 102, 'Available'),
(41, 'Paint Brush (2 inch)', 'Synthetic bristle brush suitable for all paints, stains, and varnishes. Soft tips ensure a smooth finish with minimal brush marks.', 'uploads/product_images/692f2fda244c4.jpg', 6.00, 35, 102, 'Available'),
(42, 'PVC Pipe 20mm (2m)', 'Lightweight and rigid white PVC pipe (20mm diameter). corrosion-resistant. Standard plumbing material that is also popular for crafting frames, stands, and racks.', 'uploads/product_images/692f31157248f.jpg', 10.00, 29, 104, 'Available'),
(43, 'PVC Elbow Connector', '90-degree slip-fit connector for 20mm PVC pipes. Joins two pipes at a right angle. smooth interior allows for unrestricted water flow or sturdy structural joints.', 'uploads/product_images/692f31499faff.jpg', 1.50, 34, 104, 'Available'),
(44, 'PVC Tee Connector', 'T-shaped slip-fit fitting for joining three 20mm PVC pipes. Essential for branching lines in plumbing or creating stability legs in DIY PVC structures.', 'uploads/product_images/692f3174c18b3.jpg', 2.00, 48, 104, 'Available'),
(45, 'Metallic Spray Paint', 'Premium metallic finish spray paint (Gold/Silver). Provides a shiny, reflective coating. transforms plastic, wood, and metal surfaces to look like polished metal.', 'uploads/product_images/692f31c90a3bb.jpg', 18.00, 35, 102, 'Available'),
(46, 'Sandpaper (Assorted)', 'Pack of assorted grit sandpaper sheets (coarse to fine). Used for smoothing rough wood edges, removing old paint, or prepping PVC pipes for painting.', 'uploads/product_images/692f31e9661df.jpg', 6.00, 39, 100, 'Available'),
(47, 'Orange Paint', 'A Orange Paint', 'uploads/product_images/692fbdefce725.jpg', 30.00, 40, 102, 'Disabled'),
(48, 'Red Paint', 'A red paint', 'uploads/product_images/6940a6579fcb2.jpg', 30.00, 30, 102, 'Disabled');

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(150) NOT NULL,
  `project_image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`project_id`, `project_name`, `project_image`, `description`) VALUES
(2, 'Industrial Pipe Wall Shelf', 'uploads/project_images/692f342454628.jpg', 'Create a stylish and functional storage solution with this Industrial Pipe Wall Shelf. This project combines the rustic charm of solid pine wood with the rugged look of black iron piping. Perfect for displaying books, plants, or decor in a living room or bedroom. Ideally suited for those looking to practice drilling into walls and assembling threaded pipe fittings. This design brings a modern, loft-style aesthetic to any home.'),
(3, 'Wooden Tool Caddy (Portable Toolbox)', 'uploads/project_images/692f3b63c5a80.jpg', 'Organize your workspace with this sturdy Wooden Tool Caddy. This classic woodworking project teaches the basics of measuring, sawing, and nailing. The result is a durable, portable toolbox perfect for carrying hand tools, gardening supplies, or art materials. Built from strong plywood and a smooth dowel handle, it is a great essential project for any DIY beginner looking to build their first piece of custom storage.'),
(4, 'PVC Pipe Laptop Stand', 'uploads/project_images/692f3c0ca1afc.jpg', 'Improve your home office ergonomics with this custom PVC Pipe Laptop Stand. This lightweight and modern stand elevates your laptop screen to eye level, reducing neck strain and improving cooling airflow. Built entirely from affordable PVC pipes and connectors, this project requires no power tools and can be spray painted to match any desk setup. It is a quick, easy, and highly functional project perfect for students and professionals.');

-- --------------------------------------------------------

--
-- Table structure for table `project_material`
--

CREATE TABLE `project_material` (
  `project_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_material`
--

INSERT INTO `project_material` (`project_id`, `product_id`, `quantity`) VALUES
(2, 29, 2),
(2, 30, 2),
(2, 31, 2),
(2, 32, 1),
(2, 33, 1),
(2, 34, 1),
(2, 35, 1),
(3, 36, 1),
(3, 37, 1),
(3, 38, 1),
(3, 39, 1),
(3, 40, 1),
(3, 41, 1),
(4, 27, 1),
(4, 42, 1),
(4, 43, 6),
(4, 44, 2),
(4, 45, 1),
(4, 46, 1);

-- --------------------------------------------------------

--
-- Table structure for table `project_step`
--

CREATE TABLE `project_step` (
  `step_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `step_number` int(11) NOT NULL,
  `step_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_step`
--

INSERT INTO `project_step` (`step_id`, `project_id`, `step_number`, `step_description`) VALUES
(7, 2, 1, 'Clean: Wipe down iron pipes to remove manufacturing grease.'),
(8, 2, 2, 'Assemble: Screw pipes into flanges and elbows to create L-shaped brackets.'),
(9, 2, 3, 'Prepare Wall: Use the Spirit Level to mark straight lines.'),
(10, 2, 4, 'Mount: Use the Impact Drill to drill holes and secure the flanges to the wall.'),
(11, 2, 5, 'Finish: Place the Pine Plank across the pipe brackets.'),
(12, 3, 1, 'Cut: Use the Hand Saw to cut the Plywood into 1 base, 2 sides, and 2 tall end pieces.'),
(13, 3, 2, 'Drill: Create holes in the top of the end pieces for the handle.\r\n'),
(14, 3, 3, 'Assemble: Use the Claw Hammer and nails to join the plywood pieces together.'),
(15, 3, 4, 'Handle: Cut the Wooden Dowel to size and slide it through the holes.'),
(16, 3, 5, 'Protect: Apply Wood Varnish using the Paint Brush to seal the wood.'),
(17, 4, 1, 'Measure: Determine the width needed for your laptop.'),
(18, 4, 2, 'Cut: Use the Pipe Cutter to cut the PVC Pipe into 5 segments (1 crossbar, 2 legs, 2 supports).'),
(19, 4, 3, 'Prep: Scuff the smooth plastic surface with Sandpaper to help the paint stick.'),
(20, 4, 4, 'Paint: Coat all pipes, 6 Elbows, and 2 Tee Connectors with Metallic Spray Paint.'),
(21, 4, 5, 'Connect: Assemble the pieces together to form the stand structure.'),
(23, 4, 8, 'Step8'),
(24, 4, 6, 'Step 6');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Member') NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password`, `role`, `status`, `reset_token`, `reset_token_expiry`) VALUES
(1, 'YHPANG', 'pangyuhang2003@gmail.com', '$2y$10$2/3OsNHIgAr9Qx8LXttLuuPL22VL9drGS42h0cCnrvSD89KeYXAwe', 'Admin', 'Active', NULL, NULL),
(2, 'Man', 'user@gmail.com', '$2y$10$w.4hIXyTXobB.M7umQDIV.cQhsYC/oqzWMRVp.imyMv6JZ50Uo4Qy', 'Member', 'Active', NULL, NULL),
(3, 'YHPANG2003', 'pangyh-wm22@student.tarc.edu.my', '$2y$10$rrwOPQ3sYhhfPkGxJ1seGO7elf60LW3skKdO5niczD9q84RH6d7Ai', 'Member', 'Active', NULL, NULL),
(4, 'User1', 'User1@gmail.com', '$2y$10$P7.2zDHAzES0cf/Jz7ERp.5uJJHDc2zc4n8KEYRLtUYEeyCVfVE0q', 'Member', 'Active', '7ef13bebf76e8130c00c1e42c7269b229fcf97fac6676303bee040107bf7c52d', NULL),
(5, 'Admin1', 'Admin1@gmail.com', '$2y$10$WjsQhABDLvAu4DqjdGAMv.daT9TH5ZuXXivQvg.aW2RmGPBWmJCte', 'Admin', 'Active', '3538b514f872d97f11d604aabfe58ee52051021cd7ac85663731a7423849ff68', NULL),
(6, '2409098', 'User2@gmail.com', '$2y$10$9n5N05ZqIRNaO2Rf8BvjluWnYw7Il6kE.1zyy5rnF8q63/qg2by3a', 'Member', 'Inactive', '5e59aa1c1b1b4db5be895e2e9b684d8fca6413159a891d2ab6037717cf4d358d', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `browse_history`
--
ALTER TABLE `browse_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`project_id`);

--
-- Indexes for table `project_material`
--
ALTER TABLE `project_material`
  ADD PRIMARY KEY (`project_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `project_step`
--
ALTER TABLE `project_step`
  ADD PRIMARY KEY (`step_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `browse_history`
--
ALTER TABLE `browse_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart_item`
--
ALTER TABLE `cart_item`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `project_step`
--
ALTER TABLE `project_step`
  MODIFY `step_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `browse_history`
--
ALTER TABLE `browse_history`
  ADD CONSTRAINT `browse_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `browse_history_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`),
  ADD CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`order_id`),
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `project_material`
--
ALTER TABLE `project_material`
  ADD CONSTRAINT `project_material_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`),
  ADD CONSTRAINT `project_material_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `project_step`
--
ALTER TABLE `project_step`
  ADD CONSTRAINT `project_step_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
