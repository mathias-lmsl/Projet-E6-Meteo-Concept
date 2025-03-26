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

//On verifie si la clé primaire de la carte est valide et existe
$stmt = $bdd->prepare('SELECT Nom, EtatComposant FROM carte WHERE DevEui = :id');
$stmt->execute(['id' => $_GET["id"]]);
$donnees = $stmt->fetch();
if (!$donnees) {
    header('Location: error.php');
    exit();
}
$stmt->closeCursor();
?>

<script>
    // Variable globale pour stocker l'id du capteur
    let idCapteurCommentaire = null;
    let idCarteCommentaire = null;
    let type = null;

    // Fonction pour basculer entre le mode sombre et clair
    function toggleDarkMode() {
        document.body.classList.toggle('darkmode');
    }

    // Ouvrir la modal pour ajouter ou modifier un commentaire
    // Fonction pour ouvrir le modal
    function openModal(idComm = null, typeComm = null) {
        type = typeComm;
        if (type=='capteur'){
            idCapteurCommentaire = idComm; // Assigner l'id du capteur à la variable globale
            if (idCapteurCommentaire) {
            // Vous pouvez récupérer le commentaire du capteur ici via AJAX
            fetch("get_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "Id=" + idCapteurCommentaire + "&Type=" + type
            }).then(response => response.text()).then(data => {
                document.getElementById('commentText').value = data;
            });
            } else {
                document.getElementById('commentText').value = "";
            }
        }
        else if (type=='carte'){
            idCarteCommentaire = idComm; // Assigner l'id du capteur à la variable globale
            if (idCarteCommentaire) {
            // Vous pouvez récupérer le commentaire du capteur ici via AJAX
            fetch("get_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "Id=" + idCarteCommentaire + "&Type=" + type
            }).then(response => response.text()).then(data => {
                document.getElementById('commentText').value = data;
            });
            } else {
                document.getElementById('commentText').value = "";
            }
        }
        // Afficher le modal en modifiant son style display
        document.getElementById('commentModal').style.display = 'block';
    }

    // Fonction pour fermer le modal
    function closeModal() {
        document.getElementById('commentModal').style.display = 'none';
        
    }

    // Fonction pour enregistrer le commentaire
    function saveComment() {
        const comment = document.getElementById('commentText').value;

        if (type == 'capteur') {
            fetch("add-modify_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "Id=" + idCapteurCommentaire + "&Commentaire=" + comment + "&Type=" + type
            });
        } else if (type == 'carte') {
            fetch("add-modify_comment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "Id=" + idCarteCommentaire + "&Commentaire=" + comment + "&Type=" + type
            });
        }

        // Fermer la modal après avoir enregistré
        closeModal();
        window.location.href = window.location.href;
    }

</script>

<!DOCTYPE html>
<!--On importe les fichiers css de la page-->
<link rel="stylesheet" href="../includes/css/style.css" type="text/css" />
<link rel="stylesheet" href="../includes/css/styleqrcode.css" type="text/css" />
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de maintenance</title>

</head>
<body>
    <div class="container">

        <!-- Bouton pour basculer entre le mode sombre et clair -->
        <div class="darkmode-toggle" onclick="toggleDarkMode()">
            <img src="../includes/img/sun-darkmode.svg" id="sun-icon" alt="Mode sombre" class="icon">
            <img src="../includes/img/moon-darkmode.svg" id="moon-icon" alt="Mode clair" class="icon">
        </div>

        <!-- Contenu de la page -->
        <?php
        //On regarde si on a bien un ID de carte avant de tout commencer
        if (!empty($_GET["id"])) {

            /* -------------------Affichage du titre de la page avec le nom de la carte --------------------------- */
            $stmt = $bdd->prepare('SELECT Nom FROM carte WHERE DevEui = :id');
            $stmt->execute(['id' => $_GET["id"]]);
            $donnees = $stmt->fetch();
            echo '<h1>Page de maintenance: ' . $donnees['Nom'] . '</h1><br><br><br>';
            $stmt->closeCursor();

            /*---------------------Affichage Etat des capteurs-------------------*/
            $stmt = $bdd->prepare('SELECT capteur.Nom, capteur.EtatComposant FROM capteur, possede, carte
                                            WHERE carte.DevEui = :id
                                            AND carte.DevEui = possede.DevEui
                                            AND possede.IdCapteur = capteur.IdCapteur;');
            $stmt->execute(['id' => $_GET["id"]]);
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
                                        
            $stmt->execute(['id' => $_GET["id"]]);
            if($count = $stmt->rowCount()){
                echo '<div class="box">';
                    echo '<strong>Résultat des capteurs en temps réel (<30min) :</strong><br><br>';
                    echo '<div class="card">';
                        while ($donnees = $stmt->fetch()) {
                            if ($donnees['Valeur'] == NULL) {
                                echo $donnees['Nom'] . ' : <span style="color:red;"><strong>Aucune Valeur</strong></span><br>';
                            }else echo $donnees['Nom'] . ' : <strong>' . $donnees['Valeur'] . ' ' . $donnees['Unite'] . '</strong><br>';
                            
                        }
                    echo '</div>';
                echo '</div>';
            }
            $stmt->closeCursor();
            /*-------------------- Affiche Informations de la carte ------------------------*/
            echo '<div class="centerbox">';
                echo '<strong>Informations essentielles sur la station :</strong><br><br>';
                $stmt = $bdd->prepare('SELECT * FROM carte WHERE DevEui = :id;');
                $stmt->execute(['id' => $_GET["id"]]);
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
            $stmt->execute(['id' => $_GET["id"]]);
            if($count = $stmt->rowCount()){
                echo '<div class="info-header">';
                    echo '<strong>Informations sur chaque capteur associé à la station :</strong><br><br>';
                echo '</div>';
                echo '<div class="infocard">';
                    $stmt = $bdd->prepare('SELECT * FROM capteur WHERE IdCapteur IN (SELECT IdCapteur FROM possede WHERE DevEui = :id) ORDER BY EtatComposant ASC;');
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
                                if ($donnees['SeuilMax']) echo 'Seuil Minimum : <strong>' . $donnees['SeuilMax'].' '. $donnees['Unite']  . '</strong><br>';
                                if ($donnees['SeuilMin']) echo 'Seuil Maximum : <strong>' . $donnees['SeuilMin'].' '. $donnees['Unite']  . '</strong><br>';
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
                        <textarea id="commentText" rows="2" cols="40"></textarea><br><br>
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
            echo '<p style="color:red;">Le QR code scanné est sûrement incorrect, veuillez réessayer.</p>';
        }
        ?>
        <div class="logout-container">
            <a href="logout.php?id=<?php echo $_GET["id"]; ?>&page=2">Déconnexion</a>
        </div>
    </div>
</body>
</html>
