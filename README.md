[![Build Status](https://travis-ci.org/AnaelMobilia/image-heberg.fr.svg?branch=master)](https://travis-ci.org/AnaelMobilia/image-heberg.fr)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/d61a46162db94e0b8a053f4bb5dbc62f)](https://www.codacy.com/app/AnaelMobilia/image-heberg-fr?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=AnaelMobilia/image-heberg.fr&amp;utm_campaign=Badge_Grade)
# image-heberg.fr
Service d'hébergement d'images en ligne

# Configuration requise
  - PHP 5.6, 7.0 ou 7.1
  - MySQL
  - Serveur web comprenant les fichiers .htaccess

# Installation
  - Créer une base de données à partir du fichier database.sql
  - Copier les fichiers dans le répertoire du serveur web
  - Renommer le fichier config_empty.php en config.php et compléter les différents champs
  - Configurer l'URL du site dans le fichier .htaccess
  - Se connecter avec le compte admin / password. Ce compte est le compte de l'administrateur du site. (pensez à mettre à jour l'adresse mail associée !)

# Stockage des images
  - Les images sont stockées dans le répertoire /files ou /files/thumbs en fonction de leur type.
  - Chaque image est stockée sous forme de md5 (correspondance en BDD)
  - Pour limiter le nombre de fichiers par répertoire, chaque image est stockée dans un sous répertoire qui est la première lettre de son md5