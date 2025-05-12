<?php
// Connexion à la base de données
require "connectDB.php";

// Initialisation de la réponse par défaut
$response = ['success' => false];

// Vérifie que la requête est bien de type POST et que le nom de la table est fourni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table'])) {
    $table = $_POST['table'];

    try {
        // Récupère les colonnes de la table via la commande SQL DESCRIBE
        $stmt = $bdd->prepare("DESCRIBE " . $table);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Filtre les colonnes pour exclure celles non modifiables (ex : ID auto-incrémenté, date de mise en service)
        $columns = array_filter($columns, function($col) {
            return !in_array($col, ['IdCapteur', 'DateMiseEnService']);
        });

        $insertCols = []; // Liste des colonnes à insérer
        $insertVals = []; // Liste des placeholders :col
        $params = [];     // Paramètres à lier à la requête

        // Construction dynamique des colonnes et des valeurs à insérer
        foreach ($columns as $col) {
            if (isset($_POST[$col])) {
                $insertCols[] = $col;
                $insertVals[] = ':' . $col;
                $params[':' . $col] = $_POST[$col];
            }
        }

        // Ajoute la colonne DateMiseEnService avec la valeur NOW()
        $insertCols[] = 'DateMiseEnService';
        $insertVals[] = 'NOW()';

        // Construit et exécute la requête SQL d'insertion
        $sql = "INSERT INTO $table (" . implode(',', $insertCols) . ") VALUES (" . implode(',', $insertVals) . ")";
        $stmt = $bdd->prepare($sql);
        $stmt->execute($params);

        // Réponse de succès
        $response['success'] = true;

    } catch (PDOException $e) {
        // Capture et retourne l'erreur SQL
        $response['error'] = $e->getMessage();
    }

} else {
    // Si la requête est invalide
    $response['error'] = 'Requête invalide';
}

// Renvoie la réponse au format JSON
echo json_encode($response);
?>