<?php

/*
 * Copyright 2008-2025 Anael MOBILIA
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

?>
    </div> <!-- "/jumbotron" -->
    </main>
    <footer class="footer mt-auto py-3">
        <div class="container-lg">
            <span class="text-muted">
                <?= _SITE_NAME_ ?>
                -
                <a href="<?= _URL_HTTPS_ ?>changelog.php">v2.6 (2025) <span class="bi-award"></span></a>
                -
                <a href="<?= _URL_HTTPS_ ?>stats.php">Statistiques <span class="bi-graph-up"></span></a>
                -
                <a href="<?= _URL_HTTPS_ ?>cgu.php">CGU <span class="bi-briefcase"></span></a>
                -
                <a href="<?= _URL_HTTPS_ ?>abuse.php">Signaler une image <span class="bi-radioactive"></span></a>
                <span class="d-none d-lg-inline">
                    <span class="d-none d-xl-inline">
                        -
                        Exécution en <?= round(microtime(true) - $timeStart, 4) ?>s
                        -
                        IPv<?= (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) ? '4' : '6' ?>
                    </span>
                    -
                    Outil développé par <a href="//www.anael.eu">Anael MOBILIA</a>
                    <a href="https://github.com/AnaelMobilia/image-heberg.fr" title="Voir le code source sur GitHub"><span class="bi-github"></span></a>
                </span>
            </span>
        </div>
    </footer>
    <script src="template/js/bootstrap-5.3.3.min.js"></script>
    <script src="template/js/js.php"></script>
    </body>
</html>