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

namespace ImageHeberg;

use Exception;

/**
 * Exception custom à l'application
 */
class ImageHebergException extends Exception
{
    /**
     * Définir les champs custom de l'exception
     *
     * @param string $message Message d'erreur
     * @param int $code Code de l'exception
     * @param string $file Fichier où l'erreur à eu lieu
     * @param int $line Line concernée
     * @return void
     */
    public function define(string $message, int $code, string $file, int $line): void
    {
        $this->message = $message;
        $this->code = $code;
        $this->file = $file;
        $this->line = $line;
    }
}
