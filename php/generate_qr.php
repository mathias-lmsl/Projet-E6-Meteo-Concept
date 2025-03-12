<?php
require "../config/databaseadmin.php";
include_once('../phpqrcode/qrlib.php');

if (!empty($_POST['carte_id'])) {
    $carteId = $_POST['carte_id'];
    
    // Récupérer les informations de la carte
    $stmt = $bdd->prepare('SELECT * FROM carte WHERE IdCarte = ?');
    $stmt->execute([$carteId]);
    $carte = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($carte) {
        $lien = "localhost/secur/projettest/php/qrcode.php?id=" . $carteId;
        $file = "../qrcodes/CarteId" . $carteId . ".png";
        
        // Assurer que le dossier existe
        if (!is_dir('../qrcodes')) {
            mkdir('../qrcodes', 0777, true);
        }
        
        // Générer le QR Code
        QRcode::png($lien, $file, QR_ECLEVEL_L, 3);
        
        echo '<img src="' . htmlspecialchars($file) . '" alt="QR Code">';
    } else {
        echo "<p>Erreur : Carte non trouvée.</p>";
    }
} else {
    echo "<p>Erreur : Aucune carte sélectionnée.</p>";
}
