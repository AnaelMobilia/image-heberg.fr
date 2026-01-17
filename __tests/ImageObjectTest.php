<?php

/*
 * Copyright 2008-2026 Anael MOBILIA
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
use ImageHeberg\RessourceObject;
use Imagick;
use ImagickException;
use ImagickPixelException;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * L'entête des fichiers contient des informations sur la bibliothéque système les ayant produit
 * <CREATOR: gd-jpeg v1.0 (using IJG JPEG v80), quality = 100
 */
class ImageObjectTest extends TestCase
{
    // Rotation pour les images
    private const array ROTATION_ANGLES = [0, 90, 180, 270];
    // Couleurs des 4 coins : HG / BG / BD / HD
    private const array ROTATION_COULEURS = [
        0 => [
            'r' => 255,
            'g' => 0,
            'b' => 0,
        ],
        1 => [
            'r' => 0,
            'g' => 255,
            'b' => 0,
        ],
        2 => [
            'r' => 0,
            'g' => 0,
            'b' => 255,
        ],
        3 => [
            'r' => 255,
            'g' => 255,
            'b' => 255,
        ],
    ];
    // Dimensions d'origine de l'image
    private const array ROTATION_DIM_ORIGINE = [640, 150];

    /**
     * Les couleurs des images sont conservées à +/- 1 point de valeur
     * @param array $reference Couleur de référence
     * @param array $valeur Couleur à comparer
     * @return bool
     */
    private function compareColor(array $reference, array $valeur): bool
    {
        $monRetour = true;
        foreach ($reference as $key => $value) {
            if (
                $valeur[$key] !== $value
                && $valeur[$key] !== ($value - 1)
                && $valeur[$key] !== ($value + 1)
            ) {
                $monRetour = false;
                break;
            }
        }
        // Debug
        if (!$monRetour) {
            echo PHP_EOL . 'Expected : ' . var_export($reference, true) . PHP_EOL . 'Actual : ' . var_export($valeur, true) . PHP_EOL;
        }

        return $monRetour;
    }

