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

namespace ImageHebergTests;

use ImageHeberg\SessionObject;
use ImageHeberg\UtilisateurObject;
use PDO;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class MembreTest extends TestCase
{
    /**
     * Fonction requise par l'extension Database
     * @return mixed
     */
    public function getConnection(): mixed
    {
        $pdo = new PDO('sqlite::memory:');
        return $this->createDefaultDBConnection($pdo, ':memory:');
    }

    /**
     * Fonction requise par l'extension Database
     * @return PHPUnit_Extensions_Database_DataSet_DefaultDataSet
     */
    public function getDataSet(): PHPUnit_Extensions_Database_DataSet_DefaultDataSet
    {
        return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
    }

    public function testConnexionMembreExistant(): void
    {
        // Chargement de la configuration
        require_once 'config/config.php';
        unset($_POST);
        /**
         *  Injection des valeurs du formulaire
         */
        $_POST['valider'] = 1;
        $_POST['userName'] = 'admin';
        $_POST['userPassword'] = 'password';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';

        /**
         *  Appel de la page
         */
        ob_start();
        require 'membre/connexionCompte.php';
        ob_end_clean();

        /**
         * Vérification des valeurs
         */
        $maSession = new SessionObject();
        $this->assertEquals(
            UtilisateurObject::LEVEL_ADMIN,
            $maSession->getLevel(),
            'connexion : doit être OK'
        );
    }

    /**
     * Création d'un compte membre avec un nom déjà existant
     */
    #[Depends('testConnexionMembreExistant')]
    public function testMembreCreerCompteDoublon(): void
    {
        unset($_POST);
        /**
         *  Injection des valeurs du formulaire
         */
        $_POST['valider'] = 1;
        $_POST['userName'] = 'admin';
        $_POST['userPassword'] = 'monPassword';
        $_POST['userMail'] = 'myMail@example.com';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';
        $_SESSION['flag'] = true;

        /**
         *  Appel de la page
         */
        ob_start();
        require 'membre/creerCompte.php';
        ob_end_clean();

        /**
         * Vérification des valeurs
         */
        $monMembre = new UtilisateurObject();
        $this->assertFalse(
            $monMembre->connexion($_POST['userName'], $_POST['userPassword']),
            'connexion : le nom d\'utilisateur doit être unique'
        );
    }

    /**
     * Création d'un compte membre.
     */
    #[Depends('testMembreCreerCompteDoublon')]
    public function testMembreCreerCompte(): void
    {
        unset($_POST);
        /**
         *  Injection des valeurs du formulaire
         */
        $_POST['valider'] = 1;
        $_POST['userName'] = 'username';
        $_POST['userPassword'] = 'password';
        $_POST['userMail'] = 'myMail@example.com';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';
        $_SESSION['flag'] = true;

        /**
         *  Appel de la page
         */
        ob_start();
        require 'membre/creerCompte.php';
        ob_end_clean();

        /**
         * Récupération d'un objet
         */
        $monMembre = new UtilisateurObject(3);

        /**
         * Vérification des valeurs
         */
        // Email
        $this->assertEquals(
            'mymail@example.com',
            $monMembre->getEmail(),
            'Vérification email'
        );
        // ID
        $this->assertEquals(
            3,
            $monMembre->getId()
        );
        // @ IP d'inscription
        $this->assertEquals(
            '127.0.0.1',
            $monMembre->getIpInscription()
        );
        // Niveau de droits
        $this->assertEquals(
            UtilisateurObject::LEVEL_USER,
            $monMembre->getLevel()
        );
        // Nom
        $this->assertEquals(
            'username',
            $monMembre->getUserName()
        );
        $this->assertTrue(
            $monMembre->connexion($_POST['userName'], $_POST['userPassword'])
        );
    }

    /**
     * Modification du mail
     */
    #[Depends('testMembreCreerCompte')]
    public function testMembreModifierMail(): void
    {
        unset($_POST);
        /**
         *  Injection des valeurs du formulaire
         */
        $_POST['modifierMail'] = 1;
        $_POST['userPasswordMail'] = 'password';
        $_POST['userMail'] = 'john.doe@example.com';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';

        /**
         * Simulation d'une connexion
         */
        $unMembre = new UtilisateurObject();
        $this->assertTrue(
            $unMembre->connexion('username', $_POST['userPasswordMail']),
            'connexion avant'
        );

        /**
         *  Appel de la page
         */
        ob_start();
        require 'membre/monCompte.php';
        ob_end_clean();

        /**
         * Récupération de l'utilisateur
         */
        $monMembre = new UtilisateurObject(3);

        /**
         * Vérification des valeurs
         */
        // Email
        $this->assertEquals(
            'john.doe@example.com',
            $monMembre->getEmail(),
            'getEmail'
        );
        $this->assertTrue(
            $monMembre->connexion('username', $_POST['userPasswordMail']),
            'connexion après'
        );
    }

    /**
     * Modification du mot de passe
     */
    #[Depends('testMembreModifierMail')]
    public function testMembreModifierPassword(): void
    {
        unset($_POST);
        /**
         *  Injection des valeurs du formulaire
         */
        $_POST['modifierPwd'] = 1;
        $_POST['oldUserPassword'] = 'password';
        $_POST['newUserPassword'] = 'monPassword';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';

        /**
         * Simulation d'une connexion
         */
        $unMembre = new UtilisateurObject();
        $this->assertTrue(
            $unMembre->connexion('username', $_POST['oldUserPassword']),
            'connexion avant'
        );

        /**
         *  Appel de la page
         */
        ob_start();
        require 'membre/monCompte.php';
        ob_end_clean();

        /**
         * Récupération d'un objet
         */
        $monMembre = new UtilisateurObject();

        /**
         * Vérification des valeurs
         */
        $this->assertTrue(
            $monMembre->connexion('username', $_POST['newUserPassword']),
            'connexion'
        );
        $this->assertFalse(
            $monMembre->connexion('username', $_POST['oldUserPassword']),
            'connexion'
        );
    }

    /**
     * Suppression du compte sans cochage de la checkbox
     */
    #[Depends('testMembreModifierPassword')]
    public function testMembreSupprimerCompteRequiertCheckbox(): void
    {
        unset($_POST);
        /**
         *  Injection des valeurs du formulaire
         */
        $_POST['supprimerCompte'] = 1;
        $_POST['userPasswordDelete'] = 'monPassword';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';

        /**
         * Simulation d'une connexion
         */
        $unMembre = new UtilisateurObject();
        $this->assertTrue(
            $unMembre->connexion('username', $_POST['userPasswordDelete']),
            'connexion avant'
        );

        /**
         *  Appel de la page
         */
        ob_start();
        require 'membre/monCompte.php';
        ob_end_clean();

        /**
         * Récupération d'un objet
         */
        $monMembre = new UtilisateurObject();

        /**
         * Vérification des valeurs
         */
        $this->assertTrue(
            $monMembre->connexion('username', $_POST['userPasswordDelete']),
            'connexion devrait être possible'
        );
    }

    /**
     * Suppression du compte
     */
    #[Depends('testMembreSupprimerCompteRequiertCheckbox')]
    public function testMembreSupprimerCompte(): void
    {
        unset($_POST);
        /**
         *  Injection des valeurs du formulaire
         */
        $_POST['supprimerCompte'] = 1;
        $_POST['userPasswordDelete'] = 'monPassword';
        $_POST['confirmeDelete'] = 1;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';

        /**
         * Simulation d'une connexion
         */
        $unMembre = new UtilisateurObject();
        $this->assertTrue(
            $unMembre->connexion('username', $_POST['userPasswordDelete']),
            'connexion avant'
        );

        /**
         *  Appel de la page
         */
        ob_start();
        require 'membre/monCompte.php';
        ob_end_clean();

        /**
         * Récupération d'un objet
         */
        $monMembre = new UtilisateurObject();

        /**
         * Vérification des valeurs
         */
        $this->assertFalse(
            $monMembre->connexion('username', $_POST['userPasswordDelete']),
            'connexion ne devrait plus être possible'
        );
    }

    /**
     * Connexion au compte créé lors de la création de la BDD
     */
    #[Depends('testMembreSupprimerCompte')]
    public function testConnexionCompteHistorique(): void
    {
        unset($_POST);
        /**
         * Injection des valeurs du formulaire
         */
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';
        /**
         * Récupération d'un objet
         */
        $monMembre = new UtilisateurObject();

        /**
         * Vérification des valeurs
         */
        $this->assertTrue(
            $monMembre->connexion('admin', 'password'),
            'connexion au compte créé à l\'import de la BDD devrait être possible'
        );
    }
}
