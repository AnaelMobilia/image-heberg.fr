<?php

/*
 * Copyright 2008-2025 Anael MOBILIA
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
               AND im.date_action < DATE_SUB(CURRENT_DATE(), INTERVAL ' . _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ . ' DAY)
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
        // Effacer les logs de connexion > 1 an
        $req = 'DELETE
               FROM login
               WHERE date_action < (NOW() - INTERVAL 1 YEAR)';
        MaBDD::getInstance()->query($req);

        // Toutes les comptes créés et jamais utilisés depuis xx jours
        $req = 'SELECT m.id
               FROM membres m
               WHERE m.date_action < DATE_SUB(CURRENT_DATE(), INTERVAL ' . _DELAI_EFFACEMENT_COMPTES_JAMAIS_UTILISES_ . ' DAY)
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
     * @param bool $useProjection Projeter l'utilisation actuelle de l'image (potentialité de dépassement de limite ultérieur)
     * @param bool $includeApproved Inclure les images approuvées (ou signalées) ?
     * @return ArrayObject
     */
    public static function getImagesTropAffichees(int $nbMax, bool $onlyOnImagesSuspectes = false, bool $useProjection = false, bool $includeApproved = false): ArrayObject
    {
        if ($onlyOnImagesSuspectes) {
            $tabNewName = [];
            foreach ((array)self::getImagesPotentiellementIndesirables() as $newName) {
                $tabNewName[] = '\'' . str_replace('\'', '', $newName) . '\'';
            }
            if (empty($tabNewName)) {
                // Mettre un placeholder vide
                $listeImagesForIn = '\'\'';
            } else {
                $listeImagesForIn = implode(',', $tabNewName);
            }
        }

        // Images avec trop d'affichages
        if ($useProjection) {
            // Ne prendre que les images qui sont présentes depuis plus d'une heure pour limiter les faux positifs
            $req = 'SELECT im.new_name, ( ( im.nb_view_v4 + im.nb_view_v6 + (SELECT IFNULL(SUM(th.nb_view_v4 + th.nb_view_v6), 0) FROM thumbnails th where th.images_id = im.id) ) / IF(im.date_action < (NOW() - INTERVAL 1 HOUR), ( UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(im.date_action) ), -1) ) * (60 * 60 * 24 ) as nbViewPerDay';
        } else {
            $req = 'SELECT im.new_name, ( im.nb_view_v4 + im.nb_view_v6 + (SELECT IFNULL(SUM(th.nb_view_v4 + th.nb_view_v6), 0) FROM thumbnails th where th.images_id = im.id) ) / IF(DATEDIFF(NOW(), im.date_action) > 0, DATEDIFF(NOW(), im.date_action), 1) as nbViewPerDay';
        }
        $req .= ' FROM images im
            WHERE im.isBloquee = 0';
        if (!$includeApproved) {
            $req .= ' AND im.isSignalee = 0
            AND im.isApprouvee = 0';
        }
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
        // Compléter les données "abuse_network" (normalement déjà fait dans ImageObject::creer())
        HelperAbuse::updateIpReputation();

        // Images potentiellement indésirables
        $req = 'SELECT new_name FROM (
                    SELECT im.new_name, ((nb_view_v4 + nb_view_v6) / DATEDIFF(NOW(), im.date_action)) AS nbAff
                        FROM images im
                        LEFT JOIN possede po ON po.images_id = im.id
                        WHERE im.isBloquee = 0
                          AND im.isApprouvee = 0
                          AND im.isSignalee = 0
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
                        ORDER BY nbAff DESC, im.id DESC
                    ) tableTmp
                    LIMIT 0, 100';
        return self::queryOnNewName($req);
    }

    /**
     * Images dont les données sont proches d'images déjà approuvées
     * @return ArrayObject
     */
    public static function getImagesPotentiellementApprouvables(): ArrayObject
    {
        // Compléter les données "abuse_network" (normalement déjà fait dans ImageObject::creer())
        HelperAbuse::updateIpReputation();

        // Images potentiellement approuvables
        $req = 'SELECT new_name FROM (
                    SELECT im.new_name, ((nb_view_v4 + nb_view_v6) / DATEDIFF(NOW(), im.date_action)) AS nbAff
                        FROM images im
                        LEFT JOIN possede po ON po.images_id = im.id
                        WHERE im.isBloquee = 0
                          AND im.isApprouvee = 0
                          AND im.isSignalee = 0
                          AND (
                            /* Même réseau IP */
                            im.abuse_network IN (SELECT DISTINCT abuse_network FROM images WHERE isApprouvee = 1)
                            OR (
                                /* Même propriétaire */
                                po.membres_id IS NOT NULL
                                AND
                                po.membres_id IN (SELECT DISTINCT membres_id FROM possede WHERE images_id IN (SELECT id FROM images WHERE isApprouvee = 1))
                            )
                            OR (
                                /* Même MD5 */
                                im.md5 IN (SELECT DISTINCT md5 FROM images WHERE isApprouvee = 1)
                            )
                            /*OR (
                                /* Même nom originel * /
                                im.old_name IN (SELECT DISTINCT old_name FROM images WHERE isApprouvee = 1)
                            )*/
                        )
                    HAVING nbAff > ' . (_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ / 2) . '
                    ORDER BY nbAff DESC, im.id DESC
                ) tableTmp
                LIMIT 0, 25';
        return self::queryOnNewName($req);
    }

    /**
     * Liste des réseaux avec mauvaise réputation
     * @return ArrayObject ["IP" => "count()"]
     */
    public static function getBadNetworks(): ArrayObject
    {
        $monRetour = new ArrayObject();

        $req = 'SELECT COUNT(*) AS nb, abuse_network FROM images WHERE isBloquee = 1 GROUP BY abuse_network';
        // Exécution de la requête
        $resultat = MaBDD::getInstance()->query($req);

        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            $ip = $value->abuse_network;
            // Formatter les IPv6
            if (!str_contains($ip, '.')) {
                $ip = implode(':', str_split($ip, 4));
                $ip .= '::/56';
            } else {
                $ip .= '.0/24';
            }
            $monRetour->offsetSet($ip, $value->nb);
        }
        // Tri "humain"
        $monRetour->ksort(SORT_NATURAL);

        return $monRetour;
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
