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
use ImagickException;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Imagick;

/**
 * L'entête des fichiers contient des informations sur la bibliothéque système les ayant produit
 * <CREATOR: gd-jpeg v1.0 (using IJG JPEG v80), quality = 100
 * Il faut que l'image de référence et celle générée soit avec la même version de l'outil...
 * => Passage des fonction PHP à Imagick qui est un peu plus portable
 * ==> Pour éviter les petites différences liées à la stack de ImageMagick, utilisation d'un fuzz
 */
class ImageObjectTest extends TestCase
{
    // Rotation pour les images
    private const array VALEURS_ANGLE = [90, 180, 270];
    // Tolérance pour la comparaison des couleurs
    private const int FUZZ = 10;

    /**
     * Charge en mémoire une image via Imagick
     * Intégre un fuzz sur l'image pour avoir une tolérance sur la comparaison des couleurs
     * @param $path string chemin du fichier
     * @return Imagick
     * @throws ImagickException
     */
    private function chargeImage(string $path): Imagick
    {
        $uneImage = new Imagick();
        // Tolérance pour la comparaison des couleurs
        // https://imagemagick.org/script/command-line-options.php#fuzz
        $uneImage->setOption('fuzz', self::FUZZ . '%');
        // Chargement de l'image
        $uneImage->readImage($path);

        return $uneImage;
    }

    /**
     * Compare deux images
     * @param $imgReference string Path de l'image de référence
     * @param $img string Path de l'mage à comparer
     * @return bool Identiques ?
     * @throws ImagickException
     */
    private function compareImages(string $imgReference, string $img): bool
    {
        $img1 = $this->chargeImage($imgReference);
        $img2 = $this->chargeImage($img);

        // https://www.php.net/manual/en/imagick.compareimages.php#114944
        // compare the images using METRIC=1 (Absolute Error)
        $result = $img1->compareImages($img2, 1);

        // Afficher le détail des incohérences
        if ($result[1] !== 0) {
            echo 'compareImages - ' . $imgReference . ' VS ' . $img . ' => ' . $result[1] . ' (Fuzz factor : ' . self::FUZZ . ')' . PHP_EOL;
        }

        return ($result[1] === 0);
    }

    /**
     * Rotation des images PNG
     */
    #[RunInSeparateProcess]
    public function testRotationImagesPNG(): void
    {
        require 'config/config.php';

        $monImage = new ImageObject();

        foreach (self::VALEURS_ANGLE as $angle) {
            $monImage->rotation(
                $angle,
                _PATH_TESTS_IMAGES_ . 'image_banned.png',
                _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle
            );
            $this->assertTrue(
                $this->compareImages(
                    _PATH_TESTS_IMAGES_ . 'image_banned-' . $angle . '.png',
                    _PATH_TESTS_OUTPUT_ . 'image_banned.png-' . $angle,
                ),
                'Rotation PNG ' . $angle
            );
        }
    }

    /**
     * Rotation des images JPG
     */
    #[RunInSeparateProcess]
    public function testRotationImagesJPG(): void
    {
        require 'config/config.php';
        $monImage = new ImageObject();

        foreach (self::VALEURS_ANGLE as $angle) {
            $monImage->rotation(
                $angle,
                _PATH_TESTS_IMAGES_ . 'image_banned.jpg',
                _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle
            );
            $this->assertTrue(
                $this->compareImages(
                    _PATH_TESTS_IMAGES_ . 'image_banned-' . $angle . '.jpg',
                    _PATH_TESTS_OUTPUT_ . 'image_banned.jpg-' . $angle
                ),
                'Rotation JPG ' . $angle
            );
        }
    }

    /**
     * Rotation des images GIF
     * Pas de changement en fonction des versions de PHP
     */
    #[RunInSeparateProcess]
    public function testRotationImagesGIF(): void
    {
        require 'config/config.php';
        $monImage = new ImageObject();

        foreach (self::VALEURS_ANGLE as $angle) {
            $monImage->rotation(
                $angle,
                _PATH_TESTS_IMAGES_ . 'image_banned.gif',
                _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle
            );
            $this->assertTrue(
                $this->compareImages(
                    _PATH_TESTS_IMAGES_ . 'image_banned-' . $angle . '.gif',
                    _PATH_TESTS_OUTPUT_ . 'image_banned.gif-' . $angle
                ),
                'Rotation GIF ' . $angle
            );
        }
    }


