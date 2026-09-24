-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 22, 2025 at 01:06 PM
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
-- Table structure for table `fortress`
--

CREATE TABLE `fortress` (
  `flak` int(11) NOT NULL,
  `damageResistance` int(11) NOT NULL,
  `hitResistance` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `id` varchar(25) NOT NULL,
  `pId` varchar(25) DEFAULT NULL,
  `cladding` int(11) NOT NULL,
  `storedCladding` int(11) NOT NULL,
  `name` text NOT NULL,
  `damage` text,
  `armory` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `fortress`
--

INSERT INTO `fortress` (`flak`, `damageResistance`, `hitResistance`, `points`, `id`, `pId`, `cladding`, `storedCladding`, `name`, `damage`, `armory`) VALUES
(1, 40, 40, 1481, '000002', '681f762053cb5', 3, 3, 'The Red Redoubt', 'undamaged', '[{\"id\":\"681a176683066\",\"typeId\":6,\"name\":\"TCB\",\"toHit\":80,\"criticalChance\":8,\"dieType\":15,\"debrisChance\":5,\"debrisValue\":1},{\"id\":\"681a26556f3f4\",\"typeId\":7,\"name\":\"Can of Whoop Ass\",\"toHit\":90,\"criticalChance\":10,\"dieType\":20,\"debrisChance\":5,\"debrisValue\":1},{\"id\":\"681a265ecdbaf\",\"typeId\":7,\"name\":\"Can of Whoop Ass\",\"toHit\":90,\"criticalChance\":10,\"dieType\":20,\"debrisChance\":5,\"debrisValue\":1},{\"id\":\"681a2761676bb\",\"typeId\":7,\"name\":\"Can of Whoop Ass\",\"toHit\":90,\"criticalChance\":10,\"dieType\":20,\"debrisChance\":5,\"debrisValue\":1}]'),
(24, 90, 90, 10169, '68436decad0dd', NULL, 8, 8, 'Filthy Donjon of Evil', NULL, '[{\"id\":\"12348\",\"typeId\":\"0\",\"name\":\"Finger Rocket\",\"toHit\":\"20\",\"criticalChance\":\"1\",\"dieType\":\"4\",\"debrisChance\":\"5\",\"debrisValue\":\"1\"},{\"id\":\"12349\",\"typeId\":\"0\",\"name\":\"Finger Rocket\",\"toHit\":\"20\",\"criticalChance\":\"1\",\"dieType\":\"4\",\"debrisChance\":\"5\",\"debrisValue\":\"1\"},{\"id\":\"68573e60abd13\",\"typeId\":5,\"name\":\"ICBM\",\"toHit\":70,\"criticalChance\":5,\"dieType\":12,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68573eb092f2a\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68573fff8262e\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68573fff8b947\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68573fff933d6\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68573fff96ea7\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68573fff9a963\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68573fff9e3ed\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68573fff9f10e\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68580984b9896\",\"typeId\":3,\"name\":\"Bolt\",\"toHit\":50,\"criticalChance\":2,\"dieType\":8,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68580984be130\",\"typeId\":3,\"name\":\"Bolt\",\"toHit\":50,\"criticalChance\":2,\"dieType\":8,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"685809d7c51a0\",\"typeId\":3,\"name\":\"Bolt\",\"toHit\":50,\"criticalChance\":2,\"dieType\":8,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"685809d7c8a74\",\"typeId\":3,\"name\":\"Bolt\",\"toHit\":50,\"criticalChance\":2,\"dieType\":8,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68580a7b63ac4\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68580a7b67762\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68580a8730d8a\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68580a8734794\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68580a8a10090\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68580a8a16cfc\",\"typeId\":4,\"name\":\"ICYMI\",\"toHit\":60,\"criticalChance\":5,\"dieType\":10,\"debrisChance\":10,\"debrisValue\":2},{\"id\":\"68580b397d9b7\",\"typeId\":3,\"name\":\"Bolt\",\"toHit\":50,\"criticalChance\":2,\"dieType\":8,\"debrisChance\":10,\"debrisValue\":2}]'),
(0, 0, 0, 2752, '6845ada4167b2', '681f762053cb6', 1, 1, 'The Eagle\'s Aerie', 'undamaged', '[]'),
(0, 90, 90, 100000, '684d7c5d869b7', NULL, 8, 8, 'Green Citadel of Death', NULL, '[{\"id\":\"12345\",\"typeId\":\"0\",\"name\":\"Finger Rocket\",\"toHit\":\"20\",\"criticalChance\":\"1\",\"dieType\":\"4\",\"debrisChance\":null,\"debrisValue\":null},{\"id\":\"12346\",\"typeId\":\"0\",\"name\":\"Finger Rocket\",\"toHit\":\"20\",\"criticalChance\":\"1\",\"dieType\":\"4\",\"debrisChance\":null,\"debrisValue\":null},{\"id\":\"12347\",\"typeId\":\"0\",\"name\":\"Finger Rocket\",\"toHit\":\"20\",\"criticalChance\":\"1\",\"dieType\":\"4\",\"debrisChance\":null,\"debrisValue\":null},{\"id\":\"12348\",\"typeId\":\"0\",\"name\":\"Finger Rocket\",\"toHit\":\"20\",\"criticalChance\":\"1\",\"dieType\":\"4\",\"debrisChance\":null,\"debrisValue\":null},{\"id\":\"12349\",\"typeId\":\"0\",\"name\":\"Finger Rocket\",\"toHit\":\"20\",\"criticalChance\":\"1\",\"dieType\":\"4\",\"debrisChance\":null,\"debrisValue\":null}]'),
(2, 5, 5, 150, '684d82c887603', NULL, 0, 0, 'Filthy Redoubt of Destruction', NULL, '[\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12345\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    }, \r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12346\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12347\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12348\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12349\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    }\r\n        ]'),
(2, 5, 5, 150, '684d8304bf476', NULL, 0, 0, 'Blue Alcazar of Pain', NULL, '[\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12345\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    }, \r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12346\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12347\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12348\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12349\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    }\r\n        ]'),
(2, 5, 5, 150, '684d83601f04c', NULL, 0, 0, 'Blue Alcazar of Despair', NULL, '[\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12345\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    }, \r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12346\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12347\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12348\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12349\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    }\r\n        ]'),
(2, 5, 5, 150, '684d839bed81f', NULL, 0, 0, 'Grey Bastion of Pain', NULL, '[\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12345\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    }, \r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12346\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12347\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12348\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12349\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    }\r\n        ]'),
(2, 5, 5, 150, '684d845a40d2b', NULL, 0, 0, 'Blue Fastness of Despair', NULL, '[\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12345\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    }, \r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12346\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12347\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12348\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n    {\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12349\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n{\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12350\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n{\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12351\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    },\r\n{\r\n        \"name\": \"Finger Rocket\",\r\n        \"id\": \"12352\",\r\n        \"typeId\": \"0\",\r\n        \"toHit\": \"20\",\r\n        \"criticalChance\": \"1\",\r\n        \"dieType\": \"4\"\r\n    }\r\n        ]');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fortress`
--
ALTER TABLE `fortress`
  ADD UNIQUE KEY `id` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
