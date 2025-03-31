<?php
require "../config/session.php";
require "../config/databaselog.php";
$_SESSION['redirect_to']==='qrcode.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = isset($_POST['login']) ? htmlspecialchars(trim($_POST['login'])) : '';
    $mdp = isset($_POST['mdp']) ? trim($_POST['mdp']) : '';

    if (!empty($login) && !empty($mdp)) {
        $stmt = $bdd->prepare('SELECT Login, Mdp, Fonction FROM utilisateur WHERE Login = :login');
        $stmt->execute(['login' => $login]);
        $donnees = $stmt->fetch();
            if ($stmt->rowCount() > 1) {
                // Il y a plus de 1 résultats retournés, ce qui est inattendu
                header('Location: error.php');   
            }
            
            if ($_SESSION['redirect_to']==='creationqrcode.php') { //Pour l'accès a la page de génération de qrcode
                if ($donnees && password_verify($mdp, $donnees['Mdp']) && $donnees['Fonction']=='Administrateur') {

                    $_SESSION['user'] = $login;
                    header('Location: '.$_SESSION['redirect_to'].'');     
                    exit;
                }else{
                   header('Location: index.php?error=1'); 
                   exit;
                } 

            }
            if ($_SESSION['redirect_to']==='qrcode.php') { //Pour l'accès a la page de maintenance
                if ($donnees && password_verify($mdp, $donnees['Mdp']) && ($donnees['Fonction']=='Technicien' || $donnees['Fonction']=='Administrateur')) {

                    $_SESSION['user'] = $login;
                    header('Location: '.$_SESSION['redirect_to'].'');     
                    exit;
                }else {
                    header('Location: index.php?error=1');
                    exit;
                }
                
            }           
            header('Location: error.php');
            exit;
    }
}
?>
