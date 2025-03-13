-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Már 13. 14:07
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `robin_szakdoga`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(16) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `role` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `admins`
--

INSERT INTO `admins` (`id`, `name`, `pass`, `role`) VALUES
(1, 'admin1', '*6D45FD76D5E9C6A404E39C25106A7F0', 1),
(2, 'admin2', '*0E6FD44C7B722784DAE6E67EF8C06FB', 2);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `keys`
--

CREATE TABLE `keys` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `RFID` varchar(255) NOT NULL,
  `role` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `reader`
--

CREATE TABLE `reader` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `role` int(11) NOT NULL,
  `from` time NOT NULL,
  `to` time NOT NULL,
  `role_abs` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `read_log`
--

CREATE TABLE `read_log` (
  `id` int(11) NOT NULL,
  `reader_id` int(11) NOT NULL,
  `key_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `keys`
--
ALTER TABLE `keys`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `reader`
--
ALTER TABLE `reader`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `read_log`
--
ALTER TABLE `read_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_reader_id` (`reader_id`),
  ADD KEY `FK_KEY_ID` (`key_id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT a táblához `keys`
--
ALTER TABLE `keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `reader`
--
ALTER TABLE `reader`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `read_log`
--
ALTER TABLE `read_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `read_log`
--
ALTER TABLE `read_log`
  ADD CONSTRAINT `FK_KEY_ID` FOREIGN KEY (`key_id`) REFERENCES `keys` (`id`) ON DELETE NO ACTION,
  ADD CONSTRAINT `FK_reader_id` FOREIGN KEY (`reader_id`) REFERENCES `reader` (`id`) ON DELETE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
