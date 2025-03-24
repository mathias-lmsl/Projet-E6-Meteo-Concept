// Fonction pour afficher le modèle (modal)
function ouvertureModel() {
    document.getElementById('model').style.display = 'block';
}

// Fonction pour masquer le modèle (modal)
function fermetureModel() {
    document.getElementById('model').style.display = 'none';
}

// Fonction pour mettre à jour le graphique avec les données du capteur et les filtres de temps
function updateChart(capteurId, startDate, startTime, endDate, endTime) {
    if (capteurId) {
        // Construire l'URL pour récupérer les mesures du capteur
        let url = 'getMesures.php?capteur_id=' + capteurId;
        // Ajouter les filtres de temps à l'URL si ils sont fournis
        if (startDate && startTime && endDate && endTime) {
            url += '&startDate=' + startDate + '&startTime=' + startTime + '&endDate=' + endDate + '&endTime=' + endTime;
        }

        // Récupérer les informations du capteur (grandeur et unité)
        fetch('getCapteurInfo.php?capteur_id=' + capteurId)
            .then(response => response.json())
            .then(capteurInfo => {
                const grandeur = capteurInfo.GrandeurCapt;
                const unite = capteurInfo.Unite;

                // Récupérer les mesures du capteur
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error("Erreur lors de la récupération des mesures :", data.error);
                            return;
                        }

                        // Extraire les labels (horodatages) et les valeurs des mesures
                        const labels = data.map(mesure => mesure.Horodatage);
                        const values = data.map(mesure => parseFloat(mesure.Valeur));

                        // Vérifier si les données sont valides
                        if (labels && values && labels.length > 0 && values.length > 0) {
                            const canvas = document.getElementById('monGraphique');
                            const ctx = canvas.getContext('2d');
                            const dpr = window.devicePixelRatio || 1;

                            // Ajuster la taille du canvas pour les écrans haute résolution
                            canvas.style.width = canvas.width + 'px';
                            canvas.style.height = canvas.height + 'px';
                            canvas.width *= dpr;
                            canvas.height *= dpr;

                            ctx.scale(dpr, dpr);

                            // Détruire le graphique précédent s'il existe
                            if (window.myLine) {
                                window.myLine.destroy();
                            }

                            // Créer un nouveau graphique avec les données récupérées
                            window.myLine = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: `${grandeur} (${unite})`, // Utiliser la grandeur et l'unité du capteur comme label
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
                                                text: unite // Utiliser l'unité du capteur comme titre de l'axe Y
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
                            // Mettre à jour les informations du graphique (actuelle, min, max, moyenne)
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

// Fonction pour mettre à jour les informations du graphique (actuelle, min, max, moyenne)
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

// Fonction pour mettre à jour le graphique avec les filtres de temps sélectionnés
function updateChartWithTimeRange() {
    const capteurId = document.getElementById('lstCapteur').value;
    const startDate = document.getElementById('startDate').value;
    const startTime = document.getElementById('startTime').value;
    const endDate = document.getElementById('endDate').value;
    const endTime = document.getElementById('endTime').value;

    updateChart(capteurId, startDate, startTime, endDate, endTime);
    fermetureModel();
}

// Gestionnaire d'événement pour le bouton "Ajouter une courbe"
document.getElementById('ajoutCourbe').addEventListener('click', function () {
    var menu = document.getElementById('menuAjoutCourbe');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    document.getElementById('ajoutCourbeDiv').style.display = 'block';
});

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

    document.getElementById('lstCapteur').addEventListener('change', function () {
        // Récupère l'ID du capteur sélectionné.
        var capteurId = this.value;
        // Met à jour le graphique avec les données du capteur.
        updateChart(capteurId);
    });

    document.getElementById('menuAjoutCourbe').addEventListener('click', function (event) {
        // Vérifie si l'élément cliqué est un bouton.
        if (event.target.tagName === 'BUTTON') {
            // Récupère la grandeur du capteur à partir de l'attribut data-grandeur.
            const grandeur = event.target.dataset.grandeur;
            // Ajoute une nouvelle courbe au graphique pour la grandeur sélectionnée.
            ajouterCourbe(grandeur);
        }
    });

    function ajouterCourbe(grandeur) {
        // Récupère l'ID du capteur correspondant à la grandeur sélectionnée.
        fetch('getCapteurId.php?grandeur=' + grandeur)
            .then(response => response.json())
            .then(capteurInfo => {
                const capteurId = capteurInfo.IdCapteur;
                if (capteurId) {
                    // Si le graphique existe déjà et que la grandeur est la même, ajouter une nouvelle courbe.
                    if (window.myLine && window.myLine.data.datasets[0].label.includes(grandeur)) {
                        ajouterDonneesCourbe(capteurId);
                    } else {
                        // Sinon, créer un nouveau graphique.
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
                                label: `<span class="math-inline">\{grandeur\} \(</span>{unite})`,
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

    function creerNouveauGraphique(capteurId) {
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

                // Inverse les tableaux d'horodatages et de valeurs pour afficher les données dans l'ordre chronologique.
                labels.reverse();
                values.reverse();

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

                            // Crée un nouvel élément canvas pour le nouveau graphique.
                            const nouveauCanvas = document.createElement('canvas');
                            // Définit un ID unique pour le nouveau canvas.
                            nouveauCanvas.id = 'nouveauGraphique_' + capteurId;
                            // Ajoute le nouveau canvas à la div des graphiques.
                            document.getElementById('divGraphiques').appendChild(nouveauCanvas);

                            // Obtient le contexte 2D du nouveau canvas.
                            const ctx = nouveauCanvas.getContext('2d');
                            // Obtient le ratio de pixels de l'appareil.
                            const dpr = window.devicePixelRatio || 1;

                            // Ajuste la taille du canvas pour les écrans haute résolution.
                            nouveauCanvas.style.width = nouveauCanvas.width + 'px';
                            nouveauCanvas.style.height = nouveauCanvas.height + 'px';
                            nouveauCanvas.width *= dpr;
                            nouveauCanvas.height *= dpr;

                            // Met à l'échelle le contexte 2D pour les écrans haute résolution.
                            ctx.scale(dpr, dpr);

                            // Crée un nouveau graphique Chart.js.
                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: `<span class="math-inline">\{grandeur\} \(</span>{unite})`,
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
                                            text: `<span class="math-inline">\{grandeur\} \(</span>{unite})`
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
        document.getElementById('menuAjoutCourbe').style.display = 'none';
    });

    document.getElementById('closeAjoutCourbe').addEventListener('click', function () {
        // Masque la div pour ajouter une courbe.
        document.getElementById('ajoutCourbeDiv').style.display = 'none';
    });

    document.getElementById('closePlage').addEventListener('click', function () {
        // Masque le modèle (modal).
        document.getElementById('model').style.display = 'none';
    });

    document.getElementById('validerAjoutCourbe').addEventListener('click', function () {
        // Récupère l'ID du capteur sélectionné dans la liste déroulante.
        var capteurId = document.getElementById('lstCapteurAjout').value;
        // Ajoute la courbe au graphique.
        ajouterDonneesCourbe(capteurId);
        // Masque la div pour ajouter une courbe.
        document.getElementById('ajoutCourbeDiv').style.display = 'none';
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

    // function formaterHorodatage(horodatage) {
    //     // Formate un horodatage en une chaîne de caractères lisible.
    //     const date = new Date(horodatage);
    //     const jour = String(date.getDate()).padStart(2, '0');
    //     const mois = String(date.getMonth() + 1).padStart(2, '0');
    //     const annee = date.getFullYear();
    //     const heures = String(date.getHours()).padStart(2, '0');
    //     const minutes = String(date.getMinutes()).padStart(2, '0');
    //     const secondes = String(date.getSeconds()).padStart(2, '0');
    //     return `<span class="math-inline">\{jour\}/</span>{mois}/${annee} <span class="math-inline">\{heures\}\:</span>{minutes}:${secondes}`;
    // }

    // function telechargerCSV() {
    //     // Télécharge les données du graphique actuel au format CSV.
    //     if (window.myLine && window.myLine.data.labels.length > 0) {
    //         // Récupère les labels (horodatages) et les datasets du graphique.
    //         const labels = window.myLine.data.labels;
    //         const datasets = window.myLine.data.datasets;

    //         // Initialise le contenu CSV avec l'en-tête.
    //         let csvContent = "data:text/csv;charset=utf-8,";
    //         csvContent += "Horodatage,";

    //         // Ajoute les noms des datasets comme en-têtes de colonnes.
    //         datasets.forEach(dataset => {
    //             csvContent += dataset.label + ",";
    //         });
    //         csvContent = csvContent.slice(0, -1) + "\n"; // Supprime la virgule finale et ajoute une nouvelle ligne.

    //         // Ajoute les données de chaque ligne.
    //         for (let i = 0; i < labels.length; i++) {
    //             const horodatage = formaterHorodatage(labels[i]); // Formate l'horodatage.
    //             csvContent += horodatage + ",";

    //             // Ajouter les données de chaque dataset
    //             datasets.forEach(dataset => {
    //                 // Pour chaque dataset, ajoute la valeur correspondante à l'index 'i' au contenu CSV, suivie d'une virgule.
    //                 csvContent += dataset.data[i] + ",";
    //             });
    //             // Supprime la dernière virgule ajoutée à la ligne et ajoute un saut de ligne pour passer à la ligne suivante.
    //             csvContent = csvContent.slice(0, -1) + "\n";
    //         }

    //         // Encode l'URI du contenu CSV pour le téléchargement.
    //         const encodedUri = encodeURI(csvContent);
    //         // Crée un élément lien (<a>) pour le téléchargement.
    //         const link = document.createElement("a");
    //         // Définit l'attribut 'href' du lien avec l'URI encodé du contenu CSV.
    //         link.setAttribute("href", encodedUri);
    //         // Définit l'attribut 'download' du lien avec le nom du fichier CSV à télécharger.
    //         link.setAttribute("download", "donnees_graphique.csv");
    //         // Ajoute le lien au corps du document HTML.
    //         document.body.appendChild(link);
    //         // Simule un clic sur le lien pour lancer le téléchargement du fichier.
    //         link.click();
    //         // Supprime le lien du corps du document après le téléchargement.
    //         document.body.removeChild(link);
    //     } else {
    //         // Affiche une alerte si aucune donnée n'est disponible pour le téléchargement (si le graphique est vide).
    //         alert("Aucune donnée à télécharger.");
    //     }

    //     // Ajoute un gestionnaire d'événements au bouton de téléchargement de la courbe.
    //     document.getElementById('telechargeCourbe').addEventListener('click', function () {
    //         // Appelle la fonction 'telechargerCSV' pour lancer le processus de téléchargement du fichier CSV.
    //         telechargerCSV();
    //     });
    // }
});