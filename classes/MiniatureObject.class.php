<?php

/*
 * Copyright 2008-2023 Anael MOBILIA
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

use Exception;
use PDO;

/**
 * Les miniatures
 */
class MiniatureObject extends RessourceObject implements RessourceInterface
{
    private int $idImage;
    private bool $isPreview = false;

    /**
     * Constructeur
     * @param string $value Identifiant image-heberg
     * @param string $fromField Champ à utiliser en BDD
     * @throws Exception
     */
    public function __construct(string $value = '', string $fromField = RessourceObject::SEARCH_BY_NAME)
    {
        // Définition du type pour le RessourceObject
        $this->setType(RessourceObject::TYPE_MINIATURE);

        // Faut-il charger l'objet ?
        if ($value !== '' && !$this->charger($value, $fromField)) {
            // Envoi d'une exception si l'image n'existe pas
            throw new Exception('Miniature ' . $value . ' inexistante - ' . $fromField);
        }
    }

    public function charger(string $value, string $fromField = RessourceObject::SEARCH_BY_NAME): bool
    {
        $monRetour = false;

        // Je vais chercher les infos en BDD
        $req = MaBDD::getInstance()->prepare('SELECT * FROM thumbnails WHERE ' . $fromField . ' = :value');
        $req->bindValue(':value', $value);
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
            $this->setNomNouveau($resultat->new_name);
            $this->setIdImage($resultat->images_id);
            $this->setIsPreview($resultat->is_preview);

            // Reprise des informations de l'image maitresse
            $imageParente = new ImageObject();
            $imageParente->charger($this->getIdImage(), RessourceObject::SEARCH_BY_ID);
            $this->setBloquee($imageParente->isBloquee());
            $this->setSignalee($imageParente->isSignalee());
            $this->setApprouvee($imageParente->isApprouvee());
            $this->setNomOriginal($imageParente->getNomOriginal());
            $this->setIpEnvoi($imageParente->getIpEnvoi());
            $this->setIdProprietaire($imageParente->getIdProprietaire());
            $this->setSuspecte($imageParente->isSuspecte());

            // Notification du chargement réussi
            $monRetour = true;
        }
        return $monRetour;
    }

    public function sauver(): void
    {
        // J'enregistre les infos en BDD
        $req = MaBDD::getInstance()->prepare('UPDATE thumbnails SET images_id = :imagesId, is_preview = :isPreview, date_creation = :dateCreation, new_name = :newName, size = :size, height = :height, width = :width, last_view = :lastView, nb_view_v4 = :nbViewV4, nb_view_v6 = :nbViewV6, md5 = :md5 WHERE id = :id');

        $req->bindValue(':imagesId', $this->getIdImage(), PDO::PARAM_INT);
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
    }

    public function supprimer(): void
    {
        /**
         * Suppression de l'image en BDD
         */
        $req = MaBDD::getInstance()->prepare('DELETE FROM thumbnails WHERE id = :id');
        $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        if ($req->execute() && $this->getNbDoublons() === 0 && file_exists($this->getPathMd5())) {
            /**
             * Suppression du HDD
             */
            // Plus aucune image n'utilise le fichier => supprimer l'image sur le HDD
            unlink($this->getPathMd5());
        }
    }

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
        if ($this->getNbDoublons() === 0) {
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
            $req = MaBDD::getInstance()->prepare('INSERT INTO thumbnails (images_id, date_creation, new_name, size, height, width, md5) VALUES (:imagesId, NOW(), :newName, :size, :height, :width, :md5)');
            $req->bindValue(':imagesId', $this->getIdImage(), PDO::PARAM_INT);
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
     * @param bool $isPreview
     */
    public function setIsPreview(bool $isPreview): void
    {
        $this->isPreview = $isPreview;
    }
}
