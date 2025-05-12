<?php
require "connectDB.php";

if (!isset($_GET['capteur_id'])) {
    echo json_encode(["error" => "ID capteur manquant"]);
    exit;
}

$capteurId = $_GET['capteur_id'];

try {
    $stmt = $bdd->prepare("SELECT Valeur, Horodatage FROM mesure WHERE IdCapteur = :capteurId ORDER BY Horodatage DESC LIMIT 1");
    $stmt->execute([':capteurId' => $capteurId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode($result);
    } else {
        echo json_encode(["error" => "Aucune mesure trouvée"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}