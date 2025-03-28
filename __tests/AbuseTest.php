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

namespace ImageHebergTests;

use ArrayObject;
use ImageHeberg\HelperAbuse;
use ImageHeberg\ImageObject;
use ImageHeberg\HelperAdmin;
use ImageHeberg\RessourceObject;
use ImageHeberg\UtilisateurObject;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class AbuseTest extends TestCase
{
    /**
     * Signalement d'une image
     */
    #[RunInSeparateProcess]
    public function testAbuse(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_POST['userMail'] = 'john.doe@example.com';
        $_POST['urlImage'] = 'https://www.example.com/files/image_15.png';

        ob_start();
        require 'abuse.php';
        ob_end_clean();

        $imageBloquee = new ImageObject('image_15.png');
        $imageMemeMd5 = new ImageObject('image_16.png');


        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertTrue(
            $imageBloquee->isSignalee(),
            'Image signalée doit l\'être'
        );
        $this->assertTrue(
            $imageMemeMd5->isSignalee(),
            'Image avec même MD5 qu\'une image signalée doit l\'être également'
        );
    }

    /**
     * Renvoi d'une image bloquée et demande de son affichage
     */
    #[RunInSeparateProcess]
    public function testAbuseRenvoiImage(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_FILES['fichier']['size'] = 146;
        $_FILES['fichier']['name'] = 'imageDejaBloquee.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertTrue(
            $monImage->isBloquee(),
            'Renvoi image déjà bloquée doit être isBloquée en BDD'
        );
    }

    /**
     * Signalement d'une image approuvée
     */
    #[RunInSeparateProcess]
    public function testAbuseImageApprouvee(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_POST['userMail'] = 'john.doe@example.com';
        $_POST['urlImage'] = 'https://www.example.com/files/_image_404.png';

        ob_start();
        require 'abuse.php';
        ob_end_clean();

        $imageSignalee = new ImageObject('_image_404.png');
        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertFalse(
            $imageSignalee->isSignalee(),
            'Image approuvée qui est signalée ne doit pas être bloquée'
        );
    }

    /**
     * Approbation d'une image signalée
     */
    public function testAbuseImageSignaleePuisApprouvee(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';

        // Flagguer l'image comme signalée
        $image = new ImageObject('_image_banned.png');
        $image->setSignalee(true);
        $image->sauver();

        // Se connecter en tant que l'admin
        $monMembre = new UtilisateurObject();
        $monMembre->connexion('admin', 'password');

        // Approuver l'image dans l'admin
        $_GET['action'] = 'approuver';
        $_GET['idImage'] = '2';

        ob_start();
        require 'admin/abuse.php';
        ob_end_clean();

        $image = new ImageObject('_image_banned.png');
        $this->assertFalse(
            $image->isSignalee(),
            'Image signalée qui est approuvée ne doit plus être signalée'
        );
    }

    /**
     * Renvoi d'une image déjà bloquée
     */
    #[RunInSeparateProcess]
    public function testAbuseImageRenvoiImageBloqueeDepuisReseauMalveillant(): void
    {
        require 'config/config.php';

        $imagesAvantEnvoi = HelperAdmin::getImagesPotentiellementIndesirables();

        $_SERVER['REMOTE_ADDR'] = '10.10.10.11';
        $_SERVER['REMOTE_PORT'] = '1234';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_FILES['fichier']['size'] = 146;
        $_FILES['fichier']['name'] = 'imageDejaBloquee.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $imagesApresEnvoi = HelperAdmin::getImagesPotentiellementIndesirables();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            $imagesAvantEnvoi->count(),
            $imagesApresEnvoi->count(),
            'Le renvoi d\'une image bloquée doit être bloqué'
        );
    }

    /**
     * Envoi d'une image depuis le même réseau qu'une image bloquée
     */
    #[RunInSeparateProcess]
    public function testAbuseImageEnvoiDepuisReseauMalveillant(): void
    {
        require 'config/config.php';

        $imagesAvantEnvoi = HelperAdmin::getImagesPotentiellementIndesirables();

        $_SERVER['REMOTE_ADDR'] = '10.10.10.11';
        $_SERVER['REMOTE_PORT'] = '1234';
        $_POST['Submit'] = 1;
        $_SESSION['flag'] = true;
        $_FILES['fichier']['size'] = 146;
        $_FILES['fichier']['name'] = 'rotation_original.gif';
        $_FILES['fichier']['tmp_name'] = _PATH_TESTS_IMAGES_ . $_FILES['fichier']['name'];

        ob_start();
        require 'upload.php';
        ob_end_clean();

        $imagesApresEnvoi = HelperAdmin::getImagesPotentiellementIndesirables();

        $this->assertEmpty(
            $msgErreur,
            __FUNCTION__ . ' ne devrait pas lever de message d\'erreur - Erreur : ' . $msgErreur
        );
        $this->assertEmpty(
            $msgWarning,
            __FUNCTION__ . ' ne devrait pas lever de message de warning - Warning : ' . $msgWarning
        );
        $this->assertEquals(
            ($imagesAvantEnvoi->count() + 1),
            $imagesApresEnvoi->count(),
            'L\'image envoyée devrait être considérée comme potentiellement indésirable : ' . var_export($imagesApresEnvoi, true)
        );
    }

    /**
     * Image avec une miniature ENORMEMENT affichée
     */
    #[RunInSeparateProcess]
    public function testAbuseImageMiniatureTropAffichee(): void
    {
        require 'config/config.php';

        $images = HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_);

        $this->assertNotEmpty(
            $images,
            'Les affichages des miniatures doivent compter dans les affichages d\'une image pour la détection des abus : ' . var_export($images, true)
        );
    }


    /**
     * Division des seuils de détection pour une image envoyée du même réseau qu'une image déjà bloquée
     */
    #[RunInSeparateProcess]
    public function testAbuseDivisionSeuilDetectionSiReseauMalveillant(): void
    {
        require 'config/config.php';

        // Liste des images suspectes
        $listeImagesSuspectes = HelperAdmin::getImagesPotentiellementIndesirables();

        $imagesTropAffichees = HelperAdmin::getImagesTropAffichees((_ABUSE_NB_AFFICHAGES_PAR_JOUR_WARNING_ / _ABUSE_DIVISION_SEUILS_SI_SUSPECT_), true);

        $this->assertContains(
            'image_20.gif',
            $listeImagesSuspectes,
            'L\'image 19 est suspecte car envoyée d\'un même réseau que l\'image 18'
        );

        $this->assertContains(
            'image_20.gif',
            $imagesTropAffichees,
            'L\'image 19 a été trop affichée -> WARNING (elle est suspecte)'
        );
    }

    /**
     * Réputation des adresses IP basées sur les images déjà bloquées pour leur réseau
     */
    #[RunInSeparateProcess]
    public function testAbuseReputationIp(): void
    {
        require 'config/config.php';

        // Adresse IP ayant envoyé les fichiers bloqués
        $this->assertEquals(
            5,
            HelperAbuse::checkIpReputation('192.168.0.1'),
            'Le réseau 192.168.0.0/24 a 5 images bloquées'
        );
        // Adresse IP du même réseau que celle ayant envoyé les fichiers bloqués
        $this->assertEquals(
            5,
            HelperAbuse::checkIpReputation('192.168.0.100'),
            'Le réseau 192.168.0.0/24 a 5 images bloquées'
        );
        // Adresse IP random qui n'a pas d'images bloqués
        $this->assertEquals(
            0,
            HelperAbuse::checkIpReputation('2a01:ab51:8880:e010:1da5:be67:6a52:a5bf'),
            'Aucune image bloquée dans le réseau de cette adresse IP'
        );
    }

    /**
     * Projection du nombre d'affichage d'une image pour détecter une atteinte de limite ultérieure
     */
    #[RunInSeparateProcess]
    public function testAbuseProjectionAffichages(): void
    {
        require 'config/config.php';

        $images = HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_BLOCAGE_AUTO_, false, true);

        $this->assertContains(
            'image_27.png',
            $images,
            'L\'image 27 doit être détectée comme dépassant le nombre d\'affichage en projection : ' . var_export($images, true)
        );
    }

    /**
     * Le blocage d'une image bloque toutes celles partageant le même MD5
     */
    #[RunInSeparateProcess]
    public function testAbuseContaminationMD5Blocage(): void
    {
        require 'config/config.php';

        $monImage = new ImageObject('28', RessourceObject::SEARCH_BY_ID);
        $monImage->bloquer();

        $image28 = new ImageObject('28', RessourceObject::SEARCH_BY_ID);
        $image29 = new ImageObject('29', RessourceObject::SEARCH_BY_ID);

        $this->assertTrue(
            $image28->isBloquee(),
            'L\'image 28 doit être bloquée'
        );
        $this->assertTrue(
            $image29->isBloquee(),
            'L\'image 29 doit être bloquée'
        );
        $this->assertFalse(
            $image28->isApprouvee(),
            'L\'image 28 ne doit pas être approuvée'
        );
        $this->assertFalse(
            $image29->isApprouvee(),
            'L\'image 29 ne doit pas être approuvée'
        );
    }

    /**
     * L'approbation d'une image approuve toutes celles partageant le même MD5
     */
    #[RunInSeparateProcess]
    public function testAbuseContaminationMD5Approbation(): void
    {
        require 'config/config.php';

        $monImage = new ImageObject('30', RessourceObject::SEARCH_BY_ID);
        $monImage->approuver();

        $image30 = new ImageObject('30', RessourceObject::SEARCH_BY_ID);
        $image31 = new ImageObject('31', RessourceObject::SEARCH_BY_ID);
        $image32 = new ImageObject('32', RessourceObject::SEARCH_BY_ID);

        $this->assertFalse(
            $image30->isBloquee(),
            'L\'image 30 ne doit pas être bloquée'
        );
        $this->assertFalse(
            $image31->isBloquee(),
            'L\'image 31 ne doit pas être bloquée'
        );
        $this->assertFalse(
            $image32->isBloquee(),
            'L\'image 32 ne doit pas être bloquée'
        );
        $this->assertFalse(
            $image30->isSignalee(),
            'L\'image 30 ne doit pas être signalée'
        );
        $this->assertFalse(
            $image31->isSignalee(),
            'L\'image 31 ne doit pas être signalée'
        );
        $this->assertFalse(
            $image32->isSignalee(),
            'L\'image 32 ne doit pas être signalée'
        );
        $this->assertTrue(
            $image30->isApprouvee(),
            'L\'image 30 doit être approuvée'
        );
        $this->assertTrue(
            $image31->isApprouvee(),
            'L\'image 31 doit être approuvée'
        );
        $this->assertTrue(
            $image32->isApprouvee(),
            'L\'image 32 doit être approuvée'
        );
    }

    /**
     * Une image demandée par un User-Agent suspect doit être signalée.
     */
    #[RunInSeparateProcess]
    public function testBlocageParUserAgent(): void
    {
        require 'config/config.php';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REMOTE_PORT'] = '1234';
        $_SERVER['HTTP_USER_AGENT'] = 'someUserAgentNumberOne';
        $_SERVER['REQUEST_URI'] = 'files/image_UserAgentMalveillant.png';

        $monImage = new ImageObject('33', RessourceObject::SEARCH_BY_ID);
        $this->assertFalse(
            $monImage->isSignalee(),
            'L\'image ne doit pas être signalée de base.'
        );

        ob_start();
        require 'displayPics.php';
        ob_end_clean();

        $monImage = new ImageObject('33', RessourceObject::SEARCH_BY_ID);
        $this->assertTrue(
            $monImage->isSignalee(),
            'Signalement de l\'image basé sur le User-Agent présenté'
        );
    }

    /**
     * Projection du nombre d'affichage d'une image pour détecter une atteinte de limite ultérieure
     */
    #[RunInSeparateProcess]
    public function testAbuseNombreAffichagesAbusifs(): void
    {
        require 'config/config.php';

        $images = HelperAdmin::getImagesTropAffichees(_ABUSE_NB_AFFICHAGES_PAR_JOUR_ABUSIF_, false, false, true);

        $this->assertContains(
            'image_34.png',
            $images,
            'L\'image 34 doit être détectée comme ayant un nombre d\'affichages abusif : ' . var_export($images, true)
        );
    }

    /**
     * Contamination sur tous les fichiers d'une catégorisation d'abus
     */
    #[Depends('testAbuseImageSignaleePuisApprouvee')]
    public function testAbuseContaminationCategorisationAbus(): void
    {
        require 'config/config.php';

        $images = ImageObject::chargerMultiple(['97a3a88502d6-theSameMd5-97a3a88502d6'], RessourceObject::SEARCH_BY_MD5);

        $this->assertTrue(
            (count($images) >= 2),
            'Les images 15 et 16 sont censées avoir ce MD5'
        );

        foreach ($images as $image) {
            $this->assertSame(
                $image->getCategorieBlocage(),
                '',
                'Les images 15 et 16 ne sont pas catégorisées'
            );
        }

        $monImage = new ImageObject('15', RessourceObject::SEARCH_BY_ID);
        $monImage->setCategorieBlocage('Pornographie');
        $monImage->categoriser();

        foreach ($images as $image) {
            $this->assertSame(
                $image->getCategorieBlocage(),
                'Pornographie',
                'Les images 15 et 16 doivent avoir été catégorisées (contamination par le MD5)'
            );
        }
    }

    /**
     * Nettoyage de la catégorisation d'une image lorsqu'elle est approuvée
     */
    #[RunInSeparateProcess]
    public function testDecategorisationLorsqueApprobation(): void
    {
        require 'config/config.php';

        $monImage = new ImageObject('96', RessourceObject::SEARCH_BY_ID);
        $this->assertSame(
            $monImage->getCategorieBlocage(),
            'Pornographie',
            'L\image 96 est catégorisée'
        );
        $this->assertTrue(
            $monImage->isSignalee(),
            'L\'image 96 est signalée'
        );
        $this->assertFalse(
            $monImage->isApprouvee(),
            'L\'image 96 n\'est pas approuvée'
        );

        $monImage->approuver();
        $monImage = new ImageObject('96', RessourceObject::SEARCH_BY_ID);
        $this->assertSame(
            $monImage->getCategorieBlocage(),
            '',
            'L\image 96 est approuvée et ne doit plus être catégorisée'
        );
        $this->assertFalse(
            $monImage->isSignalee(),
            'L\'image 96 n\'est pas signalée'
        );
        $this->assertTrue(
            $monImage->isApprouvee(),
            'L\'image 96 est approuvée'
        );
    }
}
