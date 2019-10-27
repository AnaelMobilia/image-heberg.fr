<?php
/*
 * Copyright 2008-2019 Anael Mobilia
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
</div> <!-- /container -->
</div><!-- /wrap -->
<footer>
    <div class="container">
        <p class="text-muted">
            <?php
            if (filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== FALSE) {
                $ip = 'IPv4';
            } else {
                $ip = 'IPv6';
            }
            ?>
            <?= _SITE_NAME_ ?>
            -
            <a href="<?= _URL_ ?>changelog.php">v2.0.2 (2019) <span class="glyphicon glyphicon-flash"></span></a>
            -
            <a href="<?= _URL_ ?>stats.php">Statistiques <span class="glyphicon glyphicon-stats"></span></a>
            -
            <a href="<?= _URL_ ?>cgu.php">CGU <span class="glyphicon glyphicon-briefcase"></span></a>
            -
            Exécution en <?= round(microtime(TRUE) - $timeStart, 5); ?>s
            -
            <?= $ip ?>
            -
            Un service proposé par <a href="<?= _ADMINISTRATEUR_SITE_ ?>"><?= _ADMINISTRATEUR_NOM_ ?></a>
        </p>
    </div>
</footer>
<script src="template/js/jquery-3.4.1.slim.min.js"></script>
<script src="template/js/bootstrap-3.3.7.min.js"></script>
<script src="template/js/js.php"></script>
</body>
</html>
