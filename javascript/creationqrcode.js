// Fonction pour basculer entre le mode sombre et clair
function toggleDarkMode() {
    document.body.classList.toggle('darkmode');
    const isDark = document.body.classList.contains('darkmode');
    localStorage.setItem('darkmode', isDark ? '1' : '0');
}

// Fonction pour garder le mode sombre après le rechargement de la page
window.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('darkmode') === '1') {
        document.body.classList.add('darkmode');
    }
});

// Fonction pour se déconnecter
function logout() {
    window.location.href = "logout.php?page=1";
}

document.addEventListener("DOMContentLoaded", function () {
    const lstSerre = document.getElementById("lstSerre");
    const lstChapelle = document.getElementById("lstChapelle");
    const lstCarte = document.getElementById("lstCarte");
    const generateQR = document.getElementById("generateQR");
    const qrCodeContainer = document.getElementById("qrCodeContainer");
    const Impression = document.getElementById("Impression");

    // Initialisation des listes déroulantes lors du choix de la serre
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
    // Initialisation des listes déroulantes lors du choix de la chapelle
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

    // Activation du bouton de génération de QR code lorsque la carte est sélectionnée
    lstCarte.addEventListener("change", function () {
        generateQR.disabled = this.value === "";
    });

    // Fonction pour générer le QR code quand le bouton est actionné
    generateQR.addEventListener("click", function () {
        const DevEui = lstCarte.value;
        if (DevEui) {
            // On envoie une requête pour générer le QR code en Ajax
            fetch("generate_qr.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "DevEui=" + DevEui
            })
            .then(response => response.text())
            .then(data => {
                // On affiche le QR code dans le conteneur et on ajoute le bouton d'impression
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