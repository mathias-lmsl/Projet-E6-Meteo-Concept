<?php
// Fichier de paramètres a modifier

/* =========================== DataBase =========================== */
//Info DB
$IpDatabase = '192.168.1.205'; 
$dbname = 'meteoconcept';

//Log DB technicien
$userTechnicien = 'technicien';
$passwordTechnicien = 'Tech';

//Log DB pour la connection de l'utilisateur
$userLog = 'log';
$passwordLog = 'Log';

//Log DB admin
$userAdmin = 'administrateur';
$passwordAdmin = 'Admin';

$options = [
    PDO::MYSQL_ATTR_SSL_KEY  => 'etc/ssl/mariadb/client-key.pem',
    PDO::MYSQL_ATTR_SSL_CERT => 'etc/ssl/mariadb/client-cert.pem',
    PDO::MYSQL_ATTR_SSL_CA   => 'etc/ssl/mariadb/ca-cert.pem',
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    PDO::ATTR_ERRMODE        =>  PDO::ERRMODE_EXCEPTION,
];

/* =========================== QRcode =========================== */
$IpApache = '192.168.1.57'; // Adresse IP du serveur Apache

$cheminPage = "https://".$IpApache."/php/qrcode.php"; // Chemin de la page qrcode.php

?>