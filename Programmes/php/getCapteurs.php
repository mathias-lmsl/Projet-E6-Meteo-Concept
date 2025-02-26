<?php
require "connectDB.php";

if (isset($_GET['carteId'])) {
    $carteId = $_GET['carteId'];

    try {
        $req = $bdd->prepare("SELECT IdCapteur, Marque, Reference FROM capteur WHERE IdCarte = :carteId");
        $req->execute([':carteId' => $carteId]);
        $capteurs = $req->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($capteurs);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erreur lors de la récupération des capteurs : " . $e->getMessage()]);
    }
}