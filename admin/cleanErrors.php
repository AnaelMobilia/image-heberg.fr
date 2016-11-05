<?php
/*
 * Copyright 2008-2016 Anael Mobilia
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
    <h1><small>Nettoyage des incohérences</small></h1>
    <?php
    // Je récupère la liste des images en BDD
    $listeImagesBDD = metaObject::getAllImagesNameBDD();
    // Je récupère la liste des images sur le HDD
    $listeImagesHDD = metaObject::getAllImagesNameHDD(_PATH_IMAGES_);

    // Je recherche les erreurs sur les images
    // Pour chaque images en BDD
    $listeErreursImagesBDD = new ArrayObject(array_diff((array) $listeImagesBDD, (array) $listeImagesHDD));
    // Pour chaque images en HDD
    $listeErreursImagesHDD = new ArrayObject(array_diff((array) $listeImagesHDD, (array) $listeImagesBDD));


    // Je récupère la liste des miniatures en BDD
    $listeMiniaturesBDD = metaObject::getAllMiniaturesNameBDD();
    // Je récupère la liste des miniatures en HDD
    $listeMiniaturesHDD = metaObject::getAllImagesNameHDD(_PATH_MINIATURES_);

    // Je recherche les erreurs sur les miniatures
    // Pour chaque miniatures en BDD
    $listeErreursMiniaturesBDD = new ArrayObject(array_diff((array) $listeMiniaturesBDD, (array) $listeMiniaturesHDD));
    // Pour chaque miniatures en HDD
    $listeErreursMiniaturesHDD = new ArrayObject(array_diff((array) $listeMiniaturesHDD, (array) $listeMiniaturesBDD));


    // Si l'effacement est demandé
    if (isset($_POST['effacer'])) {
        // Images uniquement en BDD
        foreach ((array) $listeErreursImagesBDD as $value) {
            // Je crée mon objet et lance la suppression
            $monImage = new imageObject($value);
            $monImage->supprimer();
        }
        // Images uniquement en HDD
        foreach ((array) $listeErreursImagesHDD as $value) {
            // Suppression du fichier
            $rep = substr($value, 0, 1) . '/';
            $pathFinal = _PATH_IMAGES_ . $rep . $value;
            unlink($pathFinal);
        }
        // Miniatures uniquement en BDD
        foreach ((array) $listeErreursMiniaturesBDD as $value) {
            // Je crée mon objet et lance la suppression
            $maMiniature = new miniatureObject($value);
            $maMiniature->supprimer();
        }

        // Miniatures uniquement en HDD
        foreach ((array) $listeErreursMiniaturesHDD as $value) {
            // Suppression du fichier
            $rep = substr($value, 0, 1) . '/';
            $pathFinal = _PATH_MINIATURES_ . $rep . $value;
            unlink($pathFinal);
        }
        echo '<br /><br />Effacement terminé';
    }
    ?>
    <br />

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h2 class="panel-title">
                <?= $listeErreursImagesBDD->count() ?> image(s) présente(s) uniquement en BDD
            </h2>
        </div>
        <div class="panel-body">
            <ul>
                <?php foreach ((array) $listeErreursImagesBDD as $value): ?>
                    <li><?= $value ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h2 class="panel-title">
                <?= $listeErreursImagesHDD->count() ?> image(s) présente(s) uniquement sur HDD
            </h2>
        </div>
        <div class="panel-body">
            <ul>
                <?php foreach ((array) $listeErreursImagesHDD as $value): ?>
                    <li><?= $value ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h2 class="panel-title">
                <?= $listeErreursMiniaturesBDD->count() ?> miniature(s) présente(s) uniquement en BDD
            </h2>
        </div>
        <div class="panel-body">
            <ul>
                <?php foreach ((array) $listeErreursMiniaturesBDD as $value): ?>
                    <li><?= $value ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h2 class="panel-title">
                <?= $listeErreursMiniaturesHDD->count() ?> miniature(s) présente(s) uniquement sur HDD
            </h2>
        </div>
        <div class="panel-body">
            <ul>
                <?php foreach ((array) $listeErreursMiniaturesHDD as $value): ?>
                    <li><?= $value ?> (<strong>A effacer manuellement</strong>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <form method="post">
        <input type="submit" name="effacer" value="Effacer ces fichiers" class="btn btn-danger"/>
    </form>
</div>
<?php require _TPL_BOTTOM_; ?>