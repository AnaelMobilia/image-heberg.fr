<?php

/*
 * Copyright 2008-2025 Anael MOBILIA
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
// Gestion de la session
$maSession = new SessionObject();

require _TPL_TOP_;

$msgErreur = '';
$msgWarning = '';

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
 * Vérification de l'utilisation depuis Tor
 */
if (empty($msgErreur) && _TOR_DISABLE_UPLOAD_ && Tor::checkIp($_SERVER['REMOTE_ADDR'])) {
    $msgErreur .= 'Suite à un abus d\'utilisation de ' . _SITE_NAME_ . ', l\'envoi d\'image est impossible depuis le réseau Tor.<br />';
}


/**
 * Vérification de la réputation de l'adresse IP
 */
if (empty($msgErreur) && _ABUSE_DISABLE_UPLOAD_AFTER_X_IMAGES_ > 0 && HelperAbuse::checkIpReputation($_SERVER['REMOTE_ADDR']) >= _ABUSE_DISABLE_UPLOAD_AFTER_X_IMAGES_) {
    $msgErreur .= 'Suite à un abus d\'utilisation de ' . _SITE_NAME_ . ' depuis votre plage d\'adresse IP (' . $_SERVER['REMOTE_ADDR'] . '), l\'envoi d\'image ne vous est plus accessible.<br />';
}

/**
 * Vérification de la présence d'un fichier
 */
if (empty($msgErreur) && empty($_FILES['fichier']['name'])) {
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
        $msgErreur .= 'Le poids du fichier ' . $_FILES['fichier']['name'] . ' (' . round($poids / 1048576, 1) . ' Mo) dépasse la limité autorisée (' . round(_IMAGE_POIDS_MAX_ / 1048576, 1) . ' Mo).<br />';
    }
}

/**
 * Vérification du type mime
 */
if (empty($msgErreur)) {
    $pathTmp = $_FILES['fichier']['tmp_name'];
    if (!in_array(HelperImage::getType($pathTmp), _ACCEPTED_MIME_TYPE_, true)) {
        $msgErreur .= 'Le fichier ' . $_FILES['fichier']['name'] . ' n\'est pas une image valide.<br />';
    }
}

/**
 * Vérification des dimensions
 */
