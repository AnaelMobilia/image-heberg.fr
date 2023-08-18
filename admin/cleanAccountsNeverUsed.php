<?php

/*
 * Copyright 2008-2022 Anael MOBILIA
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
<h1 class="mb-3"><small>Nettoyage des comptes jamais utilisés</small></h1>
<?php

$message = '';

// Je récupère la liste des comptes jamais utilisés
$listeComptes = MetaObject::getNeverUsedAccounts();
$isPlural = ($listeComptes->count() > 1 ? "s" : "");

// Si l'effacement est demandé
if (isset($_POST['effacer'])) :
    foreach ((array) $listeComptes as $value) {
        $message .= '<br />Suppression du compte ' . $value;

        // Je crée mon objet et lance la suppression
        $monImage = new UtilisateurObject($value);
        $monImage->supprimer();
    }
    $message .= '<br />Effacement terminé !';
    ?>
    <div class = "alert alert-success">
        <?= $message ?>
    </div>
<?php else : ?>
    <div class="card">
        <div class="card-header">
            <?= $listeComptes->count() ?> compte<?= $isPlural ?> créé<?= $isPlural ?> il y a au moins <?= _DELAI_EFFACEMENT_COMPTES_JAMAIS_UTILISES_ ?> jour<?= _DELAI_EFFACEMENT_COMPTES_JAMAIS_UTILISES_ > 1 ? "s" : "" ?> et jamais utilisé<?= $isPlural ?>
        </div>
        <div class="card-body">
            <ul>
                <?php foreach ((array) $listeComptes as $value) : ?>
                    <li><?= $value ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <form method="post">
            <button class="btn btn-danger" type="submit" name="effacer">
                <span class="fas fa-trash"></span>
                &nbsp;Effacer ce<?= $isPlural ?> compte<?= $isPlural ?>
            </button>
        </form>
    </div>
<?php endif; ?>
<?php require _TPL_BOTTOM_; ?>