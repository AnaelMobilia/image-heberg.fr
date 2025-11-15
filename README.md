[![Build Status](https://travis-ci.org/AnaelMobilia/image-heberg.fr.svg?branch=master)](https://travis-ci.org/AnaelMobilia/image-heberg.fr)

# image-heberg.fr

Service d'hébergement d'images en ligne

# Configuration requise

- PHP 8.3, 8.4 [*(Préférez une version de PHP maintenue !)*](https://www.php.net/supported-versions.php)
- Imagick
- MySQL ou MariaDB
- Serveur web gérant les fichiers .htaccess

# Installation

- Créer une base de données à partir du fichier database.sql
- Copier les fichiers dans le répertoire du serveur web
- Renommer le fichier config_empty.php en config.php et compléter les différents champs
- Ajouter votre favicon dans template/images/monSite.ico
- Ajouter votre css dans template/css/monSite.css
- Configurer l'URL du site dans le fichier .htaccess (remplacer `https://www.image-heberg.fr`, `image-heberg\.fr\.cr` et
  `image-heberg\.fr`)
- Valider l'installation de base en appelant le fichier install.php (example.com/install.php)
- Mettre en place un cron sur cron/updateTorIp.php, cron/cleanImages.php, cron/cleanAccounts.php, cron/abuse.php
- Se connecter avec le compte admin / password. Ce compte est le compte de l'administrateur du site.
    - Modifier le mot de passe du compte
    - Mettre à jour l'adresse email associée

# Changer de thème

- Choisir un thème sur [bootswatch](https://bootswatch.com/)
- Télécharger le fichier bootstrap.min.css
- Remplacer le contenu du fichier templates/css/boostrap-x.y.z.min.css par le fichier téléchargé
- Si souhaité, ajouter du code CSS spécifique dans le fichier templates/css/monSite.css

# Stockage des images

- Les images sont stockées dans le répertoire /files ou /files/thumbs en fonction de leur type.
- Chaque image est stockée sous le nom du md5 de son fichier d'origine (correspondance en BDD)
- Pour limiter le nombre de fichiers par répertoire, chaque image est stockée dans un sous répertoire qui est la
  première lettre de son md5
