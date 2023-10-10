<?php

/*
 * Copyright 2008-2023 Anael MOBILIA
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
?>
<h1 class="mb-3"><small>A propos</small></h1>

<div class="card card-default">
    <div class="card-body">
        Ce site est administré par <a href="<?= _ADMINISTRATEUR_SITE_ ?>"><?= _ADMINISTRATEUR_NOM_ ?></a>
        <br />
        Pour toute demande ou information concernant ce site, merci d'utiliser
        <a href="/contact.php">le formulaire de contact</a>.
    </div>
</div>

<div class="card">
    <div class="card-header">
        Licences
    </div>
    <div class="card-body">
        Auteur de l'outil : <a href="//www.anael.eu">Anael MOBILIA</a>
        <br />
        Le <a href="//github.com/AnaelMobilia/image-heberg.fr">code source est disponible</a> sous licence GNU GPLv3.
        <br />
        Graphismes : <a href="//getbootstrap.com/">Bootstrap</a>
        <br />
        Logos : <a href="//icons.getbootstrap.com/">Bootstrap Icons</a>
        <br />
        Technologies : PHP, MySQL, HTML5, CSS3
    </div>
</div>

<div class="card">
    <div class="card-header">
        Hébergeur
    </div>
    <div class="card-body">
        Ce site est hébergé chez <a href="<?= _HEBERGEUR_SITE_ ?> "><?= _HEBERGEUR_NOM_ ?> </a>
        <br />
        Vous trouverez leur adresse et téléphone sur <a href="<?= _HEBERGEUR_SITE_ ?>">leur site internet</a>.
    </div>
</div>

<div class="card">
    <div class="card-header">
        <a data-bs-toggle="collapse" href="#collapseCNIL">
            Conservations de données à caractère privé
            &nbsp;<span class="bi-caret-down-fill"></span>
        </a>
    </div>
    <div id="collapseCNIL" class="collapse">
        <div class="card card-body">
            L'utilisation du service conduit à l'enregistrement de cookies techniques (gestion des sessions) sur votre ordinateur. Ces cookies seront supprimés lors de la fermeture de votre navigateur.
            <br />
            Votre adresse IP est enregistrée dans la base de données lors de l'envoi d'une image, de la création et à la connexion à votre espace membre.
            <br />
            La suppression d'une image envoyée sur le service conduit à la suppression de toutes les informations liées dans la base de données. Les images qui ne sont plus affichées depuis plus de
            <?= _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ ?> jours seront automatiquement effacées.
            <br />
            La suppression de votre compte entraine la suppression de toutes les informations liées à ce dernier.
            <br />
            Conformément à la directive 2006/24/CE sur la conservation des données, tous les accès au service sont enregistrés et conservés durant une durée d'au moins 12 mois et au plus 24 mois.
            <br />
            Aucune donnée n'est utilisée à but publicitaire, ni transmise à des tiers, ou réutilisée en dehors du présent service. L'ensemble des données sont stockées au sein de l'Union Européenne.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <a data-bs-toggle="collapse" href="#collapseCNIL2">
            Responsable du traitement : <?= _ADMINISTRATEUR_NOM_ ?>
            &nbsp;<span class="bi-caret-down-fill"></span>
        </a>
    </div>
    <div id="collapseCNIL2" class="collapse">
        <div class="card card-body">
            Les informations recueillies font l’objet d’un traitement informatique destiné à personnaliser votre utilisation du service.
            <br />
            Vous n'êtes pas obligé de créer un espace membre pour utiliser le service.
            <br />
            Le destinataire des données est <?= _ADMINISTRATEUR_NOM_ ?>.
            <br />
            Conformément à la loi « informatique et libertés » du 6 janvier 1978 modifiée en 2004, vous bénéficiez d'un droit d'accès et de rectification aux informations qui vous concernent, que vous pouvez exercer en vous adressant à <?= _ADMINISTRATEUR_NOM_ ?> via le formulaire de contact.
            <br />
            Vous pouvez également, pour des motifs légitimes, vous opposer au traitement des données vous concernant.
        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>