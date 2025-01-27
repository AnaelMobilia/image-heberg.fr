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

class HelperAbuse
{
    /**
     * Retourne le nombre d'images bloquées issues du même réseau IP
     * @param string $remote_addr Adresse IP à tester
     * @return int
     */
    public static function checkIpReputation(string $remote_addr): int
    {
        $monRetour = 0;

        // IPv4 - Filtrer sur un /24 || IPv6 - Filtrer sur un /56
        $req = MaBDD::getInstance()->prepare(
            'SELECT COUNT(*) AS nb
                FROM images
                WHERE isBloquee = 1
                AND abuse_network = (
                    IF(LOCATE(\'.\', :remote_addr) != 0,
                        SUBSTRING(:remote_addr, 1, (LENGTH(:remote_addr) - LOCATE(\'.\', REVERSE(:remote_addr)))),
                        SUBSTRING(HEX(INET6_ATON(:remote_addr)), 1, 14)
                    )
                )'
        );
        $req->bindValue(':remote_addr', $remote_addr);
        $req->execute();
        $resultat = $req->fetch();
        if ($resultat !== false) {
            $monRetour = (int)$resultat->nb;
        }

        return $monRetour;
    }

    /**
     * Mettre à jour la réputation des adresses IP en base
     * @return void
     */
    public static function updateIpReputation(): void
    {
        // Compléter les données "abuse_network" (normalement déjà fait dans ImageObject::creer())
        // IPv4 - Filtrer sur un /24 || IPv6 - Filtrer sur un /56
        $req = 'UPDATE images SET abuse_network =
                    IF(LOCATE(\'.\', remote_addr) != 0,
                        SUBSTRING(remote_addr, 1, (LENGTH(remote_addr) - LOCATE(\'.\', REVERSE(remote_addr)))),
                        SUBSTRING(HEX(INET6_ATON(remote_addr)), 1, 14)
                    )
                    WHERE abuse_network = \'\'';
        MaBDD::getInstance()->query($req);
    }
}
