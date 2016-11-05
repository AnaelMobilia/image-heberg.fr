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
if (!defined('_TRAVIS_')) {
    require 'config/configV2.php';
}
require _TPL_TOP_;

$erreur = FALSE;
$msgErreur = '';

/**
 * Vérification de l'utilisation normale
 */
if (isset($_POST['Submit']) && isset($_SESSION['_upload'])) {
    // Suppression du marqueur d'affichage du formulaire d'envoi
    unset($_SESSION['_upload']);
} else {
    $erreur = TRUE;
    $msgErreur .= 'La page n\'a pas été appelée correctement.<br />';
}

/**
 * Vérification de la présence d'un fichier
 */
if (!$erreur && (!isset($_FILES['fichier']['name']) || empty($_FILES['fichier']['name']))) {
    $erreur = TRUE;
    $msgErreur .= 'Aucun fichier n\'a été envoyé.<br />';
}

/**
 * Vérification du poids (Mo)
 */
if (!$erreur) {
    $poids = $_FILES['fichier']['size'];
    // TODO : mieux gérer ce cas
    if ($poids > _IMAGE_POIDS_MAX_ && !isset($_SESSION['connected'])) {
        $erreur = TRUE;
        $msgErreur .= 'Le poids du fichier ' . round($poids / 1048576, 1) . ' Mo) dépasse la limité autorisée (' . round(_IMAGE_POIDS_MAX_ / 1048576, 1) . ' Mo).<br />';
    }
}

/**
 * Vérification du type mime
 */
if (!$erreur) {
    $pathTmp = $_FILES['fichier']['tmp_name'];
    // Type mime autorisés
    $mimeType = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
    if (!in_array(outils::getType($pathTmp), $mimeType)) {
        $erreur = TRUE;
        $msgErreur .= 'Ce fichier n\'est pas une image valide.<br />';
    }
}

/**
 * Vérification des dimensions
 */
if (!$erreur) {
    if (!outils::isModifiableEnMemoire($pathTmp)) {
        $erreur = TRUE;
        $msgErreur .= 'Les dimensions de l\'image dépassent la limite autorisée ' . _IMAGE_DIMENSION_MAX_ . ' x ' . _IMAGE_DIMENSION_MAX_ . '<br />';
    }
}

/**
 * Création d'une image pour effectuer les traitements requis
 */
if (!$erreur) {
    $monImage = new imageObject();
    $monImage->setPathTemp($pathTmp);
}

/**
 * Traitement du redimensionnement
 */
if (!$erreur) {
// @TODO
}

/**
 * Traitement de la rotation
 */
if (!$erreur && isset($_POST['angleRotation']) && is_numeric($_POST['angleRotation'])) {
    // On effectue la rotation
    $result = $monImage->rotation($_POST['angleRotation'], $monImage->getPathTemp(), $monImage->getPathTemp());

    // Une erreur ?
    if (!$result) {
        $msgErreur .= 'Impossible d\'effectuer la rotation.<br />';
    }
}

/**
 * Vérification du non réenvoi par la même personne
 */
if (!$erreur) {
    // Infos de l'image
    $monMD5 = md5_file($monImage->getPathTemp());
    $monIP = $_SERVER['REMOTE_ADDR'];

    // Info de l'utilisateur
    $maSession = new sessionObject();
    $monUtilisateur = new utilisateurObject();
    // Si j'ai un ID d'utilisateur en session...
    if ($maSession->getId() !== 0) {
        // Je charge mon utilisateur
        $monUtilisateur->charger($maSession->getId());
    }

    // Est-ce un doublon ?
    $doublon = outils::verifierRenvoiImage($monMD5, $monIP, $monUtilisateur);

    if (!is_null($doublon)) {
        // C'est un doublon -> chargement de l'image existante
        $monImage = new imageObject($doublon);
    } else {
        // Création de l'image
        $monImage->setNomTemp($_FILES['fichier']['name']);
        if (!$monImage->creer()) {
            $erreur = TRUE;
            $msgErreur .= 'Erreur lors de l\'enregistrement du fichier.<br />';
        }
    }
}

/**
 * Gestion du propriétaire
 */
