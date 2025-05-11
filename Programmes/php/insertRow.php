<?php
require "connectDB.php";

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table'])) {
    $table = $_POST['table'];

    try {
        // On récupère la structure de la table
        $stmt = $bdd->prepare("DESCRIBE " . $table);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // On exclut les colonnes non modifiables
        $columns = array_filter($columns, function($col) {
            return !in_array($col, ['IdCapteur', 'DateMiseEnService']);
        });

        $insertCols = [];
        $insertVals = [];
        $params = [];

        foreach ($columns as $col) {
            if (isset($_POST[$col])) {
                $insertCols[] = $col;
                $insertVals[] = ':' . $col;
                $params[':' . $col] = $_POST[$col];
            }
        }

        // ➕ Ajoute DateMiseEnService avec la date actuelle ➕
        $insertCols[] = 'DateMiseEnService';
        $insertVals[] = 'NOW()';

        $sql = "INSERT INTO $table (" . implode(',', $insertCols) . ") VALUES (" . implode(',', $insertVals) . ")";
        $stmt = $bdd->prepare($sql);
        $stmt->execute($params);

        $response['success'] = true;

    } catch (PDOException $e) {
        $response['error'] = $e->getMessage();
    }
} else {
    $response['error'] = 'Requête invalide';
}

echo json_encode($response);
?>