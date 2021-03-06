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
 * Fonctions génériques aux images et miniatures
 */
abstract class RessourceObject
{
    public const TYPE_IMAGE = 1;
    public const TYPE_MINIATURE = 2;

    private $id;
    private $nomOriginal;
    private $nomNouveau;
    private $largeur;
    private $hauteur;
    private $poids;
    private $lastView;
    private $nbViewIPv4;
    private $nbViewIPv6;
    private $dateEnvoi;
    private $md5;
    private $ipEnvoi;
    private $isBloquee;
    private $isSignalee;
    private $pathTemp;
    private $type;
    private $nomTemp;

    /**
     * Génère le nom d'une nouvelle image
     * @param int $nb nombre de chiffres à rajouter à la fin du nom
     * @return string nom de l'image
     */
    protected function genererNom($nb = 0)
    {
        // Random pour unicité + cassage lien nom <-> @IP
        $random = rand(100, 999);
        // @IP expéditeur
        $adresseIP = abs(crc32($_SERVER['REMOTE_ADDR'] . $random));
        // Timestamp d'envoi
        $timestamp = $_SERVER['REQUEST_TIME'];

        // Calcul du nom de l'image
        $new_name = $timestamp . $adresseIP . substr($random, 0, $nb) . '.' . Outils::getExtension($this->getPathTemp());

        return $new_name;
    }

