<?php

/*
 * Copyright 2008-2026 Anael MOBILIA
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
// Anti flood
$maSession = new SessionObject();
$msgErreur = '';

require _TPL_TOP_;

// En cas de validation du formulaire
if (
        isset($_POST['Submit']) && $maSession->checkFlag()
        && !empty($_POST['userMail']) && !empty($_POST['urlImage'])
) {
    // Suivi du traitement
    $isTraitee = false;

    // Vérification du bon format de l'adresse mail
    if (filter_var($_POST['userMail'], FILTER_VALIDATE_EMAIL) !== false) {
        // On essaie de matcher l'image - nettoyage des paramètres
        $fileName = basename(parse_url(trim($_POST['urlImage']), PHP_URL_PATH));
        if (
                preg_match('#^[\d]+\.(?:' . implode('|', _ACCEPTED_EXTENSIONS_) . ')$#', $fileName)
                || (_PHPUNIT_ && $fileName === 'image_15.png')
        ) {
            // Suivi du traitement
            $isTraitee = true;
            // On flaggue l'image en signalée en BDD
            $monImage = new ImageObject($fileName);
            // Si l'image est approuvée, on ne la bloque pas en automatique
            if (!$monImage->isApprouvee()) {
                $monImage->setSignalee(true);
                $monImage->sauver();

                // On cherche les autres images avec le même MD5
                $images = HelperAdmin::getImageByMd5($monImage->getMd5());
                foreach ($images as $uneImage) {
                    // On flaggue en signalée...
                    $monImage = new ImageObject($uneImage);
                    $monImage->setSignalee(true);
                    $monImage->sauver();
                }
                // Les miniatures reprennent automatiquement les informations de l'image parent
            } else {
                $isTraitee = false;
            }
        }

        // Gestion travis
        if (!_PHPUNIT_) {
            // Je complète le message avec l'IP de mon émeteur
            $message = 'URL : ' . $_POST['urlImage'];
            $message .= PHP_EOL . 'Blocage automatique : ' . ($isTraitee ? 'OK' : 'KO');
            $message .= PHP_EOL . 'Raison : ' . $_POST['raison'];
            $message .= PHP_EOL . 'Message : ' . $_POST['userMessage'];
            $message .= PHP_EOL . '---------------------------------------------';
            $message .= PHP_EOL . 'IP : ' . $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'];
            $message .= PHP_EOL . 'BROWSER : ' . $_SERVER['HTTP_USER_AGENT'];
            $message .= PHP_EOL . 'DATE : ' . date('Y-m-d H:i:s');

            // Tout va bien, on envoit un mail
            $subject = '[' . _SITE_NAME_ . '] - Signalement d\'image';
            mail(_ADMINISTRATEUR_EMAIL_, $subject, $message, 'From: ' . $_POST['userMail']);
            $maSession->removeFlag();
        }
        // Retour utilisateur
        echo '<div class="alert alert-success">Votre signalement a été envoyé, merci !</div>';
    } else {
        // Adresse mail invalide
        $msgErreur = '<div class="alert alert-danger">Votre adresse mail n\'est pas valide !<br /><pre>' . $_POST['userMail'] . '</pre></div>';
    }
} elseif (!isset($_POST['Submit'])) {
    // Premier affichage de la page => activation de la protection robot
    $maSession->setFlag();
}
?>
<?php if (!isset($_POST['Submit']) || $maSession->checkFlag()) : ?>
    <h1 class="mb-3"><small>Signaler une image</small></h1>
    <?= $msgErreur ?>
    <form method="post">
        <div class="mb-3 form-floating">
            <input type="text" class="form-control" name="urlImage" id="urlImage" required="required" value="<?= $_POST['urlImage'] ?? '' ?>">
            <label for="urlImage">Adresse de l'image</label>
            <div class="form-text text-muted">
                Indiquer toute l'URL de l'image, telle qu'affichée dans le navigateur (<?= _URL_IMAGES_ . _IMAGE_BAN_ ?>).
            </div>
        </div>
        <div class="mb-3 form-floating">
            <select name="raison" id="raison" class="form-select" required="required">
                <option value="" selected>-- Sélectionner une raison --</option>
                <option value="porno">Pornographie et érotisme</option>
                <option value="phishing">Spam et phishing</option>
                <option value="legislation">Non respect de la législation française (à préciser)</option>
                <option value="autre">Autre (à préciser)</option>
            </select>
            <label for="raison">Raison du signalement</label>
        </div>
        <div class="mb-3 form-floating">
            <input type="email" class="form-control" name="userMail" id="userMail" required="required" value="<?= $_POST['userMail'] ?? '' ?>">
            <label for="userMail">Votre adresse courriel</label>
            <div class="form-text text-muted">Sera utilisée uniquement pour vous apporter une réponse.</div>
        </div>
        <div class="mb-3 form-floating">
            <textarea class="form-control" rows="5" name="userMessage" id="userMessage" placeholder="Informations complémentaires sur la raison de votre demande" required="required"><?= $_POST['userMessage'] ?? '' ?></textarea>
            <label for="userMessage">Votre message</label>
        </div>
        <button type="submit" name="Submit" class="btn btn-success">Envoyer</button>
    </form>
<?php endif; ?>
    <?php require _TPL_BOTTOM_ ?>