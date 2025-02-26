<?php
// getChapelles.php
require "connectDB.php";

if (isset($_GET['serreId'])) {
    $serreId = $_GET['serreId'];

    try {
        $req = $bdd->prepare("SELECT IdChapelle, Commentaire FROM chapelle WHERE IdSerre = :serreId");
        $req->execute([':serreId' => $serreId]);
        $chapelles = $req->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($chapelles);
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des chapelles : " . $e->getMessage());
    }
}
?>