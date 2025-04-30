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

/* =========================== QRcode =========================== */
$IpApache = $_SERVER['SERVER_ADDR']; // Récupérer l'adresse IP du serveur Apache

$cheminPage = "http://".$IpApache."/projettest/php/qrcode.php"; // Chemin de la page qrcode.php

?>