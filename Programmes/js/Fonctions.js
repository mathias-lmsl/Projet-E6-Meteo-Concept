function ouvertureModel() {
    document.getElementById('model').style.display = 'block';
}

function fermetureModel() {
    document.getElementById('model').style.display = 'none';
}

function updateChart(capteurId, startDate, startTime, endDate, endTime) {
    if (capteurId) {
        let url = 'getMesures.php?capteur_id=' + capteurId;
        if (startDate && startTime && endDate && endTime) {
            url += '&startDate=' + startDate + '&startTime=' + startTime + '&endDate=' + endDate + '&endTime=' + endTime;
        }

        // Récupérer les informations du capteur (grandeur et unité)
        fetch('getCapteurInfo.php?capteur_id=' + capteurId)
            .then(response => response.json())
            .then(capteurInfo => {
                const grandeur = capteurInfo.GrandeurCapt;
                const unite = capteurInfo.Unite;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error("Erreur lors de la récupération des mesures :", data.error);
                            return;
                        }

                        const labels = data.map(mesure => mesure.Horodatage);
                        const values = data.map(mesure => parseFloat(mesure.Valeur));

                        if (labels && values && labels.length > 0 && values.length > 0) {
                            const canvas = document.getElementById('monGraphique');
                            const ctx = canvas.getContext('2d');
                            const dpr = window.devicePixelRatio || 1;

                            canvas.style.width = canvas.width + 'px';
                            canvas.style.height = canvas.height + 'px';
                            canvas.width *= dpr;
                            canvas.height *= dpr;

                            ctx.scale(dpr, dpr);

                            if (window.myLine) {
                                window.myLine.destroy();
                            }
                            window.myLine = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: `${grandeur} (${unite})`, // Modification ici
                                        data: values,
                                        borderColor: 'rgb(231, 57, 57)',
                                        tension: 0.1
                                    }]
                                },
                                options: {
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            title: {
                                                display: true,
                                                text: unite
                                            }
                                        }
                                    },
                                    font: {
                                        family: 'Arial',
                                        size: 12,
                                        weight: 'normal'
                                    }
                                }
                            });
                            updateInfoGraphique(values, unite); // Passer l'unité à updateInfoGraphique
                        } else {
                            console.error("Données de graphique invalides ou vides.");
                        }
                    })
                    .catch(error => {
                        console.error("Erreur lors de la récupération des données :", error);
                    });
            });
    }
}

function updateInfoGraphique(values, unite) {
    if (values && values.length > 0) {
        const actuelle = values[values.length - 1];
        const min = Math.min(...values);
        const max = Math.max(...values);
        const moyenne = values.reduce((acc, val) => acc + val, 0) / values.length;

        document.getElementById('infoGraphique').innerHTML = `
        <span style="display: inline-block; width: 24%; text-align: center;">Actuelle : ${actuelle.toFixed(2)} ${unite}</span>
        <span style="display: inline-block; width: 24%; text-align: center;">Minimum : ${min.toFixed(2)} ${unite}</span>
        <span style="display: inline-block; width: 24%; text-align: center;">Maximum : ${max.toFixed(2)} ${unite}</span>
        <span style="display: inline-block; width: 24%; text-align: center;">Moyenne : ${moyenne.toFixed(2)} ${unite}</span>`;
    } else {
        document.getElementById('infoGraphique').innerHTML = "Aucune donnée à afficher.";
    }
}

function updateChartWithTimeRange() {
    const capteurId = document.getElementById('lstCapteur').value;
    const startDate = document.getElementById('startDate').value;
    const startTime = document.getElementById('startTime').value;
    const endDate = document.getElementById('endDate').value;
    const endTime = document.getElementById('endTime').value;

    updateChart(capteurId, startDate, startTime, endDate, endTime);
    fermetureModel();
}

