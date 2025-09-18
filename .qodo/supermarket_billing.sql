-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2025 at 04:17 PM
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
-- Database: `supermarket_billing`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gstin` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `address`, `phone`, `gstin`, `created_at`) VALUES
(1, 'karthivk', 'NO1, Admin, Quick Booking, Admin', '9876543211', '685315', '2025-06-22 19:00:39'),
(2, 'karthivk', 'NO1, Admin, Quick Booking, Admin', '9876543211', '685315', '2025-06-22 19:00:50'),
(3, 'karthivk', 'No.6., vivekanander street, Manapparai, Trichy', '9876543211', '685315', '2025-06-22 19:01:19'),
(4, 'karthivk', 'n0.2 xxx street.,yy state. xendia', '9876543211', '685315', '2025-06-22 19:03:09'),
(5, 'karthivk', 'n0.2 xxx street.,yy state. xendia', '9876543211', '685315', '2025-06-22 19:04:07'),
(6, 'Partha', 'lsukhgseiurhgwlernlwetg', '8434379860687', '8557651', '2025-06-22 19:05:22'),
(7, 'Partha', 'lsukhgseiurhgwlernlwetg', '8434379860687', '8557651', '2025-06-22 19:05:38'),
(8, 'Parthar', 'NO1, Admin, Quick Booking, Admin', '8434379860687', '8557651', '2025-06-22 19:13:10'),
(9, 'Parthar', 'NO1, Admin, adv Booking, Admin', '8434379860687', '8557651', '2025-06-22 19:13:59'),
(10, 'Parthar', 'NO1, Admin, adv Booking, Admin', '8434379860687', '8557651', '2025-06-22 19:14:07');

-- --------------------------------------------------------

--
-- Table structure for table `daily_expenses`
--

CREATE TABLE `daily_expenses` (
  `id` int(11) NOT NULL,
  `expense_date` date NOT NULL,
  `purchase_order` varchar(255) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `printing_services` decimal(10,2) DEFAULT NULL,
  `petrol_expense` decimal(10,2) DEFAULT NULL,
  `other_expense` text DEFAULT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_expenses`
--

