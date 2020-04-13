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
require 'config/config.php';
require _TPL_TOP_;

// Anti flood
$maSession = new sessionObject();

// En cas de validation du formulaire
if (isset($_POST['envoyer']) && $maSession->checkFlag()) {
    // Vérification du bon format de l'adresse mail
    if (filter_var($_POST['userMail'], FILTER_VALIDATE_EMAIL) !== FALSE) {
        // On essaie de matcher l'image
        $result = preg_match("#^.*\/([\d]*.[pngjpif]{3})$#", trim($_POST['urlImage']), $idImage);
        if ($result) {
            // On flaggue l'image en signalee en BDD
            $monImage = new imageObject($idImage[1]);
            $monImage->setSignalee(true);
            $monImage->sauver();
        }

        // Je complète le message avec l'IP de mon émeteur
        $message = "URL : " . $_POST['urlImage'];
        $message .= "\r\n\r\nBlocage automatique : " . ($result ? 'OK' : 'KO');
        $message .= "\r\n\r\nRaison : " . $_POST['raison'];
        $message .= "\r\n\r\nMessage : " . $_POST['userMessage'];
        $message .= "\r\n\r\n---------------------------------------------";
        $message .= "\r\n\r\nIP : " . $_SERVER['REMOTE_ADDR'];
        $message .= "\r\n\r\nBROWSER : " . $_SERVER['HTTP_USER_AGENT'];

        // Tout va bien, on envoit un mail
        mail(_ADMINISTRATEUR_EMAIL_, "[" . _SITE_NAME_ . "] - Signalement d'image", $message, "From: " . $_POST['userMail']);
        $maSession->removeFlag();

        // Retour utilisateur
        ?>
        <div class="alert alert-success">Votre signalement a été envoyé !</div>
        <?php
    } else {
        // Adresse mail invalide
        ?>
        <div class = "alert alert-danger">
            Votre adresse mail n'est pas valide !
            <br />
            <pre><?= $_POST['userMail'] ?></pre>
        </div>
        <?php
    }
} else {
    // Premier affichage de la page
    if (!isset($_POST['envoyer'])) {
        // Activation de la protection robot
        $maSession->setFlag();
    }
}
?>
<?php if ($maSession->checkFlag()): ?>
    <h1><small>Signaler une image</small></h1>

    <div class="card card-primary">
        <div class="card-header">
            Signaler une image
        </div>
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label for="urlImage">URL de l'image</label>
                    <input type="text" class="form-control" name="urlImage" id="urlImage" placeholder="<?= _URL_IMAGES_ . _IMAGE_BAN_ ?>" required="required" value="<?= (isset($_POST['urlImage']) && $maSession->checkFlag()) ? $_POST['urlImage'] : '' ?>">
                    <span class="form-text text-muted">Indiquer toute l'adresse de l'image (telle qu'affichée dans le navigateur).</span>
                </div>
                <div class="form-group">
                    <label for="raison">Raison du signalement</label>
                    <select name="raison" id="raison" class="form-control" required="required">
                        <option value="" selected>-- Ne pas effectuer --</option>
                        <option value="porno">Pornographie et érotisme</option>
                        <option value="legislation">Non respect de la législation française (à indiquer)</option>
                        <option value="autre">Autre (à indiquer)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="userMail">Votre adresse courriel</label>
                    <input type="email" class="form-control" name="userMail" id="userMail" placeholder="john.doe@example.com" required="required" value="<?= (isset($_POST['userMail']) && $maSession->checkFlag()) ? $_POST['userMail'] : '' ?>">
                    <span class="form-text text-muted">Sera utilisée uniquement pour vous apporter une réponse.</span>
                </div>
                <div class="form-group">
                    <label for="userMessage">Votre message</label>
                    <textarea class="form-control" rows="5" name="userMessage" id="userMessage" placeholder="Informations complémentaires sur la raison de votre demande" required="required"><?= (isset($_POST['userMessage']) && $maSession->checkFlag()) ? $_POST['userMessage'] : '' ?></textarea>
                </div>
                <button type="submit" name="envoyer" class="btn btn-success">Envoyer</button>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php require _TPL_BOTTOM_ ?>