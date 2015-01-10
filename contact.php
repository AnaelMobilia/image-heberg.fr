<?php
/*
* Copyright 2008-2015 Anael Mobilia
*
* This file is part of NextINpact-Unofficial.
*
* NextINpact-Unofficial is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextINpact-Unofficial is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextINpact-Unofficial. If not, see <http://www.gnu.org/licenses/>
*/
require 'config/configV2.php';
require _TPL_TOP_;

// En cas de validation du formulaire
if (isset($_POST['envoyer'])) {
    // Vérification du bon format de l'adresse mail
    if (filter_var($_POST['userMail'], FILTER_VALIDATE_EMAIL) != FALSE) {
        // Je complète le message avec l'IP de mon émeteur
        $message = $_POST['userMessage'];
        $message .= "\r\n\r\n ---------------------------------------------";
        $message .= "\r\n\r\n IP : " . $_SERVER['REMOTE_ADDR'];
        $message .= "\r\n\r\n BROWSER : " . $_SERVER['HTTP_USER_AGENT'];

        // Tout va bien, on envoit un mail
        mail(_MAIL_ADMIN_, "[Image-Heberg.fr] - Formulaire de contact", $message, "From: " . $_POST['userMail']);

        // Retour utilisateur
        ?>
        <div class="alert alert-success">Votre message a été envoyé !</div>
        <?php
    }
    // Adresse mail invalide
    else {
        ?>
        <div class = "alert alert-danger">
            Votre adresse mail n'est pas valide !
            <br />
            Votre message était :
            <pre><?= $_POST['userMessage'] ?></pre>
        </div>
        <?php
    }
}
?>
<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <h1><small>Contacter Image-Heberg.fr</small></h1>

    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Webmaster - Anael Mobilia</h3>
        </div>
        <div class="panel-body">
            Un avis, une remarque, une difficulté, ... ?
            <br />
            N'hésitez pas à envoyer un message par le formulaire ci-dessous !
        </div>
    </div>

    <form role="form" method="post">
        <div class="form-group">
            <label for="userMessage">Votre message</label>
            <textarea class="form-control" rows="5" name="userMessage" id="userMessage" placeholder="Votre message" required="required"></textarea>
        </div>
        <div class="form-group">
            <label for="userMail">Votre adresse courriel</label>
            <input type="email" class="form-control" name="userMail" id="userMail" placeholder="Votre adresse courriel" required="required">
            <span class="help-block">Sera utilisée uniquement pour vous apporter une réponse.</span>
        </div>
        <button type="submit" name="envoyer" class="btn btn-success">Envoyer</button>
    </form>
</div>
<?php require _TPL_BOTTOM_ ?>