    /**
     * Rotation des images
     * @throws ImagickPixelException
     * @throws ImagickException
     */
    #[RunInSeparateProcess]
    public function testRotationImages(): void
    {
        require 'config/config.php';

        $monImage = new ImageObject();

        foreach (_ACCEPTED_EXTENSIONS_ as $uneExtension) {
            foreach (self::ROTATION_ANGLES as $unAngle) {
                $monImage->rotation(
                    $unAngle,
                    _PATH_TESTS_IMAGES_ . 'rotation_original.' . $uneExtension,
                    _PATH_TESTS_OUTPUT_ . 'rotation_original-' . $unAngle . '.' . $uneExtension
                );

                // Calcul des dimensions théoriques
                $indiceLargeur = (($unAngle % 180) === 0 ? 0 : 1);
                $indiceHauteur = (($unAngle % 180) === 0 ? 1 : 0);

                // Vérifier les dimensions des images
                $imageInfo = getimagesize(_PATH_TESTS_OUTPUT_ . 'rotation_original-' . $unAngle . '.' . $uneExtension);
                $this->assertEquals(
                    self::ROTATION_DIM_ORIGINE[$indiceLargeur],
                    $imageInfo[0],
                    'Largeur d\'image - Rotation ' . $uneExtension . ' ' . $unAngle
                );
                $this->assertEquals(
                    self::ROTATION_DIM_ORIGINE[$indiceHauteur],
                    $imageInfo[1],
                    'Hauteur d\'image - Rotation ' . $uneExtension . ' ' . $unAngle
                );

                // Vérifier les couleurs
                $image = new Imagick(_PATH_TESTS_OUTPUT_ . 'rotation_original-' . $unAngle . '.' . $uneExtension);
                $this->assertTrue(
                    $this->compareColor(self::ROTATION_COULEURS[round($unAngle / 90)], $image->getImagePixelColor(0, 0)->getColor()),
                    'Pixel (0,0) - Couleur ' . $uneExtension . ' - Rotation ' . $unAngle
                );
                $this->assertEquals(
                    $this->compareColor(self::ROTATION_COULEURS[(round($unAngle / 90) + 1) % 4], $image->getImagePixelColor(0, self::ROTATION_DIM_ORIGINE[$indiceHauteur])->getColor()),
                    'Pixel (0,' . self::ROTATION_DIM_ORIGINE[$indiceHauteur] . ') - Couleur ' . $uneExtension . ' - Rotation ' . $unAngle
                );
                $this->assertEquals(
                    $this->compareColor(self::ROTATION_COULEURS[(round($unAngle / 90) + 2) % 4], $image->getImagePixelColor(self::ROTATION_DIM_ORIGINE[$indiceLargeur], self::ROTATION_DIM_ORIGINE[$indiceHauteur])->getColor()),
                    'Pixel (' . self::ROTATION_DIM_ORIGINE[$indiceLargeur] . ',' . self::ROTATION_DIM_ORIGINE[$indiceHauteur] . ') - Couleur ' . $uneExtension . ' - Rotation ' . $unAngle
                );
                $this->assertEquals(
                    $this->compareColor(self::ROTATION_COULEURS[(round($unAngle / 90) + 3) % 4], $image->getImagePixelColor(self::ROTATION_DIM_ORIGINE[$indiceLargeur], 0)->getColor()),
                    'Pixel (' . self::ROTATION_DIM_ORIGINE[$indiceLargeur] . ',0) - Couleur ' . $uneExtension . ' - Rotation ' . $unAngle
                );
            }
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
        $imageInfo = getimagesize(_PATH_TESTS_OUTPUT_ . 'image_portrait_200x400.png');
        $this->assertEquals(
            200,
            $imageInfo[0],
            'Redimensionnement 600x800 -> 200x400'
        );
        $this->assertEquals(
            267,
            $imageInfo[1],
            'Redimensionnement 600x800 -> 200x400'
        );

        // Doit être 150x200
        $monImage->redimensionner(
            _PATH_TESTS_IMAGES_ . 'image_portrait_600x800.png',
            _PATH_TESTS_OUTPUT_ . 'image_portrait_400x200.png',
            400,
            200
        );
        $imageInfo = getimagesize(_PATH_TESTS_OUTPUT_ . 'image_portrait_400x200.png');
        $this->assertEquals(
            150,
            $imageInfo[0],
            'Redimensionnement 600x800 -> 400x200'
        );
        $this->assertEquals(
            200,
            $imageInfo[1],
            'Redimensionnement 600x800 -> 400x200'
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
        $imageInfo = getimagesize(_PATH_TESTS_OUTPUT_ . 'image_paysage_400x200.png');
        $this->assertEquals(
            267,
            $imageInfo[0],
            'Redimensionnement 800x600 -> 400x200'
        );
        $this->assertEquals(
            200,
            $imageInfo[1],
            'Redimensionnement 800x600 -> 400x200'
        );

        // Doit être 200x150
        $monImage->redimensionner(
            _PATH_TESTS_IMAGES_ . 'image_paysage_800x600.png',
            _PATH_TESTS_OUTPUT_ . 'image_paysage_200x400.png',
            200,
            400
        );
        $imageInfo = getimagesize(_PATH_TESTS_OUTPUT_ . 'image_paysage_200x400.png');
        $this->assertEquals(
            200,
            $imageInfo[0],
            'Redimensionnement 800x600 -> 200x400'
        );
        $this->assertEquals(
            150,
            $imageInfo[1],
            'Redimensionnement 800x600 -> 200x400'
        );
    }

    /**
     * Génération de miniatures d'aperçus
     */
    #[RunInSeparateProcess]
    public function testGenerationMiniaturesPreview(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $monImage = new ImageObject();

        /**
         * Génération d'une miniature d'aperçu
         */
        $monImage->charger('98', RessourceObject::SEARCH_BY_ID);
        $this->assertCount(
            0,
            $monImage->getMiniatures(true),
            'Avant génération de la miniature d\'aperçu, on en a aucune'
        );
        $miniature = $monImage->getPreviewMiniature();
        $this->assertCount(
            1,
            $monImage->getMiniatures(true),
            'Après la génération de la miniature d\'aperçu, on en a une'
        );
        $this->assertLessThanOrEqual(
            _SIZE_PREVIEW_,
            $miniature->getHauteur(),
            'Respect des dimensions (hauteur) maximale d\'une miniature d\'aperçu'
        );
        $this->assertLessThanOrEqual(
            _SIZE_PREVIEW_,
            $miniature->getLargeur(),
            'Respect des dimensions (largeur) maximale d\'une miniature d\'aperçu'
        );

        /**
         * Pas de génération pour les WebP animés
         */
        $monImage->charger('97', RessourceObject::SEARCH_BY_ID);
        $this->assertEquals(
            $monImage->getMiniatures(true)->count(),
            0,
            'Avant génération de la miniature d\'aperçu d\'une image WebP animée, on en a aucune'
        );
        $monImage->getPreviewMiniature();
        $this->assertEquals(
            $monImage->getMiniatures(true)->count(),
            0,
            'Après la génération de la miniature d\'aperçu d\'une image WebP animée, on en a toujours pas car ce n\'est pas pris en charge.'
        );
    }

    #[RunInSeparateProcess]
    public function testClassHtml(): void
    {
        require 'config/config.php';
        $monImage = new ImageObject();

        $monImage->charger('35', RessourceObject::SEARCH_BY_ID);
        $this->assertEquals(
            'approuver',
            $monImage->getHtmlClass(),
            'Classe HTML d\'une image validée'
        );
        $monImage->charger('10', RessourceObject::SEARCH_BY_ID);
        $this->assertEquals(
            'bloquer',
            $monImage->getHtmlClass(),
            'Classe HTML d\'une image bloquée'
        );
    }
}
