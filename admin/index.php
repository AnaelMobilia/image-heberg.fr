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

require '../config/config.php';
// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);
require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Panneau d'administration</small></h1>
    <div class="card">
        <div class="card-header">
            Gestion du site
        </div>
        <div class="card-body">
            <a href="<?= _URL_ADMIN_ ?>abuse.php" class="btn btn-success">
                <span class="bi-question-octagon-fill"></span>
                &nbsp;Images à vérifier
            </a>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_ADMIN_ ?>search.php" class="btn btn-success">
                <span class="bi-search"></span>
                &nbsp;Rechercher une image
            </a>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_ADMIN_ ?>lastUpload.php" class="btn btn-success">
                <span class="bi-file-earmark-image"></span>
                &nbsp;Derniers fichiers envoyés
            </a>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_ADMIN_ ?>ia-categorisation.php" class="btn btn-success">
                <span class="bi-radioactive"></span>
                &nbsp;IA - Images à catégoriser
            </a>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_ADMIN_ ?>ia-entrainement.php" class="btn btn-success">
                <span class="bi-robot"></span>
                &nbsp;IA - Entrainer le moteur
            </a>
            <div class="clearfix"></div>
            </a>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_ADMIN_ ?>abuse-network.php" class="btn btn-success">
                <span class="bi-cpu"></span>
                &nbsp;Réseaux suspects
            </a>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_ADMIN_ ?>validate.php" class="btn btn-success">
                <span class="bi-cloud-check"></span>
                &nbsp;Vérifier la configuration
            </a>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_ADMIN_ ?>cleanFilesNeverUsed.php" class="btn btn-warning">
                <span class="bi-file-earmark-x"></span>
                &nbsp;Supprimer les images jamais affichées et envoyées depuis plus de <?= _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ ?> jour<?= (_DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ > 1) ? 's' : '' ?>
            </a>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_ADMIN_ ?>cleanInactiveFiles.php" class="btn btn-warning">
                <span class="bi-file-earmark-x-fill"></span>
                &nbsp;Supprimer les images non affichées depuis plus de <?= _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ ?> jour<?= (_DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ > 1) ? 's' : '' ?>
            </a>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_ADMIN_ ?>cleanAccountsNeverUsed.php" class="btn btn-warning">
                <span class="bi-person-x"></span>
                &nbsp;Supprimer les comptes créés il y a plus de <?= _DELAI_EFFACEMENT_COMPTES_JAMAIS_UTILISES_ ?> jour<?= (_DELAI_EFFACEMENT_COMPTES_JAMAIS_UTILISES_ > 1) ? 's' : '' ?> et sans images associées
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            Gestion technique
        </div>
        <div class="card-body">
            <a href="<?= _URL_ADMIN_ ?>listeFichiers.php" class="btn btn-default">
                <span class="bi-list-check"></span>
                &nbsp;Lister les fichiers présents sur le disque
            </a>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_ADMIN_ ?>cleanErrors.php" class="btn btn-default">
                <span class="bi-database-fill-check"></span>
                &nbsp;Vérifier la cohérence disque et BDD
            </a>
        </div>
    </div>
    <?php require _TPL_BOTTOM_ ?>