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

use ImageHeberg\HelperAdmin;
use ImageHeberg\HelperImage;
use ImageHeberg\ImageObject;
use ImageHeberg\MaBDD;
use ImageHeberg\RessourceObject;
use ImageHeberg\UtilisateurObject;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class ImageUploadAndDeleteTest extends TestCase
{
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
     * Nombre d'images sur le HDD
     * @return int
     */
    private static function countImagesSurHdd(): int
    {
        return HelperAdmin::getAllImagesNameHDD(_PATH_IMAGES_)->count();
    }

    /**
     * Nombre de miniatures sur le HDD
     * @return int
     */
    private static function countMiniaturesSurHDD(): int
    {
        return HelperAdmin::getAllImagesNameHDD(_PATH_MINIATURES_)->count();
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
        $_SERVER['REQUEST_URI'] = '';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Envoi image doit créer image en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore + 1),
            self::countImagesSurHdd(),
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbThumbsFilesBefore = self::countMiniaturesSurHDD();
        $nbThumbsBddBefore = self::countMiniaturesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Envoi image + miniature doit créer image en BDD'
        );
        $this->assertEquals(
            ($nbThumbsBddBefore + 1),
            self::countMiniaturesEnBdd(),
            'Envoi image + miniature doit créer miniature en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore + 1),
            self::countImagesSurHdd(),
            'Envoi image + miniature doit créer image sur HDD'
        );
        $this->assertEquals(
            ($nbThumbsFilesBefore + 1),
            self::countMiniaturesSurHDD(),
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbThumbsFilesBefore = self::countMiniaturesSurHDD();
        $nbThumbsBddBefore = self::countMiniaturesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Envoi image + miniature (rotation) doit créer image en BDD'
        );
        $this->assertEquals(
            ($nbThumbsBddBefore + 1),
            self::countMiniaturesEnBdd(),
            'Envoi image + miniature (rotation) doit créer miniature en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore + 1),
            self::countImagesSurHdd(),
            'Envoi image + miniature (rotation) doit créer image sur HDD'
        );
        $this->assertEquals(
            ($nbThumbsFilesBefore + 1),
            self::countMiniaturesSurHDD(),
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbThumbsFilesBefore = self::countMiniaturesSurHDD();
        $nbThumbsBddBefore = self::countMiniaturesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Renvoi image - dde miniature - ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            ($nbThumbsBddBefore + 1),
            self::countMiniaturesEnBdd(),
            'Renvoi image - dde miniature - doit créer miniature en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Renvoi image - dde miniature - ne doit pas créer image sur HDD'
        );
        $this->assertEquals(
            ($nbThumbsFilesBefore + 1),
            self::countMiniaturesSurHDD(),
            'Renvoi image - dde miniature - doit créer miniature en BDD'
        );
        $this->assertEquals(
            $nbImagesPossedeesBefore,
            self::countImagesPossedeesEnBdd(),
            'Renvoi image - dde miniature (possède) - ne doit rien faire en BDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbThumbsFilesBefore = self::countMiniaturesSurHDD();
        $nbThumbsBddBefore = self::countMiniaturesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Renvoi image - dde NOUVELLE miniature - ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            ($nbThumbsBddBefore + 1),
            self::countMiniaturesEnBdd(),
            'Renvoi image - dde NOUVELLE miniature - doit créer miniature en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Renvoi image - dde NOUVELLE miniature - ne doit pas créer image sur HDD'
        );
        $this->assertEquals(
            ($nbThumbsFilesBefore + 1),
            self::countMiniaturesSurHDD(),
            'Renvoi image - dde NOUVELLE miniature - doit créer miniature sur HDD'
        );
        $this->assertEquals(
            $nbImagesPossedeesBefore,
            self::countImagesPossedeesEnBdd(),
            'Renvoi image - dde NOUVELLE miniature (possede) - ne doit rien faire en BDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Non affichage du formulaire d\'upload doit faire un message d\'erreur'
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Non affichage du formulaire d\'upload ne doit pas créer d\'image en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Non affichage du formulaire d\'upload ne doit pas créer d\'image sur HDD'
        );
    }

    /**
     * Envoi sans fichier
     */
    #[Depends('testEnvoiBrut')]
    public function testEnvoiSansFichier(): void
    {
        self::prepareTest();

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Absence de fichier envoyé devrait être détecté dans upload.php'
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Absence de fichier envoyé ne doit pas créer d\'image en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Absence de fichier envoyé ne doit pas créer d\'image sur HDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Fichier trop gros devrait être détecté dans upload.php'
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Fichier trop gros ne doit pas créer d\'image en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Fichier trop gros ne doit pas créer d\'image sur HDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Type mime : pas une image doit être bloquée dans upload.php'
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Type mime : pas une image ne doit pas créer d\'image en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Type mime : pas une image ne doit pas créer d\'image sur HDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Type mime : fausse image doit être bloquée dans upload.php'
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Type mime : fausse image ne doit pas créer d\'image en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Type mime : fausse image ne doit pas créer d\'image sur HDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Type mime : extension incorrecte ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore + 1),
            self::countImagesSurHdd(),
            'Type mime : extension incorrecte ne doit pas être bloquée sur HDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Image 10000x1 ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore + 1),
            self::countImagesSurHdd(),
            'Image 10000x1 ne doit pas être bloquée sur HDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Image 1x10000 ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore + 1),
            self::countImagesSurHdd(),
            'Image 1x10000 ne doit pas être bloquée sur HDD'
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
        $_FILES['fichier']['name'] = 'image_15000x15000.png';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Image 15000x15000 doit être bloquée dans upload.php'
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Image 15000x15000 doit être bloquée en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Image 15000x15000 doit être bloquée sur HDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            1,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Envoi image authentifié ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore + 1),
            self::countImagesSurHdd(),
            'Envoi image authentifié ne doit pas être bloquée sur HDD'
        );
        $this->assertEquals(
            ($nbImagesPossedeesBefore + 1),
            self::countImagesPossedeesEnBdd(),
            'Envoi image authentifié (possede) ne doit pas être bloquée en BDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Renvoi image ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Renvoi image doit être bloquée sur HDD'
        );
        $this->assertEquals(
            $nbImagesPossedeesBefore,
            self::countImagesPossedeesEnBdd(),
            'Renvoi image (possede) ne doit pas modifier la BDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            1,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Renvoi image ne doit pas être bloquée en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Renvoi image doit être bloquée sur HDD'
        );
        $this->assertEquals(
            ($nbImagesPossedeesBefore + 1),
            self::countImagesPossedeesEnBdd(),
            'Renvoi image (possede) doit modifier la BDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Renvoi image authentifié ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Renvoi image authentifié doit être bloqué sur HDD'
        );
        $this->assertEquals(
            $nbImagesPossedeesBefore,
            self::countImagesPossedeesEnBdd(),
            'Renvoi image authentifié (possede) ne doit pas modifier la BDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            1,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Renvoi image authentifié ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Renvoi image authentifié ne doit être bloqué sur HDD'
        );
        $this->assertEquals(
            ($nbImagesPossedeesBefore + 1),
            self::countImagesPossedeesEnBdd(),
            'Renvoi image authentifié (possede) ne doit pas être bloquée en BDD'
        );
    }

    /**
     * Renvoi d'une image - Authentifié / Authentifié autrement
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            2,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Renvoi image authentifié ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Renvoi image authentifié doit être bloqué sur HDD'
        );
        $this->assertEquals(
            ($nbImagesPossedeesBefore + 1),
            self::countImagesPossedeesEnBdd(),
            'Renvoi image authentifié (possede) ne doit pas être bloquée en BDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Suppression image inexistante doit être bloqué dans delete.php'
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Suppression image inexistante doit être bloqué en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Suppression image inexistante doit être bloqué sur BDD'
        );
        $this->assertEquals(
            $nbImagesPossedeesBefore,
            self::countImagesPossedeesEnBdd(),
            'Suppression image inexistante (possede) doit être bloqué en BDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Suppression image possédée par autrui doit être bloqué dans delete.php'
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Suppression image possédée par autrui doit être bloqué en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Suppression image possédée par autrui doit être bloqué sur HDD'
        );
        $this->assertEquals(
            $nbImagesPossedeesBefore,
            self::countImagesPossedeesEnBdd(),
            'Suppression image possédée par autrui (possede) doit être bloqué en BDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

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
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Suppression image possédée par autrui doit être bloqué en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Suppression image possédée par autrui doit être bloqué sur HDD'
        );
        $this->assertEquals(
            $nbImagesPossedeesBefore,
            self::countImagesPossedeesEnBdd(),
            'Suppression image possédée par autrui (possede) doit être bloqué en BDD'
        );
    }

    /**
     * Suppression d'une image - God Mode (Admin) - délai dépassé
     */
    #[Depends('testSuppressionImageProprietaireAuthentifie2')]
    public function testSuppressionImageGodMode(): void
    {
        self::prepareTest();
        $_GET['id'] = 'image_98.png';
        $_SERVER['REQUEST_URI'] = 'forceDelete=1';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('admin', 'password');

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEquals(
            1,
            $unMembre->getId(),
            'L\'admin doit être connecté'
        );
        $this->assertEmpty(
            $msgErreur,
            'Suppression image avec délai dépassé via God Mode ne doit pas être bloqué dans delete.php'
        );
        $this->assertEquals(
            ($nbImagesBddBefore - 1),
            self::countImagesEnBdd(),
            'Suppression image avec délai dépassé via God Mode ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore - 1),
            self::countImagesSurHdd(),
            'Suppression image avec délai dépassé via God Mode ne doit pas être bloqué sur HDD'
        );
    }

    /**
     * Suppression d'une image - God Mode (Admin) - possédée par un autre utilisateur
     */
    #[Depends('testSuppressionImageGodMode')]
    public function testSuppressionImageGodMode2(): void
    {
        self::prepareTest();
        $_GET['id'] = 'image_99.png';
        $_SERVER['REQUEST_URI'] = 'forceDelete=1';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('admin', 'password');

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEquals(
            1,
            $unMembre->getId(),
            'L\'admin doit être connecté'
        );
        $this->assertEmpty(
            $msgErreur,
            'Suppression image possédée par autrui via God Mode ne doit pas être bloqué dans delete.php'
        );
        $this->assertEquals(
            ($nbImagesBddBefore - 1),
            self::countImagesEnBdd(),
            'Suppression image possédée par autrui via God Mode ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore - 1),
            self::countImagesSurHdd(),
            'Suppression image possédée par autrui via God Mode ne doit pas être bloqué sur HDD'
        );
        $this->assertEquals(
            ($nbImagesPossedeesBefore - 1),
            self::countImagesPossedeesEnBdd(),
            'Suppression image possédée par autrui (possede) via God Mode ne doit pas être bloqué en BDD'
        );
    }

    /**
     * Vérification du bon fonctionnement du mécanisme de détection des doublons en BDD
     */
    #[Depends('testSuppressionImageGodMode2')]
    public function testVerificationCalculsDoublonsBDD(): void
    {
        self::prepareTest();

        $uneImageDoublon = new ImageObject('image_11.png');
        $uneAutreImage = new ImageObject('image_13.png');
        $uneImageInexistante = new ImageObject();

        $this->assertEquals(
            2,
            $uneImageDoublon->getNbUsages(),
            'L\'image est présente en id 10 et 11'
        );
        $this->assertEquals(
            1,
            $uneAutreImage->getNbUsages(),
            'L\'image n\'est présente qu\'en id 13'
        );
        $this->assertEquals(
            0,
            $uneImageInexistante->getNbUsages(),
            'L\'image n\'existe pas...'
        );
    }

    /**
     * Suppression d'une image - Propriétaire en étant Authentifié
     */
    #[Depends('testVerificationCalculsDoublonsBDD')]
    public function testSuppressionImageProprietaireAuthentifie(): void
    {
        self::prepareTest();

        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $_SERVER['REMOTE_PORT'] = '1234';
        $_GET['id'] = 'image_11.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('user', 'password');

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEquals(
            2,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEquals(
            ($nbImagesBddBefore - 1),
            self::countImagesEnBdd(),
            'Suppression image possédée ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Suppression image possédée ne doit pas être effacée du HDD car encore en usage par image_10.png'
        );
        $this->assertEquals(
            ($nbImagesPossedeesBefore - 1),
            self::countImagesPossedeesEnBdd(),
            'Suppression image possédée (possede) ne doit pas être bloqué en BDD'
        );
    }

    /**
     * Suppression d'une image - Anonyme en étant hors délai
     */
    #[Depends('testSuppressionImageProprietaireAuthentifie')]
    public function testSuppressionImageAnonymeHorsDelai(): void
    {
        self::prepareTest();
        $_GET['id'] = 'image_12.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Suppression image hors délai doit être bloqué dans delete.php'
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Suppression image hors délai doit être bloqué en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Suppression image hors délai doit être bloqué sur HDD'
        );
        $this->assertEquals(
            $nbImagesPossedeesBefore,
            self::countImagesPossedeesEnBdd(),
            'Suppression image hors délai (possede) doit être bloqué en BDD'
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
        $_SERVER['REMOTE_PORT'] = '1234';
        $_GET['id'] = 'image_12.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'Suppression image dans délai par autre IP doit être bloqué dans delete.php'
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'Suppression image dans délai par autre IP doit être bloqué en BDD'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
            'Suppression image dans délai par autre IP doit être bloqué sur HDD'
        );
        $this->assertEquals(
            $nbImagesPossedeesBefore,
            self::countImagesPossedeesEnBdd(),
            'Suppression image dans délai par autre IP (possede) doit être bloqué en BDD'
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
        $_SERVER['REMOTE_PORT'] = '1234';
        $_GET['id'] = 'image_13.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEquals(
            ($nbImagesBddBefore - 1),
            self::countImagesEnBdd(),
            'Suppression image anonyme dans délai ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore - 1),
            self::countImagesSurHdd(),
            'Suppression image anonyme dans délai ne doit pas être bloqué sur HDD'
        );
        $this->assertEquals(
            $nbImagesPossedeesBefore,
            self::countImagesPossedeesEnBdd(),
            'Suppression image anonyme dans délai (possede) ne doit pas modifier la BDD'
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

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Envoi image avec redim doit créer image en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore + 1),
            self::countImagesSurHdd(),
            'Envoi image avec redim doit créer image sur HDD'
        );
    }

    /**
     * Test de l'envoi d'une image WebP animée avec redimensionnement
     */
    #[Depends('testEnvoiRedim')]
    public function testEnvoiModifWebPAnimee(): void
    {
        self::prepareTest();
        $_FILES['fichier']['size'] = 37342;
        $_FILES['fichier']['name'] = 'animated-image.webp';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];
        $_POST['redimImage'] = '400x200';
        $_POST['angleRotation'] = '90';

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertNotEmpty(
            $msgWarning,
            'Envoi image WebP animée avec modif doit lever un warning'
        );
        $this->assertEquals(
            ($nbImagesBddBefore + 1),
            self::countImagesEnBdd(),
            'Envoi image WebP animée avec modif doit créer une image en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore + 1),
            self::countImagesSurHdd(),
            'Envoi image WebP animée avec modif doit créer une image sur HDD'
        );
        $this->assertFileEquals(
            $monImage->getPathMd5(),
            $_FILES['fichier']['tmp_name'],
            'Envoi image WebP animée avec modif ne doit pas faire de modif du fichier source'
        );
    }

    /**
     * Test de la suppression d'une image avec plusieurs miniatures
     */
    #[Depends('testEnvoiModifWebPAnimee')]
    public function testSuppressionImagePlusieursMiniatures(): void
    {
        self::prepareTest();
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';
        $_GET['id'] = 'image_14.png';
        $_GET['type'] = RessourceObject::TYPE_IMAGE;

        /**
         * Authentification
         */
        $unMembre = new UtilisateurObject();
        $unMembre->connexion('admin', 'password');

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();
        $nbThumbsFilesBefore = self::countMiniaturesSurHDD();
        $nbThumbsBddBefore = self::countMiniaturesEnBdd();
        $nbImagesPossedeesBefore = self::countImagesPossedeesEnBdd();

        ob_start();
        require 'delete.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEquals(
            1,
            $unMembre->getId(),
            'Le membre doit être connecté'
        );
        $this->assertEquals(
            ($nbImagesBddBefore - 1),
            self::countImagesEnBdd(),
            'Suppression image ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            ($nbImagesPossedeesBefore - 1),
            self::countImagesPossedeesEnBdd(),
            'Suppression possession ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            ($nbImagesFilesBefore - 1),
            self::countImagesSurHdd(),
            'Suppression image doit être effacé du HDD'
        );
        $this->assertEquals(
            ($nbThumbsBddBefore - 2),
            self::countMiniaturesEnBdd(),
            'Suppression miniatureS ne doit pas être bloqué en BDD'
        );
        $this->assertEquals(
            ($nbThumbsFilesBefore - 2),
            self::countMiniaturesSurHDD(),
            'Suppression miniatureS ne doit pas être bloqué sur HDD'
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
        $_SERVER['REMOTE_PORT'] = '1234';
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = 'image_banned.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        $nbImagesFilesBefore = self::countImagesSurHdd();
        $nbImagesBddBefore = self::countImagesEnBdd();

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertNotEmpty(
            $msgErreur,
            'L\'envoi d\'une image depuis une réseau ayant déjà trop d\'images bloquées ne doit pas être possible.'
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            $nbImagesBddBefore,
            self::countImagesEnBdd(),
            'L\'envoi d\'une image depuis une réseau ayant déjà trop d\'images bloquées ne doit pas être possible.'
        );
        $this->assertEquals(
            $nbImagesFilesBefore,
            self::countImagesSurHdd(),
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
            'Image WebP animée mal détectée.'
        );
        $this->assertFalse(
            HelperImage::isAnimatedWebp(_PATH_TESTS_IMAGES_ . 'test.webp'),
            'Image WebP non animée mal détectée.'
        );
    }
}
