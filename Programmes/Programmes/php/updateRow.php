<?php
require "connectDB.php";

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table']) && isset($_POST['id'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];

    try {
        // On récupère la structure de la table
        $stmt = $bdd->prepare("DESCRIBE " . $table);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Colonnes à ignorer selon la table
        $ignore = ['DateMiseEnService'];
        if ($table === 'capteur') {
            $primaryKey = 'IdCapteur';
            $ignore[] = 'IdCapteur';
        } elseif ($table === 'carte') {
            $primaryKey = 'DevEui';
            $ignore[] = 'DevEui';
        } else {
            echo json_encode(['success' => false, 'error' => 'Table non supportée']);
            exit;
        }

        $setClause = [];
        $params = [];

        foreach ($columns as $col) {
            if (!in_array($col, $ignore) && isset($_POST[$col])) {
                $setClause[] = "$col = :$col";
                $params[":$col"] = $_POST[$col];
            }
        }

        $params[":id"] = $id;

        $sql = "UPDATE $table SET " . implode(", ", $setClause) . " WHERE $primaryKey = :id";
        $stmt = $bdd->prepare($sql);
        $stmt->execute($params);

        $response['success'] = true;

    } catch (PDOException $e) {
        $response['error'] = $e->getMessage();
    }
} else {
    $response['error'] = "Requête invalide";
}

echo json_encode($response);
?>