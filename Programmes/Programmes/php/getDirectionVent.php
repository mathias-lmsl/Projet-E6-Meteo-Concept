<?php
require_once "directionVent.php";

if (isset($_GET['angle'])) {
    $angle = floatval($_GET['angle']);
    echo json_encode(['direction' => directionVent($angle)]);
} else {
    echo json_encode(['error' => 'Paramètre angle manquant']);
}
?>
