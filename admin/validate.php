<?php
/*
 * Copyright 2008-2019 Anael Mobilia
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
metaObject::checkUserAccess(utilisateurObject::levelAdmin);
require _TPL_TOP_;
?>
<h1><small>Vérification de la configuration du serveur</small></h1>
<div class="card card-primary">
    <div class="card-header">
        PHP
    </div>
    <div class="card-body">
        <?= metaObject::getPhpVersion() ?>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        MySQL
    </div>
    <div class="card-body">
        <?= metaObject::getMysqlVersion() ?>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        Accès aux répertoires protégés par .htaccess .
    </div>
    <div class="card-body">
        Les valeurs doivent être de type "HTTP *.* 403 Forbidden".
        <ul>
            <li>Répertoire config : <?= metaObject::getStatusHTTP(_URL_CONFIG_) ?></li>
            <li>Répertoire admin : <?= metaObject::getStatusHTTP(_URL_ADMIN_) ?></li>
            <li>Répertoire membre : <?= metaObject::getStatusHTTP(_URL_MEMBRE_) ?></li>
        </ul>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        Droits sur tous les dossiers dans files/
    </div>
    <div class="card-body">
        <ul>
            <?php
            $lesDroits = metaObject::isRecursivelyWritable(_PATH_IMAGES_);
            foreach ((array) $lesDroits as $unItem):
                ?>
                <li><?= $unItem ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php
require _TPL_BOTTOM_;
?>