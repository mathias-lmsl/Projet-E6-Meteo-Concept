<?php
require "../config/session.php";

session_unset();
session_destroy();
if (!empty($_GET["id"])){
    header('Location: index.php?id='.$_GET["id"].'&page='.$_GET["page"].'');
    exit;
}
header('Location: index.php?page='.$_GET["page"].'');
exit;
?>
