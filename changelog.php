<?php
/*
 * Copyright 2008-2020 Anael MOBILIA
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
require 'config/config.php';
require _TPL_TOP_;
?>
<h1><small>Historique des versions</small></h1>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v21">
            v2.1 - en cours
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v21" class="card-collapse">
        <div class="card-body">
            <ul>
                <li>Sélection uniquement des images lors du choix du fichier sur l'ordinateur.</li>
                <li>Envoi de plusieurs images simultanément.</li>
                <li>Glisser - déposer pour le choix des fichiers.</li>
                <li>Membres : Albums photos (création, affichage, partage).</li>
                <li>Membres : Permettre de définir une valeur par défaut pour les paramètres des images à l'envoi.</li>
                <li>Membres : Afficher les liens de partage dans l'espace membre.</li>
                <li>Expliquer les avantages pour les personnes inscrites sur le site.</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v205">
            v2.0.5 - En cours
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v205" class="card-collapse">
        <div class="card-body">
            <ul>
                <li>Migration bootstrap 3 -> 4.</li>
                <li>Utilisation de FontAwesome.</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v204">
            v2.0.4 - Octobre 2019
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v204" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Améliorations techniques de l'outil pour les maintenances futures.</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v203">
            v2.0.3 - Octobre 2019
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v203" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Améliorations techniques pour MySQL 5.7.</li>
                <li>Améliorations techniques pour PHP7.</li>
                <li>Ajout d'un outil de validation de l'installation.</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v202">
            v2.0.2 - Janvier 2019
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v202" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Correction d'une erreur où les images dont seulement les miniatures étaient affichées pouvaient être supprimées.</li>
                <li>Migration jQuery 2 => 3</li>
                <li>Suppression de la liste des referer <em>(optimisation des affichages d'images)</em>.</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v201">
            v2.0.1 - Juin 2018
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v201" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Ajout d'une liste des referer</li>
                <li>Gestion dynamique des délais avant suppression des fichiers inactifs</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v20">
            v2.0 - Novembre 2016
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v20" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Réactivation de l'ensemble des fonctionnalités du site (miniatures, retournement, ...)</li>
                <li>Modèle orienté objet pour une meilleure évolutivité sur le long terme</li>
                <li>Nouveau thème graphique</li>
                <li>Admin : Refonte de l'administration</li>
                <li>Admin : création de fonctions pour nettoyer les images obsolètes</li>
                <li>Reprise du schéma de la BDD</li>
                <li>Mise en place de tests automatisés sur l'ensemble des fonctionnalités du site</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v19">
            v1.9 - Janvier 2014
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v19" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Passage à la programation objet</li>
                <li>Reprise, factorisation et optimisation globale du site</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v127">
            v1.2.7 - 26 avril 2012
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v127" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Remise en place des options miniatures, rotation et redimensionnement</li>
                <li>Corrections de charset sur des messages d'erreur</li>
                <li>Amélioration de la protection anti-flood</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v126">
            v1.2.6 - 9 janvier 2012
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v126" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Changement de serveur internet (la vitesse d'affichage est meilleure + connectivité IPv6 !)</li>
                <li>Changement d'encodage par défaut pour les pages et le code HTML <em>(passage en UTF-8)</em></li>
                <li>Gestion de l'IPv6 dans la BDD et les statistiques</li>
                <li>Sélection du contenu des champs contenants l'url de l'image au clic</li>
                <li>Amélioration du code HTML</li>
                <li>Refonte des CSS</li>
                <li>Préparation d'un système de templates</li>
                <li>Utilisation de jQuery : (box en haut de page, options cachées par défaut à l'envoi d'un fichier)</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v125">
            v1.2.5 - 30 octobre 2011
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v125" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Suppression de la limite des dimensions d'images pour les utilisateurs enregistrés</li>
                <li>Ajout d'une fonction permettant le blocage d'images précises + affichage d'une image d'information sur le blocage</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v124">
            v1.2.4 - 08 septembre 2011
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v124" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Meilleure gestion des images inexistantes (erreurs 404)</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v123e">
            v1.2.3.e - 14 août 2011
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v123e" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Administration : suivi des requêtes SQL amélioré</li>
                <li>Finalisation de l'encodage des caractères spéciaux conformément à la norme HTML.</li>
                <li>Optimisation de la lisibilité du code source.</li>
                <li>Optimisation du temps d'éxecution du code PHP.</li>
            </ul>
        </div>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        <a data-toggle="collapse" href="#v123d">
            v1.2.3.d - 10 août 2011
            &nbsp;<span class="fas fa-caret-down"></span>
        </a>
    </div>
    <div id="v123d" class="card-collapse collapse">
        <div class="card-body">
            <ul>
                <li>Création du changelog.</li>
                <li>Encodage conforme à la norme HTML des caractères spéciaux.</li>
                <li>Correction d'une erreur PHP en cas d'envoi 'hack' de fichier.</li>
                <li>Amélioration de la portée des variables de language.</li>
            </ul>
        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>