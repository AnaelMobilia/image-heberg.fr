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

// Anti flood
$maSession = new SessionObject();

require _TPL_TOP_;

// En cas de validation du formulaire
if (isset($_POST['Submit']) && $maSession->checkFlag()) {
    // Vérification du bon format de l'adresse mail
    if (filter_var($_POST['userMail'], FILTER_VALIDATE_EMAIL) !== false) {
        // Je complète le message avec l'IP de mon émeteur
        $message = $_POST['userMessage'];
        $message .= PHP_EOL . '---------------------------------------------';
        $message .= PHP_EOL . 'IP : ' . $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'];
        $message .= PHP_EOL . 'BROWSER : ' . $_SERVER['HTTP_USER_AGENT'];
        $message .= PHP_EOL . 'DATE : ' . date('Y-m-d H:i:s');

        // Tout va bien, on envoit un mail
        mail(_ADMINISTRATEUR_EMAIL_, '[' . _SITE_NAME_ . '] - Formulaire de contact', $message, 'From: ' . $_POST['userMail']);
        $maSession->removeFlag();

        // Retour utilisateur
        echo '<div class="alert alert-success">Votre message a été envoyé !</div>';
    } else {
        // Adresse mail invalide
        echo '<div class = "alert alert-danger">Votre adresse mail n\'est pas valide !<br /><pre>' . $_POST['userMail'] . '</pre></div>';
    }
} elseif (!isset($_POST['Submit'])) {
    // Premier affichage de la page => activation de la protection robot
    $maSession->setFlag();
}
?>
    <?php if (!isset($_POST['Submit']) || $maSession->checkFlag()) : ?>
    <h1 class="mb-3"><small>Contacter l'administrateur du service - <?= _ADMINISTRATEUR_NOM_ ?></small></h1>
    <form method="post">
        <div class="mb-3 form-floating">
            <input type="email" class="form-control" name="userMail" id="userMail" required="required" value="<?= $_POST['userMail'] ?? '' ?>">
            <label for="userMail">Votre adresse courriel</label>
            <div class="form-text">Sera utilisée uniquement pour vous apporter une réponse.</div>
        </div>
        <div class="mb-3 form-floating">
            <textarea class="form-control" name="userMessage" id="userMessage" required="required"><?= $_POST['userMessage'] ?? '' ?></textarea>
            <label for="userMessage">Votre message</label>
        </div>
        <button type="submit" name="Submit" class="btn btn-success">Envoyer</button>
    </form>
    <?php endif; ?>
    <?php require _TPL_BOTTOM_ ?>