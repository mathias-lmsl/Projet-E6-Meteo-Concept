<?php
require "../config/session.php";

// On réinitialise la session pour déconnecter l'utilisateur

session_unset();


if ($_GET['page'] == 1) { //Si l'utilisateur viens d'une page de création de qr code
    // On retourne sur la page de connexion en indiquant qu'on vient de la page de création de qr code
    $_SESSION['redirect_to'] = 'creationqrcode.php';
    header('Location: index.php');
    exit;
}
elseif ($_GET['page'] == 2) { //Si l'utilisateur viens d'une page de maintenance
    // On retourne sur la page de connexion en indiquant qu'on vient de la page de supervision/maintenance
    $_SESSION['id_consultable'] = $_GET['id'];
    $_SESSION['redirect_to'] = 'qrcode.php';
    header('Location: index.php');
    exit;
}
?>
