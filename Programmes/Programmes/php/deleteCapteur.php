<?php
// Inclusion du fichier de connexion à la base de données
require_once 'connectDB.php'; 

// Vérifier que l'ID du capteur est passé via POST
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Vérifier que l'ID est valide
    if (is_numeric($id)) {
        // Préparer la requête de suppression pour le capteur
        $query = "DELETE FROM capteurs WHERE idCapteur = :id";

        try {
            // Préparer la requête avec la connexion à la base de données
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Vérifier si une ligne a été supprimée
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Aucun capteur trouvé avec cet ID']);
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