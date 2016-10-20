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

/**
 * Lien vers la BDD
 *
 * @author anael
 */
class maDB extends PDO {
// http://stackoverflow.com/questions/5484790/auto-connecting-to-pdo-only-if-needed
    protected $_config = array();
    protected $_connected = false;

    public function __construct($dsn, $user = null, $pass = null, $options = null) {
        //Save connection details for later
        $this->_config = array(
            'dsn' => $dsn,
            'user' => $user,
            'pass' => $pass,
            'options' => $options
        );
    }

    public function makeConnection() {
        extract($this->_config);
        parent::__construct($dsn, $user, $pass, $options);
        $this->_connected = true;

        // Params...
        parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        parent::setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

    public function query($query) {
        if (!$this->_connected) {
            $this->makeConnection();
        }
        return parent::query($query);
    }

// Ca chie sur la ligne d'après

    public function prepare($statement, array $driver_options = []) {
        if (!$this->_connected) {
            $this->makeConnection();
        }
        parent::prepare($statement, $driver_options);
    }

}