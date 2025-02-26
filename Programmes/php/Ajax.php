<?php
require "connectDB.php";

if (isset($_GET['type'])) {
    try {
        switch ($_GET['type']) {
            case 'chapelles':
                if (!isset($_GET['serreId'])) {
                    echo json_encode(["error" => "Aucun Id de serre fourni"]);
                    exit;
                }
                $req = $bdd->prepare("SELECT IdChapelle, Commentaire FROM Chapelle WHERE IdSerre = :serreId");
                $req->execute([':serreId' => $_GET['serreId']]);
                echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
                break;

            case 'cartes':
                if (!isset($_GET['chapelleId'])) {
                    echo json_encode(["error" => "Aucun Id de chapelle fourni"]);
                    exit;
                }
                $req = $bdd->prepare("SELECT IdCarte, NumSerie FROM Carte WHERE IdChapelle = :chapelleId");
                $req->execute([':chapelleId' => $_GET['chapelleId']]);
                echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
                break;

            case 'capteurs':
                if (!isset($_GET['carteId'])) {
                    echo json_encode(["error" => "Aucun Id de carte fourni"]);
                    exit;
                }
                $req = $bdd->prepare("SELECT IdCapteur, Marque, Reference FROM Capteur WHERE IdCarte = :carteId");
                $req->execute([':carteId' => $_GET['carteId']]);
                echo json_encode($req->fetchAll(PDO::FETCH_ASSOC));
                break;

            default:
                echo json_encode(["error" => "Type inconnu"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Erreur SQL : " . $e->getMessage()]);
    }
}
?>