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

    // Événement unique pour ouvrir la modale d'ajout de courbe
    document.getElementById('ajoutCourbe').addEventListener('click', function () {
        synchroniserPlageTemporelleAjout(); // Synchroniser la plage temporelle
        document.getElementById('ajoutCourbeDiv').style.display = 'block'; // Ouvrir la modale
    });

    // Événement pour fermer la modale d'ajout de courbe
    document.getElementById('closeAjoutCourbe').addEventListener('click', function () {
        document.getElementById('ajoutCourbeDiv').style.display = 'none';
        const backdrop = document.getElementById('ajoutCourbeBackdrop');
        if (backdrop) backdrop.remove();
    });

    // Événement pour valider l'ajout de la courbe
    document.getElementById('validerAjoutCourbe').addEventListener('click', function () {
        if (document.getElementById('synchroPlageAjout').checked) {
            synchroniserPlageTemporelleAjout();
        }

        var capteurId = document.getElementById('lstCapteurAjout').value;

        // Récupération de la plage
        const startDate = document.getElementById('startDateAjout').value;
        const startTime = document.getElementById('startTimeAjout').value;
        const endDate = document.getElementById('endDateAjout').value;
        const endTime = document.getElementById('endTimeAjout').value;
        const plage = `${startDate} ${startTime} -> ${endDate} ${endTime}`;

        ajouterDonneesCourbe(capteurId);
        document.getElementById('ajoutCourbeDiv').style.display = 'none';

        document.getElementById('ajoutCourbeDiv').style.display = 'none';
        const backdrop = document.getElementById('ajoutCourbeBackdrop');
        if (backdrop) backdrop.remove();
    });

    // Sélection des valeurs par défaut pour la plage temporelle
    definirPlageTemporelleParDefaut();
    
    var serreIdParDefaut = 1;
    var chapelleIdParDefaut = 1;
    var carteDevEuiParDefaut = "0004a30b00216c4c";
    var capteurIdParDefaut = 1;

    // Sélection des valeurs par défaut
    selectionnerParDefaut('lstSerre', serreIdParDefaut, function () {
        // Quand lstSerre est chargé et changé, on attend un peu le fetch de chapelles
        setTimeout(function () {
            selectionnerParDefaut('lstChapelle', chapelleIdParDefaut, function () {
                setTimeout(function () {
                    selectionnerParDefaut('lstCarte', carteDevEuiParDefaut, function () {
                        setTimeout(function () {
                            selectionnerParDefaut('lstCapteur', capteurIdParDefaut);
                        }, 100);
                    });
                }, 100);
            });
        }, 100);
    });

    document.getElementById('supprimerCourbe').addEventListener('click', function () {
        if (graphiquesAjoutes.length < 1) {
            alert("Il ne reste que le graphique principal. Vous ne pouvez pas le supprimer.");
            return;
        }
    
        const liste = document.getElementById('listeGraphiquesASupprimer');
        liste.innerHTML = '<option value="">-- Sélectionner un graphique --</option>';
    
        graphiquesAjoutes.forEach((graph, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = `Graphique ${index + 2} – Capteur ${graph.capteurId} (${graph.plage})`;
            liste.appendChild(option);
        });
    
        document.getElementById('suppressionCourbeDiv').style.display = 'block';
    });

    document.getElementById('annulerSuppressionGraphique').addEventListener('click', function () {
        document.getElementById('suppressionCourbeDiv').style.display = 'none';
    });

    document.getElementById('closeSuppressionCourbe').addEventListener('click', function () {
        document.getElementById('suppressionCourbeDiv').style.display = 'none';
    });

    document.getElementById('confirmerSuppressionGraphique').addEventListener('click', function () {
        const index = document.getElementById('listeGraphiquesASupprimer').value;
        if (index === "") return;
    
        const graphique = graphiquesAjoutes[index];
        const capteurId = graphique.capteurId;
        const plage = graphique.plage;
    
        // Supprimer le bloc graphique
        const blocs = document.querySelectorAll('.graphiqueSecondaire');
        blocs.forEach(bloc => {
            if (bloc.getAttribute('data-capteur-id') === capteurId && bloc.getAttribute('data-plage') === plage) {
                const canvas = bloc.querySelector('canvas');
                if (canvas && canvas.myChart) {
                    canvas.myChart.destroy();
                }
                bloc.remove();
            }
        });
    
        const numeroGraphique = parseInt(index) + 2;
    
        // Supprimer le bloc info correspondant
        const blocInfo = document.getElementById(`blocGraphique${numeroGraphique}`);
        if (blocInfo) blocInfo.remove();
    
        // Décaler les blocs info restants
        let i = numeroGraphique + 1;
        while (true) {
            const suivant = document.getElementById(`blocGraphique${i}`);
            if (!suivant) break;
    
            suivant.id = `blocGraphique${i - 1}`;
            const titre = suivant.querySelector('strong');
            if (titre) titre.textContent = `— Données Graphique ${i - 1} —`;
            i++;
        }
    
        // Supprimer l'entrée du tableau
        graphiquesAjoutes.splice(index, 1);
    
        // Fermer la modale
        document.getElementById('suppressionCourbeDiv').style.display = 'none';
    
        // Adapter la taille du graphique principal si plus aucun secondaire
        const encoreSecondaires = document.querySelectorAll('.graphiqueSecondaire');
        if (encoreSecondaires.length === 0) {
            const principal = document.getElementById('Graphique');
            principal.classList.remove('graphiqueMoitié');
            principal.classList.add('graphiquePlein');
        }
    });    

    const synchroCheckbox = document.getElementById('synchroPlageAjout');
    if (synchroCheckbox) {
        synchroCheckbox.addEventListener('change', function () {
            const disabled = this.checked;
            document.getElementById('startDateAjout').disabled = disabled;
            document.getElementById('startTimeAjout').disabled = disabled;
            document.getElementById('endDateAjout').disabled = disabled;
            document.getElementById('endTimeAjout').disabled = disabled;

            if (disabled) synchroniserPlageTemporelleAjout();
        });
    }

    // Gestionnaires d'événements pour les sélecteurs de capteurs dans ajoutCourbeDiv (Graphique n°1)
    gererSelectionSerreAjout('lstSerreAjout', 'lstChapelleAjout', 'lstCarteAjout', 'lstCapteurAjout');

    // Fermer la plage temporelle principale
    document.getElementById('closePlageMain').addEventListener('click', function () {
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
    
    document.getElementById('telechargeCourbe').addEventListener('click', exportCSV);

    // Gestion du mode sombre
    const modeIcon = document.getElementById('modeIcon');
    const body = document.body;

    // Vérifie le mode stocké
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-mode');
        modeIcon.src = '../img/soleil.svg';
        modeIcon.title = 'Mode clair';
    }

    document.getElementById('modeIcon').addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
    
        // Change l’icône lune/soleil
        const modeIcon = document.getElementById('modeIcon');
        const isDark = document.body.classList.contains('dark-mode');
        modeIcon.src = isDark ? '../img/soleil.svg' : '../img/lune.svg';
        modeIcon.title = isDark ? 'Mode clair' : 'Mode sombre';
        modeIcon.alt = isDark ? 'Mode sombre' : 'Mode clair';
    
        // Rafraîchit les couleurs des graphiques
        rafraichirCouleursGraphiques();
    });

});

const graphiquesAjoutes = [];