<?php
// Paramètres de connexion
$host = '192.168.1.205'; 
$dbname = 'meteoconcept';
$username = 'technicien';
$password = 'Tech';

try {
    $bdd = new PDO("mysql:host=$host;dbname=$dbname;port=3306;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
