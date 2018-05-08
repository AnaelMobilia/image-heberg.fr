<?php
/*
 * Copyright 2008-2018 Anael Mobilia
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
 */
class outils {

    /**
     * Type de l'image
     * @param string $path chemin sur le filesystem
     * @return string
     */
    public static function getType($path) {
        return exif_imagetype($path);
    }

    /**
     * MIME type de l'image
     * @param string $path chemin sur le filesystem
     * @return string
     */
    public static function getMimeType($path) {
        return image_type_to_mime_type(self::getType($path));
    }

    /**
     * Chargement ressource PHP image
     * @return resource
     */
    public static function getImage($path) {
        $monImage = NULL;

        // Je charge l'image en mémoire en fonction de son type
        switch (self::getType($path)) {
            case IMAGETYPE_GIF:
                $monImage = imagecreatefromgif($path);
                break;
            case IMAGETYPE_JPEG:
                $monImage = imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $monImage = imagecreatefrompng($path);
                break;
        }

        return $monImage;
    }

    /**
     * Enregistrement d'une ressource PHP image
     * @param ressource $uneImage Image a enregistrer
     * @param int $imageType type PHP de l'image
     * @param string $path chemin du fichier
     * @return boolean Succès ?
     */
    public static function setImage($uneImage, $imageType, $path) {
        $monRetour = FALSE;

        // Je charge l'image en mémoire en fonction de son type
        switch ($imageType) {
            case IMAGETYPE_GIF:
                $monRetour = imagegif($uneImage, $path);
                break;
            case IMAGETYPE_JPEG:
                $monRetour = imagejpeg($uneImage, $path, 100);
                break;
            case IMAGETYPE_PNG:
                $monRetour = imagepng($uneImage, $path, 9);
                break;
        }

        return $monRetour;
    }

    /**
     * Fourni l'extension officielle d'une ressource
     * @param string $path chemin sur le filesystem
     * @return string
     */
    public static function getExtension($path) {
        $ext = image_type_to_extension(self::getType($path), FALSE);
        if ($ext === 'jpeg') {
            // Préférence pour .jpg [filenmae.ext]
            $ext = 'jpg';
        }

        return $ext;
    }

    /**
     * Taille mémoire maximale autorisée
     * @see http://php.net/manual/fr/function.ini-get.php
     * @return int
     */
    public static function getMemoireAllouee() {
        // Récupération de la valeur du php.ini
        $valBrute = trim(ini_get('memory_limit'));

        // Gestion de l'unité multiplicatrice...
        $unite = strtolower(substr($valBrute, -1));
        $val = (int) substr($valBrute, 0, -1);
        switch ($unite) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * Est-il possible de modifier l'image (mémoire suffisante ?)
     * @param string $path
     * @return boolean Possible ?
     * @see http://www.dotsamazing.com/en/labs/phpmemorylimit
     */
    public static function isModifiableEnMemoire($path) {
        $monRetour = FALSE;
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
            $monRetour = TRUE;
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
    public static function getMaxDimension() {
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

    /**
     * Vérifie l'existence de l'image pour :
     * @param string $unMD5 le md5 du fichier
     * @param string $uneIp l'adresse IP d'envoi
     * @param utilisateurObject $unUtilisateur utilisateurObject
     * @param int $typeImage ressourceObject::const Type de l'image
     * @return string|NULL Nom de l'image si déjà présente
     */
    public static function verifierRenvoiImage($unMD5, $uneIp, $unUtilisateur, $typeImage) {
        $monRetour = NULL;

        /**
         * IMAGE
         */
        if ($typeImage === ressourceObject::typeImage) {
            if ($unUtilisateur->getLevel() === utilisateurObject::levelGuest) {
                /**
                 * Utilisateur anonyme
                 * Recherche sur MD5, @IP (sauf images possédées)
                 */
                $req = maBDD::getInstance()->prepare("SELECT new_name FROM images WHERE md5 = ? AND ip_envoi = ? AND date_envoi > DATE_SUB(NOW(), INTERVAL 15 DAY) AND id NOT IN (SELECT image_id from possede) ORDER BY date_envoi DESC");
                /* @var $req PDOStatement */
                $req->bindValue(1, $unMD5, PDO::PARAM_STR);
                $req->bindValue(2, $uneIp, PDO::PARAM_STR);
            } else {
                /**
                 * Utilisateur authentifié
                 * Recherche sur MD5, possede (sur ses images)
                 */
                $req = maBDD::getInstance()->prepare("SELECT new_name FROM images, possede, membres WHERE md5 = ? AND images.id = possede.image_id AND possede.pk_membres = membres.id AND membres.id = ? ORDER BY date_envoi DESC");
                /* @var $req PDOStatement */
                $req->bindValue(1, $unMD5, PDO::PARAM_STR);
                $req->bindValue(2, $unUtilisateur->getId(), PDO::PARAM_INT);
            }
        }
        /**
         * MINIATURE
         */ else {
            if ($unUtilisateur->getLevel() === utilisateurObject::levelGuest) {
                /**
                 * Utilisateur anonyme
                 * Recherche sur MD5, @IP (sauf images possédées)
                 */
                $req = maBDD::getInstance()->prepare("SELECT thumbnails.new_name FROM thumbnails, images WHERE thumbnails.md5 = ? AND images.ip_envoi = ? AND thumbnails.date_creation > DATE_SUB(NOW(), INTERVAL 15 DAY) AND id_image NOT IN (SELECT image_id from possede) AND thumbnails.id_image = images.id ORDER BY date_envoi DESC");
                /* @var $req PDOStatement */
                $req->bindValue(1, $unMD5, PDO::PARAM_STR);
                $req->bindValue(2, $uneIp, PDO::PARAM_STR);
            } else {
                /**
                 * Utilisateur authentifié
                 * Recherche sur MD5, possede (sur ses images)
                 */
                $req = maBDD::getInstance()->prepare("SELECT thumbnails.new_name FROM thumbnails, images, possede, membres WHERE thumbnails.md5 = ? AND images.id = possede.image_id AND possede.pk_membres = membres.id AND membres.id = ? AND thumbnails.id_image = images.id ORDER BY date_envoi DESC");
                /* @var $req PDOStatement */
                $req->bindValue(1, $unMD5, PDO::PARAM_STR);
                $req->bindValue(2, $unUtilisateur->getId(), PDO::PARAM_INT);
            }
        }

        // Exécution de la requête
        $req->execute();

        // Je récupère les potentielles valeurs
        $values = $req->fetch();
        if ($values !== FALSE) {
            // Données éventuelles
            $monRetour = $values->new_name;
        }

        return $monRetour;
    }

}