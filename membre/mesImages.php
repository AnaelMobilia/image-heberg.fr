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
// TODO : affichage des images dans la page en javascript ?
// TODO : navigation entre les images

require __DIR__ . '/../config/configV2.php';
// Vérification des droits d'accès
metaObject::checkUserAccess(utilisateurObject::levelUser);
require _TPL_TOP_;
?>
<div class="jumbotron">
    <h1><small>Mes images</small></h1>
    <table class="table table-hover">
        <thead>
            <tr>
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
            // Je récupère la liste des images
            $laSession = new sessionObject();
            $mesImages = metaObject::getAllPicsOffOneUser($laSession->getId());
            foreach ((array) $mesImages as $newName):
                $uneImage = new imageObject($newName);
                ?>
                <tr>
                    <td><?= $uneImage->getNomOriginalFormate() ?></td>
                    <td><?= $uneImage->getDateEnvoiFormatee() ?></td>
                    <td><?= $uneImage->getLastViewFormate() ?></td>
                    <td><?= $uneImage->getNbViewTotal() ?></td>
                    <td>
                        <a href='<?= _URL_IMAGES_ ?><?= $uneImage->getNomNouveau() ?>' target="_blank">
                            <span class="glyphicon glyphicon-share"></span>
                        </a>
                    </td>
                    <td>
                        <a href='<?= _URL_ ?>delete.php?id=<?= $uneImage->getNomNouveau() ?>&type=<?= ressourceObject::typeImage ?>' target="_blank">
                            <span class="glyphicon glyphicon-trash"></span>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require _TPL_BOTTOM_ ?>