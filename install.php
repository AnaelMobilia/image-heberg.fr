<?php

/*
 * Copyright 2008-2022 Anael MOBILIA
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
 * Vérification des prérequis basiques
 * Ce fichier peut être supprimé après l'installation
 */
/* 1 - Existence du fichier de config */
$conf = file_exists(__DIR__ . "/config/config.php");
if (!$conf) {
    $msg = "Le fichier de configuration n'existe pas dans config/config.php !";
    if (!_PHPUNIT_) {
        die($msg);
    } else {
        echo $msg;
    }
}

/* 2 - Requête sur la base de données */
if (!defined('_PHPUNIT_')) {
    require 'config/config.php';
}
$res = MaBDD::getInstance()->query("SELECT COUNT(*) AS nbImages FROM images");
if (!$res) {
    $msg = "Erreur de connexion à la base de données, vérifiez les identifiants dans le fichier config/config.php !";
    if (!_PHPUNIT_) {
        die($msg);
    } else {
        echo $msg;
    }
}
$resultat = $res->fetch()->nbImages;
if ($resultat < 2) { // 404 & banned par défaut
    $msg = "La base de données n'a pas été initialisée correctement avec le fichier database.sql !";
    if (!_PHPUNIT_) {
        die($msg);
    } else {
        echo $msg;
    }
}

/* 3 - droits sur répertoire des images */
if (!is_writable(_PATH_IMAGES_ . '_image_404.png')) {
    $msg = "PHP doit pouvoir écrire dans les répertoires " . _REPERTOIRE_IMAGE_ . "* !";
    if (!_PHPUNIT_) {
        die($msg);
    } else {
        echo $msg;
    }
}

/* 4 - gestion des sessions */
if (headers_sent()) {
    $msg = "Les entêtes (sessions) ont déjà été envoyés, corrigez votre configuration serveur !";
    if (!_PHPUNIT_) {
        die($msg);
    }
    // Sinon, ne rien faire, nous sommes dans l'environnement de tests qui a déjà affiché des données (phpunit...)
}

echo "L'installation est OK !";
