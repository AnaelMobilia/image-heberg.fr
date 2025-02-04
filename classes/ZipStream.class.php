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

/**
 * Envoyer un contenu en tant que Zip via un flux
 * Implémente les spécifications 6.3.10 (01/11/2022)
 * @see https://pkware.cachefly.net/webdocs/casestudies/APPNOTE.TXT
 *
 * /!\ Utilisation de little-endian /!\ (inversion de l'ordre des bits par rapport à la doc)
 */
class ZipStream
{
    /**
     * Ajouter des fichiers dans un fichier ZIP qui est streamé à la volée
     *
     * @param array $files ["Nom dans le ZIP" => "chemin sur le disque"]
     */
    public static function addFiles(array $files): void
    {
        // Timestamp actuel au format MS-DOS ("date in high two bytes, time in low two bytes allowing magnitude comparison).")
        $timearray = getdate();
        $currDate = (($timearray['year'] - 1980) << 25)
            | ($timearray['mon'] << 21)
            | ($timearray['mday'] << 16)
            | ($timearray['hours'] << 11)
            | ($timearray['minutes'] << 5)
            | ($timearray['seconds'] >> 1);
        unset($timearray);

        $offsetLocalHeader = 0;
        $centralDirectory = [];

        // Traitement de tous les fichiers...
        foreach ($files as $fileName => $fileWithPath) {
            $fileContent = file_get_contents($fileWithPath);

            /**
             * 4.3.7  Local file header
             */
            // local file header signature
            $buffer = "\x50\x4b\x03\x04";
            // version needed to extract
            $buffer .= "\x14\x00";
            // general purpose bit flag
            $buffer .= "\x00\x00";
            // compression method
            $buffer .= "\x08\x00";
            // last mod file time
            // last mod file date
            $hexaDateTime = pack('V', $currDate);
            $buffer .= $hexaDateTime;
            // crc-32
            $crcContent = crc32($fileContent);
            $buffer .= pack('V', $crcContent);
            // compressed size
            $compressedContent = gzcompress($fileContent, 9);
            $compressedContent = substr(substr($compressedContent, 0, -4), 2); // fix crc bug
            $compressedContentLenght = strlen($compressedContent);
            $buffer .= pack('V', $compressedContentLenght);
            // uncompressed size
            $lengthContent = strlen($fileContent);
            $buffer .= pack('V', $lengthContent);
            // file name length
            $buffer .= pack('v', strlen($fileName));
            // extra field length
            $buffer .= pack('v', 0);
            // file name (variable size)
            $buffer .= $fileName;

            /**
             * 4.3.8  File data
             */
            $buffer .= $compressedContent;
            echo $buffer;
            // Requis pour le central directory
            $bufferSize = strlen($buffer);

            /**
             * 4.3.12  Central directory structure
             */
            // central file header signature
            $buffer = "\x50\x4b\x01\x02";
            // version made by
            $buffer .= "\x3F\x00"; // Version 6.3
            // version needed to extract
            $buffer .= "\x14\x00"; //
            // general purpose bit flag
            $buffer .= "\x00\x00";
            // compression method
            $buffer .= "\x08\x00";
            // last mod file time
            // last mod file date
            $buffer .= $hexaDateTime;
            // crc-32
            $buffer .= pack('V', $crcContent);
            // compressed size
            $buffer .= pack('V', $compressedContentLenght);
            // uncompressed size
            $buffer .= pack('V', $lengthContent);
            // file name length
            $buffer .= pack('v', strlen($fileName));
            // extra field length
            $buffer .= pack('v', 0);
            // file comment length
            $buffer .= pack('v', 0);
            // disk number start
            $buffer .= pack('v', 0);
            // internal file attributes
            $buffer .= pack('v', 0);
            // external file attributes
            $buffer .= pack('V', 32);
            // relative offset of local header
            $buffer .= pack('V', $offsetLocalHeader);
            $offsetLocalHeader += $bufferSize;
            // file name (variable size)
            $buffer .= $fileName;
            // extra field (variable size)
            // file comment (variable size)

            // Stocker dans le central directory (sera envoyé à la fin de l'archive)
            $centralDirectory[] = $buffer;
        }

        /**
         * 4.3.12  Central directory structure
         */
        $centralDirectoryStr = implode('', $centralDirectory);
        echo $centralDirectoryStr;
        /**
         * 4.3.16  End of central directory record
         */
        // end of central dir signature
        echo "\x50\x4b\x05\x06\x00\x00\x00\x00";
        // number of this disk
        echo pack('v', 0);
        // number of the disk with the start of the central directory
        echo pack('v', 0);
        // total number of entries in the central directory on this disk
        echo pack('v', count($centralDirectory));
        // total number of entries in the central directory
        echo pack('v', count($centralDirectory));
        // size of the central directory
        echo pack('V', strlen($centralDirectoryStr));
        // offset of start of central directory with respect to the starting disk number
        echo pack('V', $offsetLocalHeader);
        // .ZIP file comment length
        echo "\x00\x00";
        // .ZIP file comment
    }
}