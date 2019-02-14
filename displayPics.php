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
/**
 * Affichage d'une image & mise à jour des stats
 */
require 'config/configV2.php';

// URL demandée
$url = $_SERVER['REQUEST_URI'];
// Nom du fichier demandé
$fileName = basename($url);

/**
 * Définition du type
 */
$monObjet;
if (preg_match("#/" . _REPERTOIRE_MINIATURE_ . "#", trim($url))) {
   // Miniature
   $monObjet = new miniatureObject();
} else {
   // Image (ou erreur)
   $monObjet = new imageObject();
}

/**
 * Est-ce que le fichier existe ?
 */
if (!$monObjet->charger($fileName)) {
   // Fichier non trouvé...
   $monObjet = new imageObject();
   $monObjet->charger(_IMAGE_404_);
   // Envoi d'un header en 404
   header("HTTP/1.0 404 Not Found");
}

/**
 * Le fichier est-il bloqué ?
 */
if ($monObjet->isBloquee() || $monObjet->isSignalee()) {
   $monObjet = new imageObject();
   $monObjet->charger(_IMAGE_BAN_);
}

/**
 * Mise à jour des stats d'affichage
 */
if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
// IPv4
   $monObjet->setNbViewIpv4PlusUn();
} else {
// IPv6
   $monObjet->setNbViewIpv6PlusUn();
}
$monObjet->sauver();

/**
 * Fermeture du lien sur la BDD
 */
maBDD::close();

/**
 * Envoi du bon entête HTTP
 */
if (!_TRAVIS_) {
   header("Content-type: " . outils::getMimeType($monObjet->getPathMd5()));
}

/**
 * Envoi du fichier
 */
readfile($monObjet->getPathMd5());
