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
//	./changelog.php
//	Historique des changements
//**************************************
?>
<div class="jumbotron">
    <h1><small>Changelog</small></h1>

    <div class="panel panel-primary">
        <div class="panel-body">
            Historiques des versions :

            <ul>
                <li>2.x - &agrave; venir
                    <ul>	
                        <li>Albums photos (cr&eacute;ation, affichage, partage)</li>
                        <li>API permettant l'int&eacute;gration &agrave; un site tiers</li>
                    </ul>
                </li>
                <li>2.0 - en cours
                    <ul>
                        <li>Mod&egrave;le orient&eacute; objet ?</li>
                        <li>Cr&eacute;ation d'une nouvelle fonction pour g&eacute;rer les erreurs.</li>
                        <li>Fin de la migration sur le nouveau syst&egrave;me de gestion des erreurs.</li>
                        <li>Fin de la migration sur le nouveau syst&egrave;me de gestion de la langue.</li>
                        <li>Nouvelle gestion de l'affichage des erreurs pour l'utilisateur.</li>
                        <li>Nouvelle gestion de l'affichage des erreurs pour l'administrateur.</li>
                        <li>Suivi plus pr&eacute;cis des erreurs (log++)</li>
                        <li>Nouveau calcul des erreurs via l'administration</li>
                        <li>Permettre la connexion longue dur&eacute; sur un ordinateur</li>
                        <li>Cacher les options par d&eacute;faut &agrave; l'upload</li>
                        <li>Permettre le changement de mot de passe</li>
                        <li>Permettre de d&eacute;finir une valeur par d&eacute;faut pour les param&egrave;tres des images &agrave; l'envoi</li>
                        <li>Refonte de l'administration</li>
                        <li>Expliquer les avantages pour les personnes inscrites sur le site</li>
                    </ul>
                </li>
                <li>v 1.9 - Janvier 2014
                    <ul>
                        <li>Passage à la programation objet</li>
                        <li>Reprise, factorisation et optimisation globale du site</li>
                    </ul>
                </li>
                <li>v 1.2.7 - 26 avril 2012
                    <ul>
                        <li>Remise en place des options miniatures, rotation et redimensionnement</li>
                        <li>Corrections de charset sur des messages d'erreur</li>
                        <li>Am&eacute;lioration de la protection anti-flood</li>
                    </ul>
                </li>
                <li>v 1.2.6 - 4, 9 janvier 2012
                    <ul>
                        <li>Changement de serveur internet (la vitesse d'affichage est meilleure + connectivit&eacute; IPv6 !)</li>
                        <li>Changement d'encodage par d&eacute;faut pour les pages et le code HTML <em>(passage en UTF-8)</em></li>
                        <li>Gestion de l'IPv6 dans la BDD et les statistiques</li>
                        <li>S&eacute;lection du contenu des champs contenants l'url de l'image au clic</li>
                        <li>Am&eacute;lioration du code HTML</li>
                        <li>Refonte des CSS</li>
                        <li>Pr&eacute;paration d'un syst&egrave;me de templates</li>
                        <li>Utilisation de jQuery : (box en haut de page, options cach&eacute;es par d&eacute;faut &agrave; l'envoi d'un fichier)</li>
                    </ul>
                </li>
                <li>v 1.2.5 - 30 octobre 2011
                    <ul>
                        <li>Suppression de la limite des dimensions d'images pour les utilisateurs enregistr&eacute;s</li>
                        <li>Ajout d'une fonction permettant le blocage d'images pr&eacute;cises + affichage d'une image d'information sur le blocage</li>
                    </ul>
                </li>
                <li>v 1.2.4 - 08 septembre 2011
                    <ul>
                        <li>Meilleure gestion des images inexistantes (erreurs 404)</li>
                    </ul>
                </li>
                <li>v 1.2.3.e - 14 ao&ucirc;t 2011
                    <ul>
                        <li>Administration : suivi des requ&ecirc;tes SQL am&eacute;lior&eacute;</li>
                        <li>Finalisation de l'encodage des caract&egrave;res sp&eacute;ciaux conform&eacute;ment &agrave; la norme HTML.</li>
                        <li>Optimisation de la lisibilit&eacute; du code source.</li>
                        <li>Optimisation du temps d'&eacute;xecution du code PHP.</li>
                    </ul>
                </li>
                <li>v 1.2.3.d - 10 ao&ucirc;t 2011
                    <ul>
                        <li>Cr&eacute;ation du changelog.</li>
                        <li>Encodage conforme &agrave; la norme HTML des caract&egrave;res sp&eacute;ciaux.</li>
                        <li>Correction d'une erreur PHP en cas d'envoi 'hack' de fichier.</li>
                        <li>Am&eacute;lioration de la port&eacute;e des variables de language.</li>
                    </ul>	
                </li>
            </ul>

        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>