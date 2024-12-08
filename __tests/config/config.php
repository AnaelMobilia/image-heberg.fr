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

/*
 * CHAMPS A CONFIGURER
 */
/* Base de données */
// Serveur de base de données
const _BDD_HOST_ = 'localhost';
// Utilisateur SQL
const _BDD_USER_ = 'root';
// Mot de passe SQL
const _BDD_PASS_ = 'root';
// Nom de la base de données
const _BDD_NAME_ = 'imageheberg';


/* Système de fichiers */
// Emplacement de votre site sur le système de fichiers de votre hébergeur
const _PATH_ = '/home/runner/work/image-heberg.fr/image-heberg.fr/';


/* A propos de l'outil */
// Nom affiché du service
const _SITE_NAME_ = 'monSite';
// URL du site
const _BASE_URL_ = 'www.example.com/';
// Administrateur du site
const _ADMINISTRATEUR_NOM_ = 'John DOE';
// Site web de l'administrateur
const _ADMINISTRATEUR_SITE_ = '//www.example.com/';
// Mail de l'administrateur (non affiché)
const _ADMINISTRATEUR_EMAIL_ = 'john.doe@example.com';


/* Informations légales */
// Hébergeur du site
const _HEBERGEUR_NOM_ = 'OVH';
// Site web de l'hébergeur
const _HEBERGEUR_SITE_ = '//www.ovh.com';


/* Configurations spécifiques de l'outil */
// Poids maximal des fichiers
const _IMAGE_POIDS_MAX_ = 5242880;
// Délai de conservation d'une image jamais affichée (en jours)
const _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ = 7;
// Délai depuis le dernier affichage d'une image avant de la supprimer (en jours)
const _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ = 365;
// Volume maximal de stockage d'images (en Go)
const _QUOTA_MAXIMAL_IMAGES_GO_ = 90;
// Affichage des messages d'erreur
const _DEBUG_ = true;


/* Gestion des abus */
// Nombre d'affichage par jour à partir duquel une image est suspecte
const _ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ = 1500;
// Nombre d'affichage par jour à partir duquel une image est automatiquement bloquée
const _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ = 100000;
// Division des seuils d'abus si une image est considérée comme suspecte
const _ABUSE_DIVISION_SEUILS_SI_SUSPECT_ = 2;

// Désactiver l'envoi d'images depuis un noeud de sortie Tor
const _TOR_DISABLE_UPLOAD_ = true;
// Désactiver l'envoi d'images au bout de x images bloquées (mettre 0 pour ne pas l'activer)
const _ABUSE_DISABLE_UPLOAD_AFTER_X_IMAGES_ = 5;

// User-Agent pour lesquels bloquer les images
const _ABUSE_DISABLE_PICS_WHEN_USERE_AGENT_ = ['someUserAgentNumberOne', 'AnoterUserAgentNumberTwo'];

/**
 * FIN DES CHAMPS A CONFIGURER
 */
/**
 * CHAMPS A COMPLETER UNIQUEMENT SI VOUS AVIEZ INSTALLE UNE VERSION ANTERIEURE A v2.0.4
 */
// Salt pour les mots de passe
// Legacy - n'est plus requis !!
const _GRAIN_DE_SEL_ = '';
/**
 * FIN DES CHAMPS A COMPLETER UNIQUEMENT SI VOUS AVEZ UNE VERSION ANTERIEURE A v2.0.4
 */
// Activation des tests Tests TRAVIS-CI
const _PHPUNIT_ = true;

require _PATH_ . 'config/image-heberg.php';
