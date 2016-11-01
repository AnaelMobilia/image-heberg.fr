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
        // Si on me donne un newName d'image, je charge l'objet
        if ($newName) {
            if (!$this->charger($newName)) {
                // Envoi d'une exception si l'image n'existe pas
                throw new Exception('Miniature ' . $newName . ' inexistante');
            }
        }
    }

    /**
     * Path sur le HDD
     * @return
     */
    public function getPathMd5() {
        $rep = substr($this->getMd5(), 0, 1) . '/';
        return _PATH_MINIATURES_ . $rep . $this->getMd5();
    }

    /**
     * Charger une miniature
     * @param string $newName newName de l'image maître
     * @return boolean Chargement réussi ?
     */
    public function charger($newName) {
        $monRetour = FALSE;
        $imageMaitre = new imageObject($newName);

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
            $this->setDateEnvoi($imageMaitre->getDateEnvoi());
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
        // Je supprime les infos pouvant déjà être en BDD pour cette image
        $req = maBDD::getInstance()->prepare("DELETE FROM thumbnails WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();

        // J'enregistre les infos en BDD
        $req = maBDD::getInstance()->prepare("INSERT INTO thumbnails (id, size, height, width, last_view, nb_view_v4, nb_view_v6, md5) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->bindValue(2, $this->getPoids(), PDO::PARAM_INT);
        $req->bindValue(3, $this->getHauteur(), PDO::PARAM_INT);
        $req->bindValue(4, $this->getLargeur(), PDO::PARAM_INT);
        $req->bindValue(5, $this->getLastView());
        $req->bindValue(6, $this->getNbViewIPv4(), PDO::PARAM_INT);
        $req->bindValue(7, $this->getNbViewIPv6(), PDO::PARAM_INT);
        $req->bindValue(8, $this->getMd5(), PDO::PARAM_STR);

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
        unlink($this->getPathMd5());
        // Je supprime l'image en BDD
        $req = maBDD::getInstance()->prepare("DELETE FROM thumbnails WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_STR);
        $req->execute();
    }

    public function creer($path) {

    }

}