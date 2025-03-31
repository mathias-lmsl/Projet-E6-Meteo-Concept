<?php
require "../config/session.php";

session_unset();

if ($_GET['page'] == 1) { //Si l'utilisateur viens d'une page de création de qr code
    $_SESSION['redirect_to'] = 'creationqrcode.php';
    header('Location: index.php');
    exit;
}
elseif ($_GET['page'] == 2) { //Si l'utilisateur viens d'une page de maintenance
    $_SESSION['id_consultable'] = $_GET["id"];
    $_SESSION['redirect_to'] = 'qrcode.php';
    header('Location: index.php');
    exit;
}
?>
