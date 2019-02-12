<?php
/*
 * Copyright 2008-2019 Anael Mobilia
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
 */
class imageObject extends ressourceObject implements ressourceInterface {

   /**
    * Constructeur
    * @param string $newName nom de l'image
    */
   function __construct($newName = FALSE) {
      // Définition du type pour le ressourceObject
      $this->setType(ressourceObject::typeImage);

      // Si on me donne un ID d'image, je charge l'objet
      if ($newName) {
         if (!$this->charger($newName)) {
            // Envoi d'une exception si l'image n'existe pas
            throw new Exception('Image ' . $newName . ' inexistante');
         }
      }
   }

   /**
    * {@inheritdoc}
    */
   public function charger($newName) {
      // Retour
      $monRetour = FALSE;

      // Je vais chercher les infos en BDD
      $req = maBDD::getInstance()->prepare("SELECT * FROM images WHERE new_name = :newName");
      $req->bindValue(':newName', $newName, PDO::PARAM_STR);
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
         $this->setBloquee($resultat->isBloquee);
         $this->setSignalee($resultat->isSignalee);

         // Gestion du retour
         $monRetour = TRUE;
      }

      return $monRetour;
   }

   /**
    * {@inheritdoc}
    */
   public function sauver() {
      // J'enregistre les infos en BDD
      $req = maBDD::getInstance()->prepare("UPDATE images SET ip_envoi = :ipEnvoi, date_envoi = :dateEnvoi, old_name = :oldName, new_name = :newName, size = :size, height = :height, width = :width, last_view = :lastView, nb_view_v4 = :nbViewV4, nb_view_v6 = :nbViewV6, md5 = :md5, isBloquee = :isBloquee, isSignalee = :isSignalee WHERE id = :id");
      $req->bindValue(':ipEnvoi', $this->getIpEnvoi(), PDO::PARAM_STR);
      $req->bindValue(':dateEnvoi', $this->getDateEnvoiBrute());
      $req->bindValue(':oldName', $this->getNomOriginal(), PDO::PARAM_STR);
      $req->bindValue(':newName', $this->getNomNouveau(), PDO::PARAM_STR);
      $req->bindValue(':size', $this->getPoids(), PDO::PARAM_INT);
      $req->bindValue(':height', $this->getHauteur(), PDO::PARAM_INT);
      $req->bindValue(':width', $this->getLargeur(), PDO::PARAM_INT);
      $req->bindValue(':lastView', $this->getLastView());
      $req->bindValue(':nbViewV4', $this->getNbViewIPv4(), PDO::PARAM_INT);
      $req->bindValue(':nbViewV6', $this->getNbViewIPv6(), PDO::PARAM_INT);
      $req->bindValue(':md5', $this->getMd5(), PDO::PARAM_STR);
      $req->bindValue(':isBloquee', $this->isBloquee(), PDO::PARAM_INT);
      $req->bindValue(':isSignalee', $this->IsSignalee(), PDO::PARAM_INT);
      $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);

      $req->execute();
   }

   /**
    * {@inheritdoc}
    */
   public function supprimer() {
      $monRetour = TRUE;

      /**
       * Suppression de la ou les miniatures
       */
      // Chargement des miniatures
      $req = maBDD::getInstance()->prepare("SELECT new_name FROM thumbnails where id_image = :idImage");
      $req->bindValue(':idImage', $this->getId(), PDO::PARAM_INT);
      $req->execute();

      // Je passe toutes les lignes de résultat
      foreach ($req->fetchAll() as $value) {
         // Chargement de la miniature
         $maMiniature = new miniatureObject($value->new_name);
         // Suppression
         $maMiniature->supprimer();
      }

      /**
       * Suppression de l'affectation
       */
      if ($monRetour) {
         $req = maBDD::getInstance()->prepare("DELETE FROM possede WHERE image_id = :imageId");
         /* @var $req PDOStatement */
         $req->bindValue(':imageId', $this->getId(), PDO::PARAM_INT);
         $monRetour = $req->execute();
      }

      /**
       * Suppression de l'image en BDD
       */
      if ($monRetour) {
         $req = maBDD::getInstance()->prepare("DELETE FROM images WHERE id = :id");
         /* @var $req PDOStatement */
         $req->bindValue(':id', $this->getId(), PDO::PARAM_INT);
         $monRetour = $req->execute();
      }

      /**
       * Suppression du HDD
       */
      if ($monRetour) {
         // Existe-t-il d'autres occurences de cette image ?
         $req = maBDD::getInstance()->prepare("SELECT COUNT(*) AS nb FROM images WHERE md5 = :md5");
         /* @var $req PDOStatement */
         $req->bindValue(':md5', $this->getMd5(), PDO::PARAM_STR);
         $req->execute();
         $values = $req->fetch();

         // Il n'y a plus d'image identique...
         if ($values !== FALSE && (int) $values->nb === 0) {
            // Je supprime l'image sur le HDD
            $monRetour = unlink($this->getPathMd5());
         } elseif ($values === FALSE) {
            $monRetour = FALSE;
         }
      }

      return $monRetour;
   }

   /**
    * {@inheritdoc}
    */
   public function creer() {
      /**
       * Détermination du nom &&
       * Vérification de sa disponibilité
       */
      $tmpImage = new imageObject();
      $nb = 0;
      do {
         // Récupération d'un nouveau nom
         $new_name = $this->genererNom($nb);
         // Incrémentation compteur entropie sur le nom
         $nb++;
      } while ($tmpImage->charger($new_name) !== FALSE);
      // Effacement de l'objet temporaire
      unset($tmpImage);

      // On enregistre le nom
      $this->setNomNouveau($new_name);

      /**
       * Déplacement du fichier
       */
      // Chargement de l'image + enregistrement : permet de réduire la taille
      $monRetour = outils::setImage(outils::getImage($this->getPathTemp()), outils::getType($this->getPathTemp()), $this->getPathMd5());

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
         $req = maBDD::getInstance()->prepare("INSERT INTO images (ip_envoi, date_envoi, old_name, new_name, size, height, width, md5) VALUES (:ipEnvoi, NOW(), :oldName, :newName, :size, :height, :width, :md5)");
         $req->bindValue(':ipEnvoi', $this->getIpEnvoi(), PDO::PARAM_STR);
         // Date : NOW()
         $req->bindValue(':oldName', $this->getNomOriginal(), PDO::PARAM_STR);
         $req->bindValue(':newName', $this->getNomNouveau(), PDO::PARAM_STR);
         $req->bindValue(':size', $this->getPoids(), PDO::PARAM_INT);
         $req->bindValue(':height', $this->getHauteur(), PDO::PARAM_INT);
         $req->bindValue(':width', $this->getLargeur(), PDO::PARAM_INT);
         $req->bindValue(':md5', $this->getMd5(), PDO::PARAM_STR);

         if (!$req->execute()) {
            // Gestion de l'erreur d'insertion en BDD
            $monRetour = FALSE;
         } else {
            /**
             * Récupération de l'ID de l'image
             */
            $idEnregistrement = maBDD::getInstance()->lastInsertId();
            $this->setId($idEnregistrement);
         }
      }

      return $monRetour;
   }

}