INSERT INTO `daily_expenses` (`id`, `expense_date`, `purchase_order`, `salary`, `printing_services`, `petrol_expense`, `other_expense`, `pdf_path`, `created_at`) VALUES
(1, '2025-09-01', '5581', 54000.00, 4000.00, 3210.00, '5120', 'expenses/Expense_2025-09-01.pdf', '2025-09-01 18:12:01'),
(2, '2025-09-02', '558117', 74416.00, 6541.00, 641.00, '4445', 'expenses/Expense_2025-09-02.pdf', '2025-09-01 18:29:19'),
(3, '2025-09-03', '55811', 32131.00, 354.00, 684.00, '885', 'expenses/Expense_2025-09-03.pdf', '2025-09-01 18:29:57'),
(4, '2025-09-04', '55811', 32131.00, 354.00, 684.00, '885', 'expenses/Expense_2025-09-04.pdf', '2025-09-01 18:30:09'),
(5, '2025-09-05', '55811', 32131.00, 354.00, 684.00, '885', 'expenses/Expense_2025-09-05.pdf', '2025-09-01 18:30:20'),
(6, '2025-09-14', '', 0.00, 0.00, 0.00, '', 'expenses/Expense_2025-09-14.pdf', '2025-09-14 04:24:23'),
(7, '2025-09-15', '100', 3000.00, 100.00, 500.00, '5000', 'expenses/Expense_2025-09-15.pdf', '2025-09-15 16:49:55');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `invoice_date` date NOT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `customer_name`, `invoice_date`, `pdf_path`, `created_at`) VALUES
(34, '7561646315', 'xxx', '2025-09-01', 'invoices/Invoice_7561646315.pdf', '2025-09-01 11:06:09'),
(35, '9910', 'Kiran Raj', '2025-09-01', 'invoices/Invoice_9910.pdf', '2025-09-01 17:37:28'),
(37, '9911', 'Kiran Raj1', '2025-09-02', 'invoices/Invoice_9911.pdf', '2025-09-02 18:28:29'),
(38, '99111', 'Kiran Raj8', '2025-09-02', 'invoices/Invoice_99111.pdf', '2025-09-02 18:36:30'),
(39, '99117', 'Rah,', '2025-09-02', 'invoices/Invoice_99117.pdf', '2025-09-02 18:43:45'),
(40, '123456789', 'Kiran Raaj', '2025-09-16', 'invoices/Invoice_123456789.pdf', '2025-09-16 16:04:33');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `hsn_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `name`, `price`, `hsn_code`, `created_at`) VALUES
(1, 'Sample Material', 100.00, 'A7', '2025-06-19 21:08:38'),
(2, 'Sample Material', 150.00, 'B6', '2025-06-19 21:08:38'),
(3, 'Sample Material 3', 200.00, 'C3', '2025-06-19 21:08:38'),
(6, 'Material- 88', 4000.00, 'P8', '2025-06-20 10:34:45'),
(7, 'Material -NN', 580.00, 'Z3', '2025-06-20 18:51:13'),
(8, 'Sample material -YY', 8400.00, 'L9', '2025-06-24 09:31:33'),
(9, 'Kiran\'s Material -1', 5100.00, 'K7', '2025-06-24 14:17:00'),
(11, 'PipeBomber-66', 9110.00, 'J8', '2025-06-24 16:25:59'),
(20, 'Material -NN', 5555.00, 'K9', '2025-07-04 09:51:47'),
(21, 'Material- 88 55', 4100.00, 'K7', '2025-07-04 10:21:49'),
(22, 'Material -NN', 544.00, 'K7', '2025-07-04 11:05:45');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(255) NOT NULL,
  `po_date` date NOT NULL,
  `seller_name` varchar(255) NOT NULL,
  `seller_company` varchar(255) NOT NULL,
  `seller_address` text NOT NULL,
  `seller_phone` varchar(20) NOT NULL,
  `seller_gst` varchar(50) DEFAULT NULL,
  `seller_email` varchar(255) DEFAULT NULL,
  `materials_data` text NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cgst_rate` decimal(5,2) DEFAULT 0.00,
  `sgst_rate` decimal(5,2) DEFAULT 0.00,
  `igst_rate` decimal(5,2) DEFAULT 0.00,
  `cgst_amount` decimal(15,2) DEFAULT 0.00,
  `sgst_amount` decimal(15,2) DEFAULT 0.00,
  `igst_amount` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `po_number`, `po_date`, `seller_name`, `seller_company`, `seller_address`, `seller_phone`, `seller_gst`, `seller_email`, `materials_data`, `subtotal`, `cgst_rate`, `sgst_rate`, `igst_rate`, `cgst_amount`, `sgst_amount`, `igst_amount`, `total_amount`, `created_at`, `updated_at`) VALUES
