// Fonction pour afficher le modèle (modal)
function ouvertureModel() {
    document.getElementById('model').style.display = 'block';
}

// Fonction pour masquer le modèle (modal)
function fermetureModel() {
    document.getElementById('model').style.display = 'none';
}

function updateChart(capteurId, canvas, startDate = null, startTime = null, endDate = null, endTime = null) {
    if (!capteurId) {
        console.warn("Aucun capteur sélectionné.");
        return;
    }

    // Fonction générique pour récupérer les mesures
    function getMesures(capteurId) {
        let url = `getMesures.php?capteur_id=${capteurId}`;
        if (startDate && startTime && endDate && endTime) {
            url += `&startDate=${startDate}&startTime=${startTime}&endDate=${endDate}&endTime=${endTime}`;
        }
        return fetch(url).then(response => response.json());
    }

    // Récupérer les infos du capteur
    fetch(`getCapteurInfo.php?capteur_id=${capteurId}`)
        .then(response => response.json())
        .then(capteurInfo => {
            const grandeur = capteurInfo.GrandeurCapt;
            const unite = capteurInfo.Unite;

            // Récupérer les mesures
            getMesures(capteurId).then(data => {
                if (!Array.isArray(data.mesures)) {
                    console.error("Données invalides :", data);
                    return;
                }

                const labels = data.mesures.map(m => m.Horodatage);
                const values = data.mesures.map(m => parseFloat(m.Valeur));

                renderChart(canvas, labels, [{
                    label: `${grandeur} (${unite})`,
                    data: values,
                    borderColor: 'rgb(231, 57, 57)',
                    tension: 0.1
                }], unite);

                updateInfoGraphique(values, unite);
            });
        });
}
  
// Fonction pour créer/mettre à jour un graphique sur un canvas
function renderChart(canvas, labels, datasets, unite) {
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;

    // Ajuster la résolution
    canvas.style.height = '400px';
    canvas.width = canvas.offsetWidth * dpr;
    canvas.height = 400 * dpr;
    ctx.scale(dpr, dpr);

    // Détruire l'ancien graphique s'il existe
    if (canvas.myChart) {
        canvas.myChart.destroy();
    }

    // Créer un nouveau graphique
    canvas.myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: unite
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        font: {
                            family: 'Arial',
                            size: 12
                        }
                    }
                }
            }
        }
    });
}

// Fonction pour mettre à jour les informations du graphique (actuelle, min, max, moyenne)
function updateInfoGraphique(values, unite) {
    if (values && values.length > 0) {
        const actuelle = values[values.length - 1];
        const min = Math.min(...values);
        const max = Math.max(...values);
        const moyenne = values.reduce((acc, val) => acc + val, 0) / values.length;

        document.getElementById('infoGraphique').innerHTML = `
        <span style="display: inline-block; width: 24%; text-align: center;">Actuelle : ${actuelle.toFixed(2)} ${unite}</span></br>
        <span style="display: inline-block; width: 24%; text-align: center;">Minimum : ${min.toFixed(2)} ${unite}</span></br>
        <span style="display: inline-block; width: 24%; text-align: center;">Maximum : ${max.toFixed(2)} ${unite}</span></br>
        <span style="display: inline-block; width: 24%; text-align: center;">Moyenne : ${moyenne.toFixed(2)} ${unite}</span></br>`;
    } else {
        document.getElementById('infoGraphique').innerHTML = "Aucune donnée à afficher.";
    }
}

// Fonction pour mettre à jour le graphique avec les filtres de temps sélectionnés
function updateChartWithTimeRange() {
    const test = null;
    const capteurId = document.getElementById('lstCapteur').value;
    const startDate = document.getElementById('startDate').value;
    const startTime = document.getElementById('startTime').value;
    const endDate = document.getElementById('endDate').value;
    const endTime = document.getElementById('endTime').value;
    
    updateChart(capteurId, document.getElementById('monGraphique'), startDate, startTime, endDate, endTime);
    fermetureModel();
}

