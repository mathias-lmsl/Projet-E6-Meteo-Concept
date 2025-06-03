<?php
require_once 'connectDB.php'; //

if (isset($_POST['id']) && isset($_POST['table'])) {
    $id = $_POST['id'];
    $table = $_POST['table'];

    $primaryKeys = [
        'serre'       => 'IdSerre',
        'chapelle'    => 'IdChapelle',
        'carte'       => 'DevEui',
        'capteur'     => 'IdCapteur',
        'utilisateur' => 'IdUtilisateur'
    ];

    if (!array_key_exists($table, $primaryKeys)) {
        echo json_encode(['success' => false, 'error' => 'Table non supportée']);
        exit;
    }

    $primaryKey = $primaryKeys[$table];

    try {
        $bdd->beginTransaction();

        if ($table === 'capteur') {
            // $id ici est IdCapteur
            $stmtMesure = $bdd->prepare("DELETE FROM mesure WHERE IdCapteur = :idCapteur"); //
            $stmtMesure->bindParam(':idCapteur', $id, PDO::PARAM_INT);
            $stmtMesure->execute();

            $stmtPossede = $bdd->prepare("DELETE FROM possede WHERE IdCapteur = :idCapteur"); //
            $stmtPossede->bindParam(':idCapteur', $id, PDO::PARAM_INT);
            $stmtPossede->execute();

        } elseif ($table === 'carte') {
            // $id ici est le DevEui de la carte à supprimer

            // 1. Identifier les capteurs liés à cette carte via la table 'possede'
            $stmtFindCapteursInPossede = $bdd->prepare("SELECT IdCapteur FROM possede WHERE DevEui = :carteDevEui"); //
            $stmtFindCapteursInPossede->bindParam(':carteDevEui', $id);
            $stmtFindCapteursInPossede->execute();
            $capteursLinkedViaPossede = $stmtFindCapteursInPossede->fetchAll(PDO::FETCH_COLUMN);

            foreach ($capteursLinkedViaPossede as $capteurIdToDelete) {
                // a. Supprimer les mesures de ce capteur
                $stmtMesure = $bdd->prepare("DELETE FROM mesure WHERE IdCapteur = :capteurId"); //
                $stmtMesure->bindParam(':capteurId', $capteurIdToDelete, PDO::PARAM_INT);
                $stmtMesure->execute();
                
                // b. Supprimer le capteur lui-même
                $stmtDelCapteur = $bdd->prepare("DELETE FROM capteur WHERE IdCapteur = :capteurId"); //
                $stmtDelCapteur->bindParam(':capteurId', $capteurIdToDelete, PDO::PARAM_INT);
                $stmtDelCapteur->execute();
            }

            // 2. Supprimer TOUTES les entrées de la table 'possede' qui font référence au DevEui de cette carte.
            $stmtPossedeCarteDevEui = $bdd->prepare("DELETE FROM possede WHERE DevEui = :carteDevEui"); //
            $stmtPossedeCarteDevEui->bindParam(':carteDevEui', $id);
            $stmtPossedeCarteDevEui->execute();
            
            // 3. Supprimer les entrées de 'intervient' pour cette carte
            $stmtIntervient = $bdd->prepare("DELETE FROM intervient WHERE DevEui = :carteDevEui"); //
            $stmtIntervient->bindParam(':carteDevEui', $id);
            $stmtIntervient->execute();

        } elseif ($table === 'chapelle') {
            // $id ici est IdChapelle
            
            // 1. Trouver toutes les cartes associées à cette chapelle
            $stmtFindCartes = $bdd->prepare("SELECT DevEui FROM carte WHERE IdChapelle = :idChapelle");
            $stmtFindCartes->bindParam(':idChapelle', $id, PDO::PARAM_INT);
            $stmtFindCartes->execute();
            $cartesASupprimer = $stmtFindCartes->fetchAll(PDO::FETCH_COLUMN);

            foreach ($cartesASupprimer as $carteDevEui) {
                // A. Trouver les capteurs liés à cette carte via 'possede'
                $stmtFindCapteursDeCarte = $bdd->prepare("SELECT IdCapteur FROM possede WHERE DevEui = :devEuiCarte");
                $stmtFindCapteursDeCarte->bindParam(':devEuiCarte', $carteDevEui);
                $stmtFindCapteursDeCarte->execute();
                $capteursDeCetteCarte = $stmtFindCapteursDeCarte->fetchAll(PDO::FETCH_COLUMN);

                foreach ($capteursDeCetteCarte as $capteurIdASupprimer) {
                    // A.i. Supprimer les mesures du capteur
                    $stmtMesure = $bdd->prepare("DELETE FROM mesure WHERE IdCapteur = :idCapteur");
                    $stmtMesure->bindParam(':idCapteur', $capteurIdASupprimer, PDO::PARAM_INT);
                    $stmtMesure->execute();
                    
                    // A.ii. Supprimer le capteur lui-même (la table 'capteur' n'a pas de DevEui direct)
                    $stmtDelCapteur = $bdd->prepare("DELETE FROM capteur WHERE IdCapteur = :idCapteur");
                    $stmtDelCapteur->bindParam(':idCapteur', $capteurIdASupprimer, PDO::PARAM_INT);
                    $stmtDelCapteur->execute();
                }

                // B. Supprimer toutes les entrées de 'possede' pour cette carte (par DevEui)
                $stmtPossedeCarte = $bdd->prepare("DELETE FROM possede WHERE DevEui = :devEuiCarte");
                $stmtPossedeCarte->bindParam(':devEuiCarte', $carteDevEui);
                $stmtPossedeCarte->execute();

                // C. Supprimer les entrées de 'intervient' pour cette carte
                $stmtIntervientCarte = $bdd->prepare("DELETE FROM intervient WHERE DevEui = :devEuiCarte");
                $stmtIntervientCarte->bindParam(':devEuiCarte', $carteDevEui);
                $stmtIntervientCarte->execute();
                
                // D. Supprimer la carte elle-même
                $stmtDelCarte = $bdd->prepare("DELETE FROM carte WHERE DevEui = :devEuiCarte");
                $stmtDelCarte->bindParam(':devEuiCarte', $carteDevEui);
                $stmtDelCarte->execute();
            }
        }
        
        // Requête de suppression principale (pour capteur, carte, chapelle, etc.)
        $stmt = $bdd->prepare("DELETE FROM `$table` WHERE `$primaryKey` = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $bdd->commit();
            echo json_encode(['success' => true]);
        } else {
            $bdd->rollBack(); 
            echo json_encode(['success' => false, 'error' => 'Aucun élément principal trouvé avec cet ID pour la suppression ou déjà supprimé.']);
        }

    } catch (PDOException $e) {
        if ($bdd->inTransaction()) {
            $bdd->rollBack();
        }
        error_log("PDOException in deleteRow.php for table $table, id $id: " . $e->getMessage());
        if (isset($stmt) && $stmt instanceof PDOStatement) {
             error_log("Failing query (deleteRow.php): " . $stmt->queryString);
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage() . " (Erreur lors de la suppression de la table ".$table.")"]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
}
?>