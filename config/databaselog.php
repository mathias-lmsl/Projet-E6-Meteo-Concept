<?php
// Paramètres de connexion à la base de données
require "../php/param.php";



try {
    $bdd = new PDO("mysql:host=$IpDatabase;dbname=$dbname;port=3306;charset=utf8", $userLog, $passwordLog, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
