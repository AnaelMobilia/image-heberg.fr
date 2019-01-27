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
require '../config/configV2.php';
// Vérification des droits d'accès
metaObject::checkUserAccess(utilisateurObject::levelAdmin);
require _TPL_TOP_;
?>
<h1><small>Page de provenance des visiteurs</small></h1>
<?php
// Je récupère la liste des referer
$listeReferer = metaObject::getReferers();
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h2 class="panel-title">
            <?= $listeReferer->count() ?> page(s) de provenance.
        </h2>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <td>#</td>
                        <td>URL</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listeReferer as $key => $value): ?>
                       <tr>
                           <td><?= $value ?></td>
                           <td><?= $key ?></td>                       
                       </tr>
                    <?php endforeach; ?>               
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
require _TPL_BOTTOM_;
?>