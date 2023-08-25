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

/*
 * Vérifier les images avec des comportements suspects
 */

require __DIR__ . '/../config/config.php';

$contenu = '';
$envoiMail = false;
// Liste des images signalées
$contenu .= 'Images signalées :' . PHP_EOL;
$listeImages = HelperAdmin::getImagesSignalees();
foreach ((array) $listeImages as $value) {
    $envoiMail = true;
    $monImage = new ImageObject($value);
    $contenu .= '   -> ' . $monImage->getURL() . '?forceDisplay=1 ("' . $monImage->getNomOriginalFormate() . '") : ' . $monImage->getNbViewTotal() . ' affichages (' . $monImage->getNbViewPerDay() . '/jour) - envoyée le ' . $monImage->getDateEnvoiFormatee() . ' par ' . $monImage->getIpEnvoi() . ' - dernier affichage le ' . $monImage->getLastViewFormate() . PHP_EOL;
}
$contenu .= '...done';

// Liste des images avec un ratio d'affichage incohérent
$contenu .= 'Images trop affichées :' . PHP_EOL;
$listeImages = HelperAdmin::getImagesTropAffichees();
foreach ((array) $listeImages as $value) {
    $envoiMail = true;
    $monImage = new ImageObject($value);
    $contenu .= '   -> ' . $monImage->getURL() . '?forceDisplay=1 ("' . $monImage->getNomOriginalFormate() . '") : ' . $monImage->getNbViewTotal() . ' affichages (' . $monImage->getNbViewPerDay() . '/jour) - envoyée le ' . $monImage->getDateEnvoiFormatee() . ' par ' . $monImage->getIpEnvoi() . ' - dernier affichage le ' . $monImage->getLastViewFormate() . PHP_EOL;
}
$contenu .= '...done';

if ($envoiMail) {
    // Envoyer une notification à l'admin
    mail(_ADMINISTRATEUR_EMAIL_, '[' . _SITE_NAME_ . '] - Images trop affichées', $contenu, 'From: ' . _ADMINISTRATEUR_EMAIL_);
}
echo $contenu;
