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

use ArrayObject;
use PDO;

/**
 * Les images
 */
class ImageObject extends RessourceObject implements RessourceInterface
{
    /**
     * Constructeur
     * @param string $value Identifiant image-heberg
     * @param string $fromField Champ à utiliser en BDD
     * @throws ImageHebergException
     */
    public function __construct(string $value = '', string $fromField = RessourceObject::SEARCH_BY_NAME)
    {
        // Définition du type pour le RessourceObject
        $this->setType(RessourceObject::TYPE_IMAGE);

        // Faut-il charger l'objet ?
        if ($value !== '' && !$this->charger($value, $fromField)) {
            // Envoi d'une exception si l'image n'existe pas
            throw new ImageHebergException('Image ' . $value . ' inexistante (' . $fromField . ')');
        }
    }

    public function charger(string $value, string $fromField = RessourceObject::SEARCH_BY_NAME): bool
    {
        // Retour
        $monRetour = false;

        // Je vais chercher les infos en BDD
        $req = MaBDD::getInstance()->prepare('SELECT *, (SELECT COUNT(*) FROM images im2 WHERE im2.isBloquee = 1 AND im2.abuse_network = images.abuse_network) AS reputation FROM images LEFT JOIN possede on images.id = possede.images_id WHERE ' . $fromField . ' = :value');
        $req->bindValue(':value', $value);
        $req->execute();

        // J'éclate les informations
        $resultat = $req->fetch();
        if ($resultat !== false) {
            $this->setId($resultat->id);
            $this->setIpEnvoi($resultat->remote_addr);
            $this->setDateEnvoi($resultat->date_action);
            $this->setNomOriginal($resultat->old_name);
            $this->setNomNouveau($resultat->new_name);
            $this->setPoids($resultat->size);
            $this->setHauteur($resultat->height);
            $this->setLargeur($resultat->width);
            $this->setLastView($resultat->last_view);
            $this->setNbViewIPv4($resultat->nb_view_v4);
            $this->setNbViewIPv6($resultat->nb_view_v6);
            $this->setMd5($resultat->md5);
            $this->setBloquee($resultat->isBloquee);
            $this->setSignalee($resultat->isSignalee);
            $this->setApprouvee($resultat->isApprouvee);
            $this->setIdProprietaire($resultat->membres_id);
            $this->setSuspecte(($resultat->reputation > 0));

            // Gestion du retour
            $monRetour = true;
        }

        return $monRetour;
    }

    public function sauver(): void
    {
        // J'enregistre les infos en BDD
        $req = MaBDD::getInstance()->prepare('UPDATE images SET remote_addr = :ipEnvoi, date_action = :dateEnvoi, old_name = :oldName, new_name = :newName, size = :size, height = :height, width = :width, last_view = :lastView, nb_view_v4 = :nbViewV4, nb_view_v6 = :nbViewV6, md5 = :md5, isBloquee = :isBloquee, isSignalee = :isSignalee, isApprouvee = :isApprouvee WHERE id = :id');
        $req->bindValue(':ipEnvoi', $this->getIpEnvoi());
        $req->bindValue(':dateEnvoi', $this->getDateEnvoiBrute());
        $req->bindValue(':oldName', $this->getNomOriginal());
        $req->bindValue(':newName', $this->getNomNouveau());
        $req->bindValue(':size', $this->getPoids(), PDO::PARAM_INT);
        $req->bindValue(':height', $this->getHauteur(), PDO::PARAM_INT);
        $req->bindValue(':width', $this->getLargeur(), PDO::PARAM_INT);
        $req->bindValue(':lastView', $this->getLastView());
        $req->bindValue(':nbViewV4', $this->getNbViewIPv4(), PDO::PARAM_INT);
        $req->bindValue(':nbViewV6', $this->getNbViewIPv6(), PDO::PARAM_INT);
        $req->bindValue(':md5', $this->getMd5());
        $req->bindValue(':isBloquee', $this->isBloquee(), PDO::PARAM_INT);
        $req->bindValue(':isSignalee', $this->isSignalee(), PDO::PARAM_INT);
        $req->bindValue(':isApprouvee', $this->isApprouvee(), PDO::PARAM_INT);
        $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);

