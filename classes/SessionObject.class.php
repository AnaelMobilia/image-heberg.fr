<?php

/*
 * Copyright 2008-2022 Anael MOBILIA
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

/**
 * Gestion des sessions
 */
class SessionObject
{
    // @ IP de l'utilisateur
    private string $IP = "";
    // Objet utilisateur
    private UtilisateurObject $userObject;

    public function __construct()
    {
        // Je vérifie qu'une session n'est pas déjà lancée & que pas tests travis (session_start déjà effectué)
        if (session_status() === PHP_SESSION_NONE && !_PHPUNIT_) {
            // Je lance la session côté PHP
            session_start();
        }

        // Si j'ai déjà une session existante
        if (isset($_SESSION['userObject'])) {
            // Si l'@ IP correspond
            if ($_SESSION['IP'] === $_SERVER['REMOTE_ADDR']) {
                // On recharge les informations
                $this->setIP($_SESSION['IP']);
                $this->setUserObject($_SESSION['userObject']);
            }
        }
    }

    /**
     * Mon utilisateur
     * @return UtilisateurObject
     */
    private function getUserObject(): UtilisateurObject
    {
        return $this->userObject ?? new UtilisateurObject();
    }

    /**
     * Mon utilisateur
     * @param UtilisateurObject $userObject Objet utilisateur
     */
    public function setUserObject(UtilisateurObject $userObject): void
    {
        $this->userObject = $userObject;
        $_SESSION['userObject'] = $userObject;
    }

    /**
     * Nom d'utilisateur
     * @return string
     */
    public function getUserName(): string
    {
        return $this->getUserObject()->getUserName();
    }

    /**
     * @ IP
     * @return string
     */
    public function getIP(): string
    {
        return $this->IP;
    }

    /**
     * Niveau de droits
     * @return int
     */
    public function getLevel(): int
    {
        return $this->getUserObject()->getLevel();
    }

    /**
     * ID en BDD
     * @return int
     */
    public function getId(): int
    {
        return $this->getUserObject()->getId();
    }

    /**
     * IP
     * @param string $IP
     */
    public function setIP(string $IP): void
    {
        $this->IP = $IP;
        // On enregistre dans la session
        $_SESSION['IP'] = $this->getIP();
    }

    /**
     * Vérification des droits de l'utilisateur pour la page
     * @param int $levelRequis
     * @return bool
     */
    public function verifierDroits(int $levelRequis): bool
    {
        if ($this->getLevel() >= $levelRequis) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Déconnexion d'un utilisateur
     */
    public function deconnexion(): void
    {
        // Destruction de l'objet utilisateur
        unset($_SESSION['userObject']);

        if (!_PHPUNIT_) {
            // Je détruis la session
            session_destroy();
        }
    }

    /**
     * Active le flag de suivi (vérification d'affichage de page avant envoi)
     */
    public function setFlag(): void
    {
        $_SESSION['flag'] = time();
    }

    /**
     * Supprime le flag de suivi
     */
    public function removeFlag(): void
    {
        unset($_SESSION['flag']);
    }

    /**
     * Vérifie le flag de suivi (a été activé il y a plus d'une seconde)
     * @return bool Suivi OK ?
     */
    public function checkFlag(): bool
    {
        $monRetour = false;
        if (isset($_SESSION['flag'])) {
            // Au moins une seconde pour remplir le formulaire
            if (time() - $_SESSION['flag'] > 1) {
                $monRetour = true;
            }
        }
        return $monRetour;
    }
}
