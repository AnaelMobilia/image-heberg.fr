/*
 * Copyright 2008-2025 Anael MOBILIA
 *
 * This file is part of image-heberg.fr.
 *
 * image-heberg.fr is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * image-heberg.fr is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with image-heberg.fr. If not, see <http://www.gnu.org/licenses/>
 */

--
-- Structure de la table `images`
--

CREATE TABLE IF NOT EXISTS `images`
(
    `id`              int          NOT NULL AUTO_INCREMENT,
    `remote_addr`     varchar(45)  NOT NULL,
    `date_action`     datetime     NOT NULL,
    `old_name`        varchar(255) NOT NULL,
    `new_name`        varchar(30)  NOT NULL,
    `size`            int          UNSIGNED NOT NULL,
    `height`          int          UNSIGNED NOT NULL,
    `width`           int          UNSIGNED NOT NULL,
    `last_view`       date         NOT NULL,
    `nb_view_v4`      int          UNSIGNED NOT NULL,
    `nb_view_v6`      int          UNSIGNED NOT NULL,
    `md5`             varchar(32)  NOT NULL,
    `isBloquee`       tinyint(1)   UNSIGNED NOT NULL,
    `isSignalee`      tinyint(1)   UNSIGNED NOT NULL,
    `isApprouvee`     tinyint(1)   UNSIGNED NOT NULL,
    `abuse_network`   varchar(45)  NOT NULL,
    `abuse_categorie` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `statutImage` (`isBloquee`, `isApprouvee`, `isSignalee`) USING BTREE,
    KEY `abuse_network_bloquee` (`abuse_network`,`isBloquee`) USING BTREE,
    KEY `abuse_network_approuvee` (`abuse_network`,`isApprouvee`),
    KEY `isApprouvee` (`isApprouvee`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `login`
--

CREATE TABLE IF NOT EXISTS `login`
(
    `id`          int         UNSIGNED NOT NULL AUTO_INCREMENT,
    `remote_addr` varchar(45) NOT NULL,
    `date_action` datetime    NOT NULL,
    `membres_id`  int         UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `membres_id` (`membres_id`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `membres`
--

CREATE TABLE IF NOT EXISTS `membres`
(
    `id`          int         UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`       text        NOT NULL,
    `login`       text        NOT NULL,
    `password`    text        NOT NULL,
    `date_action` date        NOT NULL,
    `remote_addr` varchar(45) NOT NULL,
    `lvl`         tinyint(1)  UNSIGNED NOT NULL,
    `isActif`     tinyint(1)  UNSIGNED NOT NULL,
    `token`       text        NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `possede`
--

CREATE TABLE IF NOT EXISTS `possede`
(
    `images_id`  int     UNSIGNED NOT NULL,
    `membres_id` int     UNSIGNED NOT NULL,
    PRIMARY KEY (`images_id`, `membres_id`),
    KEY `membres_id` (`membres_id`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Structure de la table `thumbnails`
--

CREATE TABLE IF NOT EXISTS `thumbnails`
(
    `id`          int         UNSIGNED NOT NULL AUTO_INCREMENT,
    `images_id`   int         UNSIGNED NOT NULL,
    `is_preview`  tinyint(1)  UNSIGNED NOT NULL,
    `date_action` date        NOT NULL,
    `new_name`    varchar(30) NOT NULL,
    `size`        int         UNSIGNED NOT NULL,
    `height`      int         UNSIGNED NOT NULL,
    `width`       int         UNSIGNED NOT NULL,
    `last_view`   date        NOT NULL,
    `nb_view_v4`  int         UNSIGNED NOT NULL,
    `nb_view_v6`  int         UNSIGNED NOT NULL,
    `md5`         varchar(32) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `images_id` (`images_id`) USING BTREE
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;

--
-- Création du compte administrateur
--
INSERT INTO `membres` (`id`, `email`, `login`, `password`, `date_action`, `remote_addr`, `lvl`, `isActif`,
                       `token`)
VALUES (1, 'john.doe@example.com', 'admin', '$2y$10$2mn2aXq7R2ROZhi9R3H1iO95vSXo0Vd02u3vAdAZSkZhcBq4Vd1bu', DATE(NOW()),
        '127.0.0.1', 2, 1, '');

--
-- Images 404 & bannie
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`,
                      `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`)
VALUES (1, '127.0.0.1', '2008-01-01 00:00:00', '_image_404.png', '_image_404.png', 30703, 150, 640, '0000-00-00', 0, 0,
        '6858ce6ddc171a0fd9640831a5e74dfd', 0, 0, 1, '127.0.0'),
       (2, '127.0.0.1', '2008-01-01 00:00:00', '_image_banned.png', '_image_banned.png', 28713, 150, 640, '0000-00-00',
        0, 0, '12c357976276091e7cd42e98debb7fb1', 0, 0, 1, '127.0.0');

--
-- Assignation à l'administrateur
--
INSERT INTO `possede` (`images_id`, `membres_id`)
VALUES ('1', '1'),
       ('2', '1');