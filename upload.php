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

if (!defined('_PHPUNIT_')) {
    require 'config/config.php';
}
require _TPL_TOP_;

$msgErreur = '';
$msgWarning = '';

// Gestion de la session
$maSession = new SessionObject();
/**
 * Vérification de l'utilisation normale
 */
if (isset($_POST['Submit']) && $maSession->checkFlag()) {
    // Suppression du marqueur d'affichage du formulaire d'envoi
    $maSession->removeFlag();
} else {
    $msgErreur .= 'La page n\'a pas été appelée correctement.<br />';
}

/**
 * Vérification de la présence d'un fichier
 */
if (empty($msgErreur) && (!isset($_FILES['fichier']['name']) || empty($_FILES['fichier']['name']))) {
    $msgErreur .= 'Aucun fichier n\'a été envoyé.<br />';
}

//foreach...
// cumul des messages d'erreur
// Affichage une seule fois des erreurs
// Affichage en boucle des infos de l'image...

/**
 * Vérification du poids (Mo)
 */
if (empty($msgErreur)) {
    $poids = $_FILES['fichier']['size'];
    if ($poids > _IMAGE_POIDS_MAX_) {
        $msgErreur .= 'Le poids du fichier ' . $_FILES['fichier']['name'] . ' (' . round($poids / 1048576, 1)
                . ' Mo) dépasse la limité autorisée (' . round(_IMAGE_POIDS_MAX_ / 1048576, 1) . ' Mo).<br />';
    }
}

/**
 * Vérification du type mime
 */
if (empty($msgErreur)) {
    $pathTmp = $_FILES['fichier']['tmp_name'];
    // Type mime autorisés
    $mimeType = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
    if (!in_array(Outils::getType($pathTmp), $mimeType)) {
        $msgErreur .= 'Le fichier ' . $_FILES['fichier']['name'] . ' n\'est pas une image valide.<br />';
    }
}

/**
 * Vérification des dimensions
 */
if (empty($msgErreur)) {
    if (!Outils::isModifiableEnMemoire($pathTmp)) {
        $msgErreur .= 'Les dimensions de l\'image ' . $_FILES['fichier']['name'] . ' dépassent la limite autorisée '
                . _IMAGE_DIMENSION_MAX_ . ' x ' . _IMAGE_DIMENSION_MAX_ . '<br />';
    }
}

/**
 * Création d'une image pour effectuer les traitements requis
 */
if (empty($msgErreur)) {
    $monImage = new ImageObject();
    $monImage->setPathTemp($pathTmp);
    $monImage->setNomTemp($_FILES['fichier']['name']);
}

/**
 * Traitement du redimensionnement
 */
if (empty($msgErreur) && isset($_POST['redimImage']) && !empty($_POST['redimImage'])) {
    // Calcul des dimensions demandées [largeur]x[hauteur]
    $maLargeur = substr(strstr($_POST['redimImage'], 'x'), 1);
    $maHauteur = strstr($_POST['redimImage'], 'x', true);

    $result = $monImage->redimensionner($monImage->getPathTemp(), $monImage->getPathTemp(), $maLargeur, $maHauteur);

    // Une erreur ?
    if (!$result) {
        $msgWarning .= 'Impossible d\'effectuer le redimensionnement de ' . $_FILES['fichier']['name'] . '.<br />';
    }
}

/**
 * Traitement de la rotation
 */
if (empty($msgErreur) && isset($_POST['angleRotation']) && is_numeric($_POST['angleRotation'])) {
    // On effectue la rotation
    $result = $monImage->rotation($_POST['angleRotation'], $monImage->getPathTemp(), $monImage->getPathTemp());

    // Une erreur ?
    if (!$result) {
        $msgWarning .= 'Impossible d\'effectuer la rotation de ' . $_FILES['fichier']['name'] . ' .<br />';
    }
}

/**
 * Enregistrement de l'image une fois les traitements effectués
 */
if (empty($msgErreur) && !$monImage->creer()) {
    $msgErreur .= 'Erreur lors de l\'enregistrement du fichier de l\'image ' . $_FILES['fichier']['name'] . ' .<br />';
}

/**
 * Gestion du propriétaire
 */
if (empty($msgErreur) && $maSession->getId() !== 0) {
    // Assignation à l'utilisateur
    $monUtilisateur = new UtilisateurObject($maSession->getId());
    $monUtilisateur->assignerImage($monImage);
}

/**
 * Traitement de la miniature
 */
