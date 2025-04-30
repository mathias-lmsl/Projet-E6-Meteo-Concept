<?php
require "../config/session.php";
require "../config/databasetech.php";

if (!isset($_SESSION['id_consultable'])&&isset($_GET['DevEui'])) {
    $_SESSION['id_consultable'] = $_GET['DevEui']; //On stock le devEUI du capteur a afficher dans la session
}

//On vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_to'] = 'qrcode.php'; //On met dans les cookies la page a laquel on souhaite acceder pour la page de log
    header('Location: index.php'); //On redirige vers la page de login
    exit;
}

//On stock dans une variable l'id(DevEui) de la carte a afficher
$IDutilisable=$_SESSION['id_consultable'];
//On verifie si le deveui de la carte est valide et existe
$stmt = $bdd->prepare('SELECT Nom, EtatComposant FROM carte WHERE DevEui = :id');
$stmt->execute(['id' => $IDutilisable]);
$donnees = $stmt->fetch();
if (!$donnees) {
    header('Location: error.php');
    exit;
}
$stmt->closeCursor();
?>

<script>
    // Fonction pour se déconnecter
    function logout() {
        window.location.href = "logout.php?id=<?php echo $_SESSION["id_consultable"]; ?>&page=2";
    }
</script>
<?php
//Fonction pour afficher la direction du vent
function directionVent($angle) {
    switch (true) {
        case ($angle >= 348 || $angle < 12.25):
            return "NORD";
        case ($angle >= 12.25 && $angle < 34.75):
            return "NORD-NORD-EST";
        case ($angle >= 34.75 && $angle < 57.25):
            return "NORD-EST";
        case ($angle >= 57.25 && $angle < 79.75):
            return "EST-NORD-EST";
        case ($angle >= 79.75 && $angle < 102.25):
            return "EST";
        case ($angle >= 102.25 && $angle < 124.75):
            return "EST-SUD-EST";
        case ($angle >= 124.75 && $angle < 147.25):
            return "SUD-EST";
        case ($angle >= 147.25 && $angle < 169.75):
            return "SUD-SUD-EST";
        case ($angle >= 169.75 && $angle < 192.25):
            return "SUD";
        case ($angle >= 192.25 && $angle < 214.75):
            return "SUD-SUD-OUEST";
        case ($angle >= 214.75 && $angle < 237.25):
            return "SUD-OUEST";
        case ($angle >= 237.25 && $angle < 259.75):
            return "OUEST-SUD-OUEST";
        case ($angle >= 259.75 && $angle < 282.25):
            return "OUEST";
        case ($angle >= 282.25 && $angle < 304.75):
            return "OUEST-NORD-OUEST";
        case ($angle >= 304.75 && $angle < 327.25):
            return "NORD-OUEST";
        default:
            return "NORD-NORD-OUEST";
    }
}
?>

<!DOCTYPE html>
<!--On importe les fichiers css de la page-->
<link rel="stylesheet" href="../includes/css/style.css" type="text/css" />
<link rel="stylesheet" href="../includes/css/styleqrcode.css" type="text/css" />
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de maintenance</title>
    <script src="../javascript/qrcode.js" defer></script>
