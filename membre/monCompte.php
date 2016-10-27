<?php
/*
 * Copyright 2008-2016 Anael Mobilia
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
if (!defined(_TRAVIS_)) {
    require '../config/configV2.php';
}
metaObject::checkUserAccess(utilisateurObject::levelUser);
require _TPL_TOP_;

// Je récupère la session de mon utilisateur
$laSession = new sessionObject();
// Et je reprend ses données
$monUtilisateur = new utilisateurObject($laSession->getId());

if (isset($_POST['modifierPwd'])) {
    // Je vérifie qu'on me donne le bon mot de passe
    if ($monUtilisateur->checkPassword($_POST['oldUserPassword'])) {
        // Je met à jour en BDD
        $monUtilisateur->setPassword($_POST['newUserPassword']);
        $monUtilisateur->modifier();

        // Retour utilisateur
        ?>
        <div class="alert alert-success">Le mot de passe à été mis à jour !</div>
        <?php
    } else {
        // Retour utilisateur
        ?>
        <div class = "alert alert-danger">Le mot de passe actuel ne correspond pas à celui saisi !</div>
        <?php
    }
} else if (isset($_POST['modifierMail'])) {
    // Je vérifie qu'on me donne le bon mot de passe
    if ($monUtilisateur->checkPassword($_POST['userPasswordMail'])) {
        // Vérification du bon format de l'adresse mail
        if (filter_var($_POST['userMail'], FILTER_VALIDATE_EMAIL) != FALSE) {
            // Je met à jour en BDD
            $monUtilisateur->setEmail($_POST['userMail']);
            $monUtilisateur->modifier();

            // Retour utilisateur
            ?>
            <div class="alert alert-success">L'adresse courriel à été mise à jour !</div>
            <?php
        } else {
            // Retour utilisateur
            ?>
            <div class = "alert alert-danger">L'adresse courriel saisie n'est pas correcte !</div>
            <?php
        }
    } else {
        // Retour utilisateur
        ?>
        <div class = "alert alert-danger">Le mot de passe actuel ne correspond pas à celui saisi !</div>
        <?php
    }
} else if (isset($_POST['supprimerCompte'])) {
    // Je vérifie qu'on me donne le bon mot de passe
    if ($monUtilisateur->checkPassword($_POST['userPasswordDelete'])) {
        if (isset($_POST['confirmeDelete'])) {
            // Je met à jour en BDD
            $monUtilisateur->supprimer();
            // Retour utilisateur
            ?>
            <div class="alert alert-success">
                Votre compte a été supprimé !
                <br />
                Les images qui étaient rattachées à votre compte n'ont pas été supprimées et ne seront plus supprimables avant leur expiration.
                <br />
                Cette action est irrévocable !
                <br />
                Merci d'avoir utilisé Image-Heberg.fr .
            </div>
            <?php
            // Déconnexion de la session
            $laSession->deconnexion();
        } else {
            // Retour utilisateur
            ?>
            <div class = "alert alert-danger">Vous n'avez pas coché la case de confirmation de demande de suppression de votre compte !</div>
            <?php
        }
    } else {
        // Retour utilisateur
        ?>
        <div class = "alert alert-danger">Le mot de passe actuel ne correspond pas à celui saisi !</div>
        <?php
    }
}
?>
<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <h1><small>Mon compte Image-Heberg.fr</small></h1>

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <?= $monUtilisateur->getUserName() ?>
            </h3>
        </div>
        <div class="panel-body">
            Membre depuis le : <?= $monUtilisateur->getDateInscriptionFormate() ?>
            <br />
            Adresse courriel : <?= $monUtilisateur->getEmail() ?>
            <br />
            Images possédées : <?= count(metaObject::getAllPicsOffOneUser($monUtilisateur->getId())) ?>
        </div>
    </div>

    <!-- Modification du mot de passe -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapsePwd">Changer de mot de passe <span class="caret"></span></a>
            </h2>
        </div>
        <div id="collapsePwd" class="panel-collapse collapse">
            <div class="panel-body">
                <form method="post">
                    <div class="form-group">
                        <label for="oldUserPassword">Mot de passe actuel</label>
                        <input type="password" class="form-control" name="oldUserPassword" id="oldUserPassword" placeholder="Mot de passe actuel" required="required">
                    </div>
                    <div class="form-group">
                        <label for="newUserPassword">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="newUserPassword" id="newUserPassword" placeholder="Nouveau mot de passe" required="required">
                    </div>
                    <button type="submit" name="modifierPwd" class="btn btn-success">Mettre à jour le mot de passe</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Changer l'adresse mail -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseMail">Changer d'adresse courriel <span class="caret"></span></a>
            </h2>
        </div>
        <div id="collapseMail" class="panel-collapse collapse">
            <div class="panel-body">
                <form method="post">
                    <div class="form-group">
                        <label for="userMail">Nouvelle adresse courriel</label>
                        <input type="email" class="form-control" name="userMail" id="userMail" placeholder="Nouvelle adresse courriel" required="required">
                    </div>
                    <div class="form-group">
                        <label for="userPasswordMail">Mot de passe</label>
                        <input type="password" class="form-control" name="userPasswordMail" id="userPasswordMail" placeholder="Mot de passe" required="required">
                    </div>

                    <button type="submit" name="modifierMail" class="btn btn-success">Mettre à jour l'adresse courriel</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Supprimer le compte -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseDelete">Supprimer mon compte <span class="caret"></span></a>
            </h2>
        </div>
        <div id="collapseDelete" class="panel-collapse collapse">
            <div class="panel-body">
                <form method="post">
                    <label class="text-danger">
                        <input type="checkbox" value="" name="confirmeDelete">
                        <span class="glyphicon glyphicon-warning-sign"></span>
                        Je confirme souhaiter supprimer mon compte Image-Heberg.fr.
                        <br />
                        Les images rattachées à mon compte ne seront pas supprimées et ne seront plus supprimables avant leur expiration.
                        <br />
                        Cette action est irrévocable !
                    </label>

                    <div class="form-group">
                        <label for="userPasswordDelete">Mot de passe</label>
                        <input type="password" class="form-control" name="userPasswordDelete" id="userPasswordDelete" placeholder="Mot de passe" required="required">
                    </div>

                    <button type="submit" name="supprimerCompte" class="btn btn-danger">Supprimer mon compte</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>