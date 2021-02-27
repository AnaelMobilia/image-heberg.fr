<?php

/*
 * Copyright 2008-2021 Anael MOBILIA
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

require '../config/config.php';
// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);
require _TPL_TOP_;
?>
<h1><small>Panneau d'administration</small></h1>
<div class="card card-primary">
    <div class="card-header">
        Gestion du site
    </div>
    <div class="card-body">
        <a href="<?= _URL_ADMIN_ ?>validate.php" class="btn btn-success">
            <span class="fas fa-list-alt"></span>
            &nbsp;
            Vérifier la configuration
        </a>
        <div class="clearfix"></div>
        <br />
        <a href="<?= _URL_ADMIN_ ?>cleanFilesNeverUsed.php" class="btn btn-warning">
            <span class="fas fa-trash"></span>
            &nbsp;
            Supprimer les images jamais affichées et envoyées depuis
            plus de <?= _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ ?>
            jour<?= (_DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ > 1 ) ? 's' : '' ?>
        </a>
        <div class="clearfix"></div>
        <br />
        <a href="<?= _URL_ADMIN_ ?>cleanInactiveFiles.php" class="btn btn-warning">
            <span class="fas fa-trash-alt"></span>
            &nbsp;
            Supprimer les images non affichées depuis
            plus de <?= _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ ?>
            jour<?= (_DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ > 1 ) ? 's' : '' ?>
        </a>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        Gestion technique
    </div>
    <div class="card-body">
        <a href="<?= _URL_ADMIN_ ?>listeFichiers.php" class="btn btn-default">
            <span class="fas fa-list-alt"></span>
            &nbsp;
            Lister les fichiers présents sur le disque
        </a>
        <div class="clearfix"></div>
        <br />
        <a href="<?= _URL_ADMIN_ ?>cleanErrors.php" class="btn btn-default">
            <span class="fas fa-check"></span>
            &nbsp;
            Vérifier la cohérence disque et BDD
        </a>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>