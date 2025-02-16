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

use ArrayObject;

if (!defined('_PHPUNIT_')) {
    require '../config/config.php';
}

// Vérification des droits d'accès
UtilisateurObject::checkAccess(UtilisateurObject::LEVEL_ADMIN);

const _IMAGES_APPROUVEES_ = 'Approuvées';

// Action à effectuer sur une image
if (isset($_GET['categorie']) && array_key_exists(urldecode($_GET['categorie']), array_merge(_ABUSE_TYPES_, [_IMAGES_APPROUVEES_ => '']))) {
    if (urldecode($_GET['categorie']) === _IMAGES_APPROUVEES_) {
        // Faire du picking aléatoire d'images approuvées (sauf 404 & banned)
        $req = 'SELECT MAX(new_name) as new_name
                    FROM images
                    WHERE isApprouvee = 1
                      AND id > 2
                    GROUP BY md5
                    ORDER BY RAND()
                    LIMIT '.(int)$_GET['nb'];
    } else {
        $req = 'SELECT MAX(new_name) as new_name
                    FROM images
                    WHERE abuse_categorie = \'' . urldecode($_GET['categorie']) . '\'
                    GROUP BY md5';
    }
    $listeImages = ImageObject::chargerMultiple(HelperAdmin::queryOnNewName($req), RessourceObject::SEARCH_BY_NAME);

    // Préparer le tableau d'images à ajouter dans le ZIP
    $tabImages = [];
    foreach ($listeImages as $image) {
        $tabImages[$image->getNomNouveau()] = $image->getPathMd5();
    }
    // Envoyer l'archive au navigateur
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . urldecode($_GET['categorie']) . '"');
    ZipStream::addFiles($tabImages);
    die();
}

require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Entraînement du moteur d'IA pour la détection des contenus indésirables</small></h1>
    <?php

/**
 * Catégories
 */
$req = 'SELECT COUNT(*) as nb, SUM(size) as size, abuse_categorie FROM images WHERE isBloquee = 1 GROUP BY abuse_categorie ORDER BY abuse_categorie';
// Exécution de la requête
$resultat = MaBDD::getInstance()->query($req);
$listeCat = [];
$totalImages = 0;
foreach ($resultat->fetchAll() as $value) {
    $listeCat[$value->abuse_categorie] = [
        'nbImages' => $value->nb,
        'size' => $value->size
    ];
    $totalImages += $value->nb;
}
// Ajouter un sample d'images validées
$listeCat[_IMAGES_APPROUVEES_] = [
    'nbImages' => $totalImages,
    'size' => '-1'
];

$table = [
    'legende' => 'trouvée##',
    'values' => $listeCat,
];

$isPlural = (count($table['values']) > 1 ? 's' : '');
?>
    <div class="card">
        <div class="card-header">
            <?= count($table['values']) ?> catégorie<?= $isPlural . ' ' . str_replace('##', $isPlural, $table['legende']) ?>
        </div>
        <div class="card-body">
            <ul>
                <li>Téléchargez le fichier de chaque catégorie.</li>
                <li>Allez sur <a href="https://teachablemachine.withgoogle.com/train/image">Teachable Machine</a>.</li>
                <li>Pour chaque catégorie d'images, définissez son nom et importez le contenu de l'archive correspondante.</li>
                <li>Cliquez sur le bouton "Entrainer le modèle".</li>
                <li>Cliquez sur le bouton "Exporter le modèle".</li>
                <li>Décompressez le contenu de l'archive dans le dossier "admin/ia_model/".</li>
            </ul>
            <table class="table">
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Nombre d'images</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <?php foreach ($table['values'] as $uneCategorie => $tabData) : ?>
                        <tr>
                            <td><?= $uneCategorie ?></td>
                            <td><?= $tabData['nbImages'] ?> (<?= round($tabData['size'] / 1048576) ?> Mo)</td>
                            <td>
                                <a href="<?= _URL_ADMIN_ . basename(__FILE__) ?>?categorie=<?= rawurlencode($uneCategorie) ?>&nb=<?= $tabData['nbImages'] ?>" class="text-primary" title="Télécharger"><span class="bi-cloud-download-fill"</span></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php require _TPL_BOTTOM_; ?>