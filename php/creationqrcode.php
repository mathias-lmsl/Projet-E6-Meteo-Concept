<?php
require "../config/session.php";
require "../config/databaseadmin.php";

if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_to'] = 'creationqrcode.php';
    header('Location: index.php');
    exit;
}

?>
<link rel="stylesheet" href="../includes/css/style.css?v=1.2" type="text/css" />
<link rel="stylesheet" href="../includes/css/stylecreationqrcode.css?v=1.2" type="text/css" />

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de QR Code</title>
    <script src="../javascript/creationqrcode.js" defer></script>
</head>
<body>
    <div class="cloud" style="top: 20%; left: 10%;"></div>
    <div class="cloud" style="top: 40%; right: 15%;"></div>
    <div class="cloud" style="top: 60%; left: 20%;"></div>
    <div class="container">

        <!-- Bouton pour basculer entre le mode sombre et clair -->
        <div class="darkmode-toggle" onclick="toggleDarkMode()">
            <img src="../includes/img/sun-darkmode.svg" id="sun-icon" alt="Mode sombre" class="icon">
            <img src="../includes/img/moon-darkmode.svg" id="moon-icon" alt="Mode clair" class="icon">
        </div>

        <div class="logout" onclick="logout()">
            <img src="../includes/img/logout-icon.svg" id="logout-icon" alt="Bouton logout noir" class="icon">
            <img src="../includes/img/logout-icon-darkmode.svg" id="logout-icon-darkmode" alt="Bouton logout blanc" class="icon">
        </div>

        <h1>Générateur de QR Code</h1>

        <label for="lstSerre">Sélectionnez une serre :</label>
        <select name="lstSerre" id="lstSerre">
            <option value="">-- Choisissez une serre --</option>
            <?php
            $stmt = $bdd->prepare('SELECT * FROM serre;');
            $stmt->execute();
            while ($donnees = $stmt->fetch()) {
                echo '<option value="'.htmlspecialchars($donnees['IdSerre']).'">'.htmlspecialchars($donnees['Nom']).'</option>';
            }
            $stmt->closeCursor();
            ?>
        </select>
        <br><br>
        <label for="lstChapelle">Sélectionnez une chapelle :</label>
        <select name="lstChapelle" id="lstChapelle" disabled>
            <option value="">-- Sélectionnez une serre d'abord --</option>
        </select>
        <br><br>
        <label for="lstCarte">Sélectionnez une carte :</label>
        <select name="lstCarte" id="lstCarte" disabled>
            <option value="">-- Sélectionnez une chapelle d'abord --</option>
        </select>
        <br><br>
        <button id="generateQR" disabled>Générer le QR Code</button> 
        <!-- Bouton de génération de QR code, s'active après choix d'une station--> 

        <div id="qrCodeContainer"></div> <!-- Emplacement du QR code qui sera crée -->
        
        <div id="Impression"></div> <!-- Emplacement du bouton imprimer -->

    </div>

</body>
</html>
