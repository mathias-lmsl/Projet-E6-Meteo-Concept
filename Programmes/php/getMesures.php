<?php
require "connectDB.php";

if (isset($_GET['capteur_id'])) {
    $capteurId = $_GET['capteur_id'];
    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
    $startTime = isset($_GET['startTime']) ? $_GET['startTime'] : null;
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;
    $endTime = isset($_GET['endTime']) ? $_GET['endTime'] : null;

    try {
        $sql = "SELECT Horodatage, Valeur FROM mesure WHERE IdCapteur = ?";
        $params = [$capteurId];

        if ($startDate && $startTime && $endDate && $endTime) {
            $startDateTime = $startDate . ' ' . $startTime;
            $endDateTime = $endDate . ' ' . $endTime;
            $sql .= " AND Horodatage BETWEEN ? AND ?";
            $params[] = $startDateTime;
            $params[] = $endDateTime;
        }

        $sql .= " ORDER BY Horodatage ASC";

        $stmt = $bdd->prepare($sql);
        $stmt->execute($params);
        $mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($mesures);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>