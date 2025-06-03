<?php
require "connectDB.php";
header('Content-Type: application/json');
$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table']) && isset($_POST['id'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];

    // Utiliser une copie pour le traitement, $_POST original pour référence si besoin
    $processedPost = $_POST;

    // Remplace les champs vides par NULL DANS processedPost
    foreach ($processedPost as $key => $value) {
        if ($value === '') {
            $processedPost[$key] = null;
        }
    }

    try {
        // Validation serveur pour les seuils de capteur (déjà présente)
        if ($table === 'capteur') {
            $valeurMinInput = $processedPost['ValeurMin'] ?? null;
            $valeurMaxInput = $processedPost['ValeurMax'] ?? null;
            $valeurMin = null;
            $valeurMax = null;
            $minIsSetAndNotEmpty = ($valeurMinInput !== null && $valeurMinInput !== ''); // chaîne vide déjà null
            $maxIsSetAndNotEmpty = ($valeurMaxInput !== null && $valeurMaxInput !== '');

            if ($minIsSetAndNotEmpty) {
                if (is_numeric($valeurMinInput)) {
                    $valeurMin = (float)$valeurMinInput;
                } else {
                    $response['error'] = '[PHP VALIDATION] Le seuil minimum doit être un nombre valide s\'il est renseigné.';
                    echo json_encode($response);
                    exit;
                }
            }
            if ($maxIsSetAndNotEmpty) {
                if (is_numeric($valeurMaxInput)) {
                    $valeurMax = (float)$valeurMaxInput;
                } else {
                    $response['error'] = '[PHP VALIDATION] Le seuil maximum doit être un nombre valide s\'il est renseigné.';
                    echo json_encode($response);
                    exit;
                }
            }
            if ($minIsSetAndNotEmpty && $maxIsSetAndNotEmpty) {
                if ($valeurMax < $valeurMin) {
                    $response['error'] = '[PHP VALIDATION] Erreur serveur : Le seuil maximum ne peut pas être inférieur au seuil minimum.';
                    echo json_encode($response);
                    exit;
                }
            }
        }

        // Mapping spécifique pour la table 'carte'
        if ($table === 'carte') {
            if (array_key_exists('NomCarte', $processedPost)) {
                $processedPost['Nom'] = $processedPost['NomCarte'];
            }
        }

        $stmtDescribe = $bdd->prepare("DESCRIBE " . $table);
        $stmtDescribe->execute();
        $columnsFromDB = $stmtDescribe->fetchAll(PDO::FETCH_COLUMN);

        $ignore = ['DateMiseEnService'];
        $primaryKey = null;

        if ($table === 'capteur') {
            $primaryKey = 'IdCapteur';
            $ignore[] = 'IdCapteur';
        } elseif ($table === 'carte') {
            $primaryKey = 'DevEui';
            $ignore[] = 'DevEui';
        } else {
            $response['error'] = '[PHP] Table non supportée pour la mise à jour: ' . $table;
            echo json_encode($response);
            exit;
        }

        $setClause = [];
        foreach ($columnsFromDB as $colDB) {
            if (!in_array($colDB, $ignore) && array_key_exists($colDB, $processedPost)) {
                $setClause[] = "`$colDB` = :$colDB";
            }
        }

        if (empty($setClause)) {
            $response['error'] = "[PHP] Aucun champ valide à mettre à jour ou tous les champs sont ignorés.";
            error_log("Aucun champ à MAJ. Processed POST: " . print_r($processedPost, true) . " DB Columns: " . print_r($columnsFromDB, true) . " Ignored: " . print_r($ignore, true));
            echo json_encode($response);
            exit;
        }

        $sql = "UPDATE `$table` SET " . implode(", ", $setClause) . " WHERE `$primaryKey` = :id";
        error_log("SQL Update: " . $sql); // Log pour débogage
        $stmtUpdate = $bdd->prepare($sql);

        foreach ($columnsFromDB as $colDB) {
            if (!in_array($colDB, $ignore) && array_key_exists($colDB, $processedPost)) {
                $stmtUpdate->bindValue(':' . $colDB, $processedPost[$colDB], $processedPost[$colDB] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            }
        }

        $stmtUpdate->bindValue(':id', $id);
        $stmtUpdate->execute();

        $response['success'] = true;

    } catch (PDOException $e) {
        $response['error'] = "[PHP EXCEPTION] " . $e->getMessage();
        error_log("[PHP EXCEPTION] " . $e->getMessage() . " SQL: " . ($sql ?? "Non défini"));
    }

} else {
    $response['error'] = "[PHP] Requête invalide ou paramètres manquants.";
}

echo json_encode($response);
?>