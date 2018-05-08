<?php
/*
 * Copyright 2008-2018 Anael Mobilia
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
 * Gestion des sessions
 */
class sessionObject {
   // @ IP de l'utilisateur
    private $IP;
    // Objet utilisateur
    private $userObject;

    public function __construct() {
        // Je vérifie qu'une session n'est pas déjà lancée & que pas en test unitaire
        if (session_status() === PHP_SESSION_NONE && _TRAVIS_ === FALSE) {
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
     * @return utilisateurObject
     */
    private function getUserObject() {
       return $this->userObject;
    }
    
    /**
     * Mon utilisateur
     * @param utilisateurObject $userObject Objet utilisateur
     */
    public function setUserObject($userObject) {
       $this->userObject = $userObject;
       $_SESSION['userObject'] = $userObject;
    }
    
    /**
     * Nom d'utilisateur
     * @return type
     */
    public function getUserName() {
        return $this->getUserObject()->getUserName();
    }

    /**
     * @ IP
     * @return type
     */
    public function getIP() {
        return $this->IP;
    }

    /**
     * Niveau de droits
     * @return type
     */
    public function getLevel() {
        // Si un utilisateur est défini 
        if(isset($this->userObject)) {
           return $this->getUserObject()->getLevel();
        } else {
           // Sinon visiteur par défaut !
           return utilisateurObject::levelGuest;
        }
    }

    /**
     * ID en BDD
     * @return type
     */
    public function getId() {
        return $this->getUserObject()->getId();
    }

    /**
     * IP
     * @param type $IP
     */
    public function setIP($IP) {
        $this->IP = $IP;
        // On enregistre dans la session
        $_SESSION['IP'] = $this->getIP();
    }

    /**
     * Vérification des droits de l'utilisateur pour la page
     * @param type $levelRequis
     * @return boolean
     */
    public function verifierDroits($levelRequis) {
        if ($this->getLevel() >= $levelRequis) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Déconnexion d'un utilisateur
     */
    public function deconnexion() {
        if (!_TRAVIS_) {
            // Je détruis la session
            session_destroy();
        }
    }

}