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

use ArrayObject;
use Imagick;

/**
 * Bibliothèque d'outils pour la gestion du système
 */
abstract class HelperSysteme
{
    /**
     * Taille mémoire maximale autorisée
     * @see http://php.net/manual/fr/function.ini-get.php
     * @return int
     */
    public static function getMemoireAllouee(): int
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
        $monRetour = (int)substr($valBrute, 0, -1);
        switch ($unite) {
            case 'g':
                $monRetour *= 1024;
            // no break
            case 'm':
                $monRetour *= 1024;
            // no break
            case 'k':
                $monRetour *= 1024;
            // no break
        }

        return $monRetour;
    }

    /**
     * Version de PHP
     * @return string
     */
    public static function getPhpVersion(): string
    {
        return PHP_VERSION . " - " . PHP_OS;
    }

    /**
     * Version de Imagick
     * @return string
     */
    public static function getImagickVersion(): string
    {
        return Imagick::getVersion()["versionString"];
    }

    /**
     * Version de MySQL
     * @return string
     */
    public static function getMysqlVersion(): string
    {
        // Exécution de la requête
        return MaBDD::getInstance()->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Headers HTTP status code
     * @param string $url URL à tester
     * @return string retour HTTP
     */
    public static function getStatusHTTP(string $url): string
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
        return "<span class=\"fas fa-" . $fa . " text-" . $classe . "\">&nbsp;" . $resultat[0] . "</span>";
    }

    /**
     * Vérifie de manière récursive l'écriture dans un dossier
     * @param string $folder Path du dossier parent
     * @return ArrayObject
     */
    public static function isRecursivelyWritable(string $folder): ArrayObject
    {
        // On évite le // dans le path... (estéthique)
        if (str_ends_with($folder, "/")) {
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
     * Volume des images
     * @return float
     */
    public static function getHDDUsage(): float
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

        return round(($value->images + $value->miniatures) / (1024 * 1024 * 1024));
    }
}
