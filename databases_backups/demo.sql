-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
-- Host: localhost:3306
-- Generation Time: Jul 18, 2024 at 08:42 AM
-- Server version: 10.5.20-MariaDB
-- PHP Version: 7.3.33

START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

-- Table structure for table `register`
CREATE TABLE IF NOT EXISTS `register` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(30) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `repass` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `register`
INSERT INTO `register` (`id`, `username`, `password`, `repass`) VALUES
(6, 'mrX', '$2y$10$gDj/pGqkh6XdNKaFAtrPXuNqRsdRw./dNy16RTSgjwdA0cBIEUEa.', '$2y$10$kBiZ.hhdAP.xgsh2ivIizOiXnib4xEKMF3iLykyzdXjjG.C47TZ7m'),
(7, 'Ayanabha', '$2y$10$dr.vYSQJevtu6ZU33dHIduiE19kma1xT.ESVa3prMbrawbory6326', '$2y$10$dmbSwtiv4XPiBmqOhG3R3.FMKAQVBdJ0QcRM8c4AYl2c6GWii9r4a'),
(8, 'anonymous', '$2y$10$MvBGe2oLcV0pyZVK.8LIU.KYh.9HRavEE2DN68C0nN3stLcpwJ3By', '$2y$10$gjMcu7ZfFSCCZOaIp4wk/OkXWc686Z4ROPl/PL7Q1fut1P/pNCaTa'),
(9, 'View', '$2y$10$kBsOnWYCpFddVKHCL4eEf.Qe4uVLw.qzBDl4A1cFZVpuQ0fZ6SjeW', '$2y$10$fp0WaS.cTSUAlLPFvFprNuefmF5B0uVWAhH4F.WwrIM7qPZtfYC5S'),
(11, 'Dread Pairet Robat', '$2y$10$rg9VLxWtC0mWg4hIu7dC2OZF/Oula2pTPw.Dz5lVtgMlodoMttEYO', '$2y$10$1pmIKvFb8j2E.zjMFSyLuuUJnFEHvshZRC.isGukcsk46wI3N5QAG'),
(12, 'admin', '$2y$10$GZCIANRdWiIJxrT75dbhAupU7DwC4tRedo2JYHtVIcoPhdTs2hMVq', '$2y$10$RP7UXX.sh117O9AydVHLI.2m275dBExDnjMy72magltPp.PrwZIN6'),
(13, 'ladmin', '$2y$10$kHkpdsfhjQTsIcgkKDDFHekreV9l2mRz1Ra45uLssiWOXn8P3ufgW', '$2y$10$Ymckjpi3EjTAExiZ3hqwfe49eGbja0oTt3SxsUy8QqOYgrC/uzNBG'),
(14, 'Sunada', '$2y$10$5WW94HzFrVGf2Hu3ug91y.k9E8.SA1bwRtbfXEPnppyYU67pCkWYq', '$2y$10$L50Dk/1/XDvvLa2Eve70NuxBtJW/gSR0TYMb8Qg6Gp3W3h11FVV5O'),
(15, 'Sunada', '$2y$10$hzUEdxXf90Lflh/Pqk50MeZ4yvIQjAhPjeaNzTEEyJ06u5Uaktsmq', '$2y$10$i6wu34kNeXtkmyDAmEqlhenu4QS646z7UwkLScX4SoevG9CaLW7a.'),
(16, 'fuxAdmin', '$2y$10$ujVZ13ECNoh7f2S2BYfFjuMvz8dO.vBUELJ5RUeYvXMSbMkHdbQZO', '$2y$10$jSK5c2uQSsicZLouGjHpxecKla8gcSYJsMObxr4A9inJI8cFfbe3W'),
(18, 'Rahul', '$2y$10$T7DLsGo4vrf9AT0/hh2yEOZ/CfEJdXboh4WUZUk1BQenE31nKQiii', '$2y$10$HlkvZ.0gtjIf4W5Qi8WxP.AzJj32z5vjCJx9hSEn1NcUuHfc5ChaG'),
(19, 'dodo', '$2y$10$WzRTraF1JnDSRqIqoiQm6e9QDVxPEffRe3Tr8FLgyvnMHiY7qhCSW', '$2y$10$R4SmmVcL/aKRktvN44QaOe86XDyZoplF5F.wMSJYgovLklpm0/kEK'),
(20, 'Viewjs', '$2y$10$eMULnDtIcLcKU4x/l02km.dfnvUCFzpTiRM4UDL5P/yY.39wobuzm', '$2y$10$SNJZ26y7TzJXMPx4WTpBM.Xy/y9pOmZhwEbAO2.5SAjagBKOVUKbi'),
(21, 'Test', '$2y$10$v9qnaGWCwV35tPRY.lBWNObh7qkV0dpsgGpNNMyK6RmNnlc3fLp8.', '$2y$10$gxYcxbEiXgszm2dJfzx/euEfStxBCg3ky/2N39Tkp8NNGsC1Iv07q'),
(22, 'fake', '$2y$10$BcOJRB0gCa9HTgC39wpCZuVmqEcu8jTCSxVOOhF51tvXJQVPX6Gee', '$2y$10$lHxWSiwS.mLPFb4aVPENJ.B5FLUK/3D6HA7w2GMiczOXpci0Ps8Wq'),
(28, 'neelak', '$2y$10$iKyUAvOY3qONS77G1n7nwOH5R7D.G9JIVzI7gkfPKTnAt145YIt4W', '$2y$10$r90eNu.Mhyc2eXhFHeKw2.OTJEFS2mKSP8dC2GPNJRcIr.paGTVDC'),
(29, 'nomore', '$2y$10$Hu4Kn21SXuvFQnnTfTVTcOGbCNrwux30Utlksjq7jV9K5dgbnyfYq', '$2y$10$WoxoLQYaUMJtOx1YmEfZaukLgEZUUOCCQCIKZvXUdDI/jQwBYFDgi');

-- Ensure AUTO_INCREMENT starts correctly
ALTER TABLE `register` AUTO_INCREMENT = 30;

-- --------------------------------------------------------

-- Table structure for table `storage`
CREATE TABLE IF NOT EXISTS `storage` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(225) NOT NULL,
  `links` VARCHAR(500) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `storage`
INSERT INTO `storage` (`id`, `username`, `email`, `links`, `password`) VALUES
(1, 'abcd', 'ayabhachatterjeeBuck@gmail.com', '', '21331231bnm'),
(15, 'Ayanabha', 'ayan@gmail.com', 'https://www.youtube.com/', 'dkjsdkhfd646546464'),
(24, 'fuxAdmin', 'raunakkhatry@gmail.com', 'https://www.youtube.com/', '12345678ascd'),
(26, 'fuxAdmin', 'abcd@gmail.com', 'https://stackoverflow.com/', 'hdjskdsa45645'),
(27, 'fuxAdmin', 'somthing@gmail.com', 'https://www.youtube.com/', '12345678'),
(30, 'Viewjs', 'raunakkhatry@', 'Error', 'pass@#$12345'),
(32, 'Viewjs', 'ayabhachatterjeeBuck@gmail.com', 'https://www.youtube.com/', '67676&&*&*'),
(33, 'Test', 'raunakkhatry@gmail.com', 'https://www.youtube.com/', 'qwert123456789'),
(37, 'nomore', 'raunak@hotmail.com', 'https://chat.openai.com/c/f65877f7-9466-48dc-b8dc-1a3ca8ec7c3d', 'g789456');

-- Ensure AUTO_INCREMENT starts correctly
ALTER TABLE `storage` AUTO_INCREMENT = 38;

COMMIT;