if (!$erreur) {
    $maSession = new sessionObject();
    // Si j'ai un ID d'utilisateur en session && que cette image n'est pas déjà enregistrée
    if (is_int($maSession->getId()) && $maSession->getId() !== 0 && is_null($doublon)) {
        // Nouvelle image : assignation à l'utilisateur
        $monUtilisateur = new utilisateurObject($maSession->getId());
        $monUtilisateur->assignerImage($monImage);
    }
}

/**
 * Traitement de la miniature
 */
if (!$erreur) {
// @TODO
//    if (isset($_POST['thumbs'])) {//Faut-il faire une miniature?
//        if (isset($_POST['t_size'])) {
////		$t_width = substr(stristr($_POST['t_size'], 'x'), 1);
////		//renvoi la chaine apres x
////		$t_height = substr($_POST['t_size'], 0, strlen($t_width));
//
//            $t_height = substr(stristr($_POST['t_size'], 'x'), 1);
////renvoi la chaine apres x
//            $t_width = substr($_POST['t_size'], 0, strlen($t_height));
//
////PHP 5.3.0 =>$t_height	= stristr($_POST['t_size'], 'x', TRUE);				//renvoi la chaine avant x
//
//            if (is_nan($t_width) || is_nan($t_height)) {//on verifie que ce soient bien des chiffres
////140x100
//                $t_width = __DEFAULT_T_WIDTH__;
//                $t_height = __DEFAULT_T_HEIGHT__;
//            }
//        } else {//si aucune taille n'est définie, utilisation des valeurs par defaut
//            $t_width = __DEFAULT_T_WIDTH__;
////140x100
//            $t_height = __DEFAULT_T_HEIGHT__;
//        }
//        include (__PATH__ . "thumbs.php");
////on appelle le fichier gerant la creation des miniatures
//    }
}
?>
<div class="jumbotron">
    <h1><small>Envoi d'une image</small></h1>
    <?php if (!empty($msgErreur)): ?>
        <div class="alert alert-danger">
            <span class="glyphicon glyphicon-remove"></span>
            &nbsp;
            <b>Une erreur a été rencontrée !</b>
            <br />
            <?= $msgErreur ?>
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            <span class="glyphicon glyphicon-ok"></span>
            &nbsp;
            <b>Image enregistrée avec succès !</b>
        </div>
        <div class="panel panel-primary">
            <div class="panel-body">
                <h2>Afficher l'image</h2>
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Lien direct</label>
                        <div class="col-sm-10">
                            <a href="<?= $monImage->getURL() ?>"><?= $monImage->getURL() ?></a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Forum <em>(BBcode)</em></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" onFocus="this.select();" value="[img]<?= $monImage->getURL() ?>[/img]" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">HTML</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" onFocus="this.select();" value='<a href="<?= $monImage->getURL() ?>"><?= $monImage->getNomOriginalFormate() ?></a>' />
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <br />
                <div>
                    <span class="col-sm-2">Nom de l'image</span>
                    <span class="col-sm-10"><?= $monImage->getNomOriginalFormate() ?> </span>
                </div>
                <div>
                    <span class="col-sm-2">Poids</span>
                    <span class="col-sm-10"><?= $monImage->getPoids() ?>&nbsp;octets</span>
                </div>
                <div>
                    <span class="col-sm-2">Largeur</span>
                    <span class="col-sm-10"><?= $monImage->getLargeur() ?>&nbsp;px</span>
                </div>
                <div>
                    <span class="col-sm-2">Hauteur</span>
                    <span class="col-sm-10"><?= $monImage->getHauteur() ?>&nbsp;px</span>
                </div>
                <div class="clearfix"></div>
                <br />
                <a href="<?= _URL_ ?>" class="btn btn-success">
                    <span class="glyphicon glyphicon-cloud-upload"></span>
                    &nbsp;
                    Envoyer une autre image
                </a>
                <a href="<?= _URL_ ?>delete.php?id=<?= $monImage->getNomNouveau() ?>" class="btn btn-danger">
                    <span class="glyphicon glyphicon-trash"></span>
                    &nbsp;
                    Effacer cette image
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php
require _TPL_BOTTOM_;
?>