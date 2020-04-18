<?php

/*
 * Copyright 2008-2020 Anael MOBILIA
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

class imageUploadAndDeleteTest extends TestCase {

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
        return file_get_contents(_PATH_TESTS_IMAGES_ . $nomFichier);
    }

    /**
     * Ecrit une valeur dans le fichier
     * @param string $nomFichier
     * @param int $valeur
     */
    private static function setNb($nomFichier, $valeur) {
        file_put_contents(_PATH_TESTS_IMAGES_ . $nomFichier, $valeur);
        echo "\r\n$nomFichier -> $valeur";
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
     * Prépare l'environnement pour le test
     */
    private static function prepareTest($chargerConfig = false) {
        if ($chargerConfig) {
            require_once 'config/config.php';
        }
        unset($_POST);
        unset($_FILES);
        unset($_GET);
        unset($_SESSION);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
    }

    /**
     * Test de l'envoi simple : présence BDD et HDD
     */
    public function testEnvoi() {
        self::prepareTest(true);
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_banned.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        //ob_start();
        require 'upload.php';
        //ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Envoi image ne doit pas être bloqué dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Envoi image ne doit pas être bloqué dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Envoi image doit créer image en BDD");
        // GIF : pas de changement en fonction des versions de PHP
        echo $monImage->getMd5();
        $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '9/9f8c72222fd744d4c8abe0e72759d09f'), "Envoi image doit créer image sur HDD");
    }

    /**
     * Test de l'envoi avec miniature : présence BDD et HDD
     * @depends testEnvoi
     */
    public function testEnvoiMiniature() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_pour_miniature.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '50x50';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Envoi image + miniature ne doit pas être bloqué dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Envoi image + miniature ne doit pas être bloqué dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Envoi image + miniature doit créer image en BDD");
        self::setNbPlus(self::fichierMiniature);
        $this->assertEquals(self::countMiniaturesEnBdd(), self::getNb(self::fichierMiniature), "Envoi image + miniature doit créer miniature en BDD");
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            // PHP7.2+
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . 'f/f653f58431521a201fdc23451c9a8af6'), "Envoi image + miniature doit créer image sur HDD");
            $this->assertEquals(TRUE, file_exists(_PATH_MINIATURES_ . '3/3ab7ee8245aa2a58dd42ee3fee5e2d83'), "Envoi image + miniature doit créer miniature sur HDD");
        } else {
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . 'f/f653f58431521a201fdc23451c9a8af6'), "Envoi image + miniature doit créer image sur HDD");
            $this->assertEquals(TRUE, file_exists(_PATH_MINIATURES_ . 'd/da4abc98e046001225da25adbe877669'), "Envoi image + miniature doit créer miniature sur HDD");
        }
    }

    /**
     * Test de l'envoi avec miniature ET rotation : présence BDD et HDD
     * @depends testEnvoiMiniature
     */
    public function testEnvoiMiniatureRotation() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_pour_miniature.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '50x50';
        $_POST['angleRotation'] = 90;

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Envoi image + miniature (rotation) ne doit pas être bloqué dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Envoi image + miniature (rotation) ne doit pas être bloqué dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Envoi image + miniature (rotation) doit créer image en BDD");
        self::setNbPlus(self::fichierMiniature);
        $this->assertEquals(self::countMiniaturesEnBdd(), self::getNb(self::fichierMiniature), "Envoi image + miniature (rotation) doit créer miniature en BDD");
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            // PHP7.2+
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . 'c/c602252985480b868a0852e1a141620e'), "Envoi image + miniature (rotation) doit créer image sur HDD");
            $this->assertEquals(TRUE, file_exists(_PATH_MINIATURES_ . '5/58aa6fc8aa83292b1cef879c66288aa7'), "Envoi image + miniature (rotation) doit créer miniature sur HDD");
        } else {
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . 'd/df6565cd5edc64d855c47bdedf3fae2a'), "Envoi image + miniature (rotation) doit créer image sur HDD");
            $this->assertEquals(TRUE, file_exists(_PATH_MINIATURES_ . '2/2cdc6af129f1eaaca2b1fbc51d2b3694'), "Envoi image + miniature (rotation) doit créer miniature sur HDD");
        }
    }

    /**
     * Test du renvoi d'une image mais avec demande de création d'une miniature
     * @depends testEnvoiMiniatureRotation
     */
    public function testRenvoiImageDemandeMiniature() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_banned.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '50x50';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Renvoi image - dde miniature - ne doit pas être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Renvoi image - dde miniature - ne doit pas être bloquée dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image - dde miniature - ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image - dde miniature (possède) - ne doit rien faire en BDD");
        self::setNbPlus(self::fichierMiniature);
        $this->assertEquals(self::countMiniaturesEnBdd(), self::getNb(self::fichierMiniature), "Renvoi image - dde miniature - doit créer miniature en BDD");
        // GIF : pas de changement en fonction des versions de PHP
        $this->assertEquals(TRUE, file_exists(_PATH_MINIATURES_ . '8/8816df8226a22128a12714606c52bfd3'), "Renvoi image - dde miniature - doit créer miniature sur HDD");
    }

    /**
     * Test du renvoi d'une image avec miniature mais demande demande de création d'une autre miniature
     * @depends testRenvoiImageDemandeMiniature
     */
    public function testRenvoiImageDemandeNouvelleMiniature() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_banned.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '40x40';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Renvoi image - dde NOUVELLE miniature - ne doit pas être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Renvoi image - dde NOUVELLE miniature - ne doit pas être bloquée dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image - dde NOUVELLE miniature - ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image - dde NOUVELLE miniature - ne doit rien faire en BDD");
        self::setNbPlus(self::fichierMiniature);
        $this->assertEquals(self::countMiniaturesEnBdd(), self::getNb(self::fichierMiniature), "Renvoi image - dde NOUVELLE miniature - doit créer miniature en BDD");
        // GIF : pas de changement en fonction des versions de PHP
        $this->assertEquals(TRUE, file_exists(_PATH_MINIATURES_ . '2/289f04a53233d126e177e0a93363dd63'), "Renvoi image - dde NOUVELLE miniature - doit créer miniature sur HDD");
    }

    /**
     * Envoi sans affichage page index.php
     * @depends testRenvoiImageDemandeNouvelleMiniature
     */
    public function testEnvoiBrut() {
        self::prepareTest();
        // Suppression du flag de session
        unset($_SESSION);

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), FALSE, "Non affichage du formulaire d'upload devrait être détecté dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Non affichage du formulaire d'upload devrait être détecté dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Non affichage du formulaire d'upload ne doit pas créer d'image en BDD");
    }

    /**
     * Envoi sans fichier
     * @depends testEnvoiBrut
     */
    public function testEnvoiSansFichier() {
        self::prepareTest();

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), FALSE, "Absence de fichier envoyé devrait être détecté dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Absence de fichier envoyé devrait être détecté dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Absence de fichier envoyé ne doit pas créer d'image en BDD");
    }

    /**
     * Fichier trop lourd
     * @depends testEnvoiSansFichier
     */
    public function testEnvoiGrosFichier() {
        self::prepareTest();
        $_FILES['fichier']['name'] = 'nomFichier';
        $_FILES['fichier']['size'] = _IMAGE_POIDS_MAX_ + 1;

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), FALSE, "Fichier trop gros devrait être détecté dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Fichier trop gros devrait être détecté dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Fichier trop gros ne doit pas créer d'image en BDD");
    }

    /**
     * Type Mime : envoi d'un fichier doc
     * @depends testEnvoiGrosFichier
     */
    public function testTypeMimePasUneImage() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'fichier_doc.doc';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), FALSE, "Type mime : pas une image doit être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Type mime : pas une image doit être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "type mime : pas une image doit être bloquée en BDD");
    }

    /**
     * Type Mime : mauvais type de fichier (DOC).jpg
     * @depends testTypeMimePasUneImage
     */
    public function testTypeMimeMauvaisTypeFichier() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'fichier_doc.jpg';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), FALSE, "Type mime : fausse image doit être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Type mime : fausse image doit être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "type mime : fausse image doit être bloquée en BDD");
    }

    /**
     * Type Mime : mauvaise extension (JPG).png
     * @depends testTypeMimeMauvaisTypeFichier
     */
    public function testTypeMimeMauvaiseExtension() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_jpg.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Type mime : extension incorrecte ne doit pas poser de soucis dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Type mime : extension incorrecte ne doit pas poser de soucis dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Type mime : extension incorrecte ne doit pas être bloquée en BDD");
    }

    /**
     * Dimensions de l'image - Très large
     * @depends testTypeMimeMauvaiseExtension
     */
    public function testTresLarge() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_tres_large.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Image 10000x1 ne doit pas être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Image 10000x1 ne doit pas être bloquée dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Image 10000x1 ne doit pas être bloquée en BDD");
    }

    /**
     * Dimensions de l'image - Très haute
     * @depends testTresLarge
     */
    public function testTresHaute() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_tres_haute.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Image 1x10000 ne doit pas être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Image 1x10000 ne doit pas être bloquée dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Image 1x10000 ne doit pas être bloquée en BDD");
    }

    /**
     * Dimensions de l'image - Trop grande
     * @depends testTresHaute
     */
    public function testTropGrande() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_10000x10000.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), FALSE, "Image 10000x10000 doit être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Image 10000x10000 doit être bloquée dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Image 10000x10000 doit être bloquée en BDD");
    }

    /**
     * Envoi d'une image authentifié
     * @depends testTropGrande
     */
    public function testEnvoiImageAuthentifie() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        /**
         * Authentification
         */
        $unMembre = new utilisateurObject();
        $unMembre->connexion('admin', 'password');

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEquals($unMembre->getId(), 1, "Le membre doit être connecté");
        $this->assertEquals(empty($msgErreur), TRUE, "Envoi image authentifié ne doit pas être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Envoi image authentifié ne doit pas être bloquée dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Envoi image authentifié ne doit pas être bloquée en BDD");
        self::setNbPlus(self::fichierPossede);
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Envoi image authentifié ne doit pas être bloquée en BDD");
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            // PHP7.2+
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60'), "Envoi image authentifié ne doit pas être bloquée sur HDD");
        } else {
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60'), "Envoi image authentifié ne doit pas être bloquée sur HDD");
        }
    }

    /**
     * Renvoi d'une image - Anonyme / Anonyme
     * @depends testEnvoiImageAuthentifie
     */
    public function testRenvoiImageAnonymeAnonyme() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_tres_haute.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Renvoi image ne doit pas être bloquée dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image ne doit pas modifier la BDD");
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            // PHP7.2+
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '1/1b1a8d9abaf9027d8ff2de4081579538'), "Renvoi image ne doit pas être bloquée sur HDD");
        } else {
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '1/1b1a8d9abaf9027d8ff2de4081579538'), "Renvoi image ne doit pas être bloquée sur HDD");
        }
    }

    /**
     * Renvoi d'une image - Anonyme / Authentifié
     * @depends testRenvoiImageAnonymeAnonyme
     */
    public function testRenvoiImageAnonymeAuthentifie() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_tres_haute.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        /**
         * Authentification
         */
        $unMembre = new utilisateurObject();
        $unMembre->connexion('admin', 'password');

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEquals($unMembre->getId(), 1, "Le membre doit être connecté");
        $this->assertEquals(empty($msgErreur), TRUE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Renvoi image ne doit pas être bloquée dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image ne doit pas être bloquée en BDD");
        self::setNbPlus(self::fichierPossede);
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image ne doit pas modifier la BDD");
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            // PHP7.2+
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '1/1b1a8d9abaf9027d8ff2de4081579538'), "Renvoi image ne doit pas être bloquée sur HDD");
        } else {
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '1/1b1a8d9abaf9027d8ff2de4081579538'), "Renvoi image ne doit pas être bloquée sur HDD");
        }
    }

    /**
     * Renvoi d'une image - Authentifié / Anonyme
     * @depends testRenvoiImageAnonymeAuthentifie
     */
    public function testRenvoiImageAuthentifieAnonyme() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Renvoi image ne doit pas être bloquée dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image ne doit pas être bloquée en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image doit être bloquée en BDD");
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            // PHP7.2+
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60'), "Envoi image authentifié ne doit pas être bloquée sur HDD");
        } else {
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60'), "Envoi image authentifié ne doit pas être bloquée sur HDD");
        }
    }

    /**
     * Renvoi d'une image - Authentifié / Authentifié
     * @depends testRenvoiImageAuthentifieAnonyme
     */
    public function testRenvoiImageAuthentifieAuthentifie() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        /**
         * Authentification
         */
        $unMembre = new utilisateurObject();
        $unMembre->connexion('admin', 'password');

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEquals($unMembre->getId(), 1, "Le membre doit être connecté");
        $this->assertEquals(empty($msgErreur), TRUE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Renvoi image ne doit pas être bloquée dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image ne doit pas être bloquée en BDD");
        self::setNbPlus(self::fichierPossede);
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image ne doit pas être bloquée en BDD");
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            // PHP7.2+
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60'), "Envoi image authentifié ne doit pas être bloquée sur HDD");
        } else {
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60'), "Envoi image authentifié ne doit pas être bloquée sur HDD");
        }
    }

    /**
     * Renvoi d'une image - Authentifié / Authentifié Autrement
     * @depends testRenvoiImageAuthentifieAuthentifie
     */
    public function testRenvoiImageAuthentifieAuthentifie2() {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        /**
         * Authentification
         */
        $unMembre = new utilisateurObject();
        $unMembre->connexion('user', 'password');

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEquals($unMembre->getId(), 2, "Le membre doit être connecté");
        $this->assertEquals(empty($msgErreur), TRUE, "Renvoi image ne doit pas être bloquée dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Renvoi image ne doit pas être bloquée dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Renvoi image ne doit pas être bloquée en BDD");
        self::setNbPlus(self::fichierPossede);
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Renvoi image ne doit pas être bloquée en BDD");
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            // PHP7.2+
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60'), "Envoi image authentifié ne doit pas être bloquée sur HDD");
        } else {
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60'), "Envoi image authentifié ne doit pas être bloquée sur HDD");
        }
    }

    /**
     * Suppression d'une image inexistante
     * @depends testRenvoiImageAuthentifieAuthentifie2
     */
    public function testSuppressionImageInexistante() {
        self::prepareTest();
        $_GET['id'] = 'fichierInexistant';
        $_GET['type'] = ressourceObject::typeImage;

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), FALSE, "Suppression image inexistante doit être bloqué dans delete.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Suppression image inexistante doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image inexistante doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image inexistante doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Propriétaire en étant Anonyme
     * @depends testSuppressionImageInexistante
     */
    public function testSuppressionImageProprietaireAnonyme() {
        self::prepareTest();
        $_GET['id'] = '_image_404.png';
        $_GET['type'] = ressourceObject::typeImage;

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), FALSE, "Suppression image possédée par autrui doit être bloqué dans delete.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Suppression image possédée par autrui doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image possédée par autrui doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image possédée par autrui doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Propriétaire en étant Authentifié mais Autre
     * @depends testSuppressionImageProprietaireAnonyme
     */
    public function testSuppressionImageProprietaireAuthentifie2() {
        self::prepareTest();
        $_GET['id'] = '_image_404.png';
        $_GET['type'] = ressourceObject::typeImage;

        /**
         * Authentification
         */
        $unMembre = new utilisateurObject();
        $unMembre->connexion('user', 'password');

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEquals($unMembre->getId(), 2, "Le membre doit être connecté");
        $this->assertEquals(empty($msgErreur), FALSE, "Suppression image possédée par autrui doit être bloqué dans delete.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Suppression image possédée par autrui doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image possédée par autrui doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image possédée par autrui doit être bloqué en BDD");
    }

    /**
     * Vérification du bon fonctionnement du mécanisme de détection des doublons en BDD
     * @depends testSuppressionImageProprietaireAuthentifie2
     */
    public function testVerificationCalculsDoublonsBDD() {
        self::prepareTest();

        $uneImageDoublon = new imageObject("100000019001334055750.png");
        $uneAutreImage = new imageObject("146734019451334055750.png");
        $uneImageInexistante = new imageObject();

        $this->assertEquals($uneImageDoublon->getNbDoublons(), 2, "L'image est présente en id 11 & 12");
        $this->assertEquals($uneAutreImage->getNbDoublons(), 1, "L'image est présente en id 13");
        $this->assertEquals($uneImageInexistante->getNbDoublons(), 0, "L'image n'existe pas...");
    }

    /**
     * Suppression d'une image - Propriétaire en étant Authentifié
     * @depends testVerificationCalculsDoublonsBDD
     */
    public function testSuppressionImageProprietaireAuthentifie() {
        self::prepareTest();
        // Copie du fichier
        copy(_PATH_TESTS_IMAGES_ . 'image_a_supprimer.png', _PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $_GET['id'] = '100000019001334055750.png';
        $_GET['type'] = ressourceObject::typeImage;

        /**
         * Authentification
         */
        $unMembre = new utilisateurObject();
        $unMembre->connexion('user', 'password');

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEquals($unMembre->getId(), 2, "Le membre doit être connecté");
        $this->assertEquals(empty($msgErreur), TRUE, "Suppression image possédée ne doit pas être bloqué dans delete.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Suppression image possédée ne doit pas être bloqué dans delete.php");
        self::setNbMoins(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image possédée ne doit pas être bloqué en BDD");
        self::setNbMoins(self::fichierPossede);
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image possédée ne doit pas être bloqué en BDD");
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            // PHP7.2+
            $this->assertEquals(file_exists(_PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29'), TRUE, "Suppression image possédée ne doit pas être effacée du HDD car encore en usage");
        } else {
            $this->assertEquals(file_exists(_PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29'), TRUE, "Suppression image possédée ne doit pas être effacée du HDD car encore en usage");
        }
    }

    /**
     * Suppression d'une image - Anonyme en étant hors délai
     * @depends testSuppressionImageProprietaireAuthentifie
     */
    public function testSuppressionImageAnonymeHorsDelai() {
        self::prepareTest();
        $_GET['id'] = '146734019451334055750.png';
        $_GET['type'] = ressourceObject::typeImage;

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), FALSE, "Suppression image hors délai doit être bloqué dans delete.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Suppression image hors délai doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image hors délai doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image hors délai doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Anonyme en étant dans délai mais pas la bonne IP
     * @depends testSuppressionImageProprietaireAuthentifie
     */
    public function testSuppressionImageAnonymeDansDelaiMauvaiseIP() {
        self::prepareTest();
        // Surcharge de l'adresse IP par défaut
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $_GET['id'] = '147834019001334055750.png';
        $_GET['type'] = ressourceObject::typeImage;

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), FALSE, "Suppression image dans délai par autre IP doit être bloqué dans delete.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Suppression image dans délai par autre IP doit être bloqué dans delete.php");
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Suppression image dans délai par autre IP doit être bloqué en BDD");
        $this->assertEquals(self::countImagesPossedeesEnBdd(), self::getNb(self::fichierPossede), "Suppression image dans délai par autre IP doit être bloqué en BDD");
    }

    /**
     * Suppression d'une image - Anonyme en étant dans le délai
     * @depends testSuppressionImageAnonymeDansDelaiMauvaiseIP
     */
    public function testSuppressionImageAnonymeDansDelai() {
        self::prepareTest();
        // Surcharge de l'adresse IP par défaut
        $_SERVER['REMOTE_ADDR'] = '127.0.0.10';
        $_GET['id'] = '147834019001334055750.png';
        $_GET['type'] = ressourceObject::typeImage;

        ob_start();
        require 'delete.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Suppression image dans délai ne doit pas être bloqué dans delete.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Suppression image dans délai ne doit pas être bloqué dans delete.php");
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
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_paysage_800x600.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['redimImage'] = '400x200';

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals(empty($msgErreur), TRUE, "Envoi image avec redim ne doit pas être bloqué dans upload.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Envoi image avec redim ne doit pas être bloqué dans upload.php");
        self::setNbPlus(self::fichierImage);
        $this->assertEquals(self::countImagesEnBdd(), self::getNb(self::fichierImage), "Envoi image avec redim doit créer d'image en BDD");
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            // PHP7.2+
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '0/02c7908b07fbbe94a7363bf76fc36e7f'), "Envoi image avec redim doit créer image redim sur HDD");
            $this->assertEquals(FALSE, file_exists(_PATH_IMAGES_ . '4/4db0b6f10d49fb1a8c2e8b8ff47cf3f6'), "Envoi image avec redim ne doit pas créer d'image originale sur HDD");
        } else {
            $this->assertEquals(TRUE, file_exists(_PATH_IMAGES_ . '4/4ab29350fa47c1009fe56683aeef8a5c'), "Envoi image avec redim doit créer image redim sur HDD");
            $this->assertEquals(FALSE, file_exists(_PATH_IMAGES_ . '4/4db0b6f10d49fb1a8c2e8b8ff47cf3f6'), "Envoi image avec redim ne doit pas créer d'image originale sur HDD");
        }
    }

    /**
     * Test de la suppression d'une image avec plusieurs miniatures
     * @depends testEnvoiRedim
     */
    public function testSuppressionImagePlusieursMiniatures() {
        self::prepareTest();
        // Copie des fichiers
        rename(_PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple.png', _PATH_IMAGES_ . 'a/aec65c6b4469bb7267d2d55af5fbd87b');
        rename(_PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple-100x100.png', _PATH_MINIATURES_ . '0/031328c1a7ffe7eed0a2cab4eca05a63');
        rename(_PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple-200x200.png', _PATH_MINIATURES_ . '2/278a70a02e036cc85e0d7e605fdc517f');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = '14777777.png';
        $_GET['type'] = ressourceObject::typeImage;

        /**
         * Authentification
         */
        $unMembre = new utilisateurObject();
        $unMembre->connexion('admin', 'password');

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEquals($unMembre->getId(), 1, "Le membre doit être connecté");
        $this->assertEquals(empty($msgErreur), TRUE, "Suppression image ne doit pas être bloqué dans delete.php");
        $this->assertEquals(empty($msgWarning), TRUE, "Suppression image ne doit pas être bloqué dans delete.php");
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
