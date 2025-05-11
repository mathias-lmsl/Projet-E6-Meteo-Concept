<?php
require "connectDB.php";

try {
    $stmt = $bdd->query("SELECT DevEui FROM carte");
    $cartes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $now = new DateTime();
    $nbModifies = 0;

    foreach ($cartes as $devEui) {
        // Vérifie l’état actuel de la carte
        $stmtCheck = $bdd->prepare("SELECT EtatComposant FROM carte WHERE DevEui = ?");
        $stmtCheck->execute([$devEui]);
        $etatActuel = $stmtCheck->fetchColumn();

        // Ne rien faire si la carte est en Veille
        if ($etatActuel === 'Veille') {
            continue;
        }

        // Capteurs liés à cette carte via la table possede
        $stmtCapteurs = $bdd->prepare("SELECT IdCapteur FROM possede WHERE DevEui = ?");
        $stmtCapteurs->execute([$devEui]);
        $capteurs = $stmtCapteurs->fetchAll(PDO::FETCH_COLUMN);

        $latest = null;

        foreach ($capteurs as $idCapteur) {
            $stmtLast = $bdd->prepare("SELECT Horodatage FROM mesure WHERE IdCapteur = ? ORDER BY Horodatage DESC LIMIT 1");
            $stmtLast->execute([$idCapteur]);
            $last = $stmtLast->fetch(PDO::FETCH_ASSOC);

            if ($last && !empty($last['Horodatage'])) {
                $lastDate = new DateTime($last['Horodatage']);
                if (!$latest || $lastDate > $latest) {
                    $latest = $lastDate;
                }
            }
        }

        $etat = "OK";

        if ($latest) {
            $diff = $now->getTimestamp() - $latest->getTimestamp();
            if ($diff > 1800) $etat = "HS"; // inactif depuis +30 min
        } else {
            $etat = "HS"; // aucun capteur mesurant
        }

        if ($etat !== $etatActuel) {
            $stmtUpdate = $bdd->prepare("UPDATE carte SET EtatComposant = ? WHERE DevEui = ?");
            $stmtUpdate->execute([$etat, $devEui]);
            $nbModifies++;
        }
    }

    echo "Cartes mises à jour : $nbModifies";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}