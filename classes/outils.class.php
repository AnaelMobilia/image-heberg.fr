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
     * Ressource PHP image
     * @return type
     */
    public static function getImage($path) {
        // Je charge l'image en mémoire en fonction de son type
        switch (self::getType($path)) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif($path);
                break;
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                return imagecreatefrompng($path);
                break;
        }
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
     * @param imageObject $uneImage
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

        return $dimMax;
    }

    /**
     * Vérifie l'existence de l'image pour :
     * @param type $unMD5 le md5 du fichier
     * @param type $uneIp l'adresse IP d'envoi
     * @param type $unUtilisateur [optionnel] utilisateurObject
     * @return string|NULL Nom de l'image si déjà présente
     */
    public static function verifierRenvoiImage($unMD5, $uneIp, $unUtilisateur = NULL) {
        $monRetour = NULL;

        /**
         * MD5, @IP
         */
        $req = maBDD::getInstance()->prepare("SELECT new_name, date_envoi FROM images WHERE md5 = ? AND ip_envoi = ? AND date_envoi > DATE_SUB(NOW(), INTERVAL 15 DAY) ORDER BY date_envoi DESC");
        /* @var $req PDOStatement */
        $req->bindValue(1, $unMD5, PDO::PARAM_STR);
        $req->bindValue(2, $uneIp, PDO::PARAM_STR);
        $req->execute();

        // Je récupère les potentielles valeurs
        $valuesIP = $req->fetch();

        if ($valuesIP !== FALSE) {
            // Données éventuelles
            $monRetour = $valuesIP->new_name;
        }


        /**
         * MD5, possede
         */
        if (!is_null($unUtilisateur)) {
            $req = maBDD::getInstance()->prepare("SELECT new_name, date_envoi FROM images, possede, membres WHERE md5 = ? AND images.id = possede.image_id AND possede.pk_membres = membres.id ORDER BY date_envoi DESC");
            /* @var $req PDOStatement */
            $req->bindValue(1, $unMD5, PDO::PARAM_STR);
            $req->execute();

            // Je récupère les potentielles valeurs
            $valuesPossede = $req->fetch();

            if ($valuesPossede !== FALSE) {
                // Données éventuelles
                $monRetour = $valuesPossede->new_name;
            }
        }

        return $monRetour;
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
        $largeurImage = $uneImage->getLargeur();
        $hauteurImage = $uneImage->getHauteur();

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
        $newImage = imagescale(outils::getImage($uneImage->getPathMd5()), $largeurMiniature, $hauteurMiniature);

        // Création de la miniature (en mémoire + HDD)
        $path = _PATH_MINIATURES_ . $uneImage->getNomNouveau();

        switch (outils::getType($uneImage->getPathMd5())) {
            case IMAGETYPE_GIF:
                imagegif($newImage, $path);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $path, 100);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $path, 9);
                break;
        }

        // Je crée l'objet
        $maMiniature = new miniatureObject();
        // Dimensions
        $maMiniature->setPoids(imagesx($newImage));
        $maMiniature->setHauteur(imagesy($newImage));
        // Poids
        $maMiniature->setPoids(filesize($path));
        // ID
        $maMiniature->setId($uneImage->getId());

        // On enregistre !
        $maMiniature->sauver();
    }

}