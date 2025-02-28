<?php
require "connectDB.php";

if (isset($_GET['serre_id'])) {
    $serreId = $_GET['serre_id'];
    try {
        $stmt = $bdd->prepare("SELECT IdChapelle, Nom FROM chapelle WHERE IdSerre = ?");
        $stmt->execute([$serreId]);
        $chapelles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($chapelles);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>