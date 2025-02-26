function ouvertureModel() {
    document.getElementById('model').style.display = 'block';
}

function fermetureModel() {
    document.getElementById('model').style.display = 'none';
}

// Fonction générique pour charger les options dans un select
function loadOptions(url, selectId, defaultText) {
    var select = document.getElementById(selectId);
    select.innerHTML = `<option value="">${defaultText}</option>`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            data.forEach(function(item) {
                var option = document.createElement("option");
                option.value = item.IdChapelle || item.IdCarte || item.IdCapteur;
                option.textContent = item.Commentaire || item.NumSerie || (item.Marque + ' ' + item.Reference);
                select.appendChild(option);
            });
        })
        .catch(error => console.error("Erreur lors du chargement des données:", error));
}

// Fonction appelée au changement de la serre
function loadChapelles() {
    var serreId = document.getElementById("lstSerre").value;
    select.innerHTML = `<option value="">${defaultText}</option>`;
    loadOptions(`Consultation.php?type=chapelles&serreId=${serreId}`, "lstChapelle", "Sélectionner une chapelle");
    document.getElementById("lstCarte").innerHTML = '<option value="">Sélectionner une carte</option>';
    document.getElementById("lstCapteur").innerHTML = '<option value="">Sélectionner un capteur</option>';
}

// Fonction appelée au changement de la chapelle
function loadCartes() {
    var chapelleId = document.getElementById("lstChapelle").value;
    select.innerHTML = `<option value="">${defaultText}</option>`;
    loadOptions(`Consultation.php?type=cartes&chapelleId=${chapelleId}`, "lstCarte", "Sélectionner une carte");
    document.getElementById("lstCapteur").innerHTML = '<option value="">Sélectionner un capteur</option>';
}

// Fonction appelée au changement de la carte
function loadCapteurs() {
    var carteId = document.getElementById("lstCarte").value;
    select.innerHTML = `<option value="">${defaultText}</option>`;
    loadOptions(`Consultation.php?type=capteurs&carteId=${carteId}`, "lstCapteur", "Sélectionner un capteur");
}