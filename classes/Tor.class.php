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

use JsonException;

/**
 * Fonctions relative à Tor
 */
class Tor
{
    private const string IPV4 = 'IPv4';
    private const string IPV6 = 'IPv6';

    /**
     * Mettre à jour la liste des adresses IP des noeuds de sortie Tor
     * @throws JsonException
     */
    public function updateListeExitNodes(): void
    {
        $torNodeList = file_get_contents(_TOR_EXIT_NODE_LIST_URL_);

        // Ne mettre à jour que si on a des données
        if (!empty($torNodeList)) {
            // Récupération du dernier fichier
            $objJson = json_decode($torNodeList, false, 512, JSON_THROW_ON_ERROR);

            $tabIP = [];
            $tabIP[self::IPV4] = [];
            $tabIP[self::IPV6] = [];

            foreach ($objJson->relays as $unRelay) {
                // Adresse IP de sortie (IPv4 uniquement)
                // https://metrics.torproject.org/onionoo.html#details_relay_exit_addresses
                if (isset($unRelay->exit_addresses)) {
                    foreach ($unRelay->exit_addresses as $uneIp) {
                        $this->addToTab($uneIp, $tabIP);
                    }
                }
                // Adresse IP sur lequel le noeud écoute (IPv4 + IPv6)
                // Lorsque exit_addresses incluera les IPv6, plus besoin de cette partie qui surbloque...
                if (isset($unRelay->or_addresses)) {
                    foreach ($unRelay->or_addresses as $uneIp) {
                        $this->addToTab($uneIp, $tabIP, true);
                    }
                }
            }

            // Enregister le résultat sur le disque
            $retour = file_put_contents(_TOR_LISTE_IPV4_, json_encode($tabIP[self::IPV4], JSON_THROW_ON_ERROR));
            echo 'IPv4 : ' . $retour;
            $retour = file_put_contents(_TOR_LISTE_IPV6_, json_encode($tabIP[self::IPV6], JSON_THROW_ON_ERROR));
            echo '<br />IPv6 : ' . $retour;
        } else {
            // Envoyer un mail d'avertissement
            mail(_ADMINISTRATEUR_EMAIL_, '[' . _SITE_NAME_ . '] - Actualisation des noeuds Tor en erreur', 'Liste de noeuds récupérée : ' . var_export($torNodeList, true), 'From: ' . _ADMINISTRATEUR_EMAIL_);
            die();
        }
    }

    /**
     * Nettoyer et ajouter une IP dans le tableau des adresses connues
     * @param string $ip @ IP à ajouter
     * @param string[] $tabIp Liste des addresses IP déjà connues
     * @param bool $withPort Le port est précisé (1.2.3.4:1234)
     */
    private function addToTab(string $ip, array &$tabIp, bool $withPort = false): void
    {
        if (substr_count($ip, ':') > 1) {
            // C'est une IPv6

            // Supprimer le port
            if ($withPort) {
                $ip = substr($ip, 0, strrpos($ip, ':'));
            }

            // Supprimer les crochets de la notation [1234:5678::]
            $ip = str_replace(['[', ']'], '', $ip);

            // Forcer la réécriture de l'IP
            $ip = inet_ntop(inet_pton($ip));

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $this->saveInTab($ip, $tabIp, self::IPV6);
            }
        } else {
            // C'est une IPv4

            // Supprimer le port
            if ($withPort) {
                $ip = substr($ip, 0, strrpos($ip, ':'));
            }

            // Valider l'IP et l'enregistrer si inconnue
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $this->saveInTab($ip, $tabIp, self::IPV4);
            }
        }
    }

    /**
     * Insérer dans le tableau une adresse sans faire de doublon
     * @param string $ip @ IP à ajouter
     * @param array $tabIp Liste des addresses IP déjà connues
     * @param string $typeIp IPv4 ou IPv6
     */
    private function saveInTab(string $ip, array &$tabIp, string $typeIp): void
    {
        if (!in_array($ip, $tabIp[$typeIp], true)) {
            $tabIp[$typeIp][] = self::formatIp($ip);
        }
    }

    /**
     * Formatter une adresse IP
     * @param string $ip adresse IP à formatter
     * @return string adresse IP formattée
     */
    private static function formatIp(string $ip): string
    {
        return inet_ntop(inet_pton($ip));
    }

    /**
     * Vérifie si une IP correspond à un noeud de sortie Tor
     * @param string $ip
     * @return bool
     */
    public static function checkIp(string $ip): bool
    {
        $monRetour = true;

        $ip = self::formatIp($ip);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if (file_exists(_TOR_LISTE_IPV6_) && filesize(_TOR_LISTE_IPV6_) > 0) {
                try {
                    $tabIp = json_decode(file_get_contents(_TOR_LISTE_IPV6_), true, 512, JSON_THROW_ON_ERROR);
                    if (!in_array($ip, $tabIp, true)) {
                        $monRetour = false;
                    }
                } catch (JsonException $e) {
                    // En cas d'erreur, par défaut, faire confiance à l'IP
                    $monRetour = false;
                }
            }
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (file_exists(_TOR_LISTE_IPV4_) && filesize(_TOR_LISTE_IPV4_) > 0) {
                try {
                    $tabIp = json_decode(file_get_contents(_TOR_LISTE_IPV4_), true, 512, JSON_THROW_ON_ERROR);
                    if (!in_array($ip, $tabIp, true)) {
                        $monRetour = false;
                    }
                } catch (JsonException $e) {
                    // En cas d'erreur, par défaut, faire confiance à l'IP
                    $monRetour = false;
                }
            }
        }

        return $monRetour;
    }
}
