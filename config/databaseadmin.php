<?php
// Paramètres de connexion à la base de données
require "../php/param.php";




try { // Connexion à la base de données avec les identifiants de l'utilisateur admin
    $bdd = new PDO("mysql:host=$IpDatabase;dbname=$dbname;port=3306;charset=utf8", $userAdmin, $passwordAdmin);
} catch (PDOException $e) { // Si la connexion échoue, afficher un message d'erreur
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