(1, '655', '2025-09-18', 'Seller', 'pioyh', 'Barathiyar Nagar', '63574651', '534964516948', '2@2.l', '[]', 5555.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 5555.00, '2025-09-18 12:23:10', '2025-09-18 12:27:28'),
(2, '655e', '2025-09-18', 'Seller', 'pioyh', 'Barathiyar Nagar', '63574651', '534964516948', '2@2.l', '[]', 6099.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 6099.00, '2025-09-18 12:23:44', '2025-09-18 12:27:28'),
(3, 'TEST-1758198431', '2025-09-18', 'Test Seller', 'Test Seller Company', 'Test Address', '1234567890', NULL, NULL, '[]', 451.50, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 451.50, '2025-09-18 12:27:11', '2025-09-18 12:27:28'),
(4, 'TEST-1758198454', '2025-09-18', 'Test Seller', 'Test Seller Company', 'Test Address', '1234567890', NULL, NULL, '0', 451.50, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 451.50, '2025-09-18 12:27:34', '2025-09-18 12:27:34'),
(5, 'TEST-1758198474', '2025-09-18', 'Test Seller', 'Test Seller Company', 'Test Address', '1234567890', NULL, NULL, '0', 451.50, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 451.50, '2025-09-18 12:27:54', '2025-09-18 12:27:54'),
(6, 'DIRECT-1758198498', '2025-09-18', 'Test Seller', 'Test Company', 'Test Address', '1234567890', NULL, NULL, '[{\"id\":1,\"name\":\"Test Material 1\",\"hsn_code\":\"1234\",\"price_per_unit\":100.5,\"quantity\":2}]', 451.50, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 451.50, '2025-09-18 12:28:18', '2025-09-18 12:28:18'),
(7, '655e677', '2025-09-18', 'Seller', 'selller', 'Barathiyar Nagar', '63574651', '6315638741', '2@2.l', '[{\"id\":22,\"name\":\"Material -NN\",\"hsn_code\":\"K7\",\"price_per_unit\":544,\"quantity\":1}]', 544.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 544.00, '2025-09-18 12:33:58', '2025-09-18 12:33:58'),
(8, 'TEST-NO-BUYER-1758199139', '2025-09-18', 'Test Seller', 'Test Company', 'Test Address', '1234567890', 'GST123', 'test@test.com', '[{\"id\":1,\"name\":\"Test Material\",\"hsn_code\":\"1234\",\"price_per_unit\":100,\"quantity\":1}]', 100.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 100.00, '2025-09-18 12:38:59', '2025-09-18 12:38:59'),
(9, '655e677a', '2025-09-18', 'Seller', 'pioyh', 'Barathiyar Nagar', '63574651', '6315638741', '2@2.l', '[{\"id\":6,\"name\":\"Material- 88\",\"hsn_code\":\"P8\",\"price_per_unit\":4000,\"quantity\":1}]', 4000.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 4000.00, '2025-09-18 12:39:58', '2025-09-18 12:39:58');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `hsn_code` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_quotations`
--

CREATE TABLE `sales_quotations` (
  `id` int(11) NOT NULL,
  `quotation_number` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `quotation_date` date NOT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_quotations`
--

INSERT INTO `sales_quotations` (`id`, `quotation_number`, `customer_name`, `quotation_date`, `pdf_path`, `created_at`) VALUES
(1, 'ertyuiouytr', 'kuykutxckyd', '2025-09-01', 'quotations/Quotation_ertyuiouytr.pdf', '2025-09-01 14:31:49'),
(2, '541326413', 'lucylhgclhgc', '2025-09-01', 'quotations/Quotation_541326413.pdf', '2025-09-01 17:40:08'),
(3, '878865', 'luf', '2025-09-02', 'quotations/Quotation_878865.pdf', '2025-09-02 18:38:26'),
(4, 'cscasa', 'kiran', '2025-09-15', 'quotations/Quotation_cscasa.pdf', '2025-09-15 09:49:03');

-- --------------------------------------------------------

--
-- Table structure for table `transports`
--

CREATE TABLE `transports` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transports`
--

INSERT INTO `transports` (`id`, `name`, `created_at`) VALUES
(14, 'Transport 1', '2025-09-14 15:01:31'),
(15, 'Transport 2', '2025-09-14 15:01:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(1, 'admin', 'ssenterpriseserp@gmail.com', '$2y$10$7PNctN4AKhJeakVtiz9XYOFU5jB85lNrQLfWfSI.oMaYvJkTVWK.6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_expenses`
--
ALTER TABLE `daily_expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expense_date` (`expense_date`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `idx_po_number` (`po_number`),
  ADD KEY `idx_po_date` (`po_date`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_id` (`purchase_order_id`);

--
-- Indexes for table `sales_quotations`
--
ALTER TABLE `sales_quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotation_number` (`quotation_number`);

--
-- Indexes for table `transports`
--
ALTER TABLE `transports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `daily_expenses`
--
ALTER TABLE `daily_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_quotations`
--
ALTER TABLE `sales_quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transports`
--
ALTER TABLE `transports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
