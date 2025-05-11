<?php
require "connectDB.php";

if (isset($_GET['carte_id'])) {
    $carteId = $_GET['carte_id'];
    try {
        $stmt = $bdd->prepare("SELECT ca.IdCapteur, ca.Nom FROM capteur ca JOIN possede po ON ca.IdCapteur = po.IdCapteur WHERE po.DevEui = ?");
        $stmt->execute([$carteId]);
        $capteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($capteurs);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>