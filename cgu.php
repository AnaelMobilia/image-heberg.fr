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
require 'config/configV2.php';
require _TPL_TOP_;
//**************************************
//	./cgu.php
//	Conditions G�n�rales d'Utilisation
//**************************************

require_once('./config/config.php');
?>
<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <h1><small>Conditions Générales d'Utilisation</small></h1>

    <div class="panel panel-primary">
        <div class="panel-body">
            <strong>Contenus autorisés :</strong></p>
            <ul>
                <li>Toutes images de type <?= strtoupper(__EXTENSIONS_OK__) ?>.</li>
                <li>Contenu conforme &agrave; la l&eacute;gislation fran&ccedil;aise.</li>
                <li>Pornographie et &eacute;rotisme non autoris&eacute;s.</li>
            </ul>
            <p><br /><strong>Propri&eacute;t&eacute;s de l'h&eacute;bergement :</strong></p>
            <ul>
                <li>Gratuit.</li>
                <li>Traffic illimit&eacute; <em>(hors abus)</em>.</li>
                <li>Conservation : trois ans depuis la dernière utilisation du fichier. Un fichier jamais utilisé sera conservé 1 an.</li>
                <li>Nombre d'images par compte : illimit&eacute;.</li>
                <li>Les fichiers restent votre propri&eacute;t&eacute;.</li>
                <li>Aucune suppression sur demande d'un utilisateur. <em>(utilisez la fonction de suppression à l'envoi ou utilisez l'espace membre)</em></li>
                <li>Toutes les donn&eacute;es poss&eacute;d&eacute;es seront fournies en cas de demande par la Justice.</li>
                <li>Rappel : l'administrateur peut-avoir acc&egrave;s &agrave; toutes les donn&eacute;es du service.</li>
            </ul>
            <p>Ces CGU sont modifiables, sans pr&eacute;avis, et &agrave; tout moment.
                <br />
                <em>Mise &agrave; jour : 24/01/2014 (Eclaircissements sur les durées de conservation)</em>
            </p>
        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>