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
//	./thumbs.php
//	Génération de miniature
//**************************************
//TODO : reprendre la fonction -> ne pas recalculer des variables d�j� connues !!!
//---------------------------
//	GENERE UNE MINIATURE
// Hauteur/largeur image, nom nouveau fichier, connection bdd, config.php
//---------------------------
//error_log('thumbs');
if(!defined('__IMAGE_HEBERG__'))
{
	require_once("./config/config.php");
	erreur('HACK', 'upload_script');		//acc�s direct au fichier non autoris�
	//error_log('die - thumbs.php');
        die();
}

$lang['CORPS'] .= '<p>Miniature</p>';
//-------------
//	VARIABLES
//-------------
$t_name = __T_TARGET__ . $new_name;		//d�finition du path/nom de la thumbs
//$t_name		= substr($t_name, 0, -4) . ".jpg";							//Thumbs en jpg only
$display = FALSE;						//rendu html final
$p_image = __PATH__ . __TARGET__ . $new_name;

//-----------------------
//	FONCTION CREATRICE
//-----------------------
/**
 * Cr�ation de miniatures
 *
 * @param string $path : ??
 * @param string $p_image : chemin physique de l'image
 * @param string $t_name : chemin relatif de l'image
 * @param integer $width : largeur de l'image
 * @param integer $height : hauteur de l'image
 * @param integer $t_height : largeur de la miniature
 * @param integer $t_width : hauteur de la miniature
 * @return boolean : r�sultat de la fonction
 */
function make_thumbs ($path, $p_image, $t_name, $width, $height, $t_height, $t_width)
{
    // TODO : catcher PROPREMENT ces putains d'erreurs.... crash silencieux sur PHP Fatal error:  Call to undefined function imagecreatefromjpeg() in /var/www/image-heberg.fr/www/thumbs.php on line 53, referer: http://www.image-heberg.fr/
    // @imagecreatefromjpeg
    //error_log('make_thumbs');
	$retour			= 0;	//variable retour
	$extension	= substr($p_image, -3);
	$extension	= strtolower($extension);			//on met en minuscule
	$t_name = substr($t_name, 0, -3) . $extension;	//on cr�e le type de la miniature

	if(
	!(($extension == 'jpg') && (!$image = @imagecreatefromjpeg($p_image)))
	&& !(($extension == 'gif') && (!$image = @imagecreatefromgif($p_image)))
	&& !(($extension == 'png') && (!$image = @imagecreatefrompng($p_image)))
	)	//2 req FALSE sur $extension, troisieme > image..., imagecreatefrom retourne FALSE en cas d'erreur
	{	// charge en m�moire une image du fichier
		//if(($miniature = @imagecreatetruecolor($t_width, $t_height)) != FALSE)
            if(($miniature = imagecreatetruecolor($t_width, $t_height)) != FALSE)
		{ // cr�ation image vierge
			//if(@imagecopyresampled($miniature, $image, 0,0, 0,0, $t_width,$t_height, $width,$height))
                if(imagecopyresampled($miniature, $image, 0,0, 0,0, $t_width,$t_height, $width,$height))
			{ // On cr�e la miniature
				if($extension == 'jpg')	//miniature jpg
				{
					if(@imagejpeg($miniature, $path . $t_name, 100))
                                        {// On enregistre la miniature
						$retour = 1;
					}
					else	//Erreur � l'enregistrement
					{
						retour_erreur('<p>Erreur #0 : Impossible d\'enregistrer le fichier '.$t_name.'</p>', __FILE__, 'warning');
					}
				}
				elseif($extension == 'gif')	//miniature gif
				{
					if(@imagegif($miniature, $path . $t_name))	//pas de gestion de qualit� en gif
					{// On enregistre la miniature
						$retour = 1;
					}
					else	//Erreur � l'enregistrement
					{
						retour_erreur('<p>Erreur #0 : Impossible d\'enregistrer le fichier '.$t_name.'</p>', __FILE__, 'warning');
					}
				}
				elseif($extension == 'png')
				{
					if(@imagepng($miniature, $path . $t_name, 9))
					{// On enregistre la miniature
						$retour = 1;
					}
					else	//Erreur � l'enregistrement
					{
						retour_erreur('<p>Erreur #0 : Impossible d\'enregistrer le fichier '.$t_name.'</p>', __FILE__, 'warning');
					}
				}
				else	//cas impossible
				{
					retour_erreur('Erreur de type de miniature</p>', __FILE__, 'die');
				}
			}
			else	//erreur resize
			{
				retour_erreur('<p>Erreur #2 : Impossible de redimensionner l\'image</p>', __FILE__, 'die');
			}

			if(!imageDestroy ($miniature))
			{
				retour_erreur('<p>Erreur #3 &agrave; la d&eacute;salocation m&eacute;moire de la miniature</p>', __FILE__, 'warning');
			}
		}
		else	//erreur cr�ation image blanche
		{
			retour_erreur('<p>Erreur #4 : Impossible de cr&eacute;er une image vierge</p>', __FILE__, 'warning');
		}

                if(!@imageDestroy ($image))
		{
			retour_erreur('<p>Erreur #5 &agrave; la d&eacute;salocation m&eacute;moire de l\'image</p>', __FILE__, 'warning');
		}
	}
	else	//erreur $new_name
	{
		retour_erreur('<p>Erreur #6 : Impossible d\'ouvrir le fichier '.$p_image.'<br />L\'extension du fichier est certainement &eacute;ronn&eacute;e...</p>', __FILE__, 'warning');
	}
       // error_log('fin make_thumbs');
	return $retour;
}


