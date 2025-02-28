<?php
$login = 'root';
$pass = '';

try {
    $bdd = new PDO('mysql:host=localhost;dbname=projet;charset=utf8', $login, $pass);
} catch (Exception $e) {
    die('Erreur : connexion à la base de données impossible ' . $e->getMessage());
}
?>