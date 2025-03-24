<?php
require "../config/databasetech.php";


if(isset($_POST['Id'])) {
    $type=$_POST['Type'];
    $id = $_POST['Id'];
    if ($type == 'capteur') {
        $stmt = $bdd->prepare('SELECT Commentaire FROM capteur WHERE IdCapteur = :id');
    } 
    elseif ($type == 'carte'){
        $stmt = $bdd->prepare('SELECT Commentaire FROM carte WHERE DevEui= :id');
    }
        
    $stmt->execute(['id' =>  $id]);
    $donnees = $stmt->fetch();
    echo $donnees['Commentaire'];
    $stmt->closeCursor();
}
?>
