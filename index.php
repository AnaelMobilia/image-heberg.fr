<?php
/*
 * Copyright 2008-2020 Anael MOBILIA
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
require 'config/config.php';
require _TPL_TOP_;

// Anti-flood
$maSession = new sessionObject();
$maSession->setFlag();
?>
<h1><small>Envoyer une image</small></h1>
<?php if (metaObject::getHDDUsage() > _QUOTA_MAXIMAL_IMAGES_GO_): ?>
    <div class="alert alert-danger">
        <?= _SITE_NAME_ ?> est victime de son succès : trop d'images ont été envoyées et tout l'espace disque acheté est utilisé !
        <br>
        Si vous souhaitez soutenir le projet, merci d'utiliser <a href="contact.php">le formulaire de contact</a>.
    </div>
<?php endif; ?>
<div class="alert alert-info">
    <?= _SITE_NAME_ ?> est un service gratuit vous permettant d'héberger vos images sur internet.
    <ul>
        <li>Image de type JPG, PNG, GIF</li>
        <li>Taille maximale : <?= round(_IMAGE_POIDS_MAX_ / 1048576, 1) ?> Mo</li>
        <li>Dimensions maximales : <?= _IMAGE_DIMENSION_MAX_ ?> x <?= _IMAGE_DIMENSION_MAX_ ?> pixels (hauteur x largeur)</li>
    </ul>
</div>

<div class="card card-primary">
    <div class="card-body">
        <form enctype="multipart/form-data" action="<?= _URL_HTTPS_ ?>upload.php" method="post" class="form-group">
            <div class="form-group form-group-lg">
                <label for="fichier" class="col-md-3">Fichier à envoyer</label>
                <div class="col-md-9">
                    <input type="file" accept="image/*" name="fichier" id="fichier"
                    <?php if (metaObject::getHDDUsage() > _QUOTA_MAXIMAL_IMAGES_GO_): ?>
                               disabled="disabled"
                           <?php endif; ?>
                           >
                </div>
                <div class="help-block">Tout envoi de fichier implique l'acceptation des <a href="cgu.php">Conditions Générales d'Utilisation</a> du service.</div>
            </div>
            <h3>Options</h3>
            <span class="help-block"><em>Le ratio de l'image sera conservé.</em></span>
            <div class="form-group">
                <label class="col-md-3">Rotation de l'image</label>
                <div class="col-md-9">
                    <select name="angleRotation" class="form-control">
                        <option value="" selected>-- Ne pas effectuer --</option>
                        <optgroup label="Rotation vers la gauche">
                            <option value="90">90&deg; (&frac14; tour)</option>
                            <option value="180">180&deg; (&frac12; tour)</option>
                            <option value="270">270&deg; (&frac34; tour)</option>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3">Faire une miniature</label>
                <div class="col-md-9">
                    <select name="dimMiniature" class="form-control">
                        <option value="" selected>-- Ne pas effectuer --</option>
                        <option value="100x100">Avatar (100x100)</option>
                        <option value="320x240">Miniature (320x240)</option>
                        <option value="640x480">Miniature XL (640x480)</option>
                        <option value="700x700">Miniature Forum (700x700)</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3">Redimensionner l'image</label>
                <div class="col-md-9">
                    <select name="redimImage" class="form-control">
                        <option value="" selected>-- Ne pas effectuer --</option>
                        <option value="320x240">320x240</option>
                        <option value="640x480">640x480</option>
                        <option value="800x600">800x600</option>
                        <option value="1024x768">1024x768</option>
                    </select>
                </div>
            </div>
            <button class="btn btn-info" name="Submit" type="submit">Envoyer</button>
        </form>
    </div>
</div>
<?php
require _TPL_BOTTOM_;
?>