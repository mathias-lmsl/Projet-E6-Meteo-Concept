<?php
require "../config/session.php";
require "../config/databaselog.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = isset($_POST['login']) ? htmlspecialchars(trim($_POST['login'])) : '';
    $mdp = isset($_POST['mdp']) ? trim($_POST['mdp']) : '';

    if (!empty($login) && !empty($mdp)) {  //On vérifie qu'un login et mot de passe sont donnés
        
        // On éxecute une requete SQL préparé et sécurisé avec l'identifiant(login) que l'utilisateur à rentré 
        $stmt = $bdd->prepare('SELECT Login, Mdp, Fonction FROM utilisateur WHERE Login = :login');
        $stmt->execute(['login' => $login]);
        $donnees = $stmt->fetch();
            if ($stmt->rowCount() > 1) {
                // Il y a plus de 1 résultats retournés, ce qui est inattendu (erreur)
                header('Location: error.php');   
            }
            
            if ($_SESSION['redirect_to']==='creationqrcode.php') { //Pour l'accès a la page de génération de qrcode
                
                // Vérification du login, l'utilisateur peut etre uniquement administrateur
                if ($donnees && password_verify($mdp, $donnees['Mdp']) && $donnees['Fonction']=='Administrateur') {
                    //Si les identifiants sont bon, on ajoute le login de l'utilisateur dans les cookies,
                    //et on redirige vers la page de création de qrcode
                    $_SESSION['user'] = $login;
                    header('Location: '.$_SESSION['redirect_to'].'');     
                    exit;
                }else{
                    // Sinon on retourne sur la page index.php (login) avec une erreur
                   header('Location: index.php?error=1'); 
                   exit;
                } 
            }
            if ($_SESSION['redirect_to']=='qrcode.php') { //Pour l'accès a la page de maintenance
                
                // Vérification du login, l'utilisateur peut etre un technicien ou un administrateur
                if ($donnees && password_verify($mdp, $donnees['Mdp']) && ($donnees['Fonction']=='Technicien' || $donnees['Fonction']=='Administrateur')) {
                    //Si les identifiants sont bon, on ajoute le login de l'utilisateur dans les cookies,
                    //et on redirige vers la page de maintenance
                    $_SESSION['user'] = $login;
                    header('Location: '.$_SESSION['redirect_to'].'');     
                    exit;
                }else {
                    // Sinon on retourne sur la page index.php (login) avec une erreur
                    header('Location: index.php?error=1');
                    exit;
                } 
            }           
            // Si l'on arrive ici, il y'a une erreur
            header('Location: error.php'); // Redirection vers la page d'erreur
            exit;
    }
}?>

