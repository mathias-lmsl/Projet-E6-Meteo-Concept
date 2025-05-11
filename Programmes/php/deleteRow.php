<?php
// Inclusion du fichier de connexion à la base de données
require_once 'connectDB.php'; 

// Vérifier que l'ID et la table sont bien passés via POST
if (isset($_POST['id']) && isset($_POST['table'])) {
    $id = $_POST['id'];
    $table = $_POST['table'];

    // Vérifier que l'ID est valide
    if (is_numeric($id)) {
        // Préparer la requête de suppression en fonction de la table
        if ($table === 'serre') {
            $query = "DELETE FROM serres WHERE idSerre = :id";
        } elseif ($table === 'carte') {
            $query = "DELETE FROM cartes WHERE DevEui = :id";
        } else {
            echo json_encode(['success' => false, 'error' => 'Table non supportée']);
            exit;
        }

        try {
            // Préparer la requête avec la connexion à la base de données
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Vérifier si une ligne a été supprimée
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Aucun élément trouvé avec cet ID']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID invalide']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
}
?>