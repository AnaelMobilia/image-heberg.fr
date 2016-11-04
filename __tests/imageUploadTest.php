<?php
/*
 * Copyright 2008-2016 Anael Mobilia
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

class imageUploadTest extends PHPUnit_Framework_TestCase {
    // 404, banned, une image bloquée
    const nbImagesParDefaut = 3;

    /**
     * Nombre d'images en BDD
     * @return int
     */
    private static function countImagesEnBdd() {
        $maReq = maBDD::getInstance()->query("SELECT COUNT(*) AS nb FROM images");
        $result = $maReq->fetch();
        return $result->nb;
    }

    /**
     * Envoi flood (sans affichage page index.php
     * => $erreur = TRUE
     * @runInSeparateProcess
     */
    public function testEnvoiFlood() {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_POST['Submit'] = 1;

        ob_start();
        require 'upload.php';
        ob_end_clean();
        $this->assertEquals($erreur, TRUE, "Non affichage du formulaire d'upload devrait être détecté dans upload.php");
        $this->assertEquals(self::countImagesEnBdd(), self::nbImagesParDefaut, "Non affichage du formulaire d'upload ne doit pas créer d'image en BDD");
    }

    /**
     * Poids
     */
    /**
     * Type mime
     * bonne ext mais mauvais fic
     * mauvaise ext mais bon fic
     * mauvais fic & mauvaise extension
     */
    /**
     * Taille trop grande
     * trop long
     * trop large
     * trop tout
     */
    /**
     * envoi et renvoi
     * => $doublon
     */
    /**
     *  Envoi d'une image
     * -> présence sur hdd
     * -> présence sur BDD
     * $erreur = FALSE
     */
}