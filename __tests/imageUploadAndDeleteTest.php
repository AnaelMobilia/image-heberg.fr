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
    // Fichiers pour le nombre d'images / possessions attendues
    const fichierImage = '../_nbImages';
    const fichierMiniature = '../_nbThumbnails';
    const fichierPossede = '../_nbPossede';

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
     * Nombre de miniatures en BDD
     * @return int
     */
    private static function countMiniaturesEnBdd() {
        $maReq = maBDD::getInstance()->query("SELECT COUNT(*) AS nb FROM thumbnails");
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
     * Nombre d'éléments présents dans le fichier
     * @param string $nomFichier nom du fichier
     * @return int nb éléments
     */
    private static function getNb($nomFichier) {
        $f = fopen(_PATH_TESTS_IMAGES_ . $nomFichier, 'r');
        $val = fread($f, 10);
        fclose($f);

        return $val;
    }

    /**
     * Ecrit une valeur dans le fichier
     * @param string $nomFichier
     * @param int $valeur
     */
    private static function setNb($nomFichier, $valeur) {
        $f = fopen(_PATH_TESTS_IMAGES_ . $nomFichier, 'w');
        fwrite($f, $valeur);
        fclose($f);
    }

    /**
     * $val--
     * @param string $nomFichier
     */
    private static function setNbMoins($nomFichier) {
        $val = self::getNb($nomFichier);
        self::setNb($nomFichier, --$val);
    }

    /**
     * $val++
     * @param string $nomFichier
     */
    private static function setNbPlus($nomFichier) {
        $val = self::getNb($nomFichier);
        self::setNb($nomFichier, ++$val);
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
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Envoi image doit créer d'image en BDD");
        $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '6/6a9dd81ae12c79d953031bc54c07f900'), "Envoi image doit créer d'image sur HDD");
    }

    /**
     * Test de l'envoi avec miniature : présence BDD et HDD
     * @depends testEnvoi
     */
    public function testEnvoiMiniature() {
        require_once 'config/configV2.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_pour_miniature.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '50x50';

        // Gestion des différents tests
        unset($_SESSION['id']);

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Envoi image + miniature ne doit pas être bloqué dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Envoi image + miniature doit créer image en BDD");
        self::setNbPlus(self::fichierMiniature);
        $this->assertEquals(self::countMiniaturesEnBdd(), self::getNb(self::fichierMiniature), "Envoi image + miniature doit créer miniature en BDD");
        $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . 'f/f653f58431521a201fdc23451c9a8af6'), "Envoi image + miniature doit créer image sur HDD");
        $this->assertEquals(TRUE, file_exists(_PATH_MINIATURES_ . 'e/ee5acdecd9894734e685b019662e6959'), "Envoi image + miniature doit créer miniature sur HDD");
    }

    /**
     * Test de l'envoi avec miniature ET rotation : présence BDD et HDD
     * @depends testEnvoiMiniature
     */
    public function testEnvoiMiniatureRotation() {
        require_once 'config/configV2.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_pour_miniature2.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '50x50';
        $_POST['angleRotation'] = 90;

        // Gestion des différents tests
        unset($_SESSION['id']);

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Envoi image + miniature (rotation) ne doit pas être bloqué dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Envoi image + miniature (rotation) doit créer image en BDD");
        self::setNbPlus(self::fichierMiniature);
        $this->assertEquals(self::countMiniaturesEnBdd(), self::getNb(self::fichierMiniature), "Envoi image + miniature (rotation) doit créer miniature en BDD");
        $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '4/4a3da533b304629c3ef35ece7fb01308'), "Envoi image + miniature (rotation) doit créer image sur HDD");
        $this->assertEquals(TRUE, file_exists(_PATH_MINIATURES_ . '8/8c3b9bd4f7339b9ed4e1aee52cf8b55f'), "Envoi image + miniature (rotation) doit créer miniature sur HDD");
    }

    /**
     * Test du renvoi d'une image mais avec demande de création d'une miniature
     * @depends testEnvoiMiniatureRotation
     */
    public function testRenvoiImageDemandeMiniature() {
        require_once 'config/configV2.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_banned2.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '50x50';

        // Gestion des différents tests
        unset($_SESSION['id']);

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image - dde miniature - ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image - dde miniature - doit être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image - dde miniature - doit être bloquée en BDD");
        self::setNbPlus(self::fichierMiniature);
        $this->assertEquals(self::countMiniaturesEnBdd(), self::getNb(self::fichierMiniature), "Renvoi image - dde miniature - doit créer miniature en BDD");
        var_dump($maMiniature->getPathMd5());
        $this->assertEquals(TRUE, file_exists(_PATH_MINIATURES_ . '1/18d267ff765248963656eb25ea1f7f29'), "Renvoi image - dde miniature - doit créer miniature sur HDD");
    }

    /**
     * Test du renvoi d'une image avec miniature mais demande demande de création d'une autre miniature
     * @depends testRenvoiImageDemandeMiniature
     */
    public function testRenvoiImageDemandeNouvelleMiniature() {
        require_once 'config/configV2.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_banned3.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '40x40';

        // Gestion des différents tests
        unset($_SESSION['id']);

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Renvoi image - dde NOUVELLE miniature - ne doit pas être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image - dde NOUVELLE miniature - doit être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image - dde NOUVELLE miniature - doit être bloquée en BDD");
        self::setNbPlus(self::fichierMiniature);
        $this->assertEquals(self::countMiniaturesEnBdd(), self::getNb(self::fichierMiniature), "Renvoi image - dde NOUVELLE miniature - doit créer miniature en BDD");
        var_dump($maMiniature->getPathMd5());
        $this->assertEquals(TRUE, file_exists(_PATH_MINIATURES_ . '9/9b2fb055aec30c31adfe12d208e9facf'), "Renvoi image - dde NOUVELLE miniature - doit créer miniature sur HDD");
    }

    /**
     * Envoi sans affichage page index.php
     * @depends testRenvoiImageDemandeNouvelleMiniature
     */
    public function testEnvoiBrut() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Non affichage du formulaire d'upload devrait être détecté dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Non affichage du formulaire d'upload ne doit pas créer d'image en BDD");
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
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Absence de fichier envoyé ne doit pas créer d'image en BDD");
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
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Fichier trop gros ne doit pas créer d'image en BDD");
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
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "type mime : pas une image doit être bloquée en BDD");
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
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "type mime : fausse image doit être bloquée en BDD");
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
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Type mime : extension incorrecte ne doit pas être bloquée en BDD");
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
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Image 10000x1 ne doit pas être bloquée en BDD");
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
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Image 1x10000 ne doit pas être bloquée en BDD");
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
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Image 10000x10000 doit être bloquée en BDD");
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
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Envoi image authentifié ne doit pas être bloquée en BDD");
        self::setNbPlus(self::fichierPossede);
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Envoi image authentifié ne doit pas être bloquée en BDD");
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
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image doit être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image doit être bloquée en BDD");
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
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image ne doit pas être bloquée en BDD");
        self::setNbPlus(self::fichierPossede);
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image ne doit pas être bloquée en BDD");
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
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image doit être bloquée en BDD");
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
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image doit être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image doit être bloquée en BDD");
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
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image ne doit pas être bloquée en BDD");
        self::setNbPlus(self::fichierPossede);
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image ne doit pas être bloquée en BDD");
    }

    /**
     * Suppression d'une image inexistante
     * @depends testRenvoiImageAuthentifieAuthentifie2
     */
    public function testSuppressionImageInexistante() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = 'fichierInexistant';
        $_GET['type'] = ressourceObject::typeImage;

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Suppression image inexistante doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image inexistante doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image inexistante doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Propriétaire en étant Anonyme
     * @depends testSuppressionImageInexistante
     */
    public function testSuppressionImageProprietaireAnonyme() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = '_image_404.png';
        $_GET['type'] = ressourceObject::typeImage;

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Suppression image possédée par autrui doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image possédée par autrui doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image possédée par autrui doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Propriétaire en étant Authentifié mais Autre
     * @depends testSuppressionImageProprietaireAnonyme
     */
    public function testSuppressionImageProprietaireAuthentifie2() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = '_image_404.png';
        $_GET['type'] = ressourceObject::typeImage;

        // Création d'une session
        $_SESSION['id'] = 3;
        $_SESSION['IP'] = '127.0.0.1';
        $_SESSION['level'] = utilisateurObject::levelUser;
        $_SESSION['userName'] = 'username';

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Suppression image possédée par autrui doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image possédée par autrui doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image possédée par autrui doit être bloqué en BDD");
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
        $_GET['type'] = ressourceObject::typeImage;

        // Création d'une session
        $_SESSION['id'] = 2;
        $_SESSION['IP'] = '127.0.0.2';
        $_SESSION['level'] = utilisateurObject::levelUser;
        $_SESSION['userName'] = 'username';

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Suppression image possédée ne doit pas être bloqué dans delete.php");
        self::setNbMoins(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image possédée ne doit pas être bloqué en BDD");
        self::setNbMoins(self::fichierPossede);
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image possédée ne doit pas être bloqué en BDD");
        $this->assertEquals(file_exists(_PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29'), TRUE, "Suppression image possédée ne doit pas être effacé du HDD car encore en usage");
    }

    /**
     * Suppression d'une image - Anonyme en étant hors délai
     * @depends testSuppressionImageProprietaireAuthentifie
     */
    public function testSuppressionImageAnonymeHorsDelai() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = '146734019451334055750.png';
        $_GET['type'] = ressourceObject::typeImage;

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Suppression image hors délai doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image hors délai doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image hors délai doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Anonyme en étant dans délai mais pas la bonne IP
     * @depends testSuppressionImageProprietaireAuthentifie
     */
    public function testSuppressionImageAnonymeDansDelaiMauvaiseIP() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $_GET['id'] = '147834019001334055750.png';
        $_GET['type'] = ressourceObject::typeImage;

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Suppression image dans délai par autre IP doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image dans délai par autre IP doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image dans délai par autre IP doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Anonyme en étant dans le délai
     * @depends testSuppressionImageAnonymeDansDelaiMauvaiseIP
     */
    public function testSuppressionImageAnonymeDansDelai() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.10';
        $_GET['id'] = '147834019001334055750.png';
        $_GET['type'] = ressourceObject::typeImage;

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Suppression image dans délai ne doit pas être bloqué dans delete.php");
        self::setNbMoins(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image dans délai ne doit pas être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image dans délai ne doit pas être bloqué en BDD");
        $this->assertEquals(file_exists(_PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29'), FALSE, "Suppression image dans délai doit être effacé du HDD");
    }

    /**
     * Test de l'envoi simple avec redimensionnement : présence BDD et HDD
     * @depends testSuppressionImageAnonymeDansDelai
     */
    public function testEnvoiRedim() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['_upload'] = 1;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_paysage_800x600.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['redimImage'] = '400x200';

        // Gestion des différents tests
        unset($_SESSION['id']);

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Envoi image avec redim ne doit pas être bloqué dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Envoi image avec redim doit créer d'image en BDD");
        $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '4/43b604c3a5c18a161bc3a01bdb58ebf7'), "Envoi image avec redim doit créer image redim sur HDD");
        $this->assertEquals(FALSE, file_exists(_PATH_IMAGES_ . '4/4db0b6f10d49fb1a8c2e8b8ff47cf3f6'), "Envoi image avec redim ne doit pas créer d'image originale sur HDD");
    }

    /**
     * Test de la suppression d'une image avec plusieurs miniatures
     * @depends testEnvoiRedim
     */
    public function testSuppressionImagePlusieursMiniatures() {
        // Copie des fichiers
        rename(_PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple.png', _PATH_IMAGES_ . 'a/aec65c6b4469bb7267d2d55af5fbd87b');
        rename(_PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple-100x100.png', _PATH_MINIATURES_ . '0/031328c1a7ffe7eed0a2cab4eca05a63');
        rename(_PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple-200x200.png', _PATH_MINIATURES_ . '2/278a70a02e036cc85e0d7e605fdc517f');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = '14777777.png';
        $_GET['type'] = ressourceObject::typeImage;

        // Création d'une session
        $_SESSION['id'] = 1;
        $_SESSION['IP'] = '127.0.0.1';
        $_SESSION['level'] = utilisateurObject::levelUser;
        $_SESSION['userName'] = 'admin';

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals($erreur, FALSE, "Suppression image ne doit pas être bloqué dans delete.php");
        self::setNbMoins(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image ne doit pas être bloqué en BDD");
        self::setNbMoins(self::fichierPossede);
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression possession ne doit pas être bloqué en BDD");
        $this->assertEquals(file_exists(_PATH_IMAGES_ . 'a/aec65c6b4469bb7267d2d55af5fbd87b'), FALSE, "Suppression image doit être effacé du HDD");
        self::setNbMoins(self::fichierMiniature);
        self::setNbMoins(self::fichierMiniature);
        $this->assertEquals(self::countMiniaturesEnBdd(), self::getNb(self::fichierMiniature), "Suppression miniatureS ne doit pas être bloqué en BDD");
        $this->assertEquals(file_exists(_PATH_MINIATURES_ . '0/031328c1a7ffe7eed0a2cab4eca05a63'), FALSE, "Suppression image doit effacer toutes les miniatures du HDD");
        $this->assertEquals(file_exists(_PATH_MINIATURES_ . '2/278a70a02e036cc85e0d7e605fdc517f'), FALSE, "Suppression image doit effacer toutes les miniatures du HDD");
    }

}