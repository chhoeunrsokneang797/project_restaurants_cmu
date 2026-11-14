-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 19, 2026 at 10:07 AM
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
-- Database: `restaurantpos`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `CategoryID` int(11) NOT NULL,
  `CategoryName` varchar(100) NOT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`CategoryID`, `CategoryName`, `Image`, `Status`) VALUES
(2, 'Food', NULL, 'Active'),
(3, 'Drink', NULL, 'Active'),
(4, 'Dessert', NULL, 'Active'),
(5, 'Product', NULL, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `CustomerType` enum('Regular','VIP') DEFAULT 'Regular',
  `Points` int(11) DEFAULT 0,
  `RegisterDate` datetime DEFAULT current_timestamp(),
  `Role` enum('Customer') NOT NULL DEFAULT 'Customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `EmployeeID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Gender` enum('Male','Female','Other') DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Username` varchar(50) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `Photo` varchar(255) DEFAULT NULL,
  `Salary` decimal(10,2) DEFAULT NULL,
  `RoleID` int(11) DEFAULT NULL,
  `HireDate` date DEFAULT NULL,
  `Status` enum('Active','Inactive') DEFAULT 'Active',
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`EmployeeID`, `FullName`, `Gender`, `Phone`, `Email`, `Address`, `Username`, `PasswordHash`, `Photo`, `Salary`, `RoleID`, `HireDate`, `Status`, `CreatedAt`) VALUES
(32, 'Administrator', 'Other', '0123456789', 'admin@gmail.com', 'SR', 'Admin', '$2y$10$OEU4jVyTeVzPsszdH.Ew..U2mRFCIzvYZbjtEPslXn3Lb4L.4kT7a', 'uploads/employees/1780537842_contact-icon-illustration-isolated_23-2151903337.avif', 1000.00, 1, '2026-06-01', 'Active', '2026-06-04 08:50:42'),
(33, 'Manager', 'Other', '0123123123', 'magaer@gmail.com', 'SR', 'Manager', '$2y$10$e5Ol/DKLG4U04WolPiptK.gtja9hg4zrk1aXHUkaNU3mY5Tm16Ehq', 'uploads/employees/1780537904_contact-icon-illustration-isolated_23-2151903337.avif', 700.00, 2, '2026-06-02', 'Active', '2026-06-04 08:51:44'),
(34, 'Cashier', 'Other', '0987654321', 'cashier@gmail.com', 'SR', 'Cashier', '$2y$10$UNNyef9zBK4JB/Hm0ACCq.YQCRJqgahKo1cBvM9R3Ndd0LUnrsjxW', 'uploads/employees/1780537975_contact-icon-illustration-isolated_23-2151903337.avif', 400.00, 3, '2026-06-03', 'Active', '2026-06-04 08:52:55'),
(48, 'Chef', 'Male', '06677889900', 'chef@gmail.com', 'SR', 'Chef', '$2y$10$tE79arWWgwXJ8HHtuy7eiOI/vOh5q/X0CN2DMIiWQGBFmLPT1pU6.', 'uploads/employees/1781495519_contact-icon-illustration-isolated_23-2151903337.avif', 400.00, 4, '2026-06-04', 'Inactive', '2026-06-15 10:51:59'),
(50, 'Waiter', 'Male', '01122334455', 'waiter@gmail.com', 'SR', 'Waiter', '$2y$10$kQJNKwkTpQSy.Xo0N4vo/.Gz5GKpWFo33MCiL7GYhrOdZTtuUTWnS', 'uploads/employees/1781847113_contact-icon-illustration-isolated_23-2151903337.avif', 300.00, 5, '2026-06-15', 'Inactive', '2026-06-15 14:51:12');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `ExpenseID` int(11) NOT NULL,
  `ExpenseName` varchar(100) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `ExpenseDate` date NOT NULL,
  `Note` text DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FeedbackID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `Rating` int(11) DEFAULT NULL CHECK (`Rating` >= 1 and `Rating` <= 5),
  `Comment` text DEFAULT NULL,
  `FeedbackDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `history_orders`
--

CREATE TABLE `history_orders` (
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `customer_name` varchar(50) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `persons` varchar(50) NOT NULL,
  `EmployeeID` int(11) DEFAULT NULL,
  `TableID` int(11) DEFAULT NULL,
  `tableType` varchar(50) NOT NULL,
  `OrderDate` datetime DEFAULT current_timestamp(),
  `OrderType` enum('Dine In','Take Away','Delivery') NOT NULL,
  `OrderStatus` enum('Pending','Accept','Reject') DEFAULT 'Pending',
  `PaymentStatus` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `SubTotal` decimal(10,2) DEFAULT 0.00,
  `DiscountAmount` decimal(10,2) DEFAULT 0.00,
  `TaxAmount` decimal(10,2) DEFAULT 0.00,
  `GrandTotal` decimal(10,2) DEFAULT 0.00,
  `DeliveryAddress` varchar(255) DEFAULT NULL,
  `Note` text DEFAULT NULL,
  `message` varchar(100) NOT NULL,
  `reject_message` varchar(100) NOT NULL,
  `ArchivedDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history_orders`
--

INSERT INTO `history_orders` (`OrderID`, `CustomerID`, `customer_name`, `customer_phone`, `date`, `time`, `persons`, `EmployeeID`, `TableID`, `tableType`, `OrderDate`, `OrderType`, `OrderStatus`, `PaymentStatus`, `SubTotal`, `DiscountAmount`, `TaxAmount`, `GrandTotal`, `DeliveryAddress`, `Note`, `message`, `reject_message`, `ArchivedDate`) VALUES
(22, 0, 'Lay', '0987654321', '2026-06-05', '23:33:00', '2 នាក់', 0, 0, 'Indoor', '2026-06-05 11:33:52', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 11:35:28'),
(23, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 11:35:42', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 11:35:49'),
(24, 0, 'Sokneang', '0123456789', '2026-06-05', '00:51:00', '3 នាក់ឡើង', 0, 0, 'Indoor', '2026-06-05 12:51:38', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', 'AAAAAAAAAA', '', '2026-06-05 12:51:54'),
(25, 0, 'Leapmey', '01122334455', '2026-06-06', '00:58:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 12:58:57', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 13:19:54'),
(26, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 13:20:33', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 13:24:53'),
(27, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 13:30:20', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 13:43:12'),
(28, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 13:43:21', 'Dine In', '', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 13:49:15'),
(29, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 13:50:12', 'Dine In', '', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 13:50:21'),
(30, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 13:51:39', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:02:05'),
(31, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:02:54', 'Dine In', '', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:06:03'),
(32, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:02:57', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:05:09'),
(33, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:02:59', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:03:37'),
(34, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:06:24', 'Dine In', '', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:06:31'),
(35, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:10:42', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:10:59'),
(36, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:10:44', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:11:09'),
(37, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:32:29', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:32:39'),
(38, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:32:31', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:32:42'),
(39, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:42:24', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:42:33'),
(40, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:42:27', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:42:43'),
(41, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:46:32', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:46:45'),
(42, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:46:34', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:46:41'),
(43, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:49:02', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:49:09'),
(44, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:52:27', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:52:33'),
(45, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 14:59:04', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 14:59:15'),
(46, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 15:10:50', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 15:10:57'),
(47, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 15:11:38', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 15:11:44'),
(48, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 15:13:42', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 15:13:51'),
(49, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 15:27:39', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 15:27:45'),
(50, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 15:27:51', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 15:27:59'),
(51, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 15:28:49', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 15:28:56'),
(52, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 15:51:56', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 15:52:03'),
(53, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 15:53:44', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 15:53:54'),
(54, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 15:57:34', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 15:57:40'),
(55, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:00:52', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:00:58'),
(56, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:07:31', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:07:47'),
(57, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:20:36', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:20:55'),
(58, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:20:39', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:20:59'),
(59, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:21:24', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:21:30'),
(60, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:22:04', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:22:10'),
(61, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:22:29', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:22:35'),
(62, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:23:27', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:23:44'),
(63, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:23:29', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:27:24'),
(64, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:23:32', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:27:54'),
(65, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:23:35', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:28:04'),
(66, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:23:38', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:28:53'),
(67, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:38:20', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:38:37'),
(68, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-05 16:38:24', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-05 16:38:40'),
(69, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-06 11:39:36', 'Dine In', 'Accept', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-06 14:04:07'),
(70, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-06 11:40:20', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-06 14:04:10'),
(72, 0, '', '', '0000-00-00', '00:00:00', '1 នាក់', 0, 0, 'Indoor', '2026-06-09 18:49:16', 'Dine In', 'Reject', 'Unpaid', 0.00, 0.00, 0.00, 0.00, '', '', '', '', '2026-06-09 18:49:59'),
(73, 26, 'Lay', '0964140472', '2026-06-15', '13:17:00', '', 0, 0, 'Table 1', '2026-06-15 13:17:33', 'Dine In', 'Reject', '', 8.00, 0.00, 0.80, 8.80, 'SR', '', '', '', '2026-06-15 13:37:57'),
(74, 26, 'Lay', '0964140472', '2026-06-15', '13:17:00', '', 0, 0, 'Table 1', '2026-06-15 13:17:34', 'Dine In', 'Reject', '', 8.00, 0.00, 0.80, 8.80, 'SR', '', '', '', '2026-06-15 13:37:52'),
(75, 26, 'Lay', '0964140472', '2026-06-15', '13:17:00', '', 0, 0, 'Table 1', '2026-06-15 13:17:38', 'Dine In', 'Reject', '', 8.00, 0.00, 0.80, 8.80, 'SR', '', '', '', '2026-06-15 13:37:47'),
(76, 26, 'Lay', '0964140472', '2026-06-15', '13:54:00', '', 0, 0, 'Table 1', '2026-06-15 13:57:03', 'Dine In', 'Accept', 'Unpaid', 3.00, 0.00, 0.30, 3.30, '', '', '', '', '2026-06-15 13:57:15'),
(77, 26, 'Lay', '0964140472', '2026-06-15', '13:54:00', '', 0, 0, 'Table 1', '2026-06-15 13:57:05', 'Dine In', 'Reject', 'Unpaid', 3.00, 0.00, 0.30, 3.30, '', '', '', '', '2026-06-15 13:57:19'),
(78, 26, 'Lay', '0964140472', '2026-06-15', '13:54:00', '', 0, 0, 'Table 1', '2026-06-15 13:57:05', 'Dine In', 'Reject', 'Unpaid', 3.00, 0.00, 0.30, 3.30, '', '', '', '', '2026-06-15 13:57:26'),
(79, 26, 'Lay', '0964140472', '2026-06-15', '13:54:00', '', 0, 0, 'Table 1', '2026-06-15 13:57:05', 'Dine In', 'Reject', 'Unpaid', 3.00, 0.00, 0.30, 3.30, '', '', '', '', '2026-06-15 13:58:33'),
(80, 26, 'Lay', '0964140472', '2026-06-15', '13:54:00', '', 0, 0, 'Table 1', '2026-06-15 13:57:05', 'Dine In', 'Reject', 'Unpaid', 3.00, 0.00, 0.30, 3.30, '', '', '', '', '2026-06-15 13:58:37'),
(81, 26, 'Lay', '0964140472', '2026-06-15', '13:54:00', '', 0, 0, 'Table 1', '2026-06-15 13:57:06', 'Dine In', 'Reject', 'Unpaid', 3.00, 0.00, 0.30, 3.30, '', '', '', '', '2026-06-15 13:59:11'),
(83, 27, 'Customer', '01122334455', '2026-06-19', '14:33:00', '', 0, 0, 'Table 5', '2026-06-16 14:33:53', 'Dine In', 'Reject', 'Unpaid', 9.00, 0.00, 0.90, 9.90, '', '', '', '', '2026-06-16 14:38:10'),
(84, 27, 'Customer1', '01122334455', '2026-06-19', '14:41:00', '', 0, 0, 'Table 1', '2026-06-16 14:42:13', 'Dine In', 'Reject', 'Unpaid', 15.00, 0.00, 1.50, 16.50, '', '', '', '', '2026-06-16 14:49:24'),
(85, 27, 'Customer2', '02233445566', '2026-06-19', '14:41:00', '', 0, 0, 'Table 1', '2026-06-16 14:43:00', 'Dine In', 'Reject', 'Unpaid', 6.00, 0.00, 0.60, 6.60, '', '', '', '', '2026-06-16 14:49:21'),
(86, 27, 'Customer3', '03344556677', '2026-06-20', '02:44:00', '', 0, 0, 'Table 2', '2026-06-16 14:44:59', 'Dine In', 'Reject', 'Unpaid', 6.00, 0.00, 0.60, 6.60, '', '', '', '', '2026-06-16 14:49:17'),
(87, 27, 'Customer1', '01122334455', '2026-06-17', '02:49:00', '', 0, 0, 'Table 4', '2026-06-16 14:49:58', 'Dine In', 'Accept', 'Unpaid', 5.00, 0.00, 0.50, 5.50, '', '', '', '', '2026-06-16 14:51:35'),
(88, 27, 'Customer1', '01122334455', '2026-06-19', '02:52:00', '', 0, 0, 'Table 1', '2026-06-16 14:52:43', 'Dine In', 'Reject', 'Unpaid', 8.00, 0.00, 0.80, 8.80, '', '', '', '', '2026-06-16 15:00:54'),
(89, 27, 'Customer1', '02233445566', '2026-06-17', '04:01:00', '', 0, 0, 'Table 2', '2026-06-16 16:01:46', 'Dine In', 'Accept', 'Unpaid', 6.00, 0.00, 0.60, 6.60, '', '', '', '', '2026-06-16 16:02:54'),
(90, 27, 'Customer1', '02233445566', '2026-06-17', '04:07:00', '', 0, 0, 'Table 1', '2026-06-16 16:07:59', 'Dine In', 'Accept', 'Unpaid', 6.00, 0.00, 0.60, 6.60, 'PP', '', '', '', '2026-06-16 16:08:09'),
(91, 27, 'Customer2', '03344556677', '2026-06-18', '16:12:00', '', 0, 0, 'Table 1', '2026-06-16 16:12:14', 'Dine In', 'Accept', 'Unpaid', 12.00, 0.00, 1.20, 13.20, '', '', '', '', '2026-06-16 16:17:47'),
(92, 13, 'Customer1', '02233445566', '2026-06-18', '18:37:00', '', 0, 0, 'Table 1', '2026-06-17 18:37:19', 'Dine In', 'Reject', 'Unpaid', 3.00, 0.00, 0.30, 3.30, '', '', '', '', '2026-06-17 18:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `InventoryID` int(11) NOT NULL,
  `IngredientName` varchar(100) NOT NULL,
  `Quantity` decimal(10,2) DEFAULT NULL,
  `Unit` varchar(20) DEFAULT NULL,
  `MinimumStock` decimal(10,2) DEFAULT NULL,
  `LastUpdated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`InventoryID`, `IngredientName`, `Quantity`, `Unit`, `MinimumStock`, `LastUpdated`) VALUES
