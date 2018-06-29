<?php
/*
 * Copyright 2008-2018 Anael Mobilia
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
<h1><small>A propos</small></h1>

<div class="panel panel-default">
    <div class="panel-body">
        Ce site est administré par <a href="<?= _ADMINISTRATEUR_SITE_ ?>"><?= _ADMINISTRATEUR_NOM_ ?></a>
        <br />
        Pour toute demande ou information concernant ce site, merci d'utiliser <a href="/contact.php">le formulaire de contact</a>.
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h2 class="panel-title">Licences</h2>
    </div>
    <div class="panel-body">
        Auteur de l'outil : <a href="http://www.anael.eu">Anael MOBILIA</a>
        <br />
        Le <a href="https://github.com/AnaelMobilia/image-heberg.fr">code source est disponible</a> sous licence GNU GPL V3.
        <br />
        Graphismes et images : <a href="http://getbootstrap.com/">Bootstrap</a>
        <br />
        Logo "Fork me on GitHub" inspiré de <a href="https://github.com/tholman/github-corners">Tim Holman</a>
        <br />
        Technologies : PHP, MySQL, HTML5, CSS, jQuery
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h2 class="panel-title">Hébergeur</h2>
    </div>
    <div class="panel-body">
        Ce site est hébergé chez <a href="<?= _HEBERGEUR_SITE_ ?> "><?= _HEBERGEUR_NOM_ ?> </a>
        <br />
        Vous trouverez leur adresse et téléphone sur <a href="<?= _HEBERGEUR_SITE_ ?>">leur site internet</a>.
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h2 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseCNIL">
                Conservations de données à caractère privé
                &nbsp;<span class="caret"></span>
            </a>
        </h2>
    </div>
    <div id="collapseCNIL" class="panel-collapse collapse">
        <div class="panel-body">
            L'utilisation du service peut conduire à l'enregistrement de cookies sur votre ordinateur.
            <br />
            Vous pouvez supprimer librement ces cookies via les options de votre navigateur internet.
            <br />
            A défaut, et sauf action spécifique et explicite de votre part, les cookies seront supprimés lors de la fermeture de votre navigateur.
            <br /><br />
            Votre adresse IP est enregistrée dans la base de données lors de l'envoi d'une image, de la création et à la connexion à votre espace membre.
            <br />
            La suppression d'une image envoyée sur le service conduit à la suppression de toutes les informations liées dans la base de données.
            <br /><br />
            Conformément à la directive 2006/24/CE sur la conservation des données, tous les accès au service seront enregistrés et conservés durant <em>- au moins -</em> 2 années.
            <br /><br />
            Aucune donnée n'est utilisée à but publicitaire ni est transmise à des tiers, ou réutilisée en dehors du présent service.
        </div>
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h2 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseCNIL2">
                Responsable du traitement : <?= _ADMINISTRATEUR_NOM_ ?>
                &nbsp;<span class="caret"></span>
            </a>
        </h2>
    </div>
    <div id="collapseCNIL2" class="panel-collapse collapse">
        <div class="panel-body">
            Les informations recueillies font l’objet d’un traitement informatique destiné à personnaliser votre utilisation du service.
            <br />
            Vous n'&ecirc;tes pas obligé de créer un espace membre pour utiliser le service.
            <br />
            Le destinataire des données est <?= _ADMINISTRATEUR_NOM_ ?>.
            <br />
            Conformément à la loi « informatique et libertés » du 6 janvier 1978 modifiée en 2004,
            vous bénéficiez d'un droit d'accès et de rectification aux informations qui vous concernent,
            que vous pouvez exercer en vous adressant à <a href="/contact.php"><?= _ADMINISTRATEUR_NOM_ ?> via le formulaire de contact</a>.
            <br />
            Vous pouvez également, pour des motifs légitimes, vous opposer au traitement des données vous concernant.
        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>