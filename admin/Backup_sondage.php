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
require '../config/configV2.php';
// Vérification des droits d'accès
metaObject::checkUserAccess(utilisateurObject::levelAdmin);
//
//
require_once ("./config/config.php");
$lang['TITLE'] = 'Votre avis.';

if (isset($_POST['submit'])) {
    $champ = '';
    $valeur = '';

    // Pour chaque valeur envoyée
    foreach ($_POST as $key => $value) {
	// Pas pour le bouton submit
	if ($key != 'submit') {
	    // Séparateur
	    if (!empty($champ)) {
		$champ .= ", ";
		$valeur .= ", ";
	    }

	    $champ .= "`" . mysql_real_escape_string($key) . "`";
	    $valeur .= "\"" . mysql_real_escape_string($value) . "\"";
	}
    }
    // Gestion de la traçabilité des apports...
    $champ .= ', `ip`, `quand`';
    $valeur .= ', "' . $_SERVER['REMOTE_ADDR'] . '", NOW()';
    
    // On prépare la requête
    $query = "INSERT INTO `sondage` ($champ) VALUES ($valeur);";
    // Et on la balance
    sql_query($query);
    $lang['CORPS'] = 'Merci de votre participation !';
} else {
    $lang['INFO'] = 'Une nouvelle version du service est en cours de d&eacute;veloppement.<br />
En tant qu\'utilisateur du service, je connais mes attentes, mais pas les v&ocirc;tres !
<br /><br />
<b>En r&eacute;pondant au sondage, vous me donnez les moyens de r&eacute;pondre &agrave; vos attentes !</b>';
    $lang['CORPS'] = '
<em><b>Vous pouvez ne r&eacute;pondre qu\'&agrave; une partie des questions si vous le souhaitez.</b></em>
</p>

<form action="" method="post">
    <h2>Evaluation du service actuel</h2>
    <div><em>Il s\'agit d\'&eacute;valuer le service tel qu\'il existe aujourd\'hui :</em></div>
    <table>
	<thead>
	    <tr>
		<td></td>
		<td>Correct</td>
		<td>Incorrect</td>
	    </tr>
	</thead>
	<tbody>
	    <tr>
		<td>Vitesse du service <em>(le site web, affichage des images)</em></td>
		<td><input type="radio" name="actuelSpeed" value="1"/></td>
		<td><input type="radio" name="actuelSpeed" value="0"/></td>
	    </tr>
	    <tr>
		<td>Les types d\'images accept&eacute;s <em>(PNG, JPG, GIF)</em></td>
		<td><input type="radio" name="actuelExt" value="1"/></td>
		<td><input type="radio" name="actuelExt" value="0k"/></td>
	    </tr>
	    <tr>
		<td>Dimensions <em>(la taille en pixel)</em> des images</td>
		<td><input type="radio" name="actuelDim" value="1"/></td>
		<td><input type="radio" name="actuelDim" value="0"/></td>
	    </tr>
	    <tr>
		<td>Poids <em>(en Mo)</em> des images</td>
		<td><input type="radio" name="actuelPoidsweight" value="1"/></td>
		<td><input type="radio" name="actuelPoidsweight" value="0"/></td>
	    </tr>
	    <tr>
		<td>Les options proposées <em>(miniatures, rotation, ...)</em></td>
		<td><input type="radio" name="actuelOptions" value1ok"/></td>
		<td><input type="radio" name="actuelOptions" value="0"/></td>
	    </tr>
	</tbody>
    </table>
    <hr />
    <h2>Nouveaut&eacute;s &agrave; apporter</h2>
    <div><em>Que pensez-vous de ces propositions ?</em></div>
    <h3>Pour l\'envoi des images</h3>
    <table>
	<thead>
	    <tr>
		<td></td>
		<td>Oui</td>
		<td>Non</td>
	    </tr>
	</thead>
	<tbody>
	    <tr>
		<td>Pouvoir envoyer des images au format BMP <em>(bitmap)</em></td>
		<td><input type="radio" name="bmp" value="1"/></td>
		<td><input type="radio" name="bmp" value="0"/></td>
	    </tr>
	    <tr>
		<td>Pouvoir envoyer plusieurs images en m&ecirc;me temps<br /><em>(plusieurs champs de choix de "fichier &agrave; envoyer")</em></td>
		<td><input type="radio" name="uploadMultiple" value="1"/></td>
		<td><input type="radio" name="uploadMultiple" value="0"/></td>
	    </tr>
	</tbody>
    </table>
    <h3>Pour les membres (personnes connect&eacute;es)</h3>
    <table>
	<thead>
	    <tr>
		<td></td>
		<td>Oui</td>
		<td>Non</td>
	    </tr>
	</thead>
	<tbody>
	    <tr>
		<td>Pouvoir "rester connect&eacute;" au service<br /><em>(vous &ecirc;tes actuellement d&eacute;connect&eacute; &agrave; la fermeture de votre navigateur)</em></td>
		<td><input type="radio" name="resterCo" value="1"/></td>
		<td><input type="radio" name="resterCo" value="0"/></td>
	    </tr>
	    <tr>
		<td>Pouvoir renommer les images une fois envoy&eacute;es<br /><em>(le nom affich&eacute; dans la liste des images)</em></td>
		<td><input type="radio" name="renamePic" value="1"/></td>
		<td><input type="radio" name="renamePic" value="0"/></td>
	    </tr>
	    <tr>
		<td>Pouvoir obtenir des statistiques sur chaque image<br /><em>(nombre d\'affichages)</em></td>
		<td><input type="radio" name="statsPic" value="1"/></td>
		<td><input type="radio" name="statsPic" value="0"/></td>
	    </tr>
	    <tr>
		<td>Pouvoir afficher une liste de miniatures dans la liste des images<br /><em>(actuellement le nom du fichier est affich&eacute;)</em></td>
		<td><input type="radio" name="thumbsListeImages" value="1"/></td>
		<td><input type="radio" name="thumbsListeImages" value="0"/></td>
	    </tr>
	    <tr>
		<td>Pouvoir regrouper vos photos dans des albums</td>
		<td><input type="radio" name="albums" value="1"/></td>
		<td><input type="radio" name="albums" value="0"/></td>
	    </tr>
	    <tr>
		<td>Pouvoir partager un album entier<br /><em>(fourniture d\'un lien &agrave; communiquer aux destinataires par vos soins)</em></td>
		<td><input type="radio" name="albumShare" value="1"/></td>
		<td><input type="radio" name="albumShare" value="0"/></td>
	    </tr>
	    <tr>
		<td>Pouvoir mettre un mot de passe sur un album partag&eacute;</td>
		<td><input type="radio" name="albumPwd" value="1"/></td>
		<td><input type="radio" name="albumPwd" value="0"/></td>
	    </tr>
	    <tr>
		<td>Pouvoir choisir plusieurs images &agrave; supprimer<br /><em>(case &agrave; cocher dans la liste des images)</em></td>
		<td><input type="radio" name="deleteCheckbox" value="1"/></td>
		<td><input type="radio" name="deleteCheckbox" value="0"/></td>
	    </tr>
	</tbody>
    </table>
    <h2>Autres id&eacute;es, suggestions, ...</h2>
    <div>
	<textarea name="divers" rows="15" cols="50"></textarea>
    </div>
    <input type="submit" name="submit" value="Envoyer" />
</form>
<p>
';
}
template('template.html', __FILE__);
?>