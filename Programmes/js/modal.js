// Fonction pour créer un titre (vous l'avez déjà)
function createTitle(text) {
    const title = document.createElement('h2');
    title.textContent = text;
    return title;
}

// Fonction pour obtenir le libellé (vous l'avez déjà)
function getLabel(key) {
    return {
        EtatComposant: 'État du composant',
        GrandeurCapt: 'Grandeur du capteur',
        Unite: 'Unité',
        DevEui: 'Carte associée',
        ValeurMin: 'Seuil Minimum',
        ValeurMax: 'Seuil Maximum',
        Nom: 'Nom', // Pour NomCarte et NomChapelle, NomSerre etc.
        IdChapelle: 'Chapelle Associée',
        // NOUVEAUX AJOUTS POUR CHAPELLE
        IdSerre: 'Serre Associée',
        NbRangees: 'Nombre de Rangées',
        NbTables: 'Nombre de Tables par Rangée'
    }[key] || key;
}

// Fonction pour créer un select (vous l'avez déjà)
function createSelect(name, options, selected = '', exclude = []) {
    const select = document.createElement('select');
    select.name = name;
    select.id = name;

    options.forEach(opt => {
        const val = opt.value ?? opt; // opt peut être une chaîne ou un objet {value: ..., label: ...}
        const labelText = opt.label ?? opt;
        if (exclude.includes(val)) return;
        const option = document.createElement('option');
        option.value = val;
        option.textContent = labelText;
        if (String(val) === String(selected)) option.selected = true; // Comparaison en chaîne pour flexibilité
        select.appendChild(option);
    });
    return select;
}

// Fonction pour créer le sélecteur de carte (vous l'avez déjà)
function createCarteSelect(cartes, selected = '') {
    const label = document.createElement('label');
    label.textContent = getLabel('DevEui'); // Utilise getLabel pour "Carte associée"
    label.setAttribute('for', 'DevEui');

    const select = document.createElement('select');
    select.name = 'DevEui';
    select.id = 'DevEui';

    cartes.forEach(carte => {
        const opt = document.createElement('option');
        opt.value = carte.DevEui;
        opt.textContent = carte.NomCarte || `Carte #${carte.DevEui}`; 
        if (carte.DevEui == selected) opt.selected = true;
        select.appendChild(opt);
    });

    const container = document.createElement('div');
    container.appendChild(label);
    container.appendChild(select);
    return container;
}

// Fonction pour créer les boutons de la modale (vous l'avez déjà)
function createButtonContainer(confirmText, onCancel) {
    const container = document.createElement('div');
    container.className = 'modal-buttons';

    const submit = document.createElement('button');
    submit.type = 'submit';
    submit.textContent = confirmText;

    const cancel = document.createElement('button');
    cancel.type = 'button';
    cancel.textContent = 'Annuler';
    cancel.addEventListener('click', onCancel);

    container.append(submit, cancel);
    return container;
}

// Récupère les options des champs select
export async function fetchSelectOptions() {
    try {
        const res = await fetch('getSelectOptions.php');
        const options = await res.json();
        return {
            EtatComposant: options.EtatComposant || [],
            GrandeurCapt: options.GrandeurCapt || [],
            Cartes: options.Cartes || [],
            Unite: options.Unite || [],
            Chapelles: options.Chapelles || [],
            Serres: options.Serres || [] // AJOUT : Récupérer les options de Serres
        };
    } catch (e) {
        console.error('Erreur chargement options select :', e);
        return { EtatComposant: [], GrandeurCapt: [], Cartes: [], Unite: [], Chapelles: [], Serres: [] };
    }
}

