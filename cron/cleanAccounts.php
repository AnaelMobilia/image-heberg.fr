<?php

/*
 * Copyright 2008-2026 Anael MOBILIA
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
 * Suppression des comptes obsolètes
 */

require __DIR__ . '/../config/config.php';

// Effacer les comptes jamais utilisés
echo 'Suppression des comptes jamais utilisés ' . _DELAI_EFFACEMENT_COMPTES_JAMAIS_UTILISES_ . ' jours après leur création' . PHP_EOL;
$listeComptes = HelperAdmin::getUnusedAccounts();
foreach ($listeComptes as $value) {
    // Je crée mon objet et lance la suppression
    $monUtilisateur = new UtilisateurObject($value);
    echo '   -> ' . $monUtilisateur->getEmail() . ' - créé le ' . $monUtilisateur->getDateInscriptionFormate() . ' via IP ' . $monUtilisateur->getIpInscription() . PHP_EOL;
    $monUtilisateur->supprimer();
}
echo '...done' . PHP_EOL;