(70, 'អាមុកខ្មែរ', 3.00, NULL, 10.00, '2026-06-09 09:32:00'),
(71, 'កុងយ៉ាំសាច់មាន់', 3.00, NULL, 10.00, '2026-06-09 09:38:54'),
(72, 'ប្រហុក', 3.00, NULL, 10.00, '2026-06-09 09:39:05'),
(73, 'គុយទាវ', 3.00, NULL, 10.00, '2026-06-09 09:39:20'),
(74, 'ប្រហុកខ្ទិះ', 2.00, NULL, 10.00, '2026-06-09 09:39:53'),
(79, 'នំលត', 1.00, NULL, 10.00, '2026-06-09 10:20:07'),
(80, 'គ្រាប់ទទឹម', 1.00, NULL, 10.00, '2026-06-09 10:20:26'),
(82, 'ទឹកសុទ្ធកម្ពុជា', 1.00, NULL, 10.00, '2026-06-09 11:19:29'),
(84, 'Vigor', 2.00, NULL, 10.00, '2026-06-09 11:21:22'),
(85, 'ទឹកអំពៅ', 1.00, NULL, 10.00, '2026-06-09 11:21:48'),
(86, 'ចាហ៊ួយដូង', 1.00, NULL, 10.00, '2026-06-09 11:23:33'),
(87, 'ទឹកគ្រឿង', 4.00, NULL, 10.00, '2026-06-10 11:30:47'),
(88, 'ម្ជូរគ្រឿង', 3.00, NULL, 10.00, '2026-06-10 11:31:26'),
(89, 'ចេកខ្ទិះ', 1.00, NULL, 10.00, '2026-06-10 11:32:15'),
(90, 'បាយដំណើបសង្ខ្យា', 1.00, NULL, 10.00, '2026-06-10 11:32:48'),
(91, 'បបរត្នោត', 1.00, NULL, 10.00, '2026-06-10 11:33:21'),
(92, 'Red Sting', 1.00, NULL, 10.00, '2026-06-10 11:34:39'),
(93, 'Blue Sting', 1.00, NULL, 10.00, '2026-06-10 11:35:05'),
(94, 'Yellow Sting', 1.00, NULL, 10.00, '2026-06-10 11:35:30'),
(95, 'ទឹកសុទ្ធប្រូវីដា', 1.00, NULL, 10.00, '2026-06-10 11:35:55'),
(96, 'Champion', 1.00, NULL, 10.00, '2026-06-10 11:36:18'),
(97, 'បបរគ្រឿង', 3.00, NULL, 10.00, '2026-06-10 11:37:45'),
(98, 'បាយឆាសាច់គោ', 3.00, NULL, 10.00, '2026-06-10 11:38:23'),
(99, 'នំអន្សមចេក', 1.00, NULL, 10.00, '2026-06-10 11:39:17'),
(100, 'សង្ខ្យាល្ពៅ', 1.00, NULL, 10.00, '2026-06-10 11:39:43');

