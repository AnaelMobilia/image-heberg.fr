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
// Gestion des images bannies
// On oublie le compteur d'utilisation de cette image : cela reviendrait � faire une requ�te SQL / affichage

// liste des images blacklist�es
$liste_images_banned = Array(
// Hentai - 95.20.243.156 - http://forum.wawa-mania.ws/viewtopic.php?id=1022444 
	'1318037059115399.jpg',
	'1318037074115399.jpg',
	'1318037088115399.jpg',
	'1318037098115399.jpg',
	'1318037109115399.jpg',
	'1318037118115399.jpg',
	'1318037129115399.jpg',
	'1318037139115399.jpg',
	'1318037193115399.jpg',
	'1318037202115399.jpg',
	'1318037209115399.jpg',
	'1318037219115399.jpg',
	'1318037226115399.jpg',
	'1318037234115399.jpg',
	'1318037244115399.jpg',
	'1318037267115399.jpg',
	'1318037285115399.jpg',
	'1318037294115399.jpg',
	'1318037306115399.jpg',
	'1318037317115399.jpg',
	'1318037335115399.jpg',
	'1318037346115399.jpg',
	'1318037364115399.jpg',
	'1318037376115399.jpg',
	'1318037389115399.jpg',
	'1318037400115399.jpg',
	'1318037417115399.jpg',
	'1318038308115399.jpg',
	'1318038344115399.jpg',
// Hentai - 95.22.241.239 - http://forum.wawa-mania.ws/viewtopic.php?id=1022444
	'1317231832117480.jpg',
	'1317232024117480.jpg',
	'1317232128117480.jpg',
	'1317241324117480.jpg',
	'1317241367117480.jpg',
	'1317241435117480.jpg',
	'1317241478117480.jpg',
	'1317241489117480.jpg',
	'1317241504117480.jpg',
	'1317241573117480.jpg',
	'1317250440117480.jpg',
	'1317250551117480.jpg',
	'1317250581117480.jpg',
	'1317306678117480.jpg',
	'1317306687117480.jpg',
	'1317306702117480.jpg',
	'1317308779117480.jpg',
	'1317308832117480.jpg',
	'1317308938117480.jpg',
	'1317308975117480.jpg',
	'1317321953117480.jpg',
	'1317322029117480.jpg',
	'1317322081117480.jpg',
	'1317322173117480.jpg',
	'1317322184117480.jpg',
	'1317335548117480.jpg',
	'1317335580117480.jpg',
	'1317335608117480.jpg',
	'1317337133117480.jpg',
	'1317337169117480.jpg',
	'1317337825117480.jpg',
	'1317337862117480.jpg',
	'1317386480117480.jpg',
	'1317386495117480.jpg',
	'1317513238117480.jpg',
	'1317513256117480.jpg',
	'1317513518117480.jpg',
	'1317513568117480.jpg',
	'1317571010117480.jpg',
	'1317571024117480.jpg',
// Sexe - 90.59.147.208 - http://fr.tinychat.com/teenagerszone
	'132931493555987.jpg'
);

/* TODO :
1/ une page ajout d'images bloqu�es en admin 
	-> ajoute l'image bloqu�e dans un fichier
	-> ajoute l'image bloqu�e en DB + cause
2/ bloquer la suppression d'une image bloqu�e depuis l'espace membre
3/ interdire l'envoi de la m�me image (est-ce une bonne id�e, bas� sur MD5, implique donc des calculs � venir...)
4/ cr�er une page affichant les noms des images bloqu�es + la raison
5/ remplacer l'array ci dessus par un chargement du fichier du 1/
*/

// On regarde si l'image est blacklist�e
if(in_array(basename($_SERVER['REQUEST_URI']), $liste_images_banned)) {
	// On retourne une image d'erreur !
	header("Content-type: image/png");
	readfile("/srv/data/web/vhosts/www.image-heberg.fr/htdocs/template/images/image_banned.png");
	die();
}
?>
