<?php
/*
 * Copyright 2008-2018 Anael Mobilia
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
abstract class ressourceObject {
   const typeImage = 1;
   const typeMiniature = 2;

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
   private $bloque;
   private $pathTemp;
   private $type;
   private $nomTemp;

   /**
    * Génère le nom d'une nouvelle image
    * @param int $nb nombre de chiffres à rajouter à la fin du nom
    * @return string nom de l'image
    */
   protected function genererNom($nb = 0) {
      // Random pour unicité + cassage lien nom <-> @IP
      $random = rand(100, 999);
      // @IP expéditeur
      $adresseIP = abs(crc32($_SERVER['REMOTE_ADDR'] . $random));
      // Timestamp d'envoi
      $timestamp = $_SERVER['REQUEST_TIME'];

      // Calcul du nom de l'image
      $new_name = $timestamp . $adresseIP . substr($random, 0, $nb) . '.' . outils::getExtension($this->getPathTemp());

      return $new_name;
   }

   /**
    * MD5 de la ressource
    * @return string
    */
   public function getMd5() {
      // Création d'une image
      if (is_null($this->md5)) {
         // Fichier temporaire...
         $this->md5 = md5_file($this->getPathTemp());
      }

      return $this->md5;
   }

   /**
    * Path sur le HDD
    * @return string
    */
   public function getPathMd5() {
      // Path final
      $pathFinal = '';

      // Path du type d'image
      $pathDuType = '';
      if ($this->getType() === self::typeImage) {
         // Image
         $pathDuType = _PATH_IMAGES_;
      } else {
         // Miniature
         $pathDuType = _PATH_MINIATURES_;
      }

      if ($this->getType() === self::typeImage && ($this->getId() === 1 || $this->getId() === 2)) {
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
    * URL de la ressource
    * @return string
    */
   public function getURL() {
      // Path du type d'image
      $urlDuType = '';
      if ($this->getType() === self::typeImage) {
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
    * @param int $angle xxx° de rotation GAUCHE
    * @param string $pathSrc chemin de la ressource d'origine
    * @param string $pathDst chemin de la ressource de destination
    * @return boolean succès ?
    */
   function rotation($angle, $pathSrc, $pathDst) {
      // Je charge l'image en mémoire
      $resImg = outils::getImage($pathSrc);
      // Je vérifie que tout va bien
      if ($resImg === FALSE) {
         return FALSE;
      }

      // J'effectue la rotation
      $imgRotate = imagerotate($resImg, $angle, 0);

      // Je vérifie que tout va bien
      if ($imgRotate === FALSE) {
         return FALSE;
      }

      // Nettoyage mémoire (image d'origine)
      imagedestroy($resImg);

      // J'enregistre l'image
      $retour = outils::setImage($imgRotate, outils::getType($pathSrc), $pathDst);

      // La création du fichier s'est bien passé ?
      if ($retour === FALSE) {
         return FALSE;
      }

      // Mise à jour des propriétés de l'image
      // Dimensions
      $this->setLargeur(imagesx($imgRotate));
      $this->setHauteur(imagesy($imgRotate));

      // Poids de l'image
      $this->setPoids(filesize($pathDst));

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
   public function redimensionner($pathSrc, $pathDst, $largeurDemandee, $hauteurDemandee) {
      // Chargement de l'image
      $monImage = outils::getImage($pathSrc);

      // Récupération de ses dimensions
      $largeurImage = imagesx($monImage);
      $hauteurImage = imagesy($monImage);

      // Dimension nulle : on arrête
      if ($hauteurImage <= 0 || $hauteurDemandee <= 0 || $largeurImage <= 0 || $largeurDemandee <= 0 || $largeurImage <= $largeurDemandee || $hauteurImage <= $hauteurDemandee) {
         return false;
      }

      /**
       * @author Nicolas
       */
      if ($largeurImage > $hauteurImage) {
         // Format paysage
         $largeurMax = max(array($largeurDemandee, $hauteurDemandee));
         $hauteurMax = min(array($largeurDemandee, $hauteurDemandee));
      } else {
         // Format portrait ou carré
         $largeurMax = min(array($largeurDemandee, $hauteurDemandee));
         $hauteurMax = max(array($largeurDemandee, $hauteurDemandee));
      }
      // Calcul du ratio
      $monRatio = min(array($largeurMax / $largeurImage, $hauteurMax / $hauteurImage));

      // Dimensions finales
      $largeurFinale = round($largeurImage * $monRatio);
      $hauteurFinale = round($hauteurImage * $monRatio);

      // Debug
      if (_TRAVIS_) {
         echo "Initial : " . $largeurImage . " x " . $hauteurImage . "\r\n";
         echo "Demandé : " . $largeurDemandee . " x " . $hauteurDemandee . "\r\n";
         echo "Fourni : " . $largeurFinale . " x " . $hauteurFinale . "\r\n";
      }

      // Redimensionnement (en mémoire)
      $newImage = imagescale($monImage, $largeurFinale, $hauteurFinale);

      // Ecriture de l'image
      $monRetour = outils::setImage($newImage, outils::getType($pathSrc), $pathDst);

      return $monRetour;
   }

   /**
    * Cet utilisateur est-il propriétaire de l'image ?
    * @return boolean
    */
   public function isProprietaire() {
      $monRetour = FALSE;

      // Je vais chercher les infos en BDD
      $req = maBDD::getInstance()->prepare("SELECT * FROM possede WHERE image_id = ?");
      /* @var $req PDOStatement */
      $req->bindValue(1, $this->getId(), PDO::PARAM_INT);
      $req->execute();

      // Je récupère les potentielles valeurs
      $values = $req->fetch();

      // Si l'image à un propriétaire...
      if ($values !== FALSE) {
         // Le propriétaire est-il connecté ?
         $uneSession = new sessionObject();

         // Est-ce le propriétaire de l'image ?
         if ((int) $values->pk_membres === $uneSession->getId()) {
            // Si oui... on confirme !
            $monRetour = TRUE;
         }
      }

      return $monRetour;
   }

   /**
    * Date d'envoi formatée
    * @return string
    */
   public function getDateEnvoiFormatee() {
      $phpdate = strtotime($this->getDateEnvoiBrute());
      return date("d/m/Y H:i:s", $phpdate);
   }

   /**
    * Date de dernier affichage formaté
    * @return string
    */
   public function getLastViewFormate() {
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
   public function getNbViewTotal() {
      return (int) $this->getNbViewIPv4() + $this->getNbViewIPv6();
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
      $this->nbViewIPv4 = $this->getNbViewIPv4() + 1;
      $this->setLastView(date("Y-m-d"));
   }

   /**
    * Incrémente le nombre d'affichage IPv6
    */
   public function setNbViewIpv6PlusUn() {
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
   public function getId() {
      return (int) $this->id;
   }

   /**
    * Nom original de la ressource
    * @return string
    */
   protected function getNomOriginal() {
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
      return (int) $this->largeur;
   }

   /**
    * Hauteur en px
    * @return int
    */
   public function getHauteur() {
      return (int) $this->hauteur;
   }

   /**
    * Poids de la ressource
    * @return int
    */
   public function getPoids() {
      return (int) $this->poids;
   }

   /**
    * Date de dernier affichage
    * @return type
    */
   protected function getLastView() {
      return $this->lastView;
   }

   /**
    * Nb d'affichage en IPv4
    * @return int
    */
   protected function getNbViewIPv4() {
      return (int) $this->nbViewIPv4;
   }

   /**
    * Nb d'affichage en IPv6
    * @return int
    */
   protected function getNbViewIPv6() {
      return (int) $this->nbViewIPv6;
   }

   /**
    * Date d'envoi du fichier
    * @return type
    */
   public function getDateEnvoiBrute() {
      return $this->dateEnvoi;
   }

   /**
    * @ IP d'envoi
    * @return string
    */
   public function getIpEnvoi() {
      return $this->ipEnvoi;
   }

   /**
    * Fichier bloqué ?
    * @return boolean
    */
   public function isBloque() {
      return $this->bloque;
   }

   /**
    * Path temporaire (upload d'image)
    * @return string
    */
   public function getPathTemp() {
      return $this->pathTemp;
   }

   /**
    * Type d'image
    * @return int ressoruceObject const
    */
   public function getType() {
      return $this->type;
   }

   /**
    * Nom temporaire (PC utilisateur - upload d'image)
    * @return string
    */
   public function getNomTemp() {
      return $this->nomTemp;
   }

   /**
    * Nom temporaire (PC utilisateur - upload d'image)
    * @param string $nomTemp
    */
   public function setNomTemp($nomTemp) {
      $this->nomTemp = $nomTemp;
   }

   /**
    * Type d'image
    * @param int $type ressourceObject const
    */
   public function setType($type) {
      $this->type = $type;
   }

   /**
    * Path temporaire (upload d'image)
    * @param string $pathTemp
    */
   public function setPathTemp($pathTemp) {
      $this->pathTemp = $pathTemp;
   }

   /**
    * Fichier bloqué ?
    * @param boolean $bloque
    */
   public function setBloque($bloque) {
      $this->bloque = $bloque;
   }

   /**
    * ID de l'image
    * @param int $id
    */
   protected function setId($id) {
      $this->id = $id;
   }

   /**
    * Nom original de la ressource
    * @param string $nomOriginal
    */
   protected function setNomOriginal($nomOriginal) {
      $this->nomOriginal = $nomOriginal;
   }

   /**
    * Nom image-heberg
    * @param string $nomNouveau
    */
   protected function setNomNouveau($nomNouveau) {
      $this->nomNouveau = $nomNouveau;
   }

   /**
    * Largeur en px
    * @param int $largeur
    */
   protected function setLargeur($largeur) {
      $this->largeur = $largeur;
   }

   /**
    * Hauteur en px
    * @param int $hauteur
    */
   protected function setHauteur($hauteur) {
      $this->hauteur = $hauteur;
   }

   /**
    * Poids de la ressource
    * @param int $poids
    */
   protected function setPoids($poids) {
      $this->poids = $poids;
   }

   /**
    * Date de dernier affichage
    * @param type $lastView
    */
   protected function setLastView($lastView) {
      $this->lastView = $lastView;
   }

   /**
    * Nb d'affichage en IPv4
    * @param int $nbViewIPv4
    */
   protected function setNbViewIPv4($nbViewIPv4) {
      $this->nbViewIPv4 = $nbViewIPv4;
   }

   /**
    * Nb d'affichage en IPv6
    * @param int $nbViewIPv6
    */
   protected function setNbViewIPv6($nbViewIPv6) {
      $this->nbViewIPv6 = $nbViewIPv6;
   }

   /**
    * Date d'envoi du fichier
    * @param type $dateEnvoi
    */
   protected function setDateEnvoi($dateEnvoi) {
      $this->dateEnvoi = $dateEnvoi;
   }

   /**
    * MD5 de la ressource
    * @param string $md5
    */
   protected function setMd5($md5) {
      $this->md5 = $md5;
   }

   /**
    * @ IP d'envoi
    * @param string $ipEnvoi
    */
   protected function setIpEnvoi($ipEnvoi) {
      $this->ipEnvoi = $ipEnvoi;
   }

}