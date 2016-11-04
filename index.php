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
require 'config/configV2.php';
require _TPL_TOP_;

require_once ("./config/config.php");

// Anti-flood
$_SESSION['_upload'] = TRUE;
?>
<div class="jumbotron">
    <h1><small>Envoyer une image</small></h1>

    <div class="panel panel-primary">
        <div class="panel-body">
            Image-heberg.fr est un service gratuit vous permettant d'héberger vos images sur internet.
            <ul>
                <li>Images de type JPG, PNG, GIF</li>
                <li>Taille max. : <?= round(__MAX_SIZE__ / 1048576, 1) ?> Mo</li>
                <li>Dimensions max. : <?= _IMAGE_DIMENSION_MAX_ ?> x <?= _IMAGE_DIMENSION_MAX_ ?> pixels (hauteur x largeur)</li>
            </ul>
            <form enctype="multipart/form-data" action="upload.php" method="post" class="form-inline">
                <div class="form-group">
                    <fieldset>
                        <legend>Envoyer un fichier :</legend>
                        <input name="fichier" type="file" />
                        <br />
                        <button class="btn btn-info" name="Submit" type="submit"><strong>Envoyer</strong></button>
                        <br />
                        <em>Tout envoi de fichier implique l'acceptation des <a href="/cgu.php">Conditions Générales d'Utilisation</a> du service.</em>
                    </fieldset>
                </div>
                <br />
                <div class="form-group">
                    <fieldset>
                        <legend>
                            <span id="options">
                                Options : <!--<img id="image_options" src="/template/images/fleche_bas.png" alt="Options" style="height: 15px; width: 15px;"/>-->
                            </span>
                        </legend>
                        <div id="liste_options">
    <!--                            <input disabled type="checkbox" name="thumbs" />Faire une miniature
                            <select disabled name="t_size">
                                <option value="88x31">Bouton (88x31)</option>
                                <option value="90x90">Avatar (90x90)</option>
                                <option value="100x100">Aper&ccedil;u (100x100)</option>
                                <option value="140x100">Miniature (140x100)</option>
                                <option value="320x240">Miniature+ (320x240)</option>
                                <option value="640x480" selected="selected">Miniature++ (640x480)</option>
                                <option value="700x700">Miniature Forum (700x700)</option>
                            </select>
                            <br />
                            <input type="checkbox" name="t_info" disabled="disabled" />Information poids / taille original
                            <br />
                            <input disabled="disabled" type="checkbox" name="resize" />Redimensionner l'image
                            <select disabled="disabled" name="size">
                                <option value="320x240">320x240</option>
                                <option value="640x480">640x480</option>
                                <option value="800x600">800x600</option>
                                <option value="1024x768">1024x768</option>
                            </select>
                            <br />-->
                            <input type="checkbox" name="rotate"/>&nbsp;Rotation de l'image
                            <select name="angleRotation">
                                <optgroup label="Rotation vers la gauche">
                                    <option value="90">90&deg; (&frac14; tour)</option>
                                    <option value="180">180&deg; (&frac12; tour)</option>
                                    <option value="270">270&deg; (&frac34; tour)</option>
                                </optgroup>
                            </select>
                        </div>
                    </fieldset>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
require _TPL_BOTTOM_;
?>