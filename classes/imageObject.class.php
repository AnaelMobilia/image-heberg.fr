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
class imageObject extends ressourceObject implements ressourceInterface {
    const tableName = 'images';

    private $oldName;

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
     * Path sur le HDD
     * @return type
     */
    public function getPath() {
        return _PATH_IMAGES_ . $this->getNomNouveau();
    }

    /**
     * Nom original
     * @param type $oldName
     */
    public function setOldName($oldName) {
        $this->oldName = $oldName;
    }

    /**
     * Incrémente le nombre d'affichage IPv4 & met à jour en BDD
     */
    public function setNbViewV4PlusUn() {
        $this->nbViewV4 = $this->getNbViewIPv4() + 1;
        $this->setLastView(date("Y-m-d"));
        $this->sauver();
    }

    /**
     * Incrémente le nombre d'affichage IPv6 & met à jour en BDD
     */
    public function setNbViewV6PlusUn() {
        $this->nbViewV6 = $this->getNbViewIPv6() + 1;
        $this->setLastView(date("Y-m-d"));
        $this->sauver();
    }

    /**
     * Charger les infos d'une image
     * @param text $newName nom de l'image
     */
    public function charger($newName) {
        // Je vais chercher les infos en BDD
        $req = maBDD::getInstance()->prepare("SELECT * FROM " . imageObject::tableName . " WHERE new_name = ?");
        $req->bindParam(1, $newName, PDO::PARAM_STR);
        $req->execute();

        // J'éclate les informations
        $resultat = $req->fetch();
        $this->setId($resultat->id);
        $this->setIpEnvoi($resultat->ip_envoi);
        $this->setDateEnvoi($resultat->date_envoi);
        $this->setOldName($resultat->old_name);
        // Permet l'effacement des fichiers non enregistrés en BDD
        $this->setNomNouveau($newName);
        $this->setPoids($resultat->size);
        $this->setHauteur($resultat->height);
        $this->setLargeur($resultat->width);
        $this->setLastView($resultat->last_view);
        $this->setNbViewIPv4($resultat->nb_view_v4);
        $this->setNbViewIPv6($resultat->nb_view_v6);
        $this->setMd5($resultat->md5);
    }

    /**
     * Sauver en BDD les infos d'une image
     */
    public function sauver() {
        // Je supprime les infos pouvant déjà être en BDD pour cette image
        $req = maBDD::getInstance()->prepare("DELETE FROM " . imageObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();


        // J'enregistre les infos en BDD
        $req = maBDD::getInstance()->prepare("INSERT INTO " . imageObject::tableName . " (id, ip_envoi, date_envoi, old_name, new_name, size, height, width, last_view, nb_view_v4, nb_view_v6, md5) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->bindValue(2, $this->getIpEnvoi(), PDO::PARAM_STR);
        $req->bindValue(3, $this->getDateEnvoi());
        $req->bindValue(4, $this->getOldNameBDD(), PDO::PARAM_STR);
        $req->bindValue(5, $this->getNomNouveau(), PDO::PARAM_STR);
        $req->bindValue(6, $this->getPoids(), PDO::PARAM_INT);
        $req->bindValue(7, $this->getHauteur(), PDO::PARAM_INT);
        $req->bindValue(8, $this->getLargeur(), PDO::PARAM_INT);
        $req->bindValue(9, $this->getLastView());
        $req->bindValue(10, $this->getNbViewIPv4(), PDO::PARAM_INT);
        $req->bindValue(11, $this->getNbViewIPv6(), PDO::PARAM_INT);
        $req->bindValue(12, $this->getMd5(), PDO::PARAM_STR);

        $req->execute();
    }

    /**
     * Supprimer l'image (HDD & BDD)
     * @return type
     */
    public function supprimer() {
        // Existe-t-il un propriétaire de l'image ?
        if ($this->verifierProprietaire()) {
            // TODO
            echo "proprio " . $this->getNomNouveau();
            return;
        }

        // Existe-t-il une miniature de l'image ?
        if ($this->verifierMiniature()) {
            $maMiniature = new miniatureObject($this->getNomNouveau());
            $maMiniature->supprimer();
        }
        echo "<br />Suppression de " . $this->getNomNouveau();

        // Je supprime l'image sur le HDD
        unlink($this->getPath());
        // Je supprime l'image en BDD
        $req = maBDD::getInstance()->prepare("DELETE FROM " . imageObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();
    }

    /**
     * Une miniature a-t-elle été faite ?
     * @return boolean
     */
    private function verifierMiniature() {
        // Je vais chercher les infos en BDD
        $req = maBDD::getInstance()->prepare("SELECT * FROM " . miniatureObject::tableName . " WHERE id = ?");
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
     * Met à jour les caractéristiques (dimensions & poids) d'une image
     */
    public function refreshInfos() {
        // Dimensions
        $dim = getimagesize($this->getPath());
        $this->setLargeur($dim[0]);
        $this->setHauteur($dim[1]);

        // Poids de l'image
        $this->setPoids(filesize($this->getPath()));
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