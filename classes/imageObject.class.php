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
 * TODO - PHP5.5
 * http://fr2.php.net/manual/fr/function.imagescale.php
 * http://fr2.php.net/manual/fr/function.imageflip.php
 */

/**
 * Les images
 *
 * @author anael
 */
class imageObject {
    const tableName = 'images';

    private $id;
    private $ipEnvoi;
    private $dateEnvoi;
    private $oldName;
    private $newName;
    private $size;
    private $height;
    private $width;
    private $lastView;
    private $nbViewV4;
    private $nbViewV6;
    private $md5;

    /**
     * Constructeur
     * @param type $newName nom de l'image
     */
    function __construct($newName = FALSE) {
        // Si on me donne un ID d'image, je charge l'objet
        if ($newName) {
            $this->charger($newName);
        }
    }

    /**
     * ID de l'image
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * IP d'envoi
     * @return type
     */
    public function getIpEnvoi() {
        return $this->ipEnvoi;
    }

    /**
     * Date d'envoi formatée
     * @return type
     */
    public function getDateEnvoiFormate() {
        $phpdate = strtotime($this->dateEnvoi);
        return date("d/m/Y H:i:s", $phpdate);
    }

    /**
     * Date d'envoi
     * @return type
     */
    private function getDateEnvoi() {
        return $this->dateEnvoi;
    }

    /**
     * Nom original - avec htmlentities
     * @return type
     */
    public function getOldName() {
        return htmlentities($this->oldName);
    }

    /**
     * BDD - nom original (évite un htmlentities en boucle)
     * @return type
     */
    public function getOldNameBDD() {
        return $this->oldName;
    }

    /**
     * Nom dans le système
     * @return type
     */
    public function getNewName() {
        return $this->newName;
    }

    /**
     * Path sur le HDD
     * @return type
     */
    public function getPath() {
        return _PATH_IMAGES_ . $this->getNewName();
    }

    /**
     * Taille
     * @return type
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * Hauteur
     * @return type
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * Largeur
     * @return type
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * Date de dernier affichage formaté
     * @return type
     */
    public function getLastViewFormate() {
        $phpdate = strtotime($this->lastView);

        // Gestion du cas de non affichage
        if ($phpdate == 0) {
            return "-";
        }
        return date("d/m/Y", $phpdate);
    }

    /**
     * Date de dernier affichage
     * @return type
     */
    private function getLastView() {
        return $this->lastView;
    }

    /**
     * Nombre d'appels en IPv4
     * @return type
     */
    public function getNbViewV4() {
        return $this->nbViewV4;
    }

    /**
     * Nombre d'appels en IPv6
     * @return type
     */
    public function getNbViewV6() {
        return $this->nbViewV6;
    }

    /**
     * Nombre d'appels IPv4 & IPv6
     * @return type
     */
    public function getNbViewTotal() {
        return $this->getNbViewV4() + $this->getNbViewV6();
    }

    /**
     * MD5
     * @return type
     */
    public function getMd5() {
        return $this->md5;
    }

    /**
     * ID de l'image
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * IP d'envoi
     * @param type $ipEnvoi
     */
    public function setIpEnvoi($ipEnvoi) {
        $this->ipEnvoi = $ipEnvoi;
    }

    /**
     * Date d'envoi
     * @param type $dateEnvoi
     */
    public function setDateEnvoi($dateEnvoi) {
        $this->dateEnvoi = $dateEnvoi;
    }

    /**
     * Nom original
     * @param type $oldName
     */
    public function setOldName($oldName) {
        $this->oldName = $oldName;
    }

    /**
     * Nom dans le système
     * @param type $newName
     */
    public function setNewName($newName) {
        $this->newName = $newName;
    }

    /**
     * Taille
     * @param type $size
     */
    public function setSize($size) {
        $this->size = $size;
    }

    /**
     * Hauteur
     * @param type $height
     */
    public function setHeight($height) {
        $this->height = $height;
    }

    /**
     * Largeur
     * @param type $width
     */
    public function setWidth($width) {
        $this->width = $width;
    }

    /**
     * Date de dernier affichage
     * @param type $lastView
     */
    public function setLastView($lastView) {
        $this->lastView = $lastView;
    }

    /**
     * Incrémente le nombre d'affichage IPv4 & met à jour en BDD
     */
    public function setNbViewV4PlusUn() {
        $this->nbViewV4 = $this->getNbViewV4() + 1;
        $this->setLastView(date("Y-m-d"));
        $this->sauver();
    }

    /**
     * Nombre d'appels en IPv4
     * @param type $nbViewV4
     */
    public function setNbViewV4($nbViewV4) {
        $this->nbViewV4 = $nbViewV4;
    }

    /**
     * Incrémente le nombre d'affichage IPv6 & met à jour en BDD
     */
    public function setNbViewV6PlusUn() {
        $this->nbViewV6 = $this->getNbViewV6() + 1;
        $this->setLastView(date("Y-m-d"));
        $this->sauver();
    }

    /**
     * Nombre d'appels en IPv6
     * @param type $nbViewV6
     */
    public function setNbViewV6($nbViewV6) {
        $this->nbViewV6 = $nbViewV6;
    }

    /**
     * MD5
     * @param type $md5
     */
    public function setMd5($md5) {
        $this->md5 = $md5;
    }

