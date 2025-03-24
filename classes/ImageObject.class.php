<?php

/*
 * Copyright 2008-2025 Anael MOBILIA
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
use Exception;
use ImagickException;
use PDO;

/**
 * Les images
 */
class ImageObject extends RessourceObject implements RessourceInterface
{
    private string $categorieBlocage;

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

    /**
     * @inheritDoc
     */
    public function charger(string $value, string $fromField = RessourceObject::SEARCH_BY_NAME): bool
    {
        // Charger les informations depuis la BDD
        $this->chargerFromBdd([$value], $fromField);

        // Gestion du retour
        return ($this->getId() !== 0);
    }

    /**
     * Charger des images en masse
     * @param ArrayObject|array $values Valeur du champ $fromField
     * @param string $fromField Nom du champ à utiliser en BDD pour identifier les images
     * @param bool $orderByIdAsc Trier les résultats par ID ASC ?
     * @return ImageObject[]
     */
    public static function chargerMultiple(ArrayObject|array $values, string $fromField, bool $orderByIdAsc = true): array
    {
        $monRetour = [];

        if (count($values) > 0) {
            $monRetour = (new ImageObject())->chargerFromBdd((array)$values, $fromField, false, $orderByIdAsc);
        }

        return $monRetour;
    }

    /**
     * Charger des images depuis la BDD
     * @param array $values Valeurs du champ $fromField
     * @param string $fromField Nom du champ à utiliser en BDD pour identifier les images
     * @param bool $saveOnCurrentObject Enregistrer les résultats dans l'objet courant ou dans un tableau ?
     * @param bool $orderByIdAsc Trier les résultats par ID ASC ?
     * @return ImageObject[]
     */
    private function chargerFromBdd(array $values, string $fromField, bool $saveOnCurrentObject = true, bool $orderByIdAsc = true): array
    {
        $monRetour = [];

        // Génération des placeholders
        $placeHolders = str_repeat('?,', count($values) - 1) . '?';
        // Je vais chercher les infos en BDD
        $req = MaBDD::getInstance()->prepare('SELECT *, (SELECT COUNT(*) FROM images im2 WHERE im2.isBloquee = 1 AND im2.abuse_network = images.abuse_network) AS reputation FROM images LEFT JOIN possede on images.id = possede.images_id WHERE ' . $fromField . ' IN (' . $placeHolders . ') ORDER BY images.id ' . ($orderByIdAsc ? 'ASC' : 'DESC'));
        $req->execute($values);

        // Traitement des résultats
        foreach ($req->fetchAll() as $resultat) {
            if ($saveOnCurrentObject) {
                $varName = 'this';
            } else {
                $varName = 'uneImage';
                unset(${$varName});
                ${$varName} = new ImageObject();
            }
            ${$varName}->setId($resultat->id);
            ${$varName}->setIpEnvoi($resultat->remote_addr);
            ${$varName}->setIpPortEnvoi($resultat->remote_port);
            ${$varName}->setDateEnvoi($resultat->date_action);
            ${$varName}->setNomOriginal($resultat->old_name);
            ${$varName}->setNomNouveau($resultat->new_name);
            ${$varName}->setPoids($resultat->size);
            ${$varName}->setHauteur($resultat->height);
            ${$varName}->setLargeur($resultat->width);
            ${$varName}->setLastView($resultat->last_view);
            ${$varName}->setNbViewIPv4($resultat->nb_view_v4);
            ${$varName}->setNbViewIPv6($resultat->nb_view_v6);
            ${$varName}->setMd5($resultat->md5);
            ${$varName}->setBloquee($resultat->isBloquee);
            ${$varName}->setSignalee($resultat->isSignalee);
            ${$varName}->setApprouvee($resultat->isApprouvee);
            ${$varName}->setIdProprietaire($resultat->membres_id);
            ${$varName}->setSuspecte(($resultat->reputation > 0));
            ${$varName}->setCategorieBlocage($resultat->abuse_categorie);

            if (!$saveOnCurrentObject) {
                // Gestion du retour
                $monRetour[] = ${$varName};
            }
        }

        return $monRetour;
    }


