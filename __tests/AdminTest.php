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

use ImageHeberg\HelperAdmin;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class AdminTest extends TestCase
{
    /**
     * Fichiers qui n'ont jamais été utilisés
     */
    #[RunInSeparateProcess]
    public function testGetNeverUsedFiles(): void
    {
        require 'config/config.php';

        $filesToDelete = HelperAdmin::getNeverUsedFiles();

        $this->assertContains(
            'image_36.png',
            $filesToDelete,
            'neverUsedFileWithoutThumbs.png doit être détectée'
        );
        $this->assertContains(
            'image_37.png',
            $filesToDelete,
            'neverUsedFileWithThumbsNotDisplayed.png doit être détectée'
        );
        $this->assertNotContains(
            'image_38.png',
            $filesToDelete,
            'neverUsedFileWithThumbsDisplayed.png ne doit pas être détectée (miniatures affichées récemment)'
        );
        $this->assertNotContains(
            'image_39.png',
            $filesToDelete,
            'neverUsedFileWithoutThumbsButOwned.png ne doit pas être détectée (image possédée par un utilisateur)'
        );
        $this->assertNotContains(
            'image_40.png',
            $filesToDelete,
            'expiredFileWithoutThumbs.png ne doit pas être détectée (image utilisée)'
        );
        $this->assertNotContains(
            'image_41.png',
            $filesToDelete,
            'expiredFileWithThumbsNotBetter.png ne doit pas être détectée (image utilisée)'
        );
        $this->assertNotContains(
            'image_42.png',
            $filesToDelete,
            'expiredFileWithThumbsDisplayedRecently.png ne doit pas être détectée (image utilisée)'
        );
        $this->assertNotContains(
            'image_43.png',
            $filesToDelete,
            'expiredFileWithoutThumbsAndOwned.png ne doit pas être détectée (image utilisée)'
        );
        $this->assertNotContains(
            'image_44.png',
            $filesToDelete,
            'neverUsedFileWithThumbsDisplayedLongTimeAgo.png ne doit pas être détectée (miniature déjà affichée)'
        );
    }

    /**
     * Fichiers qui ne sont plus utilisés selon les règles de conservation
     */
    #[RunInSeparateProcess]
    public function testGetUnusedFiles(): void
    {
        require 'config/config.php';

        $filesToDelete = HelperAdmin::getUnusedFiles();

        $this->assertNotContains(
            'image_36.png',
            $filesToDelete,
            'neverUsedFileWithoutThumbs.png ne doit pas être détectée (non concernée par ce test)'
        );
        $this->assertNotContains(
            'image_37.png',
            $filesToDelete,
            'neverUsedFileWithThumbsNotDisplayed.png ne doit pas être détectée (non concernée par ce test)'
        );
        $this->assertNotContains(
            'image_38.png',
            $filesToDelete,
            'neverUsedFileWithThumbsDisplayed.png ne doit pas être détectée (non concernée par ce test)'
        );
        $this->assertNotContains(
            'image_39.png',
            $filesToDelete,
            'neverUsedFileWithoutThumbsButOwned.png ne doit pas être détectée (non concernée par ce test)'
        );
        $this->assertContains(
            'image_40.png',
            $filesToDelete,
            'expiredFileWithoutThumbs.png doit être détectée'
        );
        $this->assertContains(
            'image_41.png',
            $filesToDelete,
            'expiredFileWithThumbsNotBetter.png doit être détectée'
        );
        $this->assertNotContains(
            'image_42.png',
            $filesToDelete,
            'expiredFileWithThumbsDisplayedRecently.png ne doit pas être détectée (miniature utilisée)'
        );
        $this->assertNotContains(
            'image_43.png',
            $filesToDelete,
            'expiredFileWithoutThumbsAndOwned.png ne doit pas être détectée (image possédée)'
        );
        $this->assertContains(
            'image_44.png',
            $filesToDelete,
            'neverUsedFileWithThumbsDisplayedLongTimeAgo.png doit être détectée (miniature affichée il y a trop longtemps)'
        );
    }


    /**
     * Les comptes admin ne doivent pas être détectés comme inactifs
     */
    #[RunInSeparateProcess]
    public function testProtectionCompteAdmin(): void
    {
        require 'config/config.php';

        $inactiveAccounts = HelperAdmin::getInactiveAccounts();
        $unusedAccounts = HelperAdmin::getUnusedAccounts();

        $this->assertContains(
            4,
            $inactiveAccounts,
            'Le compte 4 doit être détecté'
        );
        $this->assertContains(
            4,
            $unusedAccounts,
            'Le compte 4 doit être détecté'
        );
        $this->assertNotContains(
            3,
            $inactiveAccounts,
            'Le compte 3 ne doit pas être détecté'
        );
        $this->assertNotContains(
            3,
            $unusedAccounts,
            'Le compte 3 ne doit pas être détecté'
        );
    }

    /**
     * Détection des comptes qui n'ont jamais été utilisés
     */
    #[RunInSeparateProcess]
    public function testUnusedAccounts(): void
    {
        require 'config/config.php';

        $unusedAccounts = HelperAdmin::getUnusedAccounts();

        $this->assertContains(
            5,
            $unusedAccounts,
            'Le compte 5 doit être détecté'
        );
        $this->assertNotContains(
            6,
            $unusedAccounts,
            'Le compte 6 ne doit pas être détecté'
        );
    }


    /**
     * Détection des comptes inactifs
     */
    #[RunInSeparateProcess]
    public function testInactiveAccounts(): void
    {
        require 'config/config.php';

        $inactiveAccounts = HelperAdmin::getInactiveAccounts();

        $this->assertContains(
            4,
            $inactiveAccounts,
            'Le compte 4 doit être détecté'
        );
        $this->assertNotContains(
            5,
            $inactiveAccounts,
            'Le compte 5 ne doit pas être détecté'
        );
    }
}
