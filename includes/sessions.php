<?php
/*
* Copyright 2008-2015 Anael Mobilia
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
//	./includes/sessions.php
//	Sessions, gestion des droits
//**************************************
// note : le session_start() est ex�cut� dans /config/config.php (appel� donc pour toutes les pages dynamiques !)

/**
 * Cr�e la session
 *
 */
function session_login() {
    global $lang; // Permet l'affichage des erreurs
    //--------------------------------------
    //	VERIFICATION DES DONNEES
    //--------------------------------------
    // Variable non d�finie
    if (!isset($_POST['login']) || !isset($_POST['pass'])) {
        erreur('HACK', 'membre_bad_login');  // hack
    }
    // Login in a-zA-Z0-9
    if (!preg_match(__REG_EXP_LOGIN__, $_POST['login'])) {
        erreur('HACK', 'membre_bad_login');  // hack
    }
    // Variable manquante
    if (empty($_POST['login']) || empty($_POST['pass'])) {
        $lang['CORPS'] .= '<p>Merci de saisir votre login ET votre password.</p>';
    }
    // Login existant en db
    if (($user_pass = sql_query('SELECT `pass` from `membres` WHERE `login` = "' . mysql_real_escape_string($_POST['login']) . '"')) == NULL) {
        $lang['CORPS'] .= '<p>Erreur dans vos identifiants.</p>';
    }
    // Correspondance des passwords
    if (hash('sha256', __GRAIN_DE_SEL__ . $_POST['pass']) != $user_pass) {
        $lang['CORPS'] .= '<p>Erreur dans vos identifiants.</p>';
    }
    unset($user_pass); // effacement par mesure de pr�caution
    //--------------------------------------
    //	OUVERTURE DE LA SESSION
    //--------------------------------------
    if ($lang['CORPS'] == '') { // Pas d'erreurs dans les v�rifications pr�alables
        // chargement profil utilisateur
        $user = sql_query('SELECT `pk_membres`, `redirect_upload`, `tpl`, `lvl` from `membres` WHERE `login` = "' . mysql_real_escape_string($_POST['login']) . '"');
        // Log de la connexion
        sql_query('INSERT INTO `login` (`ip_login`, `date_login`, `pk_membres`) VALUES ("' . mysql_real_escape_string($_SERVER['REMOTE_ADDR']) . '", NOW(), ' . mysql_real_escape_string($user['pk_membres']) . ')');

        $_SESSION['connected'] = TRUE;        // Etat de la connexion
        $_SESSION['user_id'] = $user['pk_membres'];    // ID utilisateur
        $_SESSION['user'] = $_POST['login'];     // Pseudo
        $_SESSION['redirect_upload'] = $user['redirect_upload'];   // Redirection vers l'envoi d'image d�s connexion ?
        $_SESSION['tpl'] = $user['tpl'];      // Template voulu
        $_SESSION['level'] = $user['lvl'];      // user < admin
    }
}

/**
 * D�truit la session
 *
 */
function session_logout() {
    $_SESSION = array();          // Destruction des donn�es de $_SESSION
    if (isset($_COOKIE[session_name()])) {      // Expiration du cookie
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();           // Destruction de la session
}

/**
 * G�n�ration des liens des interfaces
 *
 * @return unknown
 */
function session_state() {
    // Membre connect�
    if (isset($_SESSION['connected'])) {
        // Membre
        $retour = '<a href="' . __URL_SITE__ . __MEMBRE_PATH__ . 'mesImages.php"><span style="font-style: italic">' . $_SESSION['user'] . '</span></a>';
        if ($_SESSION['level'] == 'admin') { // Admin
            $retour .= ' - <a href="' . __URL_SITE__ . __ADMIN_PATH__ . '"><span style="font-style: italic">Admin</span></a>';
        }
    }
    // Utilisateur non connect�
    else {                // Visiteur
        $retour = '<a href="' . __URL_SITE__ . __MEMBRE_PATH__ . 'connexionCompte.php"><span style="font-style: italic">Se connecter</span></a>';
    }
    return $retour;
}

/**
 * V�rifie les droits de l'utilisateur pour acc�der � une page {guest / membre / admin}
 *
 * @param string $level [optional] : niveau requis pour la page
 * @param string $file [optional] : fichier
 */
function auth($level = 'admin', $file = 'Page inconnue') {
    $allowed = FALSE;    // Refus par d�faut
    if (isset($_SESSION['level'])) {
        switch ($_SESSION['level']) {
            case 'admin':
                $allowed = TRUE; // Admin : tous les droits
                break;

            case 'member':
                if ($level == 'member' || $level == 'guest')
                    $allowed = TRUE; // Membre : poss�de �galement les droits guest
                break;

            case 'guest':
                if ($level == 'guest')
                    $allowed = TRUE; // Visiteur : droits de base
                break;

            default:    // Cas impossible
                retour_erreur('appel fonction auth(' . $level . ') avec $_SESSION[\'level\'] = ' . $_SESSION['level'], __FILE__, 'die', TRUE);
        }
    }

    if (!$allowed) {     // Droit refus�
        retour_erreur('Vous ne disposez pas des droits requis pour cette page !', $file, 'die', FALSE); //you loose ;-)
    }
}

?>