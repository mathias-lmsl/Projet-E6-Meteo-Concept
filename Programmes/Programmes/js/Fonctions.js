function ouvertureModel() {
    document.getElementById('model').style.display = 'block';
}

function fermetureModel() {
    document.getElementById('model').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('lstSerre').addEventListener('change', function() {
        var serreId = this.value;
        if (serreId) {
            fetch('getChapelles.php?serre_id=' + serreId)
                .then(response => response.json())
                .then(chapelles => {
                    var chapelleSelect = document.getElementById('lstChapelle');
                    chapelleSelect.innerHTML = '<option value="">-- Sélectionnez une chapelle --</option>';
                    chapelles.forEach(chapelle => {
                        chapelleSelect.innerHTML += '<option value="' + chapelle.IdChapelle + '">' + chapelle.Nom + '</option>';
                    });
                    // Réinitialiser les sélecteurs suivants
                    document.getElementById('lstCarte').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
                    document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
                    // Activer le sélecteur de chapelle
                    document.getElementById('lstChapelle').removeAttribute('disabled');
                    // Désactiver les sélecteurs suivants
                    document.getElementById('lstCarte').setAttribute('disabled', true);
                    document.getElementById('lstCapteur').setAttribute('disabled', true);
                });
        } else {
            // Réinitialiser tous les sélecteurs
            document.getElementById('lstChapelle').innerHTML = '<option value="">-- Sélectionnez une chapelle --</option>';
            document.getElementById('lstCarte').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
            document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            // Désactiver tous les sélecteurs
            document.getElementById('lstChapelle').setAttribute('disabled', true);
            document.getElementById('lstCarte').setAttribute('disabled', true);
            document.getElementById('lstCapteur').setAttribute('disabled', true);
        }
    });

    document.getElementById('lstChapelle').addEventListener('change', function() {
        var chapelleId = this.value;
        if (chapelleId) {
            fetch('getCartes.php?chapelle_id=' + chapelleId)
                .then(response => response.json())
                .then(cartes => {
                    var carteSelect = document.getElementById('lstCarte');
                    carteSelect.innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
                    cartes.forEach(carte => {
                        carteSelect.innerHTML += '<option value="' + carte.IdCarte + '">' + carte.Nom + '</option>';
                    });
                    // Réinitialiser le sélecteur suivant
                    document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
                    // Activer le sélecteur de carte
                    document.getElementById('lstCarte').removeAttribute('disabled');
                    // Désactiver le sélecteur suivant
                    document.getElementById('lstCapteur').setAttribute('disabled', true);
                });
        } else {
            // Réinitialiser les sélecteurs suivants
            document.getElementById('lstCarte').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
            document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            // Désactiver les sélecteurs suivants
            document.getElementById('lstCarte').setAttribute('disabled', true);
            document.getElementById('lstCapteur').setAttribute('disabled', true);
        }
    });

    document.getElementById('lstCarte').addEventListener('change', function() {
        var carteId = this.value;
        if (carteId) {
            fetch('getCapteurs.php?carte_id=' + carteId)
                .then(response => response.json())
                .then(capteurs => {
                    var capteurSelect = document.getElementById('lstCapteur');
                    capteurSelect.innerHTML = '<option value="">Sélectionnez un capteur</option>';
                    capteurs.forEach(capteur => {
                        capteurSelect.innerHTML += '<option value="' + capteur.IdCapteur + '">' + capteur.Nom + '</option>';
                    });
                    // Activer le sélecteur de capteur
                    document.getElementById('lstCapteur').removeAttribute('disabled');
                });
        } else {
            // Réinitialiser le sélecteur suivant
            document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            // Désactiver le sélecteur suivant
            document.getElementById('lstCapteur').setAttribute('disabled', true);
        }
    });
});