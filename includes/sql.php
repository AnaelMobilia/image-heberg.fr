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
//	./includes/sql.php
//	Librairie - Lien MySQL
//**************************************
/**
 * Ouvre une connexion � la base de donn�es
 *
 */
function sql_connect()
{
	global $hote, $base, $user, $pass;	//param�tres de connexion

// 2013-06-18 - migration chez gandi... et ca ping par défaut mais ca ne matche sur rien !
//	if(!@mysql_ping())	//Une connexion est d�j� ouverte ?
//	{
		if(!@mysql_connect($hote, $user, $pass))	//Ouverture de la connexion SQL
		{	//si erreur...
			send_mail_admin('[' . __URL_SITE__ . '] SQL_Connect', 'mysql_connect(' . $hote . ', $user, $pass);' . "\n" . mysql_error());
			retour_erreur('Une erreur &agrave; &eacute;t&eacute; rencontr&eacute;e &agrave; la connexion &agrave; notre base de donn&eacute;es. L\'administrateur &agrave; &eacute;t&eacute; averti.</p>', __FILE__, 'die', FALSE);
		}
		if(!@mysql_select_db($base))	//Selection de la base
		{	//si erreur...
			send_mail_admin('[' . __URL_SITE__ . '] SQL_Connect', 'mysql_select_db(' . $base . ')' . "\n" . mysql_error());
			retour_erreur('Une erreur &agrave; &eacute;t&eacute; rencontr&eacute;e &agrave; la connexion &agrave; notre base de donn&eacute;es. L\'administrateur &agrave; &eacute;t&eacute; averti.</p>', __FILE__, 'die', FALSE);
		}
//	}
}

/**
 * Execute une requ�te SQL
 *
 * @param string $query : requ�te SQL
 * @return string : r�sultat de la requ�te
 */
function sql_query($query)
{
	// Debug
	if(__DEBUG_SQL__ && isset($_SESSION['connected']) && $_SESSION['level'] == 'admin') {
		global $lang;
		// Gestion de l'affichage dans le template
		$lang['PRE_DEBUG_SQL'] = '<div style="background-color: Aquamarine">';
		$lang['POST_DEBUG_SQL'] = '</div>';
		// Donn�es � afficher (requ�tes effectu�es)
		$lang['DEBUG_SQL'] .= $query . "<br />";
	}

	if(!@mysql_ping())	//Y a-t-il d�j� une connexion ouverte?
	{
		sql_connect();
	}

	if(!$result_sql = mysql_query($query))	//Ex�cution de la requ�te
	{	//si erreur...
		send_mail_admin('[' . __URL_SITE__ . '] SQL_Query', $query . "\n" . mysql_error());
		retour_erreur('Une erreur avec la base de donn&eacute;es &agrave; &eacute;t&eacute; rencontr&eacute;e. L\'administrateur &agrave; &eacute;t&eacute; averti.</p>', __FILE__, 'die', FALSE);
	}
	else	//Requ�te ex�cut�e sans erreurs
	{
		if(@is_resource($result_sql)) //Si Ressource => pr�sence de r�sultats � afficher
		{
			if(@mysql_num_rows($result_sql) == 1)	// 1 ligne de r�sultat (1 -> n attributs dans 1 ligne !)
			{
				$result = @mysql_fetch_array($result_sql, MYSQL_ASSOC);
				if(count($result) == 1)	//1 seule ligne : retour direct
				{
					$retour = $result[mysql_field_name($result_sql, 0)];	//r�cup�re le nom du champ retourn�, sa valeur et la stocke dans la variable de retour
				}
				else	//plusieurs attributs : retour sous la forme d'un tableau � 1 dimension !
				{
					foreach($result as $key => $value)
					{
						$retour[$key] = $value;
					}
				}
			}
			else	//plusieurs retours, tableau compos� : [[ id[] , nom[] ]]
			{
				$id = 0;
				while($result = @mysql_fetch_array($result_sql, MYSQL_ASSOC))	//indice du tableau => 'id'..., MYSQL_ASSOC
				{
					foreach($result as $key => $value)	//Nom_de_la_clef => Sa_valeur
					{
						$retour[$id][$key] = $value;
					}
					$id++;
				}
			}
			@mysql_free_result($result_sql);	//on lib�re la m�moire :-)
		}
		if(!isset($retour))	//aucun r�sultat
		{
			$retour = NULL;
		}
		return $retour;	//on retourne le r�sultat
	}
	//En cas d'erreur le script appelle la fonction d'erreur >> pas de fin du script :-)
}

/**
 * Ferme la connexion � la DB
 *
 */
function sql_close()
{
	if(!@mysql_close())	//Fermeture de la connexion SQL
	{	//si erreur...
		send_mail_admin('[' . __URL_SITE__ . '] SQL_Close', 'mysql_close();' . "\n" . mysql_error());
		retour_erreur('Une erreur &agrave; &eacute;t&eacute; rencontr&eacute;e &agrave; la connexion &agrave; notre base de donn&eacute;es. L\'administrateur &agrave; &eacute;t&eacute; averti.</p>', __FILE__, 'warning');
	}
}
?>