function definirPlageTemporelleParDefaut() {
    var maintenant = new Date();
    var debut = new Date(maintenant);
    debut.setDate(maintenant.getDate() - 1); // 24 heures avant

    // Formater les dates et heures pour les champs de saisie
    var dateDebut = debut.toISOString().slice(0, 10);
    var heureDebut = debut.toTimeString().slice(0, 5);
    var dateFin = maintenant.toISOString().slice(0, 10);
    var heureFin = maintenant.toTimeString().slice(0, 5);

    // Définir les valeurs dans les champs de saisie
    document.getElementById('startDate').value = dateDebut;
    document.getElementById('startTime').value = heureDebut;
    document.getElementById('endDate').value = dateFin;
    document.getElementById('endTime').value = heureFin;

    // Mettre à jour le graphique avec la plage temporelle par défaut
    updateChartWithTimeRange();
}

function gererSelectionSerreAjout(serreId, chapelleId, carteId, capteurId) {
    document.getElementById(serreId).addEventListener('change', function () {
        var serreValue = this.value;
        if (serreValue) {
            fetch('getChapelles.php?serre_id=' + serreValue)
                .then(response => response.json())
                .then(chapelles => {
                    mettreAJourSelect(chapelleId, chapelles, 'IdChapelle', 'Nom');
                    reinitialiserSelects([carteId, capteurId]);
                    activerSelect(chapelleId);
                    desactiverSelects([carteId, capteurId]);
                });
        } else {
            reinitialiserSelects([chapelleId, carteId, capteurId]);
            desactiverSelects([chapelleId, carteId, capteurId]);
        }
    });
}

