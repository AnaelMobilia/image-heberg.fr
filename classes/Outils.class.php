<?php

/*
 * Copyright 2008-2021 Anael MOBILIA
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

namespace ImageHeberg;

use Imagick;

/**
 * Bibliothèque d'outils pour la gestion des images
 */
class Outils
{

    /**
     * Type de l'image
     * @param string $path chemin sur le filesystem
     * @return string
     */
    public static function getType($path)
    {
        return exif_imagetype($path);
    }

    /**
     * MIME type de l'image
     * @param string $path chemin sur le filesystem
     * @return string
     */
    public static function getMimeType($path)
    {
        return image_type_to_mime_type(self::getType($path));
    }

    /**
     * Chargement ressource PHP image
     * @return Imagick
     */
    /**
     * @param $path
     * @return Imagick
     * @throws \ImagickException
     */
    public static function getImage($path)
    {
        $monImage = new \Imagick();
        $monImage->readImage($path);

        return $monImage;
    }

    /**
     * Enregistrement d'une ressource PHP image
     * @param Imagick $uneImage Image à enregistrer
     * @param int $imageType type PHP de l'image
     * @param string $path chemin du fichier
     * @return boolean Succès ?
     */
    public static function setImage($uneImage, $imageType, $path)
    {
        switch ($imageType) {
            case IMAGETYPE_GIF:
                $uneImage->setInterlaceScheme(Imagick::INTERLACE_GIF);
                break;
            case IMAGETYPE_JPEG:
                $uneImage->setInterlaceScheme(Imagick::INTERLACE_JPEG);
                // Pas de destruction de l'image
                $uneImage->setImageCompression(Imagick::COMPRESSION_JPEG);
                $uneImage->setImageCompressionQuality(100);
                break;
            case IMAGETYPE_PNG:
                $uneImage->setInterlaceScheme(Imagick::INTERLACE_PNG);
                $uneImage->setImageCompression(Imagick::COMPRESSION_LZW);
                $uneImage->setImageCompressionQuality(9);
                break;
        }

        // Suppression des commentaires & co
        $uneImage->stripImage();
        $monRetour = $uneImage->writeImage($path);

        return $monRetour;
    }

    /**
     * Fourni l'extension officielle d'une ressource
     * @param string $path chemin sur le filesystem
     * @return string
     */
    public static function getExtension($path)
    {
        $ext = image_type_to_extension(self::getType($path), false);
        if ($ext === 'jpeg') {
            // Préférence pour .jpg [filename.ext]
            $ext = 'jpg';
        }

        return $ext;
    }

    /**
     * Taille mémoire maximale autorisée
     * @see http://php.net/manual/fr/function.ini-get.php
     * @return int
     */
    public static function getMemoireAllouee()
    {
        // Récupération de la valeur du php.ini
        $valBrute = trim(ini_get('memory_limit'));
        // memory_limit=0 est possible
        if ($valBrute <= 0) {
            // Arbitrairement limite à 2Go
            $valBrute = "2G";
        }

        // Gestion de l'unité multiplicatrice...
        $unite = strtolower(substr($valBrute, -1));
        $val = (int) substr($valBrute, 0, -1);
        switch ($unite) {
            case 'g':
                $val *= 1024;
            // no break
            case 'm':
                $val *= 1024;
            // no break
            case 'k':
                $val *= 1024;
            // no break
        }

        return $val;
    }

    /**
     * Est-il possible de modifier l'image (mémoire suffisante ?)
     * @param string $path
     * @return boolean Possible ?
     * @see http://www.dotsamazing.com/en/labs/phpmemorylimit
     */
    public static function isModifiableEnMemoire($path)
    {
        $monRetour = false;
        // Nombre de canaux d'information de l'image
        $nbCanaux = 4;

        /**
         * Information sur les canaux ?
         */
        $imageinfo = getimagesize($path);
        // Si information sur les canaux de l'image...
        if (isset($imageinfo['channels']) && is_int($imageinfo['channels'])) {
            $nbCanaux = $imageinfo['channels'];
        }

        /**
         * Mémoire requise :
         * (hauteur x largeur x profondeur)
         * => x 2 [imageSource + imageDest]
         * => x 1.8 [fudge factor]
         */
        $memReq = $imageinfo[1] * $imageinfo[0] * $nbCanaux;
        $memReq *= 2;
        $memReq *= _FUDGE_FACTOR_;

        // Est-ce possible ?
        if ($memReq < self::getMemoireAllouee()) {
            $monRetour = true;
        }

        return $monRetour;
    }

    /**
     * Dimension maximale acceptable en mémoire pour les images
     * <br />Suppose que l'image est carrée (donc indicatif !)
     * <br /> Suppose 4 canaux dans l'image
     * @return int
     * @see isModifiableEnMemoire
     */
    public static function getMaxDimension()
    {
        $memDispo = self::getMemoireAllouee();

        /**
         * Mémoire requise :
         * (hauteur x largeur x profondeur)
         * => x 2 [imageSource + imageDest]
         * => x 1.8 [fudge factor]
         */
        $dimMax = round(sqrt($memDispo / 4 / 2 / _FUDGE_FACTOR_), 0);

        return (int) $dimMax;
    }
}
