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
require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Gestion des abus</small></h1>
<?php

$message = '';

// Action à effectuer sur une image
if (isset($_GET['idImage']) && is_numeric($_GET['idImage'])) {
    $monImage = new ImageObject($_GET['idImage'], RessourceObject::SEARCH_BY_ID);
    if (isset($_GET['bloquer'])) {
        // Blocage de l'image
        $monImage->bloquer();
        $message .= 'Image ' . $monImage->getNomNouveau() . ' bloquée !';
    } elseif (isset($_GET['approuver'])) {
        // Approbation de l'image
        $monImage->approuver();
        $message .= 'Image ' . $monImage->getNomNouveau() . ' approuvée !';
    }
}

$tabTables = [];
// Liste des images avec un ratio d'affichage incohérent
$tabTables[] = [
    'legende' => 'affichée## > ' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ . ' fois/jour <small>(blocage automatique à ' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ . '</small>)',
    'values' => HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_)
];
// Liste des images signalées
$tabTables[] = [
    'legende' => 'signalée##',
    'values' => HelperAdmin::getImagesSignalees()
];
// Liste des images bloquées
$tabTables[] = [
    'legende' => 'bloquée##',
    'values' => HelperAdmin::getImagesBloquees()
];

if (!empty($message)) : ?>
    <div class="alert alert-success">
        <?= $message ?>
    </div>
<?php endif; ?>
    <?php foreach ($tabTables as $uneTable) : ?>
    <div class="card">
        <div class="card-header">
            <?= count($uneTable['values']) ?> image<?= (count($uneTable['values']) > 1 ? 's' : '') . ' ' . str_replace('##', (count($uneTable['values']) > 1 ? 's' : ''), $uneTable['legende']) ?>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Actions</th>
                        <th>Nom originel</th>
                        <th>Date d'envoi</th>
                        <th>IP envoi</th>
                        <th>Nb vues</th>
                        <th>Dernier affichage</th>
                        <th>Utilisateur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ((array)$uneTable['values'] as $value) : ?>
                        <?php
                        $uneImage = new ImageObject($value); ?>
                        <tr>
                            <td><a href="<?= $uneImage->getURL() ?>?forceDisplay=1" target="_blank"><?= $uneImage->getNomNouveau() ?></a></td>
                            <td>
                                <a href="<?= _URL_ADMIN_ ?>abuse.php?approuver=1&idImage=<?= $uneImage->getId() ?>" title="Approuver"><span class="bi-hand-thumbs-up-fill" style="color: green"></span></a>
                                <a href="<?= _URL_ADMIN_ ?>abuse.php?bloquer=1&idImage=<?= $uneImage->getId() ?>" title="Bloquer"><span class="bi-hand-thumbs-down-fill" style="color: red"></span></a>
                            </td>
                            <td><?= $uneImage->getNomOriginalFormate() ?></td>
                            <td><?= $uneImage->getDateEnvoiFormatee() ?></td>
                            <td><?= $uneImage->getIpEnvoi() ?></td>
                            <td><?= $uneImage->getNbViewTotal() ?><small> (<?= $uneImage->getNbViewPerDay() ?>/jour)</small></td>
                            <td><?= $uneImage->getLastViewFormate() ?></td>
                            <td><?= $uneImage->getIdProprietaire() ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>
    <?php require _TPL_BOTTOM_; ?>