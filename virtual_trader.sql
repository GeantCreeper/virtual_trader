-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3307
-- Généré le : mar. 15 avr. 2025 à 21:42
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
-- Structure de la table `action`
--

DROP TABLE IF EXISTS `action`;
CREATE TABLE IF NOT EXISTS `action` (
  `action_code` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `price` float NOT NULL,
  `annual_dividend` float NOT NULL,
  `dividend_date` int(11) NOT NULL,
  PRIMARY KEY (`action_code`),
  KEY `fk_wallet` (`wallet_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `action`
--

INSERT INTO `action` (`action_code`, `wallet_id`, `description`, `price`, `annual_dividend`, `dividend_date`) VALUES
(1, NULL, 'Apple', 198.15, 0.05, 12),
(2, NULL, 'Microsoft', 388.45, 0.24, 8),
(3, NULL, 'Nvidia', 110.93, 0.17, 4),
(4, NULL, 'Amazon', 184.87, 0.03, 6),
(5, NULL, 'Google', 159.4, 0.02, 8),
(6, NULL, 'Facebook', 543.57, 0.05, 9),
(7, NULL, 'Tesla', 252.24, 0.15, 10),
(8, NULL, 'Walmart', 92.8, 0.01, 3),
(9, NULL, 'Visa', 333.4, 0.04, 12),
(10, NULL, 'Tencent', 57.68, 0.13, 5);

-- --------------------------------------------------------

--
-- Structure de la table `history`
--

DROP TABLE IF EXISTS `history`;
CREATE TABLE IF NOT EXISTS `history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_code` int(11) NOT NULL,
  `value` float NOT NULL,
  `history_date` date NOT NULL,
  PRIMARY KEY (`history_id`),
  KEY `fk_history_action_code` (`action_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
CREATE TABLE IF NOT EXISTS `transaction` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action_code` int(11) NOT NULL,
  `value` float NOT NULL,
  `quantity` int(11) NOT NULL,
  `transaction_type` varchar(255) NOT NULL,
  `transaction_date` date NOT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `fk_transaction_user_id` (`user_id`),
  KEY `fk_transaction_action_code` (`action_code`)
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `money`) VALUES
(3, 'jean', '$2y$10$pNj51DKnYFrHfznWLDRjlOWWdyRrV56wjOp6psj6Mzl5a0CKAlxlO', 10000),
(5, 'joe baiden', '$2y$10$42xnOEiZqfCfDg1RZbTB4ObtstdT4qT3tmNNQP16kp/fGinYQkTv6', 10000);

-- --------------------------------------------------------

--
-- Structure de la table `wallet`
--

DROP TABLE IF EXISTS `wallet`;
CREATE TABLE IF NOT EXISTS `wallet` (
  `wallet_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `value` float NOT NULL,
  `update_date` date NOT NULL,
  PRIMARY KEY (`wallet_id`),
  KEY `fk_wallet_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `action`
--
ALTER TABLE `action`
  ADD CONSTRAINT `fk_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallet` (`wallet_id`);

--
-- Contraintes pour la table `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `fk_history_action_code` FOREIGN KEY (`action_code`) REFERENCES `action` (`action_code`);

--
-- Contraintes pour la table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `fk_transaction_action_code` FOREIGN KEY (`action_code`) REFERENCES `action` (`action_code`),
  ADD CONSTRAINT `fk_transaction_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Contraintes pour la table `wallet`
--
ALTER TABLE `wallet`
  ADD CONSTRAINT `fk_wallet_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
