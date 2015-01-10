<?php
require '../../config/configV2.php';
$visiteur = new sessionObject();
// Utilisateur non connecté : Menu mon-compte caché + bouton pour l'afficher
if ($visiteur->getLevel() === utilisateurObject::levelGuest) :
    ?>
    // Cache les champs liés à l'espace membre
    $("#monCompte").hide();
    // Au clic sur le bouton, affichage
    $("#buttonMonCompteGestion").click(function() {
    $("#monCompteGestion").hide();
    $("#monCompte").show();
    });
    // J'empêche l'envoi du pseudo formulaire...
    $("#monCompteGestion").on("submit", function(e){
    e.preventDefault();
    });
<?php endif; ?>