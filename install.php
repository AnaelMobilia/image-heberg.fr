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

/**
 * Vérification des prérequis basiques
 * Ce fichier peut être supprimé après l'installation
 */
/* 1 - Existence du fichier de config */
$conf = file_exists(__DIR__ . "/config/config.php");
if (!$conf) {
    die("Le fichier de configuration n'existe pas dans config/config.php !");
}

/* 2 - Requête sur la base de données */
if (!defined('_TRAVIS_')) {
    require 'config/config.php';
}
$res = maBDD::getInstance()->query("SELECT COUNT(*) AS nbImages FROM images");
if (!$res) {
    die("Erreur de communication avec la base de données, vérifiez les identifiants dans le fichier config/config.php !");
}
$resultat = $res->fetch()->nbImages;
if ($resultat < 2) { // 404 & banned par défaut
    die("La base de données n'a pas été initialisée correctement avec le fichier database.sql !");
}

/* 3 - droits sur répertoire des images */
if (!is_writable(_PATH_IMAGES_ . '_image_404.png')) {
    die("PHP doit pouvoir écrire dans les réeprtoires " . _REPERTOIRE_IMAGE_ . "* !");
}

/* 4 - gestion des sessions */
if (headers_sent()) {
    die("Les entêtes (sessions) ont déjà été envoyés, corrigez votre configuration serveur !");
}

echo "L'installation est OK !";
