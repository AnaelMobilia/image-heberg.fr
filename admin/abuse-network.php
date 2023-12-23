<?php

/*
 * Copyright 2008-2023 Anael MOBILIA
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

if (!defined('_PHPUNIT_')) {
    require '../config/config.php';
}

// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);

$listeIp = HelperAdmin::getBadNetworks();

require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Critères d'image suspecte</small></h1>
    <div class="card">
        <div class="card-header">
            <?= count($listeIp) ?> addresse<?= (count($listeIp) > 1 ? 's' : '') ?> IP
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Réseau</th>
                        <th>Nombre d'images</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <?php foreach ((array)$listeIp as $key => $value) : ?>
                        <tr>
                            <td><?= $key ?></td>
                            <td><?= $value ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php require _TPL_BOTTOM_; ?>