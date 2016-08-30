<?php
/*
* Copyright 2008-2015 Anael Mobilia
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
//	./includes/pictures.php
//	Gestion, v�rification des images
//**************************************
/**
 * S�rialise le nom du fichier image
 *
 * @param string $filename : nom du fichier entrant
 * @return string $new_filename : nom corrig� du fichier
 */
function filename_serialize($filename)
{	// Caract�res autoris�s : a->z A->Z 0->9 - _ ( ) .
	// Remplacement des caract�res non autoris�s par _
	$filename = preg_replace('#[^a-zA-Z0-9_()-.]#', '_', $filename);
	// Remplacement de .jpeg -> .jpg - insensible � la casse
	$filename = preg_replace('#(.)\.jpeg$#i', '$1.jpg', $filename);
	// Extension en minuscule - insensible � la casse
	$filename = preg_replace_callback('#(.*)\.(.*)$#', create_function('$a', 'return $a[1] . "." . strtolower($a[2]);'), $filename);
	//array(3) { [0]=> string(9) "to.to.JPG" [1]=> string(5) "to.to" [2]=> string(3) "JPG" }

	return $filename;
}

/**
 * D�termine le type du fichier
 *
 * @param string $filename : nom du fichier
 * @return string : extension du fichier
 */
function get_type($filename)
{
	// R�cup�ration de l'extension du fichier
	preg_match('#.*\.(.*)$#', $filename, $file_ext);

	// Assure le renvoi d'une valeur
	if(isset($file_ext[1])) {
		$file_ext = $file_ext[1];
	}
	else {
		$file_ext = FALSE;
	}
	// Retourne le type
	return $file_ext;
}

/**
 * V�rifie le type (extension) du fichier
 *
 * @param string $file_ext : extension du fichier
 * @return boolean : r�sultat de la v�rification
 */
function is_allowed_type($file_ext)
{
	// R�cup�ration des extensions autoris�es
	$ext_ok = explode(', ', __EXTENSIONS_OK__);

	if(in_array($file_ext, $ext_ok))
	{
		$retour = TRUE;
	}
	else
	{
		$retour = FALSE;
	}
	return $retour;
}

/**
 * V�rifie le type (MIME) du fichier
 *
 * @param string $file : chemin du fichier
 * @param string $ext : type du fichier
 * @return boolean $picture : r�sultat de la v�rification
 */
function is_picture($file, $ext)
{
	/*
	php5.3 -> http://fr.php.net/manual/en/ref.fileinfo.php
	*/

	// R�cup�ration du mime-type
	$infos = getimagesize($file);

	// Si erreur <-> ce n'est pas une image
	if($infos == false) {
		$picture = false;
	}
	else {
		// Mime-Type en indice 2
		$file_mime = $infos[2];
		// V�rifications de la coh�rence mime-type / extension
		if($file_mime == 1 && $ext == 'gif'		// 'IMAGETYPE_GIF'
		|| $file_mime == 2 && $ext == 'jpg'		// 'IMAGETYPE_JPEG'
		|| $file_mime == 3 && $ext == 'png'		// 'IMAGETYPE_PNG'
		) {
			//$picture['height'] = $infos[1];		// Hauteur
			//$picture['width'] = $infos[O];		// Largeur
			$picture = true;
		}
		else {	// V�rification -> non coh�rent
			$picture = false;
		}
	}

	// Retour : �tat de la v�rification
	return $picture;
}
?>