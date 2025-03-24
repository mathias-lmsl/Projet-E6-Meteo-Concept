<?php 
session_start();
require "connectDB.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Deconnexion'])) {
    session_destroy();
    header('Location: Log.php');
    exit;
}

function getColumnNames($bdd, $tableName) {
    $stmt = $bdd->prepare("DESCRIBE " . $tableName);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramétrage du système</title>
    <link href="../css/Parametrage.css" rel="stylesheet" type="text/css">
</head>
<body>
    <header class="navBar">
        <div id="navAutre">
            <a href="Consultation.php">
                <img src="../img/graphe.svg" alt="plus non trouvé" id="graphe" title="Consultation des mesures">
            </a>
        </div>
        <div id="navTitre">Paramétrage du système</div>
        <div id="navDeconnexion">
            <?= htmlspecialchars($prenom . ' ' . $nom) ?> | <a href="Log.php">Déconnexion</a>
        </div>
    </header>

    <div class="container">
        <div class="selectionTable">
            <button id="selectSerre">Serres</button>
            <button id="selectChapelle">Chapelles</button>
            <button id="selectCarte">Cartes</button>
            <button id="selectCapteur">Capteurs</button>
            <button id="selectUtilisateur">Utilisateurs</button>
        </div>

        <div class="tabParametrage">
            <div class="divTableau">
                <div id="enteteTab">
                    <h2>Test</h2>
                    <div id="recherche">
                        <img src="../img/recherche.svg" alt="recherche non trouvé" id="imgRecherche" title="Recherche">
                        <input type="text" id="searchInput" placeholder="Rechercher...">
                    </div>
                </div>
                <div id="tab">
                    <table>
                        <thead><tr></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modale dynamique pour Ajout / Modification -->
    <div id="modalOverlay" class="modal" style="display: none;">
        <form id="modalDynamicForm" class="modal-form">
            <h2 id="modalTitle"></h2>

            <!-- Champs dynamiques générés par JavaScript -->
            <div id="modalFieldsContainer"></div>

            <div class="modal-buttons">
                <button type="submit" id="modalSubmitBtn">Valider</button>
                <button type="button" id="modalCancelBtn">Annuler</button>
            </div>
        </form>
    </div>

    <script type="module" src="../js/modals.js"></script>
    <script type="module" src="../js/Parametrage.js"></script>
</body>
</html>