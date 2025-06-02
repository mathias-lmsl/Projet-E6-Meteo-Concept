<?php
require "../config/session.php";
unset($_SESSION['user']);//On commemence par supprimer les variables de la session qui pourrai etre en cours


?>

<!--On ajoute les fichiers css-->
<link rel="stylesheet" href="../includes/css/style.css" type="text/css" />
<link rel="stylesheet" href="../includes/css/stylelog.css" type="text/css" />
<link rel="icon" href="../includes/img/siteicon.ico" type="image/x-icon">
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
    <script src="../js/log.js" defer></script>
</head>
<body>

    <!-- Bouton pour basculer entre le mode sombre et clair -->
    <div class="darkmode-toggle" onclick="toggleDarkMode()">
        <img src="../includes/img/sun-darkmode.svg" id="sun-icon" alt="Mode sombre" class="icon">
        <img src="../includes/img/moon-darkmode.svg" id="moon-icon" alt="Mode clair" class="icon">
    </div>        
    
    <!--On ajoute les nuages-->
    <div class="cloud" style="top: 20%; left: 10%;"></div>
    <div class="cloud" style="top: 40%; right: 15%;"></div>
    <div class="cloud" style="top: 60%; left: 20%;"></div>
    <div class="container">
        <h2>Se connecter</h2>
        <?php
                    // On affiche le formulaire de connexion
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