-- --------------------------------------------------------

--
-- Table structure for table `menuitems`
--

CREATE TABLE `menuitems` (
  `MenuItemID` int(11) NOT NULL,
  `ItemName` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Size` enum('Small','Large') NOT NULL DEFAULT 'Small',
  `Discount` decimal(10,2) DEFAULT 0.00,
  `Image` varchar(255) DEFAULT NULL,
  `StockQty` int(11) DEFAULT 0,
  `PrepTime` int(11) DEFAULT NULL COMMENT 'Minutes',
  `CategoryID` int(11) NOT NULL,
  `Status` enum('Available','Unavailable') DEFAULT 'Available',
  `updated_at` datetime DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menuitems`
--

INSERT INTO `menuitems` (`MenuItemID`, `ItemName`, `Description`, `Price`, `Size`, `Discount`, `Image`, `StockQty`, `PrepTime`, `CategoryID`, `Status`, `updated_at`, `CreatedAt`) VALUES
(144, 'អាមុកខ្មែរ', NULL, 3.00, 'Small', 0.00, '2026_06_19_06_48_49_92_Amok.webp', 50, NULL, 2, 'Available', NULL, '2026-06-19 11:48:49'),
(145, 'នំអាកោត្នោត', NULL, 1.00, 'Small', 0.00, '2026_06_19_06_55_24_87_Ah-Kor-Thnout.jpg', 50, NULL, 4, 'Available', NULL, '2026-06-19 11:55:24'),
(146, 'បបរគ្រឿង', NULL, 3.00, 'Small', 0.00, '2026_06_19_06_56_32_99_BawBaw.webp', 50, NULL, 2, 'Available', NULL, '2026-06-19 11:56:32'),
(147, 'បាយឆាសាច់គោ', NULL, 3.00, 'Small', 0.00, '2026_06_19_06_56_47_70_Bay-Cha-sach-ko.jpg', 50, NULL, 2, 'Available', NULL, '2026-06-19 11:56:47'),
(148, 'បាយសាច់ជ្រូក', NULL, 3.00, 'Small', 0.00, '2026_06_19_06_57_04_35_Bay-Sach-Chrouk.webp', 50, NULL, 2, 'Available', NULL, '2026-06-19 11:57:04'),
(149, 'ឆា​ប្រៃ​ផ្អែម​គ្រឿង​ក្នុង​មាន់', NULL, 3.00, 'Small', 0.00, '2026_06_19_06_59_42_80_Cha-Prai-Paem-Kreung-Knong-Morn.jpg', 50, NULL, 2, 'Available', NULL, '2026-06-19 11:59:42'),
(150, 'នំបញ្ចុកខ្មែរ (សម្លខ្មែរ /​ សម្លការី)', NULL, 3.00, 'Small', 0.00, '2026_06_19_06_59_58_55_Khmer-Noodles.jpg', 50, NULL, 2, 'Available', NULL, '2026-06-19 11:59:58'),
(151, 'គុយទាវ', NULL, 3.00, 'Small', 0.00, '2026_06_19_07_00_18_36_Kuy-Teav.jpg', 50, NULL, 2, 'Available', NULL, '2026-06-19 12:00:18'),
(152, 'ឡុកឡាក់សាច់គោ', NULL, 3.00, 'Small', 0.00, '2026_06_19_07_00_48_53_LokLak.webp', 50, NULL, 2, 'Available', NULL, '2026-06-19 12:00:48'),
(153, 'ម្ជូរគ្រឿង', NULL, 3.00, 'Small', 0.00, '2026_06_19_07_01_00_38_Mju-Kroeung.jpg', 50, NULL, 2, 'Available', NULL, '2026-06-19 12:01:00'),
(154, 'មាន់​ស្ពៃ​ខៀវ​', NULL, 3.00, 'Small', 0.00, '2026_06_19_07_01_26_67_Morn-Spey-Kheav.jpg', 50, NULL, 2, 'Available', NULL, '2026-06-19 12:01:26'),
(155, 'ប្រហុកខ្ទិះ', NULL, 3.00, 'Small', 0.00, '2026_06_19_07_01_45_32_PrahokKtiss.webp', 50, NULL, 2, 'Available', NULL, '2026-06-19 12:01:45'),
(156, 'ត្រីបំពងជូអែម', NULL, 3.00, 'Small', 0.00, '2026_06_19_07_01_58_51_Trey-Bompong-Ju-Aem.jpg', 50, NULL, 2, 'Available', NULL, '2026-06-19 12:01:58'),
(157, 'សាច់​គោ​អាំង​ទឹក​ប្រ​ហុក​', NULL, 3.00, 'Small', 0.00, '2026_06_19_07_02_11_46_Sach-Ko-Ang-Terk-Pahok.jpg', 50, NULL, 2, 'Available', NULL, '2026-06-19 12:02:11'),
(158, 'ទឹកគ្រឿង', NULL, 3.00, 'Small', 0.00, '2026_06_19_07_02_26_63_Tik-Kroeung.jpg', 50, NULL, 2, 'Available', NULL, '2026-06-19 12:02:26'),
(159, 'ទឹកសុទ្ធកម្ពុជា', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_02_45_94_Cambodia-Water.png', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:02:45'),
(160, 'ទឹកសុទ្ធប្រូវីដា', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_03_00_76_provida-water.png', 50, NULL, 3, 'Available', '2026-06-19 07:03:58', '2026-06-19 12:03:00'),
(161, 'ទឹកសុទ្ធដាសានី', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_03_13_49_Dasani-water.png', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:03:13'),
(162, 'ទឹកសុទ្ធវីតាល់', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_03_28_18_vital-water.png', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:03:28'),
(163, 'Champion', NULL, 2.00, 'Small', 0.00, '2026_06_19_07_03_51_66_champion-drink.jpg', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:03:51'),
(164, 'Coca Cola', NULL, 2.00, 'Small', 0.00, '2026_06_19_07_04_15_46_coca-cola.webp', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:04:15'),
(165, 'Blue Sting', NULL, 2.00, 'Small', 0.00, '2026_06_19_07_04_29_28_sting-blueberry.webp', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:04:29'),
(166, 'Yellow Sting', NULL, 2.00, 'Small', 0.00, '2026_06_19_07_04_45_92_yellow-sting.jpg', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:04:45'),
(167, 'Red Sting', NULL, 2.00, 'Small', 0.00, '2026_06_19_07_04_56_66_sting-red.png', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:04:56'),
(168, 'Vigor', NULL, 2.00, 'Small', 0.00, '2026_06_19_07_05_09_93_vigor-cambodia.jpg', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:05:09'),
(169, 'Green Fanta', NULL, 2.00, 'Small', 0.00, '2026_06_19_07_05_27_50_fanta-green.webp', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:05:27'),
(170, 'Orange Fanta', NULL, 2.00, 'Small', 0.00, '2026_06_19_07_05_38_25_fanta-orange.webp', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:05:38'),
(171, 'Purple Fanta', NULL, 2.00, 'Small', 0.00, '2026_06_19_07_05_52_95_fanta-purple.webp', 50, NULL, 3, 'Available', NULL, '2026-06-19 12:05:52'),
(172, 'បបរពោត', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_19_32_36_BorBor-Port.jpg', 50, NULL, 4, 'Available', NULL, '2026-06-19 12:19:32'),
(173, 'បបរត្នោត', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_19_46_85_BorBor-Thnort.jpg', 50, NULL, 4, 'Available', NULL, '2026-06-19 12:19:46'),
(174, 'ចេកខ្ទិះ', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_19_59_79_Khmer-Banana-Dessert.jpg', 50, NULL, 4, 'Available', NULL, '2026-06-19 12:19:59'),
(175, 'ឡត', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_20_18_10_NomLort.jpg', 50, NULL, 4, 'Available', NULL, '2026-06-19 12:20:18'),
(176, 'បាយដំណើបសង់ខ្យា', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_20_36_94_Bay-Domnerb-Song-Chja.jpg', 50, NULL, 4, 'Available', NULL, '2026-06-19 12:20:36'),
(177, 'សង់ខ្យាល្ពៅ', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_20_49_74_Sankhya_Lapov.jpg', 50, NULL, 4, 'Available', NULL, '2026-06-19 12:20:49'),
(178, 'នំអន្សមចេក', NULL, 1.00, 'Small', 0.00, '2026_06_19_07_21_13_83_Num_Ansom_Chek.jpg', 50, NULL, 4, 'Available', NULL, '2026-06-19 12:21:13');

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `OrderDetailID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `MenuItemID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `UnitPrice` decimal(10,2) NOT NULL,
  `Discount` decimal(10,2) DEFAULT 0.00,
  `SubTotal` decimal(10,2) NOT NULL,
  `ItemNote` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `customer_name` varchar(50) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `persons` varchar(50) NOT NULL DEFAULT '',
  `EmployeeID` int(11) DEFAULT NULL,
  `TableID` int(11) DEFAULT NULL,
  `tableType` varchar(50) NOT NULL,
  `OrderDate` datetime DEFAULT current_timestamp(),
  `OrderType` enum('Dine In','Take Away','Takeout','Delivery') NOT NULL,
  `PaymentMethod` enum('Cash','KHQR') DEFAULT 'Cash',
  `OrderStatus` enum('Pending','Accept','Reject') DEFAULT 'Pending',
  `PaymentStatus` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `SubTotal` decimal(10,2) DEFAULT 0.00,
  `DiscountAmount` decimal(10,2) DEFAULT 0.00,
  `TaxAmount` decimal(10,2) DEFAULT 0.00,
  `GrandTotal` decimal(10,2) DEFAULT 0.00,
  `DeliveryAddress` varchar(255) DEFAULT NULL,
  `Note` text DEFAULT NULL,
  `message` varchar(100) NOT NULL DEFAULT '',
  `reject_message` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `PaymentID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `PaymentDate` datetime DEFAULT current_timestamp(),
  `PaymentMethod` enum('Cash','ABA','Card') NOT NULL,
  `AmountPaid` decimal(10,2) NOT NULL,
  `ChangeAmount` decimal(10,2) DEFAULT 0.00,
  `TransactionCode` varchar(100) DEFAULT NULL,
  `PaymentStatus` enum('Paid','Pending','Refund') DEFAULT 'Paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restauranttables`
--

CREATE TABLE `restauranttables` (
  `TableID` int(11) NOT NULL,
  `TableName` varchar(50) NOT NULL,
  `Capacity` int(11) NOT NULL,
  `Location` varchar(50) DEFAULT NULL,
  `Status` enum('Available','Reserved','Occupied','Cleaning') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restauranttables`
--

INSERT INTO `restauranttables` (`TableID`, `TableName`, `Capacity`, `Location`, `Status`) VALUES
(39, 'A01', 1, 'ក្នុង', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RoleID` int(11) NOT NULL,
  `RoleName` varchar(50) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`RoleID`, `RoleName`, `Description`) VALUES
(1, 'Admin', 'System Administrator'),
(2, 'Manager', 'Restaurant Manager'),
(3, 'Cashier', 'Cashier Staff'),
(4, 'Chef', 'Kitchen Staff'),
(5, 'Waiter', 'Service Staff');

-- --------------------------------------------------------

--
-- Table structure for table `tablebookings`
--

CREATE TABLE `tablebookings` (
  `BookingID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `TableID` int(11) NOT NULL,
  `BookingDate` date NOT NULL,
  `BookingTime` time NOT NULL,
  `NumberOfGuests` int(11) DEFAULT NULL,
  `WalkInLabel` varchar(100) DEFAULT NULL,
  `ContactPhone` varchar(20) DEFAULT NULL,
  `BookingStatus` enum('Pending','Reserved','Confirmed','CheckedIn','CheckedOut','Cancelled','Completed') DEFAULT 'Pending',
  `Note` text DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`CategoryID`),
  ADD UNIQUE KEY `CategoryName` (`CategoryName`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `Phone` (`Phone`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`EmployeeID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Phone` (`Phone`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `RoleID` (`RoleID`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`ExpenseID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `history_orders`
--
ALTER TABLE `history_orders`
  ADD PRIMARY KEY (`OrderID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`InventoryID`);

--
-- Indexes for table `menuitems`
--
ALTER TABLE `menuitems`
  ADD PRIMARY KEY (`MenuItemID`),
  ADD KEY `idx_menu_category` (`CategoryID`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`OrderDetailID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `MenuItemID` (`MenuItemID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `EmployeeID` (`EmployeeID`),
  ADD KEY `TableID` (`TableID`),
  ADD KEY `idx_orders_date` (`OrderDate`),
  ADD KEY `idx_orders_status` (`OrderStatus`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `idx_payment_date` (`PaymentDate`);

--
-- Indexes for table `restauranttables`
--
ALTER TABLE `restauranttables`
  ADD PRIMARY KEY (`TableID`),
  ADD UNIQUE KEY `TableName` (`TableName`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`RoleID`),
  ADD UNIQUE KEY `RoleName` (`RoleName`);

--
-- Indexes for table `tablebookings`
--
ALTER TABLE `tablebookings`
  ADD PRIMARY KEY (`BookingID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `TableID` (`TableID`),
  ADD KEY `idx_booking_date` (`BookingDate`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `CategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `EmployeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `ExpenseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `InventoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `menuitems`
--
ALTER TABLE `menuitems`
  MODIFY `MenuItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT for table `orderdetails`
--
ALTER TABLE `orderdetails`
  MODIFY `OrderDetailID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restauranttables`
--
ALTER TABLE `restauranttables`
  MODIFY `TableID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tablebookings`
--
ALTER TABLE `tablebookings`
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`);

--
-- Constraints for table `menuitems`
--
ALTER TABLE `menuitems`
  ADD CONSTRAINT `menuitems_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `categories` (`CategoryID`);

--
-- Constraints for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`),
  ADD CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`MenuItemID`) REFERENCES `menuitems` (`MenuItemID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`EmployeeID`) REFERENCES `employees` (`EmployeeID`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`TableID`) REFERENCES `restauranttables` (`TableID`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`);

--
-- Constraints for table `tablebookings`
--
ALTER TABLE `tablebookings`
  ADD CONSTRAINT `tablebookings_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`),
  ADD CONSTRAINT `tablebookings_ibfk_2` FOREIGN KEY (`TableID`) REFERENCES `restauranttables` (`TableID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
