-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2024 at 05:36 AM
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
-- Database: `car_rental_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `UserName` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `updationDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `UserName`, `Password`, `updationDate`) VALUES
(1, 'admin', '5c428d8875d2948607f3e3fe134d71b4', '2024-05-01 12:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `booking_req`
--

CREATE TABLE `booking_req` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `hours_booked` int(11) DEFAULT NULL,
  `days_booked` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `payment_way` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_req`
--

INSERT INTO `booking_req` (`id`, `user_id`, `vehicle_id`, `start_date`, `end_date`, `start_time`, `end_time`, `hours_booked`, `days_booked`, `total_price`, `status`, `payment_way`, `created_at`) VALUES
(1, 1, 1, '2024-11-18', '2024-11-19', '00:00:00', '23:59:59', NULL, 2, 3600.00, 'Approved', 'Razorpay', '2024-11-18 16:12:49'),
(2, 1, 1, '2024-11-21', '2024-11-22', '00:00:00', '23:59:59', NULL, 2, 3600.00, 'Approved', 'Razorpay', '2024-11-18 16:14:28'),
(3, 1, 1, '2024-11-23', '2024-11-24', '00:00:00', '23:59:59', NULL, 2, 3600.00, 'Approved', 'Razorpay', '2024-11-18 16:15:32'),
(4, 1, 1, '2024-11-25', '2024-11-26', '00:00:00', '23:59:59', NULL, 2, 3600.00, 'Approved', 'Razorpay', '2024-11-18 16:17:34'),
(5, 1, 2, '2024-11-18', '2024-11-20', '00:00:00', '23:59:59', NULL, 3, 5400.00, 'Approved', 'Razorpay', '2024-11-18 16:20:36'),
(6, 1, 2, '2024-11-21', '2024-11-23', '00:00:00', '23:59:59', NULL, 3, 5400.00, 'Approved', 'Razorpay', '2024-11-18 16:21:40'),
(7, 1, 2, '2024-12-01', '2024-12-04', '00:00:00', '23:59:59', NULL, 4, 7200.00, 'Approved', 'Razorpay', '2024-11-18 16:22:48'),
(8, 1, 2, '2024-12-06', '2024-12-10', '00:00:00', '23:59:59', NULL, 5, 9000.00, 'Approved', 'Razorpay', '2024-11-18 16:23:43'),
(9, 1, 2, '2025-01-01', '2025-01-04', '00:00:00', '23:59:59', NULL, 4, 7200.00, 'Approved', 'Razorpay', '2024-11-18 16:25:14'),
(10, 1, 7, '2024-11-19', '2024-11-23', '00:00:00', '23:59:59', NULL, 5, 6500.00, 'Approved', 'Razorpay', '2024-11-18 16:29:49'),
(11, 1, 7, '2024-11-25', '2024-11-30', '00:00:00', '23:59:59', NULL, 6, 7800.00, 'Approved', 'Razorpay', '2024-11-18 16:30:57'),
(12, 1, 7, '2024-12-03', '2024-12-07', '00:00:00', '23:59:59', NULL, 5, 6500.00, 'Approved', 'Razorpay', '2024-11-18 16:31:56'),
(13, 1, 7, '2024-12-10', '2024-12-14', '00:00:00', '23:59:59', NULL, 5, 6500.00, 'Approved', 'Razorpay', '2024-11-18 16:33:05'),
(14, 1, 7, '2025-01-07', '2025-01-11', '00:00:00', '23:59:59', NULL, 5, 6500.00, 'Approved', 'Razorpay', '2024-11-18 16:34:06'),
(15, 1, 10, '2024-11-21', '2024-11-25', '00:00:00', '23:59:59', NULL, 5, 9000.00, 'Approved', 'Razorpay', '2024-11-18 16:36:01'),
(16, 1, 10, '2024-11-27', '2024-11-30', '00:00:00', '23:59:59', NULL, 4, 7200.00, 'Approved', 'Razorpay', '2024-11-18 16:37:38'),
(17, 1, 10, '2024-12-02', '2024-12-07', '00:00:00', '23:59:59', NULL, 6, 10800.00, 'Approved', 'Razorpay', '2024-11-18 16:38:57'),
(18, 1, 10, '2024-12-09', '2024-12-14', '00:00:00', '23:59:59', NULL, 6, 10800.00, 'Approved', 'Razorpay', '2024-11-18 16:40:08'),
(19, 1, 10, '2025-01-06', '2025-01-11', '00:00:00', '23:59:59', NULL, 6, 10800.00, 'Approved', 'Razorpay', '2024-11-18 16:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `adding_date` date NOT NULL,
  `updation_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `brand_name`, `adding_date`, `updation_date`) VALUES
