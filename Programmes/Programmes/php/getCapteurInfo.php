<?php
require "connectDB.php";

if (isset($_GET['capteur_id'])) {
    $capteurId = $_GET['capteur_id'];
    try {
        $stmt = $bdd->prepare("SELECT GrandeurCapt, Unite FROM grandeur WHERE GrandeurCapt = (SELECT GrandeurCapt FROM capteur WHERE IdCapteur = ?)");
        $stmt->execute([$capteurId]);
        $capteurInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($capteurInfo);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>