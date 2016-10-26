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
 * Fonctions génériques aux images et miniatures
 */
class ressourceObject {
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

    /**
     * Rotation d'une ressource
     * @param int $angle xxx° de rotation GAUCHE
     */
    function rotation($angle) {

    }

    /**
     * Redimensionnement d'une ressource
     * @param int $hauteurVoulue Largeur finale
     * @param int $largeurVoulue Hauteur finale
     */
    function redimensionnement($hauteurVoulue, $largeurVoulue) {

    }

    /**
     * Un utilisateur est-il propriétaire de l'image ?
     * @return boolean
     */
    public function verifierProprietaire() {
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

    /**
     * Date d'envoi formatée
     * @return string
     */
    public function getDateEnvoiFormate() {
        $phpdate = strtotime($this->getDateEnvoi());
        return date("d/m/Y H:i:s", $phpdate);
    }

    /**
     * Date de dernier affichage formaté
     * @return string
     */
    public function getLastViewFormate() {
        $phpdate = strtotime($this->getLastView());

        // Gestion du cas de non affichage
        if ($phpdate == 0) {
            return "-";
        }
        return date("d/m/Y", $phpdate);
    }

    /**
     * Nombre d'appels IPv4 & IPv6
     * @return int
     */
    public function getNbViewTotal() {
        return $this->getNbViewIPv4() + $this->getNbViewIPv6();
    }

    /**
     * Nom original de la ressource
     * @return string
     */
    public function getNomOriginalFormate() {
        return htmlentities($this->nomOriginal);
    }

    /**
     * Incrémente le nombre d'affichage IPv4
     */
    public function setNbViewIpv4PlusUn() {
        $this->nbViewV4 = $this->getNbViewIPv4() + 1;
        $this->setLastView(date("Y-m-d"));
    }

    /**
     * Incrémente le nombre d'affichage IPv6
     */
    public function setNbViewIpv6PlusUn() {
        $this->nbViewV6 = $this->getNbViewIPv6() + 1;
        $this->setLastView(date("Y-m-d"));
    }

    /**
     * GETTERS ET SETTERS
     */

    /**
     * ID de la ressource
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Nom original de la ressource
     * @return string
     */
    public function getNomOriginal() {
        return $this->nomOriginal;
    }

    /**
     * Nom image-heberg
     * @return string
     */
    public function getNomNouveau() {
        return $this->nomNouveau;
    }

    /**
     * Largeur en px
     * @return int
     */
    public function getLargeur() {
        return $this->largeur;
    }

    /**
     * Hauteur en px
     * @return int
     */
    public function getHauteur() {
        return $this->hauteur;
    }

    /**
     * Poids de la ressource
     * @return int
     */
    public function getPoids() {
        return $this->poids;
    }

    /**
     * Date de dernier affichage
     * @return type
     */
    public function getLastView() {
        return $this->lastView;
    }

    /**
     * Nb d'affichage en IPv4
     * @return int
     */
    public function getNbViewIPv4() {
        return $this->nbViewIPv4;
    }

    /**
     * Nb d'affichage en IPv6
     * @return int
     */
    public function getNbViewIPv6() {
        return $this->nbViewIPv6;
    }

    /**
     * Date d'envoi du fichier
     * @return type
     */
    public function getDateEnvoi() {
        return $this->dateEnvoi;
    }

    /**
     * MD5 de la ressource
     * @return string
     */
    public function getMd5() {
        return $this->md5;
    }

    /**
     * @ IP d'envoi
     * @return string
     */
    public function getIpEnvoi() {
        return $this->ipEnvoi;
    }

    /**
     * ID de l'image
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Nom original de la ressource
     * @param string $nomOriginal
     */
    public function setNomOriginal($nomOriginal) {
        $this->nomOriginal = $nomOriginal;
    }

    /**
     * Nom image-heberg
     * @param string $nomNouveau
     */
    public function setNomNouveau($nomNouveau) {
        $this->nomNouveau = $nomNouveau;
    }

    /**
     * Largeur en px
     * @param int $largeur
     */
    public function setLargeur($largeur) {
        $this->largeur = $largeur;
    }

    /**
     * Hauteur en px
     * @param int $hauteur
     */
    public function setHauteur($hauteur) {
        $this->hauteur = $hauteur;
    }

    /**
     * Poids de la ressource
     * @param int $poids
     */
    public function setPoids($poids) {
        $this->poids = $poids;
    }

    /**
     * Date de dernier affichage
     * @param type $lastView
     */
    public function setLastView($lastView) {
        $this->lastView = $lastView;
    }

    /**
     * Nb d'affichage en IPv4
     * @param int $nbViewIPv4
     */
    public function setNbViewIPv4($nbViewIPv4) {
        $this->nbViewIPv4 = $nbViewIPv4;
    }

    /**
     * Nb d'affichage en IPv6
     * @param int $nbViewIPv6
     */
    public function setNbViewIPv6($nbViewIPv6) {
        $this->nbViewIPv6 = $nbViewIPv6;
    }

    /**
     * Date d'envoi du fichier
     * @param type $dateEnvoi
     */
    public function setDateEnvoi($dateEnvoi) {
        $this->dateEnvoi = $dateEnvoi;
    }

    /**
     * MD5 de la ressource
     * @param string $md5
     */
    public function setMd5($md5) {
        $this->md5 = $md5;
    }

    /**
     * @ IP d'envoi
     * @param string $ipEnvoi
     */
    public function setIpEnvoi($ipEnvoi) {
        $this->ipEnvoi = $ipEnvoi;
    }

}