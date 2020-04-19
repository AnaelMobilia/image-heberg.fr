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

class abuseTest extends TestCase
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
        $_POST['urlImage'] = "http://www.example.com/files/imageQuiSeraBloquee.png";

        ob_start();
        require 'abuse.php';
        ob_end_clean();

        $imageBloquee = new imageObject("15.png");
        $imageMemeMd5 = new imageObject("16.png");
        $this->assertEquals(true, $imageBloquee->isSignalee(), "Image signalée doit l'être");
        $this->assertEquals(true, $imageMemeMd5->isSignalee(), "Image avec même MD5 qu'une image signalée doit l'être également");
    }

}
