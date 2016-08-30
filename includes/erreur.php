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
//	./includes/erreur.php
//	Librairie - gestion des erreurs
//**************************************
/*
 Si erreur lors envoi fichier, dans upload.php appeler le "trace" / spam.php puis erreur()

 function erreur()
 db : id / name / type / description / count / last_date / last_ip
 + where (contact, upload, stats, display, membre_*, admin_*)
 */

/**
 * G�re les erreurs du site
 *
 * @param string $niveau_erreur : type d'erreur (Hack, Flood, Fichier)
 * @param string $info_erreur : soit le nom de l'erreur en BDD, soit le message d'erreur a afficher
 */
function erreur($niveau_erreur, $info_erreur) {
	// Donn�es de langue
	global $lang;

	// Si administrateur : affichage de ce qui serait fait. Fin.
	if (isset($_SESSION['connected']) && $_SESSION['level'] == 'admin') {
		// Gestion de l'affichage dans le template
		$lang['PRE_INFO_ADMIN'] = '<div style="background-color: Aqua">';
		$lang['POST_INFO_ADMIN'] = '</div>';
		// Donn�es � afficher (requ�tes effectu�es)
		$lang['INFO_ADMIN'] .= 'D&eacute;tection du ' . $niveau_erreur . ' ' . $info_erreur . '<br />';
	}
	// Cas d'un HACK (Action faite volontairement (avec comme but de nuire))
	elseif ($niveau_erreur == 'HACK') {
		// Enregistrement de l'erreur en BDD
		sql_query('UPDATE `hacks` SET `count`=`count`+1, `last_date`=NOW(), `last_ip`="' . mysql_real_escape_string($_SERVER['REMOTE_ADDR']) . '" WHERE `name`="' . mysql_real_escape_string($info_erreur) . '"');
		// Redirection sur la page d'accueil
		//	header('Location: ' . __URL_SITE__);
		// Arr�t du script
		//	die();
	}
	//Cas d'un FLOOD (Action anormale pouvant �tre faite par erreur)
	elseif ($niveau_erreur == 'FLOOD') {
		// Enregistrement de l'erreur en BDD
		// on r�cup�re l'id de l'erreur en question
		$id_erreur = sql_query('SELECT `id` FROM `liste_erreurs` WHERE `name` = "' . mysql_real_escape_string($info_erreur) . '"');
		// On enregistre l'erreur
		sql_query('INSERT INTO `erreurs`(`id_erreur`, `date_erreur`, `ip`) VALUES (' . $id_erreur . ', NOW(), "' . mysql_real_escape_string($_SERVER['REMOTE_ADDR']) . '")');
		// Partie � d�gager (flood isn't hack and inverse)
		// Redirection sur la page d'accueil
		//		header('Location: ' . __URL_SITE__);
		// Arr�t du script
		//		die();
	}
	//Sinon Si type = Fichier
	//	-> Enregistre le probl�me
	//	-> Affiche un message d'erreur explicite
	//			ajout d'une balise $tpl_erreur_debut
	//			ajout d'une balise $tpl_erreur_contenu <- on ajoute les messages d'erreurs ici si encore des erreurs apr�s
	//			ajout d'une balise $tpl_erreur_fin
	//Sinon Si type = SQL
	//	-> Enregistre le probl�me
	//	-> Affiche un message d'erreur explicite
	//	-> Avertir l'admin
	//Si image404 ou miniature404 -> type sp�cial d'erreur d'ou retour d'une image.
	//-> Fin du script
}

/**
 * Affiche un message d'erreur (mail, arr�t du script)
 *
 * @param string $display : message affich�
 * @param string $file [optional] : fichier appelant
 * @param string $level [optional] : niveau d'erreur {die / warning}
 * @param boolean $silent [optional] : non affichage du message [FALSE]
 * @param boolean $mail [optional] : envoi par mail [TRUE]
 */
function retour_erreur($display, $file = 'Page d\'erreur inconnue', $level = 'die', $silent = FALSE, $mail = TRUE) {
	global $lang;
	//template de page
	if ($mail)//envoi d'un mail ?
	{
		$sujet = '[' . __URL_SITE__ . '] Erreur : ' . $level;
		$corps = $file . "\n" . date('D, j M Y H:i:s +0200') . "\n" . $display . "\n" . $_SERVER['REMOTE_ADDR'] . ' --> ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
		send_mail_admin($sujet, html_entity_decode($corps));
	}

	if (!$silent)//Affichage de l'erreur ?
	{
		//$lang['CORPS'] = $display;
                    ?>
        <div class = "alert alert-danger"><?=$display?></div>
        <?php
        require _TPL_BOTTOM_;
	}

	if ($level == 'die')//erreur fatale -> stop
	{
		if (!$silent)//si erreur invisible, on affiche pas le footer !
		{
//			template('template.html', $lang, __FILE__);
		}
		die();
	}
}

/**
 * Envoit un email � l'admin
 *
 * @param string $subject : sujet du mail
 * @param string $message : corps du mail
 */
function send_mail_admin($subject, $message) {
	// Adresse exp�diteur
	$headers = 'From: ' . __MAIL_ADMIN__ . "\n";
	// Adresse de réponse TODO : définir à l'email expéditeur !
	$headers .= 'Reply-To: ' . __MAIL_ADMIN__ . "\n";
	// Agent Mail
	$headers .= 'X-Mailer: Anael\'s script At Image-heberg' . "\n";
	// @IP
	$headers .= 'User-IP: ' . $_SERVER['REMOTE_ADDR'] . "\n";
	// Date
	$headers .= 'Date: ' . date('D, j M Y H:i:s +0200') . "\r\n";
	// Encodage
	$headers .= 'Content-type: text/plain; charset=UTF-8';

	mail(__MAIL_ADMIN__, $subject, $message, $headers);
	//on envoie le mail
}

/**
 * Enregistre en DB une tentative de hack + renvoit sur la page (accueil)
 *
 * @param string $type : nom de la clef en DB
 * @param string $page [optional] : page sur laquelle le visiteur est redirig� [accueil]
 */
function hack($type, $page = __URL_SITE__) {
	erreur('HACK', $type);
}

/**
 * Enregistre en DB une tentative de flood
 *
 * @param string $type : nom de la clef en DB
 */
function flood($type) {
	erreur('FLOOD', $type);
}
?>