</head>
<body>
    <div class="container">

        <!-- Bouton pour basculer entre le mode sombre et clair -->
        <div class="darkmode-toggle" onclick="toggleDarkMode()">
            <img src="../includes/img/sun-darkmode.svg" id="sun-icon" alt="Mode sombre" class="icon">
            <img src="../includes/img/moon-darkmode.svg" id="moon-icon" alt="Mode clair" class="icon">
        </div>
        <div class="logout" onclick="logout()">
            <img src="../includes/img/logout-icon.svg" id="logout-icon" alt="Bouton logout" class="icon">
        </div>

        <!-- Contenu de la page -->
        <?php
        //On regarde si on a bien un ID de carte avant de tout commencer
        if (!empty($_SESSION["id_consultable"])) {

            /* -------------------Affichage du titre de la page avec le nom de la carte --------------------------- */
            $stmt = $bdd->prepare('SELECT Nom FROM carte WHERE DevEui = :id');
            $stmt->execute(['id' => $IDutilisable]);
            $donnees = $stmt->fetch();
            echo '<h1>Page de maintenance: ' . $donnees['Nom'] . '</h1><br>';
            $stmt->closeCursor();

            /*---------------------Affichage Etat des capteurs-------------------*/
            $stmt = $bdd->prepare('SELECT capteur.Nom, capteur.EtatComposant FROM capteur, possede, carte
                                            WHERE carte.DevEui = :id
                                            AND carte.DevEui = possede.DevEui
                                            AND possede.IdCapteur = capteur.IdCapteur;');
            $stmt->execute(['id' => $IDutilisable]);
            if($count = $stmt->rowCount()){
                echo '<div class="box">';
                    echo '<strong>État des capteurs de la station:</strong><br><br>';
                    echo '<div class="card">';
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
                    echo '</div>';
                echo '</div>';
            }
            $stmt->closeCursor();

            /*---------------------Affichage Données des capteurs en temps réel---------------*/
            $stmt = $bdd->prepare('SELECT capteur.Nom, 
                                    COALESCE(mesure.Valeur, NULL) AS Valeur, 
                                    capteur.Unite
                                    FROM capteur
                                    JOIN possede ON capteur.IdCapteur = possede.IdCapteur
                                    JOIN carte ON possede.DevEui = carte.DevEui
                                    LEFT JOIN mesure ON mesure.IdCapteur = capteur.IdCapteur 
                                    AND mesure.Horodatage = (
                                    SELECT MAX(mesure.Horodatage)
                                    FROM mesure
                                    WHERE mesure.IdCapteur = capteur.IdCapteur
                                    )
                                    AND mesure.Horodatage >= NOW() - INTERVAL 31 MINUTE
                                    WHERE carte.DevEui = :id;');
                                        
            $stmt->execute(['id' => $IDutilisable]);
            if($count = $stmt->rowCount()){
                echo '<div class="box">';
                    echo '<strong>Résultat des capteurs en temps réel (<30min) :</strong><br><br>';
                    echo '<div class="card">';
                        while ($donnees = $stmt->fetch()) {
                            if ($donnees['Valeur'] === NULL) {
                                echo $donnees['Nom'] . ' : <span style="color:red;"><strong>Aucune valeur</strong></span><br>';
                            }else{
                                if ($donnees['Unite'] == '°'){
                                    echo $donnees['Nom'] . ' : <strong>' . $donnees['Valeur'] . ' ' . $donnees['Unite'] . ' '.directionVent($donnees['Valeur']). '</strong><br>'; 
                                }else{
                                    echo $donnees['Nom'] . ' : <strong>' . $donnees['Valeur'] . ' ' . $donnees['Unite'] . '</strong><br>'; 
                                }
                            } 
                        }
                    echo '</div>';
                echo '</div>';
            }
            $stmt->closeCursor();
            
            /*-------------------- Affiche Informations de la carte ------------------------*/
            echo '<div class="centerbox">';
                echo '<strong>Informations essentielles sur la station :</strong><br><br>';
                $stmt = $bdd->prepare('SELECT * FROM carte WHERE DevEui = :id;');
                $stmt->execute(['id' => $IDutilisable]);
                while ($donnees = $stmt->fetch()) {
                    $style='';
                    switch ($donnees['EtatComposant']) {
                        case 'OK':
                            $couleur = 'green';
                            break;
                        case 'HS':
                            $couleur = 'red';
                            $style='border: 0.2em solid red;'; //On met des contours rouges pour l'identifier rapidement
                            break;
                        case 'Veille':
                            $couleur = 'grey';
                            break;
                    }
                    echo '<div class="card" style="'.$style.'">';
                        echo 'Nom de la station : <strong>' . $donnees['Nom'] . '</strong><br>';
                        echo 'Date de mise en service : <strong>' . $donnees['DateMiseEnService'] . '</strong><br>';
                        echo 'AppKey : <strong>' . $donnees['AppKey'] . '</strong><br>';
                        echo 'AppEui : <strong>' . $donnees['AppEui'] . '</strong><br>';
                        echo 'DevEui : <strong><span class="eui">' . $donnees['DevEui'] . '</span></strong><br>';
                        if ($donnees['Marque']) echo 'Marque : <strong>' . $donnees['Marque'] . '</strong><br>';
                        if ($donnees['Reference']) echo 'Référence : <strong>' . $donnees['Reference'] . '</strong><br>';
                        if ($donnees['NumSerie']) echo 'Numéro de série : <strong>' . $donnees['NumSerie'] . '</strong><br>';
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
                        echo 'Etat de la station : <span style="color:' . $couleur . ';"><strong>' . $donnees['EtatComposant'] . '</strong></span><br>';
                        if ($donnees['Commentaire']){
                            echo 'Commentaire : <strong>' . $donnees['Commentaire'] . '</strong><br>';
                            echo '<button id="boutonCommentaire" onclick="openModal(\''.$donnees['DevEui'].'\',\'carte\')">Modifier le commentaire</button>';
                        } else echo '<button id="boutonCommentaire" onclick="openModal(\''.$donnees['DevEui'].'\',\'carte\')">Ajouter un commentaire</button>';
                    echo '</div>';
                }
            echo '</div>';
            $stmt->closeCursor();

            /*---------------------Affichage Informations sur chaque capteur---------------------*/
            $stmt = $bdd->prepare('SELECT * FROM capteur WHERE IdCapteur IN (SELECT IdCapteur FROM possede WHERE DevEui = :id) 
                                   ORDER BY EtatComposant ASC;');
            $stmt->execute(['id' => $IDutilisable]);
            if($count = $stmt->rowCount()){
                echo '<div class="info-header">';
                    echo '<strong>Informations sur chaque capteur associé à la station :</strong><br><br>';
                echo '</div>';
                echo '<div class="infocapteur">';
                    $stmt = $bdd->prepare('SELECT * FROM capteur WHERE IdCapteur IN (SELECT IdCapteur FROM possede WHERE DevEui = :id) ORDER BY EtatComposant ASC;');
                    $stmt->execute(['id' => $IDutilisable]);
                    while ($donnees = $stmt->fetch()) {
                        echo '<div class="box">';
                            $style='';
                            switch ($donnees['EtatComposant']) {
                                case 'OK':
                                    $couleur = 'green';
                                    break;
                                case 'HS':
                                    $couleur = 'red';
                                    $style='border: 0.2em solid red;'; //On met des contours rouges pour l'identifier rapidement
                                    break;
                                case 'Veille':
                                    $couleur = 'grey';
                                    break;
                            }
                            echo '<div class="card" style="'.$style.'">';
                                echo 'Nom du capteur : <strong>' . $donnees['Nom'] . '</strong><br>';                
                                echo 'Etat du capteur : <span style="color:' . $couleur . ';"><strong>' . $donnees['EtatComposant'] . '</strong></span><br>';
                                echo 'Grandeur du capteur : <strong>' . $donnees['GrandeurCapt'] . '</strong> en <strong>' . $donnees['Unite'] . '</strong><br>';
                                if ($donnees['Marque']) echo 'Marque : <strong>' . $donnees['Marque'] . '</strong><br>';
                                if ($donnees['Reference']) echo 'Référence : <strong>' . $donnees['Reference'] . '</strong><br>';
                                if ($donnees['NumSerie']) echo 'Numéro de série : <strong>' . $donnees['NumSerie'] . '</strong><br>';
                                echo 'Date de mise en service : <strong>' . $donnees['DateMiseEnService'] . '</strong><br>';
                                if ($donnees['SeuilMax']) echo 'Seuil Minimum : <strong>' . $donnees['SeuilMin'].' '. $donnees['Unite']  . '</strong><br>';
                                if ($donnees['SeuilMin']) echo 'Seuil Maximum : <strong>' . $donnees['SeuilMax'].' '. $donnees['Unite']  . '</strong><br>';
                                if ($donnees['Commentaire']){
                                    echo 'Commentaire : <strong>' . $donnees['Commentaire'] . '</strong><br>';
                                    echo '<button id="boutonCommentaire" onclick="openModal(\''.$donnees['IdCapteur'].'\',\'capteur\')">Modifier le commentaire</button>';
                                } else echo '<button id="boutonCommentaire" onclick="openModal(\''.$donnees['IdCapteur'].'\',\'capteur\')">Ajouter un commentaire</button>';                               
                            echo '</div>';
                            ?>
                <div id="commentModal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <h2>Ajouter/Modifier le commentaire</h2>
                        <textarea id="commentText" rows="4" cols="40"></textarea><br><br>
                        <button id="saveComment" onclick="saveComment()">Enregistrer le commentaire</button>
                    </div>
                </div>
                <?php
                        echo '</div>';
                }
                
                echo '</div>';
            }
            $stmt->closeCursor();
        } else {
            header('Location: error.php');
            exit;
        }
        ?>
    </div>
</body>
</html>
