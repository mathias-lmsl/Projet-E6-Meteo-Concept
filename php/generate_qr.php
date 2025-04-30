<?php
require "../config/databaseadmin.php"; // Connexion à la base de données
require "param.php"; // Fichier de paramètres modifiables
include_once('../phpqrcode/qrlib.php'); // Inclusion de la librairie PHP QR Code

if (!empty($_POST['DevEui'])) {
    $DevEui = $_POST['DevEui'];
    
    // Récupérer les informations de la carte
    $stmt = $bdd->prepare('SELECT Nom FROM carte WHERE DevEui = ?');
    $stmt->execute([$DevEui]);
    $donnees = $stmt->fetch();
    
    if ($donnees) {
        // Lien contenu dans le QR code qui sera renvoyé lors de son scan
        $lien = $cheminPage . "?DevEui=" . $DevEui;
        
        // Chemin du stockage du QR code
        $file = "../qrcodes/dernierqrcodegenerer.png";

        // Assurer que le dossier qrcodes existe
        if (!is_dir('../qrcodes')) mkdir('../qrcodes', 0777, true);
        
        // Voir si un fichier existe déjà, si oui on le supprime
        if (file_exists($file)) unlink($file);

        // Générer le QR Code avec la librairie PHP QR CODE
        // On appelle la classe QRcode qui va appeler une méthode static png
        // à laquelle on va envoyé des paramètre pour la création du QR code
        QRcode::png($lien, $file, QR_ECLEVEL_L, 3);
        
        // Ajouter le nom de la carte a laquelle correspond le QR code
        echo '<div id="qrCodeTitle">'.$donnees['Nom'].'</div>';

        // Ajouter un paramètre unique à l'URL pour éviter le cache
        $uniqueParam = time(); // Utilise un timestamp unique
        echo '<img src="' . htmlspecialchars($file) . '?t=' . $uniqueParam . '" alt="QR Code" style="border-radius: 12px;">';

    } else {
        // Dans le cas ou le DevEui ne correspond à aucune carte
        header('Location: error.php'); // On renvoie sur la page d'erreur
    }
} else {
    // Dans le cas ou un utilisateur tente d'accéder a cette page par URL
    header('Location: error.php'); // On renvoie sur la page d'erreur
}
?>
