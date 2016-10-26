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
/**
 * Affichage d'une image & mise à jour des stats
 */
// TODO : gestion du blocage des images
// TODO : ne pas avoir besoin de la connexion à la bdd jusqu'au moment où on veut mettre à jour les stats
// today si pas bdd, pas d'image...

require 'config/configV2.php';

// Nom du fichier demandé
$fileName = basename($_SERVER['REQUEST_URI']);

// Path du fichier
// $_GET['type'] est fourni par le .htaccess
switch ($_GET['type']) {
    case 'pics':
        $filePath = _PATH_IMAGES_ . $fileName;
        break;
    case 'thumbs':
        $filePath = _PATH_MINIATURES_ . $fileName;
        break;
}

// Envoi de l'entête HTTP
// Je détermine le type de l'image
$typeImage = exif_imagetype($filePath);
// Le type mime qui va bien
$mimeType = image_type_to_mime_type($typeImage);
header("Content-type: " . $mimeType);

// Envoi du fichier
readfile($filePath);

// Mise à jour des stats du fichier
switch ($_GET['type']) {
    case 'pics':
        $fileObject = new imageObject($fileName);
        break;
    case 'thumbs':
        $fileObject = new miniatureObject($fileName);
        break;
}
// On enregistre l'affichage
if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    // IPv4
    $fileObject->setNbViewIpv4PlusUn();
} else {
    // IPv6
    $fileObject->setNbViewIpv6PlusUn();
}
?>