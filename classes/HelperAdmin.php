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

/**
 * Bibliothèque d'outils pour la gestion du site en tant qu'admin
 */
abstract class HelperAdmin
{
    /**
     * Liste des images n'ayant jamais été affichées et présentes sur le serveur depuis xx temps
     * @return ArrayObject
     */
    public static function getNeverUsedFiles(): ArrayObject
    {
        // Toutes les images jamais affichées & envoyées il y a plus de xx jours
        $req = 'SELECT im.new_name
               FROM images im
               WHERE im.last_view = \'0000-00-00\'
               AND im.date_envoi < DATE_SUB(CURRENT_DATE(), INTERVAL ' . _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ . ' DAY)
               /* Préservation des fichiers des membres */
               AND 0 = (
                  SELECT COUNT(*)
                  FROM possede po
                  WHERE po.images_id = im.id
               )
               /* Préservation si miniature affichée */
               AND 0 = (
                  SELECT COUNT(*)
                  FROM thumbnails th
                  WHERE th.images_id = im.id
                  AND th.last_view <> \'0000-00-00\'
               )';
        return self::queryOnNewName($req);
    }

    /**
     * Liste des images plus utilisées depuis au moins xx jours
     * @return ArrayObject
     */
    public static function getUnusedFiles(): ArrayObject
    {
        // Toutes les images non affichées depuis xx jours
        $req = 'SELECT im.new_name
               FROM images im
               WHERE im.last_view < DATE_SUB(CURRENT_DATE(), INTERVAL ' . _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ . ' DAY)
               /* Non prise en compte des images jamais affichées */
               AND im.last_view <> \'0000-00-00\'
               /* Préservation des images membres */
               AND 0 = (
                  SELECT COUNT(*)
                  FROM possede po
                  WHERE po.images_id = im.id
               )
               /* Préservation si miniature affichée */
               AND 0 = (
                  SELECT COUNT(*)
                  FROM thumbnails th
                  WHERE th.images_id = im.id
                  AND th.last_view > DATE_SUB(CURRENT_DATE(), INTERVAL ' . _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ . ' DAY)
               )';
        return self::queryOnNewName($req);
    }

    /**
     * Liste des comptes sans images et créés depuis au moins xx jours
     * @return ArrayObject
     */
    public static function getUnusedAccounts(): ArrayObject
    {
        // Toutes les comptes créés et jamais utilisés depuis xx jours
        $req = 'SELECT m.id
               FROM membres m
               WHERE m.date_inscription < DATE_SUB(CURRENT_DATE(), INTERVAL ' . _DELAI_EFFACEMENT_COMPTES_JAMAIS_UTILISES_ . ' DAY)
               /* Préservation des comptes possédant des images */
               AND 0 = (
                  SELECT COUNT(*)
                  FROM possede po
                  WHERE po.membres_id = m.id
               )';

        // Exécution de la requête
        $resultat = MaBDD::getInstance()->query($req);

        $monRetour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute l'ID du compte
            $monRetour->append($value->id);
        }

