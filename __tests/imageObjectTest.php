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

class imageObjectTest extends PHPUnit_Extensions_Database_TestCase {

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
     * Création d'un compte membre
     */
    public function testMembreCreerCompte() {
        /**
         *  Injection des valeurs du formulaire
         */
        $_POST['valider'] = 1;
        $_POST['userName'] = 'admin';
        $_POST['userPassword'] = 'password';
        $_POST['userMail'] = 'contrib@anael.eu';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        /**
         *  Appel de la page
         */
        require 'membre/creerCompte.php';

        /**
         * Récupération d'un objet
         */
        $monMembre = new utilisateurObject(1);

        /**
         * Vérification des valeurs
         */
        // Email
        $this->assertEquals('contrib@anael.eu', $monMembre->getEmail(), "Vérification email");
        // ID
        $this->assertEquals(1, $monMembre->getId());
        // @ IP d'inscription
        $this->assertEquals('127.0.0.1', $monMembre->getIpInscription());
        // Niveau de droits
        $this->assertEquals('membre', $monMembre->getLevel());
        // Nom
        $this->assertEquals('admin', $monMembre->getUserName());
        // Nom en BDD
        $this->assertEquals('admin', $monMembre->getUserNameBDD());
        // Login / password
        $monMembre->setUserName('admin');
        $monMembre->setPassword('password');
        $this->assertEquals(TRUE, $monMembre->connexion());
    }

    /**
     * Rotation des images
     */
    public function testRotationImages() {
        $monImage = new imageObject();
        // JPG
        $angle = 90;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.jpg', _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle);
        $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.jpg-' . $angle, _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle);
        $angle = 180;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.jpg', _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle);
        $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.jpg-' . $angle, _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle);
        $angle = 270;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.jpg', _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle);
        $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.jpg-' . $angle, _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle);

        // PNG
        $angle = 90;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.png', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle);
        $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.png-' . $angle, _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle);
        $angle = 180;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.png', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle);
        $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.png-' . $angle, _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle);
        $angle = 270;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.png', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle);
        $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.png-' . $angle, _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle);

        // GIF
        $angle = 90;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.gif', _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle);
        $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.gif-' . $angle, _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle);
        $angle = 180;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.gif', _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle);
        $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.gif-' . $angle, _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle);
        $angle = 270;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.gif', _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle);
        $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.gif-' . $angle, _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle);
    }

}