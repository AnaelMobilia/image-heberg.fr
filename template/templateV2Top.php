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
$timeStart = microtime(TRUE);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">

        <base href="<?= _URL_ ?>" />

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Hebergement d'images gratuit et illimité">
        <meta name="author" content="Anael Mobilia">
        <link rel="shortcut icon" href="template/images/favicon.ico">

        <title>Image-Heberg.fr - Hébergeur d'images gratuit</title>

        <!-- Bootstrap core CSS -->
        <link href="template/css/bootstrap-3.3.7.min.css" rel="stylesheet">
        <link href="template/css/template.min.css" rel="stylesheet">
    </head>

    <body>
        <!-- Wrap all page content here -->
        <div id="wrap">
            <!-- Fixed navbar -->
            <div class="navbar navbar-default navbar-fixed-top" role="navigation">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="sr-only">Menu</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="#">
                            <span class="glyphicon glyphicon-cloud-upload"></span>
                            &nbsp;
                            Image-Heberg
                        </a>
                    </div>
                    <div class="navbar-collapse collapse">
                        <ul class="nav navbar-nav">
                            <li <?= ($_SERVER['SCRIPT_NAME'] == "/index.php") ? "class='active'" : "" ?>><a href="<?= _URL_ ?>">Accueil</a></li>
                            <li <?= ($_SERVER['SCRIPT_NAME'] == "/a_propos.php") ? "class='active'" : "" ?>><a href="<?= _URL_ ?>a_propos.php">A propos</a></li>
                            <li <?= ($_SERVER['SCRIPT_NAME'] == "/contact.php") ? "class='active'" : "" ?>><a href="<?= _URL_ ?>contact.php">Contact</a></li>
                        </ul>
                        <?php
                        $visiteur = new sessionObject();
                        // Menu utilisateur non connecté
                        if ($visiteur->getLevel() === utilisateurObject::levelGuest) :
                            ?>
                            <div id="monCompteGestion">
                                <form class="navbar-form navbar-right">
                                    <button id="buttonMonCompteGestion" class="btn btn-success">Mon compte</button>
                                </form>
                            </div>
                            <div id="monCompte">
                                <form action="<?= _URL_MEMBRE_ ?>creerCompte.php" class="navbar-form navbar-right">
                                    <button type="submit" class="btn btn-success">S'enregistrer</button>
                                </form>
                                <form action="<?= _URL_MEMBRE_ ?>connexionCompte.php" method="post" class="navbar-form navbar-right">
                                    <div class="form-group">
                                        <input type="text" name="userName" placeholder="Identifiant" class="form-control" required="required">
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="userPassword" placeholder="Mot de passe" class="form-control" required="required">
                                    </div>
                                    <button type="submit" name="valider" class="btn btn-success">Se connecter</button>
                                </form>
                            </div>
                        <?php else : ?>
                            <!-- Menu utilisateur connecté -->
                            <form action="<?= _URL_MEMBRE_ ?>deconnexionCompte.php" class="navbar-form navbar-right">
                                <button type="submit" class="btn btn-success">
                                    Se déconnecter (<?= $visiteur->getUserName() ?>)
                                </button>
                            </form>
                            <!-- Bloc à déclarer en second... les float feront qu'il sera à gauche -->
                            <ul class="nav navbar-nav navbar-right">
                                <li <?= ($_SERVER['SCRIPT_NAME'] == "/membre/mesImages.php") ? "class='active'" : "" ?>><a href="<?= _URL_MEMBRE_ ?>mesImages.php">Mes images</a></li>
                                <li <?= ($_SERVER['SCRIPT_NAME'] == "/membre/monCompte.php") ? "class='active'" : "" ?>><a href="<?= _URL_MEMBRE_ ?>monCompte.php">Mon compte</a></li>
                                <!-- Si c'est un admin : lien vers le panneau admin -->
                                <?php if ($visiteur->getLevel() === utilisateurObject::levelAdmin) : ?>
                                    <li <?= ($_SERVER['SCRIPT_NAME'] == "/admin/index.php") ? "class='active'" : "" ?>><a href="<?= _URL_ADMIN_ ?>">Administration</a></li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div><!--/.nav-collapse -->
            </div>

            <div class="container">