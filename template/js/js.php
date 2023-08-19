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

require '../../config/config.php';
$visiteur = new SessionObject();
// Définition du type MIME
header('Content-Type: application/javascript');

// Utilisateur non connecté : Menu mon-compte caché + bouton pour l'afficher
if ($visiteur->getLevel() === UtilisateurObject::LEVEL_GUEST) :
    ?>
    // Cache les champs liés à l'espace membre
    document.getElementById('monCompte').style.display = 'none';
    // Au clic sur le bouton, affichage
    document.getElementById('monCompteGestion').onclick = function(e){
        document.getElementById('monCompteGestion').style.display = 'none';
        document.getElementById('monCompte').style.display = 'block';
        e.preventDefault();
    };
    // J'empêche l'envoi du pseudo formulaire...
    document.getElementById('monCompteGestion').onSubmit = function(e){
        e.preventDefault();
    };
<?php endif; ?>