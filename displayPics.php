<?php

/*
 * Copyright 2008-2024 Anael MOBILIA
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
if (!defined('_PHPUNIT_')) {
    require 'config/config.php';
}

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
 * Détection des User-Agent malveillant et blocage des images demandées
 */
if (
    isset($_SERVER['HTTP_USER_AGENT'])
    && in_array($_SERVER['HTTP_USER_AGENT'], _ABUSE_DISABLE_PICS_WHEN_USERE_AGENT_, true)
) {
    // Blocage de l'image
    $monObjet->setSignalee(true);
    $monObjet->sauver();

    if (!_PHPUNIT_) {
        // Générer un mail d'erreur à l'admin
        require 'cron/abuse.php';
    }
}

/**
 * Le fichier est-il bloqué ?
 */
if (
    !$adminForceAffichage
    && ($monObjet->isBloquee() || $monObjet->isSignalee())
) {
    $monObjet = new ImageObject();
    $monObjet->charger(_IMAGE_BAN_);
    // Envoi d'un header en 451 -> Unavailable For Legal Reasons
    header('HTTP/2 451 Unavailable For Legal Reasons');
} elseif (
    !$adminForceAffichage
    && !$monObjet->isApprouvee()
    && (
        // Image non suspecte
        (!$monObjet->isSuspecte() && $monObjet->getNbViewPerDay() > _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_)
        // Image suspecte -> seuils réduits
        || ($monObjet->isSuspecte() && $monObjet->getNbViewPerDay() > (_ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_))
    )
) {
    // Lancer un blocage de l'image si trop affichée
    require 'cron/abuse.php';
}

/**
 * Mise à jour des stats d'affichage
 */
if (!$adminForceAffichage) {
    $monObjet->updateStatsAffichage($_SERVER['REMOTE_ADDR']);
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
