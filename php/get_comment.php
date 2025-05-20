<?php
require "../config/databasetech.php";

// On arrive sur cette page par une requete AJAX, elle sert à afficher le commentaire d'un capteur ou d'une carte selectionnée
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
