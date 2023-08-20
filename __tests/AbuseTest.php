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
use ImageHeberg\MetaObject;
use ImageHeberg\MiniatureObject;
use ImageHeberg\Outils;
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
    public function testAbuse()
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_POST['userMail'] = "john.doe@example.com";
        $_POST['urlImage'] = "https://www.example.com/files/15.png";

        ob_start();
        require 'abuse.php';
        ob_end_clean();

        $imageBloquee = new ImageObject("15.png");
        $imageMemeMd5 = new ImageObject("16.png");
        $this->assertEquals(true, $imageBloquee->isSignalee(), "Image signalée doit l'être");
        $this->assertEquals(
            true,
            $imageMemeMd5->isSignalee(),
            "Image avec même MD5 qu'une image signalée doit l'être également"
        );
    }

    /**
     * Renvoi d'une image bloquée et demande de son affichage
     * @runInSeparateProcess
     */
    public function testAbuseRenvoiImage()
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_FILES['fichier']['size'] = 104857;
        $_FILES['fichier']['name'] = '15180025661369047607.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertTrue(
            empty($msgErreur),
            "Renvoi image déjà bloquée ne doit pas être bloquée dans upload.php - Erreur : " . $msgErreur
        );
        $this->assertTrue(
            empty($msgWarning),
            "Renvoi image déjà bloquée ne doit pas être bloquée dans upload.php - Warning : " . $msgWarning
        );
        $this->assertEquals(
            true,
            $monImage->isBloquee(),
            "Renvoi image déjà bloquée doit être isBloquée en BDD"
        );
    }
}
