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

namespace ImageHeberg;

require 'config/config.php';
require _TPL_TOP_;
?>
<h1><small>Conditions Générales d'Utilisation de <?= _SITE_NAME_ ?></small></h1>

<div class="alert alert-success">Ces CGU sont modifiables sans préavis et à tout moment.</div>

<div class="card card-primary">
    <div class="card-header">
        Contenus autorisés
    </div>
    <div class="card-body">
        <ul>
            <li>Toutes images de type JPG, PNG, GIF.</li>
            <li>Contenu conforme à la législation française.</li>
            <li>Pornographie et érotisme non autorisés.</li>
        </ul>
    </div>
</div>
<div class="card card-primary">
    <div class="card-header">
        Propriétés de l'hébergement
    </div>
    <div class="card-body">
        <ul>
            <li>Gratuit.</li>
            <li>Traffic illimité <em>(hors abus)</em>.</li>
            <li>
                <b>Conservation :</b>
                <ul>
                    <li>
                        <?= _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ ?> 
                        jour<?= _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ > 1 ? 's' : '' ?>
                        à compter de la dernière utilisation du fichier.
                    </li>
                    <li>
                        A défaut, <?= _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ ?>
                        jour<?= _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ > 1 ? 's' : '' ?>
                        après l'envoi <em>(si aucun affichage)</em>.
                    </li>
                </ul>
            </li>
            <li>
                Nombre d'images par compte : illimité.
            </li>
            <li>
                Les fichiers restent votre propriété.
            </li>
            <li>
                Aucune suppression sur demande d'un utilisateur.
                <em>(utilisez la fonction de suppression à l'envoi ou utilisez l'espace membre)</em>
            </li>
            <li>
                Toutes les données possédées seront fournies en cas de demande judiciaire.
            </li>
            <li>
                Rappel : l'administrateur (<?= _ADMINISTRATEUR_NOM_ ?>) a accès à toutes les données du service.
            </li>
        </ul>
        <div class="card-footer">
            <em>Mises à jour le 29 juin 2018 : réduction des durées de conservation des images inactives</em>
        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>