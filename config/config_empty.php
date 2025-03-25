<?php

/*
 * Copyright 2008-2025 Anael MOBILIA
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
const _BDD_HOST_ = 'xxx';
// Utilisateur SQL
const _BDD_USER_ = 'xxx';
// Mot de passe SQL
const _BDD_PASS_ = 'xxx';
// Nom de la base de données
const _BDD_NAME_ = 'xxx';


/* Système de fichiers */
// Emplacement de votre site sur le système de fichiers de votre hébergeur
const _PATH_ = '/path/to/example.com/';


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
const _HEBERGEUR_NOM_ = 'HOSTING';
// Site web de l'hébergeur
const _HEBERGEUR_SITE_ = '//www.example.com';


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
const _DEBUG_ = false;
// Délai de conservation des comptes jamais utilisés (en jours)
const _DELAI_EFFACEMENT_COMPTES_JAMAIS_UTILISES_ = 30;


/* Gestion des abus */
// Catégories d'images interdites
const _ABUSE_TYPES_ = [
    // "Nom de catégorie"
    'Pornographie' => [
        // Description textuelle
        'description' => 'contenu à caractère pornographique',
        // Taux de détection IA (%) à partir duquel considérer l'image comme non désirée ?
        'limite' => 90,
    ],
    'Erotisme' => [
        'description' => 'contenu à caractère érotique',
        'limite' => 90,
    ],
    'Violence' => [
        'description' => 'contenu montrant de la violence (cadavre, torture, maltraitance, image dégradante, ...) ou d\'appel à la violence',
        'limite' => 90,
    ],
    'Spam' => [
        'description' => 'contenu utilisé dans l\'envoi de messages de spam ou de phishing',
        'limite' => 90,
    ],
];
// Nombre d'affichages par jour à partir duquel une image est suspecte
const _ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ = 1500;
// Nombre d'affichages par jour à partir duquel une image est automatiquement bloquée
const _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ = 100000;
// Nombre d'affichages par jour à partir duquel une image est clairement abusive;
const _ABUSE_NB_AFFICHAGES_PAR_JOUR_ABUSIF_ = 10 * _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_;
// Division des seuils d'abus si une image est considérée comme suspecte
const _ABUSE_DIVISION_SEUILS_SI_SUSPECT_ = 2;

// Désactiver l'envoi d'images depuis un noeud de sortie Tor
const _TOR_DISABLE_UPLOAD_ = true;
// Désactiver l'envoi d'images au bout de x images bloquées (mettre 0 pour ne pas l'activer)
const _ABUSE_DISABLE_UPLOAD_AFTER_X_IMAGES_ = 100;

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
const _GRAIN_DE_SEL_ = 'xxx';
/**
 * FIN DES CHAMPS A COMPLETER UNIQUEMENT SI VOUS AVEZ UNE VERSION ANTERIEURE A v2.0.4
 */
// Désactivation des tests Tests TRAVIS-CI
const _PHPUNIT_ = false;

require _PATH_ . 'config/image-heberg.php';
