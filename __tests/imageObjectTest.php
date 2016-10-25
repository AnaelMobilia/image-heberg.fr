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

class imageObjectTest extends PHPUnit_Framework_TestCase {
    private $maBDD;

    function __construct() {
        global $maBDD;
        // BDD
        define('_BDD_HOST_', 'localhost');
        define('_BDD_USER_', 'root');
        define('_BDD_PASS_', '');
        define('_BDD_NAME_', 'imageheberg');

        // Fonction de chargement des classes en cas de besoin
        spl_autoload_register(function ($class) {
            include _PATH_ . 'classes/' . $class . '.class.php';
        });

        // Connexion centralisée à la BDD
        $maBDD = new PDO('mysql:host=' . _BDD_HOST_ . ';dbname=' . _BDD_NAME_, _BDD_USER_, _BDD_PASS_);
        $maBDD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $maBDD->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

    public function testPushAndPop() {
        $stack = array();
        $this->assertEquals(10, count($stack));

        array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[count($stack) - 1]);
        $this->assertEquals(1, count($stack));

        $this->assertEquals('foo', array_pop($stack));
        $this->assertEquals(0, count($stack));
    }

}