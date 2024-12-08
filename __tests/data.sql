/*
 * Copyright 2008-2024 Anael MOBILIA
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
-- Image bloquée
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
(10, '127.0.0.1', '2008-01-01 00:00:00', 'image_a_supprimer.png', 'image_10.png', 10, 10, 10, '0000-00-00', 0, 0, 'to-be-calculatedto-be-calculated', 1, 0, 0, '127.0.2');

--
-- Images à supprimer
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
(11, '127.0.0.1', '2008-01-01 00:00:00', 'image_a_supprimer.png', 'image_11.png', 4239, 400, 640, '0000-00-00', 0, 0, 'to-be-calculatedto-be-calculated', 0, 0, 0, '127.0.0'),
(12, '127.0.0.10', NOW(), 'image_portrait_600x800.png', 'image_12.png', 4239, 400, 640, '0000-00-00', 0, 0, 'to-be-calculatedto-be-calculated', 0, 0, 0, '127.0.0'),
(13, '127.0.0.10', NOW(), 'imageBleue10.png', 'image_13.png', 4239, 400, 640, '0000-00-00', 0, 0, 'to-be-calculatedto-be-calculated', 0, 0, 0, '127.0.0'),
(14, '127.0.0.1', '2016-01-01 00:00:00', 'image_a_supprimerMultiple.png', 'image_14.png', 4239, 400, 640, '0000-00-00', 0, 0, 'to-be-calculatedto-be-calculated', 0, 0, 0, '127.0.0'),
(15, '127.0.0.1', '2016-01-01 00:00:00', 'imageQuiSeraBloquee.png', 'image_15.png', 4239, 400, 640, '0000-00-00', 0, 0, '97a3a88502d6-theSameMd5-97a3a88502d6', 0, 0, 0, '127.0.0'),
(16, '127.0.0.1', '2016-01-01 00:00:00', 'imageAvecMemeMd5QuiDoitEtreBloquee.png', 'image_16.png', 4239, 400, 640, '0000-00-00', 0, 0, '97a3a88502d6-theSameMd5-97a3a88502d6', 0, 0, 0, '127.0.0'),
(17, '127.0.0.1', '2023-01-01 00:00:00', 'imagePeuAfficheeMaisMignatureBeaucoupAffichee.png', 'image_17.png', 4239, 400, 640, '0000-00-00', 1000, 1000, 'not-used--be5e3e8d65ecefdc0dbcca', 0, 0, 0, '127.0.0');

--
-- Image signalée
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
(18, '127.0.0.1', '2008-01-01 00:00:00', 'test.webp', 'image_18.png', 4239, 400, 640, '0000-00-00', 0, 0, 'to-be-calculatedto-be-calculated', 0, 1, 0, '127.0.1');

--
-- Image bloquée
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
(19, '10.10.10.10', '2016-01-01 00:00:00', 'imageDejaBloquee.gif', 'image_19.gif', 146, 25, 37, '0000-00-00', 0, 0, 'to-be-calculatedto-be-calculated', 1, 0, 0, '10.10.10');

--
-- Image du même réseau que celle bloquée
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
(20, '10.10.10.200', '2016-01-01 00:00:00', 'imageMemeReseauQueDejaBloquee.gif', 'image_20.gif', 146, 25, 37, '0000-00-00', 751, 0, 'not-used--dea392173d746c107beda4', 0, 0, 0, '10.10.10');

--
-- Réputation des réseaux
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
    (21, '192.168.0.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_21.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'not-used--ab4d682db12defa28f09a6', 1, 0, 0, '192.168.0'),
    (22, '192.168.0.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_22.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'not-used--009a3908f941f94f9d9d5a', 1, 0, 0, '192.168.0'),
    (23, '192.168.0.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_23.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'not-used--17dc9dab5ec4aaf13cb5d8', 1, 0, 0, '192.168.0'),
    (24, '192.168.0.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_24.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'not-used--1a18d35e0fed1f2963ac77', 1, 0, 0, '192.168.0'),
    (25, '192.168.0.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_25.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'not-used--4dd6365d4aea4e3bde009f', 1, 0, 0, '192.168.0'),
    (26, '192.168.100.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_26.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'not-used--48c026fca13b23c786ed87', 1, 0, 0, '192.168.100');

--
-- Image qui sera trop affichée EN PROJECTION
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
(27, '127.0.3.1', (NOW() - INTERVAL 2 HOUR), 'image_trop_affichee_en_projection.png', 'image_27.png', 1, 1, 1, NOW(), 5000, 5000, 'not-used--ab48e8f727e3329aaa6cf4', 0, 0, 0, '127.0.3');

--
-- Images qui seront bloquées
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
    (28, '127.0.1.1', NOW(), 'image_a_bloquer_en_prog.png', 'image_28.png', 1, 1, 1, NOW(), 50, 50, 'ab5fe1f77dfb-theSameMd5-ab5fe1f77dfb', 0, 0, 0, '127.0.1'),
    (29, '127.0.1.1', NOW(), 'image_qui_sera_aussi_bloquee_car_md5_identique.png', 'image_29.png', 1, 1, 1, NOW(), 50, 50, 'ab5fe1f77dfb-theSameMd5-ab5fe1f77dfb', 0, 0, 1, '127.0.1');

--
-- Images qui seront validées
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
    (30, '127.0.0.1', NOW(), 'image_a_valider_prog.png', 'image_30.png', 1, 1, 1, NOW(), 50, 50, 'f3a7c514d2-theSameMd5-f3a7c514d2', 0, 0, 0, '127.0.0'),
    (31, '127.0.0.1', NOW(), 'image_a_valider_prog.png', 'image_31.png', 1, 1, 1, NOW(), 50, 50, 'f3a7c514d2-theSameMd5-f3a7c514d2', 0, 1, 0, '127.0.0'),
    (32, '127.0.0.1', NOW(), 'image_a_valider_prog.png', 'image_32.png', 1, 1, 1, NOW(), 50, 50, 'f3a7c514d2-theSameMd5-f3a7c514d2', 0, 0, 1, '127.0.0');

--
-- Image qui sera bloqué lors de son affichage avec un User-Agent malveillant
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
    (33, '127.0.4.1', NOW(), 'imageQuiSeraSignaleeParUserAgent.png', 'image_33.png', 1, 1, 1, '0000-00-00', 0, 0, 'not-used--37ebcdf9e7d02e37789ed5', 0, 0, 0, '127.0.4');


--
-- Agrandir la taille du champ pour bien gérer le _bootstrap
--
ALTER TABLE `thumbnails` MODIFY `new_name` VARCHAR(50) ;
--
-- Miniatures à supprimer
--
INSERT INTO `thumbnails` (`id`, `images_id`, `date_action`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`) VALUES
(1, 14, '2016-01-01', 'image_a_supprimerMultiple-100x100.png', 10316, 100, 100, '2016-01-01', 19, 0, 'to-be-calculatedto-be-calculated'),
(2, 14, '2016-01-01', 'image_a_supprimerMultiple-200x200.png', 10316, 200, 200, '2016-01-01', 19, 0, 'to-be-calculatedto-be-calculated');
--
-- Miniature beaucoup affichée
--
INSERT INTO `thumbnails` (`id`, `images_id`, `date_action`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`) VALUES
(3, 20, '2023-01-01', '14777777.png', 10316, 100, 100, '2023-01-01', 999999999999, 999999999999, 'not-used--f12d4a42776aba3a16761e');


--
-- Possessions
--
INSERT INTO `possede` (`images_id`, `membres_id`) VALUES ('11', '2'),
('14', '1');


--
-- Second compte utilisateur
--
INSERT INTO `membres` (`id`, `email`, `login`, `password`, `date_action`, `remote_addr`, `lvl`, `token`) VALUES
(2, 'john.doe2@example.com', 'user', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', DATE(NOW()), '127.0.0.1', 1, '');