if (empty($msgErreur) && !HelperImage::isModifiableEnMemoire($pathTmp)) {
    $msgErreur .= 'Les dimensions de l\'image ' . $_FILES['fichier']['name'] . ' dépassent la limite autorisée ' . _IMAGE_DIMENSION_MAX_ . ' x ' . _IMAGE_DIMENSION_MAX_ . '<br />';
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
 * PHP ne gère pas les images WebP animée -> ne pas faire de traitements
 */
if (empty($msgErreur) && (!empty($_POST['redimImage']) || !empty($_POST['angleRotation']) || !empty($_POST['dimMiniature'])) && HelperImage::isAnimatedWebp($monImage->getPathTemp())) {
    $msgWarning .= 'Il n\'est pas possible d\'effectuer de traitement sur les images WebP animées.<br />';
    unset($_POST['redimImage'], $_POST['angleRotation'], $_POST['dimMiniature']);
}

/**
 * Traitement du redimensionnement
 */
if (empty($msgErreur) && !empty($_POST['redimImage'])) {
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
if (empty($msgErreur) && isset($_POST['angleRotation']) && preg_match('#^[0-9]+$#', $_POST['angleRotation'])) {
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
if (empty($msgErreur) && !empty($_POST['dimMiniature'])) {
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
        $msgErreur .= 'Erreur lors de l\'enregistrement du fichier de la miniature ' . $_FILES['fichier']['name'] . ' .<br />';
    }
}
?>
    <h1 class="mb-3"><small>Envoi d'une image</small></h1>
    <?php if (!empty($msgErreur)): ?>
    <div class="alert alert-danger">
        <span class="bi-x-circle"></span>
        &nbsp;
        <b>Une erreur a été rencontrée !</b>
        <br/>
        <?= $msgErreur ?>
    </div>
    <?php else : ?>
        <?php if (!empty($msgWarning)) : ?>
        <div class="alert alert-warning">
            <span class="bi-x-circle"></span>
            &nbsp;
            <b>Une erreur a été rencontrée, mais l'envoi de l'image a été effectué !</b>
            <br/>
            <?= $msgWarning ?>
        </div>
        <?php endif; ?>
    <div class="alert alert-success">
        <span class="bi-check"></span>
        &nbsp;
        <b>Image enregistrée avec succès !</b>
    </div>
    <div class="card">
        <div class="card-body">
            <h2>Afficher l'image</h2>
            <div class="container">
                <div class="row mb-3">
                    <label class="col-sm-2 form-label">Lien direct</label>
                    <div class="col-sm-10">
                        <a href="<?= $monImage->getURL() ?>" target="_blank"><?= $monImage->getURL() ?></a>
                    </div>
                </div>
                <?php if (isset($maMiniature)) : ?>
                    <div class="row mb-3">
                        <label class="col-sm-2 form-label">Lien direct miniature</label>
                        <div class="col-sm-10">
                            <a href="<?= $maMiniature->getURL() ?>" target="_blank"><?= $maMiniature->getURL() ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <label for="html" class="col-sm-2 form-label">Lien HTML</label>
                    <div class="col-sm-10">
                        <input id="html" type="text" class="form-control" onFocus="this.select();"
                               value='<a href="<?= $monImage->getURL() ?>"><?= $monImage->getNomOriginalFormate() ?></a>'/>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="imgHtml" class="col-sm-2 form-label">Image en HTML</label>
                    <div class="col-sm-10">
                        <input id="imgHtml" type="text" class="form-control" onFocus="this.select();"
                               value='<img src="<?= $monImage->getURL() ?>" alt="<?= $monImage->getNomOriginalFormate() ?>" />'/>
                    </div>
                </div>
                <?php if (isset($maMiniature)) : ?>
                    <div class="row mb-3">
                        <label for="imgHtmlMin" class="col-sm-2 form-label">Image en HTML avec miniature</label>
                        <div class="col-sm-10">
                            <input id="imgHtmlMin" type="text" class="form-control" onFocus="this.select();"
                                   value='<a href="<?= $monImage->getURL() ?>"><img src="<?= $maMiniature->getURL() ?>" alt="<?= $monImage->getNomOriginalFormate() ?>"/></a>'/>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <label for="imgBBcode" class="col-sm-2 form-label">Forum <em>(BBcode)</em></label>
                    <div class="col-sm-10">
                        <input id="imgBBcode" type="text" class="form-control" onFocus="this.select();"
                               value="[img]<?= $monImage->getURL() ?>[/img]"/>
                    </div>
                </div>
                <?php if (isset($maMiniature)) : ?>
                    <div class="row mb-3">
                        <label for="MinImgBBcode" class="col-sm-2 form-label">Forum <em>(BBcode)</em> avec miniature</label>
                        <div class="col-sm-10">
                            <input id="MinImgBBcode" type="text" class="form-control" onFocus="this.select();"
                                   value="[url=<?= $monImage->getURL() ?>][img]<?= $maMiniature->getURL() ?>[/img][/url]"/>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="clearfix"></div>
            <br/>
            <div class="container">
                <div class="row">
                    <div class="col-sm-4">
                        <img src="<?= $monImage->getPreviewMiniature()->getURL(true) ?>" alt="<?= $monImage->getNomOriginalFormate() ?>" style="max-width: 100%; height: auto;">
                    </div>
                    <div class="container col-sm-8">
                        <div class="row">
                            <span class="col-sm-4">Nom de l'image</span>
                            <span class="col-sm-8"><?= $monImage->getNomOriginalFormate() ?> </span>
                        </div>
                        <div class="row">
                            <span class="col-sm-4">Poids</span>
                            <span class="col-sm-8"><?= $monImage->getPoidsMo() ?>&nbsp;Mo</span>
                        </div>
                        <div class="row">
                            <span class="col-sm-4">Largeur</span>
                            <span class="col-sm-8"><?= $monImage->getLargeur() ?>&nbsp;px</span>
                        </div>
                        <div class="row">
                            <span class="col-sm-4">Hauteur</span>
                            <span class="col-sm-8"><?= $monImage->getHauteur() ?>&nbsp;px</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <br/>
            <a href="<?= _URL_HTTPS_ ?>" class="btn btn-success">
                <span class="bi-cloud-arrow-up-fill"></span>
                &nbsp;
                Envoyer une autre image
            </a>
            <a href="<?= _URL_HTTPS_ ?>delete.php?id=<?= $monImage->getNomNouveau() ?>&type=<?= RessourceObject::TYPE_IMAGE ?>"
               class="btn btn-danger">
                <span class="bi-trash"></span>
                &nbsp;
                Effacer cette image
            </a>
        </div>
    </div>
    <?php endif; ?>
    <?php require _TPL_BOTTOM_; ?>