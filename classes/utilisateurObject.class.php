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
 * Gestion (BDD) des utilisateurs
 *
 * @author anael
 */
class utilisateurObject {
    private $userName;
    private $password;
    private $email;
    private $dateInscription;
    private $ipInscription;
    private $level;
    private $id;

    // Niveaux de droits
    const levelGuest = 0;
    const levelUser = 1;
    const levelAdmin = 2;

    public function __construct($userID = FALSE) {
        // Utilisateur à charger
        if ($userID) {
            if (!$this->charger($userID)) {
                // Envoi d'une exception si l'utilisateur n'existe pas
                throw new Exception('Utilisateur ' . $userID . ' inexistant.');
            }
        }
        // Cas par défaut
        else {
            $this->setLevel(utilisateurObject::levelGuest);
            $this->setId(0);
        }
    }

    /**
     * Nom d'utilisateur avec htmlentities
     * @return string
     */
    public function getUserName() {
        return htmlentities($this->userName);
    }

    /**
     * BDD - Nom d'utilisateur non htmlentities
     * @return string
     */
    private function getUserNameBDD() {
        return $this->userName;
    }

    /**
     * Mot de passe
     * @return string
     */
    private function getPassword() {
        return $this->password;
    }

    /**
     * Email
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Date d'inscription
     * @return type
     */
    private function getDateInscription() {
        return $this->dateInscription;
    }

    /**
     * Date d'inscription formatée
     * @return type
     */
    public function getDateInscriptionFormate() {
        $phpdate = strtotime($this->dateInscription);
        return date("d/m/Y", $phpdate);
    }

    /**
     * @ IP d'inscription
     * @return string
     */
    public function getIpInscription() {
        return $this->ipInscription;
    }

    /**
     * Niveau de droits
     * @return int
     */
    public function getLevel() {
        return (int) $this->level;
    }

    /**
     * ID en BDD
     * @return int
     */
    public function getId() {
        return (int) $this->id;
    }

    /**
     * Nom d'utilisateur
     * @param string $userName
     */
    public function setUserName($userName) {
        $this->userName = $userName;
    }

