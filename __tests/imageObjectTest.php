<?php

/*
 * Copyright 2008-2019 Anael Mobilia
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

class imageObjectTest extends TestCase {

    /**
     * Rotation des images
     * @runInSeparateProcess
     */
    public function testRotationImages() {
        require 'config/config.php';

        $monImage = new imageObject();
        // JPG
        $angle = 90;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.jpg', _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.jpg-' . $angle . '-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle, "Rotation JPG " . $angle);
        }
        $angle = 180;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.jpg', _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.jpg-' . $angle . '-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle, "Rotation JPG " . $angle);
        }
        $angle = 270;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.jpg', _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.jpg-' . $angle . '-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle, "Rotation JPG " . $angle);
        }

        // PNG
        $angle = 90;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.png', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.png-' . $angle . '-a-partir-php-7.2', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle, "Rotation PNG " . $angle);
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.png-' . $angle . '-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle, "Rotation PNG " . $angle);
        }
        $angle = 180;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.png', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.png-' . $angle . '-a-partir-php-7.2', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle, "Rotation PNG " . $angle);
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.png-' . $angle . '-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle, "Rotation PNG " . $angle);
        }
        $angle = 270;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.png', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.png-' . $angle . '-a-partir-php-7.2', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle, "Rotation PNG " . $angle);
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.png-' . $angle . '-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle, "Rotation PNG " . $angle);
        }

        // GIF
        $angle = 90;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.gif', _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.gif-' . $angle . '-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle, "Rotation GIF " . $angle);
        }
        $angle = 180;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.gif', _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.gif-' . $angle . '-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle, "Rotation GIF " . $angle);
        }
        $angle = 270;
        $monImage->rotation($angle, _PATH_TESTS_IMAGES_ . 'image_banned.gif', _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_banned.gif-' . $angle . '-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle, "Rotation GIF " . $angle);
        }
    }

    /**
     * Redimensionnement des images
     * @runInSeparateProcess
     */
    public function testRedimensionnementImages() {
        require 'config/config.php';

        $monImage = new imageObject();

        /**
         * Cas NULL
         */
        $this->assertEquals(NULL, $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 600, 800), "Pas d'agrandissement");
        $this->assertEquals(NULL, $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 601, 800), "Pas d'agrandissement");
        $this->assertEquals(NULL, $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 600, 801), "Pas d'agrandissement");
        $this->assertEquals(NULL, $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 599, 800), "Pas d'agrandissement");
        $this->assertEquals(NULL, $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 600, 799), "Pas d'agrandissement");
        $this->assertEquals(NULL, $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 0, 799), "Image de taille zéro");
        $this->assertEquals(NULL, $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 0, 0), "Image de taille zéro");
        $this->assertEquals(NULL, $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 10, 0), "Image de taille zéro");

        /**
         * Format portrait
         */
        // Doit être 200x267
        $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', _PATH_TESTS_OUTPUT_ . 'image_portrait_200x400.png', 200, 400);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_portrait_200x400.png-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_portrait_200x400.png', "Redimensionnement portrait 200x400");
        }

        // Doit être 150x200
        $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', _PATH_TESTS_OUTPUT_ . 'image_portrait_400x200.png', 400, 200);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_portrait_400x200.png-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_portrait_400x200.png', "Redimensionnement portrait 400x200");
        }

        /**
         * Format paysage
         */
        // Doit être 267x200
        $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_paysage_800x600.png', _PATH_TESTS_OUTPUT_ . 'image_paysage_400x200.png', 400, 200);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_paysage_400x200.png-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_paysage_400x200.png', "Redimensionnement paysage 400x200");
        }

        // Doit être 200x150
        $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_paysage_800x600.png', _PATH_TESTS_OUTPUT_ . 'image_paysage_200x400.png', 200, 400);
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            
        } else {
            $this->assertFileEquals(_PATH_TESTS_IMAGES_ . 'image_paysage_200x400.png-jusqua-php-7.1', _PATH_TESTS_OUTPUT_ . 'image_paysage_200x400.png', "Redimensionnement paysage 200x400");
        }
    }

}
