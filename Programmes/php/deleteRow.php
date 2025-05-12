<?php
// Inclusion du fichier de connexion à la base de données
require_once 'connectDB.php'; 

// Vérifie que les données nécessaires sont bien envoyées via POST
if (isset($_POST['id']) && isset($_POST['table'])) {
    $id = $_POST['id'];                 // Récupère l'ID de l'élément à supprimer
    $table = $_POST['table'];           // Récupère le nom de la table concernée

    // Vérifie que l'ID est bien numérique
    if (is_numeric($id)) {

        // Détermine la requête SQL selon la table
        if ($table === 'serre') {
            $query = "DELETE FROM serre WHERE IdSerre = :id";
        } elseif ($table === 'carte') {
            $query = "DELETE FROM carte WHERE DevEui = :id";
        } else {
            // Si la table demandée n'est pas prise en charge
            echo json_encode(['success' => false, 'error' => 'Table non supportée']);
            exit;
        }

        try {
            // Prépare et exécute la requête
            $stmt = $bdd->prepare($query);                       // Prépare la requête
            $stmt->bindParam(':id', $id);                        // Lie l'ID (pas besoin de PDO::PARAM_INT si DevEui est une chaîne)
            $stmt->execute();                                    // Exécute la requête

            // Vérifie si une ligne a été supprimée
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);           // Suppression réussie
            } else {
                echo json_encode(['success' => false, 'error' => 'Aucun élément trouvé avec cet ID']);
            }

        } catch (PDOException $e) {
            // Gestion d'erreur SQL
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }

    } else {
        echo json_encode(['success' => false, 'error' => 'ID invalide']); // L’ID n’est pas numérique
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']); // POST incomplet
}
?>