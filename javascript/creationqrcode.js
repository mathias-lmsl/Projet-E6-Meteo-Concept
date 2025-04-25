// Fonction pour basculer entre le mode sombre et clair
function toggleDarkMode() {
    document.body.classList.toggle('darkmode');
}

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
        const DevEui = lstCarte.value;
        if (DevEui) {
            fetch("generate_qr.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "DevEui=" + DevEui
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