(3, 'Mahindra', '2024-09-17', '2024-09-17'),
(4, 'TATA', '2024-09-19', '2024-09-19'),
(6, 'Volkswagen', '2024-09-19', '2024-09-19'),
(7, 'BMW', '2024-09-19', '2024-09-19'),
(8, 'Toyota', '2024-09-19', '2024-09-19'),
(9, 'Maruti Suzuki', '2024-09-19', '2024-09-19'),
(10, 'Skoda', '2024-09-19', '2024-09-19'),
(11, 'Ford', '2024-09-24', '2024-09-24');

-- --------------------------------------------------------

--
-- Table structure for table `car_owner`
--

CREATE TABLE `car_owner` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_owner`
--

INSERT INTO `car_owner` (`id`, `car_id`, `owner_id`) VALUES
(0, 1, 6),
(0, 7, 1),
(0, 4, 6),
(0, 9, 6),
(0, 12, 1),
(0, 1, 1),
(0, 2, 1),
(0, 7, 1),
(0, 9, 1),
(0, 6, 1),
(0, 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `car_photos`
--

CREATE TABLE `car_photos` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `photo_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_photos`
--

INSERT INTO `car_photos` (`id`, `request_id`, `photo_path`) VALUES
(1, 1, 'uploads/cars/Brezza1.png'),
(2, 1, 'uploads/cars/Brezza2.png'),
(3, 1, 'uploads/cars/Brezza3.png'),
(4, 2, 'uploads/cars/fortuner1.png'),
(5, 2, 'uploads/cars/fortuner2.png'),
(6, 2, 'uploads/cars/fortuner3.png'),
(7, 3, 'uploads/cars/safari1.png'),
(8, 4, 'uploads/cars/Scorpio1.png'),
(9, 5, 'uploads/cars/thar1.png'),
(10, 6, 'uploads/cars/Virtus GT1.png'),
(11, 6, 'uploads/cars/Virtus GT2.png'),
(12, 6, 'uploads/cars/Virtus GT3.png');

-- --------------------------------------------------------

--
-- Table structure for table `car_rent_requests`
--

CREATE TABLE `car_rent_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `car_brand` varchar(255) NOT NULL,
  `car_model` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `kilometers_driven` int(11) NOT NULL,
  `fuel_type` varchar(50) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_rent_requests`
--

INSERT INTO `car_rent_requests` (`id`, `user_id`, `name`, `email`, `address`, `car_brand`, `car_model`, `year`, `kilometers_driven`, `fuel_type`, `status`, `created_at`) VALUES
(1, 1, 'Tushar', 'kingdhoni1107@gmail.com', 'Motavadala', 'Maruti', 'Brezza', 2024, 12000, 'Petrol', 'Pending', '2024-09-19 17:01:16'),
(2, 1, 'Maulik', 'maulikbhalodiya9999@gmail.com', 'Rajkot', 'Toyota', 'fortuner1', 2024, 12000, 'Diesel', 'Rejected', '2024-09-19 17:01:54'),
(3, 1, 'Bharat', 'bjbapusolanki@gmail.com', 'Mehsansa', 'TATA', 'safari', 2024, 12000, 'Petrol', 'Approved', '2024-09-19 17:02:19'),
(4, 1, 'Maulik', 'maulikbhalodiya9999@gmail.com', 'rajkot', 'Mahindra', 'Scorpio', 2023, 10098, 'Petrol', 'Approved', '2024-09-19 17:05:21'),
(5, 1, 'Tushar', 'kingdhoni1107@gmail.com', 'Motavadala', 'TATA', 'Tigor', 2023, 120000, 'Petrol', 'Approved', '2024-09-19 18:22:36'),
(6, 1, 'Bharat', 'bjbapusolanki@gmail.com', 'mehsana', 'KIA', 'sonet', 2022, 13000, 'Petrol', 'Pending', '2024-09-20 02:45:04');

-- --------------------------------------------------------

--
-- Table structure for table `contact_queries`
--

CREATE TABLE `contact_queries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `subject` varchar(80) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Resolved') NOT NULL DEFAULT 'Pending',
  `reply` text DEFAULT NULL,
  `reply_timestamp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_queries`
