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
if (!defined('_TRAVIS_')) {
    require __DIR__ . '/../config/config.php';
}

// Un utilisateur...
$monUtilisateur = new utilisateurObject();

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
        if ($monUtilisateur->connexion($_POST['userName'], $_POST['userPassword']) === TRUE) {
            if (!_TRAVIS_) {
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
<h1><small>Se connecter à mon compte</small></h1>

<form method="post">
    <div class="form-group">
        <label for="userName">Identifiant</label>
        <input type="text" class="form-control" name="userName" id="userName" placeholder="Identifiant" value="<?= $monUtilisateur->getUserName() ?>" required="required">
    </div>
    <div class="form-group">
        <label for="userPassword">Mot de passe</label>
        <input type="password" class="form-control" name="userPassword" id="userPassword" placeholder="Mot de passe" required="required">
    </div>
    <button type="submit" name="valider" class="btn btn-success">Se connecter</button>
    <a class="btn btn-warning" href="#" role="button">Mot de passe oublié (à venir)</a>
    <a class="btn btn-info" href="<?= _URL_MEMBRE_ ?>creerCompte.php" role="button">Créer un compte</a>
</form>
<?php require _TPL_BOTTOM_ ?>