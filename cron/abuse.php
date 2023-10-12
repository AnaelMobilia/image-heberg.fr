<?php

/*
 * Copyright 2008-2023 Anael MOBILIA
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

require __DIR__ . '/../config/config.php';

/**
 * Met en forme pour un mail une liste d'images
 * @param ArrayObject $listeImages
 * @return string
 * @throws Exception
 */
function formatageMailListeImages(ArrayObject $listeImages): string
{
    $monRetour = '';
    foreach ((array)$listeImages as $value) {
        $monImage = new ImageObject($value);
        $monRetour .= '   -> ' . $monImage->getURL() . '?forceDisplay=1 ("' . $monImage->getNomOriginalFormate() . '") : ' . $monImage->getNbViewTotal() . ' affichages (' . $monImage->getNbViewPerDay() . '/jour) - envoyée le ' . $monImage->getDateEnvoiFormatee() . ' par ' . $monImage->getIpEnvoi() . ' - dernier affichage le ' . $monImage->getLastViewFormate() . PHP_EOL;
    }
    return $monRetour;
}

$contenu = '';
$envoiMail = false;
// Liste des images signalées
$contenu .= 'Images signalées :' . PHP_EOL;
$listeImages = HelperAdmin::getImagesSignalees();
$contenuTmp = formatageMailListeImages($listeImages);
if ($contenuTmp !== '') {
    $envoiMail = true;
    $contenu .= $contenuTmp;
}
$contenu .= '...done' . PHP_EOL;

// Liste des images avec un ratio d'affichage important
$contenu .= 'Images qui sont beaucoup affichées (>' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ . '/jour):' . PHP_EOL;
$listeImages = HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_);
$contenuTmp = formatageMailListeImages($listeImages);
if ($contenuTmp !== '') {
    $envoiMail = true;
    $contenu .= $contenuTmp;
}
$contenu .= '...done' . PHP_EOL;

// Liste des images avec un ratio d'affichage abusif
$contenu .= 'Blocage des images qui abusent (>' . _ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_ . '/jour):' . PHP_EOL;
$listeImages = HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_);
// Blocage des images
foreach ((array)$listeImages as $value) {
    $monImage = new ImageObject($value);
    $monImage->setSignalee(true);
    $monImage->sauver();
}
$contenuTmp = formatageMailListeImages($listeImages);
if ($contenuTmp !== '') {
    $envoiMail = true;
    $contenu .= $contenuTmp;
}
$contenu .= '...done' . PHP_EOL;

if ($envoiMail) {
    // Envoyer une notification à l'admin
    mail(_ADMINISTRATEUR_EMAIL_, '[' . _SITE_NAME_ . '] - Gestion des abus', $contenu, 'From: ' . _ADMINISTRATEUR_EMAIL_);
}
echo $contenu;
