<?php
require "connectDB.php";

if (isset($_GET['chapelleId'])) {
    $chapelleId = $_GET['chapelleId'];

    try {
        $req = $bdd->prepare("SELECT IdCarte, NumSerie FROM carte WHERE IdChapelle = :chapelleId");
        $req->execute([':chapelleId' => $chapelleId]);
        $cartes = $req->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($cartes);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erreur lors de la récupération des cartes : " . $e->getMessage()]);
    }
}
?>