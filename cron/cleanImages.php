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

/*
 * Suppression des images obsolètes
 */

require __DIR__ . '/../config/config.php';

// Effacer les fichiers jamais affichées
echo 'Suppression des images jamais affichées depuis ' . _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ . ' jours' . PHP_EOL;
$listeImages = HelperAdmin::getNeverUsedFiles();
foreach ((array) $listeImages as $value) {
    // Je crée mon objet et lance la suppression
    $monImage = new ImageObject($value);
    echo '   -> ' . $monImage->getNomNouveau() . ' (envoi le ' . $monImage->getDateEnvoiFormatee() . ')' . PHP_EOL;
    $monImage->supprimer();
}
echo '...done' . PHP_EOL;

// Effacer les fichiers inactifs
echo 'Suppression des images non affichées depuis ' . _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ . ' jours' . PHP_EOL;
$listeImages = HelperAdmin::getUnusedFiles();
foreach ((array) $listeImages as $value) {
    // Je crée mon objet et lance la suppression
    $monImage = new ImageObject($value);
    echo '   -> ' . $monImage->getNomNouveau() . ' (dernier affichage le ' . $monImage->getLastViewFormate() . ')' . PHP_EOL;
    $monImage->supprimer();
}
echo '...done' . PHP_EOL;