if (empty($msgErreur) && isset($_POST['dimMiniature']) && !empty($_POST['dimMiniature'])) {
    // Calcul des dimensions demandées [largeur]x[hauteur]
    $maLargeur = substr(strstr($_POST['dimMiniature'], 'x'), 1);
    $maHauteur = strstr($_POST['dimMiniature'], 'x', true);

    // Création d'un objet
    $maMiniature = new MiniatureObject();
    $maMiniature->setPathTemp($pathTmp);
    // ID image parente
    $maMiniature->setIdImage($monImage->getId());

    // Génération de la miniature
    $maMiniature->redimensionner($maMiniature->getPathTemp(), $maMiniature->getPathTemp(), $maLargeur, $maHauteur);

    // Création de la miniature
    $maMiniature->setNomTemp($_FILES['fichier']['name']);
    if (!$maMiniature->creer()) {
        $msgErreur .= 'Erreur lors de l\'enregistrement du fichier de la miniature ' . $_FILES['fichier']['name']
                . ' .<br />';
    }
}
?>
<h1 class="mb-3"><small>Envoi d'une image</small></h1>
<?php if (!empty($msgErreur)) : ?>
    <div class="alert alert-danger">
        <span class="fas fa-remove"></span>
        &nbsp;
        <b>Une erreur a été rencontrée !</b>
        <br />
    <?= $msgErreur ?>
    </div>
<?php else : ?>
    <?php if (!empty($msgWarning)) : ?>
        <div class="alert alert-warning">
            <span class="fas fa-remove"></span>
            &nbsp;
            <b>Une erreur a été rencontrée, mais l'envoi de l'image a été effectué !</b>
            <br />
        <?= $msgWarning ?>
        </div>
    <?php endif; ?>
    <div class="alert alert-success">
        <span class="fas fa-ok"></span>
        &nbsp;
        <b>Image enregistrée avec succès !</b>
    </div>
    <div class="card">
        <div class="card-body">
            <h2>Afficher l'image</h2>
            <div class="form-horizontal">
                <div class="mb-3">
                    <label class="col-sm-2 form-label">Lien direct</label>
                    <div class="col-sm-10">
                        <a href="<?= $monImage->getURL() ?>"><?= $monImage->getURL() ?></a>
                    </div>
                </div>
    <?php if (isset($maMiniature)) : ?>
                    <div class="mb-3">
                        <label class="col-sm-2 form-label">Lien direct miniature</label>
                        <div class="col-sm-10">
                            <a href="<?= $maMiniature->getURL() ?>"><?= $maMiniature->getURL() ?></a>
                        </div>
                    </div>
    <?php endif; ?>
                <div class="mb-3">
                    <label class="col-sm-2 form-label">Forum <em>(BBcode)</em></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" onFocus="this.select();"
                               value="[img]<?= $monImage->getURL() ?>[/img]" />
                    </div>
                </div>
    <?php if (isset($maMiniature)) : ?>
                    <div class="mb-3">
                        <label class="col-sm-2 form-label">Forum <em>(BBcode)</em> avec miniature</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" onFocus="this.select();"
                                   value="[url=<?= $monImage->getURL() ?>][img]<?= $maMiniature->getURL() ?>[/img][/url]" />
                        </div>
                    </div>
    <?php endif; ?>
                <div class="mb-3">
                    <label class="col-sm-2 form-label">HTML</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" onFocus="this.select();"
                               value='<a href="<?= $monImage->getURL() ?>"><?= $monImage->getNomOriginalFormate() ?></a>' />
                    </div>
                </div>
    <?php if (isset($maMiniature)) : ?>
                    <div class="mb-3">
                        <label class="col-sm-2 form-label">HTML avec miniature</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" onFocus="this.select();"
                                   value='<a href="<?= $monImage->getURL() ?>"><img src="<?= $maMiniature->getURL() ?>" alt="<?= $monImage->getNomOriginalFormate() ?>" /><?= $monImage->getNomOriginalFormate() ?></a>' />
                        </div>
                    </div>
    <?php endif; ?>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="container">
                <div class="row">
                    <span class="col-sm-2">Nom de l'image</span>
                    <span class="col-sm-10"><?= $monImage->getNomOriginalFormate() ?> </span>
                 </div>
                <div class="row">
                <span class="col-sm-2">Poids</span>
                <span class="col-sm-10"><?= $monImage->getPoidsMo() ?>&nbsp;Mo</span>
                </div>
                <div class="row">
                    <span class="col-sm-2">Largeur</span>
                    <span class="col-sm-10"><?= $monImage->getLargeur() ?>&nbsp;px</span>
                </div>
                <div class="row">
                    <span class="col-sm-2">Hauteur</span>
                    <span class="col-sm-10"><?= $monImage->getHauteur() ?>&nbsp;px</span>
                </div>
            </div>
            <div class="clearfix"></div>
            <br />
            <a href="<?= _URL_ ?>" class="btn btn-success">
                <span class="fas fa-cloud-upload-alt"></span>
                &nbsp;
                Envoyer une autre image
            </a>
            <a href="<?= _URL_ ?>delete.php?id=<?= $monImage->getNomNouveau() ?>&type=<?= RessourceObject::TYPE_IMAGE ?>"
               class="btn btn-danger">
                <span class="fas fa-trash"></span>
                &nbsp;
                Effacer cette image
            </a>
        </div>
    </div>
<?php endif; ?>
<?php require _TPL_BOTTOM_; ?>