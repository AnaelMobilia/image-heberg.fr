<?php
/*
 * Copyright 2008-2017 Anael Mobilia
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
 * Fonctions devant êtres implémentées spécifiquement par les images et miniatures
 */
interface ressourceInterface {

    /**
     * Crée sur le HDD et dans la BDD la ressource
     * @return boolean Résultat ?
     */
    function creer();

    /**
     * Charge unn objet ressource depuis la BDD
     * @param string $nom Identifiant image-heberg
     * @return boolean Résultat ?
     */
    function charger($nom);

    /**
     * Enregistre en BDD un objet ressource
     * @return boolean Résultat ?
     */
    function sauver();

    /**
     * Supprime sur le HDD et dans la BDD la ressource
     * @return boolean Résultat ?
     */
    function supprimer();
}