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

if (!defined('_PHPUNIT_')) {
    require __DIR__ . '/../config/config.php';
}

// Un utilisateur...
$monUtilisateur = new UtilisateurObject();

// En cas de validation du formulaire
if (isset($_POST['valider'])) {
    $messageErreur = '';

    if (empty($_POST['userName'])) {
        $messageErreur .= "<br />Merci de saisir un identifiant.";
    }
    if (empty($_POST['userPassword'])) {
        $messageErreur .= "<br />Merci de saisir un mot de passe.";
    }
    // Si tout est bon
    if (empty($messageErreur)) {
        if ($monUtilisateur->connexion($_POST['userName'], $_POST['userPassword']) === true) {
            if (!_PHPUNIT_) {
                // Succès -> redirige sur la page d'accueil
                header('Location: ' . _URL_);
                die();
            }
        } else {
            $messageErreur .= "<br />Erreur dans vos identifiants.";
        }
    }
}

require _TPL_TOP_;
// Affichage des erreurs si requis
if (isset($messageErreur)) :
    ?>
    <div class="alert alert-danger">
        <strong>La connexion à votre compte n'est pas possible :</strong>
        <?= $messageErreur ?>
    </div>
<?php endif; ?>
<h1 class="mb-3"><small>Connexion à mon compte</small></h1>

<form method="post">
    <div class="mb-3 row form-floating">
        <input type="text" class="form-control" name="userName" id="userName" placeholder="Identifiant" value="<?= $monUtilisateur->getUserName() ?>" required="required">
        <label for="userName">Identifiant</label>
    </div>
    <div class="mb-3 row form-floating">
        <input type="password" class="form-control" name="userPassword" id="userPassword" placeholder="Mot de passe" required="required">
        <label for="userPassword">Mot de passe</label>
    </div>
    <button type="submit" name="valider" class="btn btn-success"><span class="fas fa-sign-in-alt"></span>&nbsp;Se connecter</button>
    <a class="btn btn-outline-warning" role="button" disabled>Mot de passe oublié (à venir)</a>
    <a class="btn btn-info" href="<?= _URL_MEMBRE_ ?>creerCompte.php" role="button"><span class="fas fa-save"></span>&nbsp;S'inscrire</a>
</form>
<?php require _TPL_BOTTOM_ ?>