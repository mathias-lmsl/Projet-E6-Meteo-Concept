<?php
// Connexion à la base de données
require "connectDB.php"; //

try {
    $options = []; //

    $stmt = $bdd->query("
        SELECT DISTINCT EtatComposant FROM carte
        UNION
        SELECT DISTINCT EtatComposant FROM capteur
    "); //
    $options['EtatComposant'] = $stmt->fetchAll(PDO::FETCH_COLUMN); //

    $stmt = $bdd->query("SELECT DISTINCT GrandeurCapt FROM capteur"); //
    $options['GrandeurCapt'] = $stmt->fetchAll(PDO::FETCH_COLUMN); //

    $stmt = $bdd->query("SELECT DISTINCT Unite FROM capteur WHERE Unite IS NOT NULL"); //
    $options['Unite'] = $stmt->fetchAll(PDO::FETCH_COLUMN); //

    $stmt = $bdd->query("SELECT DevEui, Nom FROM carte"); //
    $options['Cartes'] = $stmt->fetchAll(PDO::FETCH_ASSOC); //

    // Récupérer toutes les chapelles (pour l'assignation aux cartes)
    $stmtChapelles = $bdd->query("SELECT IdChapelle, Nom FROM chapelle ORDER BY Nom ASC"); // Modifié de la réponse précédente
    $options['Chapelles'] = $stmtChapelles->fetchAll(PDO::FETCH_ASSOC); // Modifié de la réponse précédente

    // NOUVEAU : Récupérer toutes les serres (pour l'assignation aux chapelles)
    $stmtSerres = $bdd->query("SELECT IdSerre, Nom FROM serre ORDER BY Nom ASC");
    $options['Serres'] = $stmtSerres->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode($options); //

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]); //
}
?>