    /**
     * @inheritDoc
     */
    public function sauver(): void
    {
        // J'enregistre les infos en BDD
        $req = MaBDD::getInstance()->prepare('UPDATE images SET remote_addr = :ipEnvoi, remote_port = :ipPortEnvoi, date_action = :dateEnvoi, old_name = :oldName, new_name = :newName, size = :size, height = :height, width = :width, last_view = :lastView, nb_view_v4 = :nbViewV4, nb_view_v6 = :nbViewV6, md5 = :md5, isBloquee = :isBloquee, isSignalee = :isSignalee, isApprouvee = :isApprouvee, abuse_categorie = :abuseCategorie WHERE id = :id');
        $req->bindValue(':ipEnvoi', $this->getIpEnvoi());
        $req->bindValue(':ipPortEnvoi', $this->getIpPortEnvoi(), PDO::PARAM_INT);
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
        $req->bindValue(':abuseCategorie', $this->getCategorieBlocage());
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

    /**
     * Récupérer la miniature preview de l'image
     * @return MiniatureObject
     * @throws ImageHebergException
     * @throws ImagickException
     */
    public function getPreviewMiniature(): MiniatureObject
    {
        $monRetour = new MiniatureObject();

        if (!HelperImage::isAnimatedWebp($this->getPathMd5())) {
            $miniatures = $this->getMiniatures(true);
            if ($miniatures->count() === 0) {
                // Duplication de l'image source
                $tmpFile = tempnam(sys_get_temp_dir(), uniqid('', true));
                copy($this->getPathMd5(), $tmpFile);

                // Génération de la miniature pour l'aperçu
                $maMiniature = new MiniatureObject();
                $maMiniature->setPathTemp($tmpFile);
                $maMiniature->setIdImage($this->getId());
                $maMiniature->redimensionner($maMiniature->getPathTemp(), $maMiniature->getPathTemp(), _SIZE_PREVIEW_, _SIZE_PREVIEW_);
                $maMiniature->setNomTemp('preview_' . $this->getId());
                $maMiniature->creer();
                $maMiniature->setIsPreview(true);
                $maMiniature->sauver();
                $monRetour = $maMiniature;
            } else {
                $monRetour = new MiniatureObject($miniatures->offsetGet(0));
            }
        }

        return $monRetour;
    }

    /**
     * @inheritDoc
     * @throws ImageHebergException
     */
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

        // Suppresion de l'affectation en BDD
        $req = MaBDD::getInstance()->prepare('DELETE FROM possede WHERE images_id = :imagesId');
        $req->bindValue(':imagesId', $this->getId(), PDO::PARAM_INT);
        if ($req->execute()) {
            // Suppresion de l'image en BDD
            $req = MaBDD::getInstance()->prepare('DELETE FROM images WHERE id = :id');
            $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);
            // Si plus aucune image n'utilise le fichier => supprimer l'image sur le HDD
            if (
                $req->execute()
                && $this->getNbUsages() === 0
                && is_file($this->getPathMd5())
            ) {
                unlink($this->getPathMd5());
            }
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
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

        // PHP ne gère pas les images WebP animée -> ne pas faire de traitements
        if (!HelperImage::isAnimatedWebp($this->getPathTemp())) {
            // Optimiser l'image (permettra de comparer son hash avec celles déjà stockées)
            HelperImage::setImage(HelperImage::getImage($this->getPathTemp()), HelperImage::getType($this->getPathTemp()), $this->getPathTemp());
        }

        /**
         * Déplacement du fichier
         */
        // Vérification de la non existence du fichier
        if ($this->getNbUsages() === 0) {
            // Copie du fichier vers l'emplacement de stockage
            // Ne peut pas être fait avant car le MD5 n'est pas encore connu
            $monRetour = copy($this->getPathTemp(), $this->getPathMd5());
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
            // @ IP + port d'envoi
            $this->setIpEnvoi($_SERVER['REMOTE_ADDR']);
            $this->setIpPortEnvoi($_SERVER['REMOTE_PORT']);

            /**
             * Création en BDD
             */
            $req = MaBDD::getInstance()->prepare('INSERT INTO images (remote_addr, remote_port, date_action, old_name, new_name, size, height, width, md5, isBloquee) VALUES (:ipEnvoi, :ipPortEnvoi, NOW(), :oldName, :newName, :size, :height, :width, :md5, :isBloquee)');
            $req->bindValue(':ipEnvoi', $this->getIpEnvoi());
            $req->bindValue(':ipPortEnvoi', $this->getIpPortEnvoi());
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
                // Définir les informations relatives au réseau utilisé (anti abus)
                HelperAbuse::updateIpReputation();
            }
        }

        return $monRetour;
    }

    /**
     * Bloquer une image en BDD
     * Effet contaminant sur les autres images partagant le même MD5
     */
    public function bloquer(): void
    {
        // J'enregistre les infos en BDD
        $req = MaBDD::getInstance()->prepare('UPDATE images SET isBloquee = 1, isApprouvee = 0 WHERE md5 = :md5');
        $req->bindValue(':md5', $this->getMd5());

        $req->execute();
    }

    /**
     * Approuver une image en BDD
     * Effet contaminant sur les autres images partagant le même MD5
     */
    public function approuver(): void
    {
        // J'enregistre les infos en BDD
        $req = MaBDD::getInstance()->prepare('UPDATE images SET isBloquee = 0, isSignalee = 0, isApprouvee = 1, abuse_categorie = \'\' WHERE md5 = :md5');
        $req->bindValue(':md5', $this->getMd5());

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

    /**
     * Catégorie de blocage
     * @return string
     */
    public function getCategorieBlocage(): string
    {
        return $this->categorieBlocage;
    }

    /**
     * @param string $categorieBlocage Catégorie de blocage
     * @return void
     */
    public function setCategorieBlocage(string $categorieBlocage): void
    {
        $this->categorieBlocage = $categorieBlocage;
    }


    /**
     * Categoriser une image en BDD
     * Effet contaminant sur les autres images partagant le même MD5
     */
    public function categoriser(): void
    {
        // J'enregistre les infos en BDD
        $req = MaBDD::getInstance()->prepare('UPDATE images SET abuse_categorie = :abuseCategorie WHERE md5 = :md5');
        $req->bindValue(':md5', $this->getMd5());
        $req->bindValue(':abuseCategorie', $this->getCategorieBlocage());

        $req->execute();
    }
}
