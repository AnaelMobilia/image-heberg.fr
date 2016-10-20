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
//	./rotate.php
//	Rotation de l'image
//**************************************
if (!defined('__IMAGE_HEBERG__')) {
    require_once("./config/config.php");
    erreur('hack', 'upload_script'); //acc�s direct au fichier non autoris�
}
if ($angle == 0) {
    retour_erreur('<p>Angle de rotation incorrect. La rotation est annul�&eacute;</p>', __FILE__, 'warning');
} else {
    $p_image = __PATH__ . __TARGET__ . $new_name; //pour simplifier la lecture ;-)

    switch ($extension) {
        case 'jpg':
            if (!($image = @imagecreatefromjpeg($p_image))) { //chargement de l'image
                retour_erreur('<p>Erreur : Impossible d\'ouvrir le fichier ' . $p_image . '</p>', __FILE__, 'warning');
            }
            $image_rotate = @imagerotate($image, $angle, 0); //rotation en m�moire
            if (!@imagejpeg($image_rotate, $p_image, 100)) {  //renvoi dans l'image
                retour_erreur('<p>Erreur : Impossible d\'enregistrer le fichier ' . $p_image . '</p>', __FILE__, 'warning');
            }
            break;

        case 'gif':
            if (!($image = @imagecreatefromgif($p_image))) {
                retour_erreur('<p>Erreur : Impossible d\'ouvrir le fichier ' . $p_image . '</p>', __FILE__, 'warning');
            }
            $image_rotate = @imagerotate($image, $angle, 0);
            if (!@imagegif($image_rotate, $p_image)) {
                retour_erreur('<p>Erreur : Impossible d\'enregistrer le fichier ' . $p_image . '</p>', __FILE__, 'warning');
            }
            break;

        case 'png':
            if (!($image = @imagecreatefrompng($p_image))) {
                retour_erreur('<p>Erreur : Impossible d\'ouvrir le fichier ' . $p_image . '</p>', __FILE__, 'warning');
            }
            $image_rotate = @imagerotate($image, $angle, 0);
            if (!@imagepng($image_rotate, $p_image, 0)) {
                retour_erreur('<p>Erreur : Impossible d\'enregistrer le fichier ' . $p_image . '</p>', __FILE__, 'warning');
            }
            break;

        default:
            retour_erreur('<p>Erreur � la rotation de l\'image ' . $p_image . '</p>', __FILE__, 'die');
    }

    //d�sallocation
    if (!@imageDestroy($image_rotate)) {
        retour_erreur('<p>Erreur � la d&eacute;salocation m&eacute;moire de la nouvelle image (' . $image_rotate . ')</p>', __FILE__, 'warning');
    }
    if (!@imageDestroy($image)) {
        retour_erreur('<p>Erreur � la d&eacute;salocation m&eacute;moire de l\'image (' . $image . ')</p>', __FILE__, 'warning');
    }

    //Mise � jour infos de l'image
    if (!($infos_img = getimagesize($p_image))) {  //on arrive pas � r�cup�rer les infos de l'image?
        retour_erreur('Probl&egrave;me &agrave; l\'issue de la rotation de l\'image (' . $new_name . ')</p>', __FILE__, 'die');
    }
    $height = $infos_img[1];
    $width = $infos_img[0];
}
?>