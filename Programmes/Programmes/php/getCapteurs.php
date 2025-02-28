<?php
require "connectDB.php";

if (isset($_GET['carte_id'])) {
    $carteId = $_GET['carte_id'];
    try {
        $stmt = $bdd->prepare("SELECT Capteur.IdCapteur, Capteur.Nom FROM capteur JOIN possede ON Capteur.IdCapteur = Possede.IdCapteur WHERE Possede.IdCarte = ?");
        $stmt->execute([$carteId]);
        $capteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($capteurs);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>