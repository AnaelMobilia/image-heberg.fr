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

class imageUploadAndDeleteTest extends PHPUnit_Framework_TestCase {

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
        $_FILES['fichier']['name'] = 'image_banned.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        // Gestion des différents tests
        unset($_SESSION['id']);

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Envoi image ne doit pas être bloqué dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 7, "Envoi image doit créer d'image en BDD");
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
        $this->assertEquals(self::countImagesEnBdd(), 7, "Non affichage du formulaire d'upload ne doit pas créer d'image en BDD");
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
        $this->assertEquals(self::countImagesEnBdd(), 7, "Absence de fichier envoyé ne doit pas créer d'image en BDD");
    }

    /**
     * Fichier trop lourd
     * @depends testEnvoiSansFichier
     */
    public function testEnvoiGrosFichier() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['name'] = 'nomFichier';
        $_FILES['fichier']['size'] = _IMAGE_POIDS_MAX_ + 1;

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Fichier trop gros devrait être détecté dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 7, "Fichier trop gros ne doit pas créer d'image en BDD");
    }

    /**
     * Type Mime : envoi d'un fichier doc
     * @depends testEnvoiGrosFichier
     */
    public function testTypeMimePasUneImage() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'fichier_doc.doc';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Type mime : pas une image doit être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 7, "type mime : pas une image doit être bloquée en BDD");
    }

    /**
     * Type Mime : mauvais type de fichier (DOC).jpg
     * @depends testTypeMimePasUneImage
     */
    public function testTypeMimeMauvaisTypeFichier() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'fichier_doc.jpg';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Type mime : fausse image doit être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 7, "type mime : fausse image doit être bloquée en BDD");
    }

    /**
     * Type Mime : mauvaise extension (JPG).png
     * @depends testTypeMimeMauvaisTypeFichier
     */
    public function testTypeMimeMauvaiseExtension() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_jpg.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Type mime : extension incorrecte ne doit pas poser de soucis dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 8, "Type mime : extension incorrecte ne doit pas être bloquée en BDD");
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
        $_FILES['fichier']['name'] = 'image_tres_large.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Image 10000x1 ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 9, "Image 10000x1 ne doit pas être bloquée en BDD");
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
        $_FILES['fichier']['name'] = 'image_tres_haute.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Image 1x10000 ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 10, "Image 1x10000 ne doit pas être bloquée en BDD");
    }

    /**
     * Dimensions de l'image - Trop grande
     * @depends testTresHaute
     */
    public function testTropGrande() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_10000x10000.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];


        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Image 10000x10000 doit être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 10, "Image 10000x10000 doit être bloquée en BDD");
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
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        // Création d'une session
        $_SESSION['id'] = 1;
        $_SESSION['IP'] = '127.0.0.1';
        $_SESSION['level'] = utilisateurObject::levelUser;
        $_SESSION['userName'] = 'username';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Envoi image authentifié ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 11, "Envoi image authentifié ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 4, "Envoi image authentifié ne doit pas être bloquée en BDD");
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
        $_FILES['fichier']['name'] = 'image_tres_haute.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];


        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 11, "Renvoi image doit être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 4, "Renvoi image doit être bloquée en BDD");
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
        $_FILES['fichier']['name'] = 'image_tres_haute.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        // Création d'une session
        $_SESSION['id'] = 1;
        $_SESSION['IP'] = '127.0.0.1';
        $_SESSION['level'] = utilisateurObject::levelUser;
        $_SESSION['userName'] = 'username';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 12, "Renvoi image ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 5, "Renvoi image ne doit pas être bloquée en BDD");
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
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 13, "Renvoi image ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 5, "Renvoi image doit être bloquée en BDD");
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
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        // Création d'une session
        $_SESSION['id'] = 1;
        $_SESSION['IP'] = '127.0.0.1';
        $_SESSION['level'] = utilisateurObject::levelUser;
        $_SESSION['userName'] = 'username';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 13, "Renvoi image doit être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 5, "Renvoi image doit être bloquée en BDD");
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
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        // Création d'une session
        $_SESSION['id'] = 2;
        $_SESSION['IP'] = '127.0.0.1';
        $_SESSION['level'] = utilisateurObject::levelUser;
        $_SESSION['userName'] = 'username';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), 14, "Renvoi image ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 6, "Renvoi image ne doit pas être bloquée en BDD");
    }

    /**
     * Suppression d'une image inexistante
     * @depends testRenvoiImageAuthentifieAuthentifie2
     */
    public function testSuppressionImageInexistante() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = 'fichierInexistant';

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Suppression image inexistante doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), 14, "Suppression image inexistante doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 6, "Suppression image inexistante doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Propriétaire en étant Anonyme
     * @depends testSuppressionImageInexistante
     */
    public function testSuppressionImageProprietaireAnonyme() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = '_image_404.png';

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Suppression image possédée par autrui doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), 14, "Suppression image possédée par autrui doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 6, "Suppression image possédée par autrui doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Propriétaire en étant Authentifié mais Autre
     * @depends testSuppressionImageProprietaireAnonyme
     */
    public function testSuppressionImageProprietaireAuthentifie2() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = '_image_404.png';

        // Création d'une session
        $_SESSION['id'] = 3;
        $_SESSION['IP'] = '127.0.0.1';
        $_SESSION['level'] = utilisateurObject::levelUser;
        $_SESSION['userName'] = 'username';

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Suppression image possédée par autrui doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), 14, "Suppression image possédée par autrui doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 6, "Suppression image possédée par autrui doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Propriétaire en étant Authentifié
     * @depends testSuppressionImageProprietaireAuthentifie2
     */
    public function testSuppressionImageProprietaireAuthentifie() {
        // Copie du fichier
        rename(_PATH_TESTS_IMAGES_ . 'image_a_supprimer.png', _PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $_GET['id'] = '100000019001334055750.png';

        // Création d'une session
        $_SESSION['id'] = 2;
        $_SESSION['IP'] = '127.0.0.2';
        $_SESSION['level'] = utilisateurObject::levelUser;
        $_SESSION['userName'] = 'admin';

        ob_start();
        require 'delete.php';
        ob_end_clean();
        echo $msgErreur;
        $this->assertEquals($erreur, FALSE, "Suppression image possédée ne doit pas être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), 13, "Suppression image possédée ne doit pas être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 5, "Suppression image possédée ne doit pas être bloqué en BDD");
        $this->assertEquals(file_exists(_PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29'), TRUE, "Suppression image possédée ne doit pas être effacé du HDD car encore en usage");
    }

    /**
     * Suppression d'une image - Anonyme en étant hors délai
     * @depends testSuppressionImageProprietaireAuthentifie
     */
    public function testSuppressionImageAnonymeHorsDelai() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = '146734019451334055750.png';

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Suppression image hors délai doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), 13, "Suppression image hors délai doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 5, "Suppression image hors délai doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Anonyme en étant dans le délai
     * @depends testSuppressionImageAnonymeHorsDelai
     */
    public function testSuppressionImageAnonymeDansDelai() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $_GET['id'] = '147834019001334055750.png';

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Suppression image dans délai ne doit pas être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), 12, "Suppression image dans délai ne doit pas être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), 5, "Suppression image dans délai ne doit pas être bloqué en BDD");
        $this->assertEquals(file_exists(_PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29'), FALSE, "Suppression image dans délai doit être effacé du HDD");
    }

}