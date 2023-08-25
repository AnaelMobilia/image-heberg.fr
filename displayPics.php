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

/*
 * Affichage d'une image & mise à jour des stats
 */
require 'config/config.php';

// URL demandée
$url = $_SERVER['REQUEST_URI'];
// Nom du fichier demandé - Nettoyer les paramètres
$fileName = basename(parse_url($url, PHP_URL_PATH));

// Faut-il forcer l'affichage (et ne pas enregistrer les stats) ?
$adminForceAffichage = false;

/**
 * Gestion du God mode
 */
if (
    str_contains($url, 'forceDisplay=1') // Mis en premier pour éviter d'ouvrir des sessions inutiles
    && UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN, false)
) {
    $adminForceAffichage = true;
}

/**
 * Définition du type
 */
if (preg_match('#/' . _REPERTOIRE_MINIATURE_ . '#', trim($url))) {
    // Miniature
    $monObjet = new MiniatureObject();
} else {
    // Image (ou erreur)
    $monObjet = new ImageObject();
}

/**
 * Est-ce que le fichier existe en BDD et sur le système de fichier ?
 */
if (
    !$monObjet->charger($fileName)
    || !file_exists($monObjet->getPathMd5())
) {
    // Fichier non trouvé...
    $monObjet = new ImageObject();
    $monObjet->charger(_IMAGE_404_);
    // Envoi d'un header en 404
    header('HTTP/2 404 Not Found');
}

/**
 * Le fichier est-il bloqué ?
 */
if (
    ($monObjet->isBloquee() || $monObjet->isSignalee())
    && !$adminForceAffichage
) {
    $monObjet = new ImageObject();
    $monObjet->charger(_IMAGE_BAN_);
    // Envoi d'un header en 451 -> Unavailable For Legal Reasons
    header('HTTP/2 451 Unavailable For Legal Reasons');
}

/**
 * Mise à jour des stats d'affichage
 */
if (!$adminForceAffichage) {
    if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        // IPv4
        $monObjet->setNbViewIpv4PlusUn();
    } else {
        // IPv6
        $monObjet->setNbViewIpv6PlusUn();
    }
    $monObjet->sauver();
}

/**
 * Fermeture du lien sur la BDD
 */
MaBDD::close();

/**
 * Envoi du bon entête HTTP
 */
if (!_PHPUNIT_) {
    header('Content-type: ' . HelperImage::getMimeType($monObjet->getPathMd5()));
}

/**
 * Envoi du fichier
 */
readfile($monObjet->getPathMd5());
