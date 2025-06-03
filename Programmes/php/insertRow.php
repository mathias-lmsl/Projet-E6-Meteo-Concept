<?php
require "connectDB.php"; //

$response = ['success' => false]; //

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table'])) { //
    $table = $_POST['table']; //

    $processedPost = [];
    foreach ($_POST as $key => $value) {
        if ($value === '') {
            $processedPost[$key] = null; //
        } else {
            $processedPost[$key] = $value;
        }
    }
    
    // Retirer 'table' de processedPost car ce n'est pas une colonne de données
    unset($processedPost['table']);


    try {
        $stmtDescribe = $bdd->prepare("DESCRIBE " . $table); //
        $stmtDescribe->execute(); //
        $dbColumns = $stmtDescribe->fetchAll(PDO::FETCH_COLUMN); //

        $autoIncrementPK = null;
        if ($table === 'capteur') $autoIncrementPK = 'IdCapteur';
        else if ($table === 'chapelle') $autoIncrementPK = 'IdChapelle';
        else if ($table === 'serre') $autoIncrementPK = 'IdSerre'; // Si vous ajoutez l'ajout de serres
        else if ($table === 'utilisateur') $autoIncrementPK = 'IdUtilisateur'; // Si vous ajoutez l'ajout d'utilisateurs
        // Pour 'carte', DevEui est la PK mais elle est fournie, pas auto-incrémentée.

        $insertCols = [];
        $insertValsPlaceholders = [];
        $bindParams = [];

        foreach ($dbColumns as $col) {
            if ($col === $autoIncrementPK) continue; 
            if ($col === 'DateMiseEnService') continue; 

            // Vérifier si la colonne existe dans les données POSTées (maintenant dans $processedPost)
            if (array_key_exists($col, $processedPost)) {
                $insertCols[] = "`$col`"; // Noms de colonnes avec backticks
                $insertValsPlaceholders[] = ':' . $col;
                $bindParams[$col] = $processedPost[$col];
            }
        }
        
        // Ajouter DateMiseEnService si la table la contient et qu'elle n'est pas déjà fournie
        if (in_array('DateMiseEnService', $dbColumns) && !in_array('`DateMiseEnService`', $insertCols)) {
            $insertCols[] = '`DateMiseEnService`';
            $insertValsPlaceholders[] = 'NOW()';
        }


        if (empty($insertCols)) {
            $response['error'] = 'Aucune donnée valide à insérer pour la table: ' . $table;
            echo json_encode($response);
            exit;
        }
        
        $sql = "INSERT INTO `$table` (" . implode(',', $insertCols) . ") VALUES (" . implode(',', $insertValsPlaceholders) . ")";
        error_log("Insert SQL for $table: " . $sql); // Pour débogage
        error_log("Bind params for $table: " . print_r($bindParams, true));


        $stmtInsert = $bdd->prepare($sql);

        foreach ($bindParams as $paramName => $paramValue) {
            $stmtInsert->bindValue(':' . $paramName, $paramValue, $paramValue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        }

        $stmtInsert->execute(); //
        $response['success'] = true; //

    } catch (PDOException $e) {
        $response['error'] = $e->getMessage() . " (SQL: " . ($sql ?? "non défini") . ")"; //
        error_log("PDOException in insertRow.php for table $table: " . $e->getMessage() . " SQL: " . ($sql ?? "Non défini"));

    }

} else {
    $response['error'] = 'Requête invalide'; //
}

echo json_encode($response); //
?>