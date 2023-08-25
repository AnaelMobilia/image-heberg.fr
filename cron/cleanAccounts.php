<?php

/*
 * Copyright 2008-2023 Anael MOBILIA
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

/*
 * Suppression des comptes obsolètes
 */

require __DIR__ . '/../config/config.php';
define('_IS_CRON_', true);

// Création d'une session admin
$monUser = new UtilisateurObject();
$monUser->setLevel(UtilisateurObject::LEVEL_ADMIN);
$maSession = new SessionObject();
$maSession->setUserObject($monUser);

// Forcer les IP
$_SESSION['IP'] = '127.0.0.1';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Effacer les comptes jamais utilisés
$_POST['effacer'] = true;
require _PATH_ . 'admin/cleanAccountsNeverUsed.php';

echo '...done';
