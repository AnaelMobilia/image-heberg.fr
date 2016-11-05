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
    <h1><small>Nettoyage des fichiers jamais utilisés depuis 1 année</small></h1>
    <?php
    $message = '';

    // Je récupère la liste des images non affichées depuis un an
    $listeImages = metaObject::getNeverUsedOneYear();

    // Si l'effacement est demandé
    if (isset($_POST['effacer'])) :
        foreach ((array) $listeImages as $value) {
            $message .= '<br />Suppression de l\'image ' . $value;

            // Je crée mon objet et lance la suppression
            $monImage = new imageObject($value);
            $monImage->supprimer();
        }
        $message .= '<br />Effacement terminé !';
        ?>
        <div class = "alert alert-success">
            <?= $message ?>
        </div>
    </div>
<?php else: ?>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h2 class="panel-title">
                <?= $listeImages->count() ?> image(s) envoyée(s) il y a au moins un an n'ont jamais été affichée(s)
            </h2>
        </div>
        <div class="panel-body">
            <ul>
                <?php foreach ((array) $listeImages as $value): ?>
                    <li><?= $value ?></li>
                <?php endforeach; ?>
            </ul>

        </div>
        <form method="post">
            <button class="btn btn-danger" type="submit" name="effacer">
                <span class="glyphicon glyphicon-trash"></span>
                &nbsp;
                Effacer ces fichiers
            </button>
        </form>
    </div>
    </div>
<?php
endif;
require _TPL_BOTTOM_;
?>