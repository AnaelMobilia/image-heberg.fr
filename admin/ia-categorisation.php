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
    require '../config/config.php';
}

// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);

// Action à effectuer sur une image
if (isset($_GET['idImage']) && preg_match('#^[0-9]+$#', $_GET['idImage'])) {
    $monImage = new ImageObject($_GET['idImage'], RessourceObject::SEARCH_BY_ID);
    if (isset($_GET['action']) && in_array($_GET['action'], ['approuver', 'bloquer'])) {
        $monImage->{$_GET['action']}();
        die('OK');
    }
    // La suppression n'est pas contaminante par défaut
    if (isset($_GET['action']) && $_GET['action'] === 'supprimer') {
        $listeImages = ImageObject::chargerMultiple([$monImage->getMd5()], RessourceObject::SEARCH_BY_MD5);
        foreach ($listeImages as $image) {
            $image->supprimer();
        }
        die('OK');
    }
    if (isset($_GET['categorie']) && array_key_exists(urldecode($_GET['categorie']), _ABUSE_TYPES_)) {
        $monImage->setCategorieBlocage(urldecode($_GET['categorie']));
        $monImage->categoriser();
        die('OK');
    }
}

require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Catégorisation pour l'IA</small></h1>
    <?php

/**
 * Images à traiter
 */
$idStart = 0;
if (!empty($_GET['lastId']) && preg_match('#^[0-9]+$#', $_GET['lastId'])) {
    $idStart = (int)$_GET['lastId'];
}
$req = 'SELECT new_name FROM images WHERE isBloquee = \'1\' AND abuse_categorie = \'\'' . ($idStart !== 0 ? ' AND id < ' . $idStart : '') . ' GROUP BY md5 ORDER BY id DESC LIMIT ' . _PAGINATION_IMAGES_;
$table = [
    'legende' => 'trouvée##',
    'values' => HelperAdmin::queryOnNewName($req)
];

