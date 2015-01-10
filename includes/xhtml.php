<?php
/*
* Copyright 2008-2015 Anael Mobilia
*
* This file is part of NextINpact-Unofficial.
*
* NextINpact-Unofficial is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextINpact-Unofficial is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextINpact-Unofficial. If not, see <http://www.gnu.org/licenses/>
*/
//**************************************
//	./includes/xhtml.php
//	Librairie - Rendu HTML
//**************************************
/**
 * Affiche un fichier de template en int�grant les entit�s contenues dans $display
 *
 * @param string $display : nom du fichier de template
 * @param string $from : fichier appelant
 */
function template($display, $from) {
	global $lang;
	// variables � afficher

	if (!$template = file_get_contents(__PATH__ . __TPL_PATH__ . $display))//lecture du fichier template.html
	{
		retour_erreur('Erreur &agrave; la lecture du fichier ' . __PATH__ . __TPL_PATH__ . $display, $from, 'template', TRUE);
		//mail d'alerte
	}

	// Gestion template : si pas d�fini, on prend le par d�faut !
	if (isset($_SESSION['tpl'])) {
		$tpl = $_SESSION['tpl'];
	}
	else {
		$tpl = 'vert_original';
	}

	if ($tpl == 'vert_original') {// template original
		$lang['TPL_VERT'] = '';
		$lang['TPL_LUKASSS'] = 'alternate ';
	}
	elseif ($tpl == 'lukass') {// nouveau template
		$lang['TPL_VERT'] = 'alternate ';
		$lang['TPL_LUKASSS'] = '';
	}

	if (__DEBUG__)//temps d'ex�cution de la page
	{
		global $time_start;
		$lang['DEBUG'] = duree_exec($time_start);		// durée d'execution
		if (stristr($_SERVER['REMOTE_ADDR'], '.')) {	// connectivité
			$ip = 'IPv4';
		}
		else {
			$ip = 'IPv6';
		}
		$lang['DEBUG'] .= ' - ' . $ip;

	}

	echo parser_recursif($template);
	//sql_close();		// Fin du script, fermeture connexion SQL
}

/**
 * Remplace les expressions entre {{}} par leur valeur
 *
 * @param string $template : ??
 * @return string : ??
 */
function parser_recursif($template) {
	global $lang;
	//items � remplacer par une valeur textuelle d�finie

	if (is_array($template))//si on a un tableau en input, c'est qu'on est en callback depuis la fonction !
	{
		$template = (isset($lang[$template[1]]) ? $lang[$template[1]] : "");
	}
	return preg_replace_callback('#{{(.+)}}#U', 'parser_recursif', $template);
	//renvoi la partie comprise entre les ()
	/*preg_replace_callback() retourne un tableau si le param�tre subject est un tableau, ou, sinon, une cha�ne de caract�res.
	 Si une erreur survient, la valeur retourn�e sera NULL.
	 Si des correspondances sont trouv�es, le nouveau sujet sera retourn�, sinon le param�tre subject sera retourn�, inchang�. */
}
?>