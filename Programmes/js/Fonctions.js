// Fonctions.js
// --- Gestion des modales ---
function ouvertureModel() {
    document.getElementById('model').style.display = 'block';
}

function fermetureModel() {
    document.getElementById('model').style.display = 'none';
}

function ouvertureModelAjout() {
    document.getElementById('model').style.display = 'block';
}

// --- Récupération des mesures ---
function getMesures(capteurId, startDate = null, startTime = null, endDate = null, endTime = null) {
    let url = `getMesures.php?capteur_id=${capteurId}`;
    if (startDate && startTime && endDate && endTime) {
        url += `&startDate=${startDate}&startTime=${startTime}&endDate=${endDate}&endTime=${endTime}`;
    }
    return fetch(url).then(response => response.json());
}

// --- Gestion du graphique ---
function updateChart(capteurId, canvas, startDate = null, startTime = null, endDate = null, endTime = null) {
    if (!capteurId) {
        console.warn("Aucun capteur sélectionné.");
        return;
    }

    fetch(`getCapteurInfo.php?capteur_id=${capteurId}`)
        .then(response => response.json())
        .then(capteurInfo => {
            const grandeur = capteurInfo.GrandeurCapt;
            const unite = capteurInfo.Unite;

            getMesures(capteurId, startDate, startTime, endDate, endTime).then(data => {
                if (!Array.isArray(data.mesures)) {
                    console.error("Données invalides :", data);
                    return;
                }

                const labels = data.mesures.map(m => m.Horodatage);
                const values = data.mesures.map(m => parseFloat(parseFloat(m.Valeur).toFixed(1)));

                renderChart(canvas, labels, [{
                    label: grandeur === "Direction du vent" ? `${grandeur}` : `${grandeur} (${unite})`,
                    data: values,
                    borderColor: 'rgb(231, 57, 57)',
                    tension: 0.1
                }], unite);

                updateInfoGraphique(values, unite, capteurId, 1, grandeur);
            });
        });
}

function renderChart(canvas, labels, datasets, unite, grandeur = null) {
    const ctx = canvas.getContext('2d');
    canvas.width = canvas.height = null;
    if (canvas.myChart) {
        canvas.myChart.destroy();
    }

    if (grandeur) {
        canvas.setAttribute('data-grandeur', grandeur);
    }

    const isDark = document.body.classList.contains('dark-mode');

    canvas.myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: unite,
                        color: isDark ? '#ffffff' : '#000000'
                    },
                    ticks: {
                        color: isDark ? '#ffffff' : '#000000'
                    },
                    grid: {
                        color: isDark ? '#555555' : '#e0e0e0'
                    }
                },
                x: {
                    ticks: {
                        color: isDark ? '#ffffff' : '#000000'
                    },
                    grid: {
                        color: isDark ? '#555555' : '#e0e0e0'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        font: {
                            family: 'Arial',
                            size: 12
                        },
                        color: isDark ? '#ffffff' : '#000000'
                    }
                }
            }
        }
    });
}