--

INSERT INTO `contact_queries` (`id`, `user_id`, `name`, `email`, `subject`, `message`, `submitted_at`, `status`, `reply`, `reply_timestamp`) VALUES
(2, 6, 'Maulik Bhalodiya', 'maulikbhalodiya9999@gmail.com', 'For Office Location Inquiry', 'Where is your office is located in Rajkot?', '2024-10-16 15:58:46', 'Resolved', 'our office in rajkot at Greenland X', '2024-11-14 11:09:45');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('Completed','Failed','Pending') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `user_id`, `vehicle_id`, `amount`, `payment_date`, `payment_method`, `payment_status`) VALUES
(1, 1, 1, 1, 3600.00, '2024-11-18 17:13:39', 'upi', 'Completed'),
(3, 2, 1, 1, 3600.00, '2024-11-22 17:14:59', 'upi', 'Completed'),
(4, 3, 1, 1, 3600.00, '2024-12-04 17:16:00', 'upi', 'Completed'),
(5, 3, 1, 1, 3600.00, '2024-12-28 17:16:06', 'upi', 'Completed'),
(6, 4, 1, 1, 3600.00, '2025-01-08 17:18:26', 'upi', 'Completed'),
(7, 5, 1, 2, 5400.00, '2024-11-18 17:21:05', 'upi', 'Completed'),
(8, 6, 1, 2, 5400.00, '2024-12-11 17:22:17', 'upi', 'Completed'),
(9, 7, 1, 2, 7200.00, '2024-12-24 17:23:15', 'upi', 'Completed'),
(10, 8, 1, 2, 9000.00, '2024-12-31 17:24:10', 'upi', 'Completed'),
(11, 9, 1, 2, 7200.00, '2025-01-11 17:25:45', 'upi', 'Completed'),
(13, 11, 1, 7, 7800.00, '2024-11-23 17:31:24', 'upi', 'Completed'),
(14, 12, 1, 7, 6500.00, '2024-12-05 17:32:22', 'upi', 'Completed'),
(15, 13, 1, 7, 6500.00, '2024-12-19 17:33:34', 'upi', 'Completed'),
(16, 14, 1, 7, 6500.00, '2025-01-01 17:34:39', 'upi', 'Completed'),
(17, 15, 1, 10, 9000.00, '2024-11-19 17:36:29', 'upi', 'Completed'),
(18, 16, 1, 10, 7200.00, '2024-11-22 17:38:11', 'upi', 'Completed'),
(19, 17, 1, 10, 10800.00, '2024-12-01 17:39:24', 'upi', 'Completed'),
(20, 18, 1, 10, 10800.00, '2024-12-18 17:40:38', 'upi', 'Completed'),
(21, 19, 1, 10, 10800.00, '2025-01-01 17:41:54', 'upi', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `rental_requests`
--

CREATE TABLE `rental_requests` (
  `id` int(11) NOT NULL,
  `car_id` int(11) DEFAULT NULL,
  `admin_inspection_status` enum('Pending','Inspected','Approved','Rejected') DEFAULT 'Pending',
  `contact_info` text DEFAULT NULL,
  `decision_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` varchar(100) NOT NULL,
  `profile_pic` varchar(100) DEFAULT NULL,
  `city` varchar(30) NOT NULL,
  `zip_code` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `phone`, `password`, `created_at`, `address`, `profile_pic`, `city`, `zip_code`) VALUES
(1, 'Umretiya Tushar', 'kingdhoni1107@gmail.com', '7043169204', '$2y$10$EpwapQghQQY3Ms3w8GfpUOeWmxD4KYW5Ct2MBSU13NWYRy6QfqIJm', '2024-09-23 08:12:48', 'Kalwad', NULL, 'Jamnagar', '361120'),
(6, 'Maulik Bhalodiya', 'maulikbhalodiya9999@gmail.com', '9726920464', '$2y$10$rYofouFMvTdrpXRju46Qi.rxGuAc0kXj.QegH3NmyFSMHttAeQeBa', '2024-09-16 00:10:44', 'Narnka , Paddhari', 'uploads/profile/image1.jpg', 'Rajkot', '360110'),
(7, 'Durgesh Mali', 'dmchauhan720@gmail.com', '7802998774', '$2y$10$CUjOfh8dXWAFhKuSAVhH2OUMuMUkhazd90HdeFGU3zd8emIMqeONu', '2024-09-16 23:54:36', 'Kherva', NULL, 'Mehsana', '384012');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `availability` tinyint(1) NOT NULL DEFAULT 1,
  `brand_id` int(11) DEFAULT NULL,
  `vehicle_name` varchar(255) NOT NULL,
  `car_type` enum('SUV','Sedan','Sports','Hatchback') DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `rent_per_hour` decimal(10,2) NOT NULL,
  `fuel_type` varchar(50) NOT NULL,
  `seating_capacity` int(2) NOT NULL,
  `engine` varchar(10) NOT NULL,
  `transmission` enum('Manual','Automatic') NOT NULL,
  `power` varchar(10) NOT NULL,
  `torque` varchar(10) NOT NULL,
  `fuel_tank_capacity` varchar(10) NOT NULL,
  `mileage` varchar(20) NOT NULL,
  `photos` text NOT NULL,
  `photos1` varchar(150) NOT NULL,
  `photos2` varchar(150) NOT NULL,
  `photos3` varchar(150) NOT NULL,
  `model_year` int(11) NOT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `availability`, `brand_id`, `vehicle_name`, `car_type`, `price_per_day`, `rent_per_hour`, `fuel_type`, `seating_capacity`, `engine`, `transmission`, `power`, `torque`, `fuel_tank_capacity`, `mileage`, `photos`, `photos1`, `photos2`, `photos3`, `model_year`, `status`) VALUES