// Gestionnaire d'événement pour le chargement du DOM
document.addEventListener('DOMContentLoaded', function () {
    
    // Gestionnaire d'événement pour le changement de sélection de la serre
    document.getElementById('lstSerre').addEventListener('change', function () {
        var serreId = this.value;
        if (serreId) {
            // Récupérer les chapelles associées à la serre sélectionnée
            fetch('getChapelles.php?serre_id=' + serreId)
                .then(response => response.json())
                .then(chapelles => {
                    var chapelleSelect = document.getElementById('lstChapelle');
                    chapelleSelect.innerHTML = '<option value="">-- Sélectionnez une chapelle --</option>';
                    chapelles.forEach(chapelle => {
                        chapelleSelect.innerHTML += '<option value="' + chapelle.IdChapelle + '">' + chapelle.Nom + '</option>';
                    });
                    // Réinitialiser les sélecteurs de carte et de capteur
                    document.getElementById('lstCarte').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
                    document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
                    // Activer le sélecteur de chapelle et désactiver les sélecteurs de carte et de capteur
                    document.getElementById('lstChapelle').removeAttribute('disabled');
                    document.getElementById('lstCarte').setAttribute('disabled', true);
                    document.getElementById('lstCapteur').setAttribute('disabled', true);
                });
        } else {
            // Réinitialiser tous les sélecteurs et les désactiver
            document.getElementById('lstChapelle').innerHTML = '<option value="">-- Sélectionnez une chapelle --</option>';
            document.getElementById('lstCarte').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
            document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            document.getElementById('lstChapelle').setAttribute('disabled', true);
            document.getElementById('lstCarte').setAttribute('disabled', true);
            document.getElementById('lstCapteur').setAttribute('disabled', true);
        }
    });

    // Gestionnaire d'événement pour le changement de sélection de la chapelle
    document.getElementById('lstChapelle').addEventListener('change', function () {
        // Récupère la valeur (l'ID) de la chapelle sélectionnée dans la liste déroulante.
        var chapelleId = this.value;
        // Vérifie si une chapelle est sélectionnée.
        if (chapelleId) {
            // Envoie une requête au serveur pour récupérer les cartes associées à la chapelle sélectionnée.
            fetch('getCartes.php?chapelle_id=' + chapelleId)
                // Convertit la réponse en JSON.
                .then(response => response.json())
                // Traite les données des cartes.
                .then(cartes => {
                    // Récupère l'élément select pour les cartes.
                    var carteSelect = document.getElementById('lstCarte');
                    // Réinitialise la liste des cartes avec une option par défaut.
                    carteSelect.innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
                    // Ajoute chaque carte comme une option dans la liste déroulante.
                    cartes.forEach(carte => {
                        carteSelect.innerHTML += '<option value="' + carte.DevEui + '">' + carte.Nom + '</option>';
                    });
                    // Réinitialise la liste des capteurs avec une option par défaut.
                    document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
                    // Active la liste déroulante des cartes.
                    document.getElementById('lstCarte').removeAttribute('disabled');
                    // Désactive la liste déroulante des capteurs.
                    document.getElementById('lstCapteur').setAttribute('disabled', true);
                });
        } else {
            // Si aucune chapelle n'est sélectionnée, réinitialise et désactive les listes de cartes et de capteurs.
            document.getElementById('lstCarte').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
            document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            document.getElementById('lstCarte').setAttribute('disabled', true);
            document.getElementById('lstCapteur').setAttribute('disabled', true);
        }
    });

    document.getElementById('lstCarte').addEventListener('change', function () {
        // Récupère la valeur (l'ID) de la carte sélectionnée.
        var carteId = this.value;
        // Vérifie si une carte est sélectionnée.
        if (carteId) {
            // Envoie une requête au serveur pour récupérer les capteurs associés à la carte sélectionnée.
            fetch('getCapteurs.php?carte_id=' + carteId)
                .then(response => response.json())
                .then(capteurs => {
                    // Récupère l'élément select pour les capteurs.
                    var capteurSelect = document.getElementById('lstCapteur');
                    // Réinitialise la liste des capteurs.
                    capteurSelect.innerHTML = '<option value="">Sélectionnez un capteur</option>';
                    // Ajoute chaque capteur comme une option dans la liste déroulante.
                    capteurs.forEach(capteur => {
                        capteurSelect.innerHTML += '<option value="' + capteur.IdCapteur + '">' + capteur.Nom + '</option>';
                    });
                    // Active la liste déroulante des capteurs.
                    document.getElementById('lstCapteur').removeAttribute('disabled');
                });
        } else {
            // Si aucune carte n'est sélectionnée, réinitialise et désactive la liste des capteurs.
            document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            document.getElementById('lstCapteur').setAttribute('disabled', true);
        }
    });

    // Listeners sur les selects
    document.getElementById('lstCapteur').addEventListener('change', function () {
        const capteurId = this.value;
        const startDate = document.getElementById('startDate').value;
        const startTime = document.getElementById('startTime').value;
        const endDate = document.getElementById('endDate').value;
        const endTime = document.getElementById('endTime').value;
        updateChart(capteurId, document.getElementById('monGraphique'), startDate, startTime, endDate, endTime);
    });

    // Sélection des valeurs par défaut pour la plage temporelle
    definirPlageTemporelleParDefaut();
    
    var serreIdParDefaut = 1;
    var chapelleIdParDefaut = 1;
    var carteDevEuiParDefaut = "0004a30b00216c4c";
    var capteurIdParDefaut = 1;

    // Sélection des valeurs par défaut
    selectionnerParDefaut('lstSerre', serreIdParDefaut, function () {
        selectionnerParDefaut('lstChapelle', chapelleIdParDefaut, function () {
            selectionnerParDefaut('lstCarte', carteDevEuiParDefaut, function () {
                selectionnerParDefaut('lstCapteur', capteurIdParDefaut);
            });
        });
    });

    // Fonction pour sélectionner une valeur dans une liste déroulante et exécuter une fonction de rappel
    function selectionnerParDefaut(selectId, valeur, callback) {
        var select = document.getElementById(selectId);
        select.value = valeur;
        select.dispatchEvent(new Event('change')); // Déclenche l'événement 'change' pour mettre à jour les listes suivantes
        if (callback) {
            // Exécute la fonction de rappel après un court délai pour s'assurer que les listes sont mises à jour
            setTimeout(callback, 100);
        }
        updateChartWithTimeRange();
    }

    document.getElementById('supprimerCourbe').addEventListener('click', function () {
        // Supprime la dernière courbe ajoutée au graphique.
        supprimerCourbe();
    });

    function supprimerCourbe() {
        // Vérifie si le graphique existe et a plus d'un dataset (courbe).
        if (window.myLine && window.myLine.data.datasets.length > 1) {
            // Supprime le dernier dataset ajouté.
            window.myLine.data.datasets.pop();
            // Met à jour le graphique.
            window.myLine.update();
        } else {
            alert("Vous ne pouvez pas supprimer la courbe principale.");
        }
    }

    function ajouterDonneesCourbe(capteurId) {
        // Récupère les mesures du capteur à partir du serveur.
        fetch('getMesures.php?capteur_id=' + capteurId)
            // Convertit la réponse en JSON.
            .then(response => response.json())
            // Traite les données des mesures.
            .then(data => {
                // Vérifie s'il y a une erreur dans les données.
                if (data.error) {
                    // Affiche l'erreur dans la console et sort de la fonction.
                    console.error("Erreur lors de la récupération des mesures :", data.error);
                    return;
                }

                // Extrait les horodatages et les valeurs des mesures.
                const labels = data.map(mesure => mesure.Horodatage);
                const values = data.map(mesure => parseFloat(mesure.Valeur));

                // Vérifie si les données sont valides.
                if (labels && values && labels.length > 0 && values.length > 0) {
                    // Récupère les informations du capteur (grandeur et unité).
                    fetch('getCapteurInfo.php?capteur_id=' + capteurId)
                        // Convertit la réponse en JSON.
                        .then(response => response.json())
                        // Traite les informations du capteur.
                        .then(capteurInfo => {
                            // Extrait la grandeur et l'unité du capteur.
                            const grandeur = capteurInfo.GrandeurCapt;
                            const unite = capteurInfo.Unite;

                            // Ajoute un nouveau dataset au graphique existant.
                            window.myLine.data.datasets.push({
                                label: `${grandeur} (${unite})`,
                                data: values,
                                borderColor: getRandomColor(), // Génère une couleur aléatoire pour la courbe.
                                tension: 0.1
                            });

                            // Met à jour le graphique avec le nouveau dataset.
                            window.myLine.update();
                        })
                        // Gestion des erreurs lors de la récupération des informations du capteur.
                        .catch(error => {
                            console.error("Erreur lors de la récupération des informations du capteur :", error);
                        });
                } else {
                    // Affiche une erreur si les données du graphique sont invalides ou vides.
                    console.error("Données de graphique invalides ou vides.");
                }
            })
            // Gestion des erreurs lors de la récupération des données.
            .catch(error => {
                console.error("Erreur lors de la récupération des données :", error);
            });
    }


    function getRandomColor() {
        // Génère une couleur hexadécimale aléatoire.
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    document.getElementById('ajoutCourbe').addEventListener('click', function () {
        // Affiche la div pour ajouter une courbe et masque le menu d'ajout de courbe.
        document.getElementById('ajoutCourbeDiv').style.display = 'block';
    });

    document.getElementById('closeAjoutCourbe').addEventListener('click', function () {
        // Masque la div pour ajouter une courbe.
        document.getElementById('ajoutCourbeDiv').style.display = 'none';
    });

    document.getElementById('validerAjoutCourbe').addEventListener('click', function () {
        // Récupère l'ID du capteur sélectionné dans la liste déroulante.
        var capteurId = document.getElementById('lstCapteurAjout').value;
        // Ajoute la courbe au graphique.
        ajouterDonneesCourbe(capteurId);
        // Masque la div pour ajouter une courbe.
        document.getElementById('ajoutCourbeDiv').style.display = 'none';
    });

    // Gestionnaires d'événements pour les sélecteurs de capteurs dans ajoutCourbeDiv (Graphique n°1)
    gererSelectionSerreAjout('lstSerreAjout', 'lstChapelleAjout', 'lstCarteAjout', 'lstCapteurAjout');

    document.getElementById('closePlage').addEventListener('click', function () {
        // Masque le modèle (modal).
        document.getElementById('model').style.display = 'none';
    });

    // Gestionnaires d'événements pour les sélecteurs de capteurs dans ajoutCourbeDiv
    document.getElementById('lstSerreAjout').addEventListener('change', function () {
        // Récupère l'ID de la serre sélectionnée.
        var serreId = this.value;
        // Vérifie si une serre est sélectionnée.
        if (serreId) {
            // Envoie une requête au serveur pour récupérer les chapelles associées à la serre.
            fetch('getChapelles.php?serre_id=' + serreId)
                // Convertit la réponse en JSON.
                .then(response => response.json())
                // Traite les données des chapelles.
                .then(chapelles => {
                    // Récupère l'élément select pour les chapelles.
                    var chapelleSelect = document.getElementById('lstChapelleAjout');
                    // Réinitialise la liste des chapelles avec une option par défaut.
                    chapelleSelect.innerHTML = '<option value="">-- Sélectionnez une chapelle --</option>';
                    // Ajoute chaque chapelle comme une option dans la liste déroulante.
                    chapelles.forEach(chapelle => {
                        chapelleSelect.innerHTML += '<option value="' + chapelle.IdChapelle + '">' + chapelle.Nom + '</option>';
                    });
                    // Réinitialise les listes de cartes et de capteurs.
                    document.getElementById('lstCarteAjout').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
                    document.getElementById('lstCapteurAjout').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
                    // Active la liste déroulante des chapelles et désactive celles des cartes et des capteurs.
                    document.getElementById('lstChapelleAjout').removeAttribute('disabled');
                    document.getElementById('lstCarteAjout').setAttribute('disabled', true);
                    document.getElementById('lstCapteurAjout').setAttribute('disabled', true);
                });
        } else {
            // Si aucune serre n'est sélectionnée, réinitialise et désactive toutes les listes.
            document.getElementById('lstChapelleAjout').innerHTML = '<option value="">-- Sélectionnez une chapelle --</option>';
            document.getElementById('lstCarteAjout').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
            document.getElementById('lstCapteurAjout').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            document.getElementById('lstChapelleAjout').setAttribute('disabled', true);
            document.getElementById('lstCarteAjout').setAttribute('disabled', true);
            document.getElementById('lstCapteurAjout').setAttribute('disabled', true);
        }
    });

    document.getElementById('lstChapelleAjout').addEventListener('change', function () {
        // Récupère l'ID de la chapelle sélectionnée.
        var chapelleId = this.value;
        // Vérifie si une chapelle est sélectionnée.
        if (chapelleId) {
            // Envoie une requête au serveur pour récupérer les cartes associées à la chapelle.
            fetch('getCartes.php?chapelle_id=' + chapelleId)
                // Convertit la réponse en JSON.
                .then(response => response.json())
                // Traite les données des cartes.
                .then(cartes => {
                    // Récupère l'élément select pour les cartes.
                    var carteSelect = document.getElementById('lstCarteAjout');
                    // Réinitialise la liste des cartes avec une option par défaut.
                    carteSelect.innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
                    // Ajoute chaque carte comme une option dans la liste déroulante.
                    cartes.forEach(carte => {
                        carteSelect.innerHTML += '<option value="' + carte.DevEui + '">' + carte.Nom + '</option>';
                    });
                    // Réinitialise la liste des capteurs.
                    document.getElementById('lstCapteurAjout').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
                    // Active la liste déroulante des cartes et désactive celle des capteurs.
                    document.getElementById('lstCarteAjout').removeAttribute('disabled');
                    document.getElementById('lstCapteurAjout').setAttribute('disabled', true);
                });
        } else {
            // Si aucune chapelle n'est sélectionnée, réinitialise et désactive les listes de cartes et de capteurs.
            document.getElementById('lstCarteAjout').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
            document.getElementById('lstCapteurAjout').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            document.getElementById('lstCarteAjout').setAttribute('disabled', true);
            document.getElementById('lstCapteurAjout').setAttribute('disabled', true);
        }
    });

    document.getElementById('lstCarteAjout').addEventListener('change', function () {
        // Récupère l'ID de la carte sélectionnée.
        var carteId = this.value;
        // Vérifie si une carte est sélectionnée.
        if (carteId) {
            // Envoie une requête au serveur pour récupérer les capteurs associés à la carte.
            fetch('getCapteurs.php?carte_id=' + carteId)
                // Convertit la réponse en JSON.
                .then(response => response.json())
                // Traite les données des capteurs.
                .then(capteurs => {
                    // Récupère l'élément select pour les capteurs.
                    var capteurSelect = document.getElementById('lstCapteurAjout');
                    // Réinitialise la liste des capteurs.
                    capteurSelect.innerHTML = '<option value="">Sélectionnez un capteur</option>';
                    // Ajoute chaque capteur comme une option dans la liste déroulante.
                    capteurs.forEach(capteur => {
                        capteurSelect.innerHTML += '<option value="' + capteur.IdCapteur + '">' + capteur.Nom + '</option>';
                    });
                    // Active la liste déroulante des capteurs.
                    document.getElementById('lstCapteurAjout').removeAttribute('disabled');
                });
        } else {
            // Si aucune carte n'est sélectionnée, réinitialise et désactive la liste des capteurs.
            document.getElementById('lstCapteurAjout').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            document.getElementById('lstCapteurAjout').setAttribute('disabled', true);
        }
    });

    document.getElementById('lstCapteurAjout').addEventListener('change', function () {
        // Récupère l'ID du capteur sélectionné.
        var capteurId = this.value;
        // (Note: Cette ligne ne fait rien d'autre que de stocker l'ID du capteur,
        //  il est probable qu'elle soit utilisée ailleurs dans le code.)
    });

    function ouvertureModelAjout() {
        // Affiche le modèle (modal).
        document.getElementById('model').style.display = 'block';
    }

    document.getElementById('ajoutCourbe').addEventListener('click', function () {
    // Crée un fond sombre derrière la modale
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop';
    backdrop.id = 'ajoutCourbeBackdrop';
    document.body.appendChild(backdrop);

    // Affiche la div modale
    document.getElementById('ajoutCourbeDiv').style.display = 'block';
});

document.getElementById('closeAjoutCourbe').addEventListener('click', function () {
    // Masque la modale
    document.getElementById('ajoutCourbeDiv').style.display = 'none';
    const backdrop = document.getElementById('ajoutCourbeBackdrop');
    if (backdrop) backdrop.remove();
});

document.getElementById('validerAjoutCourbe').addEventListener('click', function () {
    // Ajout courbe logique...
    document.getElementById('ajoutCourbeDiv').style.display = 'none';
    const backdrop = document.getElementById('ajoutCourbeBackdrop');
    if (backdrop) backdrop.remove();
});

    function formaterHorodatage(horodatage) {
        const date = new Date(horodatage);
        const jour = String(date.getDate()).padStart(2, '0');
        const mois = String(date.getMonth() + 1).padStart(2, '0');
        const annee = date.getFullYear();
        const heures = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const secondes = String(date.getSeconds()).padStart(2, '0');
        return `${jour}/${mois}/${annee} ${heures}:${minutes}:${secondes}`;
    }
    
    function telechargerCSV() {
        const canvas = document.getElementById('monGraphique');
        const chart = canvas.myChart;
    
        if (!chart || !chart.data.labels || chart.data.labels.length === 0) {
            alert("Aucune donnée à télécharger.");
            return;
        }
    
        const labels = chart.data.labels;
        const dataset = chart.data.datasets[0]; // 1ère courbe uniquement
        const values = dataset.data;
    
        // Récupération des valeurs de plage temporelle
        const startDate = document.getElementById('startDate').value;
        const startTime = document.getElementById('startTime').value;
        const endDate = document.getElementById('endDate').value;
        const endTime = document.getElementById('endTime').value;
    
        // Format brut pour le nom du fichier
        const formatNom = (str) => str.replaceAll(":", "-").replaceAll("/", "-").replaceAll(" ", "_");
    
        const debut = formatNom(`${startDate} ${startTime}`);
        const fin = formatNom(`${endDate} ${endTime}`);
    
        // Nettoyage du nom du capteur pour un nom de fichier safe (sans unité)
        const nomCapteur = dataset.label.split('(')[0].trim().replace(/\s+/g, '_');
    
        // Génération du contenu CSV
        let csvContent = "Horodatage;Valeur\r\n";
        for (let i = 0; i < labels.length; i++) {
            const horodatage = formaterHorodatage(labels[i]);
            const valeur = values[i];
            csvContent += `"${horodatage}";${valeur}\r\n`;
        }
    
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
    
        // Nom du fichier : Capteur_Température_2025-03-30_08-00_à_2025-03-31_08-00.csv
        const fileName = `${nomCapteur}__${debut}__${fin}.csv`;
    
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    document.getElementById('telechargeCourbe').addEventListener('click', telechargerCSV);
});