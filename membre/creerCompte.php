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
   require '../config/config.php';
}
require _TPL_TOP_;

// Un utilisateur...
$monUtilisateur = new utilisateurObject();

// En cas de validation du formulaire
if (isset($_POST['valider'])) {
   // Flag pour la création de l'utilisateur
   $flagCreation = TRUE;
   $messageErreur = '';

   if (empty($_POST['userName'])) {
      $flagCreation = FALSE;
      $messageErreur .= "<br />Merci de saisir un identifiant.";
   }
   if (empty($_POST['userPassword'])) {
      $flagCreation = FALSE;
      $messageErreur .= "<br />Merci de saisir un mot de passe.";
   }
   if (empty($_POST['userMail'])) {
      $flagCreation = FALSE;
      $messageErreur .= "<br />Merci de saisir une adresse courriel.";
   }
   // Vérification du bon format de l'adresse mail
   if (filter_var($_POST['userMail'], FILTER_VALIDATE_EMAIL) === FALSE) {
      $flagCreation = FALSE;
      $messageErreur .= "<br />L'adresse courriel saisie n'est pas correcte.";
   }
   // Disponibilité du login
   if (metaObject::verifierLoginDisponible($_POST['userName']) !== TRUE) {
      $flagCreation = FALSE;
      $messageErreur .= "<br />Ce nom d'utilisateur n'est pas disponible. Merci d'en choisir un autre.";
   }

   // Données administratives : droits de l'utilisateur
   $monUtilisateur->setLevel(utilisateurObject::levelUser);
   // Données fournies par l'utilisateur
   // Nom d'utilisateur
   $monUtilisateur->setUserName($_POST['userName']);
   // Mot de passe - Crypté
   $monUtilisateur->setPasswordToCrypt($_POST['userPassword']);
   // Adresse mail
   $monUtilisateur->setEmail($_POST['userMail']);

   // Si tout est bon
   if ($flagCreation) {
      // Création de l'utilisateur
      $monUtilisateur->enregistrer();
      // Connexion de l'utilisateur
      $monUtilisateur->connexion($_POST['userName'], $_POST['userPassword']);

      // TODO : envoi d'un mail avec les identifiants de l'utilisateur
      // voir une création de compte avec validation par mail ?

      if (!_TRAVIS_) {
         // Redirection sur la page d'accueil - sauf si mode tests
         header('Location: ' . _URL_);
         die();
      }
   } else {
      ?>
      <div class="alert alert-danger">
          <strong>La création de votre compte n'est pas possible :</strong>
          <?= $messageErreur ?>
      </div>
      <?php
   }
}
?>
<h1><small>Créer mon compte</small></h1>

<form method="post">
    <div class="form-group">
        <label for="userName">Identifiant</label>
        <input type="text" class="form-control" name="userName" id="userName" placeholder="Identifiant" value="<?= $monUtilisateur->getUserName() ?>" required="required">
    </div>
    <div class="form-group">
        <label for="userPassword">Mot de passe</label>
        <input type="password" class="form-control" name="userPassword" id="userPassword" placeholder="Mot de passe" required="required">
    </div>
    <div class="form-group">
        <label for="userMail">Adresse courriel</label>
        <input type="email" class="form-control" name="userMail" id="userMail" placeholder="Adresse courriel" value="<?= $monUtilisateur->getEmail() ?>" required="required">
        <span class="help-block">Utilisée uniquement en cas de réinitialisation de votre mot de passe.</span>
    </div>
    <button type="submit" name="valider" class="btn btn-success">M'enregistrer</button>
</form>
<?php require _TPL_BOTTOM_ ?>