/*
 * Copyright 2008-2016 Anael Mobilia
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
INSERT INTO `images` (`id`, `ip_envoi`, `date_envoi`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `bloque`) VALUES
(10, '127.0.0.1', '2008-01-01 00:00:00', 'imageBloquee.jpg', 'imageBloquee.jpg', 10, 10, 10, '0000-00-00', 0, 0, '6858ce6ddc171a0fd9640831a5e74dfd', 1);

--
-- Images à supprimer
--
INSERT INTO `images` (`id`, `ip_envoi`, `date_envoi`, `old_name`, `new_name`, `size`, `height`, `width`, `last_view`, `nb_view_v4`, `nb_view_v6`, `md5`, `bloque`) VALUES
(11, '127.0.0.1', '2008-01-01 00:00:00', 'image_a_supprimer.png', '100000019001334055750.png', 4239, 400, 640, '0000-00-00', 0, 0, 'e656d1b6582a15f0f458006898b40e29', 0),
(12, '127.0.0.10', NOW(), 'image_a_supprimer.png', '147834019001334055750.png', 4239, 400, 640, '0000-00-00', 0, 0, 'e656d1b6582a15f0f458006898b40e29', 0),
(13, '127.0.0.1', '20016-01-01 00:00:00', 'image.png', '146734019451334055750.png', 4239, 400, 640, '0000-00-00', 0, 0, 'a876d1b6582a15f0f458006898b40e29', 0);

--
-- Possessions
--
INSERT INTO `possede` (`image_id`, `pk_membres`) VALUES ('12', '2');


--
-- Second compte utilisateur
--
INSERT INTO `membres` (`id`, `email`, `login`, `pass`, `date_inscription`, `ip_inscription`, `lvl`) VALUES
(2, 'john.doe2@example.com', 'user', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', DATE(NOW()), '127.0.0.1', 'user');