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

//**************************************
//	./a_propos.php
//	Mentions légales & co
//**************************************
?>
<div class="jumbotron">
    <h1><small>A propos</small></h1>

    <div class="panel panel-primary">
        <div class="panel-body">
            <fieldset>
                <legend>Licences :</legend>
                <p>
                    Ce site est &eacute;dit&eacute; par <a href="http://www.anael.eu">Anael MOBILIA</a>
                    <br />
                    Pour toute demande ou information concernant ce site, merci d'utiliser <a href="/contact.php">le formulaire de contact</a>.
                </p>
            </fieldset>
            <br />
            <fieldset>
                <legend>Graphismes et images :</legend>
                <ul>
                    <li><a href="http://getbootstrap.com/">Bootstrap</a></li>
                </ul>
            </fieldset>
            <br />
            <fieldset>
                <legend>Technologies :</legend>
                <p>
                    Le code source du pr&eacute;sent site a &eacute;t&eacute; r&eacute;alis&eacute; en PHP, MySQL ainsi que XHTML, CSS et jQuery.
                    <br />
                    Il a &eacute;t&eacute; enti&egrave;rement r&eacute;alis&eacute; par l'auteur.
                    <br />
                    Le <a href="https://github.com/AnaelMobilia/image-heberg.fr">code source est disponible</a> sous licence GNU GPL V3.
                </p>
            </fieldset>
            <br />
            <fieldset>
                <legend>H&eacute;bergeur :</legend>
                <p>
                    Ce site est h&eacute;berg&eacute; chez <a href="http://www.ovh.com/">OVH</a>
                </p>
            </fieldset>
            <br />
            <fieldset>
                <legend>Conservations de donn&eacute;es &agrave; caract&egrave;re priv&eacute; :</legend>
                <p>
                    L'utilisation du service peut conduire à l'enregistrement de cookies sur votre ordinateur.
                    <br />
                    Vous pouvez supprimer librement ces cookies via les options de votre navigateur internet.
                    <br />
                    A d&eacute;faut, et sauf action sp&eacute;cifique et explicite de votre part, les cookies seront supprim&eacute;s lors de la fermeture de votre navigateur.
                    <br />
                    <br />
                    Votre adresse IP est enregistr&eacute;e dans la base de donn&eacute;es lors de l'envoi d'une image, de la cr&eacute;ation et &agrave; la connexion &agrave; votre espace membre.
                    <br />
                    La suppression d'une image envoy&eacute;e sur le service conduit &agrave; la suppression de toutes les informations li&eacute;es dans la base de donn&eacute;es.
                    <br />
                    <br />
                    Conform&eacute;ment &agrave; la directive 2006/24/CE sur la conservation des donn&eacute;es, tous les acc&egrave;s au service seront enregistr&eacute;s et conserv&eacute;s durant <em>- au moins -</em> 2 ann&eacute;es.
                    <br />
                    <br />
                    Aucune donn&eacute;e n'est utilis&eacute;e &agrave; but publicitaire ni est transmise &agrave; des tiers, ou r&eacute;utilis&eacute;e en dehors du pr&eacute;sent service.
                    <br />
                    <br />
                    Responsable du traitement : Anael Mobilia
                    <br />
                    Les informations recueillies font l’objet d’un traitement informatique destin&eacute; &agrave; personnaliser votre utilisation du service.
                    <br />
                    Vous n'&ecirc;tes pas oblig&eacute; de cr&eacute;er un espace membre pour utiliser le service.
                    <br />
                    Le destinataire des donn&eacute;es est image-heberg.fr <em>(Anael Mobilia)</em>.
                    <br />
                    Conform&eacute;ment &agrave; la loi « informatique et libert&eacute;s » du 6 janvier 1978 modifi&eacute;e en 2004,
                    vous b&eacute;n&eacute;ficiez d'un droit d'acc&egrave;s et de rectification aux informations qui vous concernent,
                    que vous pouvez exercer en vous adressant &agrave; <a href="/contact.php">Anael Mobilia via le formulaire de contact</a>.
                    <br />
                    Vous pouvez &eacute;galement, pour des motifs l&eacute;gitimes, vous opposer au traitement des donn&eacute;es vous concernant.
                </p>
            </fieldset>
        </div>
    </div>
</div>
<?php require _TPL_BOTTOM_ ?>