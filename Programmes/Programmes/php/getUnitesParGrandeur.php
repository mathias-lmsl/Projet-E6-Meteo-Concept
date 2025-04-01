<?php
require 'connectDB.php';

if (isset($_GET['grandeur'])) {
    $grandeur = $_GET['grandeur'];

    $stmt = $bdd->prepare("SELECT Unite FROM grandeur WHERE GrandeurCapt = :grandeur");
    $stmt->execute([':grandeur' => $grandeur]);

    $unites = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($unites);
} else {
    echo json_encode([]);
}
?>