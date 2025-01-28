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

use ArrayObject;

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

$table = [
    'legende' => 'trouvée##',
    'values' => new ArrayObject(),
];
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
} else {
    /**
     * Images à traiter
     */
    $idStart = 0;
    if (!empty($_GET['nextId']) && preg_match('#^[0-9]+$#', $_GET['nextId'])) {
        $idStart = (int)$_GET['nextId'];
    }
    $req = 'SELECT MAX(new_name) as new_name FROM images' . ($idStart !== 0 ? ' WHERE id < ' . $idStart : '') . ' GROUP BY md5 ORDER BY date_action DESC LIMIT 50';
    $table['values'] = HelperAdmin::queryOnNewName($req);
}
$isPlural = false;
if (count($table['values']) > 1) {
    $isPlural = true;
}
$lastId = '';
// Charger les objets concernés
$mesImages = ImageObject::chargerMultiple($table['values'], RessourceObject::SEARCH_BY_NAME, false);
?>
    <?php if (!empty($message)) : ?>
    <div class="alert alert-success">
        <?= $message ?>
    </div>
<?php endif; ?>
    <div class="card">
        <div class="card-header">
            <?= count($table['values']) ?> image<?= ($isPlural ? 's' : '') . ' ' . str_replace('##', ($isPlural ? 's' : ''), $table['legende']) ?>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Actions</th>
                        <th class="text-break">Nom originel</th>
                        <th class="text-break">Date d'envoi</th>
                        <th class="text-break">IP envoi</th>
                        <th class="text-break">Nb vues</th>
                        <th class="text-break">Dernier affichage</th>
                        <th class="text-break">Utilisateur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mesImages as $uneImage) : ?>
                        <tr>
                            <td>
                                <?= (($uneImage->isSuspecte() || $uneImage->isSignalee()) && !$uneImage->isBloquee() && !$uneImage->isApprouvee() ? '<span class="bi-exclamation-square-fill" style="color:red;"></span>' : '') ?>
                                <?php if (!$uneImage->isApprouvee()): ?>
                                    <?php if ($uneImage->getNbViewPerDay() >= _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_): ?>
                                        <span class="bi-exclamation-diamond" style="color: orange"></span>
                                    <?php elseif ($uneImage->getNbViewPerDay() >= _ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_): ?>
                                        <span class="bi-exclamation-circle" style="color: lightblue"></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <a href="<?= $uneImage->getURL(true) ?>?forceDisplay=1" target="_blank" style="<?= ($uneImage->isBloquee() ? 'text-decoration: line-through double red;' : '') ?><?= ($uneImage->isApprouvee() ? 'text-decoration: underline double green;' : '') ?>"><?= $uneImage->getNomNouveau() ?></a></td>
                            <td class="text-nowrap">
                                <a href="<?= _URL_ADMIN_ ?>lastUpload.php?approuver=1&idImage=<?= $uneImage->getId() . ($idStart !== 0 ? '&nextId=' . $idStart : "") ?>" title="Approuver"><span class="bi-hand-thumbs-up-fill" style="color: green"></span></a>
                                <a href="<?= _URL_ADMIN_ ?>lastUpload.php?bloquer=1&idImage=<?= $uneImage->getId() . ($idStart !== 0 ? '&nextId=' . $idStart : "") ?>" title="Bloquer"><span class="bi-hand-thumbs-down-fill" style="color: red"></span></a>
                                <a href="<?= _URL_ ?>delete.php?id=<?= $uneImage->getNomNouveau() ?>&type=<?=RessourceObject::TYPE_IMAGE?>&forceDelete=1" title="Supprimer"><span class="bi-trash-fill" style="color: purple"></span></a>
                            </td>
                            <td class="text-break"><?= $uneImage->getNomOriginalFormate() ?></td>
                            <td class="text-break"><?= $uneImage->getDateEnvoiFormatee() ?></td>
                            <td class="text-break"><?= $uneImage->getIpEnvoi() ?></td>
                            <td><?= $uneImage->getNbViewTotal() ?><small> (<?= $uneImage->getNbViewPerDay() ?>/jour)</small></td>
                            <td class="text-break"><?= $uneImage->getLastViewFormate() ?></td>
                            <td class="text-break"><?= $uneImage->getIdProprietaire() ?></td>
                        </tr>
                        <?php $lastId = $uneImage->getId() ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>
                            <a href="<?= _URL_ADMIN_ ?>lastUpload.php?nextId=<?= $lastId ?>" class="btn btn-primary"><span class="bi-arrow-left"></span> </a>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php require _TPL_BOTTOM_; ?>