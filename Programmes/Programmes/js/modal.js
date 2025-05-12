export async function fetchSelectOptions() {
    try {
        const res = await fetch('getSelectOptions.php');
        const options = await res.json();
        return {
            EtatComposant: options.EtatComposant || [],
            GrandeurCapt: options.GrandeurCapt || [],
            Cartes: options.Cartes || [],
            Unite: options.Unite || []
        };
    } catch (e) {
        console.error('Erreur chargement options select :', e);
        return { EtatComposant: [], GrandeurCapt: [], Cartes: [], Unite: [] };
    }
}

export async function openAddModal(currentTable, updateTable) {
    try {
        const res = await fetch('getTableData.php?table=' + currentTable);
        const data = await res.json();
        const columns = data.columns.filter(c =>
            c !== 'Actions' && c !== 'IdCapteur' && c !== 'DateMiseEnService'
        );
        const options = await fetchSelectOptions();

        const modal = document.createElement('div');
        modal.className = 'modal';
        if (document.body.classList.contains('dark-mode')) {
            modal.classList.add('dark-mode');
        }
        modal.appendChild(createCloseButton(() => modal.remove()));

        const form = document.createElement('form');
        form.className = 'modal-form';

        const titleContainer = document.createElement('div');
        titleContainer.className = 'modal-title-container';

        titleContainer.appendChild(createTitle(`Ajouter un ${currentTable}`));
        titleContainer.appendChild(createCloseButton(() => modal.remove()));
        form.appendChild(titleContainer);

        columns.forEach(col => {
            const label = document.createElement('label');
            label.textContent = getLabel(col);
            label.setAttribute('for', col);

            let input;
            if (col === 'EtatComposant') {
                // Limite aux valeurs OK et Veille
                input = createSelect(col, ['OK', 'Veille']);
            } else if (col === 'GrandeurCapt') {
                input = createSelect(col, options.GrandeurCapt);

                // Gestion dynamique des unités
                input.addEventListener('change', async e => {
                    const selectedGrandeur = e.target.value;
                    const uniteSelect = form.querySelector('#Unite');

                    uniteSelect.innerHTML = ''; // Réinitialise

                    try {
                        const res = await fetch(`getUnitesParGrandeur.php?grandeur=${encodeURIComponent(selectedGrandeur)}`);
                        const unites = await res.json();

                        unites.forEach(unite => {
                            const opt = document.createElement('option');
                            opt.value = unite;
                            opt.textContent = unite;
                            uniteSelect.appendChild(opt);
                        });
                    } catch (error) {
                        console.error('Erreur lors de la récupération des unités :', error);
                    }
                });
            } else if (col === 'Unite') {
                input = document.createElement('select');
                input.id = 'Unite';
                input.name = 'Unite';
            } else {
                input = document.createElement('input');
                input.type = 'text';
                input.name = col;
                input.id = col;
            }

            form.appendChild(label);
            form.appendChild(input);
        });

        if (currentTable === 'capteur') {
            form.appendChild(createCarteSelect(options.Cartes));
        }

        form.appendChild(createButtonContainer('Ajouter', () => modal.remove()));
        modal.appendChild(form);
        document.body.appendChild(modal);
        modal.style.display = 'flex';

        // Déclenche le changement de grandeur par défaut si sélectionnée
        const grandeurSelect = form.querySelector('#GrandeurCapt');
        if (grandeurSelect && grandeurSelect.value) {
            grandeurSelect.dispatchEvent(new Event('change'));
        }

        form.addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('table', currentTable);

            const res = await fetch('insertRow.php', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();
            if (result.success) {
                alert('Ajout réussi');
                modal.remove();
                updateTable(currentTable);
            } else {
                alert('Erreur : ' + result.error);
            }
        });
    } catch (error) {
        console.error('Erreur modale ajout :', error);
    }
}

