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

use ImageHeberg\RessourceObject;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class DisplayPicsTest extends TestCase
{
    /**
     * Affichage d'une image inexistante
     */
    #[RunInSeparateProcess]
    public function testImageInexistante(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'files/fichierInexistant.jpg';

        ob_start();
        require 'displayPics.php';
        ob_end_clean();

        /* @var $monObjet RessourceObject */
        $this->assertEquals(
            _IMAGE_404_,
            $monObjet->getNomNouveau(),
            'image_404 si inexistante'
        );
    }

    /**
     * Affichage d'une image inexistante
     */
    #[RunInSeparateProcess]
    public function testMiniatureInexistante(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'files/thumbs/fichierInexistant.jpg';

        ob_start();
        require 'displayPics.php';
        ob_end_clean();

        /* @var $monObjet RessourceObject */
        $this->assertEquals(
            _IMAGE_404_,
            $monObjet->getNomNouveau(),
            'image_404 si inexistante'
        );
    }

    /**
     * Affichage d'une image inexistante
     */
    #[RunInSeparateProcess]
    public function testRepertoireInexistant(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'files/repertoireInexistant/fichierInexistant.jpg';

        ob_start();
        require 'displayPics.php';
        ob_end_clean();

        /* @var $monObjet RessourceObject */
        $this->assertEquals(
            _IMAGE_404_,
            $monObjet->getNomNouveau(),
            'image_404 si mauvais sous répertoire'
        );
    }

    /**
     * Affichage d'une image bloquée
     */
    #[RunInSeparateProcess]
    public function testImageBloquee(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'files/image_10.png';

        ob_start();
        require 'displayPics.php';
        ob_end_clean();

        /* @var $monObjet RessourceObject */
        $this->assertEquals(
            _IMAGE_BAN_,
            $monObjet->getNomNouveau(),
            'image_ban si image bloquée'
        );
    }

    /**
     * Affichage d'une image signaléee
     */
    #[RunInSeparateProcess]
    public function testImageSignalee(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'files/image_18.png';

        ob_start();
        require 'displayPics.php';
        ob_end_clean();

        /* @var $monObjet RessourceObject */
        $this->assertEquals(
            _IMAGE_BAN_,
            $monObjet->getNomNouveau(),
            'image_ban si image signalée'
        );
    }
}
