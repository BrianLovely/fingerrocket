-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 22, 2025 at 01:07 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_fingerrocket`
--

-- --------------------------------------------------------

--
-- Table structure for table `gamehandler`
--

CREATE TABLE `gamehandler` (
  `id` varchar(25) NOT NULL,
  `basePoints` int(11) NOT NULL,
  `p1` varchar(25) DEFAULT NULL,
  `p2` varchar(25) DEFAULT NULL,
  `f1` varchar(25) DEFAULT NULL,
  `f2` varchar(25) DEFAULT NULL,
  `gameLog` longtext NOT NULL,
  `playerUp` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `gamehandler`
--

INSERT INTO `gamehandler` (`id`, `basePoints`, `p1`, `p2`, `f1`, `f2`, `gameLog`, `playerUp`) VALUES
('1', 5, NULL, NULL, 'f1', 'f2', '[\"The Red Redoubt is awarded 95 points!\",\"The Red Redoubt attacks The Eagle\'s Aerie with a Can of Whoop Ass!\",\"Defender resistance D10 roll: 2 + damageRes: 0 + Cladding: 1 Total: 3\",\"Rocket toHit: 90 + D10 roll: 8 Total: 98\",\"It\'s a hit!\",\"The Eagle\'s Aerie\'s Wood cladding takes 70 damage!\",\"The Red Redoubt is awarded 95 points!\",\"The Red Redoubt attacks The Eagle\'s Aerie with a Can of Whoop Ass!\",\"Defender resistance D10 roll: 1 + damageRes:  + Cladding: 1 Total: 2\",\"Rocket toHit: 90 + D10 roll: 10 Total: 100\",\"It\'s a hit!\",\"The Eagle\'s Aerie\'s Wood cladding takes 40 damage!\",\"The Red Redoubt is awarded 95 points!\"]', 'f1'),
('5', 5, NULL, NULL, '000002', '', '', 'player'),
('6841983d1db49', 5, '681f762053cb7', '681f762053cb6', '684d82c887603', '684d845a40d2b', '', 'player'),
('68443e5fd233b', 5, '681f762053cb6', '681f762053cb5', '68436decad0dd', '684d7c5d869b7', '[\"Filthy Donjon of Evil is awarded 65 points!\",\"Filthy Donjon of Evil attacks Green Citadel of Death with a ICYMI!\",\"Defender resistance D10 roll: 10 + damageRes:  + Cladding:  Total: 10\",\"Rocket toHit: 60 + D10 roll: 8 Total: 68\",\"It\'s a hit!\",\"Green Citadel of Death\'s damaged Steel cladding takes 25 damage!\",\"Green Citadel of Death\'s damaged Steel cladding is degraded to damaged Steel\",\"Filthy Donjon of Evil is awarded 65 points!\",\"Filthy Donjon of Evil attacks Green Citadel of Death with a Dart!\",\"Defender resistance D10 roll: 4 + damageRes:  + Cladding:  Total: 4\",\"Rocket toHit: 30 + D10 roll: 1 Total: 31\",\"It\'s a hit!\",\"Ouch, that really hurt!\",\"Green Citadel of Death\'s damaged Titanium cladding takes 20 damage!\",\"Green Citadel of Death\'s damaged Titanium cladding is degraded to damaged Titanium\",\"Filthy Donjon of Evil is awarded 35 points!\",\"Filthy Donjon of Evil attacks Green Citadel of Death with a ICYMI!\",\"It\'s a hit!\",\"Ouch, that really hurt!\",\"Green Citadel of Death\'s Wood cladding takes 50 damage!\",\"Filthy Donjon of Evil is awarded 65 points!\"]', 'player'),
('68499afee4da6', 5, NULL, NULL, '', '', 'Array', 'player'),
('68499b175b246', 5, NULL, NULL, '', '', 'Array', 'player');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gamehandler`
--
ALTER TABLE `gamehandler`
  ADD UNIQUE KEY `id` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
