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
 * R?pertoire d'enregistrement des images
 */
define('__TARGET__', 'files/');
/**
 * R?pertoire d'enregistrement des miniatures
 */
define('__T_TARGET__', __TARGET__ . 'thumbs/');
/**
 * Hauteur de la miniature par d?faut (px)
 */
define('__DEFAULT_T_HEIGHT__', 140);
/**
 * Largeur de la miniature par d?faut (px)
 */
define('__DEFAULT_T_WIDTH__', 100);

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
    __PATH__ . 'includes/erreur.php' // retour_erreur(), send_mail_admin()
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