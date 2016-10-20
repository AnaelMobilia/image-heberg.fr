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

//**************************************
//	./includes/debug.php
//	Librairie - d�buggage
//**************************************
/**
 * Calcule et affiche la dur�e d'ex�cution du script
 *
 * @param float $time_start : temps au d�but du script PHP
 * @return string
 */
function duree_exec($time_start) {
    if (__DEBUG__) {
        $time_end = microtime(TRUE); //retour sous forme de float

        $duration = round($time_end - $time_start, 5); //dur�e du script
        $unit = 'seconde' . ($duration <= 2 ? '' : 's'); //unit�

        return "Script PHP ex&eacute;cut&eacute; en $duration $unit";
    }
}
