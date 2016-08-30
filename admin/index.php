<?php
/*
* Copyright 2008-2015 Anael Mobilia
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
require '../config/configV2.php';
// Vérification des droits d'accès
metaObject::checkUserAccess(utilisateurObject::levelAdmin);
require _TPL_TOP_;
?>
<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <h1><small>Panneau d'administration</small></h1>
    <ul>
        <?php
        // Liste des fichiers d'administration
        $scan_rep = scandir(_PATH_ADMIN_);
        // Pour chaque image
        foreach ($scan_rep as $item) {
            // On ne rapporte pas les répertoires
            if (!is_dir(_PATH_ADMIN_ . $item)):
                ?>
                <li>
                    <a href="<?= _URL_ADMIN_ ?><?= $item ?>">
                        <?= $item ?>
                    </a>
                </li>
                <?php
            endif;
        }
        ?>
    </ul>
</div>
<?php require _TPL_BOTTOM_ ?>