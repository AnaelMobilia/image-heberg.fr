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

use ArrayObject;
use Exception;

/*
 * Vérifier les images avec des comportements suspects
 */
// Peut être appelé par displayPics.php -> ne pas recharger la config
require_once __DIR__ . '/../config/config.php';

/**
 * Met en forme pour un mail une liste d'images
 * @param ArrayObject $listeImages
 * @param string $titre Titre de la catégorie à indiquer dans le mail
 * @return string
 * @throws Exception
 */
function formatageMailListeImages(ArrayObject $listeImages, string $titre): string
{
    $monRetour = '';
    if (count($listeImages) > 0) {
        $monRetour = $titre . ' : (' . count($listeImages) . ')' . PHP_EOL;
        foreach ((array)$listeImages as $value) {
            $monImage = new ImageObject($value);
            $monRetour .= '   -> ' . $monImage->getURL(true) . '?forceDisplay=1 ("' . $monImage->getNomOriginalFormate() . '") : ' . $monImage->getNbViewTotal() . ' affichages (' . $monImage->getNbViewPerDay() . '/jour) - envoyée le ' . $monImage->getDateEnvoiFormatee() . ' par ' . $monImage->getIpEnvoi() . ' - dernier affichage le ' . $monImage->getLastViewFormate() . PHP_EOL;
        }
        $monRetour .= '...done' . PHP_EOL;
    }
    return $monRetour;
}

/**
 * Bloquer des images
 * @param ArrayObject $listeImages liste d'images à bloquer
 * @return void
 * @throws Exception
 */
function bloquerImage(ArrayObject $listeImages): void
{
    foreach ((array)$listeImages as $value) {
        $monImage = new ImageObject($value);
        $monImage->setSignalee(true);
        $monImage->sauver();
    }
}

$contenu = '';
// Tracer l'image ayant généré l'appel au cron
if (!empty($_SERVER['REQUEST_URI'])) {
    $contenu .= 'Requête ayant généré le cron : ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
}
// Liste des images signalées
$listeImages = HelperAdmin::getImagesSignalees();
$contenu .= formatageMailListeImages($listeImages, 'Images signalées');

// Liste des images avec un ratio d'affichage important
$listeImages = HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_);
$contenu .= formatageMailListeImages($listeImages, 'Images qui sont beaucoup affichées (>' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ . '/jour)');

// Liste des images SUSPECTES avec un ratio d'affichage important
$listeImages = HelperAdmin::getImagesTropAffichees((_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_), true);
$contenu .= formatageMailListeImages($listeImages, 'Images SUSPECTES qui sont beaucoup affichées (>' . (_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_) . '/jour)');

// Liste des images avec un ratio d'affichage abusif
$listeImages = HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_);
bloquerImage($listeImages);
$contenu .= formatageMailListeImages($listeImages, 'Blocage des images qui abusent (>' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ . '/jour)');

// Liste des images SUSPECTES avec un ratio d'affichage abusif
$listeImages = HelperAdmin::getImagesTropAffichees((_ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_), true);
bloquerImage($listeImages);
$contenu .= formatageMailListeImages($listeImages, 'Blocage des images SUSPECTES qui abusent (>' . (_ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_) . '/jour)');

// Liste des images suspectes
$listeImages = HelperAdmin::getImagesPotentiellementIndesirables();
$contenu .= formatageMailListeImages($listeImages, 'Images suspectes');

// Liste des images abusant clairement
$listeImages = HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_ABUSIF_, false, true, true);
$contenu .= formatageMailListeImages($listeImages, 'Images qui abusent du service (>' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_ABUSIF_ . '/jour)');


if (!empty($contenu)) {
    // Envoyer une notification à l'admin
    mail(_ADMINISTRATEUR_EMAIL_, '[' . _SITE_NAME_ . '] - Gestion des abus', $contenu, 'From: ' . _ADMINISTRATEUR_EMAIL_);
}
