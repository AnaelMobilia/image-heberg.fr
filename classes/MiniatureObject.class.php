<?php

/*
 * Copyright 2008-2021 Anael MOBILIA
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

use PDO;

/**
 * Les miniatures
 */
class MiniatureObject extends RessourceObject implements RessourceInterface
{
    private int $idImage;
    private bool $isPreview;

    /**
     * Constructeur
     * @param string $newName newName de l'image maître
     */
    public function __construct($newName = false)
    {
        // Définition du type pour le RessourceObject
        $this->setType(RessourceObject::TYPE_MINIATURE);

        // Si on me donne un newName d'image, je charge l'objet
        if ($newName) {
            if (!$this->charger($newName)) {
                // Envoi d'une exception si l'image n'existe pas
                throw new Exception('Miniature ' . $newName . ' inexistante');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function charger(string $nom): bool
    {
        $monRetour = false;

        // Je vais chercher les infos en BDD
        $req = MaBDD::getInstance()->prepare("SELECT * FROM thumbnails WHERE new_name = :newName");
        $req->bindValue(':newName', $nom);
        $req->execute();

        // J'éclate les informations
        $resultat = $req->fetch();
        if ($resultat !== false) {
            $this->setPoids($resultat->size);
            $this->setHauteur($resultat->height);
            $this->setLargeur($resultat->width);
            $this->setLastView($resultat->last_view);
            $this->setNbViewIPv4($resultat->nb_view_v4);
            $this->setNbViewIPv6($resultat->nb_view_v6);
            $this->setMd5($resultat->md5);
            $this->setId($resultat->id);
            $this->setDateEnvoi($resultat->date_creation);
            $this->setNomNouveau($nom);
            $this->setIdImage($resultat->id_image);
            $this->setIsPreview($resultat->is_preview);

            // Reprise des informations de l'image maitresse
            $imageMaitre = new ImageObject();
            $imageMaitre->charger($nom);
            $this->setBloquee($imageMaitre->isBloquee());
            $this->setSignalee($imageMaitre->isSignalee());
            $this->setNomOriginal($imageMaitre->getNomOriginal());
            $this->setIpEnvoi($imageMaitre->getIpEnvoi());

            // Notification du chargement réussi
            $monRetour = true;
        }
        return $monRetour;
    }

    /**
     * {@inheritdoc}
     */
    public function sauver(): bool
    {
        // J'enregistre les infos en BDD
        $req = MaBDD::getInstance()->prepare("UPDATE thumbnails SET id_image = :idImage, is_preview = :isPreview, date_creation = :dateCreation, new_name = :newName, size = :size, height = :height, width = :width, last_view = :lastView, nb_view_v4 = :nbViewV4, nb_view_v6 = :nbViewV6, md5 = :md5 WHERE id = :id");

        $req->bindValue(':idImage', $this->getIdImage(), PDO::PARAM_INT);
        $req->bindValue(':isPreview', $this->getIsPreview(), PDO::PARAM_INT);
        $req->bindValue(':dateCreation', $this->getDateEnvoiBrute());
        $req->bindValue(':newName', $this->getNomNouveau(), PDO::PARAM_STMT);
        $req->bindValue(':size', $this->getPoids(), PDO::PARAM_INT);
        $req->bindValue(':height', $this->getHauteur(), PDO::PARAM_INT);
        $req->bindValue(':width', $this->getLargeur(), PDO::PARAM_INT);
        $req->bindValue(':lastView', $this->getLastView());
        $req->bindValue(':nbViewV4', $this->getNbViewIPv4(), PDO::PARAM_INT);
        $req->bindValue(':nbViewV6', $this->getNbViewIPv6(), PDO::PARAM_INT);
        $req->bindValue(':md5', $this->getMd5());
        $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);

        $req->execute();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supprimer(): bool
    {
        /**
         * Suppression de l'image en BDD
         */
        $req = MaBDD::getInstance()->prepare("DELETE FROM thumbnails WHERE id = :id");
        $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        $monRetour = $req->execute();

        /**
         * Suppression du HDD
         */
        if ($monRetour) {
            // Plus aucune miniature n'utilise le fichier (BDD déjà mise à jour !)
            if ($this->getNbDoublons() == 0) {
                // Je supprime l'image sur le HDD
                $monRetour = unlink($this->getPathMd5());
            }
        }

        return $monRetour;
    }

    /**
     * {@inheritdoc}
     */
    public function creer(): bool
    {
        // Retour
        $monRetour = true;

        /**
         * Détermination du nom &&
         * Vérification de sa disponibilité
         */
        $tmpMiniature = new MiniatureObject();
        $nb = 0;
        do {
            // Récupération d'un nouveau nom
            $new_name = $this->genererNom($nb);
            // Incrémentation compteur entropie sur le nom
            $nb++;
        } while ($tmpMiniature->charger($new_name) !== false);
        // Effacement de l'objet temporaire
        unset($tmpMiniature);

        // On enregistre le nom
        $this->setNomNouveau($new_name);

        /**
         * Déplacement du fichier
         */
        // Vérification de la non existence du fichier
        if ($this->getNbDoublons() == 0) {
            $monRetour = rename($this->getPathTemp(), $this->getPathMd5());
        }

        // Ssi copie du fichier réussie
        if ($monRetour) {
            /**
             * Informations sur l'image
             */
            // Dimensions
            $imageInfo = getimagesize($this->getPathMd5());
            $this->setLargeur($imageInfo[0]);
            $this->setHauteur($imageInfo[1]);
            // Poids
            $this->setPoids(filesize($this->getPathMd5()));

            /**
             * Création en BDD
             */
            $req = MaBDD::getInstance()->prepare("INSERT INTO thumbnails (id_image, date_creation, new_name, size, height, width, md5) VALUES (:idImage, NOW(), :newName, :size, :height, :width, :md5)");
            $req->bindValue(':idImage', $this->getIdImage(), PDO::PARAM_INT);
            // Date : NOW()
            $req->bindValue(':newName', $this->getNomNouveau());
            $req->bindValue(':size', $this->getPoids(), PDO::PARAM_INT);
            $req->bindValue(':height', $this->getHauteur(), PDO::PARAM_INT);
            $req->bindValue(':width', $this->getLargeur(), PDO::PARAM_INT);
            $req->bindValue(':md5', $this->getMd5());

            if (!$req->execute()) {
                // Gestion de l'erreur d'insertion en BDD
                $monRetour = false;
            } else {
                /**
                 * Récupération de l'ID de l'image
                 */
                $idEnregistrement = MaBDD::getInstance()->lastInsertId();
                $this->setId($idEnregistrement);
            }
        }

        return $monRetour;
    }
    /**
     * GETTERS & SETTERS
     */

    /**
     * ID image parente
     * @return int
     */
    public function getIdImage(): int
    {
        return $this->idImage;
    }

    /**
     * ID image parente
     * @param int $idImage
     */
    public function setIdImage(int $idImage): void
    {
        $this->idImage = $idImage;
    }

    /**
     * @return bool
     */
    public function getIsPreview(): bool
    {
        return $this->isPreview;
    }

    /**
     * @param mixed $isPreview
     */
    public function setIsPreview($isPreview): void
    {
        $this->isPreview = $isPreview;
    }
}
