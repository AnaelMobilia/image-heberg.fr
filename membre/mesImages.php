<?php

/*
 * Copyright 2008-2021 Anael MOBILIA
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

// TODO : affichage des images dans la page en javascript ?
// TODO : navigation entre les images

namespace ImageHeberg;

require __DIR__ . '/../config/config.php';
// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_USER);
require _TPL_TOP_;
// Je récupère la session de mon utilisateur
$maSession = new SessionObject();
// Et je reprend ses données
$monUtilisateur = new UtilisateurObject($maSession->getId());
?>
<h1 class="mb-3"><small>Mes images</small></h1>
<table class="table table-hover">
    <thead>
        <tr class="text-center">
            <th>Nom de l'image</th>
            <th>Date d'envoi</th>
            <th>Dernier affichage</th>
            <th>Nb. vues</th>
            <th>Voir</th>
            <th>Supprimer</th>
        </tr>
    </thead>
    <tbody>
        <?php

        $mesImages = $monUtilisateur->getImages();
        foreach ((array) $mesImages as $newName) :
            $uneImage = new ImageObject($newName);
            ?>
            <tr>
                <td><?= $uneImage->getNomOriginalFormate() ?></td>
                <td class="text-center"><?= $uneImage->getDateEnvoiFormatee() ?></td>
                <td class="text-center"><?= $uneImage->getLastViewFormate() ?></td>
                <td class="text-center"><?= $uneImage->getNbViewTotal() ?></td>
                <td class="text-center">
                    <a href='<?= _URL_IMAGES_ ?><?= $uneImage->getNomNouveau() ?>' target="_blank">
                        <span class="fas fa-share-square"></span>
                    </a>
                </td>
                <td class="text-center">
                    <a href='<?= _URL_ ?>delete.php?id=<?= $uneImage->getNomNouveau() ?>&type=<?= RessourceObject::TYPE_IMAGE ?>'
                       target="_blank">
                        <span class="fas fa-trash"></span>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php require _TPL_BOTTOM_ ?>