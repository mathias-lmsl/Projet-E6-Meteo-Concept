<?php
require "../config/databaseadmin.php";

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
