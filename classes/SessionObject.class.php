<?php

/*
 * Copyright 2008-2020 Anael MOBILIA
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
    private $IP;
    // Objet utilisateur
    private $userObject;

    public function __construct()
    {
        // Je vérifie qu'une session n'est pas déjà lancée & que pas tests travis (session_start déjà effectué)
        if (session_status() === PHP_SESSION_NONE && !_TRAVIS_) {
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
    private function getUserObject()
    {
        if (isset($this->userObject)) {
            return $this->userObject;
        } else {
            return new UtilisateurObject();
        }
    }

    /**
     * Mon utilisateur
     * @param UtilisateurObject $userObject Objet utilisateur
     */
    public function setUserObject($userObject)
    {
        $this->userObject = $userObject;
        $_SESSION['userObject'] = $userObject;
    }

    /**
     * Nom d'utilisateur
     * @return string
     */
    public function getUserName()
    {
        return $this->getUserObject()->getUserName();
    }

    /**
     * @ IP
     * @return string
     */
    public function getIP()
    {
        return $this->IP;
    }

    /**
     * Niveau de droits
     * @return type
     */
    public function getLevel()
    {
        return $this->getUserObject()->getLevel();
    }

    /**
     * ID en BDD
     * @return int
     */
    public function getId()
    {
        return (int) $this->getUserObject()->getId();
    }

    /**
     * IP
     * @param string $IP
     */
    public function setIP($IP)
    {
        $this->IP = $IP;
        // On enregistre dans la session
        $_SESSION['IP'] = $this->getIP();
    }

    /**
     * Vérification des droits de l'utilisateur pour la page
     * @param type $levelRequis
     * @return boolean
     */
    public function verifierDroits($levelRequis)
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
    public function deconnexion()
    {
        // Destruction de l'objet utilisateur
        unset($_SESSION['userObject']);

        if (!_TRAVIS_) {
            // Je détruis la session
            session_destroy();
        }
    }

    /**
     * Active le flag de suivi (vérification d'affichage de page avant envoi)
     */
    public function setFlag()
    {
        $_SESSION['flag'] = true;
    }

    /**
     * Supprime le flag de suivi
     */
    public function removeFlag()
    {
        unset($_SESSION['flag']);
    }

    /**
     * Vérifie le flag de suivi
     * @return boolean Suivi OK ?
     */
    public function checkFlag()
    {
        return isset($_SESSION['flag']);
    }
}
