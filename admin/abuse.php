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

use ArrayObject;

require '../config/config.php';
// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);
require _TPL_TOP_;
?>
<h1 class="mb-3"><small>Gestion des abus</small></h1>
<?php

$message = '';

// Liste des images signalées
$listeImagesSignalees = HelperAdmin::getImagesSignalees();
// Liste des images avec un ratio d'affichage incohérent
$listeImagesTropAffichees = HelperAdmin::getImagesTropAffichees();

// Si l'effacement est demandé
if (isset($_POST['effacer'])) :
    // Images uniquement en BDD
    foreach ((array)$listeImagesSignalees as $value) {
        $message .= '<br />Suppression de la BDD de l\'image ' . $value;

        // Je crée mon objet et lance la suppression
        $monImage = new ImageObject($value, RessourceObject::SEARCH_BY_MD5);
        //$monImage->supprimer();
    }
    $message .= '<br />Effacement terminé !';
    ?>

    <div class="alert alert-success">
        <?= $message ?>
    </div>
<?php else : ?>
    <div class="card">
        <div class="card-header">
            <?= $listeImagesSignalees->count() ?> image<?= ($listeImagesSignalees->count() > 1) ? 's' : '' ?> signalée<?= ($listeImagesSignalees->count() > 1) ? 's' : '' ?>
        </div>
        <div class="card-body">
            <ul>
                <?php foreach ((array)$listeImagesSignalees as $value) : ?>
                    <?php $uneImage = new ImageObject($value); ?>
                    <li><a href="<?= $uneImage->getURL() ?>?forceDisplay=1" target="_blank"><?= $uneImage->getNomNouveau() ?></a> <?= $uneImage->getNomOriginalFormate() ?> <small>(<?= $uneImage->getDateEnvoiFormatee() ?> / <?= $uneImage->getIpEnvoi() ?>) - afichée <?= $uneImage->getNbViewTotal() ?> fois (<?= $uneImage->getNbViewPerDay() ?>/jour - last <?= $uneImage->getLastViewFormate() ?>)</small></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <?= $listeImagesTropAffichees->count() ?> image<?= ($listeImagesTropAffichees->count() > 1) ? 's' : '' ?> trop affichée<?= ($listeImagesTropAffichees->count() > 1) ? 's' : '' ?>
        </div>
        <div class="card-body">
            <ul>
                <?php foreach ((array)$listeImagesTropAffichees as $value) : ?>
                    <?php $uneImage = new ImageObject($value); ?>
                    <li><a href="<?= $uneImage->getURL() ?>?forceDisplay=1" target="_blank"><?= $uneImage->getNomNouveau() ?></a> <?= $uneImage->getNomOriginalFormate() ?> <small>(<?= $uneImage->getDateEnvoiFormatee() ?> / <?= $uneImage->getIpEnvoi() ?>) - afichée <?= $uneImage->getNbViewTotal() ?> fois (<?= $uneImage->getNbViewPerDay() ?>/jour - last <?= $uneImage->getLastViewFormate() ?>)</small></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <form method="post">
        <button class="btn btn-danger" type="submit" name="effacer">
            <span class="fas fa-trash"></span>
            &nbsp;Supprimer les incohérences
        </button>
    </form>
<?php endif; ?>
<?php require _TPL_BOTTOM_; ?>