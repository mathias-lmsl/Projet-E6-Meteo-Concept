<?php
$login = 'log';
$pass = 'Log';
// $login = 'root';
// $pass = '';

// try {
//     $bdd = new PDO('mysql:host=localhost;dbname=meteoconcept;charset=utf8', $login, $pass);
//     $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die('Erreur : connexion à la base de données impossible ' . $e->getMessage());
// }

try {
    $bdd = new PDO('mysql:host=192.168.1.205;dbname=meteoconcept;charset=utf8', $login, $pass);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur : connexion à la base de données impossible ' . $e->getMessage());
}
?>