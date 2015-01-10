<?php
/*
* Copyright 2008-2015 Anael Mobilia
*
* This file is part of NextINpact-Unofficial.
*
* NextINpact-Unofficial is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextINpact-Unofficial is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextINpact-Unofficial. If not, see <http://www.gnu.org/licenses/>
*/
$timeStart = microtime(TRUE); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">

        <base href="<?= _URL_ ?>" />

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Hebergement d'images gratuit et illimité">
        <meta name="author" content="Anael Mobilia">
        <link rel="shortcut icon" href="template/images/favicon.ico">

        <title>Image-Heberg.fr</title>

        <!-- Bootstrap core CSS -->
        <link href="template/css/bootstrap-3.2.0.min.css" rel="stylesheet">
        <link href="template/css/template.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="navbar-fixed-top.css" rel="stylesheet">
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
                            <li class = "<?= ($_SERVER['SCRIPT_NAME'] == "/index.php") ? "active" : "" ?>"><a href="<?= _URL_ ?>">Accueil</a></li>
                            <li class = "<?= ($_SERVER['SCRIPT_NAME'] == "/a_propos.php") ? "active" : "" ?>"><a href="<?= _URL_ ?>a_propos.php">A propos</a></li>
                            <li class = "<?= ($_SERVER['SCRIPT_NAME'] == "/contact.php") ? "active" : "" ?>"><a href="<?= _URL_ ?>contact.php">Contact</a></li>
                        </ul>       
                        <?php
                        $visiteur = new sessionObject();
                        // Menu utilisateur non connecté
                        if ($visiteur->getLevel() === utilisateurObject::levelGuest) :
                            ?>
                            <div id="monCompteGestion">
                                <form class="navbar-form navbar-right" role="form">
                                    <button id="buttonMonCompteGestion" class="btn btn-success">Mon compte</button>
                                </form>
                            </div>
                            <div id="monCompte">
                                <form action="<?= _URL_MEMBRE_ ?>creerCompte.php" class="navbar-form navbar-right" role="form">
                                    <button type="submit" class="btn btn-success">S'enregistrer</button>
                                </form>
                                <form action="<?= _URL_MEMBRE_ ?>connexionCompte.php" method="post" class="navbar-form navbar-right" role="form">
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
                            <form action="<?= _URL_MEMBRE_ ?>deconnexionCompte.php" class="navbar-form navbar-right" role="form">
                                <button type="submit" class="btn btn-success">
                                    Se déconnecter (<?= $visiteur->getUserName() ?>)
                                </button>
                            </form>
                            <!-- Bloc à déclarer en second... les float feront qu'il sera à gauche -->
                            <ul class="nav navbar-nav navbar-right">
                                <li class = "<?= ($_SERVER['SCRIPT_NAME'] == "/membre/mesImages.php") ? "active" : "" ?>"><a href="<?= _URL_MEMBRE_ ?>mesImages.php">Mes images</a></li>
                                <li class = "<?= ($_SERVER['SCRIPT_NAME'] == "/membre/monCompte.php") ? "active" : "" ?>"><a href="<?= _URL_MEMBRE_ ?>monCompte.php">Mon compte</a></li>
                                <!-- Si c'est un admin : lien vers le panneau admin -->
                                <?php if ($visiteur->getLevel() === utilisateurObject::levelAdmin) : ?>
                                    <li class = "<?= ($_SERVER['SCRIPT_NAME'] == "/admin/index.php") ? "active" : "" ?>"><a href="<?= _URL_ADMIN_ ?>">Administration</a></li>
                                <?php endif; ?>
                            </ul>  
                        <?php endif; ?>
                    </div>
                </div><!--/.nav-collapse -->
            </div>

            <div class="container">
                <div class="alert alert-info alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    Voici la nouvelle interface (bêta) d'Image-Heberg.fr !
                    <br />
                    <a href="contact.php">Un avis, une remarque, une suggestion ?</a>
                </div>