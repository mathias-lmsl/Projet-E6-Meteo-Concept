<?php
require "../config/session.php";
$_SESSION['user']=null;//On commemence par supprimer les variables de la session qui pourrai etre en cours

?>
<!--On ajoute les fichiers css-->
<link rel="stylesheet" href="../includes/css/style.css" type="text/css" />
<link rel="stylesheet" href="../includes/css/stylelog.css" type="text/css" />
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
    <div class="cloud" style="top: 60%; left: 20%;"></div>
    <div class="container">
        <h2>Se connecter</h2>
        <?php
                    echo '<form action="auth.php" method="POST">';
                    echo '<input type="text" id="login" name="login" placeholder="Nom d\'utilisateur" required> <br>';
                    echo '<input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required> <br>';
                    echo '<input type="submit" value="Connexion">';
                    echo '</form>';
                    if (!empty($_GET["error"])) {
                        if ($_GET["error"] == 1) echo '<br><span style="color:red;">Login/mot de passe incorrect</span>';
                    }
        ?>
    </div>
</body>
</html>
