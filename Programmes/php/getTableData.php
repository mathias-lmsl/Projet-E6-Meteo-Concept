<?php
// Connexion à la base de données
require "connectDB.php"; //

if (isset($_GET['table'])) {
    $tableName = $_GET['table']; //

    try {
        $columns = []; //
        $rows = []; //
        $idField = null; //

        switch ($tableName) {
            case 'carte':
                $stmt = $bdd->prepare("
                    SELECT carte.DevEui, carte.Nom AS NomCarte, carte.DateMiseEnService, carte.AppKey, carte.AppEUI, carte.Marque, 
                           carte.Reference, carte.NumSerie, carte.Commentaire, chapelle.Nom AS NomChapelle, carte.EtatComposant,
                           carte.IdChapelle 
                    FROM carte
                    LEFT JOIN chapelle ON carte.IdChapelle = chapelle.IdChapelle
                "); //
                $stmt->execute(); //
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); //
                $idField = 'DevEui'; //
                break;

            case 'capteur':
                $stmt = $bdd->prepare("SELECT * FROM capteur"); //
                $stmt->execute(); //
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); //
                $idField = 'IdCapteur'; //
                break;

            case 'chapelle':
                // MODIFIÉ : Sélectionner toutes les colonnes y compris IdSerre pour rowData
                // et joindre avec serre pour afficher NomSerre si nécessaire (pas demandé pour l'instant dans la table, mais utile pour rowData)
                $stmt = $bdd->prepare("
                    SELECT chapelle.*, serre.Nom AS NomSerre 
                    FROM chapelle 
                    LEFT JOIN serre ON chapelle.IdSerre = serre.IdSerre
                "); //
                $stmt->execute(); //
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); //
                $idField = 'IdChapelle'; //
                break;

            case 'serre':
                $stmt = $bdd->prepare("SELECT * FROM serre"); //
                $stmt->execute(); //
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); //
                $idField = 'IdSerre'; //
                break;

            case 'utilisateur':
                $stmt = $bdd->prepare("SELECT * FROM utilisateur"); //
                $stmt->execute(); //
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); //
                $idField = 'IdUtilisateur'; //
                break;

            default:
                $stmt = $bdd->prepare("SELECT * FROM " . $tableName); //
                $stmt->execute(); //
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC); //
                $idField = null; //
        }

        if (!empty($rows)) {
            $columns = array_keys($rows[0]); //
        }

        // MODIFIÉ : Ajoute un bouton d'action "Modifier" si la table est capteur, carte OU CHAPELLE
        if (in_array($tableName, ['capteur', 'carte', 'chapelle']) && $idField !== null) { // MODIFIÉ ICI
            foreach ($rows as &$row) {
                // Ce contenu sera remplacé/utilisé par Parametrage.js pour créer les images cliquables
                $row['Actions'] = '<span class="action-placeholder" data-id="' . htmlspecialchars($row[$idField]) . '">Actions</span>';
            }
        }

        $finalColumns = []; //
        if (isset($rows[0]['Actions'])) {
            $finalColumns[] = 'Actions'; //
        }

        foreach ($columns as $col) {
            // Exclure IdChapelle pour la table 'carte' de l'affichage direct en colonne, car on affiche NomChapelle.
            // Exclure IdSerre pour la table 'chapelle' de l'affichage direct, car on affiche NomSerre (si jointure activée pour affichage).
            // Ou simplement, ne pas afficher les clés étrangères brutes si leur équivalent "Nom" est déjà là.
            $isForeignKeyForDisplayedName = ($tableName === 'carte' && $col === 'IdChapelle') || ($tableName === 'chapelle' && $col === 'IdSerre');
            
            if ($col !== $idField && !$isForeignKeyForDisplayedName) {
                 // Ne pas ajouter NomSerre si on n'a pas de jointure ou si on ne veut pas l'afficher
                if ($tableName === 'chapelle' && $col === 'NomSerre' && !isset($rows[0]['NomSerre'])) {
                    continue;
                }
                $finalColumns[] = $col; //
            }
        }
        echo json_encode(['columns' => $finalColumns, 'rows' => $rows]); //

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]); //
    }
}
?>