document.getElementById('ajoutCourbe').addEventListener('click', function() {
    var menu = document.getElementById('menuAjoutCourbe');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    document.getElementById('ajoutCourbeDiv').style.display = 'block';
});

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
                    document.getElementById('lstCarte').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
                    document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
                    document.getElementById('lstChapelle').removeAttribute('disabled');
                    document.getElementById('lstCarte').setAttribute('disabled', true);
                    document.getElementById('lstCapteur').setAttribute('disabled', true);
                });
        } else {
            document.getElementById('lstChapelle').innerHTML = '<option value="">-- Sélectionnez une chapelle --</option>';
            document.getElementById('lstCarte').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
            document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
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
                    document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
                    document.getElementById('lstCarte').removeAttribute('disabled');
                    document.getElementById('lstCapteur').setAttribute('disabled', true);
                });
        } else {
            document.getElementById('lstCarte').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
            document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
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
                    document.getElementById('lstCapteur').removeAttribute('disabled');
                });
        } else {
            document.getElementById('lstCapteur').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            document.getElementById('lstCapteur').setAttribute('disabled', true);
        }
    });

    document.getElementById('lstCapteur').addEventListener('change', function() {
        var capteurId = this.value;
        updateChart(capteurId);
    });

    document.getElementById('menuAjoutCourbe').addEventListener('click', function(event) {
        if (event.target.tagName === 'BUTTON') {
            const grandeur = event.target.dataset.grandeur;
            ajouterCourbe(grandeur);
        }
    });

    function ajouterCourbe(grandeur) {
        // Récupérer l'ID du capteur correspondant à la grandeur sélectionnée
        fetch('getCapteurId.php?grandeur=' + grandeur)
            .then(response => response.json())
            .then(capteurInfo => {
                const capteurId = capteurInfo.IdCapteur;
                if (capteurId) {
                    // Si le graphique existe déjà et que la grandeur est la même, ajouter une nouvelle courbe
                    if (window.myLine && window.myLine.data.datasets[0].label.includes(grandeur)) {
                        ajouterDonneesCourbe(capteurId);
                    } else {
                        // Sinon, créer un nouveau graphique
                        creerNouveauGraphique(capteurId);
                    }
                } else {
                    console.error("Aucun capteur trouvé pour la grandeur :", grandeur);
                }
            })
            .catch(error => {
                console.error("Erreur lors de la récupération de l'ID du capteur :", error);
            });
    }

    document.getElementById('supprimerCourbe').addEventListener('click', function() {
        supprimerCourbe();
    });

    function supprimerCourbe() {
        if (window.myLine && window.myLine.data.datasets.length > 1) {
            // Supprimer le dernier dataset ajouté
            window.myLine.data.datasets.pop();
            window.myLine.update();
        } else {
            alert("Vous ne pouvez pas supprimer la courbe principale.");
        }
    }

    function ajouterDonneesCourbe(capteurId) {
        fetch('getMesures.php?capteur_id=' + capteurId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("Erreur lors de la récupération des mesures :", data.error);
                    return;
                }
    
                const labels = data.map(mesure => mesure.Horodatage);
                const values = data.map(mesure => parseFloat(mesure.Valeur));
    
                if (labels && values && labels.length > 0 && values.length > 0) {
                    // Récupérer la grandeur et l'unité du capteur
                    fetch('getCapteurInfo.php?capteur_id=' + capteurId)
                        .then(response => response.json())
                        .then(capteurInfo => {
                            const grandeur = capteurInfo.GrandeurCapt;
                            const unite = capteurInfo.Unite;
    
                            // Ajouter un nouveau dataset au graphique existant
                            window.myLine.data.datasets.push({
                                label: `${grandeur} (${unite})`,
                                data: values,
                                borderColor: getRandomColor(), // Fonction pour générer une couleur aléatoire
                                tension: 0.1
                            });
    
                            window.myLine.update();
                        })
                        .catch(error => {
                            console.error("Erreur lors de la récupération des informations du capteur :", error);
                        });
                } else {
                    console.error("Données de graphique invalides ou vides.");
                }
            })
            .catch(error => {
                console.error("Erreur lors de la récupération des données :", error);
            });
    }

    function creerNouveauGraphique(capteurId) {
        fetch('getMesures.php?capteur_id=' + capteurId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error("Erreur lors de la récupération des mesures :", data.error);
                    return;
                }
    
                const labels = data.map(mesure => mesure.Horodatage);
                const values = data.map(mesure => parseFloat(mesure.Valeur));
    
                labels.reverse();
                values.reverse();
    
                if (labels && values && labels.length > 0 && values.length > 0) {
                    // Récupérer la grandeur et l'unité du capteur
                    fetch('getCapteurInfo.php?capteur_id=' + capteurId)
                        .then(response => response.json())
                        .then(capteurInfo => {
                            const grandeur = capteurInfo.GrandeurCapt;
                            const unite = capteurInfo.Unite;
    
                            // Créer un nouveau canvas pour le nouveau graphique
                            const nouveauCanvas = document.createElement('canvas');
                            nouveauCanvas.id = 'nouveauGraphique_' + capteurId; // ID unique
                            document.getElementById('divGraphiques').appendChild(nouveauCanvas);
    
                            const ctx = nouveauCanvas.getContext('2d');
                            const dpr = window.devicePixelRatio || 1;
    
                            nouveauCanvas.style.width = nouveauCanvas.width + 'px';
                            nouveauCanvas.style.height = nouveauCanvas.height + 'px';
                            nouveauCanvas.width *= dpr;
                            nouveauCanvas.height *= dpr;
    
                            ctx.scale(dpr, dpr);
    
                            // Créer un nouveau graphique
                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: `${grandeur} (${unite})`,
                                        data: values,
                                        borderColor: getRandomColor(),
                                        tension: 0.1
                                    }]
                                },
                                options: {
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
                                        title: {
                                            display: true,
                                            text: `${grandeur} (${unite})`
                                        }
                                    },
                                    font: {
                                        family: 'Arial',
                                        size: 12,
                                        weight: 'normal'
                                    }
                                }
                            });
                        })
                        .catch(error => {
                            console.error("Erreur lors de la récupération des informations du capteur :", error);
                        });
                } else {
                    console.error("Données de graphique invalides ou vides.");
                }
            })
            .catch(error => {
                console.error("Erreur lors de la récupération des données :", error);
            });
    }
    
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    document.getElementById('ajoutCourbe').addEventListener('click', function() {
        document.getElementById('ajoutCourbeDiv').style.display = 'block';
        document.getElementById('menuAjoutCourbe').style.display = 'none';
    });

    document.getElementById('closeAjoutCourbe').addEventListener('click', function() {
        document.getElementById('ajoutCourbeDiv').style.display = 'none';
    });

    document.getElementById('closePlage').addEventListener('click', function() {
        document.getElementById('model').style.display = 'none';
    });

    document.getElementById('validerAjoutCourbe').addEventListener('click', function() {
        // Récupérer l'ID du capteur sélectionné
        var capteurId = document.getElementById('lstCapteurAjout').value;
        // Ajouter la courbe au graphique
        ajouterDonneesCourbe(capteurId);
        // Fermer la div
        document.getElementById('ajoutCourbeDiv').style.display = 'none';
    });

    // Gestionnaires d'événements pour les sélecteurs de capteurs dans ajoutCourbeDiv
    document.getElementById('lstSerreAjout').addEventListener('change', function() {
        var serreId = this.value;
        if (serreId) {
            fetch('getChapelles.php?serre_id=' + serreId)
                .then(response => response.json())
                .then(chapelles => {
                    var chapelleSelect = document.getElementById('lstChapelleAjout');
                    chapelleSelect.innerHTML = '<option value="">-- Sélectionnez une chapelle --</option>';
                    chapelles.forEach(chapelle => {
                        chapelleSelect.innerHTML += '<option value="' + chapelle.IdChapelle + '">' + chapelle.Nom + '</option>';
                    });
                    document.getElementById('lstCarteAjout').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
                    document.getElementById('lstCapteurAjout').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
                    document.getElementById('lstChapelleAjout').removeAttribute('disabled');
                    document.getElementById('lstCarteAjout').setAttribute('disabled', true);
                    document.getElementById('lstCapteurAjout').setAttribute('disabled', true);
                });
        } else {
            document.getElementById('lstChapelleAjout').innerHTML = '<option value="">-- Sélectionnez une chapelle --</option>';
            document.getElementById('lstCarteAjout').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
            document.getElementById('lstCapteurAjout').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            document.getElementById('lstChapelleAjout').setAttribute('disabled', true);
            document.getElementById('lstCarteAjout').setAttribute('disabled', true);
            document.getElementById('lstCapteurAjout').setAttribute('disabled', true);
        }
    });

    document.getElementById('lstChapelleAjout').addEventListener('change', function() {
        var chapelleId = this.value;
        if (chapelleId) {
            fetch('getCartes.php?chapelle_id=' + chapelleId)
                .then(response => response.json())
                .then(cartes => {
                    var carteSelect = document.getElementById('lstCarteAjout');
                    carteSelect.innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
                    cartes.forEach(carte => {
                        carteSelect.innerHTML += '<option value="' + carte.IdCarte + '">' + carte.Nom + '</option>';
                    });
                    document.getElementById('lstCapteurAjout').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
                    document.getElementById('lstCarteAjout').removeAttribute('disabled');
                    document.getElementById('lstCapteurAjout').setAttribute('disabled', true);
                });
        } else {
            document.getElementById('lstCarteAjout').innerHTML = '<option value="">-- Sélectionnez une carte --</option>';
            document.getElementById('lstCapteurAjout').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            document.getElementById('lstCarteAjout').setAttribute('disabled', true);
            document.getElementById('lstCapteurAjout').setAttribute('disabled', true);
        }
    });

    document.getElementById('lstCarteAjout').addEventListener('change', function() {
        var carteId = this.value;
        if (carteId) {
            fetch('getCapteurs.php?carte_id=' + carteId)
                .then(response => response.json())
                .then(capteurs => {
                    var capteurSelect = document.getElementById('lstCapteurAjout');
                    capteurSelect.innerHTML = '<option value="">Sélectionnez un capteur</option>';
                    capteurs.forEach(capteur => {
                        capteurSelect.innerHTML += '<option value="' + capteur.IdCapteur + '">' + capteur.Nom + '</option>';
                    });
                    document.getElementById('lstCapteurAjout').removeAttribute('disabled');
                });
        } else {
            document.getElementById('lstCapteurAjout').innerHTML = '<option value="">-- Sélectionnez un capteur --</option>';
            document.getElementById('lstCapteurAjout').setAttribute('disabled', true);
        }
    });

    document.getElementById('lstCapteurAjout').addEventListener('change', function() {
        var capteurId = this.value;
    });

    function ouvertureModelAjout() {
        document.getElementById('model').style.display = 'block';
    }

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
        if (window.myLine && window.myLine.data.labels.length > 0) {
            const labels = window.myLine.data.labels;
            const datasets = window.myLine.data.datasets;

            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Horodatage,";

            // Ajouter les noms des datasets comme en-têtes de colonnes
            datasets.forEach(dataset => {
                csvContent += dataset.label + ",";
            });
            csvContent = csvContent.slice(0, -1) + "\n"; // Modifier \r\n en \n

            for (let i = 0; i < labels.length; i++) {
                const horodatage = formaterHorodatage(labels[i]); // Formater l'horodatage
                csvContent += horodatage + ",";

                // Ajouter les données de chaque dataset
                datasets.forEach(dataset => {
                    csvContent += dataset.data[i] + ",";
                });
                csvContent = csvContent.slice(0, -1) + "\n"; // Modifier \r\n en \n
            }

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "donnees_graphique.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert("Aucune donnée à télécharger.");
        }

        document.getElementById('telechargeCourbe').addEventListener('click', function() {
            telechargerCSV();
        });
    }
});