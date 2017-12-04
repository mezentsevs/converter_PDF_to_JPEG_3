-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Дек 04 2017 г., 08:28
-- Версия сервера: 5.7.19
-- Версия PHP: 7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `db_converter_pdf3`
--

-- --------------------------------------------------------

--
-- Структура таблицы `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8_bin NOT NULL,
  `type` varchar(100) COLLATE utf8_bin NOT NULL,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=409 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `documents`
--

INSERT INTO `documents` (`id`, `filename`, `type`, `size`) VALUES
(408, 'test7_1512375774.pdf', 'pdf', 532803);

-- --------------------------------------------------------

--
-- Структура таблицы `images`
--

DROP TABLE IF EXISTS `images`;
CREATE TABLE IF NOT EXISTS `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `document_filename` varchar(255) COLLATE utf8_bin NOT NULL,
  `filename` varchar(255) COLLATE utf8_bin NOT NULL,
  `type` varchar(100) COLLATE utf8_bin NOT NULL,
  `size` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `page_id` (`document_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1562 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Дамп данных таблицы `images`
--

INSERT INTO `images` (`id`, `document_id`, `document_filename`, `filename`, `type`, `size`) VALUES
(1561, 408, 'test7_1512375774.pdf', 'test7_1512375774_07.jpeg', 'jpeg', 1493977),
(1560, 408, 'test7_1512375774.pdf', 'test7_1512375774_06.jpeg', 'jpeg', 1491807),
(1559, 408, 'test7_1512375774.pdf', 'test7_1512375774_05.jpeg', 'jpeg', 1494259),
(1558, 408, 'test7_1512375774.pdf', 'test7_1512375774_04.jpeg', 'jpeg', 1491657),
(1557, 408, 'test7_1512375774.pdf', 'test7_1512375774_03.jpeg', 'jpeg', 1482774),
(1556, 408, 'test7_1512375774.pdf', 'test7_1512375774_02.jpeg', 'jpeg', 1491738),
(1555, 408, 'test7_1512375774.pdf', 'test7_1512375774_01.jpeg', 'jpeg', 1482386);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
