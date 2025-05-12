<?php
require "connectDB.php"; // Connexion à la base de données

header('Content-Type: application/json'); // Réponse en JSON

$response = ['success' => false]; // Réponse par défaut

// Vérifie que la requête est POST et contient bien les champs nécessaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table']) && isset($_POST['id'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];

    try {
        // Récupère les colonnes de la table
        $stmt = $bdd->prepare("DESCRIBE " . $table);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Colonnes à ne pas modifier
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

        $setClause = []; // Liste des champs à mettre à jour
        $params = [];    // Paramètres liés

        // Pour chaque colonne, on crée les paires clé/valeur pour l'UPDATE
        foreach ($columns as $col) {
            if (!in_array($col, $ignore) && isset($_POST[$col])) {
                $setClause[] = "$col = :$col";
                $params[":$col"] = $_POST[$col];
            }
        }

        $params[":id"] = $id; // Clé primaire pour le WHERE

        // Construction de la requête SQL finale
        $sql = "UPDATE $table SET " . implode(", ", $setClause) . " WHERE $primaryKey = :id";
        $stmt = $bdd->prepare($sql);
        $stmt->execute($params);

        $response['success'] = true; // Mise à jour réussie

    } catch (PDOException $e) {
        $response['error'] = $e->getMessage(); // Gestion des erreurs SQL
    }
} else {
    $response['error'] = "Requête invalide"; // Mauvais appel
}

echo json_encode($response); // Envoie la réponse JSON
?>