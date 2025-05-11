<?php
session_start();
require "connectDB.php";

if (!isset($_SESSION['login']) || $_SESSION['fonction'] !== 'Administrateur') {
    header('Location: Log.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Deconnexion'])) {
    session_destroy();
    header('Location: Log.php');
    exit;
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


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consultation des Mesures</title> 
    <link href="../css/Consultation.css" rel="stylesheet" type="text/css"> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const utilisateurNomComplet = "<?php echo addslashes($prenom . ' ' . $nom); ?>";
    </script>
</head>
<body>
    <header class="navBar">
        <div id="navAutre">
            <a href="Parametrage.php">
                <img src="../img/reglage.svg" alt="plus non trouvé" id="reglage" title="Parametrage du système">
            </a>
        </div>
        <div id="navTitre">
            Consultation des mesures
        </div>
        <div id="navDeconnexion">
            <?php echo $prenom . ' ' . $nom . ' | '; ?>
            <a href="Log.php">Déconnexion</a>
            <img id="modeIcon" src="../img/lune.svg" alt="Mode clair" title="Mode sombre">
        </div>
    </header>

    <div id="divSelect">
        <div id="actionGraphique">
            <div id="graphique">
                <h2>Graphique n°1</h2>
            </div>
            <div id="actions">
                <img src="../img/plus.svg" alt="plus non trouvé" id="ajoutCourbe" title="Ajouter un graphique">
                <img src="../img/moins.svg" alt="moins non trouvé" id="supprimerCourbe" title="Supprimer un graphique">
            </div>
        </div>
        <div id="selectGraphique">
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
        </div>

        <div id="selectPlage">
            <button onclick="ouvertureModel()">Plage temporelle</button>
        </div>

        <div id="infoGraphique">
        </div>
    </div> 

    <div id="divGraphiques">
        <div id="Graphique" class="graphiqueBloc">
            <img src="../img/download.svg" alt="Export" id="telechargeCourbe" title="Export CSV">
            <canvas id="monGraphique"></canvas>
        </div>
    </div>

    <div id="model">
        <span class="close" id="closePlageMain">&times;</span>
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
        <button onclick="updateChartWithTimeRange()">Valider</button>
    </div>

    <div id="ajoutCourbeDiv" class="model" style="display: none;">
        <span class="close" id="closeAjoutCourbe">&times;</span>
        <div id="selectSerre">
            <h3>Selection serre :</h3>
            <select name="lstSerreAjout" id="lstSerreAjout" data-url="getChapelles.php">
                <option value="" selected>-- Sélectionner une serre --</option>
                <?php include 'getSerres.php'; ?>
            </select>
        </div>

        <div id="selectChapelle">
            <h3>Selection chapelle :</h3>
            <select name="lstChapelleAjout" id="lstChapelleAjout" data-url="getCartes.php" disabled>
                <option value="">-- Sélectionner une chapelle --</option>
            </select>
        </div>

        <div id="selectCarte">
            <h3>Selection carte :</h3>
            <select name="lstCarteAjout" id="lstCarteAjout" data-url="getCapteurs.php" disabled>
                <option value="">-- Sélectionner une carte --</option>
            </select>
        </div>

        <div id="selectCapteur">
            <h3>Selection capteur :</h3>
            <select name="lstCapteurAjout" id="lstCapteurAjout" disabled>
                <option value="">-- Sélectionner un capteur --</option>
            </select>
        </div>

        <div id="plageTemporelleAjout">
            <label>
                <input type="checkbox" id="synchroPlageAjout" checked>
                Utiliser la même plage que la courbe principale
            </label>
        </div>
        <div id="selectPlage">
            <div class="lignePlage">
                <label for="startDateAjout">Date début :</label>
                <input type="date" id="startDateAjout" disabled>
            </div>
            <div class="lignePlage">
                <label for="startTimeAjout">Heure début :</label>
                <input type="time" id="startTimeAjout" required disabled>
            </div>
            <div class="lignePlage">
                <label for="endDateAjout">Date fin :</label>
                <input type="date" id="endDateAjout" disabled>
            </div>
            <div class="lignePlage">
                <label for="endTimeAjout">Heure fin :</label>
                <input type="time" id="endTimeAjout" required disabled>
            </div>
        </div>
        <button id="validerAjoutCourbe">Valider</button>
    </div>

    <div id="suppressionCourbeDiv" class="model" style="display: none;">
        <span class="close" id="closeSuppressionCourbe">&times;</span>
        <h3>Supprimer un graphique</h3>
        <select id="listeGraphiquesASupprimer">
            <option value="">-- Sélectionner un graphique --</option>
        </select>
        <br><br>
        <div style="display: flex; justify-content: center; gap: 10px;">
            <button id="confirmerSuppressionGraphique">Supprimer</button>
            <button id="annulerSuppressionGraphique">Annuler</button>
        </div>
    </div>

    <script src="../js/Fonctions.js"></script>
    <script src="../js/Consultation.js"></script>
</body>
</html>