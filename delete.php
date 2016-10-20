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
//	./delete.php
//	Suppression d'une image
//**************************************
require_once('./config/config.php');    //config du script
?>
<div class="jumbotron">
    <h1><small>Suppression du fichier</small></h1>

    <div class="panel panel-primary">
        <div class="panel-body">
            <?php
//---------------------------------
//	VARIABLES
//---------------------------------
            if (isset($_GET['id'])) {
                $file = mysql_real_escape_string($_GET['id']); //id de l'image concern�e
            } else {
                retour_erreur('Aucun fichier n\'a &eacute;t&eacute; sp&eacute;cifi&eacute; pour suppression!</p>', __FILE__, 'die', FALSE);
            }

//---------------------------------
//	VERIFICATIONS
//---------------------------------
            if (basename($file) == 'index.php') { //listage r�pertoire
                erreur('HACK', 'delete_index'); //hack
            }
            if (!preg_match('#^[0-9]+.[a-z]{3}$#', $file)) { //passage de param�tres
                erreur('HACK', 'delete_params'); //hack
            }

// V�rifie l'existence du fichier dans la DB
            if (!$data = sql_query('SELECT `id`, `ip_envoi`, `date_envoi` FROM `images` WHERE `new_name` = "' . mysql_real_escape_string($file) . '"')) {
                retour_erreur('Le fichier requis (' . $file . ') n\'est pas enregistr&eacute; dans la base de donn&eacute;es !</p>', __FILE__, 'die', FALSE);
            }
// Y a-t-il une miniature ?
            $thumb = sql_query('SELECT COUNT(*) FROM `thumbnails` WHERE `id` = "' . mysql_real_escape_string($data['id']) . '"');
// Quid du propri�taire ?
            $owner = sql_query('SELECT `pk_membres` FROM `possede` WHERE `id` = ' . $data['id']);

            if (!file_exists(__PATH__ . __TARGET__ . $file)) { //Fichier sur hdd?
                retour_erreur('Le fichier requis (' . $file . ') n\'existe pas sur le serveur !</p>', __FILE__, 'die', FALSE);
            }

            if (isset($_SESSION['connected']) && $_SESSION['level'] != 'admin') { //admin =  GOD !!!
                if (!(isset($_SESSION['connected']) && $owner == $_SESSION['user_id'])) {
                    if ($data['ip_envoi'] != $_SERVER['REMOTE_ADDR']) { //M�me IP?
                        retour_erreur('Vous ne pouvez pas supprimer ce fichier : vous ne semblez pas &ecirctre son propri&eacute;taire !</p>', __FILE__, 'die', FALSE);
                    }

                    if ((strtotime($data['date_envoi']) + 3600) < strtotime("now")) { //d�lai de suppression : 10 min
                        //send_mail_admin('Fichier &agrave; supprimer', $file);
                        retour_erreur('Vous ne pouvez plus effacer ce fichier : plus de 60 minutes se sont &eacute;coul&eacute;es !</p>', __FILE__, 'die', FALSE);
                    }
                }
            }

//--------------------
//	SUPPRESSION HDD
//--------------------
            if (!@unlink(__PATH__ . __TARGET__ . $file)) { //Suppression du fichier
                retour_erreur('L\'image (' . $file . ') ne peut �tre supprim&eacute;e.</p>', __FILE__, 'die', FALSE);
            }

            if ($thumb) { //suppression miniature le cas �ch�ant
                if (!@unlink(__PATH__ . __T_TARGET__ . $file)) {
                    retour_erreur('Erreur rencontr&eacute;e lors de la suppression de la miniature (' . $file . ').</p>', __FILE__, 'die', FALSE);
                }
            }

//--------------------
//	SUPPRESSION BDD
//--------------------
//---------------------------------
//	GESTION DU PROPRIETAIRE
//---------------------------------
            if (isset($_SESSION['connected']) && $_SESSION['connected'] == TRUE) { //connect�
                sql_query('DELETE FROM `possede` WHERE `id` = "' . mysql_real_escape_string($data['id']) . '"');
            }

            if ($thumb) {
                sql_query('DELETE FROM `thumbnails` WHERE `id` = "' . mysql_real_escape_string($data['id']) . '"');
            }
            sql_query('DELETE FROM `images` WHERE `new_name` = "' . mysql_real_escape_string($file) . '"');

            //$lang['CORPS'] = 'Le fichier &agrave; &eacute;t&eacute; effac&eacute; avec succ&egrave;s';
            ?>
            <div class = "alert alert-success">
                Le fichier &agrave; &eacute;t&eacute; effac&eacute; avec succ&egrave;s !
            </div>
        </div>
    </div>
</div>


<?php
//template('template.html', __FILE__);
?>
<?php require _TPL_BOTTOM_ ?>