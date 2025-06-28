-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 28. Jun 2025 um 17:22
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `webseite_db`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `benutzer`
--

CREATE TABLE `benutzer` (
  `id` int(11) NOT NULL,
  `benutzername` varchar(72) NOT NULL,
  `email` varchar(72) NOT NULL,
  `passwort_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `benutzer`
--

INSERT INTO `benutzer` (`id`, `benutzername`, `email`, `passwort_hash`) VALUES
(1, 'Test', 'test@test.com', '$2y$10$nMB09uIaGvNXG1JngCmjA.A5hbUUVk00hqJTeV4NWXx0KwQVrULh2'),
(2, 'TestUser', 'test1@test.com', '$2y$10$H3zo5v3VD6SjPXJR3LNpW.NJTWHTp817js8WtZijXYJBM3gHHXNry');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `termine`
--

CREATE TABLE `termine` (
  `id` int(11) NOT NULL,
  `benutzer_id` int(11) NOT NULL,
  `titel` varchar(255) NOT NULL,
  `beschreibung` text DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT 0,
  `ort` varchar(255) DEFAULT NULL,
  `farbe` varchar(7) DEFAULT NULL,
  `start_time` bigint(20) NOT NULL,
  `end_time` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `termine`
--

INSERT INTO `termine` (`id`, `benutzer_id`, `titel`, `beschreibung`, `all_day`, `ort`, `farbe`, `start_time`, `end_time`) VALUES
(2, 1, 'Test2', NULL, 1, NULL, NULL, 1750888800, NULL),
(3, 1, 'Test3', NULL, 1, NULL, NULL, 1751061600, NULL),
(4, 1, 'Test4', NULL, 1, NULL, NULL, 1743116400, NULL),
(5, 1, 'Test5', NULL, 1, NULL, NULL, 1753999200, NULL),
(6, 2, 'TestUser1', NULL, 1, NULL, NULL, 1750888800, NULL);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `benutzer`
--
ALTER TABLE `benutzer`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `termine`
--
ALTER TABLE `termine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`benutzer_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `benutzer`
--
ALTER TABLE `benutzer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `termine`
--
ALTER TABLE `termine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `termine`
--
ALTER TABLE `termine`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`benutzer_id`) REFERENCES `benutzer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
