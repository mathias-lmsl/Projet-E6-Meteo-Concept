<?php
require "../config/session.php";
require "../config/databaseadmin.php";

if (!isset($_SESSION['user'])) {
    header('Location: index.php?page=1');
    exit;
}

?>
<link rel="stylesheet" href="../includes/css/style.css" type="text/css" />
<link rel="stylesheet" href="../includes/css/stylecreationqrcode.css" type="text/css" />

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
    <title>Générateur de QR Code</title>
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

        <h1>Générateur de QR Code</h1>

        <label for="lstSerre">Sélectionnez une serre :</label>
        <select name="lstSerre" id="lstSerre">
            <option value="">-- Choisissez une serre --</option>
            <?php
            $stmt = $bdd->prepare('SELECT * FROM serre;');
            $stmt->execute();
            while ($donnees = $stmt->fetch()) {
                echo '<option value="'.$donnees['IdSerre'].'">'.$donnees['Nom'].'</option>';
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

        
        <div id="qrCodeContainer"></div> <!-- Emplacement du QR code qui sera crée -->
        
        <div id="Impression"></div> <!-- Emplacement du bouton imprimer -->

        <a href="logout.php?page=1">Déconnexion</a>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const lstSerre = document.getElementById("lstSerre");
            const lstChapelle = document.getElementById("lstChapelle");
            const lstCarte = document.getElementById("lstCarte");
            const generateQR = document.getElementById("generateQR");
            const qrCodeContainer = document.getElementById("qrCodeContainer");
            const Impression = document.getElementById("Impression");
            const gererQrCode = document.getElementById("gererQrCode");


            lstSerre.addEventListener("change", function () {
                const serreId = this.value;
                lstChapelle.innerHTML = '<option value="">-- Sélectionnez une serre d\'abord --</option>';
                lstChapelle.disabled = true;
                lstCarte.innerHTML = '<option value="">-- Sélectionnez une chapelle d\'abord --</option>';
                lstCarte.disabled = true;
                generateQR.disabled = true;

                if (serreId) {
                    fetch("get_chapelles.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "serre_id=" + serreId
                    })
                    .then(response => response.text())
                    .then(data => {
                        lstChapelle.innerHTML = data;
                        lstChapelle.disabled = false;
                    });
                }
            });

            lstChapelle.addEventListener("change", function () {
                const chapelleId = this.value;
                lstCarte.innerHTML = '<option value="">-- Sélectionnez une chapelle d\'abord --</option>';
                lstCarte.disabled = true;
                generateQR.disabled = true;

                if (chapelleId) {
                    fetch("get_cartes.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "chapelle_id=" + chapelleId
                    })
                    .then(response => response.text())
                    .then(data => {
                        lstCarte.innerHTML = data;
                        lstCarte.disabled = false;
                    });
                }
            });

            lstCarte.addEventListener("change", function () {
                generateQR.disabled = this.value === "";
            });

            generateQR.addEventListener("click", function () {
                const carteId = lstCarte.value;
                if (carteId) {
                    fetch("generate_qr.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "carte_id=" + carteId
                    })
                    .then(response => response.text())
                    .then(data => {
                        qrCodeContainer.innerHTML = data;
                        Impression.innerHTML = '<button id="imprimer">Imprimer</button>';
                        const imprimer = document.getElementById("imprimer");
                        imprimer.addEventListener("click", function () {
                            window.print();                 
                        });
                    });
                }
            });
        });
    </script>
</body>
</html>
