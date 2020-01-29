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
if (!defined('_TRAVIS_')) {
   require 'config/config.php';
}
require _TPL_TOP_;

$erreur = FALSE;
$msgErreur = '';

/**
 * Vérification du paramètre
 */
if (!isset($_GET['id']) || !isset($_GET['type'])) {
   $erreur = TRUE;
   $msgErreur .= 'La page n\'a pas été appelée correctement !<br />';
}

/**
 * Chargement de l'image depuis la BDD
 */
if (!$erreur) {
   if ((int) $_GET['type'] === ressourceObject::typeImage) {
      $monImage = new imageObject();
   } else {
      $monImage = new miniatureObject();
   }

   $retour = $monImage->charger($_GET['id']);

   // Gestion du retour
   if (!$retour) {
      $erreur = TRUE;
      $msgErreur .= 'Cette image n\'existe pas !<br />';
   }
}

/**
 * Vérification des droits sur l'image
 * -> Possession
 * -> Envoi il y a moins d'une heure par la même @ IP
 */
if (!$erreur) {
   if ($monImage->isProprietaire() || ((strtotime($monImage->getDateEnvoiBrute()) + 3600) > strtotime("now") && $monImage->getIpEnvoi() === $_SERVER['REMOTE_ADDR'])) {
      // Effacement...
      $monImage->supprimer();
   } else {
      $erreur = TRUE;
      $msgErreur = 'Vous n\'avez pas le droit de supprimer cette image !<br />';
   }
}
?>
<h1><small>Suppression du fichier</small></h1>
<?php if (!empty($msgErreur)): ?>
   <div class="alert alert-danger">
       <span class="glyphicon glyphicon-remove"></span>
       &nbsp;
       <b>Une erreur a été rencontrée !</b>
       <br />
       <?= $msgErreur ?>
   </div>
<?php else: ?>
   <div class="alert alert-success">
       <span class="glyphicon glyphicon-ok"></span>
       &nbsp;
       <b>L'image a été supprimée avec succès !</b>
   </div>
<?php endif; ?>

<?php require _TPL_BOTTOM_; ?>