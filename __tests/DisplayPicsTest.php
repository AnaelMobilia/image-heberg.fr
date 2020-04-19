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

class DisplayPicsTest extends TestCase
{

    /**
     * Affichage d'une image inexistante
     * @runInSeparateProcess
     */
    public function testImageInexistante()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'files/fichierInexistant.jpg';
        ob_start();
        require 'displayPics.php';
        ob_end_clean();
        /* @var $monObjet RessourceObject */
        $this->assertEquals(_IMAGE_404_, $monObjet->getNomNouveau(), "image_404 si inexistante");
    }

    /**
     * Affichage d'une image inexistante
     * @runInSeparateProcess
     */
    public function testMiniatureInexistante()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'files/thumbs/fichierInexistant.jpg';
        ob_start();
        require 'displayPics.php';
        ob_end_clean();
        /* @var $monObjet RessourceObject */
        $this->assertEquals(_IMAGE_404_, $monObjet->getNomNouveau(), "image_404 si inexistante");
    }

    /**
     * Affichage d'une image inexistante
     * @runInSeparateProcess
     */
    public function testRepertoireInexistant()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'files/repertoireInexistant/fichierInexistant.jpg';
        ob_start();
        require 'displayPics.php';
        ob_end_clean();
        /* @var $monObjet RessourceObject */
        $this->assertEquals(_IMAGE_404_, $monObjet->getNomNouveau(), "image_404 si mauvais sous répertoire");
    }

    /**
     * Affichage d'une image bloquée
     * @runInSeparateProcess
     */
    public function testImageBloquee()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'files/imageBloquee.jpg';
        ob_start();
        require 'displayPics.php';
        ob_end_clean();
        /* @var $monObjet RessourceObject */
        $this->assertEquals(_IMAGE_BAN_, $monObjet->getNomNouveau(), "image_ban si image bloquée");
    }

    /**
     * Affichage d'une image signaléee
     * @runInSeparateProcess
     */
    public function testImageSignalee()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = 'files/imageSignalee.png';
        ob_start();
        require 'displayPics.php';
        ob_end_clean();
        /* @var $monObjet RessourceObject */
        $this->assertEquals(_IMAGE_BAN_, $monObjet->getNomNouveau(), "image_ban si image signalée");
    }
}
