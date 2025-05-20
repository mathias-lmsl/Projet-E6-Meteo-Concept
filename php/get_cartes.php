<?php
require "../config/databaseadmin.php";

// On arrive sur cette page par une requete AJAX, elle sert à afficher les cartes d'une chapelle selectionnée
if (!empty($_POST['chapelle_id'])) {
    $chapelleId = $_POST['chapelle_id'];
    
    $stmt = $bdd->prepare('SELECT DevEui, Nom FROM carte WHERE IdChapelle = ?');
    $stmt->execute([$chapelleId]);
    
    echo '<option value="">-- Choisissez une carte --</option>';
    while ($donnees = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' . htmlspecialchars($donnees['DevEui']) . '">' . htmlspecialchars($donnees['Nom']) . '</option>';
    }
    
    $stmt->closeCursor();
} else {
    echo '<option value="">-- Erreur : Aucune chapelle sélectionnée --</option>';
}
?>