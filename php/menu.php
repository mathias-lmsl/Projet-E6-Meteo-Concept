<link rel="stylesheet" href="../includes/css/style.css?v=1.2" type="text/css" />
<link rel="stylesheet" href="../includes/css/stylemenu.css?v=1.2" type="text/css" />

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu de navigation</title>
    <script src="../javascript/menu.js" defer></script>
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

        <h1>Menu de naviguation</h1>
        <br><br>
        <button id="pageConsultation"><img src="../includes/img/graph-icon.svg" id="icon-consult" alt="Icone graphique" class="icon"> Page de consultation<br><br></button>
        <br><br> 
        <button id="pageCreationQR"><img src="../includes/img/qrcode-icon.svg" id="icon-qr" alt="Icone QR code" class="icon"> Page de création de QR code<br><br></button> 
    </div>

</body>
</html>
