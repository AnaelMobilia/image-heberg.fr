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

use PHPUnit\Framework\TestCase;

class installationTest extends TestCase {

    public function testVaidationInstallation() {
        /**
         * Valeur attendue
         */
        $this->expectOutputString("L'installation est OK !");

        /**
         * Exécution du script...
         */
        require 'install.php';
    }

}
