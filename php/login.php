<?php
require "../config/session.php";
require "../config/databaselog.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = isset($_POST['login']) ? htmlspecialchars(trim($_POST['login'])) : '';
    $mdp = isset($_POST['mdp']) ? trim($_POST['mdp']) : '';

    if (!empty($login) && !empty($mdp)) {
        $stmt = $bdd->prepare('SELECT Login, Mdp, Fonction FROM utilisateur WHERE Login = :login');
        $stmt->execute(['login' => $login]);
        $donnees = $stmt->fetch();
            
            if ($_GET["page"]==1) { //Pour l'accès a la page de génération de qrcode
                if ($donnees && password_verify($mdp, $donnees['Mdp']) && $donnees['Fonction']=='Administrateur') {
                    session_regenerate_id(true);
                    $_SESSION['user'] = $login;
                    header('Location: creationqrcode.php');     
                    exit;
                }else{
                   header('Location: index.php?page='.$_GET["page"].'&error=1'); 
                   exit;
                } 
                
            }
            if ($_GET["page"]==2) { //Pour l'accès a la page de maintenance
                if ($donnees && password_verify($mdp, $donnees['Mdp']) && ($donnees['Fonction']=='Technicien' || $donnees['Fonction']=='Administrateur')) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = $login;
                    $_SESSION['id_consultable'] = $_GET["id"];
                    header('Location: qrcode.php?id='.$_GET["id"].'');     
                    exit;
                }else {
                    header('Location: index.php?id='.$_GET["id"].'&page='.$_GET["page"].'&error=1');
                    exit;
                }
                
            }           
            header('Location: error.php');
            exit;
                 
    }
}
?>
