<?php

/*
 * Copyright 2008-2021 Anael MOBILIA
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

/*
 * CHAMPS A CONFIGURER
 */
/* Base de données */
// Serveur de base de données
define('_BDD_HOST_', 'xxx');
// Utilisateur SQL
define('_BDD_USER_', 'xxx');
// Mot de passe SQL
define('_BDD_PASS_', 'xxx');
// Nom de la base de données
define('_BDD_NAME_', 'xxx');

/* Système de fichiers */
// Emplacement de votre site sur le système de fichiers de votre hébergeur
define('_PATH_', '/path/to/example.com/');


/* A propos de l'outil */
// Nom affiché du service
define('_SITE_NAME_', 'monSite');
// URL du site
define('_BASE_URL_', 'www.example.com/');
// Administrateur du site
define('_ADMINISTRATEUR_NOM_', 'John DOE');
// Site web de l'administrateur
define('_ADMINISTRATEUR_SITE_', '//www.example.com/');
// Mail de l'administrateur (non affiché)
define('_ADMINISTRATEUR_EMAIL_', 'john.doe@example.com');


/* Informations légales */
// Hébergeur du site
define('_HEBERGEUR_NOM_', 'OVH');
// Site web de l'hébergeur
define('_HEBERGEUR_SITE_', '//www.ovh.com');


/* Configurations spécifiques de l'outil */
// Poids maximal des fichiers
define('_IMAGE_POIDS_MAX_', 5242880);
// Délai de conservation d'une image jamais affichée (en jours)
define('_DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_', 7);
// Délai depuis le dernier affichage d'une image avant de la supprimer (en jours)
define('_DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_', 365);
// Volume maximal de stockage d'images (en Go)
define('_QUOTA_MAXIMAL_IMAGES_GO_', 90);
// Affichage des messages d'erreur
define('_DEBUG_', true);

/**
 * FIN DES CHAMPS A CONFIGURER
 */
/**
 * CHAMPS A COMPLETER UNIQUEMENT SI VOUS AVIEZ INSTALLE UNE VERSION ANTERIEURE A v2.0.4
 */
// Salt pour les mots de passe
// Legacy - n'est plus requis !!
define('_GRAIN_DE_SEL_', 'xxx');
/**
 * FIN DES CHAMPS A COMPLETER UNIQUEMENT SI VOUS AVEZ UNE VERSION ANTERIEURE A v2.0.4
 */
// Désactivation des tests Tests TRAVIS-CI
define('_PHPUNIT_', false);

require _PATH_ . 'config/image-heberg.php';