export async function openEditModal(rowData, currentTable, updateTable) {
    try {
        const options = await fetchSelectOptions();
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.appendChild(createCloseButton(() => modal.remove()));

        const form = document.createElement('form');
        form.className = 'modal-form';

        const titleContainer = document.createElement('div');
        titleContainer.className = 'modal-title-container';

        titleContainer.appendChild(createTitle(`Modifier ${currentTable}`));
        titleContainer.appendChild(createCloseButton(() => modal.remove()));
        form.appendChild(titleContainer);

        const ignore = ['IdCapteur', 'DevEui', 'DateMiseEnService', 'Actions'];

        Object.entries(rowData).forEach(([key, val]) => {
            if (ignore.includes(key)) return;

            const label = document.createElement('label');
            label.textContent = getLabel(key);
            label.setAttribute('for', key);

            let input;
            if (key === 'EtatComposant') {
                const allEtats = ['OK', 'Veille', 'HS'];
                const etatsDisponibles = allEtats.filter(etat => etat !== val);
                input = createSelect(key, etatsDisponibles);
            } else if (key === 'GrandeurCapt') {
                input = createSelect(key, options[key], val);
                input.addEventListener('change', async (e) => {
                    const selectedGrandeur = e.target.value;
                    const uniteSelect = form.querySelector('#Unite');
                    uniteSelect.innerHTML = '';

                    try {
                        const res = await fetch(`getUnitesParGrandeur.php?grandeur=${encodeURIComponent(selectedGrandeur)}`);
                        const unites = await res.json();
                        unites.forEach(unite => {
                            const opt = document.createElement('option');
                            opt.value = unite;
                            opt.textContent = unite;
                            uniteSelect.appendChild(opt);
                        });

                        // Si l’ancienne unité est toujours valide, la remettre
                        if (unites.includes(rowData.Unite)) {
                            uniteSelect.value = rowData.Unite;
                        }
                    } catch (error) {
                        console.error("Erreur récupération unités :", error);
                    }
                });
            } else if (key === 'Unite') {
                input = document.createElement('select');
                input.name = 'Unite';
                input.id = 'Unite';
            } else {
                input = document.createElement('input');
                input.type = 'text';
                input.name = key;
                input.id = key;
                input.value = typeof val === 'number' ? parseFloat(val).toFixed(1) : val;
            }

            form.appendChild(label);
            form.appendChild(input);
        });

        if (currentTable === 'capteur') {
            form.appendChild(createCarteSelect(options.Cartes, rowData.DevEui));
        }

        form.appendChild(createButtonContainer('Enregistrer', () => modal.remove()));
        modal.appendChild(form);
        document.body.appendChild(modal);
        modal.style.display = 'flex';

        // Déclencher l’event pour charger les unités si Grandeur existe déjà
        const grandeurSelect = form.querySelector('#GrandeurCapt');
        if (grandeurSelect && grandeurSelect.value) {
            grandeurSelect.dispatchEvent(new Event('change'));
        }

        form.addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('table', currentTable);

            const idKey = currentTable === 'capteur' ? 'IdCapteur' : 'DevEui';
            formData.append('id', rowData[idKey]);

            const res = await fetch('updateRow.php', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();
            if (result.success) {
                alert('Modification réussie');
                modal.remove();
                updateTable(currentTable);
            } else {
                alert('Erreur : ' + result.error);
            }
        });
    } catch (error) {
        console.error('Erreur modale édition :', error);
    }
}

function createTitle(text) {
    const title = document.createElement('h2');
    title.textContent = text;
    return title;
}

function getLabel(key) {
    return {
        EtatComposant: 'État du composant',
        GrandeurCapt: 'Grandeur du capteur',
        Unite: 'Unité',
        DevEui: 'Carte associée'
    }[key] || key;
}

function createSelect(name, options, selected = '', exclude = []) {
    const select = document.createElement('select');
    select.name = name;
    select.id = name;

    options.forEach(opt => {
        const val = opt.value ?? opt;
        const label = opt.label ?? opt;

        // Si cette valeur est dans la liste des exclusions, on la saute
        if (exclude.includes(val)) return;

        const option = document.createElement('option');
        option.value = val;
        option.textContent = label;
        if (val == selected) option.selected = true;
        select.appendChild(option);
    });

    return select;
}

function createCarteSelect(cartes, selected = '') {
    const label = document.createElement('label');
    label.textContent = 'Carte associée';
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

function createCloseButton(onClick) {
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.className = 'modal-close';
    closeBtn.type = 'button';
    closeBtn.addEventListener('click', onClick);
    return closeBtn;
}