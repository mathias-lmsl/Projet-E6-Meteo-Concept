<?php
require "connectDB.php";

if (isset($_GET['capteur_id'])) {
    $capteurId = $_GET['capteur_id'];
    $startDate = $_GET['startDate'] ?? null;
    $startTime = $_GET['startTime'] ?? null;
    $endDate   = $_GET['endDate'] ?? null;
    $endTime   = $_GET['endTime'] ?? null;

    try {
        // 1. Requête pour récupérer les mesures (optionnellement avec plage horaire)
        $sql = "SELECT Horodatage, Valeur FROM mesure WHERE IdCapteur = ?";
        $params = [$capteurId];

        if ($startDate && $startTime && $endDate && $endTime) {
            $startDateTime = $startDate . ' ' . $startTime;
            $endDateTime   = $endDate . ' ' . $endTime;
            $sql .= " AND Horodatage BETWEEN ? AND ?";
            $params[] = $startDateTime;
            $params[] = $endDateTime;
        }

        $sql .= " ORDER BY Horodatage ASC";

        $stmt = $bdd->prepare($sql);
        $stmt->execute($params);
        $mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Dernière mesure disponible
        $stmtLast = $bdd->prepare("SELECT Horodatage FROM mesure WHERE IdCapteur = ? ORDER BY Horodatage DESC LIMIT 1");
        $stmtLast->execute([$capteurId]);
        $last = $stmtLast->fetch(PDO::FETCH_ASSOC);

        $etat = "OK"; // Valeur par défaut

        if ($last && !empty($last['Horodatage'])) {
            $lastDate = new DateTime($last['Horodatage']);
            $now = new DateTime();
            $diff = $now->getTimestamp() - $lastDate->getTimestamp();

            if ($diff > 1800) {
                $etat = "HS";
            }
        } else {
            // Aucune mesure du tout
            $etat = "HS";
        }

        // 3. Met à jour la table capteur UNIQUEMENT si nécessaire
        $stmtCheck = $bdd->prepare("SELECT EtatComposant FROM capteur WHERE IdCapteur = ?");
        $stmtCheck->execute([$capteurId]);
        $etatActuel = $stmtCheck->fetchColumn();

        if ($etatActuel !== $etat) {
            $stmtUpdate = $bdd->prepare("UPDATE capteur SET EtatComposant = ? WHERE IdCapteur = ?");
            $stmtUpdate->execute([$etat, $capteurId]);
        }

        // 4. Réponse JSON
        echo json_encode([
            'etat' => $etat,
            'mesures' => $mesures
        ]);

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>