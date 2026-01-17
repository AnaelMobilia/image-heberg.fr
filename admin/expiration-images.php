<?php

/*
 * Copyright 2008-2026 Anael MOBILIA
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

use DateTime;

require '../config/config.php';

// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);
require _TPL_TOP_;

// Espace disque libéré dans les x prochains jours
// Les images
$tabImages = [];
$tabImageIdByMd5 = [];
$req = 'SELECT id, size, md5, isBloquee, last_view, date_action FROM images';
// Exécution de la requête
$resultat = MaBDD::getInstance()->query($req);
foreach ($resultat->fetchAll() as $value) {
    $tabImageIdByMd5[$value->id] = $value->md5;
    // Grouper par MD5
    if (!isset($tabImages[$value->md5])) {
        // Initialiser l'objet
        $tabImages[$value->md5] = [
                'size'        => $value->size,
                'bloquee'     => false,
                'possedee'    => false,
                'date_action' => $value->date_action,
                'last_view'   => $value->last_view,
        ];
    }
    // Mise à jour des infos de l'image (plusieurs images avec le même MD5)
    if ($value->isBloquee === 1) {
        $tabImages[$value->md5]['bloquee'] = true;
    }
    if ($value->last_view > $tabImages[$value->md5]['last_view']) {
        $tabImages[$value->md5]['last_view'] = $value->last_view;
    }
    if ($value->date_action > $tabImages[$value->md5]['date_action']) {
        $tabImages[$value->md5]['date_action'] = $value->date_action;
    }
}
// Les miniatures
$req = 'SELECT images_id, size, MAX(last_view) as last_view, MAX(date_action) as date_action
    FROM thumbnails
    GROUP BY md5, images_id, size';
// Exécution de la requête
$resultat = MaBDD::getInstance()->query($req);
foreach ($resultat->fetchAll() as $value) {
    // Incrémenter la taille totale associée à l'image
    $tabImages[$tabImageIdByMd5[$value->images_id]]['size'] += $value->size;
    // Mise à jour des infos de l'image
    if ($value->last_view > $tabImages[$tabImageIdByMd5[$value->images_id]]['last_view']) {
        $tabImages[$tabImageIdByMd5[$value->images_id]]['last_view'] = $value->last_view;
    }
    if ($value->date_action > $tabImages[$tabImageIdByMd5[$value->images_id]]['date_action']) {
        $tabImages[$tabImageIdByMd5[$value->images_id]]['date_action'] = $value->date_action;
    }
}
$req = 'SELECT images_id FROM possede';
// Exécution de la requête
$resultat = MaBDD::getInstance()->query($req);
foreach ($resultat->fetchAll() as $value) {
    // Mise à jour des infos de l'image
    if (!$tabImages[$tabImageIdByMd5[$value->images_id]]['possedee']) {
        $tabImages[$tabImageIdByMd5[$value->images_id]]['possedee'] = true;
    }
}
//var_dump($tabImages);
// Calcul des dates d'expiration
$tabInfos = [];
foreach ($tabImages as $md5 => $tabImage) {
    $dateAction = '0000-00-00';
    if ($tabImage['possedee']) {
        // Image possédée -> date forcée
        $dateAction = '2036-12-31';
    } else {
        if ($tabImage['last_view'] !== '0000-00-00') {
            // Image déjà affichée
            $date = new DateTime($tabImage['last_view']);
            $date->modify('+' . _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ . ' days');
        } else {
            // Image jamais affichée
            $date = new DateTime($tabImage['date_action']);
            $date->modify('+' . _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ . ' days');
        }
        $dateAction = $date->format('Y-m-d');
    }
    // Mettre à jour le tableau
    if (!isset($tabInfos[$dateAction])) {
        $tabInfos[$dateAction] = 0;
    }
    $tabInfos[$dateAction] += $tabImage['size'];
}
ksort($tabInfos);
$totalSize = 0;
?>
    <h1 class="mb-3"><small>Expiration des images dans le temps</small></h1>
    <div class="card">
        <div class="card-header">
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <td>Date</td>
                        <td>Volumétrie</td>
                        <td>Volumétrie cumulée</td>
                    </tr>
                </thead>
                <?php foreach ($tabInfos as $date => $size): ?>
                    <tr>
                        <td><?= $date ?></td>
                        <td><?= number_format($size / 1024 / 1024, 1, ',', ' ') ?> Mo</td>
                        <?php
                        $totalSize += $size;
                        ?>
                        <td><?= number_format($totalSize / 1024 / 1024 / 1024, 1, ',', ' ') ?> Go</td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <?php require _TPL_BOTTOM_; ?>