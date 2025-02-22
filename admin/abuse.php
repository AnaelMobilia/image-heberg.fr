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

if (!defined('_PHPUNIT_')) {
    require '../config/config.php';
}

// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);

// Action à effectuer sur une image
if (isset($_GET['idImage']) && preg_match('#^[0-9]+$#', $_GET['idImage'])) {
    $monImage = new ImageObject($_GET['idImage'], RessourceObject::SEARCH_BY_ID);
    if (isset($_GET['action']) && in_array ($_GET['action'], ['approuver', 'bloquer'])) {
        $monImage->{$_GET['action']}();
        die('OK');
    }
    // La suppression n'est pas contaminante par défaut
    if (isset($_GET['action']) && $_GET['action'] === 'supprimer') {
        $listeImages = ImageObject::chargerMultiple([$monImage->getMd5()], RessourceObject::SEARCH_BY_MD5);
        foreach ($listeImages as $image) {
            $image->supprimer();
        }
        die('OK');
    }
}

require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Gestion des abus</small></h1>
    <?php

$tabTables = [];
/**
 * Images à traiter
 */
// Liste des images avec un ratio d'affichage incohérent
$tabTables[] = [
    'legende' => 'affichée## > ' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ . ' fois/jour <small>(blocage automatique à ' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ . '</small>)',
    'values' => HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_)
];
// Liste des images avec un ratio d'affichage incohérent EN PROJECTION
$tabTables[] = [
    'legende' => 'projetée## avec un nombre d\'affichages > ' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ . ' fois/jour <small>(blocage automatique à ' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ . '</small>)',
    'values' => HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_, false, true)
];
// Liste des images suspectes avec un ratio d'affichage incohérent
$tabTables[] = [
    'legende' => '<b>suspecte##</b> affichée## > ' . (_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_) . ' fois/jour <small>(blocage automatique à ' . (_ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_) . '</small>)',
    'values' => HelperAdmin::getImagesTropAffichees((_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_), true)
];
// Liste des images suspectes avec un ratio d'affichage incohérent EN PROJECTION
$tabTables[] = [
    'legende' => '<b>suspecte##</b> projetée## avec un nombre d\'affichages > ' . (_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_) . ' fois/jour <small>(blocage automatique à ' . (_ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_) . '</small>)',
    'values' => HelperAdmin::getImagesTropAffichees((_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_), true, true)
];
// Liste des images signalées
$tabTables[] = [
    'legende' => 'signalée##',
    'values' => HelperAdmin::getImagesSignalees()
];
// Liste des images suspectes
$tabTables[] = [
    'legende' => 'suspecte##',
    'values' => HelperAdmin::getImagesPotentiellementIndesirables()
];
// Liste des images approuvables
$tabTables[] = [
    'legende' => 'approuvable##',
    'values' => HelperAdmin::getImagesPotentiellementApprouvables()
];
// Liste de TOUTES les images avec un ratio d'affichage abusif
$tabTables[] = [
    'legende' => 'affichée## > ' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_ABUSIF_ . ' fois/jour',
    'values' => HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_ABUSIF_, false, false, true)
];
?>
    <?php foreach ($tabTables as $uneTable) : ?>
        <?php $mesImages = ImageObject::chargerMultiple($uneTable['values'], RessourceObject::SEARCH_BY_NAME); ?>
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
                            <tr data-id="<?= $uneImage->getId() ?>" data-md5="<?= $uneImage->getMd5() ?>">
                                <td>
                                    <a href="<?= $uneImage->getURL(true) ?>?forceDisplay=1" target="_blank" style="<?= ($uneImage->isBloquee() ? 'text-decoration: line-through double red;' : '') . ($uneImage->isApprouvee() ? 'text-decoration: underline double green;' : '') ?>">
                                        <img src="<?= $uneImage->getPreviewMiniature()->getURL(true) ?>?forceDisplay=1" style="max-width: <?= (_SIZE_PREVIEW_ / 2) ?>px; max-height: <?= (_SIZE_PREVIEW_ / 2) ?>px" loading="lazy">
                                        <br />
                                        <?= $uneImage->getNomNouveau() ?>
                                    </a>
                                </td>
                                <td class="text-nowrap">
                                    <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'approuver');" title="Approuver"><span class="bi-hand-thumbs-up-fill text-success"></span></button>
                                    <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'bloquer');" title="Bloquer"><span class="bi-hand-thumbs-down-fill text-danger"></span></button>
                                    <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'supprimer');" title="Supprimer"><span class="bi-trash-fill" style="color: purple"></span></button>
                                </td>
                                <td class="text-break"><?= $uneImage->getNomOriginalFormate() ?></td>
                                <td class="text-break"><?= $uneImage->getDateEnvoiFormatee() ?></td>
                                <td class="text-break"><?= $uneImage->getIpEnvoi() ?></td>
                                <td class="text-break"><?= $uneImage->getNbViewTotal() ?><small> (<?= $uneImage->getNbViewPerDay() ?>/jour)</small></td>
                                <td class="text-break"><?= $uneImage->getLastViewFormate() ?></td>
                                <td class="text-break"><?= $uneImage->getIdProprietaire() ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <br>
    <?php endforeach; ?>
    <script>
    /**
     * Gestion des actions sur les images
     * @param idImage ID de l'image
     * @param md5 MD5 de l'image
     * @param action Action à réaliser
     */
    function runAction(idImage, md5, action) {
        if (confirm(action.substring(0, 1).toUpperCase() + action.substring(1) + ' cette image ?')) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '<?= _URL_ADMIN_ . basename(__FILE__) ?>?action=' + action + '&idImage=' + idImage);
            xhr.onload = function () {
                if (xhr.status === 200 && xhr.responseText === 'OK') {
                    // En cas de succès, supprimer les lignes correspondantes
                    const images = document.querySelectorAll('tr[data-id="' + idImage + '"], tr[data-md5="' + md5 + '"]');
                    images.forEach(function (ligne) {
                        ligne.remove();
                    });
                }
            };
            xhr.onerror = function () {
                alert('Une erreur a été rencontrée lors de l\'action ' + action + ' sur l\'image ' + idImage + ' : ' + xhr.response);
            };
            xhr.send();
        }
    }
    </script>
    <?php require _TPL_BOTTOM_; ?>