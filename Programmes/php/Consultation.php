<?php
session_start(); // Démarre la session PHP
require "connectDB.php"; // Inclusion de la connexion à la base de données

if (!isset($_SESSION['login']) || $_SESSION['fonction'] !== 'Administrateur') {
    header('Location: Log.php'); // Redirection si l'utilisateur n'est pas connecté ou non admin
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Deconnexion'])) {
    session_destroy(); // Destruction de la session si demande de déconnexion
    header('Location: Log.php'); // Redirection vers la page de login
    exit;
}

try {
    $req = $bdd->prepare("SELECT Prenom, Nom FROM utilisateur WHERE Login = :username"); // Préparation de la requête utilisateur
    $req->execute([':username' => $_SESSION['login']]); // Exécution avec paramètre de session
    $user = $req->fetch(PDO::FETCH_ASSOC); // Récupération des données utilisateur

    if ($user) {
        $prenom = $user['Prenom']; // Assignation du prénom
        $nom = $user['Nom']; // Assignation du nom
    } else {
        error_log("Utilisateur non trouvé : " . $_SESSION['login']); // Log si utilisateur introuvable
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des informations utilisateur : " . $e->getMessage()); // Gestion d'erreur
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"> <!-- Encodage UTF-8 -->
    <title>Consultation des Mesures</title> 
    <link href="../css/Consultation.css" rel="stylesheet" type="text/css"> <!-- Feuille de style -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Librairie Chart.js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
    <script>
        const utilisateurNomComplet = "<?php echo addslashes($prenom . ' ' . $nom); ?>"; // Variable JS avec le nom complet
    </script>
</head>
<body>
    <header class="navBar"> <!-- Barre de navigation -->
        <div id="navAutre">
            <a href="Parametrage.php">
                <img src="../img/reglage.svg" alt="plus non trouvé" id="reglage" title="Parametrage du système"> <!-- Lien vers la page de paramétrage -->
            </a>
        </div>
        <div id="navTitre">
            Consultation des mesures <!-- Titre principal -->
        </div>
        <div id="navDeconnexion">
            <?php echo $prenom . ' ' . $nom . ' | '; ?> <!-- Affichage du nom -->
            <a href="Log.php">Déconnexion</a> <!-- Lien de déconnexion -->
            <img id="modeIcon" src="../img/lune.svg" alt="Mode clair" title="Mode sombre"> <!-- Icône mode sombre -->
        </div>
    </header>

    <div id="divSelect"> <!-- Bloc principal de sélection -->
        <div id="actionGraphique">
            <div id="graphique">
                <h2>Graphique n°1</h2> <!-- Titre du graphique principal -->
            </div>
            <div id="actions">
                <img src="../img/plus.svg" alt="plus non trouvé" id="ajoutCourbe" title="Ajouter un graphique"> <!-- Ajout de courbe -->
                <img src="../img/moins.svg" alt="moins non trouvé" id="supprimerCourbe" title="Supprimer un graphique"> <!-- Suppression de courbe -->
            </div>
        </div>
        <div id="selectGraphique">
            <div id="selectSerre">
                <h3>Selection serre :</h3>
                <select name="lstSerre" id="lstSerre" data-url="getChapelles.php"> <!-- Liste des serres -->
                    <option value="" selected>-- Sélectionner une serre --</option>
                    <?php include 'getSerres.php'; ?> <!-- Remplissage dynamique -->
                </select>
            </div>

            <div id="selectChapelle">
                <h3>Selection chapelle :</h3>
                <select name="lstChapelle" id="lstChapelle" data-url="getCartes.php" disabled> <!-- Liste chapelles dépendante -->
                    <option value="">-- Sélectionner une chapelle --</option>
                </select>
            </div>

            <div id="selectCarte">
                <h3>Selection carte :</h3>
                <select name="lstCarte" id="lstCarte" data-url="getCapteurs.php" disabled> <!-- Liste cartes dépendante -->
                    <option value="">-- Sélectionner une carte --</option>
                </select>
            </div>

            <div id="selectCapteur">
                <h3>Selection capteur :</h3>
                <select name="lstCapteur" id="lstCapteur" disabled> <!-- Liste capteurs dépendante -->
                    <option value="">-- Sélectionner un capteur --</option>
                </select>
            </div>
        </div>

        <div id="selectPlage">
            <button onclick="ouvertureModel()">Plage temporelle</button> <!-- Bouton pour ouvrir la plage de temps -->
        </div>

        <div id="infoGraphique">
        </div>
    </div> 

    <div id="divGraphiques">
        <div id="Graphique" class="graphiqueBloc"> <!-- Bloc graphique principal -->
            <img src="../img/download.svg" alt="Export" id="telechargeCourbe" title="Export CSV"> <!-- Export CSV -->
            <canvas id="monGraphique"></canvas> <!-- Canvas pour Chart.js -->
        </div>
    </div>

    <div id="model"> <!-- Modale pour la plage temporelle principale -->
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
        <button onclick="updateChartWithTimeRange()">Valider</button> <!-- Bouton de validation -->
    </div>

    <div id="ajoutCourbeDiv" class="model" style="display: none;"> <!-- Modale ajout courbe -->
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
                Utiliser la même plage que la courbe principale <!-- Synchronisation des plages -->
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
        <button id="validerAjoutCourbe">Valider</button> <!-- Bouton ajout courbe -->
    </div>

    <div id="suppressionCourbeDiv" class="model" style="display: none;"> <!-- Modale suppression courbe -->
        <span class="close" id="closeSuppressionCourbe">&times;</span>
        <h3>Supprimer un graphique</h3>
        <select id="listeGraphiquesASupprimer">
            <option value="">-- Sélectionner un graphique --</option> <!-- Liste déroulante des courbes -->
        </select>
        <br><br>
        <div style="display: flex; justify-content: center; gap: 10px;">
            <button id="confirmerSuppressionGraphique">Supprimer</button> <!-- Confirmation suppression -->
            <button id="annulerSuppressionGraphique">Annuler</button> <!-- Annulation suppression -->
        </div>
    </div>

    <script src="../js/Fonctions.js"></script> <!-- Fonctions JS -->
    <script src="../js/Consultation.js"></script> <!-- Script principal -->
</body>
</html>