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

use PHPUnit\Framework\TestCase;

class membreTest extends TestCase {

   /**
    * Fonction requise par l'extension Database
    * @return type
    */
   public function getConnection() {
      $pdo = new PDO('sqlite::memory:');
      return $this->createDefaultDBConnection($pdo, ':memory:');
   }

   /**
    * Fonction requise par l'extension Database
    * @return \PHPUnit_Extensions_Database_DataSet_DefaultDataSet
    */
   public function getDataSet() {
      return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
   }

   /**
    * Création d'un compte membre avec un nom déjà existant
    */
   public function testMembreCreerCompteDoublon() {
      require_once 'config/configV2.php';
      /**
       *  Injection des valeurs du formulaire
       */
      $_POST['valider'] = 1;
      $_POST['userName'] = 'admin';
      $_POST['userPassword'] = 'monPassword';
      $_POST['userMail'] = 'contrib@anael.eu';
      $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

      /**
       *  Appel de la page
       */
      ob_start();
      require 'membre/creerCompte.php';
      ob_end_clean();

      /**
       * Vérification des valeurs
       */
      $monMembre = new utilisateurObject();
      $this->assertEquals(FALSE, $monMembre->connexion($_POST['userName'], $_POST['userPassword']), "connexion : le nom d'utilisateur doit être unique");
   }

   /**
    * Création d'un compte membre.
    * @depends testMembreCreerCompteDoublon
    */
   public function testMembreCreerCompte() {
      /**
       *  Injection des valeurs du formulaire
       */
      $_POST['valider'] = 1;
      $_POST['userName'] = 'username';
      $_POST['userPassword'] = 'password';
      $_POST['userMail'] = 'contrib@anael.eu';
      $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

      /**
       *  Appel de la page
       */
      ob_start();
      require 'membre/creerCompte.php';
      ob_end_clean();

      /**
       * Récupération d'un objet
       */
      $monMembre = new utilisateurObject(3);

      /**
       * Vérification des valeurs
       */
      // Email
      $this->assertEquals('contrib@anael.eu', $monMembre->getEmail(), "Vérification email");
      // ID
      $this->assertEquals(3, $monMembre->getId());
      // @ IP d'inscription
      $this->assertEquals('127.0.0.1', $monMembre->getIpInscription());
      // Niveau de droits
      $this->assertEquals(utilisateurObject::levelUser, $monMembre->getLevel());
      // Nom
      $this->assertEquals('username', $monMembre->getUserName());
      $this->assertEquals(TRUE, $monMembre->connexion($_POST['userName'], $_POST['userPassword']));
   }

   /**
    * Modification du mail
    * @depends testMembreCreerCompte
    */
   public function testMembreModifierMail() {
      /**
       *  Injection des valeurs du formulaire
       */
      $_POST['modifierMail'] = 1;
      $_POST['userPasswordMail'] = 'password';
      $_POST['userMail'] = 'john.doe@example.com';
      $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

      /**
       *  Appel de la page
       */
      ob_start();
      require 'membre/monCompte.php';
      ob_end_clean();

      /**
       * Récupération d'un objet
       */
      $monMembre = new utilisateurObject(3);

      /**
       * Vérification des valeurs
       */
      // Email
      $this->assertEquals('john.doe@example.com', $monMembre->getEmail(), "getEmail");
      $this->assertEquals(TRUE, $monMembre->connexion('username', $_POST['userPasswordMail']), "connexion");
   }

   /**
    * Modification du mot de passe
    * @depends testMembreModifierMail
    */
   public function testMembreModifierPassword() {
      /**
       *  Injection des valeurs du formulaire
       */
      $_POST['modifierPwd'] = 1;
      $_POST['oldUserPassword'] = 'password';
      $_POST['newUserPassword'] = 'monPassword';
      $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

      /**
       *  Appel de la page
       */
      //ob_start();
      require 'membre/monCompte.php';
      //ob_end_clean();

      /**
       * Récupération d'un objet
       */
      $monMembre = new utilisateurObject();

      /**
       * Vérification des valeurs
       */
      $this->assertEquals(TRUE, $monMembre->connexion('username', $_POST['newUserPassword']), "connexion");
   }

   /**
    * Suppression du compte sans cochage de la checkbox
    * @depends testMembreModifierPassword
    */
   public function testMembreSupprimerCompteRequiertCheckbox() {
      /**
       *  Injection des valeurs du formulaire
       */
      $_POST['supprimerCompte'] = 1;
      $_POST['userPasswordDelete'] = 'monPassword';
      $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

      /**
       *  Appel de la page
       */
      ob_start();
      require 'membre/monCompte.php';
      ob_end_clean();

      /**
       * Récupération d'un objet
       */
      $monMembre = new utilisateurObject();

      /**
       * Vérification des valeurs
       */
      $this->assertEquals(TRUE, $monMembre->connexion('username', $_POST['userPasswordDelete']), "connexion devrait être possible");
   }

   /**
    * Suppression du compte
    * @depends testMembreSupprimerCompteRequiertCheckbox
    */
   public function testMembreSupprimerCompte() {
      /**
       *  Injection des valeurs du formulaire
       */
      $_POST['supprimerCompte'] = 1;
      $_POST['userPasswordDelete'] = 'monPassword';
      $_POST['confirmeDelete'] = '1';
      $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

      /**
       *  Appel de la page
       */
      //ob_start();
      require 'membre/monCompte.php';
      //ob_end_clean();

      /**
       * Récupération d'un objet
       */
      $monMembre = new utilisateurObject();

      /**
       * Vérification des valeurs
       */
      $this->assertEquals(FALSE, $monMembre->connexion('username', 'password'), "OLD connexion ne devrait plus être possible");
      $this->assertEquals(FALSE, $monMembre->connexion('username', $_POST['userPasswordDelete']), "connexion ne devrait plus être possible");

   }

   /**
    * Connexion au compte créé lors de la création de la BDD
    * @depends testMembreSupprimerCompte
    */
   public function testConnexionCompteHistorique() {
      /**
       * Injection des valeurs du formulaire
       */
      $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
      /**
       * Récupération d'un objet
       */
      $monMembre = new utilisateurObject();

      /**
       * Vérification des valeurs
       */
      $this->assertEquals(TRUE, $monMembre->connexion('admin', 'password'), "connexion au compte créé à l'import de la BDD devrait être possible");
   }

}