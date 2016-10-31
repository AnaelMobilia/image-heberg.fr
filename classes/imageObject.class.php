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
 * Les images
 *
 * @author anael
 */
class imageObject extends ressourceObject implements ressourceInterface {
    const tableName = 'images';

    /**
     * Constructeur
     * @param string $newName nom de l'image
     */
    function __construct($newName = FALSE) {
        // Si on me donne un ID d'image, je charge l'objet
        if ($newName) {
            if (!$this->charger($newName)) {
                // Envoi d'une exception si l'image n'existe pas
                throw new Exception('Image ' . $newName . ' inexistante');
            }
        }
    }

    /**
     * Path sur le HDD
     * @return string
     */
    public function getPathMd5() {
        $pathFinal = '';

        if ($this->getId() == 1 || $this->getId() == 2) {
            // Gestion des images spécificques 404 / ban
            $pathFinal = _PATH_IMAGES_ . $this->getNomNouveau();
        } else {
            // Cas par défaut
            $rep = substr($this->getMd5(), 0, 1) . '/';
            $pathFinal = _PATH_IMAGES_ . $rep . $this->getMd5();
        }
        return $pathFinal;
    }

    /**
     * Charger les infos d'une image
     * @param string $newName nom de l'image
     * @return boolean Image chargée ?
     */
    public function charger($newName) {
        // Retour
        $monRetour = FALSE;

        // Je vais chercher les infos en BDD
        $req = maBDD::getInstance()->prepare("SELECT * FROM " . imageObject::tableName . " WHERE new_name = ?");
        $req->bindParam(1, $newName, PDO::PARAM_STR);
        $req->execute();

        // J'éclate les informations
        $resultat = $req->fetch();
        if ($resultat !== FALSE) {
            $this->setId($resultat->id);
            $this->setIpEnvoi($resultat->ip_envoi);
            $this->setDateEnvoi($resultat->date_envoi);
            $this->setNomOriginal($resultat->old_name);
            // Permet l'effacement des fichiers non enregistrés en BDD
            $this->setNomNouveau($newName);
            $this->setPoids($resultat->size);
            $this->setHauteur($resultat->height);
            $this->setLargeur($resultat->width);
            $this->setLastView($resultat->last_view);
            $this->setNbViewIPv4($resultat->nb_view_v4);
            $this->setNbViewIPv6($resultat->nb_view_v6);
            $this->setMd5($resultat->md5);
            $this->setBloque($resultat->bloque);

            // Gestion du retour
            $monRetour = TRUE;
        }

        return $monRetour;
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
        $req = maBDD::getInstance()->prepare("INSERT INTO " . imageObject::tableName . " (id, ip_envoi, date_envoi, old_name, new_name, size, height, width, last_view, nb_view_v4, nb_view_v6, md5, bloque) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->bindValue(2, $this->getIpEnvoi(), PDO::PARAM_STR);
        $req->bindValue(3, $this->getDateEnvoi());
        $req->bindValue(4, $this->getNomOriginal(), PDO::PARAM_STR);
        $req->bindValue(5, $this->getNomNouveau(), PDO::PARAM_STR);
        $req->bindValue(6, $this->getPoids(), PDO::PARAM_INT);
        $req->bindValue(7, $this->getHauteur(), PDO::PARAM_INT);
        $req->bindValue(8, $this->getLargeur(), PDO::PARAM_INT);
        $req->bindValue(9, $this->getLastView());
        $req->bindValue(10, $this->getNbViewIPv4(), PDO::PARAM_INT);
        $req->bindValue(11, $this->getNbViewIPv6(), PDO::PARAM_INT);
        $req->bindValue(12, $this->getMd5(), PDO::PARAM_STR);
        $req->bindValue(13, $this->isBloque(), PDO::PARAM_INT);

        $req->execute();
    }

    /**
     * Supprimer l'image (HDD & BDD)
     */
    public function supprimer() {
        // Existe-t-il un propriétaire de l'image ?
        if ($this->verifierProprietaire()) {
            // TODO
            echo "proprio " . $this->getNomNouveau();
            return;
        }

        /**
         * MINIATURE ?
         */
        $maMiniature = new miniatureObject($this->getNomNouveau());
        // Existe-t-il une miniature de l'image ?
        if ($maMiniature) {
            $maMiniature->supprimer();
        }
        echo "<br />Suppression de " . $this->getNomNouveau();

        // Je supprime l'image sur le HDD
        unlink($this->getPathMd5());
        // Je supprime l'image en BDD
        $req = maBDD::getInstance()->prepare("DELETE FROM " . imageObject::tableName . " WHERE id = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
        $req->execute();
    }

    /**
     * Enregistre une nouvelle image dans le système
     * @param string $path path sur le filesystem
     */
    public function creer($path) {
        /**
         * Détermination du nom
         */
        $adresseIP = abs(crc32($_SERVER['REMOTE_ADDR']));
        $random = rand(10, 99);

        $debutTimestamp = substr($_SERVER['REQUEST_TIME'], 0, 5);
        $finTimestamp = substr($_SERVER['REQUEST_TIME'], 6);
        $new_name = $debutTimestamp . $addresseIP . $random . $finTimestamp . '.' . outils::getExtension($path);

        $this->setNomNouveau($new_name);

        /**
         * Déplacement du fichier
         */
        // copier le fichier au bon endroit
        //

        /**
         * GERER LE CHANGEMENT D'EXTENSION SI BESOIN JPEG / JPG
         */
    }

}