(1, 1, 3, 'Scorpio S11', 'SUV', 1800.00, 300.00, 'Petrol', 7, '2.2', 'Manual', '140', '320', '60', '15.0 ', 'Scorpio 1.png', 'Scorpio 2.png', 'Scorpio 3.png', 'Scorpio 4.png', 2020, 1),
(2, 1, 3, 'Thar', 'SUV', 1800.00, 300.00, 'Diesel', 7, '2.2', 'Automatic', '150', '350', '60', '16.0 ', 'thar 1.png', 'thar 2.png', 'thar 3.png', 'thar 4.png', 2020, 1),
(3, 1, 4, 'Safari', 'SUV', 1700.00, 250.00, 'Diesel', 4, '2.0', 'Manual', '130', '320', '57', '16.0 ', 'Safari 1.png', 'Safari 2.png', 'Safari 3.png', 'Safari 4.png', 2022, 1),
(4, 1, 6, 'Virtus GT', 'Sedan', 1500.00, 200.00, 'Petrol', 7, '2.0', 'Manual', '170', '350', '50', '15.0 ', 'Virtus GT 1.png', 'Virtus GT 2.png', 'Virtus GT 3.png', 'Virtus GT 4.png', 2023, 1),
(6, 1, 9, 'Brezza', 'SUV', 1900.00, 350.00, 'Diesel', 7, '2.8', 'Automatic', '200', '500', '80', '14.0 ', 'Brezza 1.png', 'Brezza 2.png', 'Brezza 3.png', 'Brezza 4.png', 2023, 1),
(7, 1, 10, 'Slavia', 'Sedan', 1300.00, 2.00, 'CNG', 5, '1.5', 'Manual', '105', '138', '37', '19.0 ', 'Slavia 1.png', 'Slavia 2.png', 'Slavia 3.png', 'Slavia 4.png', 2021, 1),
(8, 1, 7, 'X1', 'SUV', 2000.00, 400.00, 'Petrol', 7, '2.0', 'Manual', '147', '280', '51', '14.82', 'X1 1.png', 'X1 2.png', 'X1 3.png', 'X1 4.png', 2023, 1),
(9, 1, 3, 'Scorpio N', 'SUV', 1900.00, 350.00, 'Diesle', 7, '2.2', 'Automatic', '150', '370', '60', '16.0', 'Scorpio-n 1.png', 'Scorpio-n 2.png', 'Scorpio-n 3.png', 'Scorpio-n 4.png', 2020, 1),
(10, 1, 3, 'Xuv300', 'SUV', 1800.00, 300.00, 'Petrol', 5, '1.2', 'Manual', '108', '200', '42', '16.0', 'Xuv300 1.png', 'Xuv300 2.png', 'Xuv300 3.png', 'Xuv300 4.png', 2019, 1),
(11, 1, 9, 'Swift', 'Hatchback', 1500.00, 200.00, 'Diesel', 5, '1.2', 'Manual', '85', '111', '36', '20.6', 'Swift 1.png', 'Swift 2.png', 'Swift 3.png', 'Swift 4.png', 2018, 1),
(12, 1, 11, 'Ecosport', 'SUV', 1500.00, 200.00, 'CNG', 5, '1.5', 'Manual', '134', '149', '52', '19.7', 'EcoSport 1.png', 'EcoSport 2.png', 'EcoSport 3.png', 'EcoSport 4.png', 2016, 1);

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_ratings`
--

CREATE TABLE `vehicle_ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `rating_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_ratings`
--

