<?php
require "connectDB.php";

if (isset($_GET['grandeur'])) {
    $grandeur = $_GET['grandeur'];
    try {
        $stmt = $bdd->prepare("SELECT IdCapteur FROM capteur WHERE GrandeurCapt = ?");
        $stmt->execute([$grandeur]);
        $capteurInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($capteurInfo);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>