function updateInfoGraphique(values, unite, capteurId = null, numeroGraphique = 1, grandeur = null) {
    const infoDiv = document.getElementById('infoGraphique');
    if (!infoDiv) return;

    // Supprime les anciennes infos de ce graphique (s'il y en a)
    const ancienBloc = document.getElementById(`blocGraphique${numeroGraphique}`);
    if (ancienBloc) ancienBloc.remove();

    const bloc = document.createElement('div');
    bloc.id = `blocGraphique${numeroGraphique}`;
    bloc.className = 'blocInfoGraphique';

    bloc.innerHTML = `<strong>— Données Graphique ${numeroGraphique} —</strong><br>`;

    if (values && values.length > 0) {
        const min = Math.min(...values);
        const max = Math.max(...values);
        const moyenne = values.reduce((acc, val) => acc + val, 0) / values.length;

        if (grandeur === "Direction du vent" || unite === "°") {
            Promise.all([
                fetch(`getDirectionVent.php?angle=${values[values.length - 1]}`).then(r => r.json()),
                fetch(`getDirectionVent.php?angle=${min}`).then(r => r.json()),
                fetch(`getDirectionVent.php?angle=${max}`).then(r => r.json()),
                fetch(`getDirectionVent.php?angle=${moyenne}`).then(r => r.json())
            ]).then(([actuelle, minDir, maxDir, moyenneDir]) => {
                bloc.innerHTML += `
                    <span>Actuelle : ${values[values.length - 1].toFixed(1)}° (${actuelle.direction})</span><br>
                    <span>Minimum (plage) : ${min.toFixed(1)}° (${minDir.direction})</span><br>
                    <span>Maximum (plage) : ${max.toFixed(1)}° (${maxDir.direction})</span><br>
                    <span>Moyenne (plage) : ${moyenne.toFixed(1)}° (${moyenneDir.direction})</span><br><br>
                `;
            });
        } else {
            bloc.innerHTML += `
                <span class="valActuelle">Actuelle : ...</span><br>
                <span>Minimum (plage) : ${min.toFixed(1)} ${unite}</span><br>
                <span>Maximum (plage) : ${max.toFixed(1)} ${unite}</span><br>
                <span>Moyenne (plage) : ${moyenne.toFixed(1)} ${unite}</span><br><br>
            `;
        }
    } else {
        bloc.innerHTML += `<span>Aucune donnée à afficher pour cette plage.</span><br><br>`;
    }

    infoDiv.appendChild(bloc);

    // Mise à jour de la valeur actuelle (asynchrone)
    if (capteurId) {
        fetch(`getDerniereMesure.php?capteur_id=${capteurId}`)
            .then(res => res.json())
            .then(data => {
                const spanActuelle = bloc.querySelector('.valActuelle');
                if (!data.error && data.Valeur) {
                    spanActuelle.textContent = `Actuelle : ${parseFloat(data.Valeur).toFixed(1)} ${unite}`;
                } else {
                    spanActuelle.textContent = "Actuelle : non disponible";
                }
            })
            .catch(() => {
                const spanActuelle = bloc.querySelector('.valActuelle');
                spanActuelle.textContent = "Actuelle : erreur";
            });
    }
}

function updateChartWithTimeRange() {
    const capteurId = document.getElementById('lstCapteur').value;
    const startDate = document.getElementById('startDate').value;
    const startTime = document.getElementById('startTime').value;
    const endDate = document.getElementById('endDate').value;
    const endTime = document.getElementById('endTime').value;

    updateChart(capteurId, document.getElementById('monGraphique'), startDate, startTime, endDate, endTime);
    fermetureModel();
}

function definirPlageTemporelleParDefaut() {
    const maintenant = new Date();
    const debut = new Date(maintenant);
    debut.setDate(maintenant.getDate() - 1);

    document.getElementById('startDate').value = debut.toISOString().slice(0, 10);
    document.getElementById('startTime').value = debut.toTimeString().slice(0, 5);
    document.getElementById('endDate').value = maintenant.toISOString().slice(0, 10);
    document.getElementById('endTime').value = maintenant.toTimeString().slice(0, 5);

    updateChartWithTimeRange();
}

// --- Génériques de gestion des select ---
function remplirSelect(selectId, options, valueKey, labelKey) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">-- Sélectionnez --</option>';
    options.forEach(item => {
        const option = document.createElement('option');
        option.value = item[valueKey];
        option.textContent = item[labelKey];
        select.appendChild(option);
    });
}

function reinitialiserSelects(ids) {
    ids.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.innerHTML = '<option value="">-- Sélectionnez --</option>';
        }
    });
}

function selectionnerParDefaut(selectId, valeurParDefaut, callback = null) {
    const select = document.getElementById(selectId);
    if (!select) return;

    const option = Array.from(select.options).find(opt => opt.value == valeurParDefaut);
    if (option) {
        option.selected = true;
        const event = new Event('change');
        select.dispatchEvent(event); // Déclenche un changement pour charger les données suivantes

        if (typeof callback === 'function') {
            callback(100);
        }
    }
}

function activerSelect(id) {
    const select = document.getElementById(id);
    if (select) select.removeAttribute('disabled');
}

function desactiverSelects(ids) {
    ids.forEach(id => {
        const select = document.getElementById(id);
        if (select) select.setAttribute('disabled', true);
    });
}

// --- Utilitaires divers ---

// --- Couleurs fixes pour les courbes ---
const couleurListe = [
    'rgb(57, 130, 231)',  // Bleu
    'rgb(57, 231, 123)',  // Vert
    'rgb(255, 165, 0)',   // Orange
    'rgb(156, 39, 176)',  // Violet
    'rgb(0, 188, 212)',   // Cyan
    'rgb(231, 57, 57)'    // Rouge
];