// Ouvre modale d’ajout
export async function openAddModal(currentTable, updateTable) {
    try {
        const res = await fetch('getTableData.php?table=' + currentTable);
        const data = await res.json();
        
        let columnsToGenerate = data.columns.filter(c =>
            c !== 'Actions' && 
            c !== 'IdCapteur' && 
            c !== 'IdChapelle' && 
            c !== 'DateMiseEnService'
        );

        if (currentTable === 'carte') {
            columnsToGenerate = columnsToGenerate.filter(c => c !== 'NomChapelle'); // IdChapelle déjà filtré
        } else if (currentTable === 'chapelle') {
            // IdSerre sera un select dédié. NomSerre est un alias et ne doit pas être un champ.
            columnsToGenerate = columnsToGenerate.filter(c => c !== 'IdSerre' && c !== 'NomSerre'); // MODIFIÉ ICI
        }

        const options = await fetchSelectOptions();
        const modal = document.createElement('div');
        modal.className = 'modal';
        if (document.body.classList.contains('dark-mode')) {
            modal.classList.add('dark-mode');
        }

        const form = document.createElement('form');
        form.className = 'modal-form';

        const titleContainer = document.createElement('div');
        titleContainer.className = 'modal-title-container';
        titleContainer.appendChild(createTitle(`Ajouter ${currentTable === 'chapelle' ? 'une' : 'un'} ${currentTable}`)); // Ajustement titre

        const closeImg = document.createElement('img');
        closeImg.src = '../img/croix.svg';
        closeImg.alt = 'Fermer';
        closeImg.className = 'modal-close-icon';
        closeImg.onclick = () => modal.remove();
        titleContainer.appendChild(closeImg);
        form.appendChild(titleContainer);

        columnsToGenerate.forEach(colKey => {
            const label = document.createElement('label');
            label.textContent = getLabel(colKey === 'NomCarte' ? 'Nom' : colKey); // Utiliser 'Nom' pour NomCarte
            label.setAttribute('for', colKey);
            let input;

            // Logique spécifique pour certains champs (EtatComposant, GrandeurCapt, Unite)
            if (colKey === 'EtatComposant') {
                input = createSelect(colKey, ['OK', 'Veille']);
            } else if (colKey === 'GrandeurCapt' && currentTable === 'capteur') { // S'assurer que c'est bien pour un capteur
                input = createSelect(colKey, options.GrandeurCapt);
                input.addEventListener('change', async e => {
                    const selectedGrandeur = e.target.value;
                    const uniteSelect = form.querySelector('#Unite');
                    if (uniteSelect) { // Vérifier que uniteSelect existe
                        uniteSelect.innerHTML = '';
                        try {
                            const resUnites = await fetch(`getUnitesParGrandeur.php?grandeur=${encodeURIComponent(selectedGrandeur)}`);
                            const unites = await resUnites.json();
                            unites.forEach(unite => {
                                const opt = document.createElement('option');
                                opt.value = unite;
                                opt.textContent = unite;
                                uniteSelect.appendChild(opt);
                            });
                        } catch (error) {
                            console.error('Erreur lors de la récupération des unités :', error);
                        }
                    }
                });
            } else if (colKey === 'Unite' && currentTable === 'capteur') { // S'assurer que c'est bien pour un capteur
                input = document.createElement('select');
                input.id = 'Unite';
                input.name = 'Unite';
            } else { // Champs texte par défaut
                input = document.createElement('input');
                input.type = (colKey === 'NbRangees' || colKey === 'NbTables') ? 'number' : 'text'; // Type number pour NbRangees/NbTables
                input.name = colKey;
                input.id = colKey;
                if (input.type === 'number') {
                    input.min = "0"; // Empêcher les valeurs négatives
                }
            }
            form.appendChild(label);
            form.appendChild(input);
        });

        // Ajout de sélecteurs spécifiques après la boucle
        if (currentTable === 'capteur') {
            const cartesPourSelect = options.Cartes.map(c => ({ DevEui: c.DevEui, NomCarte: c.Nom }));
            form.appendChild(createCarteSelect(cartesPourSelect));
        } else if (currentTable === 'carte') {
            const chapelleLabel = document.createElement('label');
            chapelleLabel.textContent = getLabel('IdChapelle');
            chapelleLabel.setAttribute('for', 'IdChapelle');
            
            const chapelleOptions = options.Chapelles.map(chap => ({ value: chap.IdChapelle, label: chap.Nom }));
            const chapelleSelect = createSelect('IdChapelle', chapelleOptions, '');
            
            const aucuneOption = document.createElement('option');
            aucuneOption.value = '';
            aucuneOption.textContent = '-- Aucune chapelle --';
            chapelleSelect.prepend(aucuneOption);
            chapelleSelect.value = '';

            form.appendChild(chapelleLabel);
            form.appendChild(chapelleSelect);
        } else if (currentTable === 'chapelle') { // NOUVEAU : Pour ajouter une chapelle
            const serreLabel = document.createElement('label');
            serreLabel.textContent = getLabel('IdSerre');
            serreLabel.setAttribute('for', 'IdSerre');
            
            const serreOptions = options.Serres.map(serre => ({ value: serre.IdSerre, label: serre.Nom }));
            const serreSelect = createSelect('IdSerre', serreOptions, ''); 
            
            // Une chapelle doit appartenir à une serre (IdSerre NOT NULL dans BDD)
            // Il est donc important qu'une serre soit sélectionnée.
            // On peut ajouter une option placeholder si la liste est vide ou pour guider.
            if (serreOptions.length === 0) {
                serreSelect.innerHTML = '<option value="">Aucune serre disponible</option>';
                serreSelect.disabled = true;
            } else {
                 const placeholderOption = document.createElement('option');
                 placeholderOption.value = '';
                 placeholderOption.textContent = '-- Sélectionner une serre --';
                 placeholderOption.disabled = true; // Ne peut pas être sélectionné
                 placeholderOption.selected = true; // Affiché par défaut
                 serreSelect.prepend(placeholderOption);
                 serreSelect.required = true; // Rend le champ obligatoire HTML5
            }

            form.appendChild(serreLabel);
            form.appendChild(serreSelect);
        }

        form.appendChild(createButtonContainer('Ajouter', () => modal.remove()));
        modal.appendChild(form);
        document.body.appendChild(modal);
        modal.style.display = 'flex';

        const grandeurSelect = form.querySelector('#GrandeurCapt');
        if (grandeurSelect && grandeurSelect.value) {
            grandeurSelect.dispatchEvent(new Event('change'));
        }

        form.addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(form);

            if (currentTable === 'chapelle' && !formData.get('IdSerre')) {
                alert('Veuillez sélectionner une serre pour la chapelle.');
                return;
            }

            if (currentTable === 'capteur') {
                // ... (validation seuils capteur existante)
            }

            formData.append('table', currentTable);
            const res = await fetch('insertRow.php', { //
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                alert('Ajout réussi');
                modal.remove();
                updateTable(currentTable);
            } else {
                alert('Erreur Ajout : ' + result.error);
            }
        });
    } catch (error) {
        console.error('Erreur modale ajout :', error);
    }
}

