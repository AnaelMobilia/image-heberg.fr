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

// Anti flood
$maSession = new SessionObject();

// En cas de validation du formulaire
if (isset($_POST['Submit']) && $maSession->checkFlag()) {
    // Vérification du bon format de l'adresse mail
    if (filter_var($_POST['userMail'], FILTER_VALIDATE_EMAIL) !== false) {
        // Je complète le message avec l'IP de mon émeteur
        $message = $_POST['userMessage'];
        $message .= "\r\n\r\n ---------------------------------------------";
        $message .= "\r\n\r\n IP : " . $_SERVER['REMOTE_ADDR'];
        $message .= "\r\n\r\n BROWSER : " . $_SERVER['HTTP_USER_AGENT'];

        // Tout va bien, on envoit un mail
        mail(_ADMINISTRATEUR_EMAIL_, "[" . _SITE_NAME_ . "] - Formulaire de contact", $message, "From: " . $_POST['userMail']);
        $maSession->removeFlag();

        // Retour utilisateur
        echo '<div class="alert alert-success">Votre message a été envoyé !</div>';
    } else {
        // Adresse mail invalide
        echo '<div class = "alert alert-danger">Votre adresse mail n\'est pas valide !<br /><pre>' . $_POST['userMail'] . '</pre></div>';
    }
} else {
    // Premier affichage de la page
    if (!isset($_POST['Submit'])) {
        // Activation de la protection robot
        $maSession->setFlag();
    }
}
?>
<?php if ($maSession->checkFlag()) : ?>
    <h1><small>Contact</small></h1>

    <div class="card card-primary">
        <div class="card-header">
            Administrateur - <?= _ADMINISTRATEUR_NOM_ ?>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label for="userMail">Votre adresse courriel</label>
                    <input type="email" class="form-control" name="userMail" id="userMail"
                           placeholder="john.doe@example.com" required="required"
                           value="<?= (isset($_POST['userMail']) && $maSession->checkFlag()) ? $_POST['userMail'] : '' ?>">
                    <span class="form-text text-muted">Sera utilisée uniquement pour vous apporter une réponse.</span>
                </div>
                <div class="form-group">
                    <label for="userMessage">Votre message</label>
                    <textarea class="form-control" rows="5" name="userMessage" id="userMessage"
                              placeholder="Votre message" required="required">
                                  <?= (isset($_POST['userMessage']) && $maSession->checkFlag()) ? $_POST['userMessage'] : '' ?>
                    </textarea>
                </div>
                <button type="submit" name="Submit" class="btn btn-success">Envoyer</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require _TPL_BOTTOM_ ?>