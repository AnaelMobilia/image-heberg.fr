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
 */
class miniatureObject extends ressourceObject implements ressourceInterface {

    /**
     * Constructeur
     * @param string $newName newName de l'image maître
     */
    function __construct($newName = FALSE) {
        // Définition du type pour le ressourceObject
        $this->setType(ressourceObject::typeMiniature);

        // Si on me donne un newName d'image, je charge l'objet
        if ($newName) {
            if (!$this->charger($newName)) {
                // Envoi d'une exception si l'image n'existe pas
                throw new Exception('Miniature ' . $newName . ' inexistante');
            }
        }
    }

    /**
     * Charger une miniature
     * @param string $newName newName de l'image maître
     * @return boolean Chargement réussi ?
     */
    public function charger($newName) {
        $monRetour = FALSE;
        $imageMaitre = new imageObject();
        $imageMaitre->charger($newName);

        // Je vais chercher les infos en BDD
        $req = maBDD::getInstance()->prepare("SELECT * FROM thumbnails WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $imageMaitre->getId(), PDO::PARAM_INT);
        $req->execute();

        // J'éclate les informations
        $resultat = $req->fetch();
        if ($resultat !== FALSE) {
            $this->setPoids($resultat->size);
            $this->setHauteur($resultat->height);
            $this->setLargeur($resultat->width);
            $this->setLastView($resultat->last_view);
            $this->setNbViewIPv4($resultat->nb_view_v4);
            $this->setNbViewIPv6($resultat->nb_view_v6);
            $this->setMd5($resultat->md5);

            // Reprise des informations de l'image maitresse
            $this->setId($imageMaitre->getId());
            $this->setNomNouveau($imageMaitre->getNomNouveau());
            $this->setBloque($imageMaitre->isBloque());
            $this->setNomOriginal($imageMaitre->getNomOriginal());
            $this->setDateEnvoi($imageMaitre->getDateEnvoiBrute());
            $this->setIpEnvoi($imageMaitre->getIpEnvoi());

            // Notification du chargement réussi
            $monRetour = TRUE;
        }
        return $monRetour;
    }

    /**
     * Sauver en BDD les infos d'une miniature
     */
    public function sauver() {
        // J'enregistre les infos en BDD
        $req = maBDD::getInstance()->prepare("UPDATE thumbnails SET size = ?, height = ?, width = ?, last_view = ?, nb_view_v4 = ?, nb_view_v6 = ?, md5 = ? WHERE id = ?");

        $req->bindValue(1, $this->getPoids(), PDO::PARAM_INT);
        $req->bindValue(2, $this->getHauteur(), PDO::PARAM_INT);
        $req->bindValue(3, $this->getLargeur(), PDO::PARAM_INT);
        $req->bindValue(4, $this->getLastView());
        $req->bindValue(5, $this->getNbViewIPv4(), PDO::PARAM_INT);
        $req->bindValue(6, $this->getNbViewIPv6(), PDO::PARAM_INT);
        $req->bindValue(7, $this->getMd5(), PDO::PARAM_STR);
        $req->bindValue(8, $this->getId(), PDO::PARAM_INT);

        $req->execute();
    }

    /**
     * Supprimer la miniature (HDD & BDD)
     * @return boolean Supprimée ?
     */
    public function supprimer() {
        $monRetour = TRUE;
        /**
         * Suppression de l'image en BDD
         */
        $req = maBDD::getInstance()->prepare("DELETE FROM thumbnails WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $monRetour = $req->execute();

        /**
         * Suppression du HDD
         */
        if ($monRetour) {
            // Existe-t-il d'autres occurences de cette image ?
            $req = maBDD::getInstance()->prepare("SELECT COUNT(*) AS nb FROM miniatures WHERE md5 = ?");
            /* @var $req PDOStatement */
            $req->bindValue(1, $this->getMd5(), PDO::PARAM_STR);
            $req->execute();
            $values = $req->fetch();

            // Il n'y a plus d'image identique...
            if ($values !== FALSE && $values->nb === 0) {
                // Je supprime l'image sur le HDD
                $monRetour = unlink($this->getPathMd5());
            } elseif ($values === FALSE) {
                $monRetour = FALSE;
            }
        }

        return $monRetour;
    }

    public function creer() {

    }

}