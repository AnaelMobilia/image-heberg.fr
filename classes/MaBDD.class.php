<?php

/*
 * Copyright 2008-2025 Anael MOBILIA
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

namespace ImageHeberg;

use PDO;
use PDOStatement;

/**
 * Lien vers la BDD
 *
 * @author anael
 */
class MaBDD
{
    // PDO
    private PDO $maBDD;
    // Instance de la classe
    private static ?MaBDD $monInstance = null;

    /**
     * Constructeur
     */
    private function __construct()
    {
        $this->maBDD = new PDO('mysql:host=' . _BDD_HOST_ . ';dbname=' . _BDD_NAME_, _BDD_USER_, _BDD_PASS_);
        $this->maBDD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->maBDD->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

    /**
     * Crée & renvoi l'objet d'instance
     * @return MaBDD
     */
    public static function getInstance(): MaBDD
    {
        // Si pas de connexion active, en crée une
        if (is_null(self::$monInstance)) {
            self::$monInstance = new MaBDD();
        }
        return self::$monInstance;
    }

    /**
     * PDO::query
     * @param string $query
     * @return false|PDOStatement
     */
    public function query(string $query): bool|PDOStatement
    {
        return $this->maBDD->query($query);
    }

    /**
     * PDO::prepare
     * @param string $query
     * @return false|PDOStatement
     */
    public function prepare(string $query): bool|PDOStatement
    {
        return $this->maBDD->prepare($query);
    }

    /**
     * PDO::lastInsertId
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->maBDD->lastInsertId();
    }

    /**
     * Fermeture du PDO
     */
    public static function close(): void
    {
        self::$monInstance = null;
    }

    /**
     * PDO::getAttribute
     * @param int $attribute
     * @return mixed
     */
    public function getAttribute(int $attribute): mixed
    {
        return $this->maBDD->getAttribute($attribute);
    }
}
