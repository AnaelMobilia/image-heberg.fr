<?php
/*
 * Copyright 2008-2020 Anael MOBILIA
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
require '../config/config.php';
// Vérification des droits d'accès
utilisateurObject::checkAccess(utilisateurObject::levelAdmin);
require _TPL_TOP_;
?>
<h1><small>Nettoyage des fichiers dormants</small></h1>
<?php
$message = '';

// Je récupère la liste des images non affichées depuis xx jours
$listeImages = metaObject::getUnusedFiles();

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
    <div class="alert alert-success">
        <?= $message ?>
    </div>
<?php else: ?>
    <div class="card card-primary">
        <div class="card-header">
            <?= $listeImages->count() ?> image(s) non affichée(s) depuis au moins <?= _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ ?> jour(s).
        </div>
        <div class="card-body">
            <ul>
                <?php foreach ((array) $listeImages as $value): ?>
                    <li><?= $value ?></li>
                <?php endforeach; ?>
            </ul>

        </div>
        <form method="post">
            <button class="btn btn-danger" type="submit" name="effacer">
                <span class="fas fa-trash"></span>
                &nbsp;
                Effacer ces fichiers
            </button>
        </form>
    </div>
<?php
endif;
require _TPL_BOTTOM_;
?>