<?php

/*
 * Copyright 2008-2024 Anael MOBILIA
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

use InvalidArgumentException;
use PDO;
use ArrayObject;

/**
 * Gestion (BDD) des utilisateurs
 */
class UtilisateurObject
{
    private string $userName = '';
    private string $password = '';
    private string $email = '';
    private string $dateInscription = '';
    private string $ipInscription = '';
    private int $level = self::LEVEL_GUEST;
    private int $id = 0;
    private bool $isActif = true;
    private string $token = '';

    // Niveaux de droits
    final public const int LEVEL_GUEST = 0;
    final public const int LEVEL_USER = 1;
    final public const int LEVEL_ADMIN = 2;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct($userID = false)
    {
        // Utilisateur à charger
        if ($userID && !$this->charger($userID)) {
            // Envoi d'une exception si l'utilisateur n'existe pas
            throw new InvalidArgumentException('Utilisateur ' . $userID . ' inexistant.');
        }
    }

    /**
     * Nom d'utilisateur avec htmlentities
     * @return string
     */
    public function getUserName(): string
    {
        return htmlentities($this->userName);
    }

    /**
     * BDD - Nom d'utilisateur non htmlentities
     * @return string
     */
    private function getUserNameBDD(): string
    {
        return $this->userName;
    }

    /**
     * Mot de passe
     * @return string
     */
    private function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Email
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Date d'inscription
     * @return string
     */
    private function getDateInscription(): string
    {
        return $this->dateInscription;
    }

    /**
     * Date d'inscription formatée
     * @return string
     */
    public function getDateInscriptionFormate(): string
    {
        $phpdate = strtotime($this->getDateInscription());
        return date('d/m/Y', $phpdate);
    }

    /**
     * @ IP d'inscription
     * @return string
     */
    public function getIpInscription(): string
    {
        return $this->ipInscription;
    }

    /**
     * Niveau de droits
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * ID en BDD
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Utilisateur est actif ?
     * @return bool
     */
    public function getIsActif(): bool
    {
        return $this->isActif;
    }

    /**
     * Token associé au compte utilisateur
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Utilisateur est actif ?
     * @param bool $isActif
     */
    public function setIsActif(bool $isActif): void
    {
        $this->isActif = $isActif;
    }