let indexCouleur = 0;

function getNextColor() {
    const couleur = couleurListe[indexCouleur];
    indexCouleur = (indexCouleur + 1) % couleurListe.length; // boucle à la fin
    return couleur;
}

function formaterHorodatage(horodatage) {
    const date = new Date(horodatage);
    return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`;
}

function exportCSV() {
    const canvas = document.getElementById('monGraphique');
    const chart = canvas.myChart;
    if (!chart || !chart.data.labels || chart.data.labels.length === 0) {
        alert("Aucune donnée à exporter.");
        return;
    }

    const labels = chart.data.labels;
    const dataset = chart.data.datasets[0];
    const values = dataset.data;

    const startDate = document.getElementById('startDate').value;
    const startTime = document.getElementById('startTime').value;
    const endDate = document.getElementById('endDate').value;
    const endTime = document.getElementById('endTime').value;

    const now = new Date();
    const exportDate = `${now.getDate().toString().padStart(2, '0')}/${(now.getMonth()+1).toString().padStart(2, '0')}/${now.getFullYear()} ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;

    const formatNom = (str) => str.replaceAll(":", "-").replaceAll("/", "-").replaceAll(" ", "_");

    const debut = formatNom(`${startDate} ${startTime}`);
    const fin = formatNom(`${endDate} ${endTime}`);

    const selectCapteur = document.getElementById('lstCapteur');
    let nomCapteur = "Capteur inconnu";
    if (selectCapteur && selectCapteur.options[selectCapteur.selectedIndex]) {
        nomCapteur = selectCapteur.options[selectCapteur.selectedIndex].textContent.trim();
    }

    const grandeurAvecUnite = dataset.label.trim();

    let csvContent = `Export réalisé par : ${utilisateurNomComplet}\r\n`;
    csvContent += `Date d'export : ${exportDate}\r\n`;
    csvContent += `Nom du capteur : ${nomCapteur}\r\n`;
    csvContent += `Grandeur mesurée : ${grandeurAvecUnite}\r\n`;
    csvContent += `Plage de mesure : ${startDate} ${startTime} -> ${endDate} ${endTime}\r\n`;
    csvContent += `\r\n`;
    csvContent += "Horodatage;Valeur\r\n";

    for (let i = 0; i < labels.length; i++) {
        csvContent += `${formaterHorodatage(labels[i])};${parseFloat(values[i]).toFixed(1)}\r\n`;
    }

    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const fileName = `Capteur_${formatNom(nomCapteur)}__${debut}_a_${fin}.csv`;

    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", fileName);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function gererSelectionSerreAjout(serreId, chapelleId, carteId, capteurId) {
    const serreSelect = document.getElementById(serreId);
    const chapelleSelect = document.getElementById(chapelleId);
    const carteSelect = document.getElementById(carteId);
    const capteurSelect = document.getElementById(capteurId);

    if (!serreSelect || !chapelleSelect || !carteSelect || !capteurSelect) {
        console.error("Erreur : un ou plusieurs sélecteurs n'existent pas.");
        return;
    }

    serreSelect.addEventListener('change', function () {
        const idSerre = this.value;
        if (idSerre) {
            fetch(`getChapelles.php?serre_id=${idSerre}`)
                .then(response => response.json())
                .then(chapelles => {
                    remplirSelect(chapelleId, chapelles, 'IdChapelle', 'Nom');
                    activerSelect(chapelleId);
                    reinitialiserSelects([carteId, capteurId]);
                    desactiverSelects([carteId, capteurId]);
                });
        } else {
            reinitialiserSelects([chapelleId, carteId, capteurId]);
            desactiverSelects([chapelleId, carteId, capteurId]);
        }
    });

    chapelleSelect.addEventListener('change', function () {
        const idChapelle = this.value;
        if (idChapelle) {
            fetch(`getCartes.php?chapelle_id=${idChapelle}`)
                .then(response => response.json())
                .then(cartes => {
                    remplirSelect(carteId, cartes, 'DevEui', 'Nom');
                    activerSelect(carteId);
                    reinitialiserSelects([capteurId]);
                    desactiverSelects([capteurId]);
                });
        } else {
            reinitialiserSelects([carteId, capteurId]);
            desactiverSelects([carteId, capteurId]);
        }
    });

    carteSelect.addEventListener('change', function () {
        const idCarte = this.value;
        if (idCarte) {
            fetch(`getCapteurs.php?carte_id=${idCarte}`)
                .then(response => response.json())
                .then(capteurs => {
                    remplirSelect(capteurId, capteurs, 'IdCapteur', 'Nom');
                    activerSelect(capteurId);
                });
        } else {
            reinitialiserSelects([capteurId]);
            desactiverSelects([capteurId]);
        }
    });
}

