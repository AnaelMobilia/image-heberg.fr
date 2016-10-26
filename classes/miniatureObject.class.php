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
 * Les miniatures
 *
 * @author anael
 */
class miniatureObject extends ressourceObject implements ressourceInterface {
    const tableName = 'thumbnails';

    private $id;

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
     * ID
     * @return type
     */
    public function getId() {
        return $this->id;
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
     * Charger une miniature
     * @param text $newName newName de l'image maître
     */
    public function charger($newName) {
        $imageMaitre = new imageObject($newName);

        // Je vais chercher les infos en BDD
        $req = maBDD::getInstance()->prepare("SELECT * FROM " . miniatureObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $imageMaitre->getId(), PDO::PARAM_INT);
        $req->execute();

        // J'éclate les informations
        $resultat = $req->fetch();
        $this->setId($resultat->id);
        $this->setPoids($resultat->t_size);
        $this->setHauteur($resultat->t_height);
        $this->setPoids($resultat->t_width);
        $this->setLastView($resultat->t_last_view);
        $this->setNbViewIPv4($resultat->t_nb_view_v4);
        $this->setNbViewIPv6($resultat->t_nb_view_v6);

        // Et je reprend le nom de l'image maître
        $this->setNomNouveau($imageMaitre->getNomNouveau());
    }

    /**
     * Sauver en BDD les infos d'une miniature
     */
    public function sauver() {
        // Je supprime les infos pouvant déjà être en BDD pour cette image
        $req = maBDD::getInstance()->prepare("DELETE FROM " . miniatureObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();


        // J'enregistre les infos en BDD
        $req = maBDD::getInstance()->prepare("INSERT INTO " . miniatureObject::tableName . " (id, t_size, t_height, t_width, t_last_view, t_nb_view_v4, t_nb_view_v6) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->bindValue(2, $this->getPoids(), PDO::PARAM_INT);
        $req->bindValue(3, $this->getHauteur(), PDO::PARAM_INT);
        $req->bindValue(4, $this->getLargeur(), PDO::PARAM_INT);
        $req->bindValue(5, $this->getLastView());
        $req->bindValue(6, $this->getNbViewIPv4(), PDO::PARAM_INT);
        $req->bindValue(7, $this->getNbViewIPv6(), PDO::PARAM_INT);

        $req->execute();
    }

    /**
     * Supprimer la miniature (HDD & BDD)
     * @return type
     */
    public function supprimer() {
        // Existe-t-il un propriétaire de l'image ?
        if ($this->verifierProprietaire()) {
            // @TODO
            echo "proprio " . $this->getNomNouveau();
            return;
        }

        // Je supprime l'image sur le HDD
        unlink(_PATH_MINIATURES_ . $this->getNomNouveau());
        // Je supprime l'image en BDD
        $req = maBDD::getInstance()->prepare("DELETE FROM " . miniatureObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_STR);
        $req->execute();
    }

    /**
     * Un utilisateur est-il propriétaire de la miniature ?
     * @return boolean
     */
    private function verifierProprietaire() {
        // Je vais chercher les infos en BDD
        $req = maBDD::getInstance()->prepare("SELECT * FROM " . utilisateurObject::tableNamePossede . " WHERE id = ?");
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