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

$timeStart = microtime(true);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">

        <base href="<?= _URL_SANS_SCHEME_ ?>" />

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Hebergement d'images gratuit et illimité">
        <meta name="author" content="Anael Mobilia">
        <link rel="shortcut icon" href="template/images/monSite.ico">

        <title><?= _SITE_NAME_ ?> - Hébergeur d'images gratuit</title>

        <!-- Bootstrap core CSS -->
        <link href="template/css/bootstrap-4.4.1.min.css" rel="stylesheet" type="text/css">
        <link href="template/css/image-heberg.min.css" rel="stylesheet">
        <link href="template/css/fontawesome-solid-5.12.min.css" rel="stylesheet">
        <link href="template/css/fontawesome-5.12.min.css" rel="stylesheet">
        <link href="template/css/monSite.css" rel="stylesheet">
    </head>

    <body>
        <header class="header">
            <!-- Fixed navbar -->
            <nav class="navbar navbar-expand-md navbar-light fixed-top">
                <a class="navbar-brand" href="#"><span class="fas fa-cloud-upload-alt"></span>
                    &nbsp;
                    <?= _SITE_NAME_ ?>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
                        <li class="nav-item<?= ($_SERVER['SCRIPT_NAME'] === "/index.php") ? " active" : "" ?>">
                            <a class="nav-link" href="<?= _URL_HTTPS_ ?>">
                                <span class="fas fa-home"></span>&nbsp;
                                Accueil
                            </a>
                        </li>
                        <li class="nav-item<?= ($_SERVER['SCRIPT_NAME'] === "/a_propos.php") ? " active" : "" ?>">
                            <a class="nav-link" href="<?= _URL_HTTPS_ ?>a_propos.php">
                                <span class="fas fa-cloud"></span>&nbsp;
                                A propos
                            </a>
                        </li>
                        <li class="nav-item<?= ($_SERVER['SCRIPT_NAME'] === "/contact.php") ? " active" : "" ?>">
                            <a class="nav-link" href="<?= _URL_HTTPS_ ?>contact.php">
                                <span class="fas fa-envelope"></span>&nbsp;
                                Contact
                            </a>
                        </li>
                    </ul>
                    <?php

                    $visiteur = new SessionObject();
                    // Menu utilisateur non connecté
                    if ($visiteur->getLevel() === UtilisateurObject::LEVEL_GUEST) :
                        ?>
                        <div id="monCompteGestion">
                            <form class="form-inline my-2 my-lg-0">
                                <button id="buttonMonCompteGestion" class="btn btn-success">
                                    <span class="fas fa-user"></span>&nbsp;
                                    Mon compte
                                </button>
                            </form>
                        </div>
                        <div id="monCompte" class="my-2 my-lg-0">
                            <form action="<?= _URL_MEMBRE_ ?>connexionCompte.php" method="post" class="form-inline">
                                <div class="form-group">
                                    <input type="text" name="userName" placeholder="Identifiant" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <input type="password" name="userPassword" placeholder="Mot de passe" class="form-control" required="required">
                                </div>
                                <button type="submit" name="valider" class="btn btn-success">
                                    <span class="fas fa-sign-in-alt"></span>&nbsp;
                                    Se connecter
                                </button>
                                <a href="<?= _URL_MEMBRE_ ?>creerCompte.php"  class="btn btn-success">
                                    <span class="fas fa-save"></span>&nbsp;
                                    S'enregistrer
                                </a>
                            </form>
                        </div>
                    <?php else : ?>
                        <!-- Menu utilisateur connecté -->
                        <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
                            <li class="nav-item<?= ($_SERVER['SCRIPT_NAME'] === "/membre/mesImages.php") ? " active" : "" ?>">
                                <a class="nav-link" href="<?= _URL_MEMBRE_ ?>mesImages.php">
                                    <span class="fas fa-images"></span>&nbsp;
                                    Mes images
                                </a>
                            </li>
                            <li class="nav-item<?= ($_SERVER['SCRIPT_NAME'] === "/membre/monCompte.php") ? " active" : "" ?>">
                                <a class="nav-link" href="<?= _URL_MEMBRE_ ?>monCompte.php">
                                    <span class="fas fa-user"></span>&nbsp;
                                    Mon compte
                                </a>
                            </li>
                            <!-- Si c'est un admin : lien vers le panneau admin -->
                            <?php if ($visiteur->getLevel() === UtilisateurObject::LEVEL_ADMIN) : ?>
                                <li class="nav-item<?= ($_SERVER['SCRIPT_NAME'] === "/admin/index.php") ? " active" : "" ?>">
                                    <a class="nav-link" href="<?= _URL_ADMIN_ ?>">
                                        <span class="fas fa-wrench"></span>&nbsp;
                                        Administration
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <form action="<?= _URL_MEMBRE_ ?>deconnexionCompte.php" class="form-inline">
                            <button type="submit" class="btn btn-success">
                                <span class="fas fa-power-off"></span>&nbsp;
                                Se déconnecter (<?= $visiteur->getUserName() ?>)
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </nav>
        </header>
        <!-- Wrap all page content here -->
        <main class="container">
            <div class="jumbotron">