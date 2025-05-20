<?php
// On démarre la session et on modifie les paramètres de cookie pour la sécurité
session_set_cookie_params([
    'lifetime' => 0,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();
?>
