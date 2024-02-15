<?php

/*
 * Copyright 2008-2024 Anael MOBILIA
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
    require '../config/config.php';
}

// Anti flood
$maSession = new SessionObject();

// Un utilisateur...
$monUtilisateur = new UtilisateurObject();

// En cas de validation du formulaire
if (isset($_POST['valider']) && $maSession->checkFlag()) {
    // Flag pour la création de l'utilisateur
    $flagCreation = true;
    $messageErreur = '';

    if (empty($_POST['userName'])) {
        $flagCreation = false;
        $messageErreur .= '<br />Merci de saisir un identifiant.';
    }
    if (empty($_POST['userPassword'])) {
        $flagCreation = false;
        $messageErreur .= '<br />Merci de saisir un mot de passe.';
    }
    if (empty($_POST['userMail'])) {
        $flagCreation = false;
        $messageErreur .= '<br />Merci de saisir une adresse courriel.';
    }
    // Vérification du bon format de l'adresse mail
    if (filter_var($_POST['userMail'], FILTER_VALIDATE_EMAIL) === false) {
        $flagCreation = false;
        $messageErreur .= '<br />L\'adresse courriel saisie n\'est pas correcte.';
    }
    // Disponibilité de l'email
    if (UtilisateurObject::verifierEmailDisponible($_POST['userMail']) !== true) {
        $flagCreation = false;
        $messageErreur .= '<br />Cet email n\'est pas disponible. Merci d\'en choisir un autre.';
    }
    // Disponibilité du login
    if (UtilisateurObject::verifierLoginDisponible($_POST['userName']) !== true) {
        $flagCreation = false;
        $messageErreur .= '<br />Ce nom d\'utilisateur n\'est pas possible. Merci d\'en choisir un autre.';
    }

    // Données administratives : droits de l'utilisateur
    $monUtilisateur->setLevel(UtilisateurObject::LEVEL_USER);
    // Données fournies par l'utilisateur
    // Nom d'utilisateur
    $monUtilisateur->setUserName($_POST['userName']);
    // Mot de passe - Crypté
    $monUtilisateur->setPasswordToCrypt($_POST['userPassword']);
    // Adresse mail
    $monUtilisateur->setEmail(strtolower($_POST['userMail']));

    // Si tout est bon
    if ($flagCreation) {
        // Création de l'utilisateur
        $monUtilisateur->enregistrer();
        // Connexion de l'utilisateur
        $monUtilisateur->connexion($_POST['userName'], $_POST['userPassword']);

        $maSession->removeFlag();
        // TODO : envoi d'un mail avec les identifiants de l'utilisateur
        // voir une création de compte avec validation par mail ?

        if (!_PHPUNIT_) {
            // Redirection sur la page d'accueil - sauf si mode tests
            header('Location: ' . _URL_HTTPS_);
            die();
        }
    }
}
require _TPL_TOP_;
$maSession->setFlag();
?>
    <h1 class="mb-3"><small>Créer mon compte</small></h1>
    <?php if (!empty($messageErreur)) : ?>
    <div class="alert alert-danger"><strong>La création de votre compte n'est pas possible :</strong>
        <?= $messageErreur ?>
    </div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3 form-floating">
            <input type="text" class="form-control" name="userName" id="userName" value="<?= $monUtilisateur->getUserName() ?>" required="required">
            <label for="userName">Identifiant</label>
        </div>
        <div class="mb-3 form-floating">
            <input type="password" class="form-control" name="userPassword" id="userPassword" required="required">
            <label for="userPassword">Mot de passe</label>
        </div>
        <div class="mb-3 form-floating">
            <input type="email" class="form-control" name="userMail" id="userMail" value="<?= $monUtilisateur->getEmail() ?>" required="required">
            <label for="userMail">Adresse courriel</label>
            <div class="form-text">Utilisée uniquement en cas de réinitialisation de votre mot de passe.</div>
        </div>
        <button type="submit" name="valider" class="btn btn-success">
            <span class="bi-person-add"></span>&nbsp;
            M'inscrire
        </button>
    </form>
    <?php require _TPL_BOTTOM_ ?>