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
use ImagickException;
use PDO;

/**
 * Fonctions génériques aux images et miniatures
 */
abstract class RessourceObject
{
    // Types de ressources
    public const TYPE_IMAGE = 1;
    public const TYPE_MINIATURE = 2;

    // Champ à utiliser en BDD pour charger la ressource
    public const SEARCH_BY_MD5 = 'md5';
    public const SEARCH_BY_NAME = 'new_name';
    public const SEARCH_BY_ID = 'id';

    // Attributs de la classe
    private int $id = 0;
    private string $nomOriginal = '';
    private string $nomNouveau = '';
    private int $largeur = 0;
    private int $hauteur = 0;
    private int $poids = 0;
    private string $lastView = '0000-00-00';
    private int $nbViewIPv4 = 0;
    private int $nbViewIPv6 = 0;
    private string $dateEnvoi = '';
    private ?string $md5 = null;
    private string $ipEnvoi = '';
    private bool $isBloquee = false;
    private bool $isSignalee = false;
    private bool $isApprouvee = false;
    private string $pathTemp = '';
    private int $type = self::TYPE_IMAGE;
    private string $nomTemp = '';
    private ?int $idProprietaire = null;

    /**
     * Génère le nom d'une nouvelle image
     * @param int $nb nombre de chiffres à rajouter à la fin du nom
     * @return string nom de l'image
     * @throws Exception
     */
    protected function genererNom(int $nb = 0): string
    {
        // Random pour unicité + cassage lien nom <-> @IP
        $random = random_int(100, 999);
        // @IP expéditeur
        $adresseIP = abs(crc32($_SERVER['REMOTE_ADDR'] . $random));
        // Timestamp d'envoi
        $timestamp = $_SERVER['REQUEST_TIME'];

        // Taille max : 28 chr
        //   - Timestamp : 1699048241 : 10 caractères jusqu'en 2286...
        //   - $adresseIP -> CRC32 : 8 bits en hexa, soit 16^8 valeurs => 4 294 967 296 : 10 caractères
        //   - Suffixe anti-doublon : hypothèse très haute 10k doublons : 4 caractères
        //   - Extension : 1+3 caractères

        // Calcul du nom de l'image
        return $timestamp . $adresseIP . substr($random, 0, $nb) . '.' . HelperImage::getExtension($this->getPathTemp());
    }

    /**
     * MD5 de la ressource
     * @return string
     */
    public function getMd5(): string
    {
        // Création d'une image => Utilisation du fichier temporaire
        if (is_null($this->md5) && $this->getPathTemp()) {
            // Fichier temporaire...
            $this->md5 = md5_file($this->getPathTemp());
        }

        return $this->md5;
    }

    /**
     * Path sur le HDD
     * @return string
     */
    public function getPathMd5(): string
    {
        // Path du type d'image
        if ($this->getType() === self::TYPE_IMAGE) {
            // Image
            $pathDuType = _PATH_IMAGES_;
        } else {
            // Miniature
            $pathDuType = _PATH_MINIATURES_;
        }

        // Path final
        if ($this->getType() === self::TYPE_IMAGE && ($this->getId() === 1 || $this->getId() === 2)) {
            // Gestion des images spécificques 404 / ban
            $pathFinal = $pathDuType . $this->getNomNouveau();
        } else {
            // Cas par défaut
            $rep = substr($this->getMd5(), 0, 1) . '/';
            $pathFinal = $pathDuType . $rep . $this->getMd5();
        }

        return $pathFinal;
    }

    /**
     * Nombre d'images ayant le même MD5 (Normalement 1 à minima, l'image courante...)
     * @return int nombre d'images ayant ce MD5 (-1 en cas d'erreur)
     */
    public function getNbDoublons(): int
    {
        // Retour - -1 par défaut pour marquer l'erreur
        $monRetour = -1;

        // Existe-t-il d'autres occurences de cette image ?
        if ($this->getType() === self::TYPE_IMAGE) {
            // Image
            $req = MaBDD::getInstance()->prepare('SELECT COUNT(*) AS nb FROM images WHERE md5 = :md5');
        } else {
            // Miniature
            $req = MaBDD::getInstance()->prepare('SELECT COUNT(*) AS nb FROM thumbnails WHERE md5 = :md5');
        }
        $req->bindValue(':md5', $this->getMd5());
        $req->execute();
        $values = $req->fetch();
        if ($values !== false) {
            $monRetour = (int)$values->nb;
        }
        return $monRetour;
    }

