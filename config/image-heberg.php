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

namespace ImageHeberg;

use Exception;

if (_DEBUG_) {
    error_reporting(E_ALL | E_STRICT);
}
if (!_PHPUNIT_) {

    /**
     * Gestion des exceptions de l'application
     * @param Exception $exception
     */
    function exception_handler($exception)
    {
        /* @var $exception Exception */
        if (_DEBUG_) {
            echo '<pre>';
            print_r($exception->getMessage());
            echo '<br /><br /><hr /><br />';
            print_r($exception->getTraceAsString());
            echo '</pre>';
        } else {
            echo 'Une erreur a été rencontrée';
        }

        /**
         * Envoi d'un mail avec le détail de l'erreur à l'administrateur
         */
        // Adresse expediteur
        $headers = 'From: ' . _ADMINISTRATEUR_EMAIL_ . "\n";
        // Adresse de retour
        $headers .= 'Reply-To: ' . _ADMINISTRATEUR_EMAIL_ . "\n";
        // Agent mail
        $headers .= 'X-Mailer: ' . _SITE_NAME_ . ' script at ' . _URL_ . "\n";
        // Date
        $headers .= 'Date: ' . date('D, j M Y H:i:s +0200') . "\n";
        $message = $exception->getMessage() . "\r\n" . $exception->getTraceAsString();
        $message .= "\r\nURL : " . $_SERVER['REQUEST_URI'];
        if (isset($_SERVER['HTTP_REFERER'])) {
            $message .= "\r\nHTTP REFERER : " . $_SERVER['HTTP_REFERER'];
        }
        $message .= "\r\nHTTP USER AGENT : " . $_SERVER['HTTP_USER_AGENT'];
        $message .= "\r\nREMOTE ADDR : " . $_SERVER['REMOTE_ADDR'];

        mail(_ADMINISTRATEUR_EMAIL_, '[' . _URL_ . '] Erreur rencontrée', $message, $headers);
    }
    set_exception_handler('ImageHeberg\exception_handler');
}

// Répertoires
define('_REPERTOIRE_IMAGE_', 'files/');
define('_REPERTOIRE_MINIATURE_', _REPERTOIRE_IMAGE_ . 'thumbs/');
define('_REPERTOIRE_ADMIN_', 'admin/');
define('_REPERTOIRE_MEMBRE_', 'membre/');
define('_REPERTOIRE_CONFIG_', 'config/');

// URL
define('_URL_', 'http://' . _BASE_URL_);
define('_URL_HTTPS_', 'https://' . _BASE_URL_);
define('_URL_SANS_SCHEME_', '//' . _BASE_URL_);
define('_URL_ADMIN_', _URL_HTTPS_ . _REPERTOIRE_ADMIN_);
define('_URL_MEMBRE_', _URL_HTTPS_ . _REPERTOIRE_MEMBRE_);
define('_URL_IMAGES_', _URL_ . _REPERTOIRE_IMAGE_);
define('_URL_MINIATURES_', _URL_ . _REPERTOIRE_MINIATURE_);
define('_URL_CONFIG_', _URL_HTTPS_ . _REPERTOIRE_CONFIG_);

// Système de fichiers
define('_PATH_IMAGES_', _PATH_ . _REPERTOIRE_IMAGE_);
define('_PATH_MINIATURES_', _PATH_ . _REPERTOIRE_MINIATURE_);
define('_PATH_ADMIN_', _PATH_ . _REPERTOIRE_ADMIN_);
define('_PATH_TESTS_IMAGES_', _PATH_ . '__tests/images/');
define('_PATH_TESTS_OUTPUT_', _PATH_ . '__tests/output/');
define('_TPL_TOP_', _PATH_ . 'template/templateV2Top.php');
define('_TPL_BOTTOM_', _PATH_ . 'template/templateV2Bottom.php');

// Fonction de chargement des classes en cas de besoin
spl_autoload_register(function ($class) {
    // Suppression du namespace
    $class = str_replace('ImageHeberg\\', "", $class);
    $file = _PATH_ . 'classes/' . $class . '.class.php';

    // Si le fichier existe...
    if (file_exists($file)) {
        require $file;
    }
});

// Gestion de la mémoire
define('_FUDGE_FACTOR_', 1.8);
define('_IMAGE_DIMENSION_MAX_', Outils::getMaxDimension());

// Images spécifiques
define('_IMAGE_404_', '_image_404.png');
define('_IMAGE_BAN_', '_image_banned.png');
