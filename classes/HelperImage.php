<?php

/*
 * Copyright 2008-2023 Anael MOBILIA
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
use ImagickException;

/**
 * Bibliothèque d'outils pour la gestion des images
 */
abstract class HelperImage
{
    /**
     * Type de l'image
     * @param string $path chemin sur le filesystem
     * @return false|int
     */
    public static function getType(string $path): bool|int
    {
        return exif_imagetype($path);
    }

    /**
     * MIME type de l'image
     * @param string $path chemin sur le filesystem
     * @return string
     */
    public static function getMimeType(string $path): string
    {
        return image_type_to_mime_type(self::getType($path));
    }

    /**
     * Chargement ressource PHP image
     * @param string $path
     * @return Imagick
     * @throws ImagickException
     */
    public static function getImage(string $path): Imagick
    {
        $monImage = new Imagick();
        $monImage->readImage($path);

        // Pour les images animeés (GIF), générer une image pour chaque frame la composant
        if (self::getType($path) === IMAGETYPE_GIF) {
            $monImage = $monImage->coalesceImages();
        }

        return $monImage;
    }

    /**
     * Enregistrement d'une ressource PHP image
     * @param Imagick $uneImage Image à enregistrer
     * @param int $imageType type PHP de l'image
     * @param string $path chemin du fichier
     * @return bool Succès ?
     * @throws ImagickException
     */
    public static function setImage(Imagick $uneImage, int $imageType, string $path): bool
    {
        $monRetour = false;

        // Image animée (GIF)
        if ($imageType === IMAGETYPE_GIF) {
            $uneImage->setInterlaceScheme(Imagick::INTERLACE_GIF);
            // Pour la génération du GIF, on ne veut que les différences entre les images
            $uneImage = $uneImage->deconstructImages();
            // Suppression des commentaires & co
            $uneImage->stripImage();
            // Enregistrement de l'ensemble des images
            $monRetour = $uneImage->writeImages($path, true);
        } else {
            // Image non animée
            if ($imageType === IMAGETYPE_JPEG) {
                $uneImage->setInterlaceScheme(Imagick::INTERLACE_JPEG);
                // Pas de destruction de l'image
                $uneImage->setImageCompression(Imagick::COMPRESSION_JPEG);
                $uneImage->setImageCompressionQuality(100);
            } elseif ($imageType === IMAGETYPE_PNG) {
                $uneImage->setInterlaceScheme(Imagick::INTERLACE_PNG);
                $uneImage->setImageCompression(Imagick::COMPRESSION_LZW);
                $uneImage->setImageCompressionQuality(9);
            } elseif ($imageType === IMAGETYPE_WEBP) {
                $uneImage->setImageFormat('webp');
                $uneImage->setImageCompression(Imagick::COMPRESSION_LZW);
                $uneImage->setImageCompressionQuality(100);
            }
            // Suppression des commentaires & co
            $uneImage->stripImage();
            $monRetour = $uneImage->writeImage($path);
        }
        return $monRetour;
    }

    /**
     * Fourni l'extension officielle d'une ressource
     * @param string $path chemin sur le filesystem
     * @return string
     */
    public static function getExtension(string $path): string
    {
        $ext = image_type_to_extension(self::getType($path), false);
        if ($ext === 'jpeg') {
            // Préférence pour .jpg [filename.ext]
            $ext = 'jpg';
        }

        return $ext;
    }

    /**
     * Est-il possible de modifier l'image (mémoire suffisante ?)
     * @param string $path
     * @return bool Possible ?
     * @see http://www.dotsamazing.com/en/labs/phpmemorylimit
     */
    public static function isModifiableEnMemoire(string $path): bool
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
        if ($memReq < HelperSysteme::getMemoireAllouee()) {
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
    public static function getMaxDimension(): int
    {
        $memDispo = HelperSysteme::getMemoireAllouee();

        /**
         * Mémoire requise :
         * (hauteur x largeur x profondeur)
         * => x 2 [imageSource + imageDest]
         * => x 1.8 [fudge factor]
         */
        $dimMax = round(sqrt($memDispo / 4 / 2 / _FUDGE_FACTOR_));

        return (int)$dimMax;
    }
}