    /**
     * URL de la ressource
     * @param bool $forceHttps Forcer le HTTPS ?
     * @return string
     */
    public function getURL($forceHttps = false): string
    {
        // Path du type d'image
        if ($this->getType() === self::TYPE_IMAGE) {
            // Image
            $urlDuType = _URL_IMAGES_;
        } else {
            // Miniature
            $urlDuType = _URL_MINIATURES_;
        }

        // Forcer le HTTPS ?
        if ($forceHttps) {
            $urlDuType = str_replace('http:/', 'https:/', $urlDuType);
        }

        return $urlDuType . $this->getNomNouveau();
    }

    /**
     * Rotation d'une ressource <br />
     * Inclus mise à jour largeur / hauteur / poids de l'image
     * @param int $angle xxx° de rotation horaire
     * @param string $pathSrc chemin de la ressource d'origine
     * @param string $pathDst chemin de la ressource de destination
     * @return bool succès ?
     * @throws ImagickException
     */
    public function rotation(int $angle, string $pathSrc, string $pathDst): bool
    {
        // Je charge l'image en mémoire
        $resImg = HelperImage::getImage($pathSrc);

        // Rotation (Imagick est dans le sens horaire, imagerotate dans le sens anti-horaire)
        if (HelperImage::getType($pathSrc) === IMAGETYPE_GIF) {
            // Image animée (GIF) : c'est une succession d'images !
            foreach ($resImg as $frame) {
                $frame->rotateImage('rgb(0,0,0)', $angle);
            }
        } else {
            // Cas standard (image non animée)
            $resImg->rotateImage('rgb(0,0,0)', $angle);
        }

        // J'enregistre l'image
        return HelperImage::setImage($resImg, HelperImage::getType($pathSrc), $pathDst);
    }

    /**
     * Redimensionne une image en respectant le ratio de l'image original
     * @param string $pathSrc chemin de la ressource d'origine
     * @param string $pathDst chemin de la ressource de destination
     * @param int $largeurDemandee largeur souhaitée
     * @param int $hauteurDemandee hauteur souhaitée
     * @return bool réussi ?
     * @throws ImagickException
     */
    public function redimensionner(string $pathSrc, string $pathDst, int $largeurDemandee, int $hauteurDemandee): bool
    {
        // Chargement de l'image
        $monImage = HelperImage::getImage($pathSrc);

        // Récupération de ses dimensions
        $largeurImage = $monImage->getImageWidth();
        $hauteurImage = $monImage->getImageHeight();

        // Dimensions incohérentes : on arrête
        if ($hauteurImage <= 0 || $hauteurDemandee <= 0 || $largeurImage <= 0 || $largeurDemandee <= 0 || $largeurImage <= $largeurDemandee || $hauteurImage <= $hauteurDemandee) {
            return false;
        }

        // Redimensionnement par Imagick
        if (HelperImage::getType($pathSrc) === IMAGETYPE_GIF) {
            // Cas image animée (GIF) : c'est une succession d'images !
            foreach ($monImage as $frame) {
                $frame->thumbnailImage($largeurDemandee, $hauteurDemandee, true);
            }
        } else {
            // Cas standard (image non animée)
            $monImage->thumbnailImage($largeurDemandee, $hauteurDemandee, true);
        }

        // Ecriture de l'image
        return HelperImage::setImage($monImage, HelperImage::getType($pathSrc), $pathDst);
    }

    /**
     * Cet utilisateur est-il propriétaire de l'image ?
     * @return bool
     */
    public function isProprietaire(): bool
    {
        $monRetour = false;

        // Si l'image à un propriétaire...
        if ($this->getIdProprietaire() !== null) {
            // Le propriétaire est-il connecté ?
            $uneSession = new SessionObject();

            // Est-ce le propriétaire de l'image ?
            if ($this->getIdProprietaire() === $uneSession->getId()) {
                // Si oui... on confirme !
                $monRetour = true;
            }
        }

        return $monRetour;
    }

    /**
     * Date d'envoi formatée
     * @return string
     */
    public function getDateEnvoiFormatee(): string
    {
        $phpdate = strtotime($this->getDateEnvoiBrute());
        return date('d/m/Y H:i:s', $phpdate);
    }

