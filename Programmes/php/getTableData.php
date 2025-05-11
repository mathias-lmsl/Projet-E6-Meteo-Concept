<?php
require "connectDB.php";

if (isset($_GET['table'])) {
    $tableName = $_GET['table'];

    try {
        $columns = [];
        $rows = [];
        $idField = null;

        switch ($tableName) {
            case 'carte':
                $stmt = $bdd->prepare("
                    SELECT carte.DevEui, carte.Nom AS NomCarte, carte.DateMiseEnService, carte.AppKey, carte.AppEUI, carte.Marque, 
                           carte.Reference, carte.NumSerie, carte.Commentaire, chapelle.Nom AS NomChapelle, carte.EtatComposant
                    FROM carte
                    LEFT JOIN chapelle ON carte.IdChapelle = chapelle.IdChapelle
                ");
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $idField = 'DevEui';
                break;

            case 'capteur':
                $stmt = $bdd->prepare("SELECT * FROM capteur");
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $idField = 'IdCapteur';
                break;

            case 'chapelle':
                $stmt = $bdd->prepare("SELECT * FROM chapelle");
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $idField = 'IdChapelle';
                break;

            case 'serre':
                $stmt = $bdd->prepare("SELECT * FROM serre");
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $idField = 'IdSerre';
                break;

            case 'utilisateur':
                $stmt = $bdd->prepare("SELECT * FROM utilisateur");
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $idField = 'IdUtilisateur';
                break;

            default:
                $stmt = $bdd->prepare("SELECT * FROM " . $tableName);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $idField = null;
        }

        // Colonnes disponibles (si des données existent)
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
        }

        // Ajouter la colonne "Actions" si nécessaire
        if (in_array($tableName, ['capteur', 'carte']) && $idField !== null) {
            foreach ($rows as &$row) {
                $row['Actions'] = '<button class="modifyImage" data-id="' . $row[$idField] . '">Modifier</button>';
            }
        }

        // Construire la liste finale des colonnes
        $finalColumns = [];

        if (isset($rows[0]['Actions'])) {
            $finalColumns[] = 'Actions';
        }

        foreach ($columns as $col) {
            if ($col !== $idField && $col !== 'IdChapelle') {
                $finalColumns[] = $col;
            }
        }

        echo json_encode(['columns' => $finalColumns, 'rows' => $rows]);

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>