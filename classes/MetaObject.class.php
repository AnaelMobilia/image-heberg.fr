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

use PDO;
use ArrayObject;
use Imagick;

/**
 * Les méthodes "génériques"
 *
 * @author anael
 */
class MetaObject
{

    /**
     * Liste des images n'ayant jamais été affichées et présentes sur le serveur depuis xx temps
     * @return \ArrayObject
     */
    public static function getNeverUsedFiles()
    {
        // Toutes les images jamais affichées & envoyées il y a plus de xx jours
        $req = "SELECT im.new_name
               FROM images im
               WHERE im.last_view IS NULL
               AND im.date_envoi < DATE_SUB(CURRENT_DATE(), INTERVAL " . _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ . " DAY)
               /* Préservation des fichiers des membres */
               AND 0 = (
                  SELECT COUNT(*)
                  FROM possede po
                  WHERE po.image_id = im.id
               )
               /* Préservation si miniature affichée */
               AND 0 = (
                  SELECT COUNT(*)
                  FROM thumbnails th
                  WHERE th.id_image = im.id
                  AND th.last_view IS NOT NULL
               )
";

        // Exécution de la requête
        $resultat = MaBDD::getInstance()->query($req);

        $retour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $retour->append($value->new_name);
        }

        return $retour;
    }

    /**
     * Liste des images plus utilisées depuis au moins xx jours
     * @return \ArrayObject
     */
    public static function getUnusedFiles()
    {
        // Toutes les images non affichées depuis xx jours
        $req = "SELECT im.new_name
               FROM images im
               WHERE im.last_view < DATE_SUB(CURRENT_DATE(), INTERVAL " . _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ . " DAY)
               /* Non prise en compte des images jamais affichées */
               AND im.last_view IS NOT NULL
               /* Préservation des images membres */
               AND 0 = (
                  SELECT COUNT(*)
                  FROM possede po
                  WHERE po.image_id = im.id
               )
               /* Préservation si miniature affichée */
               AND 0 = (
                  SELECT COUNT(*)
                  FROM thumbnails th
                  WHERE th.id_image = im.id
                  AND th.last_view > DATE_SUB(CURRENT_DATE(), INTERVAL " . _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ . " DAY)
               )";

        // Exécution de la requête
        $resultat = MaBDD::getInstance()->query($req);


        $retour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $retour->append($value->new_name);
        }

        return $retour;
    }

    /**
     * Liste de l'ensemble des images en BDD
     * @return \ArrayObject
     */
    public static function getAllImagesNameBDD()
    {
        // Toutes les images (sauf 404 & banned)
        $req = "SELECT md5 FROM images WHERE id > 2";

        // Exécution de la requête
        $resultat = MaBDD::getInstance()->query($req);

        $retour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $retour->append($value->md5);
        }

        return $retour;
    }

    /**
     * Liste de l'ensemble des images en HDD
     * @param type $path path à analyser
     * @return \ArrayObject
     */
    public static function getAllImagesNameHDD($path)
    {
        $monRetour = new ArrayObject();

        // Scanne le répertoire des images
        $scan_rep = scandir($path);
        // Pour chaque item
        foreach ($scan_rep as $item) {
            if ($item !== '.' && $item !== '..' && $item !== '_dummy') {
                if (is_dir($path . $item)) {
                    // Appel récursif
                    if ($path . $item . '/' !== _PATH_MINIATURES_) {
                        $monRetourTmp = self::getAllImagesNameHDD($path . $item . '/');
                        // Parsage et récupération des sous fichiers...
                        foreach ($monRetourTmp as $fichier) {
                            $monRetour->append($fichier);
                        }
                    }
                } elseif ($item !== _IMAGE_404_ && $item !== _IMAGE_BAN_) {
                    $monRetour->append($item);
                }
            }
        }

        return $monRetour;
    }

    /**
     * Liste de l'ensemble des miniatures en BDD
     */
    public static function getAllMiniaturesNameBDD()
    {
        // Toutes les images
        $req = "SELECT thumbnails.md5 FROM images, thumbnails WHERE images.id = thumbnails.id";

        // Exécution de la requête
        $resultat = MaBDD::getInstance()->query($req);


        $retour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $retour->append($value->md5);
        }

        return $retour;
    }

    /**
     * Volume des images
     * @return int
     */
    public static function getHDDUsage()
    {
        // Poids de l'ensemble des images
        $req = "SELECT SUM(im.size) AS images, (
                  SELECT SUM(th.size)
                  FROM thumbnails th
               ) AS miniatures
               FROM images im";

        // Exécution de la requête
        $resultat = MaBDD::getInstance()->query($req);

        // Récupération de la valeur
        $value = $resultat->fetch();

        $retour = round(($value->images + $value->miniatures) / (1024 * 1024 * 1024));

        return $retour;
    }

    /**
     * Version de PHP
     * @return string
     */
    public static function getPhpVersion()
    {
        $retour = PHP_VERSION . " - " . PHP_OS;

        return $retour;
    }

    /**
     * Version de Imagick
     * @return string
     */
    public static function getImagickVersion()
    {
        $retour = Imagick::getVersion()["versionString"];

        return $retour;
    }

    /**
     * Version de MySQL
     * @return string
     */
    public static function getMysqlVersion()
    {
        // Exécution de la requête
        $retour = MaBDD::getInstance()->getAttribute(PDO::ATTR_SERVER_VERSION);

        return $retour;
    }

    /**
     * Headers HTTP status code
     * @param string $url URL à tester
     * @return string retour HTTP
     */
    public static function getStatusHTTP($url)
    {
        $classe = "danger";
        $fa = "exclamation-circle";

        // On regarde ce que ça donne
        $resultat = get_headers($url);

        // Est-ce le résultat attendu ?
        if (stripos($resultat[0], "Forbidden")) {
            $classe = "success";
            $fa = "check";
        }
        // Mise en forme du résultat
        $retour = "<span class=\"fas fa-" . $fa . " text-" . $classe . "\">&nbsp;" . $resultat[0] . "</span>";

        return $retour;
    }

    /**
     * Vérifie de manière récursive l'écriture dans un dossier
     * @param string $folder Path du dossier parent
     * @return ArrayObject
     */
    public static function isRecursivelyWritable($folder)
    {
        // On évite le // dans le path... (estéthique)
        if (substr($folder, -1) === "/") {
            $folder = substr($folder, 0, -1);
        }
        $monRetour = new ArrayObject();

        if (is_writable($folder)) {
            $monRetour->append("<span class=\"fas fa-check text-success\">&nbsp;" . $folder . "</span>");
        } else {
            $monRetour->append("<span class=\"fas fa-exclamation-circle text-danger\">&nbsp;" . $folder . "</span>");
        }

        // Dossiers enfants
        $objects = glob($folder . "/*", GLOB_ONLYDIR);
        foreach ($objects as $object) {
            // Je vérifie si les dossiers enfants sont écrivables
            $sousRetour = self::isRecursivelyWritable($object);
            // Gestion de l'itération...
            foreach ($sousRetour as $unRetour) {
                $monRetour->append($unRetour);
            }
        }

        return $monRetour;
    }

    /**
     * Toutes les images avec un même MD5
     * @param string $unMd5
     * @return \ArrayObject
     */
    public static function getImageByMd5($unMd5)
    {
        // Images avec le même MD5
        $req = MaBDD::getInstance()->prepare("SELECT new_name FROM images WHERE md5 = :md5");
        $req->bindValue(':md5', $unMd5, PDO::PARAM_STR);
        $req->execute();

        $retour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($req->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $retour->append($value->new_name);
        }

        return $retour;
    }
}
