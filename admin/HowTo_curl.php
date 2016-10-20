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
//	./admin/curl.php
//	Upload d'une image via curl
//**************************************
if (isset($_POST['Submit'])) { //<== l� tu modifies en fonction du nom du bouton submit de ton formulaire (et pi tu modifie rien d'autre :-p)
    //	Premi�re partie dans laquelle sont stock�es les param�tres du formulaire
    $post_data = array(//tableau de donn�es
        'fichier' => '@' . realpath($_FILES['fichier']['tmp_name']), //le @ veut dire qu'il va aller chercher le contenu du fichier ; realpath => chemin d'acc�s sur le serveur ; $_FILES... => 1 fichier qu'on envoi sur un serveur est stock� sous un nom temporaire (tmp_name) dans le r�pertoire /tmp du serveur
        'thumbs' => '', //est-ce qu'on fait une miniature
        't_size' => '140x100', //dimensions miniatures
        't_info' => '', //information sur la taille de l'image
        'resize' => '', //redimensionnement
        'size' => '', //taille de redimensionement
        //'rotate' => '',	//ca on n'y touche pas, sinon je retourne ton image ;-)
        //'angle' => '90',
        'Submit' => 'TRUE', //formulaire valid�
        'login' => 'Jeremy', //ton login client
        'password' => 'Wb9@Jhs$$', //ton mot de passe
        'real_name' => $_FILES['fichier']['name']); //le nom original du fichier envoy�
    //modifies dans les deux $_FILE['fichier']['*'] => 'fichier' par le nom de ton champ de type file (dans ton formulaire)

    $curl = curl_init("http://image-heberg.fr.cr/upload.php"); //d�fini l'url � laquelle le tout sera envoy�
    curl_setopt($curl, CURLOPT_POST, TRUE);           //valide l'envoi par m�thode POST
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);     //les donn�es du formulaires sont envoy�es par ici
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       //n'affiche pas ce que renvoit mon site web ;-)
    $page = curl_exec($curl);     //on lui demande d'ex�cuter la requ�te (comme si c'�tait toi qui l'avait fait :))
    curl_close($curl);  //on ferme la connexion vers mon site
    //echo $page;	//afficherait toute la page retourn�e ;-)

    preg_match('#<div id="corps"><h2>Envoi du fichier .*</h2><p style="float:right;"><a href="\./delete.php\?id=([0-9]*.[a-z]{3})#U', $page, $url);
    // on va chercher dans $page le contenu <div id="corps.......delete.php?id=
    // REGEXP en d�tail : http://www.siteduzero.com/tutoriel-3-14618-les-expressions-regulieres-partie-1-2.html
    // les # servent � d�limiter l'expression que je recherche
    // les caract�res . et ? sont �chapp�s par un \ => pour les caract�res textes (le . est aussi un caract�re sp�cifique aux expressions r�guli�res)
    // DANS NOTRE CAS
    // . => n'importe quel caract�re
    // * => autant de fois qu'il en trouve
    // [0-9] => n'importe quel chiffre
    // * => autant de fois qu'il en trouve
    // [a-z] => caract�re de a � z
    // {3} => 3 fois
    // (...) => tu me m�morises le contenu de ce que tu trouves entre les () dans $url[1]
    // #U => Ungreedy (pas gourmand) => [difficile :-p] oblige le script � chercher la solution la plus courte (�vite qu'avec le [.]* le script aille chercher un id de fichier � l'autre bout d'un fichier si tu veux qu'il prenne en compte la premi�re occurence)
    $url_image = 'http://image-heberg.fr.cr/files/' . $url[1]; //contient le r�sultat de la regexp

    echo $url_image; //<== l� tu as l'url d'acc�s � l'image sur mon service (as : http://image-heberg.fr.cr/files/1247175331117143.jpg )
    //Tu en fais ce que tu veux, tu l'ins�re dans ta bdd, tu affiches l'image,... as you want :)
} else { //formulaire lambda d'envoi d'un fichier
    ?>
    <form enctype="multipart/form-data" action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>
            <legend>Envoyer le fichier :</legend>
            <input name="fichier" type="file" />
            <button name="Submit" type="submit"><strong>Uploader</strong></button>
        </fieldset>
    </form>
    <?php
}
?>