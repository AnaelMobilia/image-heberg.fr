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

if (!defined('_PHPUNIT_')) {
    require '../config/config.php';
}

// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);

// Action à effectuer sur une image
if (isset($_GET['idImage']) && preg_match('#^[0-9]+$#', $_GET['idImage'])) {
    $monImage = new ImageObject($_GET['idImage'], RessourceObject::SEARCH_BY_ID);
    if (isset($_GET['action']) && in_array($_GET['action'], [RessourceObject::ACTION_APPROUVER, RessourceObject::ACTION_BLOQUER], true)) {
        $monImage->{$_GET['action']}();
        die('OK');
    }
    // La suppression n'est pas contaminante par défaut
    if (isset($_GET['action']) && $_GET['action'] === RessourceObject::ACTION_SUPPRIMER) {
        $listeImages = ImageObject::chargerMultiple([$monImage->getMd5()], RessourceObject::SEARCH_BY_MD5);
        foreach ($listeImages as $image) {
            $image->supprimer();
        }
        die('OK');
    }
}

require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Derniers fichiers envoyés</small></h1>
    <?php

$message = '';

$table = [];
/**
 * Images à traiter
 */
$idStart = 0;
if (!empty($_GET['lastId']) && preg_match('#^[0-9]+$#', $_GET['lastId'])) {
    $idStart = (int)$_GET['lastId'];
}
$req = 'SELECT MAX(new_name) as new_name FROM images' . ($idStart !== 0 ? ' WHERE id < ' . $idStart : '') . ' GROUP BY md5 ORDER BY date_action DESC LIMIT ' . _PAGINATION_IMAGES_;
$table['legende'] = 'trouvée##';
$table['values'] = HelperAdmin::queryOnNewName($req);

$isPlural = (count($table['values']) > 1 ? 's' : '');
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
            <?= count($table['values']) ?> image<?= $isPlural . ' ' . str_replace('##', $isPlural, $table['legende']) ?>
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
                                <?= (($uneImage->isSuspecte() || $uneImage->isSignalee()) && !$uneImage->isBloquee() && !$uneImage->isApprouvee() ? '<span class="bi-exclamation-square-fill" style="color:red;"></span>' : '') ?>
                                <?php if (!$uneImage->isApprouvee()): ?>
                                    <?php if ($uneImage->getNbViewPerDay() >= _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_): ?>
                                        <span class="bi-exclamation-diamond" style="color: orange"></span>
                                    <?php elseif ($uneImage->getNbViewPerDay() >= _ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_): ?>
                                        <span class="bi-exclamation-circle" style="color: lightblue"></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <a href="<?= $uneImage->getURL(true) ?>?forceDisplay=1" target="_blank" class="<?= $uneImage->getHtmlClass() ?>"><?= $uneImage->getNomNouveau() ?></a></td>
                            <td class="text-nowrap">
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', '<?= RessourceObject::ACTION_APPROUVER ?>');" title="Approuver"><span class="bi-hand-thumbs-up-fill text-success"></span></button>
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', '<?= RessourceObject::ACTION_BLOQUER ?>');" title="Bloquer"><span class="bi-hand-thumbs-down-fill text-danger"></span></button>
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', '<?= RessourceObject::ACTION_SUPPRIMER ?>');" title="Supprimer"><span class="bi-trash-fill" style="color: purple"></span></button>
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
                <?php if (count($table['values']) === _PAGINATION_IMAGES_) : ?>
                    <tfoot>
                        <tr>
                            <th>
                                <a href="<?= _URL_ADMIN_ . basename(__FILE__) ?>?lastId=<?= $lastId ?>" class="btn btn-primary"><span class="bi-arrow-left"></span> </a>
                            </th>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
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
                        if (action === '<?=RessourceObject::ACTION_SUPPRIMER ?>') {
                            // En cas de succès, supprimer les lignes correspondantes
                            const images = document.querySelectorAll('tr[data-id="' + idImage + '"], tr[data-md5="' + md5 + '"]');
                            images.forEach(function (ligne) {
                                ligne.remove();
                            });
                        } else {
                            const images = document.querySelectorAll('tr[data-id="' + idImage + '"] > td > a, tr[data-md5="' + md5 + '"] > td > a');
                            images.forEach(function (ligne) {
                                ligne.setAttribute('class', action);
                                console.log(ligne);
                            });
                        }
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