function synchroniserPlageTemporelleAjout() {
    document.getElementById('startDateAjout').value = document.getElementById('startDate').value;
    document.getElementById('startTimeAjout').value = document.getElementById('startTime').value;
    document.getElementById('endDateAjout').value = document.getElementById('endDate').value;
    document.getElementById('endTimeAjout').value = document.getElementById('endTime').value;
}

function supprimerCourbe() {
    const divGraphiques = document.getElementById('divGraphiques');
    const canvasList = divGraphiques.querySelectorAll('canvas');

    if (canvasList.length > 1) {
        const dernierCanvas = canvasList[canvasList.length - 1];

        if (dernierCanvas.id !== 'monGraphique') {
            if (dernierCanvas.myChart) {
                dernierCanvas.myChart.destroy();
            }
        
            const blocASupprimer = dernierCanvas.parentElement;
            const capteurId = blocASupprimer.getAttribute('data-capteur-id');
            const plage = blocASupprimer.getAttribute('data-plage');
        
            // Supprimer l'info associée
            const blocInfo = document.getElementById(`blocGraphique2`);
            if (blocInfo) blocInfo.remove();

            // Décaler tous les blocs info après celui supprimé
            for (let i = numeroGraphique + 1; ; i++) {
                const blocSuivant = document.getElementById(`blocGraphique${i}`);
                if (!blocSuivant) break;

                // Mettre à jour l'ID
                blocSuivant.id = `blocGraphique${i - 1}`;

                // Mettre à jour le titre dans le contenu HTML
                const titre = blocSuivant.querySelector('strong');
                if (titre) {
                    titre.innerHTML = `— Données Graphique ${i - 1} —`;
                }
            }
        
            // Retirer aussi dans graphiquesAjoutes
            const index = graphiquesAjoutes.findIndex(item => item.capteurId === capteurId && item.plage === plage);
            if (index !== -1) {
                graphiquesAjoutes.splice(index, 1);
            }
        
            blocASupprimer.remove();

            // Après avoir supprimé, vérifier s'il reste encore des graphiques secondaires
            const encoreSecondaires = divGraphiques.querySelectorAll('.graphiqueSecondaire');
            if (encoreSecondaires.length === 0) {
                const graphiquePrincipal = document.getElementById('Graphique');
                graphiquePrincipal.classList.remove('graphiqueMoitié');
                graphiquePrincipal.classList.add('graphiquePlein');
            }
        }
    } else {
        alert('Il ne reste plus que le graphique principal. Vous ne pouvez pas le supprimer.');
    }
}

