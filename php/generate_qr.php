<?php
require "../config/databaseadmin.php";
include_once('../phpqrcode/qrlib.php');

if (!empty($_POST['carte_id'])) {
    $carteId = $_POST['carte_id'];
    
    // Récupérer les informations de la carte
    $stmt = $bdd->prepare('SELECT * FROM carte WHERE DevEui = ?');
    $stmt->execute([$carteId]);
    $donnees = $stmt->fetch();
    
    if ($donnees) {
        $lien = "http://192.168.1.86/projettest/php/qrcode.php?id=" . $carteId;
        $file = "../qrcodes/dernierqrcodegenerer.png";

        // Assurer que le dossier qrcodes existe
        if (!is_dir('../qrcodes')) mkdir('../qrcodes', 0777, true);
        
        // Voir si un fichier existe déjà, si oui on le supprime
        if (file_exists($file)) unlink($file);

        // Générer le QR Code
        QRcode::png($lien, $file, QR_ECLEVEL_L, 3);
        
        
        echo '<div id="qrCodeTitle">'.$donnees['Nom'].'</div>';
        // Ajouter un paramètre unique à l'URL pour éviter le cache
        $uniqueParam = time(); // Utilise un timestamp unique
        echo '<img src="' . htmlspecialchars($file) . '?t=' . $uniqueParam . '" alt="QR Code" style="border-radius: 12px;">';

    } else {
        echo "<p>Erreur : Carte non trouvée.</p>";
    }
} else {
    echo "<p>Erreur : Aucune carte sélectionnée.</p>";
}
