<?php
session_start();
require "connectDB.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Nom = trim($_POST["Nom"]);
    $Mdp = trim($_POST["Mdp"]);

    // Requête préparée pour éviter les injections SQL
    $req = $bdd->prepare("SELECT Mdp FROM utilisateur WHERE Login = :nom");
    $req->bindParam(':nom', $Nom, PDO::PARAM_STR);
    $req->execute();
    $reponse = $req->fetch();

    if ($reponse) {
        if ($Mdp == $reponse['Mdp']) { // Comparaison directe du mot de passe (sans hash)
            $_SESSION['login'] = $Nom;
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
    <div class="container">
        <form action="" method="POST">
            <div id="divConnexion">
                <p>Nom : <input type="text" name="Nom" required>
                <p>Mdp : <input type="password" name="Mdp" required>
                </br></br>
                <input type="submit" value="Connexion">
            </div>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
