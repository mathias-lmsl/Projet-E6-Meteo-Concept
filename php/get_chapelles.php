<?php
require "../config/databaseadmin.php";

// On arrive sur cette page par une requete AJAX, elle sert à afficher les chapelles d'une serre selectionnée
if(isset($_POST['serre_id'])) {
    $serre_id = $_POST['serre_id'];

    $stmt = $bdd->prepare('SELECT IdChapelle,chapelle.Nom FROM chapelle,serre WHERE chapelle.IdSerre = serre.IdSerre AND chapelle.IdSerre=:id');
    $stmt->execute(['id' =>  $serre_id]);

    echo '<option value="">-- Choisissez une chapelle --</option>';
    while ($donnees = $stmt->fetch()) {
        echo '<option value="'.htmlspecialchars($donnees['IdChapelle']).'">'.htmlspecialchars($donnees['Nom']).'</option>';
    }
    $stmt->closeCursor();
}
?>
