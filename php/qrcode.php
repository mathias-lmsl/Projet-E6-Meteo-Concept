<?php
require "../config/session.php";
require "../config/database.php";

if (!isset($_SESSION['user'])) {
    header('Location: index.php?id='.$_GET["id"].'&page=2');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page QR Code</title>
    <style>
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #87CEEB, #4682B4);
            padding: 20px;
            box-sizing: border-box;
        }

        .container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        a {
            padding: 10px 20px;
            margin-top: 10px;
            border: none;
            background-color: #007BFF;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        a:hover {
            background-color: #0056b3;
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body> 
    <div class="container">
        <?php
        if (!empty($_GET["id"])) {
            if ($_SESSION['id_consultable']!=$_GET["id"]){ //Si l'utilisateur change l'ID dans la barre de recherche, SECURITÉ
                header('Location: logout.php?id='.$_GET["id"].'&page=2');
            }
            $stmt = $bdd->prepare('SELECT Nom, EtatComposant FROM carte WHERE IdCarte = :id');
            $stmt->execute(['id' => $_GET["id"]]);
            $donnees = $stmt->fetch();
            if (!$donnees) {
                header("Location: error.php");
                exit();
            }

            echo '<p>Vous êtes connecté. Voici la page de maintenance.</p>';
            echo '<br><strong>État des capteurs en temps réel :</strong><br><br>';
            $stmt = $bdd->prepare('SELECT capteur.Nom,capteur.EtatComposant FROM capteur,possede,carte 
                                    WHERE carte.IdCarte=:id
                                    AND carte.IdCarte=possede.IdCarte
                                    AND possede.IdCapteur=capteur.IdCapteur;');
            $stmt->execute(['id' => $_GET["id"]]);
            while ($donnees = $stmt->fetch()){
                echo $donnees['Nom'].' : '.$donnees['EtatComposant'].'<br>';
            }
            $stmt->closeCursor();
            
            echo '<br><strong>Résultat des capteurs en temps réel :</strong><br><br>';
            $stmt = $bdd->prepare('SELECT capteur.Nom,mesure.Valeur,capteur.Unite FROM capteur,possede,carte,mesure 
                                    WHERE carte.IdCarte=:id
                                    AND carte.IdCarte=possede.IdCarte
                                    AND possede.IdCapteur=capteur.IdCapteur
                                    AND mesure.IdCapteur=capteur.IdCapteur ORDER BY Horodatage DESC LIMIT 3;');
            $stmt->execute(['id' => $_GET["id"]]);
            while ($donnees = $stmt->fetch()){
                echo $donnees['Nom'].' : '.$donnees['Valeur'].' '.$donnees['Unite'].'<br>';
            }
            $stmt->closeCursor();

            echo '<br><strong>Informations essentiel sur la carte :</strong><br><br>';
            $stmt = $bdd->prepare('SELECT * FROM carte WHERE IdCarte=:id;');
            $stmt->execute(['id' => $_GET["id"]]);
            while ($donnees = $stmt->fetch()){
                echo 'Date de mise en service : '.$donnees['DateMiseEnService'].'<br>';
                echo 'EUI : '.$donnees['EUI'].' |AppEUI : '.$donnees['AppEUI'].'<br>';
                echo 'Marque : '.$donnees['Marque'].'<br>';
                echo 'Référence : '.$donnees['Reference'].'<br>Numéro de série : '.$donnees['NumSerie'].'<br>';
                echo 'Commentaire : '.$donnees['Commentaire'].'<br>';
                echo 'Etat de la carte : '.$donnees['EtatComposant'].'<br>';

            }
            $stmt->closeCursor();
        } else {
            echo '<p style="color:red;">Le QR code scanné est sûrement incorrect, veuillez réessayer.</p>';
        }
        ?>
        <br>
        <a href="logout.php?id=<?php echo $_GET["id"]; ?>&page=2">Déconnexion</a>
    </div>
</body>
</html>
