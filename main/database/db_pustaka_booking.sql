-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2021 at 07:03 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pustaka_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `image` varchar(256) NOT NULL,
  `author` varchar(50) NOT NULL,
  `publisher` varchar(50) NOT NULL,
  `publication_year` year(4) NOT NULL,
  `stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='buku';

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`id`, `category_id`, `name`, `isbn`, `image`, `author`, `publisher`, `publication_year`, `stock`) VALUES
(12, 4, 'test', '123123', 'book-20210510123541-test.jpeg', 'test', 'test', 2021, 119),
(13, 5, 'hghgc', '34565', 'book-20210510125341-hghgc.png', 'ascasc', 'ascascasc', 2022, 8);

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `booking_number` varchar(256) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pickup_due_date` date NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id`, `booking_number`, `user_id`, `pickup_due_date`, `created_at`) VALUES
(6, 'ID2105106', 2, '2021-05-12', '2021-05-10 21:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `booking_detail`
--

CREATE TABLE `booking_detail` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `booking_detail`
--

INSERT INTO `booking_detail` (`id`, `booking_id`, `book_id`) VALUES
(11, 6, 13),
(12, 6, 12);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='kategori buku';

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'Sains'),
(2, 'Komputer'),
(3, 'Agama'),
(4, 'Bahasa'),
(5, 'Hobby');

-- --------------------------------------------------------

--
-- Table structure for table `loaning`
--

CREATE TABLE `loaning` (
  `id` int(11) NOT NULL,
  `loaning_number` varchar(256) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `loaning_time` int(11) NOT NULL,
  `return_due_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `is_return_done` tinyint(1) NOT NULL,
  `penalty_day` int(11) NOT NULL,
  `penalty_price` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='peminjaman buku';

--
-- Dumping data for table `loaning`
--

INSERT INTO `loaning` (`id`, `loaning_number`, `booking_id`, `user_id`, `loaning_time`, `return_due_date`, `return_date`, `is_return_done`, `penalty_day`, `penalty_price`, `created_at`) VALUES
(7, 'LN2105107', 6, 2, 6, '2021-05-16', '2021-05-10', 1, 0, 1000, '2021-05-10 21:14:22');

-- --------------------------------------------------------

--
-- Table structure for table `loaning_detail`
--

CREATE TABLE `loaning_detail` (
  `id` int(11) NOT NULL,
  `loaning_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='detail peminjaman buku';

--
-- Dumping data for table `loaning_detail`
--

INSERT INTO `loaning_detail` (`id`, `loaning_id`, `book_id`) VALUES
(13, 7, 13),
(14, 7, 12);

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='hak akses';

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'member');

-- --------------------------------------------------------

--
-- Table structure for table `temp`
--

CREATE TABLE `temp` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `email` varchar(25) NOT NULL,
  `password` varchar(256) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(256) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='pengguna';

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `role_id`, `email`, `password`, `name`, `image`, `is_active`, `created_at`) VALUES
(1, 1, 'admin@gmail.com', '$2y$10$VIiM8rqmexhdZOyhLkLXlelAKUg3JJjeJu8Sme/UgWCMs.DMPrnsC', 'Admin Pustaka Booking', NULL, 1, '2021-05-10 08:58:00'),
(2, 2, 'testing@gmail.com', '$2y$10$X/frVeHqpepvc7XsU.7joO0ZOEqwoUwJqoyJAXDIdlghFBvUGM5X2', 'Testing Member', NULL, 1, '2021-05-10 12:39:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `booking_detail`
--
ALTER TABLE `booking_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loaning`
--
ALTER TABLE `loaning`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `loaning_detail`
--
ALTER TABLE `loaning_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loaning_id` (`loaning_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp`
--
ALTER TABLE `temp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `book`
--
ALTER TABLE `book`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `booking_detail`
--
ALTER TABLE `booking_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `loaning`
--
ALTER TABLE `loaning`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `loaning_detail`
--
ALTER TABLE `loaning_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `temp`
--
ALTER TABLE `temp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book`
--
ALTER TABLE `book`
  ADD CONSTRAINT `book_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `booking_detail`
--
ALTER TABLE `booking_detail`
  ADD CONSTRAINT `booking_detail_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_detail_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `book` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `loaning`
--
ALTER TABLE `loaning`
  ADD CONSTRAINT `loaning_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `loaning_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `loaning_detail`
--
ALTER TABLE `loaning_detail`
  ADD CONSTRAINT `loaning_detail_ibfk_1` FOREIGN KEY (`loaning_id`) REFERENCES `loaning` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `loaning_detail_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `book` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `temp`
--
ALTER TABLE `temp`
  ADD CONSTRAINT `temp_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `book` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `temp_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
