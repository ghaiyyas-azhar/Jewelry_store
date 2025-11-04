-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2025 at 12:58 PM
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
-- Database: `jewelry_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `name`, `email`, `password`) VALUES
(1, 'admin1', 'admin@contoh.com', 'admin1111');

-- --------------------------------------------------------

--
-- Table structure for table `booking_order`
--

CREATE TABLE `booking_order` (
  `booking_id` int(11) NOT NULL,
  `booking_date` date DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `order_details` text DEFAULT NULL,
  `session_date` date DEFAULT NULL,
  `session_time` time DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_order`
--

INSERT INTO `booking_order` (`booking_id`, `booking_date`, `name`, `customer_id`, `admin_id`, `collection_id`, `order_details`, `session_date`, `session_time`, `status`, `notes`) VALUES
(1, NULL, ' ', 1, 1, NULL, 'ocqb982eynqeomdcfp4e5mlk,ucf0dp4eri5', '2025-10-09', '17:53:00', 'approved', NULL),
(2, NULL, ' ', 1, 1, NULL, '9qc37yn8e30,ru2hydewskhu8ewfdceuwh8dpewhupd8pweuhf', '2025-10-08', '10:07:00', 'approved', NULL),
(3, NULL, ' ', 1, 1, NULL, ';aow vidawip;o mwacx,;idpjw;oaci,djcawd,i;jocadw;io,jm', '2025-10-01', '12:24:00', 'approved', NULL),
(4, NULL, ' ', 1, 1, NULL, 'ap0n9wucn80pum3qvn80puvmr3qvn3p08rmuw', '2025-10-23', '13:45:00', 'approved', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE `collection` (
  `collection_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection`
--

INSERT INTO `collection` (`collection_id`, `product_name`, `description`, `price`, `stock`, `image`) VALUES
(1, 'pearl rings', 'a set of pearl rings that is beautiful, gleam with beautiful white and pink pearl', 120000.00, 20, 'product_68f33cd9e5bb34.94264473.jpeg'),
(2, 'Black butterfly necklace', 'a beautiful necklace adorn with butterflies, black and white pearl', 250000.00, 20, 'product_68f3509ecb7c78.52098013.jpeg'),
(3, 'Pink quartz necklace', 'a necklace that can have a pink quartz, the beauty of this ', 300000.00, 20, 'product_68f355a81287f7.64634414.jpeg'),
(4, 'Green viper necklace', 'A necklace that is coated with the color of a green phyton, perfect to bring out the toxicity for your body', 100000.00, 0, 'product_68f35d93e007d4.36755657.jpeg'),
(5, 'Golden Heart of vow necklace', 'A necklace of solitude, a golden vow made under the altar for your loved ones, perfect for married couples', 500000.00, 0, 'product_68f35dfd37ffc2.58695371.png'),
(6, 'Cherry blosson Bracelet', 'a necklace made with pink pearls and coated with the tree sap of cherry blossoms.', 485000.00, 0, 'product_68f35e592f11c5.36518287.png'),
(7, 'Full package bracelets', 'buy all of our bracelets here', 1000000.00, 0, 'product_68f35e9fca79e4.39725499.png'),
(8, 'Angel Demon Necklace', 'a necklace that represents the good and evil of the world, where the white pearl and black pearl connects', 800000.00, 0, 'product_68f360a8288f33.59554776.png'),
(9, 'Sakura scented bracelet', 'A bracelet that is submerged in sakura tree bark for a year and came out with a tint of sakura tree smell', 2000000.00, 0, 'product_68f360e8a0fa91.73337048.png');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `name`, `email`, `password`, `phone`, `address`) VALUES
(1, 'ATHAYAFI AHMAD MUNANDHAR', 'athayafiam@gmail.com', 'atha1234', '081513308206', NULL),
(2, 'sahroni', 'sahroni1@contoh.com', 'sahroni1111', '0876688889177', 'FTUI, INDONESIA');

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`order_id`, `customer_id`, `admin_id`, `collection_id`, `quantity`, `total_price`, `order_date`) VALUES
(1, 1, NULL, 1, 1, 120000.00, '2025-10-18 16:08:15'),
(2, 1, NULL, 2, 1, 250000.00, '2025-10-18 16:08:15'),
(3, 1, NULL, 3, 1, 300000.00, '2025-10-18 16:08:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `booking_order`
--
ALTER TABLE `booking_order`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_booking_customer` (`customer_id`),
  ADD KEY `fk_booking_admin` (`admin_id`),
  ADD KEY `fk_booking_collection` (`collection_id`);

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`collection_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_order_customer` (`customer_id`),
  ADD KEY `fk_order_admin` (`admin_id`),
  ADD KEY `fk_order_collection` (`collection_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_order`
--
ALTER TABLE `booking_order`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `collection`
--
ALTER TABLE `collection`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_order`
--
ALTER TABLE `booking_order`
  ADD CONSTRAINT `fk_booking_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_collection` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `fk_order_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_collection` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
