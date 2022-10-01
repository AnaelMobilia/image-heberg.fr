<?php

/*
 * Copyright 2008-2022 Anael MOBILIA
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

if (!defined('_PHPUNIT_')) {
    require 'config/config.php';
}

$erreur = false;
$msgErreur = '';

/**
 * Vérification du paramètre
 */
if (!isset($_GET['id']) || !isset($_GET['type'])) {
    $erreur = true;
    $msgErreur .= 'La page n\'a pas été appelée correctement !<br />';
}

/**
 * Chargement de l'image depuis la BDD
 */
if (!$erreur) {
    if ((int) $_GET['type'] === RessourceObject::TYPE_IMAGE) {
        $monImage = new ImageObject();
    } else {
        $monImage = new MiniatureObject();
    }

    $retour = $monImage->charger($_GET['id']);

    // Gestion du retour
    if (!$retour) {
        $erreur = true;
        $msgErreur .= 'Cette image n\'existe pas !<br />';
    }
}

/**
 * Vérification des droits sur l'image
 * -> Possession
 * -> Envoi il y a moins d'une heure par la même @ IP
 */
if (!$erreur) {
    if ($monImage->isProprietaire() || ((strtotime($monImage->getDateEnvoiBrute()) + 3600) > strtotime("now") && $monImage->getIpEnvoi() === $_SERVER['REMOTE_ADDR'])) {
        // Effacement...
        $monImage->supprimer();
    } else {
        $erreur = true;
        $msgErreur = 'Vous n\'avez pas le droit de supprimer cette image !<br />';
    }
}

// Pas d'erreur => Redirection sur la page d'accueil
if (empty($erreur)) {
    header('Location: ' . _URL_HTTPS_ . '?delete_success');
} else {
    require _TPL_TOP_;
    ?>
    <h1 class="mb-3"><small>Suppression du fichier</small></h1>
    <div class="alert alert-danger">
        <span class="glyphicon glyphicon-remove"></span>
        &nbsp;
        <b>Une erreur a été rencontrée !</b>
        <br />
    <?= $msgErreur ?>
    </div>
    <?php
    require _TPL_BOTTOM_;
}
