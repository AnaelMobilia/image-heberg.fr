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
require '../config/configV2.php';
// Vérification des droits d'accès
metaObject::checkUserAccess(utilisateurObject::levelAdmin);
//
//
//**************************************
//	/admin/curl/contact.php
//	Tentative de flood contact
//**************************************
// Donn�es du formulaire
$post_data = array(
//'fichier' => '@' . realpath($_FILES['fichier']['tmp_name']),
'email' => 'curl@flood.eu',
'message' => 'test_flood',
'_contact' => 'TRUE');

$curl1 = curl_init('http://image-heberg.fr/contact.php');				// URL de la page
curl_setopt($curl1, CURLOPT_RETURNTRANSFER, 1);							// N'affiche pas la page
curl_setopt($curl1, CURLOPT_COOKIESESSION, TRUE);						// Nouveau cookie de session
curl_setopt($curl1, CURLOPT_HEADER, TRUE);								// Affiche le contenu des headers
preg_match('/^Set-Cookie: (.*?);/m', curl_exec($curl1), $cookie);		// Ex�cute & r�cup�re le cookies
curl_close($curl1);
$cookie = $cookie[1];	// The cookie !

/*
HTTP/1.1 200 OK Date: Fri, 23 Apr 2010 02:01:32 GMT Server: Apache X-Powered-By: PHP/5.2.13 Set-Cookie: PHPSESSID=2vfqd2e565hk88ikmj5lmrcev1; path=/ Expires: Thu, 19 Nov 1981 08:52:00 GMT Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0 Pragma: no-cache Content-Length: 3253 Content-Type: text/html
*/

//echo '<hr />';

$curl = curl_init('http://image-heberg.fr/contact.php');	// URL de la page
curl_setopt($curl, CURLOPT_COOKIE, $cookie);				// Envoi le cookie
//curl_setopt($curl, CURLOPT_HEADER, TRUE);					// Affiche le contenu des headers
curl_setopt($curl, CURLOPT_POST, TRUE);						// Flag d'information : Donn�es en POST
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);			// Contenu du POST
curl_exec($curl);											// Effectue la requ�te
curl_close($curl);											// Fermeture connexion au site
?>