<?php

/**
 * Préconfiguration de l'environnement de tests
 *  - Calcul des MD5 des images locales (dépendant de la version de PHP)
 *  - Mise à jour en BDD des MD5 enregistrés
 * - Copie des fichiers requis
 */

/*
 * Copyright 2008-2024 Anael MOBILIA
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

namespace ImageHebergTests;

use ImageHeberg\HelperImage;
use ImageHeberg\MaBDD;

require 'config/config.php';

// Faire la liste des fichiers
$tabFiles = [];
foreach (scandir(_PATH_TESTS_IMAGES_A_IMPORTER_) as $file) {
    if (is_file(_PATH_TESTS_IMAGES_A_IMPORTER_ . $file)) {
        if (
            in_array(HelperImage::getExtension(_PATH_TESTS_IMAGES_A_IMPORTER_ . $file), _ACCEPTED_EXTENSIONS_, true)
            && HelperImage::isModifiableEnMemoire(_PATH_TESTS_IMAGES_A_IMPORTER_ . $file)
        ) {
            $tabFiles[$file] = _PATH_TESTS_IMAGES_A_IMPORTER_ . $file;
        }
        // Remettre à disposition le fichier pour les tests
        copy(_PATH_TESTS_IMAGES_A_IMPORTER_ . $file, _PATH_TESTS_IMAGES_ . $file);
    }
}

// Optimiser localement chaque image et en calculer le MD5 résultant
foreach ($tabFiles as $file => $path) {
    // Créer un fichier temporaire pour travailler dessus
    $fileTmp = tempnam('/tmp/', 'ih');

    // PHP ne gère pas les images WebP animée -> ne pas faire de traitements
    if (!HelperImage::isAnimatedWebp($path)) {
        // Optimiser l'image (permettra de comparer son hash avec celles déjà stockées)
        HelperImage::setImage(HelperImage::getImage($path), HelperImage::getType($path), $fileTmp);
    } else {
        copy($path, $fileTmp);
    }

    // Calculer le MD5 de l'image
    $md5 = md5_file($fileTmp);

    // Corriger l'information en BDD
    $req = MaBDD::getInstance()->prepare('UPDATE images SET md5 = :md5 WHERE old_name = :oldName');
    $req->bindValue(':md5', $md5);
    $req->bindValue(':oldName', $file);
    $req->execute();

    // Copier le fichier dans le bon répertoire
    $fileDst = _PATH_IMAGES_ . substr($md5, 0, 1) . '/' . $md5;
    copy($fileTmp, $fileDst);
    echo $file . ' -> ' . $fileDst . PHP_EOL;
}

// Traitement pour ImageUploadAndDeleteTest::testSuppressionImagePlusieursMiniatures()
$tabThumbs = [
    'image_a_supprimerMultiple-100x100.png' => _PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple-100x100.png',
    'image_a_supprimerMultiple-200x200.png' => _PATH_TESTS_IMAGES_ . 'image_a_supprimerMultiple-200x200.png',
];
foreach ($tabThumbs as $file => $path) {
    // Calculer le MD5 de l'image
    $md5 = md5_file($path);

    // Corriger l'information en BDD
    $req = MaBDD::getInstance()->prepare('UPDATE thumbnails SET md5 = :md5 WHERE new_name = :newName');
    $req->bindValue(':md5', $md5);
    $req->bindValue(':newName', $file);
    $req->execute();

    // Copier le fichier dans le bon répertoire
    $fileDst = _PATH_MINIATURES_ . substr($md5, 0, 1) . '/' . $md5;
    copy($path, $fileDst);
    echo $file . ' -> ' . $fileDst . PHP_EOL;
}
