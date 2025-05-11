<?php
require "connectDB.php";

if (isset($_GET['chapelle_id'])) {
    $chapelleId = $_GET['chapelle_id'];
    try {
        $stmt = $bdd->prepare("SELECT DevEui, Nom FROM carte WHERE IdChapelle = ?");
        $stmt->execute([$chapelleId]);
        $cartes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($cartes);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>