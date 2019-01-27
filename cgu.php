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
require 'config/configV2.php';
require _TPL_TOP_;
?>
<h1><small>Conditions Générales d'Utilisation</small></h1>

<div class="alert alert-success">Ces CGU sont modifiables, sans pr&eacute;avis, et &agrave; tout moment.</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            Contenus autorisés
        </h3>
    </div>
    <div class="panel-body">
        <ul>
            <li>Toutes images de type JPG, PNG, GIF.</li>
            <li>Contenu conforme &agrave; la l&eacute;gislation fran&ccedil;aise.</li>
            <li>Pornographie et &eacute;rotisme non autoris&eacute;s.</li>
        </ul>
    </div>
</div>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            Propri&eacute;t&eacute;s de l'h&eacute;bergement
        </h3>
    </div>
    <div class="panel-body">
        <ul>
            <li>Gratuit.</li>
            <li>Traffic illimit&eacute; <em>(hors abus)</em>.</li>
            <li><b>Conservation :</b>
                <ul>
                    <li><?= _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ ?> jour(s) à compter de la dernière utilisation du fichier.</li>
                    <li>A défaut, <?= _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ ?> jour(s) après l'envoi <em>(si aucun affichage)</em>.</li>
                </ul>
            </li>
            <li>Nombre d'images par compte : illimit&eacute;.</li>
            <li>Les fichiers restent votre propri&eacute;t&eacute;.</li>
            <li>Aucune suppression sur demande d'un utilisateur. <em>(utilisez la fonction de suppression à l'envoi ou utilisez l'espace membre)</em></li>
            <li>Toutes les donn&eacute;es poss&eacute;d&eacute;es seront fournies en cas de demande judiciaire.</li>
            <li>Rappel : l'administrateur peut-avoir acc&egrave;s &agrave; toutes les donn&eacute;es du service.</li>
        </ul>
        <div class="panel-footer">
            <em>Mise &agrave; jour le 29 juin 2018 : réduction des durées de conservation des images inactives</em>
        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>