    /**
     * MD5 de la ressource
     * @return string
     */
    public function getMd5()
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
    public function getPathMd5()
    {
        // Path final
        $pathFinal = '';

        // Path du type d'image
        $pathDuType = '';
        if ($this->getType() === self::TYPE_IMAGE) {
            // Image
            $pathDuType = _PATH_IMAGES_;
        } else {
            // Miniature
            $pathDuType = _PATH_MINIATURES_;
        }

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
    public function getNbDoublons()
    {
        // Retour - -1 par défaut pour marquer l'erreur
        $monRetour = -1;

        // Existe-t-il d'autres occurences de cette image ?
        if ($this->getType() === self::TYPE_IMAGE) {
            // Image
            $req = MaBDD::getInstance()->prepare("SELECT COUNT(*) AS nb FROM images WHERE md5 = :md5");
        } else {
            // Miniature
            $req = MaBDD::getInstance()->prepare("SELECT COUNT(*) AS nb FROM thumbnails WHERE md5 = :md5");
        }
        /* @var $req \PDOStatement */
        $req->bindValue(':md5', $this->getMd5(), PDO::PARAM_STR);
        $req->execute();
        $values = $req->fetch();
        if ($values !== false) {
            $monRetour = (int) $values->nb;
        }
        return $monRetour;
    }

    /**
     * URL de la ressource
     * @return string
     */
    public function getURL()
    {
        // Path du type d'image
        $urlDuType = '';
        if ($this->getType() === self::TYPE_IMAGE) {
            // Image
            $urlDuType = _URL_IMAGES_;
        } else {
            // Miniature
            $urlDuType = _URL_MINIATURES_;
        }

        return $urlDuType . $this->getNomNouveau();
    }

    /**
     * Rotation d'une ressource <br />
     * Inclus mise à jour largeur / hauteur / poids de l'image
     * @param int $angle xxx° de rotation horaire
     * @param string $pathSrc chemin de la ressource d'origine
     * @param string $pathDst chemin de la ressource de destination
     * @return boolean succès ?
     */
    public function rotation($angle, $pathSrc, $pathDst)
    {
        // Je charge l'image en mémoire
        $resImg = Outils::getImage($pathSrc);

        // Rotation (Imagick est dans le sens horaire, imagerotate dans le sens anti-horaire)
        $resImg->rotateImage("rgb(0,0,0)", $angle);

        // J'enregistre l'image
        $retour = Outils::setImage($resImg, Outils::getType($pathSrc), $pathDst);

        return $retour;
    }

    /**
     * Redimensionne une image en respectant le ratio de l'image original
     * @param string $pathSrc chemin de la ressource d'origine
     * @param string $pathDst chemin de la ressource de destination
     * @param int $largeurDemandee largeur souhaitée
     * @param int $hauteurDemandee hauteur souhaitée
     * @return boolean réussi ?
     */
    public function redimensionner($pathSrc, $pathDst, $largeurDemandee, $hauteurDemandee)
    {
        // Chargement de l'image
        $monImage = Outils::getImage($pathSrc);

        // Récupération de ses dimensions
        $largeurImage = $monImage->getImageWidth();
        $hauteurImage = $monImage->getImageHeight();

        // Dimensions incohérentes : on arrête
        if ($hauteurImage <= 0 || $hauteurDemandee <= 0 || $largeurImage <= 0 || $largeurDemandee <= 0 || $largeurImage <= $largeurDemandee || $hauteurImage <= $hauteurDemandee) {
            return false;
        }

        // Redimensionnement par Imagick
        $monImage->thumbnailImage($largeurDemandee, $hauteurDemandee, true);

        // Ecriture de l'image
        $monRetour = Outils::setImage($monImage, Outils::getType($pathSrc), $pathDst);

        return $monRetour;
    }

    /**
     * Cet utilisateur est-il propriétaire de l'image ?
     * @return boolean
     */
    public function isProprietaire()
    {
        $monRetour = false;

        // Je vais chercher les infos en BDD
        $req = MaBDD::getInstance()->prepare("SELECT * FROM possede WHERE image_id = :imageId");
        /* @var $req \PDOStatement */
        $req->bindValue(':imageId', $this->getId(), PDO::PARAM_INT);
        $req->execute();

        // Je récupère les potentielles valeurs
        $values = $req->fetch();

        // Si l'image à un propriétaire...
        if ($values !== false) {
            // Le propriétaire est-il connecté ?
            $uneSession = new SessionObject();

            // Est-ce le propriétaire de l'image ?
            if ((int) $values->pk_membres === $uneSession->getId()) {
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
    public function getDateEnvoiFormatee()
    {
        $phpdate = strtotime($this->getDateEnvoiBrute());
        return date("d/m/Y H:i:s", $phpdate);
    }

    /**
     * Date de dernier affichage formaté
     * @return string
     */
    public function getLastViewFormate()
    {
        $phpdate = strtotime($this->getLastView());

        // Gestion du cas de non affichage
        if ($phpdate === 0) {
            return "-";
        }
        return date("d/m/Y", $phpdate);
    }

    /**
     * Nombre d'appels IPv4 & IPv6
     * @return int
     */
    public function getNbViewTotal()
    {
        return (int) $this->getNbViewIPv4() + $this->getNbViewIPv6();
    }

    /**
     * Nom original de la ressource
     * @return string
     */
    public function getNomOriginalFormate()
    {
        return htmlentities($this->nomOriginal);
    }

    /**
     * Incrémente le nombre d'affichage IPv4
     */
    public function setNbViewIpv4PlusUn()
    {
        $this->nbViewIPv4 = $this->getNbViewIPv4() + 1;
        $this->setLastView(date("Y-m-d"));
    }

    /**
     * Incrémente le nombre d'affichage IPv6
     */
    public function setNbViewIpv6PlusUn()
    {
        $this->nbViewIPv6 = $this->getNbViewIPv6() + 1;
        $this->setLastView(date("Y-m-d"));
    }
    /**
     * GETTERS ET SETTERS
     */

    /**
     * ID de la ressource
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * Nom original de la ressource
     * @return string
     */
    protected function getNomOriginal()
    {
        return $this->nomOriginal;
    }

    /**
     * Nom image-heberg
     * @return string
     */
    public function getNomNouveau()
    {
        return $this->nomNouveau;
    }

    /**
     * Largeur en px
     * @return int
     */
    public function getLargeur()
    {
        return (int) $this->largeur;
    }

    /**
     * Hauteur en px
     * @return int
     */
    public function getHauteur()
    {
        return (int) $this->hauteur;
    }

    /**
     * Poids de la ressource
     * @return int
     */
    public function getPoids()
    {
        return (int) $this->poids;
    }

    /**
     * Poids de la ressource en Mo
     * @return float
     */
    public function getPoidsMo()
    {
        return round($this->getPoids() / 1024 / 1024, 1);
    }

    /**
     * Date de dernier affichage
     * @return type
     */
    protected function getLastView()
    {
        return $this->lastView;
    }

    /**
     * Nb d'affichage en IPv4
     * @return int
     */
    protected function getNbViewIPv4()
    {
        return (int) $this->nbViewIPv4;
    }

    /**
     * Nb d'affichage en IPv6
     * @return int
     */
    protected function getNbViewIPv6()
    {
        return (int) $this->nbViewIPv6;
    }

    /**
     * Date d'envoi du fichier
     * @return type
     */
    public function getDateEnvoiBrute()
    {
        return $this->dateEnvoi;
    }

    /**
     * @ IP d'envoi
     * @return string
     */
    public function getIpEnvoi()
    {
        return $this->ipEnvoi;
    }

    /**
     * Image bloquée ?
     * @return boolean
     */
    public function isBloquee()
    {
        return $this->isBloquee;
    }

    /**
     * Image signalée ?
     * @return boolean
     */
    public function isSignalee()
    {
        return $this->isSignalee;
    }

    /**
     * Path temporaire (upload d'image)
     * @return string
     */
    public function getPathTemp()
    {
        return $this->pathTemp;
    }

    /**
     * Type d'image
     * @return int ressoruceObject const
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Nom temporaire (PC utilisateur - upload d'image)
     * @return string
     */
    public function getNomTemp()
    {
        return $this->nomTemp;
    }

    /**
     * Nom temporaire (PC utilisateur - upload d'image)
     * @param string $nomTemp
     */
    public function setNomTemp($nomTemp)
    {
        $this->nomTemp = $nomTemp;
    }

    /**
     * Type d'image
     * @param int $type RessourceObject const
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Path temporaire (upload d'image)
     * @param string $pathTemp
     */
    public function setPathTemp($pathTemp)
    {
        $this->pathTemp = $pathTemp;
    }

    /**
     * Image bloquée ?
     * @param boolean $bloquee
     */
    public function setBloquee($bloquee)
    {
        $this->isBloquee = $bloquee;
    }

    /**
     * Image signalée ?
     * @param boolean $isSignalee
     */
    public function setSignalee($isSignalee)
    {
        $this->isSignalee = $isSignalee;
    }

    /**
     * ID de l'image
     * @param int $id
     */
    protected function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Nom original de la ressource
     * @param string $nomOriginal
     */
    protected function setNomOriginal($nomOriginal)
    {
        $this->nomOriginal = $nomOriginal;
    }

    /**
     * Nom image-heberg
     * @param string $nomNouveau
     */
    protected function setNomNouveau($nomNouveau)
    {
        $this->nomNouveau = $nomNouveau;
    }

    /**
     * Largeur en px
     * @param int $largeur
     */
    protected function setLargeur($largeur)
    {
        $this->largeur = $largeur;
    }

    /**
     * Hauteur en px
     * @param int $hauteur
     */
    protected function setHauteur($hauteur)
    {
        $this->hauteur = $hauteur;
    }

    /**
     * Poids de la ressource
     * @param int $poids
     */
    protected function setPoids($poids)
    {
        $this->poids = $poids;
    }

    /**
     * Date de dernier affichage
     * @param type $lastView
     */
    protected function setLastView($lastView)
    {
        $this->lastView = $lastView;
    }

    /**
     * Nb d'affichage en IPv4
     * @param int $nbViewIPv4
     */
    protected function setNbViewIPv4($nbViewIPv4)
    {
        $this->nbViewIPv4 = $nbViewIPv4;
    }

    /**
     * Nb d'affichage en IPv6
     * @param int $nbViewIPv6
     */
    protected function setNbViewIPv6($nbViewIPv6)
    {
        $this->nbViewIPv6 = $nbViewIPv6;
    }

    /**
     * Date d'envoi du fichier
     * @param type $dateEnvoi
     */
    protected function setDateEnvoi($dateEnvoi)
    {
        $this->dateEnvoi = $dateEnvoi;
    }

    /**
     * MD5 de la ressource
     * @param string $md5
     */
    protected function setMd5($md5)
    {
        $this->md5 = $md5;
    }

    /**
     * @ IP d'envoi
     * @param string $ipEnvoi
     */
    protected function setIpEnvoi($ipEnvoi)
    {
        $this->ipEnvoi = $ipEnvoi;
    }
}
