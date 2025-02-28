<?php
session_start();
require "connectDB.php";

if (!isset($_SESSION['login'])) {
    header('Location: Log.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Deconnexion'])) {
    session_destroy();
    header('Location: Log.php');
    exit;
}

// Récupération des serres
try {
    $stmtSerre = $bdd->query("SELECT IdSerre, Commentaire FROM serre");
    $serres = $stmtSerre->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des serres : " . $e->getMessage());
}

try {
    $req = $bdd->prepare("SELECT Prenom, Nom FROM utilisateur WHERE Login = :username");
    $req->execute([':username' => $_SESSION['login']]);
    $user = $req->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $prenom = $user['Prenom'];
        $nom = $user['Nom'];
    } else {
        error_log("Utilisateur non trouvé : " . $_SESSION['login']);
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des informations utilisateur : " . $e->getMessage());
}

try {
    $req = $bdd->query("SELECT Horodatage, Valeur FROM mesure WHERE IdCapteur = 1 ORDER BY Horodatage DESC LIMIT 10");
    $mesures = $req->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des mesures : " . $e->getMessage());
}

// Transformation des données pour le script JS
$labels = array_column($mesures, 'Horodatage');
$values = array_column($mesures, 'Valeur');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consultation des Mesures</title> 
    <link href="../css/Consultation.css" rel="stylesheet" type="text/css"> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="navBar">
        <div id="navAutre"><a href="Parametrage.php">Paramétrage du système</a></div>
        <div id="navTitre">Consultation des mesures</div>
        <div id="navDeconnexion">
            <?php echo $prenom . ' ' . $nom . ' | '; ?>
            <a href="Log.php">Déconnexion</a>
        </div>
    </header>
    <div class="clouds">
        <div class="cloud"></div>
        <div class="cloud"></div>
        <div class="cloud"></div>
        <div class="cloud"></div>
        <div class="cloud"></div>
    </div>
    <div id="divSelect"> 
        <div id="selectGraphique">
            <h3>Graphique 1</h3>
            <img src="../img/plus.svg" alt="plus non trouvé">
        </div>

        <div id="selectSerre">
            <h3>Selection serre :</h3>
            <select name="lstSerre" id="lstSerre" data-url="getChapelles.php">
                <option value="" selected>-- Sélectionner une serre --</option>
                <?php include 'getSerres.php'; ?>
            </select>
        </div>

        <div id="selectChapelle">
            <h3>Selection chapelle :</h3>
            <select name="lstChapelle" id="lstChapelle" data-url="getCartes.php" disabled>
                <option value="">-- Sélectionner une chapelle --</option>
            </select>
        </div>

        <div id="selectCarte">
            <h3>Selection carte :</h3>
            <select name="lstCarte" id="lstCarte" data-url="getCapteurs.php" disabled>
                <option value="">-- Sélectionner une carte --</option>
            </select>
        </div>

        <div id="selectCapteur">
            <h3>Selection capteur :</h3>
            <select name="lstCapteur" id="lstCapteur" disabled>
                <option value="">-- Sélectionner un capteur --</option>
            </select>
        </div>

        <div id="selectPlage">
            <button onclick="ouvertureModel()">Plage temporelle</button>
        </div>
    </div> 
    <div id="model">
        <label for="startDate">Date de début :</label>
        <input type="date" id="startDate">
        <label for="startTime">Heure de début :</label>
        <input type="time" id="startTime" required>
        <br><br>
        <label for="endDate">Date de fin :</label>
        <input type="date" id="endDate">
        <label for="endTime">Heure de fin :</label>
        <input type="time" id="endTime" required>
        <br><br>
        <button onclick="validateTime()">Valider</button>
        <button onclick="fermetureModel()">Fermer</button>
    </div>

    <div id="divGraphiques">
        <div id="Graphique">
            <canvas id="graphiqueCapteur"></canvas>
        </div>
        <div id="infoGraphique">
            <?php echo "Actuelle : "." Minimum : "." Maximum : "." Moyenne : ";?>
        </div>
    </div>
    <script>
        var labels = <?php echo json_encode($labels); ?>;
        var values = <?php echo json_encode($values); ?>;
    </script>
    <script src="../js/Consultation.js"></script>
    <script src="../js/Fonctions.js"></script>
</body>
</html>