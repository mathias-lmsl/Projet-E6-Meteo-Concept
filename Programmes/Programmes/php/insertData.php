<?php
require "connectDB.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'];

    try {
        if ($table === 'capteur') {
            // On vérifie que toutes les données nécessaires existent
            $requiredFields = ['NomCapteur', 'GrandeurCapt', 'Unite', 'ValeurMin', 'ValeurMax', 'EtatComposant', 'DevEui'];
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field])) {
                    echo json_encode([
                        'success' => false,
                        'error' => "Le champ '$field' est manquant !"
                    ]);
                    exit;
                }
            }

            // Prépare la requête d'insertion du capteur
            $stmt = $bdd->prepare("
                INSERT INTO capteur 
                    (NomCapteur, GrandeurCapt, Unite, ValeurMin, ValeurMax, EtatComposant, DateMiseEnService, DevEui)
                VALUES 
                    (:NomCapteur, :GrandeurCapt, :Unite, :ValeurMin, :ValeurMax, :EtatComposant, NOW(), :DevEui)
            ");

            $stmt->execute([
                ':NomCapteur'     => $_POST['NomCapteur'],
                ':GrandeurCapt'   => $_POST['GrandeurCapt'],
                ':Unite'          => $_POST['Unite'],
                ':ValeurMin'      => $_POST['ValeurMin'],
                ':ValeurMax'      => $_POST['ValeurMax'],
                ':EtatComposant'  => $_POST['EtatComposant'],
                ':DevEui'        => $_POST['DevEui']
            ]);

        } elseif ($table === 'carte') {
            // Vérifie les champs pour l'ajout d'une carte
            $requiredFields = ['NomCarte', 'EtatComposant'];
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field])) {
                    echo json_encode([
                        'success' => false,
                        'error' => "Le champ '$field' est manquant !"
                    ]);
                    exit;
                }
            }

            $stmt = $bdd->prepare("
                INSERT INTO carte 
                    (NomCarte, EtatComposant, DateMiseEnService)
                VALUES 
                    (:NomCarte, :EtatComposant, NOW())
            ");

            $stmt->execute([
                ':NomCarte'       => $_POST['NomCarte'],
                ':EtatComposant'  => $_POST['EtatComposant']
            ]);

        } else {
            echo json_encode([
                'success' => false,
                'error' => "Table non supportée"
            ]);
            exit;
        }

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }

} else {
    echo json_encode([
        'success' => false,
        'error' => "Requête invalide"
    ]);
}
?>