<?php
require "connectDB.php";

try {
    $options = [];

    // Etats composants
    $stmt = $bdd->query("
        SELECT DISTINCT EtatComposant FROM carte
        UNION
        SELECT DISTINCT EtatComposant FROM capteur
    ");
    $options['EtatComposant'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Grandeur capteurs
    $stmt = $bdd->query("SELECT DISTINCT GrandeurCapt FROM capteur");
    $options['GrandeurCapt'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // ➕ Unités capteurs
    $stmt = $bdd->query("SELECT DISTINCT Unite FROM capteur WHERE Unite IS NOT NULL");
    $options['Unite'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // ➕ Cartes
    $stmt = $bdd->query("SELECT DevEui, Nom FROM carte");
    $options['Cartes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($options);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>