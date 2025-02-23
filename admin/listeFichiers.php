<?php

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

namespace ImageHeberg;

require '../config/config.php';
// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);
require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Images présentes sur le disque</small></h1>
    <ul>
        <?php

        /**
         * Scan récursif
         * @param string $path
         * @return string
         */
        function getScandirRecursif(string $path): string
        {
            $monRetour = '<ul>';

            // Scanne le répertoire fourni
            $scan_rep = scandir($path);
            // Pour chaque item
            foreach ($scan_rep as $item) {
                if ($item !== '.' && $item !== '..') {
                    $monRetour .= '<li>' . $path . $item . '</li>';
                    if (is_dir($path . $item)) {
                        // Appel récursif
                        $monRetour .= getScandirRecursif($path . $item . '/');
                    }
                }
            }
            $monRetour .= '</ul>';
            return $monRetour;
        }

        echo getScandirRecursif(_PATH_IMAGES_);
        ?>
    </ul>
    <?php require _TPL_BOTTOM_ ?>