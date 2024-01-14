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
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_USER);

// Je récupère la session de mon utilisateur
$maSession = new SessionObject();
// Et je reprend ses données
$monUtilisateur = new UtilisateurObject($maSession->getId());

require _TPL_TOP_;
if (isset($_POST['modifierPwd'])) {
    // Je vérifie qu'on me donne le bon mot de passe
    if ($monUtilisateur->connexion($maSession->getUserName(), $_POST['oldUserPassword'])) {
        // Je met à jour en BDD
        $monUtilisateur->setPasswordToCrypt($_POST['newUserPassword']);
        $monUtilisateur->modifier();

        // Retour utilisateur
        echo '<div class="alert alert-success">Le mot de passe à été mis à jour !</div>';
    } else {
        // Retour utilisateur
        echo '<div class="alert alert-danger">Le mot de passe actuel ne correspond pas à celui saisi !</div>';
    }
} elseif (isset($_POST['modifierMail'])) {
    // Je vérifie qu'on me donne le bon mot de passe
    if ($monUtilisateur->connexion($maSession->getUserName(), $_POST['userPasswordMail'])) {
        // Vérification du bon format de l'adresse mail
        if (filter_var($_POST['userMail'], FILTER_VALIDATE_EMAIL) !== false) {
            // Je met à jour en BDD
            $monUtilisateur->setEmail($_POST['userMail']);
            $monUtilisateur->modifier();

            // Retour utilisateur
            echo '<div class="alert alert-success">L\'adresse courriel à été mise à jour !</div>';
        } else {
            // Retour utilisateur
            echo '<div class="alert alert-danger">L\'adresse courriel saisie n\'est pas correcte !</div>';
        }
    } else {
        // Retour utilisateur
        echo '<div class="alert alert-danger">Le mot de passe actuel ne correspond pas à celui saisi !</div>';
    }
} elseif (isset($_POST['supprimerCompte'])) {
    // Je vérifie qu'on me donne le bon mot de passe
    if ($monUtilisateur->connexion($maSession->getUserName(), $_POST['userPasswordDelete'])) {
        if (isset($_POST['confirmeDelete'])) {
            // Je met à jour en BDD
            $monUtilisateur->supprimer();
            // Retour utilisateur
            ?>
            <div class="alert alert-success">
                Votre compte a été supprimé !
                <br/>
                Les images liées à votre compte n'ont pas été supprimées.
                <br/>
                Cette action est irrévocable !
                <br/>
                Merci d'avoir utilisé <?= _SITE_NAME_ ?>.
            </div>
            <?php

            // Déconnexion de la session
            $maSession->deconnexion();
        } else {
            // Retour utilisateur
            echo '<div class="alert alert-danger">Vous n\'avez pas coché la case de confirmation de demande de suppression de votre compte !</div>';
        }
    } else {
        // Retour utilisateur
        echo '<div class="alert alert-danger">Le mot de passe actuel ne correspond pas à celui saisi !</div>';
    }
}
?>
    <h1 class="mb-3"><small>Mon compte <?= _SITE_NAME_ ?></small></h1>

    <div class="card">
        <div class="card-header">
            <?= $monUtilisateur->getUserName() ?>
        </div>
        <div class="card-body">
            Membre depuis le : <?= $monUtilisateur->getDateInscriptionFormate() ?>
            <br/>
            Adresse courriel : <?= $monUtilisateur->getEmail() ?>
            <br/>
            Images possédées : <?= count($monUtilisateur->getImages()) ?>
        </div>
    </div>

    <!-- Modification du mot de passe -->
    <div class="card card-default">
        <div class="card-header">
            <a data-bs-toggle="collapse" href="#collapsePwd">
                Changer de mot de passe <span class="bi-caret-down-fill"></span>
            </a>
        </div>
        <div id="collapsePwd" class="card-collapse collapse">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="oldUserPassword" class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control" name="oldUserPassword" id="oldUserPassword" placeholder="Mot de passe actuel" required="required">
                    </div>
                    <div class="mb-3">
                        <label for="newUserPassword" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="newUserPassword" id="newUserPassword" placeholder="Nouveau mot de passe" required="required">
                    </div>
                    <button type="submit" name="modifierPwd" class="btn btn-success">Modifier le mot de passe</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Changer l'adresse mail -->
    <div class="card card-default">
        <div class="card-header">
            <a data-bs-toggle="collapse" href="#collapseMail">
                Changer d'adresse courriel <span class="bi-caret-down-fill"></span>
            </a>
        </div>
        <div id="collapseMail" class="card-collapse collapse">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="userMail" class="form-label">Nouvelle adresse courriel</label>
                        <input type="email" class="form-control" name="userMail" id="userMail"
                               placeholder="Nouvelle adresse courriel" required="required">
                    </div>
                    <div class="mb-3">
                        <label for="userPasswordMail" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" name="userPasswordMail" id="userPasswordMail"
                               placeholder="Mot de passe" required="required">
                    </div>

                    <button type="submit" name="modifierMail" class="btn btn-success">Modifier l'adresse courriel</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Supprimer le compte -->
    <div class="card card-default">
        <div class="card-header">
            <a data-bs-toggle="collapse" href="#collapseDelete">
                Supprimer mon compte <span class="bi-caret-down-fill"></span>
            </a>
        </div>
        <div id="collapseDelete" class="card-collapse collapse">
            <div class="card-body">
                <form method="post">
                    <label class="text-danger form-label">
                        <input type="checkbox" value="" name="confirmeDelete">
                        <span class="bi-exclamation-triangle-fill"></span>
                        Je confirme souhaiter supprimer mon compte <?= _SITE_NAME_ ?>.
                        <br/>
                        <b>Les images rattachées à mon compte ne seront pas supprimées et ne seront plus supprimables avant leur expiration !</b>
                        <br/>
                        Cette action est irrévocable !
                    </label>

                    <div class="mb-3">
                        <label for="userPasswordDelete" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" name="userPasswordDelete" id="userPasswordDelete" placeholder="Mot de passe" required="required">
                    </div>

                    <button type="submit" name="supprimerCompte" class="btn btn-danger">Supprimer mon compte</button>
                </form>
            </div>
        </div>
    </div>
    <?php require _TPL_BOTTOM_ ?>