    /**
     * Rotation des images WEBP
     * Pas de changement en fonction des versions de PHP
     */
    #[RunInSeparateProcess]
    public function testRotationImagesWEBP(): void
    {
        require 'config/config.php';
        $monImage = new ImageObject();

        foreach (self::VALEURS_ANGLE as $angle) {
            $monImage->rotation(
                $angle,
                _PATH_TESTS_IMAGES_ . 'test.webp',
                _PATH_TESTS_OUTPUT_ . 'test.webp-' . $angle
            );
            $this->assertTrue(
                $this->compareImages(
                    _PATH_TESTS_IMAGES_ . 'test-' . $angle . '.webp',
                    _PATH_TESTS_OUTPUT_ . 'test.webp-' . $angle
                ),
                'Rotation WEBP ' . $angle
            );
        }
    }

    /**
     * Redimensionnement des images
     */
    #[RunInSeparateProcess]
    public function testRedimensionnementImages(): void
    {
        require 'config/config.php';
        $monImage = new ImageObject();

        /*
         * Cas incohérents => Ne rien faire
         */
        $this->assertFalse(
            $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 600, 800),
            'Pas d\'agrandissement'
        );
        $this->assertFalse(
            $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 601, 800),
            'Pas d\'agrandissement'
        );
        $this->assertFalse(
            $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 600, 801),
            'Pas d\'agrandissement'
        );
        $this->assertFalse(
            $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 599, 800),
            'Pas d\'agrandissement'
        );
        $this->assertFalse(
            $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 600, 799),
            'Pas d\'agrandissement'
        );
        $this->assertFalse(
            $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 0, 799),
            'Image de taille zéro'
        );
        $this->assertFalse(
            $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 0, 0),
            'Image de taille zéro'
        );
        $this->assertFalse(
            $monImage->redimensionner(_PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png', '', 10, 0),
            'Image de taille zéro'
        );

        /*
         * Format portrait
         */
        // Doit être 200x267
        $monImage->redimensionner(
            _PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png',
            _PATH_TESTS_OUTPUT_ . 'image_portrait_200x400.png',
            200,
            400
        );
        $this->assertTrue(
            $this->compareImages(
                _PATH_TESTS_IMAGES_ . 'image_portrait_200x400.png',
                _PATH_TESTS_OUTPUT_ . 'image_portrait_200x400.png'
            ),
            'Redimensionnement portrait 200x400'
        );

        // Doit être 150x200
        $monImage->redimensionner(
            _PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png',
            _PATH_TESTS_OUTPUT_ . 'image_portrait_400x200.png',
            400,
            200
        );
        $this->assertTrue(
            $this->compareImages(
                _PATH_TESTS_IMAGES_ . 'image_portrait_400x200.png',
                _PATH_TESTS_OUTPUT_ . 'image_portrait_400x200.png'
            ),
            'Redimensionnement portrait 400x200'
        );

        /*
         * Format paysage
         */
        // Doit être 267x200
        $monImage->redimensionner(
            _PATH_TESTS_IMAGES_ . 'image_paysage_800x600.png',
            _PATH_TESTS_OUTPUT_ . 'image_paysage_400x200.png',
            400,
            200
        );
        $this->assertTrue(
            $this->compareImages(
                _PATH_TESTS_IMAGES_ . 'image_paysage_400x200.png',
                _PATH_TESTS_OUTPUT_ . 'image_paysage_400x200.png'
            ),
            'Redimensionnement paysage 400x200'
        );

        // Doit être 200x150
        $monImage->redimensionner(
            _PATH_TESTS_IMAGES_ . 'image_paysage_800x600.png',
            _PATH_TESTS_OUTPUT_ . 'image_paysage_200x400.png',
            200,
            400
        );
        $this->assertTrue(
            $this->compareImages(
                _PATH_TESTS_IMAGES_ . 'image_paysage_200x400.png',
                _PATH_TESTS_OUTPUT_ . 'image_paysage_200x400.png'
            ),
            'Redimensionnement paysage 200x400'
        );
    }
}
