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
    if (isset($_GET['action']) && in_array ($_GET['action'], ['approuver', 'bloquer'])) {
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
}

require _TPL_TOP_;
?>
    <h1 class="mb-3"><small>Rechercher des images</small></h1>
<?php
$idStart = 0;
if (!empty($_POST['lastId']) && preg_match('#^[0-9]+$#', $_POST['lastId'])) {
    $idStart = (int)$_POST['lastId'];
}
$lastId = '';
$table = [
    'legende' => 'trouvée##',
    'values' => [],
];
/**
 * Recherche
 */
$tabSearch = [
    'Adresse IP' => 'SELECT new_name FROM images WHERE remote_addr LIKE \'%##value##%\'' . ($idStart !== 0 ? ' AND id < ' . $idStart : '') . ' GROUP BY md5 ORDER BY id DESC LIMIT ' . _PAGINATION_IMAGES_,
    'Nom originel' => 'SELECT new_name FROM images WHERE old_name LIKE \'%##value##%\'' . ($idStart !== 0 ? ' AND id < ' . $idStart : '') . ' GROUP BY md5 ORDER BY id DESC LIMIT ' . _PAGINATION_IMAGES_,
    'Nouveau nom' => 'SELECT new_name FROM images WHERE new_name LIKE \'%##value##%\'' . ($idStart !== 0 ? ' AND id < ' . $idStart : '') . ' GROUP BY md5 ORDER BY id DESC LIMIT ' . _PAGINATION_IMAGES_,
    'Utilisateur' => 'SELECT im.new_name FROM images im LEFT JOIN possede po ON po.images_id = im.id WHERE po.membres_id = \'##value##\'' . ($idStart !== 0 ? ' AND id < ' . $idStart : '') . ' GROUP BY md5 ORDER BY im.id DESC LIMIT ' . _PAGINATION_IMAGES_,
    'Bloquée' => 'SELECT new_name FROM images WHERE isBloquee = \'1\'' . ($idStart !== 0 ? ' AND id < ' . $idStart : '') . ' GROUP BY md5 ORDER BY id DESC LIMIT ' . _PAGINATION_IMAGES_,
    'Approuvée' => 'SELECT new_name FROM images WHERE isApprouvee = \'1\'' . ($idStart !== 0 ? ' AND id < ' . $idStart : '') . ' GROUP BY md5 ORDER BY id DESC LIMIT ' . _PAGINATION_IMAGES_,
];
// Ajout des catégories de filtrage
foreach (_ABUSE_TYPES_ as $categorie => $detail) {
    $tabSearch[ucfirst($detail)] = 'SELECT new_name FROM images WHERE abuse_categorie = \'' . str_replace("'", "\'", $categorie) . '\'' . ($idStart !== 0 ? ' AND id < ' . $idStart : '') . ' GROUP BY md5 ORDER BY id DESC LIMIT ' . _PAGINATION_IMAGES_;
}
if (isset($_POST['Submit']) && !empty($_POST['champ']) && !empty($_POST['valeur'])) {
    $reqValue = trim(str_replace('\'', '_', $_POST['valeur']));
    $req = str_replace('##value##', $reqValue, $tabSearch[$_POST['champ']]);
    $table['values'] = HelperAdmin::queryOnNewName($req);
}
// Charger les objets concernés
$mesImages = ImageObject::chargerMultiple($table['values'], RessourceObject::SEARCH_BY_NAME, false);
?>
    <div class="alert alert-info">
        <form method="post" id="form_search">
            <div class="mb-3 form-floating">
                <select name="champ" id="champ" class="form-select" required="required">
                    <option value="" selected>-- Sélectionner un champ --</option>
                    <?php foreach (array_keys($tabSearch) as $key) : ?>
                        <option value="<?= $key ?>"<?= (isset($_REQUEST['champ']) && $key === $_REQUEST['champ'] ? ' selected' :'') ?>><?= $key ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="champ">Champ à utiliser</label>
            </div>
            <div class="mb-3 form-floating">
                <input type="text" class="form-control" name="valeur" id="valeur" required="required" value="<?= ($_REQUEST['valeur'] ?? '')?>">
                <label for="valeur">Valeur recherchée</label>
                <input type="hidden" name="lastId" id="lastId">
            </div>
            <button type="submit" name="Submit" id="submit" class="btn btn-success">Rechercher</button>
        </form>
    </div>
    <div class="card">
        <div class="card-header">
            <?= count($table['values']) ?> image<?= (count($table['values']) > 1 ? 's' : '') . ' ' . str_replace('##', (count($table['values']) > 1 ? 's' : ''), $table['legende']) ?>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Actions</th>
                        <th class="text-break">Nom originel</th>
                        <th class="text-break">Date d'envoi</th>
                        <th class="text-break">IP envoi</th>
                        <th class="text-break">Nb vues</th>
                        <th class="text-break">Dernier affichage</th>
                        <th class="text-break">Utilisateur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mesImages as $uneImage) : ?>
                        <tr data-id="<?= $uneImage->getId() ?>" data-md5="<?= $uneImage->getMd5() ?>">
                            <td><a href="<?= $uneImage->getURL(true) ?>?forceDisplay=1" target="_blank" style="<?= ($uneImage->isBloquee() ? 'text-decoration: line-through double red;' : '') . ($uneImage->isApprouvee() ? 'text-decoration: underline double green;' : '') ?>"><?= $uneImage->getNomNouveau() ?></a></td>
                            <td class="text-nowrap">
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'approuver');" title="Approuver"><span class="bi-hand-thumbs-up-fill text-success"></span></button>
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'bloquer');" title="Bloquer"><span class="bi-hand-thumbs-down-fill text-danger"></span></button>
                                <button class="btn p-0" onclick="runAction('<?= $uneImage->getId() ?>', '<?= $uneImage->getMd5() ?>', 'supprimer');" title="Supprimer"><span class="bi-trash-fill" style="color: purple"></span></button>
                            </td>
                            <td class="text-break"><?= $uneImage->getNomOriginalFormate() ?></td>
                            <td class="text-break"><?= $uneImage->getDateEnvoiFormatee() ?></td>
                            <td class="text-break"><?= $uneImage->getIpEnvoi() ?></td>
                            <td class="text-break"><?= $uneImage->getNbViewTotal() ?><small> (<?= $uneImage->getNbViewPerDay() ?>/jour)</small></td>
                            <td class="text-break"><?= $uneImage->getLastViewFormate() ?></td>
                            <td class="text-break"><?= $uneImage->getIdProprietaire() ?></td>
                        </tr>
                        <?php $lastId = $uneImage->getId() ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>
                            <button onclick="document.getElementById('lastId').value=<?= $lastId ?>;document.getElementById('submit').click();" class="btn btn-primary"><span class="bi-arrow-left"></span></button>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <br>
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
    </script>
    <?php require _TPL_BOTTOM_; ?>
