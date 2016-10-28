<?php
/*
 * Copyright 2008-2016 Anael Mobilia
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
//**************************************
//	./config/config.php
//	Configuration, appel des librairies
//**************************************
//--------------------------------------
//	DEFINITION DES CONSTANTES
//--------------------------------------
/**
 * Activation du mode debug
 */
define('__DEBUG__', TRUE);
/**
 * Activation du mode debug - SQL
 */
define('__DEBUG_SQL__', TRUE);
/**
 * URL du site
 */
define('__URL_SITE__', 'http://www.image-heberg.fr/');
/**
 * @ mail de l'administrateur
 */
define('__MAIL_ADMIN__', 'john.doe@example.com');
/**
 * Grain de sel pour l'encryptage des mots de passe des utilisateur
 */
define('__GRAIN_DE_SEL__', 'xxx');

//--------------------------------------
//	INITIALISATION DES SESSIONS
//--------------------------------------
//if(! defined('__NO_SESSION__'))							// session_start <-> 1 header envoy?. D?sactiv? pour l'affichage des images
//{
//	session_start();
//}
if (__DEBUG__) {           // Dur?e d'ex?cution du script
    $time_start = microtime(TRUE); //float
}
if (!isset($_SESSION['level'])) {       // Utilisateur non authentifi? <-> invit?
    $_SESSION['level'] = 'guest';
}

//--------------------------------------
//	DEFINITION DES VARIABLES
//--------------------------------------
/**
 * Emplacement physique
 */
define('__PATH__', '/path/to/image-heberg.fr/');
/**
 * R?pertoire du template
 */
define('__TPL_PATH__', 'template/');
/**
 * R?pertoire zone admin
 */
define('__ADMIN_PATH__', 'admin/');
/**
 * R?pertoire zone membre
 */
define('__MEMBRE_PATH__', 'membre/');
/**
 * R?pertoire d'enregistrement des images
 */
define('__TARGET__', 'files/');
/**
 * R?pertoire d'enregistrement des miniatures
 */
define('__T_TARGET__', __TARGET__ . 'thumbs/');
/**
 * Extensions d'images autoris?es
 */
define('__EXTENSIONS_OK__', 'jpg, gif, png');
/**
 * Taille max de l'image (octets)
 */
define('__MAX_SIZE__', 5242880);
/**
 * Largeur max de l'image (px)
 */
define('__WIDTH_MAX__', 3500);
/**
 * Hauteur max de l'image (px)
 */
define('__HEIGHT_MAX__', 3500);
/**
 * Hauteur de la miniature par d?faut (px)
 */
define('__DEFAULT_T_HEIGHT__', 140);
/**
 * Largeur de la miniature par d?faut (px)
 */
define('__DEFAULT_T_WIDTH__', 100);
/**
 * Classe de caract?res autoris?s pour les logins
 */
define('__REG_EXP_LOGIN__', '#^[a-zA-Z0-9]+$#');

// Connexion SQL
$hote = 'xxx';
$base = 'xxx';
$user = 'xxx';
$pass = 'xxx';

//--------------------------------------
//	CHARGEMENT DES LIBRAIRIES
//--------------------------------------
$library = array(// liste des librairies
    __PATH__ . 'includes/sql.php', // sql_connect(), sql_query(), sql_close()
    __PATH__ . 'includes/erreur.php', // retour_erreur(), send_mail_admin()
    __PATH__ . 'includes/spam.php', // cadeau pour les spammeurs - ouvre connexion SQL !
    __PATH__ . 'includes/pictures.php'  // filename_serialize(), is_allowed_type(), is_picture()
);

foreach ($library as $load_lib) {
    if (@is_readable($load_lib)) {   // la librairie est-elle lisible ?
        require_once($load_lib);
    } else {        // cas d'erreur. Pr?venir.
        mail(__MAIL_ADMIN__, '[' . __URL_SITE__ . '] Config.php', 'erreur is_readable(' . $load_lib . ')');
        die('Une erreur &agrave; &eacute;t&eacute; rencontr&eacute;e.<br />L\'administrateur &agrave; &eacute;t&eacute; averti.');
    }
}
?>