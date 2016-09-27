<?php
/*
* Copyright 2008-2016 Anael Mobilia
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
 *
 * @author anael
 */
class sessionObject {

    private $userName;
    private $IP;
    private $level;
    private $id;

    public function __construct() {
        // Je vérifie qu'une session n'est pas déjà lancée
        if (session_status() === PHP_SESSION_NONE) {
            // Je lance la session côté PHP
            session_start();
        }

        // Si j'ai déjà une session existante
        if (isset($_SESSION['id'])) {
            // Si l'@ IP correspond
            if ($_SESSION['IP'] === $_SERVER['REMOTE_ADDR']) {
                // On recharge les informations
                $this->setIP($_SESSION['IP']);
                $this->setId($_SESSION['id']);
                $this->setLevel($_SESSION['level']);
                $this->setUserName($_SESSION['userName']);

                // TODO : à supprimer : compatibilité pour la migration
                $_SESSION['connected'] = TRUE;
                $_SESSION['user_id'] = $this->getId();
                $_SESSION['user'] = $this->getUserName();
            } else {
                // Par défaut on défini un niveau Invité
                $this->setLevel(utilisateurObject::levelGuest);
            }
        } else {
            // Par défaut on défini un niveau Invité
            $this->setLevel(utilisateurObject::levelGuest);
        }
    }

    /**
     * Nom d'utilisateur
     * @return type
     */
    public function getUserName() {
        return $this->userName;
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
        return $this->level;
    }

    /**
     * ID en BDD
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Nom d'utilisateur - htmlentities
     * @param type $userName
     */
    public function setUserName($userName) {
        $this->userName = htmlentities($userName);
        // On enregistre dans la session
        $_SESSION['userName'] = $this->getUserName();
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
     * Niveau de droits
     * @param type $level
     */
    public function setLevel($level) {
        // TODO : en attendant la mise à jour de la BDD
        if ($level === 'admin') {
            $level = utilisateurObject::levelAdmin;
        } elseif ($level === 'user') {
            $level = utilisateurObject::levelUser;
        }
        $this->level = $level;
        // On enregistre dans la session
        $_SESSION['level'] = $this->getLevel();
    }

    /**
     * ID en BDD
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
        // On enregistre dans la session
        $_SESSION['id'] = $this->getId();
    }

    /**
     * On vérifie que l'utilisateur à les droits pour la page
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
        // Je détruis la session
        session_destroy();
    }

}