    /**
     * Token lié à l'utilisateur
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Nom d'utilisateur
     * @param string $userName
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    /**
     * Mot de passe
     * @param string $password
     */
    private function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Mot de passe à crypter
     * @param string $password
     */
    public function setPasswordToCrypt(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Email
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Date d'inscription
     * @param string $dateInscription
     */
    private function setDateInscription(string $dateInscription): void
    {
        $this->dateInscription = $dateInscription;
    }

    /**
     * @ IP d'inscription
     * @param string $ipInscription
     */
    private function setIpInscription(string $ipInscription): void
    {
        $this->ipInscription = $ipInscription;
    }

    /**
     * Niveau de droits
     * @param int $level
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * ID en BDD
     * @param int $id
     */
    private function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Connexion d'un utilisateur : vérification & création de la session
     * @param string $user Nom de l'utilisateur
     * @param string $pwd Mot de passe associé
     * @return int ID de l'utilisateur (0 si identifiants invalides)
     */
    private function verifierIdentifiants(string $user, string $pwd): int
    {
        // Identifiants KO par défaut
        $monRetour = 0;

        // Vérification de l'existance du login
        $req = MaBDD::getInstance()->prepare('SELECT * FROM membres WHERE login = :login');
        $req->bindValue(':login', $user);
        $req->execute();

        // Je récupère les potentielles valeurs
        $resultat = $req->fetch();

        // Si l'utilisateur existe
        if ($resultat !== false) {
            // Faut-il mettre à jour le hash du mot de passe ?
            $updateHash = false;

            // Est-ce un cas de compatibilité avec les anciens mots de passe ?
            if (!str_starts_with($resultat->password, '$')) {
                // Les hash générés par crypt possédent un schème spécifique avec $ en premier chr
                // https://en.wikipedia.org/wiki/Crypt_(C)#Key_derivation_functions_supported_by_crypt
                if (hash_equals($resultat->password, hash('sha256', _GRAIN_DE_SEL_ . $pwd))) {
                    // Ancien mot de passe => update hash du password ;-)
                    $updateHash = true;
                    // Identifiants matchent !
                    $monRetour = $resultat->id;
                }
            } elseif (password_verify($pwd, $resultat->password)) {
                // Cas standard : comparaison du hash du mot de passe fourni avec celui stocké en base
                // => Faut-il mettre à jour le chiffrement utilisé ?
                if (password_needs_rehash($resultat->password, PASSWORD_DEFAULT)) {
                    $updateHash = true;
                }
                // Identifiants matchent !
                $monRetour = $resultat->id;
            }

            // Mise à jour du hash si requis
            if ($updateHash) {
                $monUtilisateur = new UtilisateurObject();
                $monUtilisateur->charger($resultat->id);
                $monUtilisateur->setPasswordToCrypt($pwd);
                $monUtilisateur->modifier();
            }
        }
        return $monRetour;
    }

    /**
     * Connexion d'un utilisateur : vérification & création de la session
     * @param string $user Utilisateur
     * @param string $pwd Mot de passe
     * @return bool
     */
    public function connexion(string $user, string $pwd): bool
    {
        // Protection contre une attaque : on délaie un peu l'action
        usleep(500000);
        // Ma session
        $maSession = new SessionObject();
        // Mon retour
        $monRetour = false;

        // Vérification des identifiants
        $userID = $this->verifierIdentifiants($user, $pwd);
        if ($userID) {
            $monRetour = true;

            // Chargement de mon utilisateur
            $this->charger($userID);

            // Je complète les variables de la session
            $maSession->setIP($_SERVER['REMOTE_ADDR']);
            $maSession->setUserObject($this);

            // J'enregistre en BDD la connexion réussie
            $req = MaBDD::getInstance()->prepare('INSERT INTO login (remote_addr, date_action, membres_id) VALUES (:ipLogin, NOW(), :membresId)');
            $req->bindValue(':ipLogin', $_SERVER['REMOTE_ADDR']);
            $req->bindValue(':membresId', $userID, PDO::PARAM_INT);

            $req->execute();
        }

        // Retour...
        return $monRetour;
    }

    /**
     * Charge un utilisateur depuis la BDD
     * @param int $userID ID en BDD
     * @return bool Utilisateur existant ?
     */
    private function charger(int $userID): bool
    {
        $monRetour = false;

        // Je récupère les données en BDD
        $req = MaBDD::getInstance()->prepare('SELECT * FROM membres WHERE id = :id');
        $req->bindValue(':id', $userID, PDO::PARAM_INT);
        $req->execute();

        // Je récupère les potentielles valeurs
        $resultat = $req->fetch();

        // Si l'utilisateur n'existe pas... on retourne un UtilisateurObject vide
        if ($resultat !== false) {
            // Je charge les informations de l'utilisateur (sauf password)
            $this->setId($userID);
            $this->setEmail($resultat->email);
            $this->setUserName($resultat->login);
            $this->setDateInscription($resultat->date_action);
            $this->setIpInscription($resultat->remote_addr);
            $this->setLevel($resultat->lvl);
            $this->setPassword($resultat->password);
            $this->setIsActif($resultat->isActif);
            $this->setToken($resultat->token);

            // Gestion du retour
            $monRetour = true;
        }

        return $monRetour;
    }

    /**
     * Enregistrement (BDD) d'un utilisateur
     */
    public function enregistrer(): void
    {
        $req = MaBDD::getInstance()->prepare('INSERT INTO membres (email, login, password, date_action, remote_addr, lvl, isActif, token) VALUES (:email, :login, :password, NOW(), :ipInscription, :lvl, :isActif, :token)');
        $req->bindValue(':email', $this->getEmail());
        $req->bindValue(':login', $this->getUserNameBDD());
        $req->bindValue(':password', $this->getPassword());
        // Date est définie par NOW()
        $req->bindValue(':ipInscription', $_SERVER['REMOTE_ADDR']);
        $req->bindValue(':lvl', $this->getLevel(), PDO::PARAM_INT);
        $req->bindValue(':isActif', $this->getIsActif(), PDO::PARAM_BOOL);
        $req->bindValue(':token', $this->getToken());

        $req->execute();
    }

    /**
     * Modifier (BDD) un utilisateur déjà existant
     */
    public function modifier(): void
    {
        $req = MaBDD::getInstance()->prepare('UPDATE membres SET email = :email, login = :login, password = :password, lvl = :lvl, isActif = :isActif, token = :token WHERE id = :id');
        $req->bindValue(':email', $this->getEmail());
        $req->bindValue(':login', $this->getUserNameBDD());
        $req->bindValue(':password', $this->getPassword());
        $req->bindValue(':lvl', $this->getLevel(), PDO::PARAM_INT);
        $req->bindValue(':isActif', $this->getIsActif(), PDO::PARAM_BOOL);
        $req->bindValue(':token', $this->getToken());
        $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);

        $req->execute();
    }

