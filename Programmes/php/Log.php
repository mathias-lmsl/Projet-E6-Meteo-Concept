<?php
session_start();
require "connectLog.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Nom = trim($_POST["Nom"]);
    $Mdp = $_POST["Mdp"]; // Ne pas trim le mot de passe avant le hashage

    // Requête préparée pour éviter les injections SQL
    $req = $bdd->prepare("SELECT Mdp, Fonction FROM utilisateur WHERE Login = :nom");
    $req->bindParam(':nom', $Nom, PDO::PARAM_STR);
    $req->execute();
    $reponse = $req->fetch();

    if ($reponse) {
        if (password_verify($Mdp, $reponse['Mdp'])) {
            $_SESSION['login'] = $Nom;
            $_SESSION['fonction'] = $reponse['Fonction'];
            header('Location: Consultation.php');
            exit();
        } else {
            $message = "Mot de passe invalide !";
        }
    } else {
        $message = "Nom d'utilisateur non trouvé !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="../css/Log.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="cloud" style="top: 20%; left: 10%;"></div>
     <div class="cloud" style="top: 40%; right: 15%; animation-delay: 0s;"></div>
     <div class="cloud" style="top: 60%; left: 20%; animation-delay: 0s;"></div>
    <div class="container">
        <h2>Se connecter</h2>
        <form action="" method="POST">
            <input type="text" name="Nom" placeholder="Nom d'utilisateur" required>
            <input type="password" name="Mdp" placeholder="Mot de passe" required>
            </br></br>
            <input type="submit" value="Connexion">
        </form>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>