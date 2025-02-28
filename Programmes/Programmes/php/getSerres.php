<?php
require "connectDB.php";

try {
    $stmtSerre = $bdd->query("SELECT IdSerre, Nom FROM serre");
    $serres = $stmtSerre->fetchAll(PDO::FETCH_ASSOC);

    foreach ($serres as $serre) {
        echo "<option value='" . $serre["IdSerre"] . "'>" . $serre["Nom"] . "</option>";
    }
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des serres : " . $e->getMessage());
    // Ne pas afficher l'erreur directement sur la page en production
}
?>