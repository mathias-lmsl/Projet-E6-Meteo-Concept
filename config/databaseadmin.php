<?php
// Paramètres de connexion à la base de données
require "../php/param.php";




try {
    $bdd = new PDO("mysql:host=$IpDatabase;dbname=$dbname;port=3306;charset=utf8", $userAdmin, $passwordAdmin,$options);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
