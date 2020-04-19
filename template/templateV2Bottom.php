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
?>
</div> <!-- /jumbotron -->
</main>
<footer class="footer">
    <div class="container">
        <span class="text-muted">
            <?php
            if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                $ip = '4';
            } else {
                $ip = '6';
            }
            ?>
            <?= _SITE_NAME_ ?>
            -
            <a href="<?= _URL_ ?>changelog.php">v2.0.5 (2020) <span class="fas fa-award"></span></a>
            -
            <a href="<?= _URL_ ?>stats.php">Statistiques <span class="fas fa-chart-bar"></span></a>
            -
            <a href="<?= _URL_ ?>cgu.php">CGU <span class="fas fa-briefcase"></span></a>
            -
            <a href="<?= _URL_ ?>abuse.php">Signaler une image <span class="fas fa-radiation"></span></a>
            <span class="d-none d-lg-inline">
                <span class="d-none d-xl-inline">
                    -
                    Exécution en <?= round(microtime(true) - $timeStart, 5); ?>s
                    -
                    IPv<?= $ip ?>
                </span>
                -
                Outil développé par <a href="//www.anael.eu">Anael MOBILIA</a>
            </span>
        </span>
    </div>
</footer>
<script src="template/js/jquery-3.4.1.slim.min.js"></script>
<script src="template/js/bootstrap-4.4.1.min.js"></script>
<script src="template/js/js.php"></script>
</body>
</html>
