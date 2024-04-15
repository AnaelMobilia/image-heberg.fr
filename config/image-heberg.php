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

use Throwable;

error_reporting(E_ALL | E_STRICT);
// Avoir le détail des paramètres des méthodes dans les stack traces
ini_set('zend.exception_string_param_max_len', 1000000);

if (!_PHPUNIT_) {
    /**
     * Supprime des informations sensibles du log d'erreur
     * @param string $value
     * @return string
     */
    function exception_handler_cleaner(string $value): string
    {
        return str_replace([_BDD_HOST_, _BDD_NAME_, _BDD_USER_, _BDD_PASS_, _PATH_], 'xxx', $value);
    }

    /**
     * Gestion des exceptions de l'application
     * @param Throwable $exception
     */
    function exception_handler(Throwable $exception): void
    {
        if (_DEBUG_) {
            // Afficher l'erreur en masquant les informations sensibles
            echo '<pre>';
            print_r(exception_handler_cleaner($exception->getMessage()));
            echo '<br /><br /><hr /><br />';
            print_r(exception_handler_cleaner($exception->getTraceAsString()));
            echo '</pre>';
        } else {
            echo 'Une erreur a été rencontrée';
        }

        /**
         * Envoi d'un mail avec le détail de l'erreur à l'administrateur
         */
        // Adresse expediteur
        $headers = 'From: ' . _ADMINISTRATEUR_EMAIL_;
        // Adresse de retour
        $headers .= PHP_EOL . 'Reply-To: ' . _ADMINISTRATEUR_EMAIL_;
        // Agent mail
        $headers .= PHP_EOL . 'X-Mailer: ' . _SITE_NAME_ . ' script at ' . _URL_;
        // Date
        $headers .= PHP_EOL . 'Date: ' . date('D, j M Y H:i:s +0200');
        $message = PHP_EOL . exception_handler_cleaner($exception->getMessage()) . PHP_EOL . exception_handler_cleaner($exception->getTraceAsString());
        $message .= PHP_EOL . 'URL : ' . ($_SERVER['REQUEST_URI'] ?? '');
        $message .= PHP_EOL . 'HTTP REFERER : ' . ($_SERVER['HTTP_REFERER'] ?? '');
        $message .= PHP_EOL . 'HTTP USER AGENT : ' . ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $message .= PHP_EOL . 'REMOTE ADDR : ' . $_SERVER['REMOTE_ADDR'];

        mail(_ADMINISTRATEUR_EMAIL_, '[' . _SITE_NAME_ . '] -  Erreur rencontrée', $message, $headers);
    }

    /**
     * Gestions des erreurs dans l'application
     * @param int $errno Niveau d'erreur
     * @param string $errstr Message d'erreur
     * @param string $errfile Fichier où à lieu l'erreur
     * @param int $errline Ligne concernée
     * @return void
     */
    function error_handler(int $errno, string $errstr, string $errfile, int $errline): void
    {
        $monException = new ImageHebergException();
        $monException->define($errstr, $errno, $errfile, $errline);

        exception_handler($monException);
    }

    set_exception_handler('ImageHeberg\exception_handler');
    set_error_handler('ImageHeberg\error_handler');
}

/**
 * Répertoires
 */
define('_REPERTOIRE_IMAGE_', 'files/');
define('_REPERTOIRE_MINIATURE_', _REPERTOIRE_IMAGE_ . 'thumbs/');
define('_REPERTOIRE_ADMIN_', 'admin/');
define('_REPERTOIRE_MEMBRE_', 'membre/');
define('_REPERTOIRE_CONFIG_', 'config/');

/**
 * URL
 */
define('_URL_', 'http://' . _BASE_URL_);
define('_URL_HTTPS_', 'https://' . _BASE_URL_);
define('_URL_SANS_SCHEME_', '//' . _BASE_URL_);
define('_URL_ADMIN_', _URL_HTTPS_ . _REPERTOIRE_ADMIN_);
define('_URL_MEMBRE_', _URL_HTTPS_ . _REPERTOIRE_MEMBRE_);
define('_URL_IMAGES_', _URL_ . _REPERTOIRE_IMAGE_);
define('_URL_MINIATURES_', _URL_ . _REPERTOIRE_MINIATURE_);
define('_URL_CONFIG_', _URL_HTTPS_ . _REPERTOIRE_CONFIG_);

/**
 * Système de fichiers
 */
define('_PATH_IMAGES_', _PATH_ . _REPERTOIRE_IMAGE_);
define('_PATH_MINIATURES_', _PATH_ . _REPERTOIRE_MINIATURE_);
define('_PATH_TESTS_IMAGES_', _PATH_ . '__tests/images/'); // Images pour les tests
define('_PATH_TESTS_IMAGES_A_IMPORTER_', _PATH_TESTS_IMAGES_ . 'aImporter/'); // Images devant déjà être importées avant d'exécurer les tests
define('_PATH_TESTS_OUTPUT_', _PATH_ . '__tests/output/');
define('_TPL_TOP_', _PATH_ . 'template/templateV2Top.php');
define('_TPL_BOTTOM_', _PATH_ . 'template/templateV2Bottom.php');

// Fonction de chargement des classes en cas de besoin
spl_autoload_register(static function ($class) {
    // Ne pas faire d'erreur pour les classes gérées par l'autoloade de PHPUnit
    if (_PHPUNIT_ && str_starts_with($class, 'PHPUnit\\')) {
        return;
    }

    // Suppression du namespace
    $class = str_replace('ImageHeberg\\', '', $class);
    if (str_contains($class, 'Helper')) {
        // Helper par exemple
        $file = _PATH_ . 'classes/' . $class . '.php';
    } else {
        // Classe instantiable
        $file = _PATH_ . 'classes/' . $class . '.class.php';
    }

    // Si le fichier existe...
    if (file_exists($file)) {
        require $file;
    } elseif (_PHPUNIT_) {
        echo 'spl_autoload_register() - Impossible de charger : ' . $file . ' (' . $class . ')' . PHP_EOL;
    }
});

/**
 * Paramètres divers pour les images
 */
// Gestion de la mémoire
define('_FUDGE_FACTOR_', 1.8);
define('_IMAGE_DIMENSION_MAX_', HelperImage::getMaxDimension());

// Images spécifiques
define('_IMAGE_404_', '_image_404.png');
define('_IMAGE_BAN_', '_image_banned.png');

// Dimensions des apeçus dans l'espace membre
define('_SIZE_PREVIEW_', 256);

// Types d'images gérés
define('_ACCEPTED_EXTENSIONS_', ['JPG', 'PNG', 'GIF', 'WEBP']);
define('_ACCEPTED_MIME_TYPE_', [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP]);

/**
 * Tor
 */
// URL de l'API Tor
define('_TOR_EXIT_NODE_LIST_URL_', 'https://onionoo.torproject.org/details?flag=exit');
define('_TOR_LISTE_IPV4_', _PATH_ . _REPERTOIRE_IMAGE_ . 'z_cache/ipv4.txt');
define('_TOR_LISTE_IPV6_', _PATH_ . _REPERTOIRE_IMAGE_ . 'z_cache/ipv6.txt');
