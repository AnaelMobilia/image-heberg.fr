<?php
/*
* Copyright 2008-2015 Anael Mobilia
*
* This file is part of NextINpact-Unofficial.
*
* NextINpact-Unofficial is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextINpact-Unofficial is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextINpact-Unofficial. If not, see <http://www.gnu.org/licenses/>
*/

/**
 * Les miniatures
 *
 * @author anael
 */
class miniatureObject {

    const tableName = 'thumbnails';

    private $size;
    private $height;
    private $width;
    private $lastView = "";
    private $id;
    private $nbViewV4 = 0;
    private $nbViewV6 = 0;
    private $newName;

    /**
     * Constructeur
     * @param type $newName newName de l'image maître
     */
    function __construct($newName = FALSE) {
        // Si on me donne un newName d'image, je charge l'objet
        if ($newName) {
            $this->charger($newName);
        }
    }

    /**
     * Poids
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
     * Date du dernier affichage
     * @return type
     */
    public function getLastView() {
        return $this->lastView;
    }

    /**
     * ID
     * @return type
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Nombre d'affichage en IPv4
     * @return type
     */
    public function getNbViewV4() {
        return $this->nbViewV4;
    }

    /**
     * Nombre d'affichage en IPv6
     * @return type
     */
    public function getNbViewV6() {
        return $this->nbViewV6;
    }

    /**
     * Nom du fichier sur le HDD
     * @return type
     */
    public function getNewName() {
        return $this->newName;
    }

    /**
     * Poids
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
     * Date du dernier affichage
     * @param type $lastView
     */
    public function setLastView($lastView) {
        $this->lastView = $lastView;
    }

    /**
     * ID
     * @param type $id
     */
    public function setId($id) {
        $this->id = $id;
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
     * Nom du fichier sur le HDD
     * @param type $newName
     */
    public function setNewName($newName) {
        $this->newName = $newName;
    }

    /**
     * Charger une miniature
     * @global type $maBDD
     * @param text $newName newName de l'image maître
     */
    public function charger($newName) {
        global $maBDD;

        $imageMaitre = new imageObject($newName);

        // Je vais chercher les infos en BDD
        $req = $maBDD->prepare("SELECT * FROM " . miniatureObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $imageMaitre->getId(), PDO::PARAM_INT);
        $req->execute();

        // J'éclate les informations
        $resultat = $req->fetch();
        $this->setId($resultat->id);
        $this->setSize($resultat->t_size);
        $this->setHeight($resultat->t_height);
        $this->setWidth($resultat->t_width);
        $this->setLastView($resultat->t_last_view);
        $this->setNbViewV4($resultat->t_nb_view_v4);
        $this->setNbViewV6($resultat->t_nb_view_v6);

        // Et je reprend le nom de l'image maître
        $this->setNewName($imageMaitre->getNewName());
    }

    /**
     * Sauver en BDD les infos d'une miniature
     * @global type $maBDD
     */
    public function sauver() {
        global $maBDD;

        // Je supprime les infos pouvant déjà être en BDD pour cette image
        $req = $maBDD->prepare("DELETE FROM " . miniatureObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();


        // J'enregistre les infos en BDD
        $req = $maBDD->prepare("INSERT INTO " . miniatureObject::tableName . " (id, t_size, t_height, t_width, t_last_view, t_nb_view_v4, t_nb_view_v6) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->bindValue(2, $this->getSize(), PDO::PARAM_INT);
        $req->bindValue(3, $this->getHeight(), PDO::PARAM_INT);
        $req->bindValue(4, $this->getWidth(), PDO::PARAM_INT);
        $req->bindValue(5, $this->getLastView());
        $req->bindValue(6, $this->getNbViewV4(), PDO::PARAM_INT);
        $req->bindValue(7, $this->getNbViewV6(), PDO::PARAM_INT);

        $req->execute();
    }

    /**
     * Supprimer la miniature (HDD & BDD) 
     * @global type $maBDD
     * @return type
     */
    public function supprimer() {
        global $maBDD;

        // Existe-t-il un propriétaire de l'image ?
        if ($this->verifierProprietaire()) {
            // @TODO
            echo "proprio " . $this->getNewName();
            return;
        }

        // Je supprime l'image sur le HDD
        unlink(_PATH_MINIATURES_ . $this->getNewName());
        // Je supprime l'image en BDD
        $req = $maBDD->prepare("DELETE FROM " . miniatureObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_STR);
        $req->execute();
    }

    /**
     * Un utilisateur est-il propriétaire de la miniature ?
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
}