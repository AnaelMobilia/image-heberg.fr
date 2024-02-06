<?php

/*
 * Copyright 2008-2024 Anael MOBILIA
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

use ImageHeberg\ImageObject;
use ImageHeberg\MaBDD;
use ImageHeberg\HelperImage;
use ImageHeberg\RessourceObject;
use ImageHeberg\UtilisateurObject;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class ImageUploadAndDeleteTest extends TestCase
{
    /**
     * Le MD5 est calculé sur le fichier original
     * Les miniatures travaillent sur le fichier uploadé => faire une copie du fichier original
     */
    // Fichiers pour le nombre d'images / possessions attendues
    private const string FICHIER_IMAGE = '../_nbImages';
    private const string FICHIER_MINIATURE = '../_nbThumbnails';
    private const string FICHIER_POSSEDE = '../_nbPossede';

    /**
     * Nombre d'images en BDD
     * @return int
     */
    private static function countImagesEnBdd(): int
    {
        $maReq = MaBDD::getInstance()->query('SELECT COUNT(*) AS nb FROM images');
        return $maReq->fetch()->nb;
    }

    /**
     * Nombre de miniatures en BDD
     * @return int
     */
    private static function countMiniaturesEnBdd(): int
    {
        $maReq = MaBDD::getInstance()->query('SELECT COUNT(*) AS nb FROM thumbnails');
        return $maReq->fetch()->nb;
    }

    /**
     * Nombre d'images POSSEDEES en BDD
     * @return int
     */
    private static function countImagesPossedeesEnBdd(): int
    {
        $maReq = MaBDD::getInstance()->query('SELECT COUNT(*) AS nb FROM possede');
        return $maReq->fetch()->nb;
    }

    /**
     * Nombre d'éléments présents dans le fichier
     * @param string $nomFichier nom du fichier
     * @return int nb éléments
     */
    private static function getNb(string $nomFichier): int
    {
        return (int)file_get_contents(_PATH_TESTS_IMAGES_ . $nomFichier);
    }

    /**
     * Ecrit une valeur dans le fichier
     * @param string $nomFichier
     * @param int $valeur
     */
    private static function setNb(string $nomFichier, int $valeur): void
    {
        file_put_contents(_PATH_TESTS_IMAGES_ . $nomFichier, $valeur);
        echo PHP_EOL . $nomFichier . ' -> ' . $valeur . PHP_EOL;
    }

    /**
     * $val--
     * @param string $nomFichier
     */
    private static function setNbMoins(string $nomFichier): void
    {
        $val = self::getNb($nomFichier);
        self::setNb($nomFichier, --$val);
    }

    /**
     * $val++
     * @param string $nomFichier
     */
    private static function setNbPlus(string $nomFichier): void
    {
        $val = self::getNb($nomFichier);
        self::setNb($nomFichier, ++$val);
    }

    /**
     * Prépare l'environnement pour le test
     */
    private static function prepareTest($chargerConfig = false): void
    {
        if ($chargerConfig) {
            require_once 'config/config.php';
        }
        unset($_POST, $_FILES, $_GET, $_SESSION);
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
    }

    /**
     * Test de l'envoi simple : présence BDD et HDD
     */
    public function testEnvoi(): void
    {
        self::prepareTest(true);
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_banned.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Envoi image ne doit pas être bloqué dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Envoi image ne doit pas être bloqué dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Envoi image doit créer image en BDD'
        );
        // GIF : pas de changement en fonction des versions de PHP
        $this->assertFileExists(
            _PATH_IMAGES_ . '3/3487a240d00aa62f2abcfe43ba84a85c',
            'Envoi image doit créer image sur HDD'
        );
    }

    /**
     * Test de l'envoi avec miniature : présence BDD et HDD
     */
    #[Depends('testEnvoi')]
    public function testEnvoiMiniature(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_pour_miniature2.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '50x50';

        ob_start();
        require 'upload.php';
        ob_end_clean();

        /* @var $monImage ImageObject */
        echo 'MD5 : ' . $monImage->getMd5();

        $this->assertEmpty(
            $msgErreur,
            'Envoi image + miniature ne doit pas être bloqué dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Envoi image + miniature ne doit pas être bloqué dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Envoi image + miniature doit créer image en BDD'
        );
        self::setNbPlus(self::FICHIER_MINIATURE);
        $this->assertEquals(
            self::countMiniaturesEnBdd(),
            self::getNb(self::FICHIER_MINIATURE),
            'Envoi image + miniature doit créer miniature en BDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . 'f/f653f58431521a201fdc23451c9a8af6',
            'Envoi image + miniature doit créer image sur HDD'
        );
        $this->assertFileExists(
            _PATH_MINIATURES_ . '3/3ab7ee8245aa2a58dd42ee3fee5e2d83',
            'Envoi image + miniature doit créer miniature sur HDD'
        );
    }

    /**
     * Test de l'envoi avec miniature ET rotation : présence BDD et HDD
     */
    #[Depends('testEnvoiMiniature')]
    public function testEnvoiMiniatureRotation(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_pour_miniature3.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '50x50';
        $_POST['angleRotation'] = 90;

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Envoi image + miniature (rotation) ne doit pas être bloqué dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Envoi image + miniature (rotation) ne doit pas être bloqué dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Envoi image + miniature (rotation) doit créer image en BDD'
        );
        self::setNbPlus(self::FICHIER_MINIATURE);
        $this->assertEquals(
            self::countMiniaturesEnBdd(),
            self::getNb(self::FICHIER_MINIATURE),
            'Envoi image + miniature (rotation) doit créer miniature en BDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . 'f/f653f58431521a201fdc23451c9a8af6',
            'Envoi image + miniature doit créer image sur HDD'
        );
        $this->assertFileExists(
            _PATH_MINIATURES_ . '5/58aa6fc8aa83292b1cef879c66288aa7',
            'Envoi image + miniature (rotation) doit créer miniature sur HDD'
        );
    }

    /**
     * Test du renvoi d'une image mais avec demande de création d'une miniature
     */
    #[Depends('testEnvoiMiniatureRotation')]
    public function testRenvoiImageDemandeMiniature(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_banned2.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '50x50';

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Renvoi image - dde miniature - ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Renvoi image - dde miniature - ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Renvoi image - dde miniature - ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Renvoi image - dde miniature (possède) - ne doit rien faire en BDD'
        );
        self::setNbPlus(self::FICHIER_MINIATURE);
        $this->assertEquals(
            self::countMiniaturesEnBdd(),
            self::getNb(self::FICHIER_MINIATURE),
            'Renvoi image - dde miniature - doit créer miniature en BDD'
        );
        // GIF : pas de changement en fonction des versions de PHP
        $this->assertFileExists(
            _PATH_MINIATURES_ . '8/8816df8226a22128a12714606c52bfd3',
            'Renvoi image - dde miniature - doit créer miniature sur HDD'
        );
    }

    /**
     * Test du renvoi d'une image avec miniature mais demande demande de création d'une autre miniature
     */
    #[Depends('testRenvoiImageDemandeMiniature')]
    public function testRenvoiImageDemandeNouvelleMiniature(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_banned3.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['dimMiniature'] = '40x40';

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Renvoi image - dde NOUVELLE miniature - ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Renvoi image - dde NOUVELLE miniature - ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Renvoi image - dde NOUVELLE miniature - ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Renvoi image - dde NOUVELLE miniature - ne doit rien faire en BDD'
        );
        self::setNbPlus(self::FICHIER_MINIATURE);
        $this->assertEquals(
            self::countMiniaturesEnBdd(),
            self::getNb(self::FICHIER_MINIATURE),
            'Renvoi image - dde NOUVELLE miniature - doit créer miniature en BDD'
        );
        // GIF : pas de changement en fonction des versions de PHP
        $this->assertFileExists(
            _PATH_MINIATURES_ . '2/289f04a53233d126e177e0a93363dd63',
            'Renvoi image - dde NOUVELLE miniature - doit créer miniature sur HDD'
        );
    }

    /**
     * Envoi sans affichage page index.php
     */
    #[Depends('testRenvoiImageDemandeNouvelleMiniature')]
    public function testEnvoiBrut(): void
    {
        self::prepareTest();
        // Suppression du flag de session
        unset($_SESSION);

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Non affichage du formulaire d\'upload devrait être détecté dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Non affichage du formulaire d\'upload devrait être détecté dans upload.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Non affichage du formulaire d\'upload ne doit pas créer d\'image en BDD'
        );
    }

    /**
     * Envoi sans fichier
     */
    #[Depends('testEnvoiBrut')]
    public function testEnvoiSansFichier(): void
    {
        self::prepareTest();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Absence de fichier envoyé devrait être détecté dans upload.php'
        );
        $this->assertEmpty(
            $msgWarning,
            'Absence de fichier envoyé devrait être détecté dans upload.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Absence de fichier envoyé ne doit pas créer d\'image en BDD'
        );
    }

    /**
     * Fichier trop lourd
     */
    #[Depends('testEnvoiSansFichier')]
    public function testEnvoiGrosFichier(): void
    {
        self::prepareTest();
        $_FILES['fichier']['name'] = 'nomFichier';
        $_FILES['fichier']['size'] = _IMAGE_POIDS_MAX_ + 1;

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Fichier trop gros devrait être détecté dans upload.php'
        );
        $this->assertEmpty(
            $msgWarning,
            'Fichier trop gros devrait être détecté dans upload.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Fichier trop gros ne doit pas créer d\'image en BDD'
        );
    }

    /**
     * Type Mime : envoi d'un fichier doc
     */
    #[Depends('testEnvoiGrosFichier')]
    public function testTypeMimePasUneImage(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'fichier_doc.doc';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Type mime : pas une image doit être bloquée dans upload.php'
        );
        $this->assertEmpty(
            $msgWarning,
            'Type mime : pas une image doit être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'type mime : pas une image doit être bloquée en BDD'
        );
    }

    /**
     * Type Mime : mauvais type de fichier (DOC).jpg
     */
    #[Depends('testTypeMimePasUneImage')]
    public function testTypeMimeMauvaisTypeFichier(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'fichier_doc.jpg';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Type mime : fausse image doit être bloquée dans upload.php'
        );
        $this->assertEmpty(
            $msgWarning,
            'Type mime : fausse image doit être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'type mime : fausse image doit être bloquée en BDD'
        );
    }

    /**
     * Type Mime : mauvaise extension (JPG).png
     */
    #[Depends('testTypeMimeMauvaisTypeFichier')]
    public function testTypeMimeMauvaiseExtension(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_jpg.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Type mime : extension incorrecte ne doit pas poser de soucis dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Type mime : extension incorrecte ne doit pas poser de soucis dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Type mime : extension incorrecte ne doit pas être bloquée en BDD'
        );
    }

    /**
     * Dimensions de l'image - Très large
     */
    #[Depends('testTypeMimeMauvaiseExtension')]
    public function testTresLarge(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_tres_large.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Image 10000x1 ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Image 10000x1 ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Image 10000x1 ne doit pas être bloquée en BDD'
        );
    }

    /**
     * Dimensions de l'image - Très haute
     */
    #[Depends('testTresLarge')]
    public function testTresHaute(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_tres_haute.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Image 1x10000 ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Image 1x10000 ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Image 1x10000 ne doit pas être bloquée en BDD'
        );
    }

    /**
     * Dimensions de l'image - Trop grande
     */
    #[Depends('testTresHaute')]
    public function testTropGrande(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_10000x10000.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Image 10000x10000 doit être bloquée dans upload.php'
        );
        $this->assertEmpty(
            $msgWarning,
            'Image 10000x10000 doit être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Image 10000x10000 doit être bloquée en BDD'
        );
    }

    /**
     * Envoi d'une image authentifié
     */
    #[Depends('testTropGrande')]
    public function testEnvoiImageAuthentifie(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('admin', 'password');

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEquals(
            1,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEmpty(
            $msgErreur,
            'Envoi image authentifié ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Envoi image authentifié ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Envoi image authentifié ne doit pas être bloquée en BDD'
        );
        self::setNbPlus(self::FICHIER_POSSEDE);
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Envoi image authentifié ne doit pas être bloquée en BDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60',
            'Envoi image authentifié ne doit pas être bloquée sur HDD'
        );
    }

    /**
     * Renvoi d'une image - Anonyme / Anonyme
     */
    #[Depends('testEnvoiImageAuthentifie')]
    public function testRenvoiImageAnonymeAnonyme(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_tres_haute.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Renvoi image ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Renvoi image ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Renvoi image ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Renvoi image ne doit pas modifier la BDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . '1/1b1a8d9abaf9027d8ff2de4081579538',
            'Renvoi image ne doit pas être bloquée sur HDD'
        );
    }

    /**
     * Renvoi d'une image - Anonyme / Authentifié
     */
    #[Depends('testRenvoiImageAnonymeAnonyme')]
    public function testRenvoiImageAnonymeAuthentifie(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_tres_haute.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('admin', 'password');

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEquals(
            1,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEmpty(
            $msgErreur,
            'Renvoi image ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Renvoi image ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Renvoi image ne doit pas être bloquée en BDD'
        );
        self::setNbPlus(self::FICHIER_POSSEDE);
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Renvoi image ne doit pas modifier la BDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . '1/1b1a8d9abaf9027d8ff2de4081579538',
            'Renvoi image ne doit pas être bloquée sur HDD'
        );
    }

    /**
     * Renvoi d'une image - Authentifié / Anonyme
     */
    #[Depends('testRenvoiImageAnonymeAuthentifie')]
    public function testRenvoiImageAuthentifieAnonyme(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Renvoi image ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Renvoi image ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Renvoi image ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Renvoi image doit être bloquée en BDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60',
            'Envoi image authentifié ne doit pas être bloquée sur HDD'
        );
    }

    /**
     * Renvoi d'une image - Authentifié / Authentifié
     */
    #[Depends('testRenvoiImageAuthentifieAnonyme')]
    public function testRenvoiImageAuthentifieAuthentifie(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('admin', 'password');

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEquals(
            1,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEmpty(
            $msgErreur,
            'Renvoi image ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Renvoi image ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Renvoi image ne doit pas être bloquée en BDD'
        );
        self::setNbPlus(self::FICHIER_POSSEDE);
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Renvoi image ne doit pas être bloquée en BDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60',
            'Envoi image authentifié ne doit pas être bloquée sur HDD'
        );
    }

    /**
     * Renvoi d'une image - Authentifié / Authentifié Autrement
     */
    #[Depends('testRenvoiImageAuthentifieAuthentifie')]
    public function testRenvoiImageAuthentifieAuthentifie2(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_authentifie.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('user', 'password');

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEquals(
            2,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEmpty(
            $msgErreur,
            'Renvoi image ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Renvoi image ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Renvoi image ne doit pas être bloquée en BDD'
        );
        self::setNbPlus(self::FICHIER_POSSEDE);
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Renvoi image ne doit pas être bloquée en BDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . '7/79d74bcadc1b403c1b833ba60792ce60',
            'Envoi image authentifié ne doit pas être bloquée sur HDD'
        );
    }

    /**
     * Suppression d'une image inexistante
     */
    #[Depends('testRenvoiImageAuthentifieAuthentifie2')]
    public function testSuppressionImageInexistante(): void
    {
        self::prepareTest();
        $_GET['id'] = 'fichierInexistant';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Suppression image inexistante doit être bloqué dans delete.php'
        );
        $this->assertEmpty(
            $msgWarning,
            'Suppression image inexistante doit être bloqué dans delete.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Suppression image inexistante doit être bloqué en BDD'
        );
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Suppression image inexistante doit être bloqué en BDD'
        );
    }

    /**
     * Suppression d'une image - Propriétaire en étant Anonyme
     */
    #[Depends('testSuppressionImageInexistante')]
    public function testSuppressionImageProprietaireAnonyme(): void
    {
        self::prepareTest();
        $_GET['id'] = '_image_404.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Suppression image possédée par autrui doit être bloqué dans delete.php'
        );
        $this->assertEmpty(
            $msgWarning,
            'Suppression image possédée par autrui doit être bloqué dans delete.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Suppression image possédée par autrui doit être bloqué en BDD'
        );
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Suppression image possédée par autrui doit être bloqué en BDD'
        );
    }

    /**
     * Suppression d'une image - Propriétaire en étant Authentifié mais Autre
     */
    #[Depends('testSuppressionImageProprietaireAnonyme')]
    public function testSuppressionImageProprietaireAuthentifie2(): void
    {
        self::prepareTest();
        $_GET['id'] = '_image_404.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('user', 'password');

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEquals(
            2,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertNotEmpty(
            $msgErreur,
            'Suppression image possédée par autrui doit être bloqué dans delete.php'
        );
        $this->assertEmpty(
            $msgWarning,
            'Suppression image possédée par autrui doit être bloqué dans delete.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Suppression image possédée par autrui doit être bloqué en BDD'
        );
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Suppression image possédée par autrui doit être bloqué en BDD'
        );
    }

    /**
     * Vérification du bon fonctionnement du mécanisme de détection des doublons en BDD
     */
    #[Depends('testSuppressionImageProprietaireAuthentifie2')]
    public function testVerificationCalculsDoublonsBDD(): void
    {
        self::prepareTest();

        $uneImageDoublon = new ImageObject('image_11.png');
        $uneAutreImage = new ImageObject('image_13.png');
        $uneImageInexistante = new ImageObject();

        $this->assertEquals(2, $uneImageDoublon->getNbDoublons(), 'L\'image est présente en id 11 & 12');
        $this->assertEquals(1, $uneAutreImage->getNbDoublons(), 'L\'image est présente en id 13');
        $this->assertEquals(0, $uneImageInexistante->getNbDoublons(), 'L\'image n\'existe pas...');
    }

    /**
     * Suppression d'une image - Propriétaire en étant Authentifié
     */
    #[Depends('testVerificationCalculsDoublonsBDD')]
    public function testSuppressionImageProprietaireAuthentifie(): void
    {
        self::prepareTest();
        // Copie du fichier
        copy(_PATH_TESTS_IMAGES_ . 'image_a_supprimer.png', _PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29');

        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $_GET['id'] = '100000019001334055750.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('user', 'password');

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEquals(
            2,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEmpty(
            $msgErreur,
            'Suppression image possédée ne doit pas être bloqué dans delete.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Suppression image possédée ne doit pas être bloqué dans delete.php - Warning : ' . $msgWarning
        );
        self::setNbMoins(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Suppression image possédée ne doit pas être bloqué en BDD'
        );
        self::setNbMoins(self::FICHIER_POSSEDE);
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Suppression image possédée ne doit pas être bloqué en BDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29',
            'Suppression image possédée ne doit pas être effacée du HDD car encore en usage'
        );
    }

    /**
     * Suppression d'une image - Anonyme en étant hors délai
     */
    #[Depends('testSuppressionImageProprietaireAuthentifie')]
    public function testSuppressionImageAnonymeHorsDelai(): void
    {
        self::prepareTest();
        $_GET['id'] = '146734019451334055750.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Suppression image hors délai doit être bloqué dans delete.php'
        );
        $this->assertEmpty(
            $msgWarning,
            'Suppression image hors délai doit être bloqué dans delete.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Suppression image hors délai doit être bloqué en BDD'
        );
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Suppression image hors délai doit être bloqué en BDD'
        );
    }

    /**
     * Suppression d'une image - Anonyme en étant dans délai mais pas la bonne IP
     */
    #[Depends('testSuppressionImageProprietaireAuthentifie')]
    public function testSuppressionImageAnonymeDansDelaiMauvaiseIP(): void
    {
        self::prepareTest();
        // Surcharge de l'adresse IP par défaut
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $_GET['id'] = '147834019001334055750.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Suppression image dans délai par autre IP doit être bloqué dans delete.php'
        );
        $this->assertEmpty(
            $msgWarning,
            'Suppression image dans délai par autre IP doit être bloqué dans delete.php - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Suppression image dans délai par autre IP doit être bloqué en BDD'
        );
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Suppression image dans délai par autre IP doit être bloqué en BDD'
        );
    }

    /**
     * Suppression d'une image - Anonyme en étant dans le délai
     */
    #[Depends('testSuppressionImageAnonymeDansDelaiMauvaiseIP')]
    public function testSuppressionImageAnonymeDansDelai(): void
    {
        self::prepareTest();
        // Surcharge de l'adresse IP par défaut
        $_SERVER['REMOTE_ADDR'] = '127.0.0.10';
        $_GET['id'] = '147834019001334055750.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Suppression image dans délai ne doit pas être bloqué dans delete.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Suppression image dans délai ne doit pas être bloqué dans delete.php - Warning : ' . $msgWarning
        );
        self::setNbMoins(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Suppression image dans délai ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Suppression image dans délai ne doit pas être bloqué en BDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . 'e/e656d1b6582a15f0f458006898b40e29',
            'Suppression image dans délai doit être effacé du HDD'
        );
    }

    /**
     * Test de l'envoi simple avec redimensionnement : présence BDD et HDD
     */
    #[Depends('testSuppressionImageAnonymeDansDelai')]
    public function testEnvoiRedim(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_paysage_800x600.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['redimImage'] = '400x200';

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Envoi image avec redim ne doit pas être bloqué dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Envoi image avec redim ne doit pas être bloqué dans upload.php - Warning : ' . $msgWarning
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Envoi image avec redim doit créer d\'image en BDD'
        );
        $this->assertFileDoesNotExist(
            _PATH_IMAGES_ . '4/4db0b6f10d49fb1a8c2e8b8ff47cf3f6',
            'Envoi image avec redim ne doit pas créer d\'image originale sur HDD'
        );
        $this->assertFileExists(
            _PATH_IMAGES_ . '0/02c7908b07fbbe94a7363bf76fc36e7f',
            'Envoi image avec redim doit créer image redim sur HDD'
        );
    }

    /**
     * Test de l'envoi d'une image WebP animée avec redimensionnement
     */
    #[Depends('testEnvoiRedim')]
    public function testEnvoiModifWebP(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 37342;
        $_FILES['fichier']['name'] = 'animated-image.webp';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['redimImage'] = '400x200';
        $_POST['angleRotation'] = '90';

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Envoi image webp avec modif ne pas pas faire d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertNotEmpty(
            $msgWarning,
            'Envoi image webp avec modif doit lever un warning'
        );
        self::setNbPlus(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Envoi image webp avec modif doit créer une image en BDD'
        );
        $this->assertFileEquals(
            $monImage->getPath(),
            $_FILES['fichier']['tmp_name'],
            'Envoi image webp avec modif ne doit pas faire de modif du fichier source'
        );
    }

    /**
     * Test de la suppression d'une image avec plusieurs miniatures
     */
    #[Depends('testEnvoiModifWebP')]
    public function testSuppressionImagePlusieursMiniatures(): void
    {
        self::prepareTest();
        // Copie des fichiers
        rename(
            _PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple.png',
            _PATH_IMAGES_ . 'a/aec65c6b4469bb7267d2d55af5fbd87b'
        );
        rename(
            _PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple-100x100.png',
            _PATH_MINIATURES_ . '0/031328c1a7ffe7eed0a2cab4eca05a63'
        );
        rename(
            _PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple-200x200.png',
            _PATH_MINIATURES_ . '2/278a70a02e036cc85e0d7e605fdc517f'
        );

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_GET['id'] = '14777777.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('admin', 'password');

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEquals(
            1,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEmpty(
            $msgErreur,
            'Suppression image ne doit pas être bloqué dans delete.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Suppression image ne doit pas être bloqué dans delete.php - Warning : ' . $msgWarning
        );
        self::setNbMoins(self::FICHIER_IMAGE);
        $this->assertEquals(
            self::countImagesEnBdd(),
            self::getNb(self::FICHIER_IMAGE),
            'Suppression image ne doit pas être bloqué en BDD'
        );
        self::setNbMoins(self::FICHIER_POSSEDE);
        $this->assertEquals(
            self::countImagesPossedeesEnBdd(),
            self::getNb(self::FICHIER_POSSEDE),
            'Suppression possession ne doit pas être bloqué en BDD'
        );
        $this->assertFileDoesNotExist(
            _PATH_IMAGES_ . 'a/aec65c6b4469bb7267d2d55af5fbd87b',
            'Suppression image doit être effacé du HDD'
        );
        self::setNbMoins(self::FICHIER_MINIATURE);
        self::setNbMoins(self::FICHIER_MINIATURE);
        $this->assertEquals(
            self::countMiniaturesEnBdd(),
            self::getNb(self::FICHIER_MINIATURE),
            'Suppression miniatureS ne doit pas être bloqué en BDD'
        );
        $this->assertFileDoesNotExist(
            _PATH_MINIATURES_ . '0/031328c1a7ffe7eed0a2cab4eca05a63',
            'Suppression image doit effacer toutes les miniatures du HDD'
        );
        $this->assertFileDoesNotExist(
            _PATH_MINIATURES_ . '2/278a70a02e036cc85e0d7e605fdc517f',
            'Suppression image doit effacer toutes les miniatures du HDD'
        );
    }


    /**
     * Blocage de l'envoi de fichiers lorsqu'une adresse IP dépasse le seuil de tolérance
     */
    #[RunInSeparateProcess]
    public function testAbuseReputationIpBlocageUpload(): void
    {
        self::prepareTest(true);
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1'; // IP avec trop d'images déjà bloquées
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_banned.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'L\'envoi d\'une image depuis une réseau ayant déjà trop d\'images bloquées ne doit pas être possible.'
        );
    }

    /**
     * Détection du caractère animé ou non d'une image WebP
     */
    #[RunInSeparateProcess]
    public function testWebPDetection(): void
    {
        require 'config/config.php';

        $this->assertTrue(
            HelperImage::isAnimatedWebp(_PATH_TESTS_IMAGES_ . 'animated-image.webp'),
            'Image webp animée mal détectée.'
        );
        $this->assertFalse(
            HelperImage::isAnimatedWebp(_PATH_TESTS_IMAGES_ . 'test.webp'),
            'Image webp non animée mal détectée.'
        );
    }
}
