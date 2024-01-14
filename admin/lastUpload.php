<?php

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

namespace ImageHeberg;

if (!defined('_PHPUNIT_')) {
    require '../config/config.php';
}

// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);
require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Derniers fichiers envoyés</small></h1>
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

/**
 * Images à traiter
 */
$idStart = 0;
if (!empty($_GET['nextId']) && is_numeric($_GET['nextId'])) {
    $idStart = trim(str_replace('\'', '_', $_GET['nextId']));
}
$req = 'SELECT new_name FROM images' . ($idStart !== 0 ? ' WHERE id < ' . $idStart : '') . ' ORDER BY id DESC LIMIT 50';
$table = [
    'legende' => 'trouvée##',
    'values' => HelperAdmin::queryOnNewName($req)
];
?>
    <?php if (!empty($message)) : ?>
    <div class="alert alert-success">
        <?= $message ?>
    </div>
<?php endif; ?>
    <div class="card">
        <div class="card-header">
            <?= count($table['values']) ?> image<?= (count($table['values']) > 1 ? 's' : '') . ' ' . str_replace('##', (count($table['values']) > 1 ? 's' : ''), $table['legende']) ?>
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
                    foreach ((array)$table['values'] as $value) : ?>
                        <?php
                        $uneImage = new ImageObject($value); ?>
                        <tr>
                            <td><a href="<?= $uneImage->getURL(true) ?>?forceDisplay=1" target="_blank" style="<?= ($uneImage->isBloquee() ? 'text-decoration: line-through double red;' : '') . ($uneImage->isApprouvee() ? 'text-decoration: underline double green;' : '') ?>"><?= $uneImage->getNomNouveau() ?></a></td>
                            <td>
                                <a href="<?= _URL_ADMIN_ ?>lastUpload.php?approuver=1&idImage=<?= $uneImage->getId() ?>" title="Approuver"><span class="bi-hand-thumbs-up-fill" style="color: green"></span></a>
                                <a href="<?= _URL_ADMIN_ ?>lastUpload.php?bloquer=1&idImage=<?= $uneImage->getId() ?>" title="Bloquer"><span class="bi-hand-thumbs-down-fill" style="color: red"></span></a>
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
                <tfoot>
                    <tr>
                        <th>
                            <a href="<?= _URL_ADMIN_ ?>lastUpload.php?nextId=<?= $uneImage->getId() ?>" class="btn btn-primary"><span class="bi-arrow-left"></span> </a>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php require _TPL_BOTTOM_; ?>