    /**
     * Date de dernier affichage formaté
     * @return string
     */
    public function getLastViewFormate(): string
    {
        $monRetour = '?';
        if ($this->getLastView() !== '0000-00-00') {
            $phpdate = strtotime($this->getLastView());

            // Gestion du cas de non affichage
            if ($phpdate !== 0 && $phpdate !== false) {
                $monRetour = date('d/m/Y', $phpdate);
            }
        }
        return $monRetour;
    }

    /**
     * Nombre d'appels IPv4 & IPv6
     * @return int
     */
    public function getNbViewTotal(): int
    {
        return $this->getNbViewIPv4() + $this->getNbViewIPv6();
    }

    /**
     * Nombre d'affichage par jour
     * @return int
     */
    public function getNbViewPerDay(): int
    {
        // date_diff ne comptabilise que les journées entières, alors qu'en SQL, on compare des dates
        // date_diff('2023-09-03 23:38:42', '2023-09-05 22:53:03') => 1
        // date_diff('2023-09-03', '2023-09-05 22:53:03') => 2
        // => substr() de la date d'envoi pour aligner sur les valeurs du SQL
        $nbJours = (int)date_diff(date_create(substr($this->getDateEnvoiBrute(), 0, 10)), date_create())->format('%r%a');

        // Le premier jour, autoriser les xxx vues de la journée
        if ($nbJours === 0) {
            $nbJours = 1;
        }

        return $this->getNbViewTotal() / $nbJours;
    }

    /**
     * Nom original de la ressource
     * @return string
     */
    public function getNomOriginalFormate(): string
    {
        return htmlentities($this->nomOriginal, ENT_SUBSTITUTE);
    }

