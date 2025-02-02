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

require 'config/config.php';
require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Conditions Générales d'Utilisation de <?= _SITE_NAME_ ?></small></h1>

    <div class="alert alert-success">Ces CGU sont modifiables sans préavis et à tout moment.</div>

    <div class="card">
        <div class="card-header">
            Contenus autorisés
        </div>
        <div class="card-body">
            <ul>
                <li>Toutes images de type <?= strtoupper(implode(', ', _ACCEPTED_EXTENSIONS_)) ?>.</li>
                <li>Contenus conformes à la législation française.</li>
                <li>L'administrateur du site est seul juge de la conformité d'une image aux présentes CGU. En cas de désaccord, vous pouvez le contacter via <a href="/contact.php">le formulaire de contact</a>.</li>
            </ul>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            Contenus non autorisés
        </div>
        <div class="card-body">
            <ul>
                <li>Contenus non conformes à la législation française.</li>
                <?php foreach (_ABUSE_TYPES_ as $categorie => $detail) : ?>
                    <li><b><?= $categorie ?></b> : <?= $detail ?></li>
                <?php endforeach; ?>
                <li>Pour information, les images peuvent être analysées par un composant d'IA dédié au service afin d'aider à leur vérification.</li>
            </ul>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            Propriétés de l'hébergement
        </div>
        <div class="card-body">
            <ul>
                <li>Gratuit.</li>
                <li>Traffic illimité <em>(hors abus, auquel cas des limitations peuvent être mises en place)</em>.</li>
                <li>
                    <b>Conservation :</b>
                    <ul>
                        <li>
                            <?= _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ ?> jour<?= _DELAI_INACTIVITE_AVANT_EFFACEMENT_IMAGES_ > 1 ? 's' : '' ?> à compter de la dernière utilisation du fichier.
                        </li>
                        <li>
                            A défaut, <?= _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ ?> jour<?= _DELAI_EFFACEMENT_IMAGES_JAMAIS_AFFICHEES_ > 1 ? 's' : '' ?> après l'envoi <em>(si aucun affichage)</em>.
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
                    Aucune suppression sur demande d'un utilisateur. <em>(utilisez la fonction de suppression à l'envoi ou utilisez <a href="membre/connexionCompte.php">l'espace membre)</a></em>
                </li>
                <li>
                    Toutes les données possédées seront fournies en cas de demande judiciaire ou de dépôt de plainte.
                </li>
                <li>
                    Suppression des comptes membres qui n'ont jamais été utilisés au bout de <?= _DELAI_EFFACEMENT_COMPTES_JAMAIS_UTILISES_ ?> jour<?= _DELAI_EFFACEMENT_COMPTES_JAMAIS_UTILISES_ > 1 ? 's' : '' ?>.
                </li>
                <li>
                    Rappel : <a href="contact.php">l'administrateur (<?= _ADMINISTRATEUR_NOM_ ?>)</a> a accès à toutes les données du service.
                </li>
            </ul>
            <div class="card-footer">
                <em>Mises à jour le 02 janvier 2024 : ajout de précisions sur les images non autorisées + module de catégorisation IA.</em>
            </div>
        </div>
    </div>
    <?php require _TPL_BOTTOM_ ?>