    /**
     * Suppression (BDD) d'un utilisateur
     */
    public function supprimer(): void
    {
        // Les images possédées
        $req = MaBDD::getInstance()->prepare('DELETE FROM possede WHERE membres_id = :membresId');
        $req->bindValue(':membresId', $this->getId(), PDO::PARAM_INT);
        $req->execute();

        // Historique des logins
        $req = MaBDD::getInstance()->prepare('DELETE FROM login WHERE membres_id = :membresId');
        $req->bindValue(':membresId', $this->getId(), PDO::PARAM_INT);
        $req->execute();

        // Paramètres du compte
        $req = MaBDD::getInstance()->prepare('DELETE FROM membres WHERE id = :id');
        $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        $req->execute();
    }

    /**
     * Assigne une image à un utilisateur en BDD
     * @param ImageObject $imageObject
     * @throws Exception
     */
    public function assignerImage(ImageObject $imageObject): void
    {
        if ($this->getId() === 0) {
            throw new InvalidArgumentException('Aucun utilisateur n\'est défini !');
        }

        // Les images possédées
        $req = MaBDD::getInstance()->prepare('INSERT INTO possede (images_id, membres_id) VALUES (:imagesId, :membresId)');
        $req->bindValue(':imagesId', $imageObject->getId(), PDO::PARAM_INT);
        $req->bindValue(':membresId', $this->getId(), PDO::PARAM_INT);
        $req->execute();
    }

    /**
     * Vérifier si un login est disponible pour enregistrement
     * @param string $login
     * @return bool
     */
    public static function verifierLoginDisponible(string $login): bool
    {
        $req = MaBDD::getInstance()->prepare('SELECT * FROM membres WHERE login = :login');
        $req->bindValue(':login', $login);
        $req->execute();

        // Par défaut le login est disponible
        $monRetour = true;

        // Si j'ai un résultat...
        if ($req->fetch()) {
            // Le retour est négatif
            $monRetour = false;
        }

        return $monRetour;
    }

    /**
     * Vérifier si un email est disponible pour enregistrement
     * @param string $email
     * @return bool
     */
    public static function verifierEmailDisponible(string $email): bool
    {
        $req = MaBDD::getInstance()->prepare('SELECT * FROM membres WHERE email = :email');
        $req->bindValue(':email', strtolower($email));
        $req->execute();

        // Par défaut l'email est disponible
        $monRetour = true;

        // Si j'ai un résultat...
        if ($req->fetch()) {
            // Le retour est négatif
            $monRetour = false;
        }

        return $monRetour;
    }

    /**
     * Vérifie que l'utilisateur à le droit d'afficher la page et affiche un EM au cas où
     * @param int $levelRequis Niveau de droit minimum requis
     * @param bool $dieIfNotGranted Arrêter le script si le niveau de droit n'est pas atteint
     * @return void|bool
     */
    public static function checkAccess(int $levelRequis, bool $dieIfNotGranted = true)
    {
        $monRetour = false;

        $monUser = new SessionObject();
        if ($monUser->verifierDroits($levelRequis) === false) {
            // Arrêter le script...
            if ($dieIfNotGranted) {
                header('HTTP/2 403 Forbidden');
                require _TPL_TOP_;
                echo '<h1 class="mb-3">Accès refusé</h1>';
                echo '<p>Désolé, vous n\'avez pas le droit d\'accèder à cette page.</p>';
                require _TPL_BOTTOM_;
                die();
            }
        } else {
            $monRetour = true;
        }

        return $monRetour;
    }

    /**
     * Toutes les images appartenant à un utilisateur
     * @return ArrayObject new_name image
     */
    public function getImages(): ArrayObject
    {
        // Toutes les images
        $req = MaBDD::getInstance()->prepare('SELECT new_name FROM possede, images WHERE id = images_id AND membres_id = :membresId ');
        $req->bindValue(':membresId', $this->getId(), PDO::PARAM_INT);

        // Exécution de la requête
        $req->execute();

        $monRetour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($req->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $monRetour->append($value->new_name);
        }

        return $monRetour;
    }
}
