<?php
require "../config/databasetech.php";

if (isset($_POST['Commentaire'])) {
    $id = $_POST['Id'];
    $commentaire = $_POST['Commentaire'];
    $type = $_POST['Type'];
    
    if ($type == 'capteur') {
        // Préparer la requête pour mettre à jour la table 'capteur'
        $stmt = $bdd->prepare('UPDATE capteur SET Commentaire = :Commentaire WHERE IdCapteur = :id');
        $stmt->execute(['Commentaire' => $commentaire, 'id' => $id]);  // Passer à la fois 'Commentaire' et 'id'
    } elseif ($type == 'carte') {
        // Préparer la requête pour mettre à jour la table 'carte'
        $stmt = $bdd->prepare('UPDATE carte SET Commentaire = :Commentaire WHERE DevEui = :id');
        $stmt->execute(['Commentaire' => $commentaire, 'id' => $id]);  // Passer à la fois 'Commentaire' et 'id'
    }

    // Fermer le curseur
    $stmt->closeCursor();
}
?>
