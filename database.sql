-- phpMyAdmin SQL Dump
-- version 4.1.9
-- http://www.phpmyadmin.net
--
-- Généré le :  Sam 10 Janvier 2015 à 19:32
-- Version du serveur :  5.1.73-2+squeeze+build1+1-log
-- Version de PHP :  5.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
--

-- --------------------------------------------------------

--
-- Structure de la table `erreurs`
--

CREATE TABLE IF NOT EXISTS `erreurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_erreur` int(11) NOT NULL,
  `date_erreur` datetime DEFAULT NULL,
  `ip` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `hacks`
--

CREATE TABLE IF NOT EXISTS `hacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `count` smallint(6) NOT NULL DEFAULT '0',
  `last_date` datetime DEFAULT NULL,
  `last_ip` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `images`
--

CREATE TABLE IF NOT EXISTS `images` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `ip_envoi` text NOT NULL,
  `date_envoi` datetime NOT NULL,
  `old_name` text NOT NULL,
  `new_name` text NOT NULL,
  `size` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `last_view` date NOT NULL,
  `nb_view_v4` int(11) NOT NULL DEFAULT '0',
  `nb_view_v6` int(11) NOT NULL DEFAULT '0',
  `md5` tinytext,
  `bloque` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `ip2ban`
--

CREATE TABLE IF NOT EXISTS `ip2ban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('ip','user-agent','reverse-ip') NOT NULL,
  `info` text NOT NULL,
  `end_date` date DEFAULT NULL,
  `nb_bans` int(11) NOT NULL DEFAULT '0',
  `last_date` datetime DEFAULT NULL,
  `last_ip` text,
  `commentaire` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `liste_erreurs`
--

CREATE TABLE IF NOT EXISTS `liste_erreurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `type` enum('hack','flood','picture') DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `login`
--

CREATE TABLE IF NOT EXISTS `login` (
  `pk_login` int(11) NOT NULL AUTO_INCREMENT,
  `ip_login` text NOT NULL,
  `date_login` datetime NOT NULL,
  `pk_membres` int(11) NOT NULL,
  PRIMARY KEY (`pk_login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `membres`
--

CREATE TABLE IF NOT EXISTS `membres` (
  `pk_membres` int(11) NOT NULL AUTO_INCREMENT,
  `email` text NOT NULL,
  `login` text NOT NULL,
  `pass` text NOT NULL,
  `date_inscription` date NOT NULL,
  `ip_inscription` text NOT NULL,
  `lvl` text NOT NULL,
  PRIMARY KEY (`pk_membres`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Structure de la table `possede`
--

CREATE TABLE IF NOT EXISTS `possede` (
  `id` int(11) NOT NULL,
  `pk_membres` int(11) NOT NULL,
  PRIMARY KEY (`id`,`pk_membres`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `sondage`
--

CREATE TABLE IF NOT EXISTS `sondage` (
  `index` int(11) NOT NULL AUTO_INCREMENT,
  `actuelSpeed` tinyint(1) DEFAULT NULL,
  `actuelExt` tinyint(1) DEFAULT NULL,
  `actuelDim` tinyint(1) DEFAULT NULL,
  `actuelPoidsweight` tinyint(1) DEFAULT NULL,
  `actuelOptions` tinyint(1) DEFAULT NULL,
  `bmp` tinyint(1) DEFAULT NULL,
  `uploadMultiple` tinyint(1) DEFAULT NULL,
  `resterCo` tinyint(1) DEFAULT NULL,
  `renamePic` tinyint(1) DEFAULT NULL,
  `statsPic` tinyint(1) DEFAULT NULL,
  `thumbsListeImages` tinyint(1) DEFAULT NULL,
  `albums` tinyint(1) DEFAULT NULL,
  `albumShare` tinyint(1) DEFAULT NULL,
  `albumPwd` tinyint(1) DEFAULT NULL,
  `deleteCheckbox` tinyint(1) DEFAULT NULL,
  `divers` text,
  `ip` text NOT NULL,
  `quand` datetime NOT NULL,
  PRIMARY KEY (`index`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Structure de la table `thumbnails`
--

CREATE TABLE IF NOT EXISTS `thumbnails` (
  `id` int(11) NOT NULL,
  `size` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `last_view` date NOT NULL,
  `nb_view_v4` int(11) NOT NULL DEFAULT '0',
  `nb_view_v6` int(11) NOT NULL DEFAULT '0',
  `md5` tinytext NOT NULL,
  `bloque` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