        return $monRetour;
    }

    /**
     * Liste de l'ensemble des images en BDD
     * @return ArrayObject
     */
    public static function getAllImagesNameBDD(): ArrayObject
    {
        // Toutes les images (sauf 404 & banned)
        $req = 'SELECT md5 FROM images WHERE id > 2';

        // Exécution de la requête
        $resultat = MaBDD::getInstance()->query($req);

        $monRetour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $monRetour->append($value->md5);
        }

        return $monRetour;
    }

    /**
     * Liste de l'ensemble des images en HDD
     * @param string $path path à analyser
     * @return ArrayObject
     */
    public static function getAllImagesNameHDD(string $path): ArrayObject
    {
        $monRetour = new ArrayObject();

        // Scanne le répertoire des images
        $scan_rep = scandir($path);
        // Pour chaque item
        foreach ($scan_rep as $item) {
            if (!in_array($item, ['.', '..', '_dummy', 'z_cache'])) {
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
    public static function getAllMiniaturesNameBDD(): ArrayObject
    {
        // Toutes les images
        $req = 'SELECT md5 FROM thumbnails';

        // Exécution de la requête
        $resultat = MaBDD::getInstance()->query($req);


        $monRetour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $monRetour->append($value->md5);
        }

        return $monRetour;
    }

    /**
     * Toutes les images avec un même MD5
     * @param string $unMd5
     * @return ArrayObject
     */
    public static function getImageByMd5(string $unMd5): ArrayObject
    {
        // Images avec le même MD5
        $req = MaBDD::getInstance()->prepare('SELECT new_name FROM images WHERE md5 = :md5');
        $req->bindValue(':md5', $unMd5);
        $req->execute();

        $monRetour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($req->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $monRetour->append($value->new_name);
        }

        return $monRetour;
    }

    /**
     * Toutes les images signalées
     * @return ArrayObject
     */
    public static function getImagesSignalees(): ArrayObject
    {
        // Images signalées
        $req = 'SELECT new_name FROM images WHERE isSignalee = 1 and isBloquee = 0';
        return self::queryOnNewName($req);
    }

    /**
     * Images dont les statistiques d'affichage sont incohérentes
     * @param int $nbMax Nb affichage / jour à partir duquel on veut les images
     * @param bool $onlyOnImagesSuspectes Filtrer sur les images suspectes ?
     * @return ArrayObject
     */
    public static function getImagesTropAffichees(int $nbMax, bool $onlyOnImagesSuspectes = false): ArrayObject
    {
        $monRetour = new ArrayObject();

        if ($onlyOnImagesSuspectes) {
            $listeImagesForIn = '';
            foreach ((array)self::getImagesPotentiellementIndesirables() as $newName) {
                $listeImagesForIn .= '\'' . str_replace('\'', '', $newName) . '\',';
            }
            // Avoir un placeholder vide à la fin si aucune image n'a été trouvée
            $listeImagesForIn .= '\'\'';
        }

        // Images avec trop d'affichages
        $req = 'SELECT im.new_name, ( im.nb_view_v4 + im.nb_view_v6 + (SELECT IFNULL(SUM(th.nb_view_v4 + th.nb_view_v6), 0) FROM thumbnails th where th.images_id = im.id) ) / IF(DATEDIFF(NOW(), im.date_envoi) > 0, DATEDIFF(NOW(), im.date_envoi), 1) as nbViewPerDay
            FROM images im
            WHERE im.isBloquee = 0
            AND im.isApprouvee = 0';
        // Filter sur certaines images
        if ($onlyOnImagesSuspectes) {
            $req .= ' AND im.new_name IN (' . $listeImagesForIn . ')';
        }
        $req .= ' HAVING nbViewPerDay > ' . $nbMax . '
            ORDER BY nbViewPerDay DESC';
        return self::queryOnNewName($req);
    }

    /**
     * Images dont les données sont proches d'images déjà bloquées
     * @return ArrayObject
     */
    public static function getImagesPotentiellementIndesirables(): ArrayObject
    {
        // Compléter les données "abuse_network"
        // IPv4 - Filtrer sur un /24
        $req = 'UPDATE `images` SET abuse_network = SUBSTRING(ip_envoi, 1, (LENGTH(ip_envoi)-LOCATE(\'.\', REVERSE(ip_envoi))))
                    WHERE abuse_network = \'\'
                    AND LOCATE(\'.\', ip_envoi) != 0';
        MaBDD::getInstance()->query($req);
        // IPv6 - Filtrer sur un /56
        $req = 'UPDATE `images` SET abuse_network = SUBSTRING(HEX(INET6_ATON(ip_envoi)), 1, 14)
                    WHERE abuse_network = \'\'
                    AND LOCATE(\':\', ip_envoi) != 0';
        MaBDD::getInstance()->query($req);

        // Images potentiellement indésirables
        $req = 'SELECT im.new_name
                    FROM images im
                    LEFT JOIN possede po ON po.images_id = im.id
                    WHERE im.isBloquee = 0
                      AND im.isApprouvee = 0
                      AND (
                        /* Même réseau IP */
                        im.abuse_network IN (SELECT DISTINCT abuse_network FROM images WHERE isBloquee = 1)
                        OR (
                            /* Même propriétaire */
                            po.membres_id IS NOT NULL
                            AND
                            po.membres_id IN (SELECT DISTINCT membres_id FROM possede WHERE images_id IN (SELECT id FROM images WHERE isBloquee = 1))
                        )
                        OR (
                            /* Même MD5 */
                            im.md5 IN (SELECT DISTINCT md5 FROM images WHERE isBloquee = 1)
                        )
                    )
                    ORDER BY im.id DESC';
        return self::queryOnNewName($req);
    }

    /**
     * Joue une requête SQL et retourne un tableau "new_name"
     * @param string $req
     * @return ArrayObject
     */
    public static function queryOnNewName(string $req): ArrayObject
    {
        // Exécution de la requête
        $resultat = MaBDD::getInstance()->query($req);

        $monRetour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $monRetour->append($value->new_name);
        }

        return $monRetour;
    }
}