INSERT INTO `vehicle_ratings` (`id`, `user_id`, `vehicle_id`, `rating`, `comment`, `rating_date`) VALUES
(1, 6, 8, 4, '', '2024-11-15 15:23:50'),
(2, 6, 1, 4, '', '2024-11-17 13:56:41'),
(3, 1, 2, 1, '', '2024-11-19 02:52:19'),
(4, 1, 6, 1, '', '2024-11-19 02:52:44'),
(5, 1, 9, 4, '', '2024-11-19 02:54:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_req`
--
ALTER TABLE `booking_req`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `car_owner`
--
ALTER TABLE `car_owner`
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `car_owner_ibfk_1` (`car_id`);

--
-- Indexes for table `car_photos`
--
ALTER TABLE `car_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `car_rent_requests`
--
ALTER TABLE `car_rent_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_queries`
--
ALTER TABLE `contact_queries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `rental_requests`
--
ALTER TABLE `rental_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idx_email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Indexes for table `vehicle_ratings`
--
ALTER TABLE `vehicle_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_req`
--
ALTER TABLE `booking_req`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `car_photos`
--
ALTER TABLE `car_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `car_rent_requests`
--
ALTER TABLE `car_rent_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `contact_queries`
--
ALTER TABLE `contact_queries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `rental_requests`
--
ALTER TABLE `rental_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `vehicle_ratings`
--
ALTER TABLE `vehicle_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_req`
--
ALTER TABLE `booking_req`
  ADD CONSTRAINT `booking_req_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `booking_req_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- Constraints for table `car_owner`
--
ALTER TABLE `car_owner`
  ADD CONSTRAINT `car_owner_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `car_owner_ibfk_2` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `car_photos`
--
ALTER TABLE `car_photos`
  ADD CONSTRAINT `car_photos_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `car_rent_requests` (`id`);

--
-- Constraints for table `contact_queries`
--
ALTER TABLE `contact_queries`
  ADD CONSTRAINT `contact_queries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking_req` (`id`);

--
-- Constraints for table `vehicle_ratings`
--
ALTER TABLE `vehicle_ratings`
  ADD CONSTRAINT `vehicle_ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `vehicle_ratings_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
