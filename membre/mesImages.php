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

require __DIR__ . '/../config/config.php';
// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_USER);
// Je récupère la session de mon utilisateur
$maSession = new SessionObject();
// Et je reprend ses données
$monUtilisateur = new UtilisateurObject($maSession->getId());

require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Mes images</small></h1>
    <div class="row">
        <?php
        // Charger les objets concernés
        $mesImages = ImageObject::chargerMultiple($monUtilisateur->getImages(), RessourceObject::SEARCH_BY_NAME);
        foreach ($mesImages as $uneImage) :
            $miniature = $uneImage->getMiniatures(true);
            if ($miniature->count() === 0) {
                // Duplication de l'image source
                $tmpFile = tempnam(sys_get_temp_dir(), uniqid('', true));
                copy($uneImage->getPathMd5(), $tmpFile);

                // Génération de la miniature pour l'aperçu
                $maMiniature = new MiniatureObject();
                $maMiniature->setPathTemp($tmpFile);
                $maMiniature->setIdImage($uneImage->getId());
                $maMiniature->redimensionner($maMiniature->getPathTemp(), $maMiniature->getPathTemp(), _SIZE_PREVIEW_, _SIZE_PREVIEW_);
                $maMiniature->setNomTemp('preview_' . $uneImage->getId());
                $maMiniature->creer();
                $maMiniature->setIsPreview(true);
                $maMiniature->sauver();
            } else {
                $maMiniature = new MiniatureObject($miniature->offsetGet(0));
            }
            ?>
            <div class="col p-1" style="min-width: <?= _SIZE_PREVIEW_ ?>px;">
                <div class="card h-100">
                    <div class="card-header">
                        <?= $uneImage->getNomOriginalFormate() ?>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Envoyée le <?= $uneImage->getDateEnvoiFormatee() ?>
                            <br/>
                            <?= $uneImage->getNbViewTotal() ?> affichage<?= $uneImage->getNbViewTotal() > 1 ? 's' : '' ?> <span title="Date du dernier affichage" class="fw-light fst-italic">(<?= $uneImage->getLastViewFormate() ?>)</span>
                        </p>
                    </div>
                    <div class="text-center">
                        <a href="<?= _URL_IMAGES_ ?><?= $uneImage->getNomNouveau() ?>" title="<?= $uneImage->getNomOriginalFormate() ?>" target="_blank">
                            <img src="<?= $maMiniature->getURL() ?>" class="card-img-bottom rounded-3" alt="<?= $uneImage->getNomOriginalFormate() ?>" style="<?= ($maMiniature->getLargeur() < 256 ? 'max-height: ' . _SIZE_PREVIEW_ . 'px; width: auto;' : 'height: auto; max-width: ' . _SIZE_PREVIEW_ . 'px;') ?>" loading="lazy">
                        </a>
                    </div>
                    <div class="card-footer text-muted text-end">
                        <a href="<?= _URL_IMAGES_ ?><?= $uneImage->getNomNouveau() ?>" title="Afficher" target="_blank">
                            <button type="button" class="btn btn-sm btn-outline-primary">
                                <span class="bi-box-arrow-up-right"></span>
                            </button>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#linksModal">
                            <span class="bi-link-45deg"></span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#renameModal">
                            <span class="bi-pencil"></span>
                        </button>
                        <a href="<?= _URL_HTTPS_ ?>delete.php?id=<?= $uneImage->getNomNouveau() ?>&type=<?= RessourceObject::TYPE_IMAGE ?>" title="Effacer" target="_blank">
                            <button type="button" class="btn btn-sm btn-outline-danger">
                                <span class="bi-trash"></span>
                            </button>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php require _TPL_BOTTOM_; ?>