<?php
require "connectDB.php";

try {
    // 1. Récupère tous les capteurs
    $stmt = $bdd->query("SELECT IdCapteur FROM capteur");
    $capteurs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $now = new DateTime();
    $nbModifies = 0;

    foreach ($capteurs as $id) {
        // 2. État actuel du capteur
        $stmtCheck = $bdd->prepare("SELECT EtatComposant FROM capteur WHERE IdCapteur = ?");
        $stmtCheck->execute([$id]);
        $etatActuel = $stmtCheck->fetchColumn();

        // 3. Si capteur en Veille, on ne touche à rien
        if ($etatActuel === 'Veille') {
            continue;
        }

        // 4. Dernière mesure
        $stmtLast = $bdd->prepare("SELECT Horodatage FROM mesure WHERE IdCapteur = ? ORDER BY Horodatage DESC LIMIT 1");
        $stmtLast->execute([$id]);
        $last = $stmtLast->fetch(PDO::FETCH_ASSOC);

        $etat = "OK";

        if ($last && !empty($last['Horodatage'])) {
            $lastDate = new DateTime($last['Horodatage']);
            $diff = $now->getTimestamp() - $lastDate->getTimestamp();

            if ($diff > 1800) {
                $etat = "HS";
            }
        } else {
            $etat = "HS"; // Aucun relevé = HS
        }

        // 5. Mise à jour uniquement si nécessaire
        if ($etat !== $etatActuel) {
            $stmtUpdate = $bdd->prepare("UPDATE capteur SET EtatComposant = ? WHERE IdCapteur = ?");
            $stmtUpdate->execute([$etat, $id]);
            $nbModifies++;
        }
    }

    echo "Mise à jour terminée. Capteurs modifiés : $nbModifies";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>