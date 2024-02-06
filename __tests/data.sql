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
(10, '127.0.0.1', '2008-01-01 00:00:00', 'imageBloquee.jpg', 'image_10.jpg', 10, 10, 10, '0000-00-00', 0, 0, '6858ce6ddc171a0fd9640831a5e74dfd', 1, 0, 0, '127.0.0.0');

--
-- Images à supprimer
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
(11, '127.0.0.1', '2008-01-01 00:00:00', 'image_a_supprimer.png', 'image_11.png', 4239, 400, 640, '0000-00-00', 0, 0, 'e656d1b6582a15f0f458006898b40e29', 0, 0, 0, '127.0.0.0'),
(12, '127.0.0.10', NOW(), 'image_a_supprimer.png', 'image_12.png', 4239, 400, 640, '0000-00-00', 0, 0, 'e656d1b6582a15f0f458006898b40e29', 0, 0, 0, '127.0.0.0'),
(13, '127.0.0.1', '2016-01-01 00:00:00', 'image.png', 'image_13.png', 4239, 400, 640, '0000-00-00', 0, 0, 'a876d1b6582a15f0f458006898b40e29', 0, 0, 0, '127.0.0.0'),
(14, '127.0.0.1', '2016-01-01 00:00:00', 'image_a_supprimerMultiple.png', 'image_14.png', 4239, 400, 640, '0000-00-00', 0, 0, 'aec65c6b4469bb7267d2d55af5fbd87b', 0, 0, 0, '127.0.0.0'),
(15, '127.0.0.1', '2016-01-01 00:00:00', 'imageQuiSeraBloquee.png', 'image_15.png', 4239, 400, 640, '0000-00-00', 0, 0, 'bec65c6b4469bb7267d2d55af5fbd87b', 0, 0, 0, '127.0.0.0'),
(16, '127.0.0.1', '2016-01-01 00:00:00', 'imageAvecMemeMd5QuiDoitEtreBloquee.png', 'image_16.png', 4239, 400, 640, '0000-00-00', 0, 0, 'bec65c6b4469bb7267d2d55af5fbd87b', 0, 0, 0, '127.0.0.0'),
(17, '127.0.0.1', '2023-01-01 00:00:00', 'imagePeuAfficheeMaisMignatureBeaucoupAffichee.png', 'image_17.png', 4239, 400, 640, '0000-00-00', 1000, 1000, 'bec65c6b4469bb7267d2d55af5fbd87b', 0, 0, 0, '127.0.0.0');

--
-- Image signalée
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
(18, '127.0.0.1', '2008-01-01 00:00:00', 'imageSignalee.png', 'image_18.png', 4239, 400, 640, '0000-00-00', 0, 0, 'd456d1b6582a15f0f458006898b40e29', 0, 1, 0, '127.0.1.0');

--
-- Image bloquée
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
(19, '10.10.10.10', '2016-01-01 00:00:00', 'imageDejaBloquee.gif', 'image_19.gif', 146, 25, 37, '0000-00-00', 0, 0, 'f7a498af28acb8a3bbc20ddc95da4c2a', 1, 0, 0, '10.10.10.0');

--
-- Image approuvée (même réseau que celle bloquée)
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
(20, '10.10.10.200', '2016-01-01 00:00:00', 'imageApprouveeMemeReseauQueDejaBloquee.gif', 'image_20.gif', 146, 25, 37, '0000-00-00', 751, 0, 'f7a498af28acb8a3bbc20ddc95da4c2a', 0, 0, 1, '10.10.10.0');

--
-- Réputation des réseaux
--
INSERT INTO `images` (`id`, `remote_addr`, `date_action`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `isBloquee`, `isSignalee`, `isApprouvee`, `abuse_network`) VALUES
    (21, '192.168.0.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_21.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'n0gi96gc0kgpnc43wbfu58y6l0evfj6q', 1, 0, 0, '192.168.0.0'),
    (22, '192.168.0.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_22.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'n0gi96gc0kgpnc43wbfu58y6l0evfj6q', 1, 0, 0, '192.168.0.0'),
    (23, '192.168.0.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_23.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'n0gi96gc0kgpnc43wbfu58y6l0evfj6q', 1, 0, 0, '192.168.0.0'),
    (24, '192.168.0.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_24.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'n0gi96gc0kgpnc43wbfu58y6l0evfj6q', 1, 0, 0, '192.168.0.0'),
    (25, '192.168.0.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_25.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'n0gi96gc0kgpnc43wbfu58y6l0evfj6q', 1, 0, 0, '192.168.0.0'),
    (26, '192.168.100.1', '2024-01-01 00:00:00', 'Capture.jpg', 'image_26.jpg', 1, 1, 1, '0000-00-00', 0, 0, 'n0gi96gc0kgpnc43wbfu58y6l0evfj6q', 1, 0, 0, '192.168.100.0');


--
-- Miniatures à supprimer
--
INSERT INTO `thumbnails` (`id`, `images_id`, `date_action`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`) VALUES
(1, 14, '2016-01-01', '14777777.png', 10316, 100, 100, '2016-01-01', 19, 0, '031328c1a7ffe7eed0a2cab4eca05a63'),
(2, 14, '2016-01-01', '147777772.png', 10316, 200, 200, '2016-01-01', 19, 0, '278a70a02e036cc85e0d7e605fdc517f');
--
-- Miniature beaucoup affichée
--
INSERT INTO `thumbnails` (`id`, `images_id`, `date_action`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`) VALUES
(3, 20, '2023-01-01', '14777777.png', 10316, 100, 100, '2023-01-01', 999999999999, 999999999999, 'a31328c1a7ffe7eed0a2cab4eca05a63');


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