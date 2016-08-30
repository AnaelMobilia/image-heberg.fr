<?php
/*
* Copyright 2008-2015 Anael Mobilia
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
require '../config/configV2.php';

// Un utilisateur...
$monUtilisateur = new utilisateurObject();

// En cas de validation du formulaire
if (isset($_POST['valider'])) {
    // Flag pour la connexion de l'utilisateur
    $flagConnexion = TRUE;
    $messageErreur = '';

    if (empty($_POST['userName'])) {
        $flagConnexion = FALSE;
        $messageErreur .= "<br />Merci de saisir un identifiant.";
    }
    if (empty($_POST['userPassword'])) {
        $flagConnexion = FALSE;
        $messageErreur .= "<br />Merci de saisir un mot de passe.";
    }

    // Données fournies par l'utilisateur
    // Nom d'utilisateur
    $monUtilisateur->setUserName($_POST['userName']);
    // Mot de passe
    $monUtilisateur->setPassword($_POST['userPassword']);

    // Si tout est bon
    if ($flagConnexion === TRUE) {
        // On lance la connexion
        if ($monUtilisateur->connexion() === TRUE) {
            // Succès -> redirige sur la page d'accueil
            header('Location: ' . _URL_);
            die();
        } else {
            // Appel du template à ce niveau seulement pour éviter d'envoyer des headers si redirection
            require _TPL_TOP_;
            $flagConnexion = FALSE;
            $messageErreur .= "<br />Erreur dans vos identifiants.";
        }
    }

    // Affichage des erreurs si requis
    if ($flagConnexion !== TRUE) {
        ?>
        <div class="alert alert-danger">
            <strong>La connexion à votre compte n'est pas possible :</strong>
            <?= $messageErreur ?>
        </div>
        <?php
    }
}
?>
<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <h1><small>Se connecter à mon compte</small></h1>

    <form role="form" method="post">
        <div class="form-group">
            <label for="userName">Identifiant</label>
            <input type="text" class="form-control" name="userName" id="userName" placeholder="Identifiant" value="<?= $monUtilisateur->getUserName() ?>" required="required">
        </div>
        <div class="form-group">
            <label for="userPassword">Mot de passe</label>
            <input type="password" class="form-control" name="userPassword" id="userPassword" placeholder="Mot de passe" required="required">
        </div>
        <div class="form-group">
            <!-- // TODO -->
            <a href="#">Mot de passe oublié (à venir)</a>
        </div>
        <button type="submit" name="valider" class="btn btn-success">Se connecter</button>
    </form>
    <br />
    <form action="<?= _URL_MEMBRE_ ?>creerCompte.php" role="form">
        <button type="submit" class="btn btn-info">Créer un compte</button>
    </form>
</div>
<?php require _TPL_BOTTOM_ ?>