        $req->execute();
    }

    /**
     * Récupérer les ID des images des miniatures associées
     * @param bool $onlyPreview Uniquement les miniatures d'aperçu dans l'espace membre ?
     * @return ArrayObject new_name en BDD des miniatures
     */
    public function getMiniatures(bool $onlyPreview = false): ArrayObject
    {
        $monRetour = new ArrayObject();

        // Chargement des miniatures
        $query = 'SELECT new_name FROM thumbnails WHERE images_id = :imagesId';
        if ($onlyPreview) {
            $query .= ' AND is_preview = 1';
        }

        $req = MaBDD::getInstance()->prepare($query);
        $req->bindValue(':imagesId', $this->getId(), PDO::PARAM_INT);
        $req->execute();

        // Je passe toutes les lignes de résultat
        foreach ($req->fetchAll() as $value) {
            // Nom du fichier
            $monRetour->append($value->new_name);
        }

        return $monRetour;
    }

    public function supprimer(): void
    {
        /**
         * Suppression de la ou les miniatures
         */
        // Je passe toutes les lignes de résultat
        foreach ($this->getMiniatures() as $new_name) {
            // Chargement de la miniature
            $maMiniature = new MiniatureObject($new_name);
            // Suppression
            $maMiniature->supprimer();
        }

        /**
         * Suppression de l'affectation
         */
        $req = MaBDD::getInstance()->prepare('DELETE FROM possede WHERE images_id = :imagesId');
        $req->bindValue(':imagesId', $this->getId(), PDO::PARAM_INT);
        if ($req->execute()) {
            /**
             * Suppression de l'image en BDD
             */
            $req = MaBDD::getInstance()->prepare('DELETE FROM images WHERE id = :id');
            $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);
            if ($req->execute() && $this->getNbDoublons() === 0) {
                /**
                 * Suppression du HDD
                 */
                // Plus aucune image n'utilise le fichier => supprimer l'image sur le HDD
                unlink($this->getPathMd5());
            }
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
        $tmpImage = new ImageObject();
        $nb = 0;
        do {
            // Récupération d'un nouveau nom
            $new_name = $this->genererNom($nb);
            // Incrémentation compteur entropie sur le nom
            $nb++;
        } while ($tmpImage->charger($new_name) !== false);
        // Effacement de l'objet temporaire
        unset($tmpImage);

        // On enregistre le nom
        $this->setNomNouveau($new_name);

        /**
         * Déplacement du fichier
         */
        // Vérification de la non existence du fichier
        if ($this->getNbDoublons() === 0) {
            // PHP ne gère pas les images WebP animée -> ne pas faire de traitements
            if (!HelperImage::isAnimatedWebp($this->getPathTemp())) {
                // Image inconnue : optimisation de sa taille
                $monRetour = HelperImage::setImage(HelperImage::getImage($this->getPathTemp()), HelperImage::getType($this->getPathTemp()), $this->getPathTemp());
            }
            // Copie du fichier vers l'emplacement de stockage
            // Ne peut pas être fait avant car le MD5 n'est pas encore connu
            copy($this->getPathTemp(), $this->getPathMd5());
        } else {
            // Ce MD5 est-il déjà bloqué pour une autre image ?
            $req = MaBDD::getInstance()->prepare('SELECT MAX(isBloquee) AS isBloquee FROM images WHERE md5 = :md5');
            $req->bindValue(':md5', $this->getMd5());
            $req->execute();
            $values = $req->fetch();
            if ($values !== false) {
                $this->setBloquee((bool)$values->isBloquee);
            }
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
            // Nom originel (non récupérable sur le fichier)
            $this->setNomOriginal($this->getNomTemp());
            // @ IP d'envoi
            $this->setIpEnvoi($_SERVER['REMOTE_ADDR']);

            /**
             * Création en BDD
             */
            $req = MaBDD::getInstance()->prepare('INSERT INTO images (remote_addr, date_action, old_name, new_name, size, height, width, md5, isBloquee) VALUES (:ipEnvoi, NOW(), :oldName, :newName, :size, :height, :width, :md5, :isBloquee)');
            $req->bindValue(':ipEnvoi', $this->getIpEnvoi());
            // Date : NOW()
            $req->bindValue(':oldName', $this->getNomOriginal());
            $req->bindValue(':newName', $this->getNomNouveau());
            $req->bindValue(':size', $this->getPoids(), PDO::PARAM_INT);
            $req->bindValue(':height', $this->getHauteur(), PDO::PARAM_INT);
            $req->bindValue(':width', $this->getLargeur(), PDO::PARAM_INT);
            $req->bindValue(':md5', $this->getMd5());
            $req->bindValue(':isBloquee', $this->isBloquee());

            if (!$req->execute()) {
                // Gestion de l'erreur d'insertion en BDD
                $monRetour = false;
            } else {
                /**
                 * Récupération de l'ID de l'image
                 */
                $idEnregistrement = MaBDD::getInstance()->lastInsertId();
                $this->setId($idEnregistrement);
                /**
                 * Définir les informations relatives au réseau utilisé (anti abus)
                 */
                // IPv4 - Filtrer sur un /24 || IPv6 - Filtrer sur un /56
                $req = MaBDD::getInstance()->prepare(
                    'UPDATE images SET abuse_network =
                    IF(LOCATE(\'.\', remote_addr) != 0,
                        SUBSTRING(remote_addr, 1, (LENGTH(remote_addr) - LOCATE(\'.\', REVERSE(remote_addr)))),
                        SUBSTRING(HEX(INET6_ATON(remote_addr)), 1, 14)
                    )
                    WHERE id = :id'
                );
                $req->bindValue(':id', $this->getId());
                $req->execute();
            }
        }

        return $monRetour;
    }

    /**
     * Bloquer une image en BDD
     */
    public function bloquer(): void
    {
        // J'enregistre les infos en BDD
        $req = MaBDD::getInstance()->prepare('UPDATE images SET isBloquee = 1, isApprouvee = 0 WHERE id = :id');
        $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);

        $req->execute();
    }

    /**
     * Approuver une image en BDD
     */
    public function approuver(): void
    {
        // J'enregistre les infos en BDD
        $req = MaBDD::getInstance()->prepare('UPDATE images SET isBloquee = 0, isSignalee = 0, isApprouvee = 1 WHERE id = :id');
        $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);

        $req->execute();
    }

    /**
     * Nombre d'appels IPv4 & IPv6
     * @return int
     */
    public function getNbViewTotal(): int
    {
        return parent::getNbViewTotal() + $this->getNbViewMiniatures();
    }

    /**
     * Récupérer le total d'affichage des miniatures
     * @return int
     */
    public function getNbViewMiniatures(): int
    {
        $monRetour = 0;

        // Chargement des miniatures
        $query = 'SELECT SUM(nb_view_v4 + nb_view_v6) as total FROM thumbnails WHERE images_id = :imagesId';
        $req = MaBDD::getInstance()->prepare($query);
        $req->bindValue(':imagesId', $this->getId(), PDO::PARAM_INT);
        $req->execute();

        // Je passe toutes les lignes de résultat
        foreach ($req->fetchAll() as $value) {
            if (!is_null($value->total)) {
                $monRetour = $value->total;
            }
        }

        return $monRetour;
    }
}