function ajouterDonneesCourbe(capteurId) {
    if (!capteurId) {
        console.warn("Aucun capteur sélectionné pour ajouter une courbe.");
        return;
    }

    // Récupération de la plage temporelle sélectionnée
    const startDate = document.getElementById('startDateAjout').value;
    const startTime = document.getElementById('startTimeAjout').value;
    const endDate = document.getElementById('endDateAjout').value;
    const endTime = document.getElementById('endTimeAjout').value;

    const plage = `${startDate} ${startTime} -> ${endDate} ${endTime}`;

    // Vérification : capteur et plage déjà utilisés ?
    const dejaAjoute = graphiquesAjoutes.some(item => {
        return item.capteurId == capteurId && item.plage.trim() === plage.trim();
    });

    console.log("Vérif doublon : ", capteurId, plage, graphiquesAjoutes);

    if (dejaAjoute) {
        const capteurSelect = document.getElementById('lstCapteurAjout');
        const capteurNom = capteurSelect.options[capteurSelect.selectedIndex]?.textContent.trim() || "inconnu";

        alert(`Erreur : le capteur "${capteurNom}" est déjà affiché pour la plage suivante :\n${plage}`);
        return;
    }   

    // Sinon on continue normalement
    fetch(`getCapteurInfo.php?capteur_id=${capteurId}`)
        .then(response => response.json())
        .then(capteurInfo => {
            const grandeur = capteurInfo.GrandeurCapt;
            const unite = capteurInfo.Unite;

            getMesures(capteurId, startDate, startTime, endDate, endTime).then(data => {
                if (!Array.isArray(data.mesures)) {
                    console.error("Données invalides :", data);
                    return;
                }

                const labels = data.mesures.map(m => m.Horodatage);
                const values = data.mesures.map(m => parseFloat(parseFloat(m.Valeur).toFixed(1)));

                const divGraphiques = document.getElementById('divGraphiques');

                const graphiquePrincipalBloc = document.getElementById('Graphique');
                if (graphiquePrincipalBloc && !graphiquePrincipalBloc.classList.contains('graphiqueMoitié')) {
                    graphiquePrincipalBloc.classList.remove('graphiquePlein');
                    graphiquePrincipalBloc.classList.add('graphiqueMoitié');
                }

                const newBloc = document.createElement('div');
                newBloc.className = 'graphiqueBloc graphiqueSecondaire';
                newBloc.style.position = 'relative';
                newBloc.setAttribute('data-capteur-id', capteurId);
                newBloc.setAttribute('data-plage', plage);

                const newCanvas = document.createElement('canvas');
                newCanvas.width = 400;
                newCanvas.height = 300;
                newBloc.appendChild(newCanvas);

                const boutonExport = document.createElement('img');
                boutonExport.src = '../img/download.svg';
                boutonExport.alt = 'Export';
                boutonExport.title = 'Export CSV';
                boutonExport.style.width = '15px';
                boutonExport.style.cursor = 'pointer';
                boutonExport.style.position = 'absolute';
                boutonExport.style.top = '10px';
                boutonExport.style.right = '10px';
                boutonExport.addEventListener('click', function () {
                    exportCSVDepuisCanvas(newCanvas);
                });

                newBloc.appendChild(boutonExport);

                divGraphiques.appendChild(newBloc);

                renderChart(newCanvas, labels, [{
                    label: grandeur === "Direction du vent" ? `${grandeur}` : `${grandeur} (${unite})`,
                    data: values,
                    borderColor: getNextColor(),
                    tension: 0.1
                }], unite, grandeur);

                // Très important : on ajoute l'entrée dans graphiquesAjoutes
                graphiquesAjoutes.push({ capteurId: String(capteurId), plage });

                console.log("Ajouté :", { capteurId: String(capteurId), plage });

                const numeroGraphique = graphiquesAjoutes.length + 1; // +1 car graphique 1 est principal
                updateInfoGraphique(values, unite, capteurId, numeroGraphique, grandeur);
            });
        });
}

function exportCSVDepuisCanvas(canvas) {
    const chart = canvas.myChart;
    if (!chart || !chart.data.labels || chart.data.labels.length === 0) {
        alert("Aucune donnée à exporter.");
        return;
    }

    const labels = chart.data.labels;
    const dataset = chart.data.datasets[0];
    const values = dataset.data;

    const formatNom = (str) => str.replaceAll(":", "-").replaceAll("/", "-").replaceAll(" ", "_");
    const debut = formatNom(labels[0]);
    const fin = formatNom(labels[labels.length - 1]);
    const nomCapteurLisible = dataset.label.trim();

    let csvContent = "Horodatage;Valeur\r\n";
    for (let i = 0; i < labels.length; i++) {
        csvContent += `${formaterHorodatage(labels[i])};${parseFloat(values[i]).toFixed(1)}\r\n`;
    }

    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const fileName = `Capteur ${nomCapteurLisible}__${debut}_a_${fin}.csv`;

    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", fileName);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function rafraichirCouleursGraphiques() {
    const isDark = document.body.classList.contains('dark-mode');
    const canvases = document.querySelectorAll('canvas');

    canvases.forEach(canvas => {
        if (canvas.myChart) {
            const chart = canvas.myChart;

            // Couleurs du texte
            chart.options.scales.x.ticks.color = isDark ? '#ffffff' : '#000000';
            chart.options.scales.y.ticks.color = isDark ? '#ffffff' : '#000000';
            chart.options.scales.y.title.color = isDark ? '#ffffff' : '#000000';
            chart.options.plugins.legend.labels.color = isDark ? '#ffffff' : '#000000';

            // Couleur des lignes de la grille
            chart.options.scales.x.grid.color = isDark ? '#555555' : '#e0e0e0';
            chart.options.scales.y.grid.color = isDark ? '#555555' : '#e0e0e0';

            chart.update();
        }
    });
}