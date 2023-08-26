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
<h1 class="mb-3"><small>Nettoyage des incohérences</small></h1>
<?php

$message = '';

// Je récupère la liste des images en BDD
$listeImagesBDD = HelperAdmin::getAllImagesNameBDD();
// Je récupère la liste des images sur le HDD
$listeImagesHDD = HelperAdmin::getAllImagesNameHDD(_PATH_IMAGES_);

// Je recherche les erreurs sur les images
// Pour chaque images en BDD
$listeErreursImagesBDD = new ArrayObject(array_diff((array) $listeImagesBDD, (array) $listeImagesHDD));
// Pour chaque images en HDD
$listeErreursImagesHDD = new ArrayObject(array_diff((array) $listeImagesHDD, (array) $listeImagesBDD));


// Je récupère la liste des miniatures en BDD
$listeMiniaturesBDD = HelperAdmin::getAllMiniaturesNameBDD();
// Je récupère la liste des miniatures en HDD
$listeMiniaturesHDD = HelperAdmin::getAllImagesNameHDD(_PATH_MINIATURES_);

// Je recherche les erreurs sur les miniatures
// Pour chaque miniatures en BDD
$listeErreursMiniaturesBDD = new ArrayObject(array_diff((array) $listeMiniaturesBDD, (array) $listeMiniaturesHDD));
// Pour chaque miniatures en HDD
$listeErreursMiniaturesHDD = new ArrayObject(array_diff((array) $listeMiniaturesHDD, (array) $listeMiniaturesBDD));


// Si l'effacement est demandé
if (isset($_POST['effacer'])) :
    // Images uniquement en BDD
    foreach ((array) $listeErreursImagesBDD as $value) {
        $message .= '<br />Suppression de la BDD de l\'image ' . $value;

        // Je crée mon objet et lance la suppression
        $monImage = new ImageObject($value, RessourceObject::SEARCH_BY_MD5);
        $monImage->supprimer();
    }
    // Images uniquement en HDD
    foreach ((array) $listeErreursImagesHDD as $value) {
        $message .= '<br />Suppression du disque de l\'image ' . $value;

        // Suppression du fichier
        $rep = substr($value, 0, 1) . '/';
        $pathFinal = _PATH_IMAGES_ . $rep . $value;
        unlink($pathFinal);
    }
    // Miniatures uniquement en BDD
    foreach ((array) $listeErreursMiniaturesBDD as $value) {
        $message .= '<br />Suppression de la BDD de la miniature ' . $value;

        // Je crée mon objet et lance la suppression
        $maMiniature = new MiniatureObject($value, RessourceObject::SEARCH_BY_MD5);
        $maMiniature->supprimer();
    }

    // Miniatures uniquement en HDD
    foreach ((array) $listeErreursMiniaturesHDD as $value) {
        $message .= '<br />Suppression du disque de la miniature ' . $value;

        // Suppression du fichier
        $rep = substr($value, 0, 1) . '/';
        $pathFinal = _PATH_MINIATURES_ . $rep . $value;
        unlink($pathFinal);
    }
    $message .= '<br />Effacement terminé !';
    ?>

    <div class="alert alert-success">
        <?= $message ?>
    </div>
<?php else : ?>
    <div class="card">
        <div class="card-header">
            <?= $listeErreursImagesBDD->count() ?> image<?= ($listeErreursImagesBDD->count() > 1) ? 's' : '' ?> présente<?= ($listeErreursImagesBDD->count() > 1) ? 's' : '' ?> uniquement en BDD
        </div>
        <div class="card-body">
            <ul>
                <?php foreach ((array) $listeErreursImagesBDD as $value) : ?>
                    <li><?= $value ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <?= $listeErreursImagesHDD->count() ?> image<?= ($listeErreursImagesHDD->count() > 1) ? 's' : '' ?> présente<?= ($listeErreursImagesHDD->count() > 1) ? 's' : '' ?> uniquement sur HDD
        </div>
        <div class="card-body">
            <ul>
                <?php foreach ((array) $listeErreursImagesHDD as $value) : ?>
                    <li><?= $value ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <?= $listeErreursMiniaturesBDD->count() ?> miniature<?= ($listeErreursMiniaturesBDD->count() > 1) ? 's' : '' ?> présente<?= ($listeErreursMiniaturesBDD->count() > 1) ? 's' : '' ?> uniquement en BDD
        </div>
        <div class="card-body">
            <ul>
                <?php foreach ((array) $listeErreursMiniaturesBDD as $value) : ?>
                    <li><?= $value ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <?= $listeErreursMiniaturesHDD->count() ?> miniature<?= ($listeErreursMiniaturesHDD->count() > 1) ? 's' : '' ?> présente<?= ($listeErreursMiniaturesHDD->count() > 1) ? 's' : '' ?> uniquement sur HDD
        </div>
        <div class="card-body">
            <ul>
                <?php foreach ((array) $listeErreursMiniaturesHDD as $value) : ?>
                    <li><?= $value ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <form method="post">
        <button class="btn btn-danger" type="submit" name="effacer">
            <span class="bi-trash"></span>
            &nbsp;Supprimer les incohérences
        </button>
    </form>
<?php endif; ?>
<?php require _TPL_BOTTOM_; ?>