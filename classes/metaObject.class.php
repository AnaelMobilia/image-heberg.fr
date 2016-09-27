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

/**
 * Les méthodes "génériques"
 *
 * @author anael
 */
class metaObject {

    /**
     * Liste des images n'ayant jamais été affichée et présente sur le serveur depuis une année
     * @return \ArrayObject
     */
    public static function getNeverUsedOneYear() {
        // BDD
        global $maBDD;

        // Toutes les images jamais affichées & envoyées il y a plus d'un an
        $dateUnAn = date('Y-m-d', strtotime('-1year'));
        $req = "SELECT new_name FROM " . imageObject::tableName . " where last_view = '0000-00-00' and date_envoi < '" . $dateUnAn . "'";

        // Exécution de la requête
        $resultat = $maBDD->query($req);


        $retour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $retour->append($value->new_name);
        }

        return $retour;
    }

    /**
     * Liste des images n'ayant pas été affichée les trois dernières années
     * @return \ArrayObject
     */
    public static function getUnusedThreeYear() {
        // BDD
        global $maBDD;

        // Toutes les images jnon affichées depuis 3 ans
        $dateTroisAns = date('Y-m-d', strtotime('-3year'));
        $req = "SELECT new_name FROM " . imageObject::tableName . " where last_view < '" . $dateTroisAns . "'";

        // Exécution de la requête
        $resultat = $maBDD->query($req);


        $retour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $retour->append($value->new_name);
        }

        return $retour;
    }

    /**
     * Liste de l'ensemble des images en BDD
     * @return \ArrayObject
     */
    public static function getAllImagesNameBDD() {
        // BDD
        global $maBDD;

        // Toutes les images
        $req = "SELECT new_name FROM " . imageObject::tableName;

        // Exécution de la requête
        $resultat = $maBDD->query($req);


        $retour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $retour->append($value->new_name);
        }

        return $retour;
    }

    /**
     * Liste de l'ensemble des images en HDD
     * @return \ArrayObject
     */
    public static function getAllImagesNameHDD() {
        $retour = new ArrayObject();

        // Scanne le répertoire des images
        $scan_rep = scandir(_PATH_IMAGES_);
        // Pour chaque image
        foreach ($scan_rep as $item) {
            // On ne rapporte pas les répertoires
            if (!is_dir(_PATH_IMAGES_ . $item)) {
                $retour->append($item);
            }
        }

        return $retour;
    }

    /**
     * Liste de l'ensemble des miniatures en BDD
     */
    public static function getAllMiniaturesNameBDD() {
        // BDD
        global $maBDD;

        // Toutes les images
        $req = "SELECT new_name FROM " . imageObject::tableName . ", " . miniatureObject::tableName . " WHERE " . imageObject::tableName . ".id = " . miniatureObject::tableName . ".id";

        // Exécution de la requête
        $resultat = $maBDD->query($req);


        $retour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($resultat->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $retour->append($value->new_name);
        }

        return $retour;
    }

    /**
     * Liste de l'ensemble des miniatures en HDD
     * @return \ArrayObject
     */
    public static function getAllMiniaturesNameHDD() {
        $retour = new ArrayObject();

        // Scanne le répertoire des images
        $scan_rep = scandir(_PATH_MINIATURES_);
        // Pour chaque image
        foreach ($scan_rep as $item) {
            // On ne rapporte pas les répertoires
            if (!is_dir(_PATH_MINIATURES_ . $item)) {
                $retour->append($item);
            }
        }

        return $retour;
    }

    /**
     * Toutes les images appartenant à un utilisateur
     * @global type $maBDD
     * @param type $userId ID de l'user en question
     * @return \ArrayObject new_name image
     */
    public static function getAllPicsOffOneUser($userId) {
        global $maBDD;

        // Toutes les images
        $req = $maBDD->prepare("SELECT new_name FROM " . utilisateurObject::tableNamePossede . " a, " . imageObject::tableName . " b WHERE a.id = b.id AND pk_membres = ? ");
        /* @var $req PDOStatement */
        $req->bindParam(1, $userId, PDO::PARAM_INT);

        // Exécution de la requête
        $req->execute();

        $retour = new ArrayObject();
        // Pour chaque résultat retourné
        foreach ($req->fetchAll() as $value) {
            // J'ajoute le nom de l'image
            $retour->append($value->new_name);
        }

        return $retour;
    }

    /**
     * Vérifie que l'utilisateur à le droit d'afficher la page et affiche un EM au cas où
     * @param type $levelRequis
     */
    public static function checkUserAccess($levelRequis) {
        $monUser = new sessionObject();
        if ($monUser->verifierDroits($levelRequis) === FALSE) {
            require _TPL_TOP_;
            ?>
            <div class="jumbotron">
                <h1>Accès refusé</h1>
                <p>Désolé, vous n'avez pas le droit d'accèder à cette page.</p>
            </div>
            <?php
            require _TPL_BOTTOM_;
            die();
        }
    }

    /**
     * Vérifier si un login est disponible pour enregistrement
     * @global type $maBDD
     * @param type $login
     * @return boolean
     */
    public static function verifierLoginDisponible($login) {
        global $maBDD;

        // Je vais chercher les infos en BDD
        $req = $maBDD->prepare("SELECT * FROM " . utilisateurObject::tableNameUtilisateur . " WHERE login = ?");
        /* @var $req PDOStatement */
        $req->bindValue(1, $login, PDO::PARAM_STR);
        $req->execute();

        // Par défaut le login est disponible
        $retour = TRUE;

        // Si j'ai un résultat...
        if ($req->fetch()) {
            // Le retour est négatif
            $retour = FALSE;
        }

        return $retour;
    }

}
