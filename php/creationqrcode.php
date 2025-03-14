<?php
require "../config/session.php";
require "../config/databaseadmin.php";

if (!isset($_SESSION['user'])) {
    header('Location: index.php?id='.$_GET["id"].'&page=1');
    exit;
}

?>
<link rel="stylesheet" href="../includes/style.css" type="text/css" />
<link rel="stylesheet" href="../includes/stylecreationqrcode.css" type="text/css" />

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
    <!--
    <style>
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #87CEEB, #4682B4);
            padding: 20px;
            box-sizing: border-box;
        }

        .container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            opacity: 0.9;
        }

        select, button {
            width: 90%;
            padding: 10px;
            border-radius: 10px;
            margin-top: 10px;
            font-size: 15px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
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

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
        }

        /*---------------------------Nuage---------------------------*/
        .cloud { /*Bas nuage*/
            position: absolute;
            width: 150px;
            height: 80px;
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

        .cloud::before {/*Haut gauche*/
            width: 85px;
            height: 80px;
            top: -35px;
            left: 10px;
        }

        .cloud::after { /*Haut droit*/ 
            width: 100px;
            height: 60px;
            top: -25px;
            right: 10px;
        }

        @keyframes float { /*Deplacement des nuages*/
            0% { transform: translateX(-30px); }
            100% { transform: translateX(30px); }
        }

        /* Styles pour l'impression */
        @media print {
            body * {
                visibility: hidden;
            }
            #qrCodeContainer, #qrCodeContainer * {
                visibility: visible;
            }
            #qrCodeContainer {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
            }
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
