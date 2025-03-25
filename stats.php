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

require 'config/config.php';
require _TPL_TOP_;

// Stats Images
$reqImage = MaBDD::getInstance()->query('SELECT COUNT(*) AS nb, SUM(nb_view_v4 * size) AS bpv4, SUM(nb_view_v6 * size) AS bpv6, SUM(nb_view_v4 + nb_view_v6) AS nbAff, SUM(size) as totSize, MAX(id) as nbTot FROM images');
// Je récupère les valeurs
$valImage = $reqImage->fetch();

// Stats Miniatures
$reqMiniature = MaBDD::getInstance()->query('SELECT COUNT(*) AS nb, SUM(nb_view_v4 * size) AS bpv4, SUM(nb_view_v6 * size) AS bpv6, SUM(nb_view_v4 + nb_view_v6) AS nbAff, SUM(size) as totSize FROM thumbnails WHERE is_preview = 0');
// Je récupère les valeurs
$valMiniature = $reqMiniature->fetch();

// Stats membres
$reqMembre = MaBDD::getInstance()->query('SELECT COUNT(*) AS nb FROM membres');
// Je récupère les valeurs
$valMembre = $reqMembre->fetch();

// Stats membres -> possède
$reqPossede = MaBDD::getInstance()->query('SELECT COUNT(*) AS nb FROM possede');
// Je récupère les valeurs
$valPossede = $reqPossede->fetch();

// Bande passante
$bp_all = 1;
$bp_all += $valImage->bpv4 + $valMiniature->bpv4;
$bp_v6 = $valImage->bpv6 + $valMiniature->bpv6;
$bp_all += $bp_v6;
// Nombre d'affichages
$nb_view_all = $valImage->nbAff + $valMiniature->nbAff;
// Taille totale
$size_all = $valImage->totSize + $valMiniature->totSize;
?>
<h1 class="mb-3"><small>Statistiques</small></h1>

<div class="card">
    <div class="card-body">
        <ul>
            <li>
                <?= number_format($valImage->nbTot, 0, ',', ' ') ?> images hébergées au total.
            </li>
            <li>
                <?= number_format($valImage->nb, 0, ',', ' ') ?> images et
                <?= number_format($valMiniature->nb, 0, ',', ' ') ?> miniatures actuellement hébergées.
            </li>
            <li>
                <?= number_format($size_all / 1073741824, 1, ',', ' ') ?> Go de fichiers actuellement stockés.
            </li>
            <li>
                <?= number_format($bp_all / 1073741824, 1, ',', ' ') ?> Go de trafic - dont
                <?= number_format(($bp_v6 / $bp_all) * 100, 2) ?>%
                en <a href="https://fr.wikipedia.org/wiki/Ipv6">IPv6</a>.
            </li>
            <li>
                <?= number_format($nb_view_all, 0, ',', ' ') ?> affichages d'images
                <em>(<?= number_format($valImage->nbAff, 0, ',', ' ') ?> images
                    + <?= number_format($valMiniature->nbAff, 0, ',', ' ') ?> miniatures)</em>.
            </li>
            <li>
                <?= $valMembre->nb ?> membres possèdant au total <?= $valPossede->nb ?> images.
            </li>
        </ul>
    </div>
</div>
<?php
require _TPL_BOTTOM_ ?>
