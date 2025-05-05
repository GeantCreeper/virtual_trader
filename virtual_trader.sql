-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3307
-- Généré le : lun. 05 mai 2025 à 20:11
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
(1, 'Apple', 134, 0.5, 12),
(2, 'Microsoft', 389, 0.24, 8),
(3, 'Nvidia', 67, 1.7, 4),
(4, 'Amazon', 117, 0.3, 6),
(5, 'Google', 72, 0.2, 8),
(6, 'Facebook', 786, 0.5, 9),
(7, 'Tesla', 225, 1.5, 10),
(8, 'Walmart', 13, 1, 3),
(9, 'Visa', 438, 0.4, 12),
(10, 'Tencent', 1, 1.3, 5);

-- --------------------------------------------------------

--
-- Structure de la table `action_history`
--

DROP TABLE IF EXISTS `action_history`;
CREATE TABLE IF NOT EXISTS `action_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) NOT NULL,
  `price` float NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `action_id` (`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `game_state`
--

DROP TABLE IF EXISTS `game_state`;
CREATE TABLE IF NOT EXISTS `game_state` (
  `id` int(11) NOT NULL,
  `actual_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `game_state`
--

INSERT INTO `game_state` (`id`, `actual_date`) VALUES
(1, '2025-05-05');

-- --------------------------------------------------------

--
-- Structure de la table `portfolio_history`
--

DROP TABLE IF EXISTS `portfolio_history`;
CREATE TABLE IF NOT EXISTS `portfolio_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `value` float NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_date` (`user_id`,`date`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `action_history`
--
ALTER TABLE `action_history`
  ADD CONSTRAINT `action_history_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `actions` (`action_id`);

--
-- Contraintes pour la table `portfolio_history`
--
ALTER TABLE `portfolio_history`
  ADD CONSTRAINT `portfolio_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

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