$isPlural = (count($table['values']) > 1 ? 's' : '');
$lastId = '';
// Charger les objets concernés
$mesImages = ImageObject::chargerMultiple($table['values'], RessourceObject::SEARCH_BY_NAME, false);
?>
    <div class="card">
        <div class="card-header">
            <?= count($table['values']) ?> image<?= $isPlural . ' ' . str_replace('##', $isPlural, $table['legende']) ?>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Catégorisation de l'image</th>
                        <th>Suggestion IA</th>
                        <th>Actions</th>
                        <th>Date d'envoi</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <?php foreach ($mesImages as $uneImage) : ?>
                        <tr data-id="<?= $uneImage->getId() ?>" data-md5="<?= $uneImage->getMd5() ?>">
                            <td>
                                <a href="<?= $uneImage->getURL(true) ?>?forceDisplay=1" target="_blank" style="<?= ($uneImage->isBloquee() ? 'text-decoration: line-through double red;' : '') . ($uneImage->isApprouvee() ? 'text-decoration: underline double green;' : '') ?>">
                                    <img src="<?= $uneImage->getPreviewMiniature()->getURL(true) ?>?forceDisplay=1" style="max-width: <?= (_SIZE_PREVIEW_ / 2) ?>px; max-height: <?= (_SIZE_PREVIEW_ / 2) ?>px" loading="lazy">
                                    <br/>
                                    <?= $uneImage->getNomNouveau() ?>
                                </a>
                            </td>
                            <td>
                                <select onchange="categoriser('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', this.value)">
                                    <option value="">-</option>
                                    <?php foreach (_ABUSE_TYPES_ as $categorie => $detail) : ?>
                                        <option value="<?= $categorie ?>"><?= $categorie ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><i>Calcul en cours...</i></td>
                            <td>
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'approuver');" title="Approuver"><span class="bi-hand-thumbs-up-fill text-success"></span></button>
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'bloquer');" title="Bloquer"><span class="bi-hand-thumbs-down-fill text-danger"></span></button>
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'supprimer');" title="Supprimer"><span class="bi-trash-fill" style="color: purple"></span></button>
                            </td>
                            <td><?= $uneImage->getDateEnvoiFormatee() ?></td>
                        </tr>
                        <?php $lastId = $uneImage->getId() ?>
                    <?php endforeach; ?>
                </tbody>
                <?php if(count($table['values']) === _PAGINATION_IMAGES_) : ?>
                <tfoot>
                    <tr>
                        <th>
                            <a href="<?= _URL_ADMIN_ . basename(__FILE__) ?>?lastId=<?= $lastId ?>" class="btn btn-primary"><span class="bi-arrow-left"></span> </a>
                        </th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <script>
        /**
         * Gestion des actions sur les images
         * @param idImage ID de l'image
         * @param md5 MD5 de l'image
         * @param action Action à réaliser
         */
        function runAction(idImage, md5, action) {
            if (confirm(action.substring(0, 1).toUpperCase() + action.substring(1) + ' cette image ?')) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '<?= _URL_ADMIN_ . basename(__FILE__) ?>?action=' + action + '&idImage=' + idImage);
                xhr.onload = function () {
                    if (xhr.status === 200 && xhr.responseText === 'OK') {
                        // En cas de succès, supprimer les lignes correspondantes
                        const images = document.querySelectorAll('tr[data-id="' + idImage + '"], tr[data-md5="' + md5 + '"]');
                        images.forEach(function (ligne) {
                            ligne.remove();
                        });
                    }
                };
                xhr.onerror = function () {
                    alert('Une erreur a été rencontrée lors de l\'action ' + action + ' sur l\'image ' + idImage + ' : ' + xhr.response);
                };
                xhr.send();
            }
        }

        /**
         * Catégoriser une image
         * @param idImage ID de l'image
         * @param md5 MD5 de l'image
         * @param categorie Catégorie à appliquer
         */
        function categoriser(idImage, md5, categorie) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '<?= _URL_ADMIN_ . basename(__FILE__) ?>?action=categoriser&categorie=' + encodeURIComponent(categorie) + '&idImage=' + idImage);
            xhr.onload = function () {
                if (xhr.status === 200 && xhr.responseText === 'OK') {
                    // En cas de succès, supprimer les lignes correspondantes
                    const images = document.querySelectorAll('tr[data-id="' + idImage + '"], tr[data-md5="' + md5 + '"]');
                    images.forEach(function (ligne) {
                        ligne.remove();
                    });
                }
            };
            xhr.onerror = function () {
                alert('Une erreur a été rencontrée lors de la catégorisation en tant que ' + categorie + ' sur l\'image ' + idImage + ' : ' + xhr.response);
            };
            xhr.send();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@teachablemachine/image@latest/dist/teachablemachine-image.min.js"></script>
    <script type="text/javascript">
        // More API functions here:
        // https://github.com/googlecreativelab/teachablemachine-community/tree/master/libraries/image
        let model, maxPredictions;

        async function init() {
            // Activer l'exécution GPU avec TensorFlow.js
            await tf.setBackend('webgl');
            // load the model and metadata
            // Note: the pose library adds "tmImage" object to your window (window.tmImage)
            model = await tmImage.load('<?=_URL_ADMIN_ ?>ia_model/model.json', '<?=_URL_ADMIN_ ?>ia_model/metadata.json');
            maxPredictions = model.getTotalClasses();

            // Utiliser un IntersectionObserver pour charger les images lorsque elles deviennent visibles
            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        // Une fois l'image visible, prédire et désobserver
                        observer.unobserve(img);
                        predictImage(img, img.closest('tr'));
                    }
                });
            }, { threshold: 0.1 });

            // Sélectionner toutes les images et les observer lorsqu'elles deviennent visibles
            document.querySelectorAll('#tbody img').forEach(img => {
                observer.observe(img);
            });
        }

        /**
         * Prédire les catégories d'images
         * @param img contenu à analyser
         * @param unTr une ligne du tableau correspondante
         * @returns {Promise<void>}
         */
        async function predictImage(img, unTr) {
            // Lancer la prédiction sur l'image
            const prediction = await model.predict(img);
            // Trouver la classe avec la plus grande probabilité
            const bestPrediction = prediction.reduce((max, p) => (p.probability > max.probability ? p : max), prediction[0]);
            // Remontée dans l'interface
            unTr.children[2].innerHTML = `${bestPrediction.className} -> ${Math.round(bestPrediction.probability * 100)}%`;
        }

        window.onload = init;
    </script>
    <?php require _TPL_BOTTOM_; ?>