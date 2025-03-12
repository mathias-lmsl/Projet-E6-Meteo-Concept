<?php
require "../config/session.php";
require "../config/databasetech.php";

if (!isset($_SESSION['user'])) {
    header('Location: index.php?id=' . $_GET["id"] . '&page=2');
    exit;
}
?>
<link rel="stylesheet" href="../includes/style.css" type="text/css" />
<link rel="stylesheet" href="../includes/styleqrcode.css" type="text/css" />
<script>
    // Fonction pour basculer entre le mode sombre et clair
    function toggleDarkMode() {
        document.body.classList.toggle('darkmode');
    }
</script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de maintenance</title>
    <!--
    <style>
        /* Reset général pour tous les éléments */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* HTML et body pour éviter le débordement */
        html, body {
            width: 100%;
            overflow-x: hidden;
            font-family: Arial, sans-serif;
        }

        /* Styles généraux (Mode clair par défaut) */
        body {
            background: linear-gradient(to bottom, #87CEEB, #4682B4);
            padding: 20px;
            box-sizing: border-box;
            overflow-y: auto;
            min-height: 100%;
            font-family: Arial, sans-serif;
            color: #333; /* Couleur de texte par défaut (mode clair) */
        }

        /* Mode sombre */
        body.darkmode {
            background: #121212; /* Couleur de fond sombre */
            color: #e0e0e0; /* Couleur de texte clair */
        }

        /* Container principal (mode clair par défaut) */
        .container {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
        }

        /* Mode sombre pour le container */
        body.darkmode .container {
            background: #333; /* Fond plus sombre pour le container */
            color: #e0e0e0; /* Couleur de texte claire pour le container */
        }

        /* Bouton de changement de mode sombre/clair */
        .darkmode-toggle {
            position: absolute;
            top: 25px;
            left: 25px;
            cursor: pointer;
            z-index: 10;
        }

        /* Masquer l'icône lorsque l'autre est active */
        .icon {
            width: 30px;
            height: 30px;
            display: inline-block;
        }

        #sun-icon {
            display: none;
        }

        /* Afficher l'icône de la lune lorsque le mode sombre est activé */
        body.darkmode #sun-icon {
            display: inline-block;
        }

        /* Afficher l'icône du soleil lorsque le mode clair est activé */
        body.darkmode #moon-icon {
            display: none;
        }

        /* Conteneur principal */
        .container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            width: 100%; /* Utilise toute la largeur disponible */
            max-width: 1000px;
            margin: 0 auto; /* Centrer horizontalement */
            opacity: 0.9;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 5px; /* Espace entre les boîtes */
            overflow: hidden;
        }

        /* Boîtes qui s'adaptent sur la même ligne en fonction de l'espace disponible */
        /* Autres éléments pour éviter les débordements */
        .box {
            flex: 1 1 calc(50% - 20px); /* Permet aux boîtes de s'adapter côte à côte */
            padding: 15px;
            max-width: 100%;
            box-sizing: border-box;
            overflow: hidden; /* Cache le contenu qui dépasse */
            word-wrap: break-word; /* Permet de casser les mots longs */
        }

        /* Boîte centrale pour les informations principales */
        .centerbox {
            flex: 1 0 calc(100%); /* Même comportement que .box */
            width: 100%;
            box-sizing: border-box;
        }

        /* Carte d'information */
        .card {
            padding: 20px;
            
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden; /* Cache le contenu qui dépasse */
            white-space: normal; /* Permet au texte de se diviser en plusieurs lignes */
            word-wrap: break-word; /* Force la coupure des mots longs */
            margin-bottom: 20px;
            max-width: 100%;
        }

        .infocard {
            width: 100%;
            box-sizing: border-box;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 5px; /* Espace entre les boîtes */
        }

        .info-header {
            width: 100%;
            text-align: center;
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

        /* Responsive design : ajustement pour les petits écrans */
        @media (max-width: 723px) {
            .container {
                padding: 10px;
            }
            .box, .centerbox {
                flex: 1 1 100%; /* Les boîtes prennent toute la largeur sur les petits écrans */
            }
        }

        /* Animation des nuages */
        .cloud {
            position: absolute;
            width: 100px;
            height: 60px;
            background: white;
            border-radius: 50px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
            animation: float 6s infinite alternate ease-in-out;
            z-index: -1;
        }

        .cloud::before, .cloud::after {
            content: "";
            position: absolute;
            background: white;
            border-radius: 50%;
        }

        .cloud::before {
            width: 70px;
            height: 70px;
            top: -35px;
            left: 10px;
        }

        .cloud::after {
            width: 50px;
            height: 50px;
            top: -25px;
            right: 10px;
        }

        @keyframes float {
            0% { transform: translateX(-30px); }
            100% { transform: translateX(30px); }
        }

        /* Conteneur pour le bouton de déconnexion */
        .logout-container {
            display: flex;
            justify-content: center; /* Centre le bouton horizontalement */
            width: 100%; /* Prend toute la largeur de son parent */
            margin-top: 20px; /* Ajoute de l'espace autour du bouton */
        }

        .logout-container a {
            padding: 12px 24px; /* Taille du bouton */
            background-color: #007BFF; /* Couleur du fond */
            color: white; /* Couleur du texte */
            border-radius: 5px; /* Coins arrondis */
            text-decoration: none; /* Enlève le soulignement */
            font-size: 16px; /* Taille du texte */
        }

        .logout-container a:hover {
            background-color: #0056b3; /* Couleur du fond au survol */
        }
    </style>-->

</head>
<body>
    <div class="cloud" style="top: 20%; left: 10%;"></div>
    <div class="cloud" style="top: 40%; right: 15%;"></div>
    <div class="cloud" style="top: 60%; left: 20%;"></div>
    <div class="container">
        <!-- Bouton pour basculer entre le mode sombre et clair -->
        <div class="darkmode-toggle" onclick="toggleDarkMode()">
            <img src="../includes/sun-darkmode.svg" id="sun-icon" alt="Mode sombre" class="icon">
            <img src="../includes/moon-darkmode.svg" id="moon-icon" alt="Mode clair" class="icon">
        </div>

        <?php
        if (!empty($_GET["id"])) {
            if ($_SESSION['id_consultable'] != $_GET["id"]) { // Sécurité contre le changement d'ID dans la barre d'adresse
                header('Location: logout.php?id=' . $_GET["id"] . '&page=2');
            }
            $stmt = $bdd->prepare('SELECT Nom, EtatComposant FROM carte WHERE IdCarte = :id');
            $stmt->execute(['id' => $_GET["id"]]);
            $donnees = $stmt->fetch();
            if (!$donnees) {
                header("Location: error.php");
                exit();
            }
            echo '<h1>Page de maintenance: ' . $donnees['Nom'] . '</h1><br><br><br>';
            $stmt->closeCursor();
            /*---------------------Etat des capteurs-------------------*/

            echo '<div class="box">';
            echo '<strong>État des capteurs en temps réel :</strong><br><br>';
            echo '<div class="card">';
            $stmt = $bdd->prepare('SELECT capteur.Nom, capteur.EtatComposant FROM capteur, possede, carte
                                    WHERE carte.IdCarte = :id
                                    AND carte.IdCarte = possede.IdCarte
                                    AND possede.IdCapteur = capteur.IdCapteur;');
            $stmt->execute(['id' => $_GET["id"]]);
            while ($donnees = $stmt->fetch()) {
                switch ($donnees['EtatComposant']) {
                    case 'OK':
                        $couleur = 'green';
                        break;
                    case 'HS':
                        $couleur = 'red';
                        break;
                    case 'Veille':
                        $couleur = 'grey';
                        break;
                }
                echo $donnees['Nom'] . ' : <span style="color:' . $couleur . ';"><strong>' . $donnees['EtatComposant'] . '</strong></span><br>';
            }
            $stmt->closeCursor();
            echo '</div>';
            echo '</div>';

            /*---------------------Données des capteurs en temps réel---------------*/

            echo '<div class="box">';
            echo '<strong>Résultat des capteurs en temps réel (<30min) :</strong><br><br>';
            $stmt = $bdd->prepare('SELECT capteur.Nom, mesure.Valeur, capteur.Unite
                                    FROM capteur
                                    JOIN possede ON capteur.IdCapteur = possede.IdCapteur
                                    JOIN carte ON possede.IdCarte = carte.IdCarte
                                    JOIN mesure ON mesure.IdCapteur = capteur.IdCapteur
                                    WHERE carte.IdCarte = :id
                                    AND mesure.Horodatage = (
                                        SELECT MAX(mesure.Horodatage)
                                        FROM mesure
                                        WHERE mesure.IdCapteur = capteur.IdCapteur
                                    )
                                    AND mesure.Horodatage >= NOW() - INTERVAL 30 MINUTE
                                    ');
            $stmt->execute(['id' => $_GET["id"]]);
            echo '<div class="card">';
            while ($donnees = $stmt->fetch()) {
                echo $donnees['Nom'] . ' : <strong>' . $donnees['Valeur'] . ' ' . $donnees['Unite'] . '</strong><br>';
            }
            $stmt->closeCursor();
            echo '</div>';
            echo '</div>';

            /*--------------------Informations de la carte ------------------------*/

            echo '<div class="centerbox">';
            echo '<strong>Informations essentielles sur la carte :</strong><br><br>';
            $stmt = $bdd->prepare('SELECT * FROM carte WHERE IdCarte = :id;');
            $stmt->execute(['id' => $_GET["id"]]);
            while ($donnees = $stmt->fetch()) {
                echo '<div class="card">';
                echo 'Date de mise en service : <strong>' . $donnees['DateMiseEnService'] . '</strong><br>';
                echo 'EUI : <strong><span class="eui">' . $donnees['EUI'] . '</span></strong> || <strong><span class="appeui">' . $donnees['AppEUI'] . '</span></strong><br>';
                if ($donnees['Marque']) echo 'Marque : <strong>' . $donnees['Marque'] . '</strong><br>';
                if ($donnees['Reference']) echo 'Référence : <strong>' . $donnees['Reference'] . '</strong><br>';
                if ($donnees['NumSerie']) echo 'Numéro de série : <strong>' . $donnees['NumSerie'] . '</strong><br>';
                if ($donnees['Commentaire']) echo 'Commentaire : <strong>' . $donnees['Commentaire'] . '</strong><br>';
                switch ($donnees['EtatComposant']) {
                    case 'OK':
                        $couleur = 'green';
                        break;
                    case 'HS':
                        $couleur = 'red';
                        break;
                    case 'Veille':
                        $couleur = 'grey';
                        break;
                }
                echo 'Etat de la carte : <span style="color:' . $couleur . ';"><strong>' . $donnees['EtatComposant'] . '</strong></span>';
                echo '</div>';
            }
            $stmt->closeCursor();
            echo '</div>';

            /*---------------------Informations sur chaque capteur---------------------*/

            echo '<div class="info-header">';
            echo '<strong>Informations sur chaque capteur associé à la carte :</strong><br><br>';
            echo '</div>';
            echo '<div class="infocard">';
            $stmt = $bdd->prepare('SELECT * FROM capteur WHERE IdCapteur IN (SELECT IdCapteur FROM possede WHERE IdCarte = :id) ORDER BY EtatComposant ASC;');
            $stmt->execute(['id' => $_GET["id"]]);
            while ($donnees = $stmt->fetch()) {
                echo '<div class="box">';
                $style='';
                switch ($donnees['EtatComposant']) {
                    case 'OK':
                        $couleur = 'green';
                        break;
                    case 'HS':
                        $couleur = 'red';
                        $style='border: 0.2em solid red;';
                        break;
                    case 'Veille':
                        $couleur = 'grey';
                        break;
                }
                echo '<div class="card" style="'.$style.'">';
                echo 'Nom du capteur : <strong>' . $donnees['Nom'] . '</strong><br>';                
                echo 'Etat du capteur : <span style="color:' . $couleur . ';"><strong>' . $donnees['EtatComposant'] . '</strong></span><br>';
                if ($donnees['Marque']) echo 'Marque : <strong>' . $donnees['Marque'] . '</strong><br>';
                if ($donnees['Reference']) echo 'Référence : <strong>' . $donnees['Reference'] . '</strong><br>';
                if ($donnees['NumSerie']) echo 'Numéro de série : <strong>' . $donnees['NumSerie'] . '</strong><br>';
                echo 'Date de mise en service : <strong>' . $donnees['DateMiseEnService'] . '</strong><br>';
                if ($donnees['Commentaire']) echo 'Commentaire : <strong>' . $donnees['Commentaire'] . '</strong><br>';
                echo 'Grandeur du capteur : <strong>' . $donnees['GrandeurCapt'] . '</strong> en <strong>' . $donnees['Unite'] . '</strong><br>';
                if ($donnees['SeuilMax']) echo 'Seuil Minimum : <strong>' . $donnees['SeuilMax'] . '</strong><br>';
                if ($donnees['SeuilMin']) echo 'Seuil Maximum : <strong>' . $donnees['SeuilMin'] . '</strong>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';

        } else {
            echo '<p style="color:red;">Le QR code scanné est sûrement incorrect, veuillez réessayer.</p>';
        }
        ?>
        <div class="logout-container">
            <a href="logout.php?id=<?php echo $_GET["id"]; ?>&page=2">Déconnexion</a>
        </div>
    </div>
</body>
</html>
