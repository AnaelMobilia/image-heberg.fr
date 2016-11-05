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
<div class="jumbotron">
    <h1><small>Panneau d'administration</small></h1>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h2 class="panel-title">
                Outils
            </h2>
        </div>
        <div class="panel-body">
            <a href="<?= _URL_ADMIN_ ?>listeFichiers.php" class="btn btn-default">
                <span class="glyphicon glyphicon-list-alt"></span>
                &nbsp;
                Lister les fichiers présents sur le disque
            </a>
            <div class="clearfix"></div>
            <br />
            <a href="<?= _URL_ADMIN_ ?>cleanNeverUsedOneYear.php" class="btn btn-danger">
                <span class="glyphicon glyphicon-trash"></span>
                &nbsp;
                Fichiers non affichés et envoi > 1 an
            </a>
            <div class="clearfix"></div>
            <br />
            <a href="<?= _URL_ADMIN_ ?>cleanUnusedThreeYears.php" class="btn btn-danger">
                <span class="glyphicon glyphicon-flash"></span>
                &nbsp;
                Fichiers dernier affichage > 3 ans
            </a>
            <div class="clearfix"></div>
            <br />
            <a href="<?= _URL_ADMIN_ ?>cleanErrors.php" class="btn btn-warning">
                <span class="glyphicon glyphicon-check"></span>
                &nbsp;
                Vérifier la cohérence disque / BDD
            </a>
        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>