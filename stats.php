<?php
/*
* Copyright 2008-2015 Anael Mobilia
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
require 'config/configV2.php';
require _TPL_TOP_;

require_once('./config/config.php');   //config du script
//-------------------------
//	NOMBRE IMAGES & BANDE PASSANTE
//-------------------------
// Nombre d'images
$nb_images = sql_query('SELECT COUNT(*) FROM `images`');
$nb_thumbs = sql_query('SELECT COUNT(*) FROM `thumbnails`');
// Bande passante
$bp_images_v4 = sql_query('SELECT SUM(`nb_view_v4`*`size`) FROM `images`');
$bp_images_v6 = sql_query('SELECT SUM(`nb_view_v6`*`size`) FROM `images`');
$bp_thumbs_v4 = sql_query('SELECT SUM(`t_nb_view_v4`*`t_size`) FROM `thumbnails`');
$bp_thumbs_v6 = sql_query('SELECT SUM(`t_nb_view_v6`*`t_size`) FROM `thumbnails`');
$bp_v4 = $bp_images_v4 + $bp_thumbs_v4;
$bp_v6 = $bp_images_v6 + $bp_thumbs_v6;
$bp_all = $bp_v4 + $bp_v6;
// Nombre d'affichages
$nb_view = sql_query('SELECT SUM(`nb_view_v4` + `nb_view_v6`) FROM `images`');
$nb_view_thumbs = sql_query('SELECT SUM(`t_nb_view_v4` + `t_nb_view_v6`) FROM `thumbnails`');
$nb_view_all = $nb_view + $nb_view_thumbs;
// Membres
$nb_mbr = sql_query('SELECT COUNT(*) FROM `membres`');
$nb_mbr_img = sql_query('SELECT COUNT(*) FROM `possede`');
?>
<div class="jumbotron">
    <h1><small>Statistiques</small></h1>

    <div class="panel panel-primary">
        <div class="panel-body">
            <ul>
                <li><?= number_format($nb_images, 0, ',', '`') ?> images et <?= number_format($nb_thumbs, 0, ',', '`') ?> miniatures actuellement h&eacute;berg&eacute;es</li>
                <li><?= number_format(($bp_all) / 1073741824, 1, ',', '`') ?> Go de trafic - dont <?= number_format(($bp_v6 / $bp_all) * 100, 2) ?>% en <a href="http://fr.wikipedia.org/wiki/Ipv6">IPv6</a></li>
                <li><?= number_format($nb_view_all, 0, ',', '`') ?> affichages d'images (<?= number_format($nb_view, 0, ',', '`') ?> + <?= number_format($nb_view_thumbs, 0, ',', '`') ?>)</li>
                <li><?= $nb_mbr ?> membres possèdant au total <?= $nb_mbr_img ?> images</li>
            </ul>
        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>