// Ouvre modale édition
export async function openEditModal(rowData, currentTable, updateTable) {
    try {
        console.log("[MODAL.JS] Opening edit modal for:", currentTable, "with data:", JSON.stringify(rowData)); //
        const options = await fetchSelectOptions(); //
        const modal = document.createElement('div'); //
        modal.className = 'modal'; //
        if (document.body.classList.contains('dark-mode')) { //
            modal.classList.add('dark-mode'); //
        }

        const form = document.createElement('form'); //
        form.className = 'modal-form'; //

        const titleContainer = document.createElement('div'); //
        titleContainer.className = 'modal-title-container'; //
        titleContainer.appendChild(createTitle(`Modifier ${currentTable}`)); //

        const closeImg = document.createElement('img'); //
        closeImg.src = '../img/croix.svg'; //
        closeImg.alt = 'Fermer'; //
        closeImg.className = 'modal-close-icon'; //
        closeImg.onclick = () => modal.remove(); //
        titleContainer.appendChild(closeImg); //
        form.appendChild(titleContainer); //

        const ignore = ['IdCapteur', 'DateMiseEnService', 'Actions']; 
        if (currentTable === 'carte') { 
            ignore.push('DevEui'); 
            ignore.push('IdChapelle'); 
            ignore.push('NomChapelle'); 
        }
        if (currentTable === 'chapelle') { 
            ignore.push('IdChapelle'); 
            ignore.push('IdSerre');    
            ignore.push('NomSerre'); // MODIFIÉ ICI : Assurer que NomSerre est ignoré
        }

        Object.entries(rowData).forEach(([key, val]) => { //
            if (currentTable === 'capteur' && key === 'DevEui') return; // Géré par createCarteSelect //
            if (ignore.includes(key)) return; //

            const label = document.createElement('label'); //
            label.textContent = getLabel(key === 'NomCarte' ? 'Nom' : key); //
            label.setAttribute('for', key); //
            let input; //
            if (key === 'EtatComposant') { //
                const allEtats = ['OK', 'Veille', 'HS']; //
                input = createSelect(key, allEtats, val); //
            } else if (key === 'GrandeurCapt') { //
                input = createSelect(key, options.GrandeurCapt, val); //
                input.addEventListener('change', async (e) => { //
                    const selectedGrandeur = e.target.value; //
                    const uniteSelect = form.querySelector('#Unite'); //
                    uniteSelect.innerHTML = ''; //
                    try { //
                        const resUnites = await fetch(`getUnitesParGrandeur.php?grandeur=${encodeURIComponent(selectedGrandeur)}`); //
                        const unites = await resUnites.json(); //
                        unites.forEach(unite => { //
                            const opt = document.createElement('option'); //
                            opt.value = unite; //
                            opt.textContent = unite; //
                            uniteSelect.appendChild(opt); //
                        });
                        if (unites.includes(rowData.Unite)) { //
                           uniteSelect.value = rowData.Unite; //
                        }
                    } catch (error) { //
                        console.error("Erreur récupération unités :", error); //
                    }
                });
            } else if (key === 'Unite') { //
                input = document.createElement('select'); //
                input.name = 'Unite'; //
                input.id = 'Unite'; //
            } else { //
                input = document.createElement('input'); //
                input.type = (key === 'NbRangees' || key === 'NbTables') ? 'number' : 'text'; // Type number pour NbRangees/NbTables
                input.name = key; //
                input.id = key; //
                input.value = (val === null || val === undefined) ? '' : val; //
                if (typeof val === 'number' && (key === 'ValeurMin' || key === 'ValeurMax')) { //
                     input.value = parseFloat(val).toFixed(1); //
                }
                 if (input.type === 'number') { //
                    input.min = "0"; // Empêcher les valeurs négatives //
                }
            }
            form.appendChild(label); //
            form.appendChild(input); //
        });

        if (currentTable === 'capteur') { //
            const cartesPourSelect = options.Cartes.map(c => ({ DevEui: c.DevEui, NomCarte: c.Nom })); //
            form.appendChild(createCarteSelect(cartesPourSelect, rowData.DevEui)); //
        } else if (currentTable === 'carte') { //
            const chapelleLabel = document.createElement('label'); //
            chapelleLabel.textContent = getLabel('IdChapelle'); //
            chapelleLabel.setAttribute('for', 'IdChapelle'); //
            
            const chapelleOptions = options.Chapelles.map(chap => ({ value: chap.IdChapelle, label: chap.Nom })); //
            const chapelleSelect = createSelect('IdChapelle', chapelleOptions, rowData.IdChapelle || ''); //
            
            if (rowData.IdChapelle === null || rowData.IdChapelle === '' || rowData.IdChapelle === undefined) { //
                let aucuneOptionExists = Array.from(chapelleSelect.options).some(opt => opt.value === ''); //
                if (!aucuneOptionExists) { //
                    const aucuneOption = document.createElement('option'); //
                    aucuneOption.value = ''; //
                    aucuneOption.textContent = '-- Aucune chapelle --'; //
                    chapelleSelect.prepend(aucuneOption); //
                }
                chapelleSelect.value = ''; // Assurer la sélection de "Aucune" //
            } else if (!Array.from(chapelleSelect.options).some(opt => opt.value === '')) { //
                const aucuneOption = document.createElement('option'); //
                aucuneOption.value = ''; //
                aucuneOption.textContent = '-- Aucune chapelle --'; //
                chapelleSelect.prepend(aucuneOption); //
            }

            form.appendChild(chapelleLabel); //
            form.appendChild(chapelleSelect); //
        } else if (currentTable === 'chapelle') { //
            const serreLabel = document.createElement('label'); //
            serreLabel.textContent = getLabel('IdSerre'); //
            serreLabel.setAttribute('for', 'IdSerre'); //
            
            const serreOptions = options.Serres.map(serre => ({ value: serre.IdSerre, label: serre.Nom })); //
            const serreSelect = createSelect('IdSerre', serreOptions, rowData.IdSerre || ''); //
            
            if (serreOptions.length === 0) { //
                serreSelect.innerHTML = '<option value="">Aucune serre disponible</option>'; //
                serreSelect.disabled = true; //
            } else if (!rowData.IdSerre && serreOptions.length > 0) { //
                const placeholderOption = document.createElement('option'); //
                placeholderOption.value = ''; //
                placeholderOption.textContent = '-- Sélectionner une serre --'; //
                placeholderOption.disabled = true; //
                placeholderOption.selected = true; //
                serreSelect.prepend(placeholderOption); //
            }
            serreSelect.required = true; //

            form.appendChild(serreLabel); //
            form.appendChild(serreSelect); //
        }


        form.appendChild(createButtonContainer('Enregistrer', () => modal.remove())); //
        modal.appendChild(form); //
        document.body.appendChild(modal); //
        modal.style.display = 'flex'; //

        const grandeurSelect = form.querySelector('#GrandeurCapt'); //
        if (grandeurSelect) { //
             if (grandeurSelect.value) { //
                grandeurSelect.dispatchEvent(new Event('change')); //
             }
             setTimeout(() => { //
                const uniteSelect = form.querySelector('#Unite'); //
                if (uniteSelect && rowData.Unite && Array.from(uniteSelect.options).some(opt => opt.value === rowData.Unite)) { //
                    uniteSelect.value = rowData.Unite; //
                }
            }, 0); 
        }

        form.addEventListener('submit', async e => { //
            e.preventDefault(); //
            const formData = new FormData(form); //

            console.log("[MODAL.JS] Soumission du formulaire pour la table:", currentTable); //

            if (currentTable === 'chapelle' && !formData.get('IdSerre')) { //
                alert('Veuillez sélectionner une serre pour la chapelle.'); //
                return; //
            }
            if (currentTable === 'capteur') { //
                const valeurMinStr = formData.get('ValeurMin'); //
                const valeurMaxStr = formData.get('ValeurMax'); //

                console.log("[MODAL.JS VALIDATION] ValeurMin (string from formData):", `"${valeurMinStr}"`); //
                console.log("[MODAL.JS VALIDATION] ValeurMax (string from formData):", `"${valeurMaxStr}"`); //

                const minIsPresent = valeurMinStr !== null && valeurMinStr.trim() !== ''; //
                const maxIsPresent = valeurMaxStr !== null && valeurMaxStr.trim() !== ''; //

                console.log("[MODAL.JS VALIDATION] minIsPresent:", minIsPresent, " maxIsPresent:", maxIsPresent); //

                let valeurMin, valeurMax; //

                if (minIsPresent) { //
                    valeurMin = parseFloat(valeurMinStr); //
                    console.log("[MODAL.JS VALIDATION] ValeurMin (parsed):", valeurMin, "(is NaN:", isNaN(valeurMin), ")"); //
                    if (isNaN(valeurMin)) { //
                        alert('Erreur : Le seuil minimum doit être un nombre valide s\'il est renseigné.'); //
                        console.error("[MODAL.JS VALIDATION] Échec: ValeurMin n'est pas un nombre (NaN)"); //
                        return; //
                    }
                }

                if (maxIsPresent) { //
                    valeurMax = parseFloat(valeurMaxStr); //
                    console.log("[MODAL.JS VALIDATION] ValeurMax (parsed):", valeurMax, "(is NaN:", isNaN(valeurMax), ")"); //
                    if (isNaN(valeurMax)) { //
                        alert('Erreur : Le seuil maximum doit être un nombre valide s\'il est renseigné.'); //
                        console.error("[MODAL.JS VALIDATION] Échec: ValeurMax n'est pas un nombre (NaN)"); //
                        return; //
                    }
                }

                if (minIsPresent && maxIsPresent) { //
                    console.log(`[MODAL.JS VALIDATION] Comparaison: ${valeurMax} < ${valeurMin} ? Résultat: ${valeurMax < valeurMin}`); //
                    if (valeurMax < valeurMin) { //
                        alert('Erreur : Le seuil maximum ne peut pas être inférieur au seuil minimum.'); //
                        console.error("[MODAL.JS VALIDATION] Échec: ValeurMax < ValeurMin"); //
                        return; //
                    }
                }
            }
            
            console.log("[MODAL.JS VALIDATION] Validation passée ou non applicable. Envoi des données..."); //
            formData.append('table', currentTable); //
            const idKey = currentTable === 'capteur' ? 'IdCapteur' : //
                          currentTable === 'carte' ? 'DevEui' : //
                          currentTable === 'chapelle' ? 'IdChapelle' : // Clé primaire pour chapelle //
                          'id'; // Fallback générique //
            formData.append('id', rowData[idKey]); //

            console.log("FormData envoyé à updateRow.php:"); //
            for (let [key, value] of formData.entries()) { //
                console.log(key, value); //
            }

            const res = await fetch('updateRow.php', { //
                method: 'POST', //
                body: formData //
            });
            const result = await res.json(); //
            if (result.success) { //
                alert('Modification réussie'); //
                modal.remove(); //
                updateTable(currentTable); //
            } else { //
                alert('Erreur Modification: ' + result.error); //
            }
        });
    } catch (error) { //
        console.error('Erreur modale édition :', error); //
    }
}