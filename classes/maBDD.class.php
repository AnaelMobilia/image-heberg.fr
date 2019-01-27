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
 * Lien vers la BDD
 *
 * @author anael
 */
class maBDD {
   // PDO
   private $maBDD = null;
   // Instance de la classe
   private static $monInstance = null;

   /**
    * Constructeur
    */
   private function __construct() {
      $this->maBDD = new PDO('mysql:host=' . _BDD_HOST_ . ';dbname=' . _BDD_NAME_, _BDD_USER_, _BDD_PASS_);
      $this->maBDD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->maBDD->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
   }

   /**
    * Crée & renvoi l'objet d'instance
    * @return PDO
    */
   public static function getInstance() {
      // Si pas de connexion active, en crée une
      if (is_null(self::$monInstance)) {
         self::$monInstance = new maBDD();
      }
      return self::$monInstance;
   }

   /**
    * PDO::query
    * @param type $query
    * @return type
    */
   public function query($query) {
      return $this->maBDD->query($query);
   }

   /**
    * PDO::prepare
    * @param type $query
    * @return type
    */
   public function prepare($query) {
      return $this->maBDD->prepare($query);
   }

   /**
    * PDO::lastInsertId
    * @return type
    */
   public function lastInsertId() {
      return $this->maBDD->lastInsertId();
   }

   /**
    * Fermeture du PDO
    */
   public static function close() {
      self::$monInstance = null;
   }

}