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
                    if ($visiteur->getLevel() === UtilisateurObject::levelGuest) :
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
                            <?php if ($visiteur->getLevel() === UtilisateurObject::levelAdmin) : ?>
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
                    <a href="https://github.com/AnaelMobilia/image-heberg.fr" title="Voir le code source sur GitHub">
                        <svg class="forkMeOnGitHub" height="100%" viewBox="0 0 250 250">
                        <path d="M0,0 L115,115 L130,115 L142,142 L250,250 L250,0 Z"></path>
                        <path d="M128.3,109.0 C113.8,99.7 119.0,89.6 119.0,89.6 C122.0,82.7 120.5,78.6 120.5,78.6 C119.2,72.0 123.4,76.3 123.4,76.3 C127.3,80.9 125.5,87.3 125.5,87.3 C122.9,97.6 130.6,101.9 134.4,103.2" fill="currentColor" style="transform-origin: 130px 106px;" class="octo-arm"></path>
                        <path d="M115.0,115.0 C114.9,115.1 118.7,116.5 119.8,115.4 L133.7,101.6 C136.9,99.2 139.9,98.4 142.2,98.6 C133.8,88.0 127.5,74.4 143.8,58.0 C148.5,53.4 154.0,51.2 159.7,51.0 C160.3,49.4 163.2,43.6 171.4,40.1 C171.4,40.1 176.1,42.5 178.8,56.2 C183.1,58.6 187.2,61.8 190.9,65.4 C194.5,69.0 197.7,73.2 200.1,77.6 C213.8,80.2 216.3,84.9 216.3,84.9 C212.7,93.1 206.9,96.0 205.4,96.6 C205.1,102.4 203.0,107.8 198.3,112.5 C181.9,128.9 168.3,122.5 157.7,114.1 C157.9,116.9 156.7,120.9 152.7,124.9 L141.0,136.5 C139.8,137.7 141.6,141.9 141.8,141.8 Z" fill="currentColor" class="octo-body"></path>
                        </svg>
                    </a>
                </div>
            </nav>
        </header>
        <!-- Wrap all page content here -->
        <main class="container">
            <div class="jumbotron">