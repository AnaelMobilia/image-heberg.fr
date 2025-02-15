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
    if (isset($_GET['action']) && in_array ($_GET['action'], ['approuver', 'bloquer', 'supprimer'])) {
        $monImage->{$_GET['action']}();
        die('OK');
    }
}

require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>NSFWJS</small></h1>
    <?php

$message = '';

// Action à effectuer sur une image
if (isset($_GET['idImage']) && preg_match('#^[0-9]+$#', $_GET['idImage'])) {
    $monImage = new ImageObject($_GET['idImage'], RessourceObject::SEARCH_BY_ID);
    if (isset($_GET['bloquer'])) {
        // Blocage de l'image
        $monImage->bloquer();
        $message .= 'Image ' . $monImage->getNomNouveau() . ' bloquée !';
    } elseif (isset($_GET['approuver'])) {
        // Approbation de l'image
        $monImage->approuver();
        $message .= 'Image ' . $monImage->getNomNouveau() . ' approuvée !';
    }
}

/**
 * Images à traiter
 */
$idStart = 0;
if (!empty($_GET['lastId']) && preg_match('#^[0-9]+$#', $_GET['lastId'])) {
    $idStart = (int)$_GET['lastId'];
}
$req = 'SELECT new_name FROM images' . ($idStart !== 0 ? ' WHERE id < ' . $idStart : '') . ' ORDER BY id DESC LIMIT ' . _PAGINATION_IMAGES_;
$table = [
    'legende' => 'trouvée##',
    'values' => HelperAdmin::queryOnNewName($req)
];

$isPlural = (count($table['values']) > 1 ? 's' : '');
$lastId = '';
// Charger les objets concernés
$mesImages = ImageObject::chargerMultiple($table['values'], RessourceObject::SEARCH_BY_NAME, false);
?>
    <?php if (!empty($message)) : ?>
    <div class="alert alert-success">
        <?= $message ?>
    </div>
<?php endif; ?>
    <div class="card">
        <div class="card-header">
            <?= count($table['values']) ?> image<?= $isPlural . ' ' . str_replace('##', $isPlural, $table['legende']) ?>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Actions</th>
                        <th>Date d'envoi</th>
                        <th>Catégorisation IA nsfwjs</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <?php foreach ($mesImages as $uneImage) : ?>
                        <tr data-id="<?= $uneImage->getId() ?>" data-md5="<?= $uneImage->getMd5() ?>">
                            <td><a href="<?= $uneImage->getURL(true) ?>?forceDisplay=1" target="_blank" style="<?= ($uneImage->isBloquee() ? 'text-decoration: line-through double red;' : '') . ($uneImage->isApprouvee() ? 'text-decoration: underline double green;' : '') ?>"><?= $uneImage->getNomNouveau() ?></a></td>
                            <td>
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'approuver');" title="Approuver"><span class="bi-hand-thumbs-up-fill text-success"></span></button>
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'bloquer');" title="Bloquer"><span class="bi-hand-thumbs-down-fill text-danger"></span></button>
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'supprimer');" title="Supprimer"><span class="bi-trash-fill" style="color: purple"></span></button>
                            </td>
                            <td><?= $uneImage->getDateEnvoiFormatee() ?></td>
                            <td></td>
                        </tr>
                        <?php $lastId = $uneImage->getId() ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>
                            <a href="<?= _URL_ADMIN_ . basename(__FILE__) ?>?lastId=<?= $lastId ?>" class="btn btn-primary"><span class="bi-arrow-left"></span> </a>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js"></script>
    <script type="text/javascript" src="https://unpkg.com/nsfwjs"></script>

    <script type="module">
        // Forcer le mode "Production" de tensorflow
        tf.enableProdMode();
        // Modèle récupéré de https://github.com/infinitered/nsfwjs/tree/master/example/nsfw_demo/public/quant_mid
        const model = await nsfwjs.load('<?=_URL_ADMIN_ ?>nsfwjs_model_quant_mid/', {type: 'graph'});

        // On itère sur le tableau HTML
        let tabTr = Array.from(document.getElementById("tbody").children);
        for (const unTr of tabTr) {
            let img = new Image();
            // Lien vers l'image
            img.src = unTr.children[0].children[0].href;
            console.log(img.src);
            // Attendre que la ressource soit chargée
            await img.decode();

            // Classify the image -> une seule classification attendue en résultat
            const predictions = await model.classify(img, 1);
            console.log('Predictions: ', predictions);
            // Résultats
            let results = '';
            for (let unType of predictions) {
                results += unType.className + ' -> ' + Math.round(unType.probability * 100) + '%<br />';
            }
            // Injection de la valeur dans la page
            unTr.children[3].innerHTML = results;
        }
    </script>
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
                        // En cas de succès, supprimer la ligne correspondante
                        const images = document.querySelectorAll('tr[data-id="' + idImage + '"], tr[data-md5="' + md5 + '"]');
                        images.forEach(function (ligne) {
                            ligne.remove();
                        });                    }
                };
                xhr.onerror = function () {
                    alert('Une erreur a été rencontrée lors de l\'action ' + action + ' sur l\'image ' + idImage + ' : ' + xhr.response);
                };
                xhr.send();
            }
        }
    </script>
    <?php require _TPL_BOTTOM_; ?>