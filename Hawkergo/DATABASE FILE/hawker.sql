-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2026 at 01:43 PM
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
-- Database: `hawker`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adm_id` int(222) NOT NULL,
  `username` varchar(222) NOT NULL,
  `password` varchar(222) NOT NULL,
  `email` varchar(222) NOT NULL,
  `code` varchar(222) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adm_id`, `username`, `password`, `email`, `code`, `date`, `reset_expires`) VALUES
(1, 'admin', 'CAC29D7A34687EB14B37068EE4708E7B', 'admin@mail.com', 'b3ab7c20a315f1f812723f4d4d826245cf23f961574678794d610a3751ac572b', '2026-06-13 09:01:26', '2026-06-13 12:01:26'),
(2, 'Rama', '34bc115cf89f5cc0b1bb8b9983bbab29', '36rama36@gmail.com', '', '2026-06-13 04:54:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `d_id` int(222) NOT NULL,
  `rs_id` int(222) NOT NULL,
  `title` varchar(222) NOT NULL,
  `slogan` varchar(222) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `img` varchar(222) NOT NULL,
  `is_sold_out` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`d_id`, `rs_id`, `title`, `slogan`, `price`, `img`, `is_sold_out`) VALUES
(1, 1, 'Mutton satay', 'Mutton satay  consisting of skewered, marinated meat that is grilled and served with a rich peanut dipping sauce, cucumber, and onions. (5 Sticks)', 10.00, '6a27988c9888c.jpg', 0),
(2, 1, 'Pork satay', 'Pork satay  consisting of skewered, marinated meat that is grilled and served with a rich peanut dipping sauce, cucumber, and onions. (10 Sticks)', 10.00, '6a279918719c7.jpg', 0),
(4, 1, 'Chicken satay ', 'Chicken satay  consisting of skewered, marinated meat that is grilled and served with a rich peanut dipping sauce, cucumber, and onions. (10 Sticks)', 10.00, '6a27996c18a94.jpg', 0),
(5, 2, 'Sugarcane juice ', 'Sugarcane juice is a freshly pressed, unrefined beverage extracted from sugarcane stalks.', 2.00, '6a279bd097e99.jpg', 0),
(6, 2, 'Kopi', 'Kopi made from Robusta coffee beans that are traditionally roasted with sugar and margarine, creating a rich, deeply caramelized, and caffeinated brew.', 1.20, '6a279c4e890dd.png', 0),
(7, 2, 'Kopi C Kosong ', 'Kopi C Kosong  made with dark roasted coffee, unsweetened evaporated milk, and no sugar', 1.50, '6a279cc63a197.png', 0),
(8, 2, 'Kopi o kosong ', 'Kopi O Kosong is traditional black coffee with no milk and no sugar added.', 1.50, '6a279da5cb6e9.png', 0),
(9, 3, 'Vegetable Fried Rice', 'Chinese rice wok with cabbage, beans, carrots, and spring onions.', 5.00, '606d7575798fb.jpg', 0),
(10, 3, 'Prawn Crackers', '12 pieces deep-fried prawn crackers', 7.00, '606d75a7e21ec.jpg', 0),
(11, 3, 'Spring Rolls', 'Lightly seasoned shredded cabbage, onion and carrots, wrapped in house made spring roll wrappers, deep fried to golden brown.', 6.00, '606d75ce105d0.jpg', 0),
(12, 3, 'Manchurian Chicken', 'Chicken pieces slow cooked with spring onions in our house made manchurian style sauce.', 11.00, '606d7600dc54c.jpg', 0),
(17, 8, 'Value meal', 'crispy fried chicken wings , rice,caesar salad', 3.00, '6a2790b98bb07.jpg', 0),
(18, 8, 'Chicken Chop ', 'Golden-fried or grilled boneless chicken thigh drenched in a savory, buttery mushroom cream gravy, typically served with crispy fries, coleslaw, and buttered toast.', 6.50, '6a2791c116fee.jpg', 0),
(19, 8, 'Pork Chop', 'Grilled pork thigh drenched in a savory, buttery mushroom cream gravy, typically served with crispy fries, coleslaw, and buttered toast.', 6.50, '6a2792d2e5fdd.jpg', 0),
(20, 7, 'Pork Bun', ' Features a snowy-white, fluffy yeasted dough. The dough is often slightly sweet, and the top can be left smooth or pleated, sometimes splitting open slightly when steamed', 1.50, '6a2793f8532de.jpg', 0),
(21, 7, 'Pork and Chives Dumplings', 'Homemade dumplings, featuring a juicy, savory filling folded in a chewy wrapper.', 4.00, '6a2794b776b3b.jpg', 0),
(22, 7, 'Frozen Dumplings', 'Frozen dumplings, featuring a juicy, savory filling folded in a chewy wrapper.', 4.00, '6a2794fd26802.jpg', 0),
(23, 7, 'Dumplings with red chilli oil sauce', 'Dumplings with red chilli oil sauce are tender, juicy dumplings swimming in an intoxicating, umami-packed sauce. ', 5.00, '6a2795d900e4d.jpg', 0),
(24, 9, 'Chicken Rice', 'Consisting of tender poached or roasted chicken served over fragrant, flavorful rice.', 4.00, '6a27b3de584ce.jpg', 0),
(25, 9, 'Prawm Noodle', 'Consisting of yellow egg noodles and rice vermicelli (bee hoon) bathed in a rich, umami-packed broth made from simmered prawn heads, shells, and pork bones', 5.50, '6a27b46525664.jpg', 0),
(26, 9, 'Minced Pork Noodle', 'It features springy egg noodles tossed in a signature savory, spicy, and tangy sauce, generously topped with braised mushrooms, minced pork, tender pork slices, meatballs, and crispy lard.', 4.50, '6a27b4e21f319.jpg', 0),
(27, 9, 'Wanton Mee', 'Consisting of thin, springy egg noodles tossed in a savory, oily sauce or served in a hot broth.', 5.00, '6a27b525d6c1c.jpg', 0),
(28, 4, 'Fish soup', 'Fish soup is a highly versatile and nourishing dish made by combining fresh fish or seafood with vegetables, herbs, and a savory broth. ', 4.00, '6a27b5a4af584.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `dish_reviews`
--

CREATE TABLE `dish_reviews` (
  `review_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `d_id` int(11) NOT NULL,
  `o_id` int(11) NOT NULL,
  `rs_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dish_reviews`
--

INSERT INTO `dish_reviews` (`review_id`, `u_id`, `d_id`, `o_id`, `rs_id`, `rating`, `comment`, `created_at`) VALUES
(1, 7, 18, 45, 8, 3, 'I love it', '2026-06-12 01:42:04');

-- --------------------------------------------------------

--
-- Table structure for table `hawkerstalls`
--

CREATE TABLE `hawkerstalls` (
  `rs_id` int(222) NOT NULL,
  `c_id` int(222) NOT NULL,
  `title` varchar(222) NOT NULL,
  `email` varchar(222) NOT NULL,
  `phone` varchar(222) NOT NULL,
  `url` varchar(222) NOT NULL,
  `o_hr` varchar(222) NOT NULL,
  `c_hr` varchar(222) NOT NULL,
  `o_days` varchar(222) NOT NULL,
  `address` text NOT NULL,
  `image` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `accepting_orders` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `hawkerstalls`
--

INSERT INTO `hawkerstalls` (`rs_id`, `c_id`, `title`, `email`, `phone`, `url`, `o_hr`, `c_hr`, `o_days`, `address`, `image`, `date`, `accepting_orders`) VALUES
(1, 1, 'Satay Stall', 'nthavern@mail.com', ' 6510 3000', 'www.northstreettavern.com', '10am', '11pm', 'Tue-Sun', ' 9 Woodlands Ave 9, Singapore 738964 ', '6a2b7b7396cfa.jpg', '2026-06-12 03:22:27', 1),
(2, 2, 'Hot and Cold Beverages Stall', 'hot&coldstall@gmail.com', '+65 98765432', 'www.hot&coldstall.com', '7am', '8pm', 'mon-sat', '9 Woodlands Ave 9, Singapore 738964', '6a279aa62e075.jpg', '2026-06-09 04:46:30', 1),
(4, 4, 'Porridge and Soups Stall', 'porridge&soup@mail.com', '+65 95687458', 'www.porridge&soup.com', '6am', '8pm', 'mon-sat', '9 Woodlands Ave 9, Singapore 738964', '6a27b27e4fb8a.jpg', '2026-06-09 06:28:14', 1),
(7, 5, 'Dumpling Stall', 'dumplings@mail.com', '+65 8745260', 'www.dumplings.com', '7am', '10pm', 'Mon-Sat', '9 Woodlands Ave 9, Singapore 738964', '6a27877bd718f.png', '2026-06-12 03:15:04', 1),
(8, 6, 'Western Food Stall', 'westernfood@mail.com', ' 6510 3000', 'www.westernfood.com', '7am', '9pm', 'Mon-Sat', '9 Woodlands Ave 9, Singapore 738964', '6a2787cb59a22.png', '2026-06-09 03:26:03', 1),
(9, 3, 'Noodle Stall', 'noodle@gmail.com', ' +65 66103000', 'wwwnoodlestall.com', '7am', '8pm', 'Mon-Sat', '9 Woodlands Ave 9, Singapore 738964', '6a27b37c2680b.jpg', '2026-06-09 06:32:28', 1);

-- --------------------------------------------------------

--
-- Table structure for table `hawker_users`
--

CREATE TABLE `hawker_users` (
  `h_id` int(11) NOT NULL,
  `rs_id` int(11) NOT NULL,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hawker_users`
--

INSERT INTO `hawker_users` (`h_id`, `rs_id`, `username`, `password`, `email`, `created_at`, `reset_token`, `reset_expires`) VALUES
(1, 1, 'stall1', '$2y$10$TDwX.xY0Rb2iR45JbGEBP.AHmOq4UkzZOs1bh2U09qyGVATmBenfa', 'stall1@hawker.local', '2026-06-13 16:33:15', 'cfd900ba90abd72e69cd51f09e5acf32f9e7665ca3a91d7bc39779293ffd1e4c', '2026-06-13 12:01:26'),
(2, 2, 'stall2', '$2y$10$TDwX.xY0Rb2iR45JbGEBP.AHmOq4UkzZOs1bh2U09qyGVATmBenfa', 'stall2@hawker.local', '2026-06-13 16:33:15', NULL, NULL),
(3, 4, 'stall4', '$2y$10$TDwX.xY0Rb2iR45JbGEBP.AHmOq4UkzZOs1bh2U09qyGVATmBenfa', 'stall4@hawker.local', '2026-06-13 16:33:15', NULL, NULL),
(4, 7, 'stall7', '$2y$10$TDwX.xY0Rb2iR45JbGEBP.AHmOq4UkzZOs1bh2U09qyGVATmBenfa', 'stall7@hawker.local', '2026-06-13 16:33:15', NULL, NULL),
(5, 8, 'stall8', '$2y$10$TDwX.xY0Rb2iR45JbGEBP.AHmOq4UkzZOs1bh2U09qyGVATmBenfa', 'stall8@hawker.local', '2026-06-13 16:33:15', NULL, NULL),
(6, 9, 'stall9', '$2y$10$TDwX.xY0Rb2iR45JbGEBP.AHmOq4UkzZOs1bh2U09qyGVATmBenfa', 'stall9@hawker.local', '2026-06-13 16:33:15', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_issues`
--

CREATE TABLE `payment_issues` (
  `issue_id` int(11) NOT NULL,
  `o_id` int(11) NOT NULL,
  `rs_id` int(11) NOT NULL,
  `h_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `admin_note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remark`
--

CREATE TABLE `remark` (
  `id` int(11) NOT NULL,
  `frm_id` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `remark` mediumtext NOT NULL,
  `remarkDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `remark`
--

INSERT INTO `remark` (`id`, `frm_id`, `status`, `remark`, `remarkDate`) VALUES
(1, 2, 'in process', 'none', '2026-05-01 05:17:49'),
(2, 3, 'in process', 'none', '2026-05-27 11:01:30'),
(3, 2, 'closed', 'thank you for your order!', '2026-05-27 11:11:41'),
(4, 3, 'closed', 'none', '2026-05-27 11:42:35'),
(5, 4, 'in process', 'none', '2026-05-27 11:42:55'),
(6, 1, 'rejected', 'none', '2026-05-27 11:43:26'),
(7, 7, 'in process', 'none', '2026-05-27 13:03:24'),
(8, 8, 'in process', 'none', '2026-05-27 13:03:38'),
(9, 9, 'rejected', 'thank you', '2026-05-27 13:03:53'),
(10, 7, 'closed', 'thank you for your ordering with us', '2026-05-27 13:04:33'),
(11, 8, 'closed', 'thanks ', '2026-05-27 13:05:24'),
(12, 5, 'closed', 'none', '2026-05-27 13:18:03'),
(13, 45, 'closed', 'done', '2026-06-11 17:39:07');

-- --------------------------------------------------------

--
-- Table structure for table `res_category`
--

CREATE TABLE `res_category` (
  `c_id` int(222) NOT NULL,
  `c_name` varchar(222) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `res_category`
--

INSERT INTO `res_category` (`c_id`, `c_name`, `date`) VALUES
(1, 'Satay Stall', '2026-06-09 03:06:00'),
(2, 'Hot and Cold Beverages Stall', '2026-06-09 06:22:40'),
(3, 'Noodle Stall', '2026-06-09 06:23:27'),
(4, ' Porridge and Soups Stall', '2026-06-09 06:28:46'),
(5, 'Dumpling Stall', '2026-06-09 03:08:58'),
(6, 'Western Food Stall', '2026-06-09 03:09:15');

-- --------------------------------------------------------

--
-- Table structure for table `stall_reviews`
--

CREATE TABLE `stall_reviews` (
  `review_id` int(11) NOT NULL,
  `u_id` int(11) NOT NULL,
  `rs_id` int(11) NOT NULL,
  `order_batch_id` varchar(32) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `u_id` int(222) NOT NULL,
  `username` varchar(222) NOT NULL,
  `f_name` varchar(222) NOT NULL,
  `l_name` varchar(222) NOT NULL,
  `email` varchar(222) NOT NULL,
  `phone` varchar(222) NOT NULL,
  `password` varchar(222) NOT NULL,
  `status` int(222) NOT NULL DEFAULT 1,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `username`, `f_name`, `l_name`, `email`, `phone`, `password`, `status`, `date`, `address`) VALUES
(7, 'SSS', 'AA', 'A', '36rama36@gmail.com', '+65 8745260', '$2y$10$PwQW.2Fn9wK3AGV99gopnOUMf/KK/3dPIsooy/qo.ri/K/dSko4qu', 1, '2026-06-11 16:05:29', '');

-- --------------------------------------------------------

--
-- Table structure for table `users_orders`
--

CREATE TABLE `users_orders` (
  `o_id` int(222) NOT NULL,
  `u_id` int(222) NOT NULL,
  `title` varchar(222) NOT NULL,
  `quantity` int(222) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` varchar(222) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rs_id` int(11) DEFAULT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `special_request` text DEFAULT NULL,
  `order_batch_id` varchar(32) DEFAULT NULL,
  `payment_method` varchar(30) DEFAULT NULL,
  `order_type` varchar(20) DEFAULT NULL,
  `queue_number` int(11) DEFAULT NULL,
  `verification_code` varchar(10) DEFAULT NULL,
  `ordered_at` datetime DEFAULT current_timestamp(),
  `d_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users_orders`
--

INSERT INTO `users_orders` (`o_id`, `u_id`, `title`, `quantity`, `price`, `status`, `date`, `rs_id`, `guest_phone`, `guest_name`, `special_request`, `order_batch_id`, `payment_method`, `order_type`, `queue_number`, `verification_code`, `ordered_at`, `d_id`) VALUES
(25, 7, 'Mutton satay', 1, 10.00, 'in process', '2026-06-11 17:35:13', 1, '', '', '', 'ord_6a2ac58be79899.77535024', 'COD', 'takeaway', 1, 'C1ECE4', '2026-06-11 22:36:28', 1),
(26, 7, 'Chicken satay ', 1, 10.00, 'in process', '2026-06-11 17:35:13', 1, '', '', '', 'ord_6a2ac58be79899.77535024', 'COD', 'takeaway', 1, 'C1ECE4', '2026-06-11 22:36:28', 4),
(27, 7, 'Fish soup', 1, 4.00, 'in process', '2026-06-11 17:35:13', 4, '', '', '', 'ord_6a2ac58be79899.77535024', 'COD', 'takeaway', 1, 'C1ECE4', '2026-06-11 22:36:28', 28),
(28, 7, 'Frozen Dumplings', 1, 4.00, 'in process', '2026-06-11 17:35:13', 7, '', '', '', 'ord_6a2ac58be79899.77535024', 'COD', 'takeaway', 1, 'C1ECE4', '2026-06-11 22:36:28', 22),
(29, 0, 'Test2', 1, 5.00, 'in process', '2026-06-11 14:35:39', 2, '91111111', 'T', '', 'ord_6a2ac7bbbe34c4.97483386', 'COD', 'takeaway', 2, 'CE969A', '2026-06-11 22:36:28', NULL),
(30, 0, 'Test2', 1, 5.00, 'in process', '2026-06-11 14:35:39', 2, '91111111', 'T', '', 'ord_6a2ac7bbbee3a5.87290299', 'COD', 'takeaway', 3, '0C5A07', '2026-06-11 22:36:28', NULL),
(31, 7, 'Pork and Chives Dumplings', 2, 4.00, 'in process', '2026-06-11 17:35:13', 7, '', '', '', 'ord_6a2ac9ab4b1881.50785625', 'COD', 'dine-in', 1, '7807D6', '2026-06-11 22:43:55', 21),
(32, 7, 'Sugarcane juice ', 1, 2.00, 'in process', '2026-06-11 17:35:13', 2, '', '', '', 'ord_6a2ac9ab4b1881.50785625', 'COD', 'dine-in', 1, '7807D6', '2026-06-11 22:43:55', 5),
(33, 7, 'Prawm Noodle', 1, 5.50, 'in process', '2026-06-11 17:35:13', 9, '', '', '', 'ord_6a2ac9ab4b1881.50785625', 'COD', 'dine-in', 1, '7807D6', '2026-06-11 22:43:55', 25),
(34, 7, 'Chicken Chop ', 1, 6.50, 'in process', '2026-06-11 17:35:13', 8, '', '', '', 'ord_6a2ac9ab4b1881.50785625', 'COD', 'dine-in', 1, '7807D6', '2026-06-11 22:43:55', 18),
(35, 7, 'Pork Bun', 1, 1.50, 'in process', '2026-06-11 17:35:13', 7, '', '', '', 'ord_6a2aca14a5d5b1.27925987', 'COD', 'dine-in', 2, 'F55887', '2026-06-11 22:45:40', 20),
(36, 7, 'Pork Chop', 1, 6.50, 'in process', '2026-06-11 17:35:13', 8, '', '', '', 'ord_6a2aca14a5d5b1.27925987', 'COD', 'dine-in', 2, 'F55887', '2026-06-11 22:45:40', 19),
(37, 7, 'Kopi o kosong ', 1, 1.50, 'in process', '2026-06-11 17:35:13', 2, '', '', '', 'ord_6a2aca14a5d5b1.27925987', 'COD', 'dine-in', 2, 'F55887', '2026-06-11 22:45:40', 8),
(38, 7, 'Chicken Chop ', 1, 6.50, 'in process', '2026-06-11 17:35:13', 8, '', '', '', 'ord_6a2acb71886a72.56548149', 'COD', 'takeaway', 1, 'FB3D3B', '2026-06-11 22:51:29', 18),
(40, 7, 'Sugarcane juice ', 1, 2.00, 'in process', '2026-06-11 17:35:13', 2, '', '', '', 'ord_6a2acc9d3945a0.02192709', 'COD', 'dine-in', 4, 'A4DCAA', '2026-06-11 22:56:29', 5),
(41, 7, 'Chicken Rice', 1, 4.00, 'in process', '2026-06-11 17:35:13', 9, '', '', '', 'ord_6a2acc9d3945a0.02192709', 'COD', 'dine-in', 2, 'A4DCAA', '2026-06-11 22:56:29', 24),
(42, 7, 'Value meal', 1, 3.00, 'in process', '2026-06-11 17:35:13', 8, '', '', '', 'ord_6a2ad9faa3ef86.22946290', 'COD', 'takeaway', 3, '9C0E36', '2026-06-11 23:53:30', 17),
(43, 7, 'Sugarcane juice ', 1, 2.00, 'in process', '2026-06-11 17:35:13', 2, '', '', '', 'ord_6a2ade47b95364.59122866', 'COD', 'takeaway', 1, '986502', '2026-06-12 00:11:51', 5),
(44, 7, 'Pork satay', 1, 10.00, 'in process', '2026-06-11 17:35:13', 1, '', '', '', 'ord_6a2aee728b5e11.08715102', 'CARD', 'dine-in', 1, '616301', '2026-06-12 01:20:50', 2),
(45, 7, 'Chicken Chop ', 1, 6.50, 'closed', '2026-06-11 17:39:07', 8, '', '', '', 'ord_6a2aefed781ca3.37093006', 'COD', 'dine-in', 1, '5CF703', '2026-06-12 01:27:09', 18);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adm_id`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`d_id`);

--
-- Indexes for table `dish_reviews`
--
ALTER TABLE `dish_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `uniq_dish_order_review` (`u_id`,`o_id`),
  ADD KEY `idx_dish_reviews_d_id` (`d_id`),
  ADD KEY `idx_dish_reviews_rs_id` (`rs_id`);

--
-- Indexes for table `hawkerstalls`
--
ALTER TABLE `hawkerstalls`
  ADD PRIMARY KEY (`rs_id`);

--
-- Indexes for table `hawker_users`
--
ALTER TABLE `hawker_users`
  ADD PRIMARY KEY (`h_id`),
  ADD UNIQUE KEY `uniq_hawker_username` (`username`),
  ADD UNIQUE KEY `uniq_hawker_rs_id` (`rs_id`);

--
-- Indexes for table `payment_issues`
--
ALTER TABLE `payment_issues`
  ADD PRIMARY KEY (`issue_id`),
  ADD KEY `idx_payment_issues_rs_id` (`rs_id`),
  ADD KEY `idx_payment_issues_status` (`status`);

--
-- Indexes for table `remark`
--
ALTER TABLE `remark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `res_category`
--
ALTER TABLE `res_category`
  ADD PRIMARY KEY (`c_id`);

--
-- Indexes for table `stall_reviews`
--
ALTER TABLE `stall_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `uniq_stall_batch_review` (`u_id`,`rs_id`,`order_batch_id`),
  ADD KEY `idx_stall_reviews_rs_id` (`rs_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `users_orders`
--
ALTER TABLE `users_orders`
  ADD PRIMARY KEY (`o_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adm_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `d_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `dish_reviews`
--
ALTER TABLE `dish_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hawkerstalls`
--
ALTER TABLE `hawkerstalls`
  MODIFY `rs_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `hawker_users`
--
ALTER TABLE `hawker_users`
  MODIFY `h_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_issues`
--
ALTER TABLE `payment_issues`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remark`
--
ALTER TABLE `remark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `res_category`
--
ALTER TABLE `res_category`
  MODIFY `c_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stall_reviews`
--
ALTER TABLE `stall_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users_orders`
--
ALTER TABLE `users_orders`
  MODIFY `o_id` int(222) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