//--------------
//	Protection
//--------------
if($t_width == 0 || $t_height == 0)	//Dimension miniature nulle
{
	$t_width = __DEFAULT_T_WIDTH__;	//dim par d�faut
	$t_height = __DEFAULT_T_HEIGHT__;
	retour_erreur('<p>Dimensions de la miniature incorrectes, utilisation des valeurs par d&eacute;faut.</p>', __FILE__, 'warning');
}

//-------------------------------------------------
// l'image de base est plus petite que la miniature
//-------------------------------------------------
if($width < $t_width && $height < $t_height)
{
	if(copy("./" . $new_name, "./" . $t_name))
	{
		$display = 1;	//retour html
	}
	else	// fichier non ouvert
	{
		retour_erreur('<p>Impossible d\'ouvrir le fichier source (Erreur #7)</p>', __FILE__, 'warning');
	}
}
//-------------------------------------------------
// Ratio image < ratio miniature
//-------------------------------------------------
elseif(($height/$width) < ($t_height/$t_width))
{
	// calcul du ratio Lt | Ht=(Lim*Ht)/Him
	$t_height = ceil(($height*$t_width)/$width);	//Ceil arrondit sup�rieur

	if(make_thumbs (__PATH__, $p_image, $t_name, $width, $height, $t_height, $t_width))
	{
		$display = 1;	//g�n�ration OK
	}
	else
	{
		retour_erreur('<p>Erreur � la cr&eacute;ation de la miniature (Erreur #8)</p>', __FILE__, 'warning');
	}
}
//-------------------------------------------------
// Ratio image = ratio miniature
//-------------------------------------------------
elseif(($height/$width) == ($t_height/$t_width))
{
	if(make_thumbs (__PATH__, $p_image, $t_name, $width, $height, $t_height, $t_width))
	{
		$display = 1;	//g�n�ration OK
	}
	else
	{
		retour_erreur('<p>Erreur &agrave; la cr&eacute;ation de la miniature (Erreur #9)</p>', __FILE__, 'warning');
	}
}
//-------------------------------------------------
// Ratio image > ratio miniature
//-------------------------------------------------
elseif(($height/$width) > ($t_height/$t_width))
{
	// calcul du ratio Lt | Ht=(Lim*Ht)/Him
	$t_width = ceil(($width*$t_height)/$height);	//Ceil arrondit sup�rieur
	if(make_thumbs (__PATH__, $p_image, $t_name, $width, $height, $t_height, $t_width))
	{
		$display = 1;	//g�n�ration OK
	}
	else
	{
		retour_erreur('<p>Erreur &agrave; la cr&eacute;ation de la miniature (Erreur #10)</p>', __FILE__, 'warning');
	}
}
//---------------
// cas impossible
//---------------
else
{
	retour_erreur('Erreur</p>', __FILE__, 'die');
}


if($display && isset($_POST['thumbs']))	//retour HTML
{
	$lang['CORPS'] .= '<img src="' . $t_name . '" alt="Miniature" />';
	$lang['CORPS'] .= '<ul><li>Liens<ul>';
	$lang['CORPS'] .= '<li>URL : <a href="' . __URL_SITE__ . $t_name . '">' . __URL_SITE__ . $t_name . '</a></li>';
	$lang['CORPS'] .= '<li>HTML : <input type="text" size="50" value=\'&lt;a href="' . __URL_SITE__ . $t_name . '"&gt;Miniature de ' . $filename . '&lt;/a&gt;\' /></li>';
	$lang['CORPS'] .= '<li>BBcode : <input type="text" size="50" value="[img]' . __URL_SITE__ . $t_name . '[/img]" /></li>';
	$lang['CORPS'] .= '<li>BBcode2 : <input type="text" size="50" value="[url=' . __URL_SITE__ . __TARGET__ . $new_name . '][img]' . __URL_SITE__ . $t_name . '[/img][/url]" /></li></ul></li></ul>';
}

if(!($t_size = @filesize(__PATH__ . $t_name)))	//taille de la miniature
{
	retour_erreur('<p>Erreur #12 &agrave; l\'obtention de la taille de '. __PATH__ . $t_name .'</p>', __FILE__, 'warning');
}

sql_query('INSERT INTO `thumbnails` (`t_size`, `t_height`, `t_width`, `id`) VALUES ('. mysql_real_escape_string($t_size) .', '. mysql_real_escape_string($t_height) .', '. mysql_real_escape_string($t_width) .', '. mysql_real_escape_string($id) .')');
?>