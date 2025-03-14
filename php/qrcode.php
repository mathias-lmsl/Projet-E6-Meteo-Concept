<?php
require "../config/session.php";
require "../config/databasetech.php";

//On vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: index.php?id=' . $_GET["id"] . '&page=2');
    exit;
}

//On vérifie si l'utilisateur a le droit d'accéder à cette page
if ($_SESSION['id_consultable'] != $_GET["id"]) { 
    header('Location: logout.php?id=' . $_GET["id"] . '&page=2');
}

//On verifie si l'ID de la carte est valide
$stmt = $bdd->prepare('SELECT Nom, EtatComposant FROM carte WHERE IdCarte = :id');
$stmt->execute(['id' => $_GET["id"]]);
$donnees = $stmt->fetch();
if (!$donnees) {
    header('Location: error.php');
    exit();
}
$stmt->closeCursor();
?>

<!--On importe les fichiers css de la page-->
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

</head>
<body>
    <!-- On affiche les nuages animés -->
    <div class="cloud" style="top: 20%; left: 10%;"></div>
    <div class="cloud" style="top: 40%; right: 15%;"></div>
    <div class="cloud" style="top: 60%; left: 20%;"></div>
    <div class="container">

        <!-- Bouton pour basculer entre le mode sombre et clair -->
        <div class="darkmode-toggle" onclick="toggleDarkMode()">
            <img src="../includes/sun-darkmode.svg" id="sun-icon" alt="Mode sombre" class="icon">
            <img src="../includes/moon-darkmode.svg" id="moon-icon" alt="Mode clair" class="icon">
        </div>

        <!-- Contenu de la page -->
        <?php
        //On regarde si on a bien un ID de carte avant de tout commencer
        if (!empty($_GET["id"])) {

            /* -------------------Affichage du titre de la page avec le nom de la carte --------------------------- */
            $stmt = $bdd->prepare('SELECT Nom FROM carte WHERE IdCarte = :id');
            $stmt->execute(['id' => $_GET["id"]]);
            $donnees = $stmt->fetch();
            echo '<h1>Page de maintenance: ' . $donnees['Nom'] . '</h1><br><br><br>';
            $stmt->closeCursor();

            /*---------------------Affichage Etat des capteurs-------------------*/
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

            /*---------------------Affichage Données des capteurs en temps réel---------------*/
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
                                        )AND mesure.Horodatage >= NOW() - INTERVAL 30 MINUTE');
                $stmt->execute(['id' => $_GET["id"]]);
                echo '<div class="card">';
                    while ($donnees = $stmt->fetch()) {
                        echo $donnees['Nom'] . ' : <strong>' . $donnees['Valeur'] . ' ' . $donnees['Unite'] . '</strong><br>';
                    }
                    $stmt->closeCursor();
                echo '</div>';
            echo '</div>';

            /*--------------------Affiche Informations de la carte ------------------------*/
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

            /*---------------------Affichage Informations sur chaque capteur---------------------*/

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
