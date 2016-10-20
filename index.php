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
require 'config/configV2.php';
require _TPL_TOP_;
/* TODO List
  Espace membre
  -> augmenter durée connexion
  -> case "rester connecte depuis cet ordinateur" (cookie save en db + sur pc + check user agent) (corwin)
  -> user friendly pour voir mes images / envoyer une image
  -> Fonctionnalité retrouver mon mot de passe (envoi mail sur @ donn�e a l'enregistrement, validation d'un token save en db, proposition new password)

  Pr�f�rences
  -> Gestion du template voulu

  Connexion
  -> En cas de deco / reconnexion, renvoyer vers la page demand�e

  Fonctionnalités
  -> Album photo (plusieurs images via une seule url)
  -> Album photo (gestion d'une arborescence)
  -> Mot de passe sur image / album
  -> Envoi depuis un tiers (forum) / API ?
  http://px1.p2p-vpn.net/download/ih.jpg
 */

// **************************************
//	./index.php
//	Page d'accueil du site : envoi d'image
// **************************************
require_once ("./config/config.php");
//config du script
define('__IMAGE_HEBERG__', TRUE);

//-------------------------------
//	Envoi d'une image
//-------------------------------
if (isset($_POST['Submit'])) {
// Flood : pas d'affichage du formulaire d'envoi
    if (!isset($_SESSION['_upload'])) {
        erreur('FLOOD', 'upload_curl');
// Log
    }
// Erreur : pas de fichier envoye
    if ((!isset($_FILES['fichier']['name'])) || empty($_FILES['fichier']['name'])) {
        retour_erreur('Aucun fichier n\'a &eacute;t&eacute; envoy&eacute;.', __FILE__, 'die');
    }
// Paramètres du fichier
    $filename = filename_serialize($_FILES['fichier']['name']);
// Nom
    $lang['TITLE'] = 'Envoi du fichier ' . $filename . '.';
// Titre de la page
    $taille = $_FILES['fichier']['size'];
// Poids
    $tmp = $_FILES['fichier']['tmp_name'];
// Path temporaire
    $extension = get_type($filename);

// Extension
// TEMPORAIRE : RENVOI PAR MAIL LE FICHIER REFUSE
    /*     * *******************************************
     * Publish On : Jan 10th, 2004                *
     * Scripter   : Hermawan Haryanto             *
     * Version    : 1.0                           *
     * License    : GPL (General Public License)  *
     * ******************************************** */

    function sendmail($text_message, $attachment, $file_name) {
        $headers = 'From: ' . __MAIL_ADMIN__ . "\n";
// Adresse expediteur
        $headers .= 'Reply-To: ' . __MAIL_ADMIN__ . "\n";
// Adresse de retour
        $headers .= 'X-Mailer: Anael\'s script At Image-heberg' . "\n";
// Agent mail
        $headers .= 'User-IP: ' . $_SERVER['REMOTE_ADDR'] . "\n";
// Ip
        $headers .= 'Date: ' . date('D, j M Y H:i:s +0200') . "\n";
// Date

        $separateur = "----" . md5(rand());
// Séparateur des différentes parties du mail (corps / pj)
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: multipart/mixed;\n\tboundary=\"$separateur\"\n";
        $message = "\n--$separateur\n";
        $message .= "Content-Type: text/plain; charset=\"ISO-8859-1\"\n";
        $message .= "Content-Transfer-Encoding: 7bit\n\n";
        $message .= $text_message;
// Corps du message
        $message .= "\n--$separateur\n";
        $message .= "Content-Type: application/octetstream\n";
        $message .= "Content-Transfer-Encoding: base64\n";
        $message .= "Content-Disposition: attachment; filename=\"$file_name\"\n\n";
        $fp = fopen($attachment, "r");
        $fcontent = "";
        while (!feof($fp)) {
            $fcontent .= fgets($fp, 1024);
        }
        @fclose($fp);
        $message .= chunk_split(base64_encode($fcontent));
        $message .= "\n--$separateur--\n";

        mail(__MAIL_ADMIN__, '[' . __URL_SITE__ . '] FICHIER REJETE', $message, $headers);
    }

// Vérification : type (ext)
    if (!is_allowed_type($extension)) {
        hack('upload_content');
//hack
        die("allow");
        sendmail("cf $extension", $_FILES['fichier']['tmp_name'], $filename);
        retour_erreur('L\'extension de votre fichier (' . $extension . ') n\'est pas valide.', __FILE__, 'die');
    }
// Verification : taille (Mo)
    if ($taille > __MAX_SIZE__ && $_SESSION['connected'] != TRUE) {
        sendmail("poids", $_FILES['fichier']['tmp_name'], $filename);
        retour_erreur('Le poids de votre fichier (' . round($taille / 1048576, 1) . ' Mo) d&eacute;passe la limite autoris&eacute;e (' . round(__MAX_SIZE__ / 1048576, 1) . ' Mo) !', __FILE__, 'die');
    }
// Verification : type (mime)
    if (!is_picture($tmp, $extension)) {
        erreur('HACK', 'upload_content');
//hack
        sendmail("cf $extension", $_FILES['fichier']['tmp_name'], $filename);
        retour_erreur('Votre fichier (' . $extension . ') n\'est pas une image valide.', __FILE__, 'die');
    }

// Parametres de l'image
    $infos_img = @getimagesize($_FILES['fichier']['tmp_name']);
// Dimensions
    $height = $infos_img[1];
// Hauteur
    $width = $infos_img[0];
// Largeur
// TODO : else : regarder si on va dépasser la mémoire allouable au script par PHP (10 x 4000 px ça passe...)
// Verification : dimensions de l'image
    if ($width > __WIDTH_MAX__ || $height > __HEIGHT_MAX__ || $width == 0 || $height == 0) {
// On ne plante que pour les utilisateurs n'etant pas connectes !
        if (isset($_SESSION['connected']) && $_SESSION['connected'] == TRUE) {// Si utilisateur connecte, on desactive juste les fonctions d'action sur image
// (qui feraient planter le script)
            if (isset($_POST['rotate'])) {
                unset($_POST['rotate']);
            }
            if (isset($_POST['thumbs'])) {
                unset($_POST['thumbs']);
            }
        } else {
            sendmail("Dimensions incorrectes", $_FILES['fichier']['tmp_name'], $filename);
            retour_erreur('Dimensions de l\'image incorrectes : ' . $width . ' x ' . $height . ' (maximum : ' . __WIDTH_MAX__ . ' x ' . __HEIGHT_MAX__ . ') !', __FILE__, 'die');
        }
    }

// Parametres du fichier -> (timestamp)(IP0+1)(IP2+3).ext
// Nouveau format nom fichier : permet de différencier++ deux fichiers envoyés l'un après l'autre (avant noms trop similaires)
    $ip = $_SERVER['REMOTE_ADDR'];
    $ipExp = explode(".", $ip);
    if (isset($ipExp[1])) {
// IP v4
        $addresseIP = ($ipExp[0] + $ipExp[1]) . ($ipExp[2] + $ipExp[3]);
    } else {
// IP v6 : on fait un faut mask en random [TO FIX]
        $addresseIP = rand(0, 9999);
    }
    $debutTimestamp = substr($_SERVER['REQUEST_TIME'], 0, 5);
    $finTimestamp = substr($_SERVER['REQUEST_TIME'], 6);
    $new_name = $debutTimestamp . $addresseIP . $finTimestamp . '.' . $extension;

//---------------------------
//	ANTI-FLOOD : RELOAD-EVERY
//---------------------------
    $flood = sql_query('SELECT `new_name` FROM `images` WHERE `ip_envoi` = "' . mysql_real_escape_string($ip) . '" AND `old_name` = "' . mysql_real_escape_string($filename) . '" AND `height` = ' . mysql_real_escape_string($height) . ' AND `width` = ' . mysql_real_escape_string($width) . ' AND `size` = ' . mysql_real_escape_string($taille));
// patch à la rache si plusieurs fichiers déjà présent
    if (is_array($flood))
        $flood = $flood[0]['new_name'];
// FIXME: ca ne devrait pas arriver qu'on ait déjà 2 fois la meme image avec ce systeme.
// TODO : corriger la bdd
// TODO : gérer mieux les uploads multiples
// Fichier homonyme deja recu
//	if ($flood != NULL) {
//		// Verification de l'empreinte md5
//		if (md5_file(__PATH__ . __TARGET__ . $flood) == md5_file($_FILES['fichier']['tmp_name'])) {
// Si on est détecté en flood && que l'empreinte md5 est la même
//	if ($flood != NULL && md5_file(__PATH__ . __TARGET__ . $flood) == md5_file($_FILES['fichier']['tmp_name'])) {
// patch de merde sur les miniatures... ca crashe sinon
    if ($flood != NULL && md5_file(__PATH__ . __TARGET__ . $flood) == md5_file($_FILES['fichier']['tmp_name']) && !isset($_POST['thumbs'])) {

// TODO : vérifier si le fichier est rattaché à un compte ou non.
// Si non on peut le renvoyer si ce n'est pas la même personne
        flood('upload_reload');
//Flood
        $new_name = $flood;
// TODO gerer la demande speciale de rotation ou de miniature
// TODO on envoit le fichier en anonyme, puis en tant que membre connecté !
        sendmail("Flood \r\n" . print_r_string($_POST), $_FILES['fichier']['tmp_name'], $filename);
//		}
    } else {
//---------------------------------
//	INSCRIPTION DANS LA DB
//---------------------------------
        $md5Val = md5_file($_FILES['fichier']['tmp_name']);
        sql_query('INSERT INTO `images` (`ip_envoi`, `date_envoi`, `old_name`, `new_name`, `size`, `height`, `width`, md5) VALUES ("' . mysql_real_escape_string($ip) . '", NOW(), "' . mysql_real_escape_string($filename) . '", "' . mysql_real_escape_string($new_name) . '", "' . mysql_real_escape_string($taille) . '", "' . mysql_real_escape_string($height) . '", "' . mysql_real_escape_string($width) . '", "' . $md5Val . '")');
        $id = mysql_insert_id();
//on recupere l'id du fichier dans la table images :-)
//Donne toutes les informations de la transaction
//---------------------------------
//	GESTION DU PROPRIETAIRE
//---------------------------------
        if (isset($_SESSION['connected']) && $_SESSION['connected'] == TRUE) {//connecte
            sql_query('INSERT INTO `possede` (pk_membres, id) VALUES (' . mysql_real_escape_string($_SESSION['user_id']) . ', ' . mysql_real_escape_string($id) . ')');
        }

//---------------------------------
//	COPIE DU FICHIER
//---------------------------------
        if (!@move_uploaded_file($tmp, __TARGET__ . $new_name)) {
            retour_erreur('Un probl&egrave;me &agrave; &eacute;t&eacute; rencontr&eacute; lors de l\'enregistrement de votre image</p>', __FILE__, 'die');
        }
//-------------------------------------
//	ROTATION DE L'IMAGE (si requis)
//-------------------------------------
        if (isset($_POST['rotate'])) { //si la checkbox est cochée
            $angle = $_POST['angle']; //angle de rotation
            include(__PATH__ . "rotate.php");
        }
//------------------------------------------
//	GENERATION DE LA MINIATURE (si requis)
//------------------------------------------
        if (isset($_POST['thumbs'])) {//Faut-il faire une miniature?
            if (isset($_POST['t_size'])) {
//		$t_width = substr(stristr($_POST['t_size'], 'x'), 1);
//		//renvoi la chaine apres x
//		$t_height = substr($_POST['t_size'], 0, strlen($t_width));

                $t_height = substr(stristr($_POST['t_size'], 'x'), 1);
//renvoi la chaine apres x
                $t_width = substr($_POST['t_size'], 0, strlen($t_height));

//PHP 5.3.0 =>$t_height	= stristr($_POST['t_size'], 'x', TRUE);				//renvoi la chaine avant x

                if (is_nan($t_width) || is_nan($t_height)) {//on verifie que ce soient bien des chiffres
//140x100
                    $t_width = __DEFAULT_T_WIDTH__;
                    $t_height = __DEFAULT_T_HEIGHT__;
                }
            } else {//si aucune taille n'est définie, utilisation des valeurs par defaut
                $t_width = __DEFAULT_T_WIDTH__;
//140x100
                $t_height = __DEFAULT_T_HEIGHT__;
            }
            include (__PATH__ . "thumbs.php");
//on appelle le fichier gerant la creation des miniatures
        }
    }
//------------------------------------------
//	RETOUR UTILISATEUR
//------------------------------------------
// Si upload OK alors on affiche le message de reussite
    ?>
    <div class="jumbotron">
        <h1><small>Envoyer une image</small></h1>

        <div class="panel panel-primary">
            <div class="panel-body">
                <p style="float:right;"><a href="./delete.php?id=<?= $new_name ?>"><img src="./template/images/trash.png" alt="Supprimer l'image" /></a></p>
                <p>Image enregistr&eacute;e avec succ&egrave;s !</p>
                <ul><li>Fichier : <?= $filename ?></li>
                    <li>Taille : <?= $taille ?> Octets</li>
                    <li>Largeur : <?= $width ?> px</li>
                    <li>Hauteur : <?= $height ?>px</li>
                    <li>Liens
                        <ul>
                            <li>URL : <a href="<?= __URL_SITE__ . __TARGET__ . $new_name ?>"><?= __URL_SITE__ . __TARGET__ . $new_name ?></a></li>
                            <li>HTML : <input type="text" size="50" onFocus="this.select();" value='<a href="<?= __URL_SITE__ . __TARGET__ . $new_name ?>"><?= $filename ?></a>' /></li>
                            <li>BBcode : <input type="text" size="50" onFocus="this.select();" value="[img]<?= __URL_SITE__ . __TARGET__ . $new_name ?>[/img]" /></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <?php
} else {
    $_SESSION['_upload'] = TRUE;
    // Autorise l'envoi d'un fichier (protection anti-flood curl basique)
    // $lang['INFO'] = '<em>05/06/2012 :</em> <b>10 millions</b> affichages d\'images ont &eacute;t&eacute; effectu&eacute;s ! <em>(<a href="/stats.php">statistiques</a>)</em>';  //Une nouvelle fonctionnalit&eacute;, un besoin, une API, ... <a href="/contact.php">proposez vos id&eacute;es !</a>
    //    $lang['INFO'] = '<em>10/08/2012 :</em> <b>9 000</b>+ images sont h&eacute;berg&eacute;es ! <em>(<a href="/stats.php">statistiques</a>)</em>';
    ?>
    <div class="jumbotron">
        <h1><small>Envoyer une image</small></h1>

        <div class="panel panel-primary">
            <div class="panel-body">
                Image-heberg.fr est un service gratuit vous permettant d'h&eacute;berger vos images sur internet.
                <ul>
                    <li>Fichiers : <?= strtoupper(__EXTENSIONS_OK__) ?></li>
                    <li>Taille max. : <?= round(__MAX_SIZE__ / 1048576, 1) ?> Mo</li>
                    <li>Dimensions max. : <?= __WIDTH_MAX__ ?> x <?= __HEIGHT_MAX__ ?> pixels (hauteur / largeur)</li>
                </ul>
                <form role="form" enctype="multipart/form-data" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" class="form-inline">
                    <div class="form-group">
                        <fieldset>
                            <legend>Envoyer un fichier :</legend>
                            <input name="fichier" type="file" />
                            <br />
                            <button class="btn btn-info" name="Submit" type="submit"><strong>Envoyer</strong></button>
                            <br />
                            <em>Tout envoi de fichier implique l'acceptation des <a href="/cgu.php">Conditions Générales d'Utilisation</a> du service.</em>
                        </fieldset>
                    </div>
                    <br />
                    <div class="form-group">
                        <fieldset>
                            <legend>
                                <span id="options">
                                    Options : <!--<img id="image_options" src="/template/images/fleche_bas.png" alt="Options" style="height: 15px; width: 15px;"/>-->
                                </span>
                            </legend>
                            <div id="liste_options">
        <!--                            <input disabled type="checkbox" name="thumbs" />Faire une miniature
                                <select disabled name="t_size">
                                    <option value="88x31">Bouton (88x31)</option>
                                    <option value="90x90">Avatar (90x90)</option>
                                    <option value="100x100">Aper&ccedil;u (100x100)</option>
                                    <option value="140x100">Miniature (140x100)</option>
                                    <option value="320x240">Miniature+ (320x240)</option>
                                    <option value="640x480" selected="selected">Miniature++ (640x480)</option>
                                    <option value="700x700">Miniature Forum (700x700)</option>
                                </select>
                                <br />
                                <input type="checkbox" name="t_info" disabled="disabled" />Information poids / taille original
                                <br />
                                <input disabled="disabled" type="checkbox" name="resize" />Redimensionner l'image
                                <select disabled="disabled" name="size">
                                    <option value="320x240">320x240</option>
                                    <option value="640x480">640x480</option>
                                    <option value="800x600">800x600</option>
                                    <option value="1024x768">1024x768</option>
                                </select>
                                <br />-->
                                <input type="checkbox" name="rotate"/>Rotation de l'image
                                <select name="angle">
                                    <optgroup label="Rotation vers la gauche">
                                        <option value="90">90&deg; (&frac14; tour)</option>
                                        <option value="180">180&deg; (&frac12; tour)</option>
                                        <option value="270">270&deg; (&frac34; tour)</option>
                                    </optgroup>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
//    ';
//
//$lang['SCRIPT_JS'] = '
//			$(document).ready(function() {
//				// Cache les options
//				$(\'#liste_options\').hide();
//
//				$(\'#options\').click(function() {
//					// Affiche les options
//					$(\'#liste_options\').show();
//					// Cache le bouton
//					$(\'#image_options\').hide();
//				});
//			});';
}
?>

<?php require _TPL_BOTTOM_ ?>