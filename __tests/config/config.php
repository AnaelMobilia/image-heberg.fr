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
 * CONFIGURATION FILE FOR TRAVIS
 * https://travis-ci.org/AnaelMobilia/image-heberg.fr/
 */
// DEBUG
define('_DEBUG_', TRUE);
if (_DEBUG_) {
   error_reporting(E_ALL | E_STRICT);
}
define('_TRAVIS_', TRUE);

if (!_TRAVIS_) {

   // Gestion des exceptions de l'application
   function exception_handler($exception) {
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
      $headers = 'From: ' . _MAIL_ADMIN_ . "\n";
      // Adresse de retour
      $headers .= 'Reply-To: ' . _MAIL_ADMIN_ . "\n";
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

      mail(_MAIL_ADMIN_, '[' . _URL_ . '] Erreur rencontrée', $message, $headers);
   }

   set_exception_handler('exception_handler');
}

// Nom du service
define('_SITE_NAME_', 'MonSite');

// mail admin
define('_MAIL_ADMIN_', 'john.doe@example.com');

// Répertoires
define('_REPERTOIRE_IMAGE_', 'files/');
define('_REPERTOIRE_MINIATURE_', _REPERTOIRE_IMAGE_ . 'thumbs/');
define('_REPERTOIRE_ADMIN_', 'admin/');
define('_REPERTOIRE_MEMBRE_', 'membre/');

// URL
define('_BASE_URL_', 'www.example.com/');
define('_URL_', 'http://' . _BASE_URL_);
define('_URL_HTTPS_', 'https://' . _BASE_URL_);
define('_URL_SANS_SCHEME_', '//' . _BASE_URL_);
define('_URL_ADMIN_', _URL_HTTPS_ . _REPERTOIRE_ADMIN_);
define('_URL_MEMBRE_', _URL_HTTPS_ . _REPERTOIRE_MEMBRE_);
define('_URL_IMAGES_', _URL_ . _REPERTOIRE_IMAGE_);
define('_URL_MINIATURES_', _URL_ . _REPERTOIRE_MINIATURE_);

// Système de fichiers
define('_PATH_', '/home/travis/build/AnaelMobilia/image-heberg.fr/');
define('_PATH_IMAGES_', _PATH_ . _REPERTOIRE_IMAGE_);
define('_PATH_MINIATURES_', _PATH_ . _REPERTOIRE_MINIATURE_);
define('_PATH_ADMIN_', _PATH_ . _REPERTOIRE_ADMIN_);
define('_PATH_TESTS_IMAGES_', _PATH_ . '__tests/images/');
define('_PATH_TESTS_OUTPUT_', _PATH_ . '__tests/output/');
define('_TPL_TOP_', _PATH_ . 'template/templateV2Top.php');
define('_TPL_BOTTOM_', _PATH_ . 'template/templateV2Bottom.php');

// Fonction de chargement des classes en cas de besoin
spl_autoload_register(function ($class) {
   // Code pour TRAVIS
   $charger = TRUE;

   // Code spécifique Travis : pas de chargement des classes de PHPUnit
   if (_TRAVIS_ && (strpos($class, "PHPUnit") !== FALSE || strpos($class, "Composer") !== FALSE)) {
      $charger = FALSE;
   }

   if ($charger) {
      require _PATH_ . 'classes/' . $class . '.class.php';
   }
});


// Images spécifiques
define('_IMAGE_404_', '_image_404.png');
define('_IMAGE_BAN_', '_image_banned.png');

// Salt pour les mots de passe
// Legacy - n'est plus requis !!
define('_GRAIN_DE_SEL_', '');

// BDD
define('_BDD_HOST_', 'localhost');
define('_BDD_USER_', 'root');
define('_BDD_PASS_', '');
define('_BDD_NAME_', 'imageheberg');

// Administrateur du site
define('_ADMINISTRATEUR_NOM_', 'John DOE');
define('_ADMINISTRATEUR_SITE_', '//www.example.com/');

// Hébergeur du site
define('_HEBERGEUR_NOM_', 'OVH');
define('_HEBERGEUR_SITE_', '//www.ovh.com');

// Spécifications mémoire
define('_FUDGE_FACTOR_', 1.8);
define('_IMAGE_DIMENSION_MAX_', outils::getMaxDimension());
define('_IMAGE_POIDS_MAX_', 5242880);

// Délais de conservation des images **en jours**
define('_DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_', 7);
define('_DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_', 365);

// Quota maximal du site pour l'hébergement d'images **en Go**
define('_QUOTA_MAXIMAL_IMAGES_GO_', 90);