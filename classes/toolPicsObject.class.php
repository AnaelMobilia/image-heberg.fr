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

/**
 * Bibliothèque d'outils pour la gestion des images
 *
 * @author anael
 */
class toolPicsObject {

    /**
     * Effectue une rotation
     * @param type $pathSrc path source
     * @param type $pathDst path destination
     * @param type $angle angle de rotation
     * @return boolean Succès / Echec
     */
    public static function faireRotation($pathSrc, $pathDst, $angle) {
        // Je détermine le type de l'image
        $typeImage = exif_imagetype($pathSrc);

        // Je charge l'image en mémoire
        $resImg = '';
        switch ($typeImage) {
            case IMAGETYPE_GIF:
                $resImg = imagecreatefromgif($pathSrc);
                break;
            case IMAGETYPE_JPEG:
                $resImg = imagecreatefromjpeg($pathSrc);
                break;
            case IMAGETYPE_PNG:
                $resImg = imagecreatefrompng($pathSrc);
                break;
        }
        // Je vérifie que tout va bien
        if ($resImg === FALSE) {
            return FALSE;
        }

        // J'effectue la rotation
        $imgRotate = imagerotate($resImg, $angle);

        // Je vérifie que tout va bien
        if ($imgRotate === FALSE) {
            return FALSE;
        }

        // On commence le ménage mémoire (l'image d'origine)
        imagedestroy($resImg);

        // J'enregistre l'image
        switch ($typeImage) {
            case IMAGETYPE_GIF:
                $retour = imagegif($imgRotate, $pathDst);
                break;
            case IMAGETYPE_JPEG:
                // 100 : taux de qualité (avec pertes)
                $retour = imagejpeg($imgRotate, $pathDst, 100);
                break;
            case IMAGETYPE_PNG:
                // 9 : niveau de compression (sans pertes)
                $retour = imagepng($imgRotate, $pathDst, 9);
                break;
        }

        // On fait le retour final
        return $retour;
    }

    /**
     * Redimensionne une image
     * @param type $pathSrc path source
     * @param type $pathDst path destination
     * @param type $hauteur
     * @param type $largeur
     * @return boolean Succès / Echec
     */
    public static function redimensionner($pathSrc, $pathDst, $hauteur, $largeur) {

        return FALSE;
    }

    /**
     * Crée une miniature (dimensions) en respectant le ratio de l'image original
     * @param int $largeurDemandee largeur
     * @param int $hauteurDemandee hauteur
     */
    public function faireMiniature(imageObject $uneImage, $largeurDemandee, $hauteurDemandee) {
        $largeurImage = $uneImage->getWidth();
        $hauteurImage = $uneImage->getHeight();

        // Dimension nulle : on arrête
        if ($hauteurImage <= 0 || $hauteurDemandee <= 0 || $largeurImage <= 0 || $largeurDemandee <= 0) {
            return false;
        }

        // http://stackoverflow.com/a/26586225
        // Calcul des dimensions de la miniature en respectant le ratio d'origine
        if ($largeurImage > $hauteurImage) {
            $largeurMiniature = $largeurDemandee;
            $hauteurMiniature = $hauteurImage / $largeurImage * $largeurDemandee;
        } else if ($largeurImage < $hauteurImage) {
            $largeurMiniature = $largeurImage / $hauteurImage * $hauteurDemandee;
            $hauteurMiniature = $hauteurDemandee;
        } else if ($largeurImage == $hauteurImage) {
            $largeurMiniature = $largeurDemandee;
            $hauteurMiniature = $hauteurDemandee;
        }

        // Redimensionnement (en mémoire)
        $newImage = imagescale($uneImage->getImage(), $largeurMiniature, $hauteurMiniature);

        // Création de la miniature (en mémoire + HDD)
        $path = _PATH_MINIATURES_ . $uneImage->getNewName();
        if ($uneImage->getType() == IMAGETYPE_GIF) {
            imagegif($newImage, $path);
        } else if ($uneImage->getType() == IMAGETYPE_JPEG) {
            imagejpeg($newImage, $path, 100);
        } else if ($uneImage->getType() == IMAGETYPE_PNG) {
            imagepng($newImage, $path, 9);
        }

        // Je crée l'objet
        $maMiniature = new miniatureObject();
        // Dimensions
        $maMiniature->setWidth(imagesx($newImage));
        $maMiniature->setHeight(imagesy($newImage));
        // Poids
        $maMiniature->setSize(filesize($path));
        // ID
        $maMiniature->setId($uneImage->getId());

        // On enregistre !
        $maMiniature->sauver();
    }

}