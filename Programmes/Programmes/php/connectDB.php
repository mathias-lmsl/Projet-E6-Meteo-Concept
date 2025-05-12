<?php

// $login = 'administrateur';
// $pass = 'Admin';

// try {
//     $bdd = new PDO('mysql:host=192.168.1.205;dbname=meteoconcept;charset=utf8', $login, $pass);
//     $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die('Erreur : connexion à la base de données impossible ' . $e->getMessage());
// }

$login = 'root';
$pass = '';

try {
    $bdd = new PDO('mysql:host=localhost;dbname=meteoconcept;charset=utf8', $login, $pass);
} catch (Exception $e) {
    die('Erreur : connexion à la base de données impossible ' . $e->getMessage());
}

// $login = 'administrateur';
// $pass = 'Admin';

// try {
//     $bdd = new PDO('mysql:host=192.168.1.205;dbname=meteoconcept;charset=utf8', $login, $pass, array(
//         PDO::MYSQL_ATTR_SSL_KEY    => '/etc/ssl/mariadb/client-key.pem',
//         PDO::MYSQL_ATTR_SSL_CERT   => '/etc/ssl/mariadb/client-cert.pem',
//         PDO::MYSQL_ATTR_SSL_CA     => '/etc/ssl/mariadb/ca-cert.pem',
//         PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION
//     ));
// } catch (PDOException $e) {
//     die('Erreur : connexion à la base de données impossible ' . $e->getMessage());
// }
?>