    /**
     * Mot de passe
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Mot de passe à crypter
     * @param string $password
     */
    public function setPasswordToCrypt($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Email
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * Date d'inscription
     * @param type $dateInscription
     */
    private function setDateInscription($dateInscription) {
        $this->dateInscription = $dateInscription;
    }

    /**
     * @ IP d'inscription
     * @param string $ipInscription
     */
    private function setIpInscription($ipInscription) {
        $this->ipInscription = $ipInscription;
    }

    /**
     * Niveau de droits
     * @param int $level
     */
    public function setLevel($level) {
        $this->level = $level;
    }

    /**
     * ID en BDD
     * @param int $id
     */
    private function setId($id) {
        $this->id = $id;
    }

    /**
     * Connexion d'un utilisateur : vérification & création de la session
     * @return boolean
     */
    public function connexion() {
        // Le sessionObject qui sera retourné
        $monUser = new sessionObject();

        $req = maBDD::getInstance()->prepare("SELECT * FROM membres WHERE login = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getUserName(), PDO::PARAM_STR);
        $req->execute();

        // Je récupère les potentielles valeurs
        $values = $req->fetch();

        // Si l'utilisateur n'existe pas... on retourne false
        if ($values === FALSE) {
            return FALSE;
        }

        // Faut-il mettre à jour le hash du mot de passe ?
        $updateHash = FALSE;

        // Est-ce un cas de compatibilité avec les anciens mots de passe ?
        if (substr($values->pass, 0, 1) !== '$') {
            // Les hash générés par crypt possédent un schème spécifique avec $ en premier chr
            // https://en.wikipedia.org/wiki/Crypt_(C)#Key_derivation_functions_supported_by_crypt
            if (!hash_equals($values->pass, hash('sha256', _GRAIN_DE_SEL_ . $this->getPassword()))) {
                // Ancien mot de passe + ne matche pas
                return FALSE;
            } else {
                // Ancien mot de passe + matche
                // => update password ;-)
                $updateHash = TRUE;
            }
        } else {
            // Cas standard : comparaison du hash du mot de passe fourni avec celui stocké en base
            if (! password_verify($this->getPassword(), $values->pass)) {
                // Nouveau mot de passe + ne matche pas
                return FALSE;
            } else {
                // Nouveau mot de passe + matche
                // => Faut-il mettre à jour le cryptage utilisé ?
                if (password_needs_rehash($values->pass, PASSWORD_DEFAULT)) {
                    $updateHash = TRUE;
                }
            }
        }

        // Mise à jour du hash si requis
        if ($updateHash) {
            $this->charger($values->id);
            $this->setPasswordToCrypt($this->getPassword());
            $this->modifier();
        }
        
        // Je supprime le mot de passe de l'objet
        $this->setPassword('');

        // Je charge les informations de la session
        $monUser->setIP($_SERVER['REMOTE_ADDR']);
        $monUser->setId($values->id);
        $monUser->setLevel($values->lvl);
        $monUser->setUserName($values->login);

        // J'enregistre en BDD la connexion réussie
        $req = maBDD::getInstance()->prepare("INSERT INTO login (ip_login, date_login, pk_membres) VALUES (?, NOW(), ?)");
        $req->bindValue(1, $monUser->getIP(), PDO::PARAM_STR);
        $req->bindValue(2, $monUser->getId(), PDO::PARAM_INT);

        $req->execute();

        // On dit que tout s'est bien passé
        return TRUE;
    }

    /**
     * Charge un utilisateur depuis la BDD
     * @param int $userID ID en BDD
     */
    public function charger($userID) {
        $monRetour = FALSE;

        // Je récupère les données en BDD
        $req = maBDD::getInstance()->prepare("SELECT * FROM membres WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $userID, PDO::PARAM_INT);
        $req->execute();

        // Je récupère les potentielles valeurs
        $values = $req->fetch();

        // Si l'utilisateur n'existe pas... on retourne un utilisateurObject vide
        if ($values !== FALSE) {
            // Je charge les informations de l'utilisateur (sauf password)
            $this->setId($userID);
            $this->setEmail($values->email);
            $this->setUserName($values->login);
            $this->setDateInscription($values->date_inscription);
            $this->setIpInscription($values->ip_inscription);
            $this->setLevel($values->lvl);

            // Gestion du retour
            $monRetour = TRUE;
        }

        return $monRetour;
    }

    /**
     * Enregistrement (BDD) d'un utilisateur
     */
    public function enregistrer() {
        $req = maBDD::getInstance()->prepare("INSERT INTO membres (email, login, pass, date_inscription, ip_inscription, lvl) VALUES (?, ?, ?, NOW(), ?, ?)");
        $req->bindValue(1, $this->getEmail(), PDO::PARAM_STR);
        $req->bindValue(2, $this->getUserNameBDD(), PDO::PARAM_STR);
        $req->bindValue(3, $this->getPassword(), PDO::PARAM_STR);
        // Date est définie par NOW()
        $req->bindValue(4, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $req->bindValue(5, $this->getLevel(), PDO::PARAM_INT);

        $req->execute();
    }

    /**
     * Modifier (BDD) un utilisateur déjà existant
     */
    public function modifier() {
        $req = maBDD::getInstance()->prepare("UPDATE membres SET email = ?, login = ?, pass = ?, lvl = ? WHERE id = ?");
        $req->bindValue(1, $this->getEmail(), PDO::PARAM_STR);
        $req->bindValue(2, $this->getUserNameBDD(), PDO::PARAM_STR);
        // ?????
        $req->bindValue(3, $this->getPassword(), PDO::PARAM_STR);
        $req->bindValue(4, $this->getLevel(), PDO::PARAM_INT);
        $req->bindValue(5, $this->getId(), PDO::PARAM_INT);

        $req->execute();
    }

    /**
     * Suppression (BDD) d'un utilisateur
     */
    public function supprimer() {
        // Les images possédées
        $req = maBDD::getInstance()->prepare("DELETE FROM possede WHERE pk_membres = ?");
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();

        // Historique des logins
        $req = maBDD::getInstance()->prepare("DELETE FROM login WHERE pk_membres = ?");
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();

        // Paramètres du compte
        $req = maBDD::getInstance()->prepare("DELETE FROM membres WHERE id = ?");
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();
    }

    /**
     * Assigne une image à un utilisateur en BDD
     * @param imageObject $imageObject
     */
    public function assignerImage($imageObject) {
        if ($this->getId() === 0) {
            throw new Exception("Aucun utilisateur n'est défini !");
        }

        // Les images possédées
        $req = maBDD::getInstance()->prepare("INSERT INTO possede (image_id, pk_membres) VALUES (?, ?)");
        $req->bindValue(1, $imageObject->getId(), PDO::PARAM_INT);
        $req->bindValue(2, $this->getId(), PDO::PARAM_INT);
        $req->execute();
    }

}