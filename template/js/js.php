<?php
/*
 * Copyright 2008-2020 Anael MOBILIA
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
require '../../config/config.php';
$visiteur = new SessionObject();
// Définition du type MIME
header('Content-Type: application/javascript');

// Utilisateur non connecté : Menu mon-compte caché + bouton pour l'afficher
if ($visiteur->getLevel() === UtilisateurObject::LEVEL_GUEST) :
    ?>
    // Cache les champs liés à l'espace membre
    $("#monCompte").hide();
    // Au clic sur le bouton, affichage
    $("#buttonMonCompteGestion").click(function() {
    $("#monCompteGestion").hide();
    $("#monCompte").show();
    });
    // J'empêche l'envoi du pseudo formulaire...
    $("#monCompteGestion").on("submit", function(e){
    e.preventDefault();
    });
<?php endif; ?>