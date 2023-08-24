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

// Ce script peut être appelé par un cron
if (! defined("_IS_CRON_") || !_IS_CRON_) {
    require '../config/config.php';
}
// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);
require _TPL_TOP_;
?>
<h1 class="mb-3"><small>Nettoyage des fichiers jamais utilisés</small></h1>
<?php

$message = '';

// Je récupère la liste des images jamais affichées
$listeImages = HelperAdmin::getNeverUsedFiles();
$isPlural = ($listeImages->count() > 1 ? "s" : "");

// Si l'effacement est demandé
if (isset($_POST['effacer'])) :
    foreach ((array)$listeImages as $value) {
        $message .= '<br />Suppression de l\'image ' . $value;

        // Je crée mon objet et lance la suppression
        $monImage = new ImageObject($value);
        $monImage->supprimer();
    }
    $message .= '<br />Effacement terminé !';
    ?>
    <div class="alert alert-success">
        <?= $message ?>
    </div>
<?php else : ?>
    <div class="card">
        <div class="card-header">
            <?= $listeImages->count() ?> image<?= $isPlural ?> envoyée<?= $isPlural ?> il y a au moins <?= _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ ?> jour<?= _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ > 1 ? "s" : "" ?> et jamais affichée<?= $isPlural ?>
        </div>
        <div class="card-body">
            <ul>
                <?php foreach ((array) $listeImages as $value) : ?>
                    <li>
                        <a href="<?= str_replace('http:', 'https:', _URL_IMAGES_) . $value ?>?forceDisplay=1" target="_blank"><?= $value ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>
        <form method="post">
            <button class="btn btn-danger" type="submit" name="effacer">
                <span class="fas fa-trash"></span>
                &nbsp;Effacer ce<?= $isPlural ?> fichier<?= $isPlural ?>
            </button>
        </form>
    </div>
<?php endif; ?>
<?php require _TPL_BOTTOM_; ?>