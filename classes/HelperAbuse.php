<?php

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
