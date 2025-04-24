-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3307
-- Généré le : jeu. 24 avr. 2025 à 16:16
-- Version du serveur : 10.10.2-MariaDB
-- Version de PHP : 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `virtual_trader`
--

-- --------------------------------------------------------

--
-- Structure de la table `actions`
--

DROP TABLE IF EXISTS `actions`;
CREATE TABLE IF NOT EXISTS `actions` (
  `action_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` float NOT NULL,
  `annual_dividend` float NOT NULL,
  `dividend_date` int(11) NOT NULL,
  PRIMARY KEY (`action_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `actions`
--

INSERT INTO `actions` (`action_id`, `name`, `price`, `annual_dividend`, `dividend_date`) VALUES
(1, 'Apple', 198.15, 0.05, 12),
(2, 'Microsoft', 388.45, 0.24, 8),
(3, 'Nvidia', 110.93, 0.17, 4),
(4, 'Amazon', 184.87, 0.03, 6),
(5, 'Google', 159.4, 0.02, 8),
(6, 'Facebook', 543.57, 0.05, 9),
(7, 'Tesla', 252.24, 0.15, 10),
(8, 'Walmart', 92.8, 0.01, 3),
(9, 'Visa', 333.4, 0.04, 12),
(10, 'Tencent', 57.68, 0.13, 5);

-- --------------------------------------------------------

--
-- Structure de la table `action_history`
--

DROP TABLE IF EXISTS `action_history`;
CREATE TABLE IF NOT EXISTS `action_history` (
  `action_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `price` float NOT NULL,
  PRIMARY KEY (`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `value` float NOT NULL,
  `quantity` int(11) NOT NULL,
  `transaction_type` varchar(255) NOT NULL,
  `transaction_date` date NOT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `user_id` (`user_id`),
  KEY `action_id` (`action_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `action_id`, `value`, `quantity`, `transaction_type`, `transaction_date`) VALUES
(12, 1, 1, 198.15, 4, 'buy', '2025-04-24'),
(13, 1, 6, 543.57, 7, 'buy', '2025-04-24'),
(14, 1, 1, 198.15, 2, 'sell', '2025-04-24'),
(15, 2, 6, 543.57, 15, 'buy', '2025-04-24'),
(16, 1, 2, 388.45, 3, 'buy', '2025-04-24');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `money` float NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `money`) VALUES
(1, 'jean', '$2y$10$LtepU/pIe2isLklJ4daNqu3n0E29uoO07J2xgcOUCh3oVbZnJ17qW', 4633.36),
(2, 'bob', '$2y$10$yK12UFT6SJ3KlSPzs1Wgu.chB3U2IXh7ck7.L9VvjpUTYt.b0zGXG', 1846.45);

-- --------------------------------------------------------

--
-- Structure de la table `wallet`
--

DROP TABLE IF EXISTS `wallet`;
CREATE TABLE IF NOT EXISTS `wallet` (
  `user_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`action_id`),
  KEY `action_id` (`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `wallet`
--

INSERT INTO `wallet` (`user_id`, `action_id`, `quantity`) VALUES
(1, 1, 2),
(1, 2, 3),
(1, 6, 7),
(2, 6, 15);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `action_history`
--
ALTER TABLE `action_history`
  ADD CONSTRAINT `action_history_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `actions` (`action_id`);

--
-- Contraintes pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `actions` (`action_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Contraintes pour la table `wallet`
--
ALTER TABLE `wallet`
  ADD CONSTRAINT `wallet_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `wallet_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `actions` (`action_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
