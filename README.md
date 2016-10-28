[![Build Status](https://travis-ci.org/AnaelMobilia/image-heberg.fr.svg?branch=master)](https://travis-ci.org/AnaelMobilia/image-heberg.fr)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/d61a46162db94e0b8a053f4bb5dbc62f)](https://www.codacy.com/app/AnaelMobilia/image-heberg-fr?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=AnaelMobilia/image-heberg.fr&amp;utm_campaign=Badge_Grade)
# image-heberg.fr
Service d'hébergement d'images en ligne

# Configuration requise
  - PHP 5.5 ou plus récent
  - MySQL
  - Serveur web comprenant les fichiers .htaccess

# Installation
  - Créer une base de données à partir du fichier database.sql
  - Copier les fichiers dans le répertoire du serveur web
  - Renommer le fichier configV2_empty.php en configV2.php et compléter les différents champs
  - Renommer le fichier config_empty.php en config.php et compléter les différents champs
  - Configurer l'URL du site dans le fichier .htaccess
  - Se connecter avec le compte admin / password. Ce compte sera le compte de l'administrateur du site (prévoir de modifier le hash stocké en base si un salt est défini !)
