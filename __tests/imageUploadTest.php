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

class imageUploadTest extends PHPUnit_Framework_TestCase {
    // 404, banned, une image bloquée
    private $nbImagesOriginal = 3;
    // 404, banned
    private $nbImagePossedeOriginal = 2;

    /**
     * Nombre d'images en BDD
     * @return int
     */
    private static function countImagesEnBdd() {
        $maReq = maBDD::getInstance()->query("SELECT COUNT(*) AS nb FROM images");
        $result = $maReq->fetch();
        return $result->nb;
    }

    /**
     * Nombre d'images POSSEDEES en BDD
     * @return int
     */
    private static function countImagesPossedeesEnBdd() {
        $maReq = maBDD::getInstance()->query("SELECT COUNT(*) AS nb FROM possede");
        $result = $maReq->fetch();
        return $result->nb;
    }

    /**
     * Test de l'envoi simple : présence BDD et HDD
     */
    public function testEnvoi() {
        require_once 'config/configV2.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_banned.gif';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Envoi image ne doit pas être détecté dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), ++$this->nbImagesOriginal, "Envoi image doit créer d'image en BDD");
        $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '6/6a9dd81ae12c79d953031bc54c07f900'), "Envoi image doit créer d'image sur HDD");
    }

    /**
     * Envoi sans affichage page index.php
     * @depends testEnvoi
     */
    public function testEnvoiBrut() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Non affichage du formulaire d'upload devrait être détecté dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), $this->nbImagesOriginal, "Non affichage du formulaire d'upload ne doit pas créer d'image en BDD");
    }

    /**
     * Envoi sans fichier
     * @depends testEnvoiBrut
     */
    public function testEnvoiSansFichier() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Absence de fichier envoyé devrait être détecté dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), $this->nbImagesOriginal, "Absence de fichier envoyé ne doit pas créer d'image en BDD");
    }

    /**
     * Fichier trop lourd
     * @depends testEnvoiSansFichier
     */
    public function testEnvoiGrosFichier() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = _IMAGE_POIDS_MAX_ + 1;

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Fichier trop gros devrait être détecté dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), $this->nbImagesOriginal, "Fichier trop gros ne doit pas créer d'image en BDD");
    }

    /**
     * Type Mime : envoi d'un fichier doc
     * @depends testEnvoiSansFichier
     */
    public function testTypeMimePasUneImage() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'fichier_doc.doc';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Type mime : pas une image doit être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), $this->nbImagesOriginal, "type mime : pas une image doit être bloquée en BDD");
    }

    /**
     * Type Mime : mauvais type de fichier (DOC).jpg
     * @depends testTypeMimePasUneImage
     */
    public function testTypeMimeMauvaiseTypeFichier() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'fichier_doc.jpg';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Type mime : fausse image doit être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), $this->nbImagesOriginal, "type mime : fausse image doit être bloquée en BDD");
    }

    /**
     * Type Mime : mauvaise extension (JPG).png
     * @depends testTypeMimeMauvaiseTypeFichier
     */
    public function testTypeMimeMauvaiseExtension() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_jpg.png';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Type mime : extension incorrecte ne doit pas poser de soucis dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), ++$this->nbImagesOriginal, "Type mime : extension incorrecte ne doit pas être bloquée en BDD");
    }

    /**
     * Dimensions de l'image - Très large
     * @depends testTypeMimeMauvaiseExtension
     */
    public function testTresLarge() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_tres_large.png';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Image 10000x1 ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), ++$this->nbImagesOriginal, "Image 10000x1 ne doit pas être bloquée en BDD");
    }

    /**
     * Dimensions de l'image - Très haute
     * @depends testTresLarge
     */
    public function testTresHaute() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_tres_haute.png';


        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Image 1x10000 ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), ++$this->nbImagesOriginal, "Image 1x10000 ne doit pas être bloquée en BDD");
    }

    /**
     * Dimensions de l'image - Trop grende
     * @depends testTresHaute
     */
    public function testTropGrande() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_10000x10000.png';


        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Image 10000x10000 doit être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), $this->nbImagesOriginal, "Image 10000x10000 doit être bloquée en BDD");
    }

    /**
     * Envoi d'une image authentifié
     * @depends testTropGrande
     */
    public function testEnvoiImageAuthentifie() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_authentifie.png';

        // Création d'une session
        $_SESSION['id'] = 1;
        $_SESSION['IP'] = '127.0.0.1';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Envoi image authentifié ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), ++$this->nbImagesOriginal, "Envoi image authentifié ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), ++$this->nbImagePossedeOriginal, "Envoi image authentifié ne doit pas être bloquée en BDD");
    }

    /**
     * Renvoi d'une image - Anonyme / Anonyme
     * @depends testEnvoiImageAuthentifie
     */
    public function testRenvoiImageAnonymeAnonyme() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_tres_haute.png';


        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), $this->nbImagesOriginal, "Renvoi image doit être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), $this->nbImagePossedeOriginal, "Renvoi image doit être bloquée en BDD");
    }

    /**
     * Renvoi d'une image - Anonyme / Authentifié
     * @depends testRenvoiImageAnonymeAnonyme
     */
    public function testRenvoiImageAnonymeAuthentifie() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_tres_haute.png';

        // Création d'une session
        $_SESSION['id'] = 1;
        $_SESSION['IP'] = '127.0.0.1';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), ++$this->nbImagesOriginal, "Renvoi image ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), ++$this->nbImagePossedeOriginal, "Renvoi image ne doit pas être bloquée en BDD");
    }

    /**
     * Renvoi d'une image - Authentifié / Anonyme
     * @depends testRenvoiImageAnonymeAuthentifie
     */
    public function testRenvoiImageAuthentifieAnonyme() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_authentifie.png';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), ++$this->nbImagesOriginal, "Renvoi image ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), $this->nbImagePossedeOriginal, "Renvoi image doit être bloquée en BDD");
    }

    /**
     * Renvoi d'une image - Authentifié / Authentifié
     * @depends testRenvoiImageAuthentifieAnonyme
     */
    public function testRenvoiImageAuthentifieAuthentifie() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_authentifie.png';

        // Création d'une session
        $_SESSION['id'] = 1;
        $_SESSION['IP'] = '127.0.0.1';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), $this->nbImagesOriginal, "Renvoi image doit être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), $this->nbImagePossedeOriginal, "Renvoi image doit être bloquée en BDD");
    }

    /**
     * Renvoi d'une image - Authentifié / Authentifié Autrement
     * @depends testRenvoiImageAuthentifieAuthentifie
     */
    public function testRenvoiImageAuthentifieAuthentifie2() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . 'image_authentifie.png';

        // Création d'une session
        $_SESSION['id'] = 2;
        $_SESSION['IP'] = '127.0.0.1';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), ++$this->nbImagesOriginal, "Renvoi image ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), ++$this->nbImagePossedeOriginal, "Renvoi image ne doit pas être bloquée en BDD");
    }

}