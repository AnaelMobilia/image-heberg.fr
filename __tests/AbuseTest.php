<?php

/*
 * Copyright 2008-2023 Anael MOBILIA
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
use ImageHeberg\HelperAdmin;
use ImageHeberg\MiniatureObject;
use ImageHeberg\HelperImage;
use ImageHeberg\HelperSysteme;
use ImageHeberg\RessourceInterface;
use ImageHeberg\RessourceObject;
use ImageHeberg\SessionObject;
use ImageHeberg\UtilisateurObject;
use PHPUnit\Framework\TestCase;

class AbuseTest extends TestCase
{
    /**
     * Signalement d'une image
     * @runInSeparateProcess
     */
    public function testAbuse(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_POST['userMail'] = 'john.doe@example.com';
        $_POST['urlImage'] = 'https://www.example.com/files/15.png';

        ob_start();
        require 'abuse.php';
        ob_end_clean();

        $imageBloquee = new ImageObject('15.png');
        $imageMemeMd5 = new ImageObject('16.png');
        $this->assertTrue($imageBloquee->isSignalee(), 'Image signalée doit l\'être');
        $this->assertTrue(
            $imageMemeMd5->isSignalee(),
            'Image avec même MD5 qu\'une image signalée doit l\'être également'
        );
    }

    /**
     * Renvoi d'une image bloquée et demande de son affichage
     * @runInSeparateProcess
     */
    public function testAbuseRenvoiImage(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_FILES['fichier']['size'] = 146;
        $_FILES['fichier']['name'] = 'imageDejaBloquee.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            'Renvoi image déjà bloquée ne doit pas être bloquée dans upload.php - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            'Renvoi image déjà bloquée ne doit pas être bloquée dans upload.php - Warning : ' . $msgWarning
        );
        $this->assertTrue(
            $monImage->isBloquee(),
            'Renvoi image déjà bloquée doit être isBloquée en BDD'
        );
    }

    /**
     * Signalement d'une image approuvée
     * @runInSeparateProcess
     */
    public function testAbuseImageApprouvee(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_POST['userMail'] = 'john.doe@example.com';
        $_POST['urlImage'] = 'https://www.example.com/files/_image_404.png';

        ob_start();
        require 'abuse.php';
        ob_end_clean();

        $imageSignalee = new ImageObject('_image_404.png');
        $this->assertFalse($imageSignalee->isSignalee(), 'Image approuvée qui est signalée ne doit pas être bloquée');
    }

    /**
     * Approbation d'une image signalée
     * @runInSeparateProcess
     */
    public function testAbuseImageSignaleePuisApprouvee(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // Flagguer l'image comme signalée
        $image = new ImageObject('_image_banned.png');
        $image->setSignalee(true);
        $image->sauver();

        // Se connecter en tant que l'admin
        $monMembre = new UtilisateurObject();
        $monMembre->connexion('admin', 'password');

        // Approuver l'image dans l'admin
        $_GET['approuver'] = '1';
        $_GET['idImage'] = '2';

        ob_start();
        require 'admin/abuse.php';
        ob_end_clean();

        $image = new ImageObject('_image_banned.png');
        $this->assertFalse($image->isSignalee(), 'Image signalée qui est approuvée ne doit plus être signalée');
    }

    /**
     * Envoi d'une image depuis le même réseau qu'une image bloquée
     * @runInSeparateProcess
     */
    public function testAbuseImageEnvoiDepuisReseauMalveillant(): void
    {
        $imagesAvantEnvoi = HelperAdmin::getImagesPotentiellementIndesirables();

        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '10.10.10.11';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_FILES['fichier']['size'] = 146;
        $_FILES['fichier']['name'] = 'imageDejaBloquee.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $imagesApresEnvoi = HelperAdmin::getImagesPotentiellementIndesirables();

        $this->assertEmpty(
            $imagesAvantEnvoi,
            'Aucune image ne doit être considérée comme potentiellement indésirable : ' . var_export($imagesAvantEnvoi, true)
        );
        $this->assertNotEmpty(
            $imagesApresEnvoi,
            'L\'image envoyée devrait être considérée comme potentiellement indésirable : ' . var_export($imagesApresEnvoi, true)
        );
    }

    /**
     * Image avec une miniature ENORMEMENT affichée
     * @runInSeparateProcess
     */
    public function testAbuseImageMiniatureTropAffichee(): void
    {
        require 'config/config.php';

        $images = HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_);

        $this->assertNotEmpty(
            $images,
            'Les affichages des miniatures doivent compter dans les affichages d\'une image pour la détection des abus : ' . var_export($images, true)
        );
    }
}
