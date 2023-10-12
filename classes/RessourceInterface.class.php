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

/**
 * Fonctions devant êtres implémentées spécifiquement par les images et miniatures
 */
interface RessourceInterface
{
    /**
     * Crée sur le HDD et dans la BDD la ressource
     * @return bool Résultat ?
     */
    public function creer(): bool;

    /**
     * Charge un objet ressource depuis la BDD
     * @param string $value Identifiant image-heberg
     * @param string $fromField Champ à utiliser en BDD
     * @return bool Résultat ?
     */
    public function charger(string $value, string $fromField = RessourceObject::SEARCH_BY_NAME): bool;

    /**
     * Enregistre en BDD un objet ressource
     */
    public function sauver(): void;

    /**
     * Supprime sur le HDD et dans la BDD la ressource
     */
    public function supprimer(): void;
}
