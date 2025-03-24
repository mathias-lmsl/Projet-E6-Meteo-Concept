<?php
require "../config/session.php";
?>
<!--On ajoute les fichiers css-->
<link rel="stylesheet" href="../includes/style.css" type="text/css" />
<link rel="stylesheet" href="../includes/stylelog.css" type="text/css" />
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
</head>
<body>
    <!--On ajoute les nuages-->
    <div class="cloud" style="top: 20%; left: 10%;"></div>
    <div class="cloud" style="top: 40%; right: 15%;"></div>
    <div class="cloud" style="top: 70%; left: 20%;"></div>
    <div class="container">
        <h2>Se connecter</h2>
        <?php
        //On vérifie si la page est définie pour la redirection
        if (!empty($_GET["page"])){
            //On verifie le numero de la page
            switch ($_GET["page"]) {
                //Si la page est 1 ()
                case 1:
                    echo '<form action="login.php?page='. $_GET["page"] .'" method="POST">';
                    echo '<input type="text" id="login" name="login" placeholder="Nom d\'utilisateur" required> <br>';
                    echo '<input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required> <br>';
                    echo '<input type="submit" value="Connexion">';
                    echo '</form>';
                    if (!empty($_GET["error"])) {
                        if ($_GET["error"] == 1) echo '<br><span style="color:red;">Login/mot de passe incorrect</span>';
                    }
                    break;
                case 2:
                    if (!empty($_GET["id"])){
                        echo '<form action="login.php?id=' . $_GET["id"] .'&page='. $_GET["page"] .'" method="POST">';
                        echo '<input type="text" id="login" name="login" placeholder="Nom d\'utilisateur" required> <br>';
                        echo '<input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required> <br>';
                        echo '<input type="submit" value="Connexion">';
                        echo '</form>';
                        if (!empty($_GET["error"])) {
                            if ($_GET["error"] == 1) echo '<br><span style="color:red;">Login/mot de passe incorrect</span>';
                        }
                    }
                    else echo '<p style="color:red;">Le QR code que vous venez de scanner n\'est pas valide !<br>Veuillez réessayer ou scanner un nouveau QR code</p>';
                    break;
                default:
                    header('Location: error.php');
                    exit;
            }
        }else {
            header('Location: error.php');
            exit;
        }
        ?>
    </div>
</body>
</html>