    /**
     * Charger les infos d'une image
     * @global type $maBDD
     * @param text $newName nom de l'image
     */
    public function charger($newName) {
        global $maBDD;

        // Je vais chercher les infos en BDD
        $req = $maBDD->prepare("SELECT * FROM " . imageObject::tableName . " WHERE new_name = ?");
        $req->bindParam(1, $newName, PDO::PARAM_STR);
        $req->execute();

        // J'éclate les informations
        $resultat = $req->fetch();
        $this->setId($resultat->id);
        $this->setIpEnvoi($resultat->ip_envoi);
        $this->setDateEnvoi($resultat->date_envoi);
        $this->setOldName($resultat->old_name);
        // Permet l'effacement des fichiers non enregistrés en BDD
        $this->setNewName($newName);
        $this->setSize($resultat->size);
        $this->setHeight($resultat->height);
        $this->setWidth($resultat->width);
        $this->setLastView($resultat->last_view);
        $this->setNbViewV4($resultat->nb_view_v4);
        $this->setNbViewV6($resultat->nb_view_v6);
        $this->setMd5($resultat->md5);
    }

    /**
     * Sauver en BDD les infos d'une image
     * @global type $maBDD
     */
    public function sauver() {
        global $maBDD;

        // Je supprime les infos pouvant déjà être en BDD pour cette image
        $req = $maBDD->prepare("DELETE FROM " . imageObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();


        // J'enregistre les infos en BDD
        $req = $maBDD->prepare("INSERT INTO " . imageObject::tableName . " (id, ip_envoi, date_envoi, old_name, new_name, size, height, width, last_view, nb_view_v4, nb_view_v6, md5) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->bindValue(2, $this->getIpEnvoi(), PDO::PARAM_STR);
        $req->bindValue(3, $this->getDateEnvoi());
        $req->bindValue(4, $this->getOldNameBDD(), PDO::PARAM_STR);
        $req->bindValue(5, $this->getNewName(), PDO::PARAM_STR);
        $req->bindValue(6, $this->getSize(), PDO::PARAM_INT);
        $req->bindValue(7, $this->getHeight(), PDO::PARAM_INT);
        $req->bindValue(8, $this->getWidth(), PDO::PARAM_INT);
        $req->bindValue(9, $this->getLastView());
        $req->bindValue(10, $this->getNbViewV4(), PDO::PARAM_INT);
        $req->bindValue(11, $this->getNbViewV6(), PDO::PARAM_INT);
        $req->bindValue(12, $this->getMd5(), PDO::PARAM_STR);

        $req->execute();
    }

    /**
     * Supprimer l'image (HDD & BDD)
     * @global type $maBDD
     * @return type
     */
    public function supprimer() {
        global $maBDD;

        // Existe-t-il un propriétaire de l'image ?
        if ($this->verifierProprietaire()) {
            // TODO
            echo "proprio " . $this->getNewName();
            return;
        }

        // Existe-t-il une miniature de l'image ?
        if ($this->verifierMiniature()) {
            $maMiniature = new miniatureObject($this->getNewName());
            $maMiniature->supprimer();
        }
        echo "<br />Suppression de " . $this->getNewName();

        // Je supprime l'image sur le HDD
        unlink(_PATH_IMAGES_ . $this->getNewName());
        // Je supprime l'image en BDD
        $req = $maBDD->prepare("DELETE FROM " . imageObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();
    }

    /**
     * Une miniature a-t-elle été faite ?
     * @global type $maBDD
     * @return boolean
     */
    private function verifierMiniature() {
        global $maBDD;

        // Je vais chercher les infos en BDD
        $req = $maBDD->prepare("SELECT * FROM " . miniatureObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();

        // Retour négatif par défaut
        $retour = FALSE;

        // Si j'ai un résultat...
        if ($req->fetch()) {
            // Le retour est positif
            $retour = TRUE;
        }

        return $retour;
    }

    /**
     * Un utilisateur est-il propriétaire de l'image ?
     * @global type $maBDD
     * @return boolean
     */
    private function verifierProprietaire() {
        global $maBDD;

        // Je vais chercher les infos en BDD
        $req = $maBDD->prepare("SELECT * FROM " . utilisateurObject::tableNamePossede . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();

        // Retour négatif par défaut
        $retour = FALSE;

        // Je récupère les potentielles valeurs
        $values = $req->fetch();

        // Si l'image à un propriétaire...
        if ($values !== FALSE) {
            // Le propriétaire est-il connecté ?
            $uneSession = new sessionObject();

            // Est-ce le propriétaire de l'image ?
            if ($values->pk_membres === $uneSession->getId()) {
                // Si oui... on confirme !
                $retour = TRUE;
            }
        }

        return $retour;
    }

    /**
     * Met à jour les caractéristiques (dimensions & poids) d'une image
     */
    public function refreshInfos() {
        // Dimensions
        $dim = getimagesize($this->getPath());
        $this->setWidth($dim[0]);
        $this->setHeight($dim[1]);

        // Poids de l'image
        $this->setSize(filesize($this->getPath()));
    }

    /**
     * Type EXIF de l'image
     * @return type
     */
    public function getType() {
        return exif_imagetype($this->getPath());
    }

    /**
     * La ressource PHP image
     * @return type
     */
    public function getImage() {
        // Je charge l'image en mémoire en fonction de son type
        if ($this->getType() == IMAGETYPE_GIF) {
            return imagecreatefromgif($this->getPath());
        } else if ($this->getType() == IMAGETYPE_JPEG) {
            return imagecreatefromjpeg($this->getPath());
        } else if ($this->getType() == IMAGETYPE_PNG) {
            return imagecreatefrompng($this->getPath());
        }
    }

}