    /**
     * Met à jour les statistiques (nb d'affichage et date) en BDD
     */
    public function updateStatsAffichage(string $remoteAddr): void
    {
        // Prendre la bonne table
        if ($this->getType() === self::TYPE_IMAGE) {
            // Image
            $table = "images";
        } else {
            // Miniature
            $table = "thumbnails";
        }

        // Prendre le bon type d'@ IP
        if (filter_var($remoteAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IPv4
            $typeAcces = 'nb_view_v4';
        } else {
            // IPv6
            $typeAcces = 'nb_view_v6';
        }

        $req = MaBDD::getInstance()->prepare('UPDATE ' . $table . ' SET last_view = NOW(), ' . $typeAcces . ' = ' . $typeAcces . ' + 1 WHERE id = :id');
        $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        $req->execute();
    }

    /**
     * GETTERS ET SETTERS
     */

    /**
     * ID de la ressource
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Nom original de la ressource
     * @return string
     */
    protected function getNomOriginal(): string
    {
        return $this->nomOriginal;
    }

    /**
     * Nom image-heberg
     * @return string
     */
    public function getNomNouveau(): string
    {
        return $this->nomNouveau;
    }

    /**
     * Largeur en px
     * @return int
     */
    public function getLargeur(): int
    {
        return $this->largeur;
    }

    /**
     * Hauteur en px
     * @return int
     */
    public function getHauteur(): int
    {
        return $this->hauteur;
    }

    /**
     * Poids de la ressource
     * @return int
     */
    public function getPoids(): int
    {
        return $this->poids;
    }

    /**
     * Poids de la ressource en Mo
     * @return float
     */
    public function getPoidsMo(): float
    {
        return round($this->getPoids() / 1024 / 1024, 1);
    }

    /**
     * Date de dernier affichage
     * @return string
     */
    protected function getLastView(): string
    {
        return $this->lastView;
    }

    /**
     * Nb d'affichage en IPv4
     * @return int
     */
    protected function getNbViewIPv4(): int
    {
        return $this->nbViewIPv4;
    }

    /**
     * Nb d'affichage en IPv6
     * @return int
     */
    protected function getNbViewIPv6(): int
    {
        return $this->nbViewIPv6;
    }

    /**
     * Date d'envoi du fichier
     * @return string
     */
    public function getDateEnvoiBrute(): string
    {
        return $this->dateEnvoi;
    }

    /**
     * @ IP d'envoi
     * @return string
     */
    public function getIpEnvoi(): string
    {
        return $this->ipEnvoi;
    }

    /**
     * Image bloquée ?
     * @return bool
     */
    public function isBloquee(): bool
    {
        return $this->isBloquee;
    }

    /**
     * Image signalée ?
     * @return bool
     */
    public function isSignalee(): bool
    {
        return $this->isSignalee;
    }

    /**
     * Image approuvée (marquée comme valide)
     * @return bool
     */
    public function isApprouvee(): bool
    {
        return $this->isApprouvee;
    }

    /**
     * Path temporaire (upload d'image)
     * @return string
     */
    public function getPathTemp(): string
    {
        return $this->pathTemp;
    }

    /**
     * Type d'image
     * @return int ressourceObject const
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Nom temporaire (PC utilisateur - upload d'image)
     * @return string
     */
    public function getNomTemp(): string
    {
        return $this->nomTemp;
    }

    /**
     * Nom temporaire (PC utilisateur - upload d'image)
     * @param string $nomTemp
     */
    public function setNomTemp(string $nomTemp): void
    {
        $this->nomTemp = $nomTemp;
    }

    /**
     * Type d'image
     * @param int $type RessourceObject const
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * Path temporaire (upload d'image)
     * @param string $pathTemp
     */
    public function setPathTemp(string $pathTemp): void
    {
        $this->pathTemp = $pathTemp;
    }

    /**
     * Image bloquée ?
     * @param bool $bloquee
     */
    public function setBloquee(bool $bloquee): void
    {
        $this->isBloquee = $bloquee;
    }

    /**
     * Image signalée ?
     * @param bool $isSignalee
     */
    public function setSignalee(bool $isSignalee): void
    {
        $this->isSignalee = $isSignalee;
    }

    /**
     * Image approuvée (marquée comme valide) ?
     * @param bool $isApprouvee
     */
    public function setApprouvee(bool $isApprouvee): void
    {
        $this->isApprouvee = $isApprouvee;
    }

    /**
     * ID de l'image
     * @param int $id
     */
    protected function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Nom original de la ressource
     * @param string $nomOriginal
     */
    protected function setNomOriginal(string $nomOriginal): void
    {
        $this->nomOriginal = $nomOriginal;
    }

    /**
     * Nom image-heberg
     * @param string $nomNouveau
     */
    protected function setNomNouveau(string $nomNouveau): void
    {
        $this->nomNouveau = $nomNouveau;
    }

    /**
     * Largeur en px
     * @param int $largeur
     */
    protected function setLargeur(int $largeur): void
    {
        $this->largeur = $largeur;
    }

    /**
     * Hauteur en px
     * @param int $hauteur
     */
    protected function setHauteur(int $hauteur): void
    {
        $this->hauteur = $hauteur;
    }

    /**
     * Poids de la ressource
     * @param int $poids
     */
    protected function setPoids(int $poids): void
    {
        $this->poids = $poids;
    }

    /**
     * Date de dernier affichage
     * @param ?string $lastView
     */
    protected function setLastView(?string $lastView): void
    {
        if (is_null($lastView)) {
            // Si l'image n'a jamais été affichée, elle est à NULL en BDD
            $lastView = '';
        }
        $this->lastView = $lastView;
    }

    /**
     * Nb d'affichage en IPv4
     * @param int $nbViewIPv4
     */
    protected function setNbViewIPv4(int $nbViewIPv4): void
    {
        $this->nbViewIPv4 = $nbViewIPv4;
    }

    /**
     * Nb d'affichage en IPv6
     * @param int $nbViewIPv6
     */
    protected function setNbViewIPv6(int $nbViewIPv6): void
    {
        $this->nbViewIPv6 = $nbViewIPv6;
    }

    /**
     * Date d'envoi du fichier
     * @param string $dateEnvoi
     */
    protected function setDateEnvoi(string $dateEnvoi): void
    {
        $this->dateEnvoi = $dateEnvoi;
    }

    /**
     * MD5 de la ressource
     * @param string $md5
     */
    protected function setMd5(string $md5): void
    {
        $this->md5 = $md5;
    }

    /**
     * @ IP d'envoi
     * @param string $ipEnvoi
     */
    protected function setIpEnvoi(string $ipEnvoi): void
    {
        $this->ipEnvoi = $ipEnvoi;
    }

    /**
     * ID du compte propriétaire de l'image
     * @return ?int
     */
    public function getIdProprietaire(): ?int
    {
        return $this->idProprietaire;
    }

    /**
     * @param ?int $idProprietaire ID du compte propriétaire de l'image
     * @return void
     */
    protected function setIdProprietaire(?int $idProprietaire): void
    {
        $this->idProprietaire = $idProprietaire;
    }
}
