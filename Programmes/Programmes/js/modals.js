export async function fetchSelectOptions() {
    try {
        const response = await fetch('getSelectOptions.php');
        const options = await response.json();
        return {
            EtatComposant: options.EtatComposant || [],
            GrandeurCapt: options.GrandeurCapt || [],
            Cartes: options.Cartes || [],
            Unite: options.Unite || []
        };
    } catch (error) {
        console.error('Erreur chargement options select :', error);
        return { EtatComposant: [], GrandeurCapt: [], Cartes: [], Unite: [] };
    }
}

export async function openAddModal(currentTable, updateTable) {
    try {
        const response = await fetch('getTableData.php?table=' + currentTable);
        const data = await response.json();
        const columns = data.columns.filter(col =>
            col !== 'Actions' && col !== 'IdCapteur' && col !== 'DateMiseEnService'
        );

        const selectOptions = await fetchSelectOptions();
        const modal = document.createElement('div');
        modal.className = 'modal';

        const form = document.createElement('form');
        form.className = 'modal-form';

        form.appendChild(createTitle(`Ajouter un ${currentTable}`));

        columns.forEach(column => {
            const label = document.createElement('label');
            label.setAttribute('for', column);

            let input;
            switch (column) {
                case 'EtatComposant':
                case 'GrandeurCapt':
                case 'Unite':
                    label.textContent = getLabelText(column);
                    input = createSelect(column, selectOptions[column]);
                    break;
                default:
                    label.textContent = column;
                    input = document.createElement('input');
                    input.type = 'text';
                    input.name = column;
                    input.required = true;
            }

            input.name = column;
            input.id = column;

            form.appendChild(label);
            form.appendChild(input);
        });

        if (currentTable === 'capteur') {
            form.appendChild(createCarteSelect(selectOptions.Cartes));
        }

        const btnContainer = createButtonContainer('Ajouter', () => modal.remove());

        form.appendChild(btnContainer);
        modal.appendChild(form);
        document.body.appendChild(modal);
        modal.style.display = 'flex';

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
                alert('Ajout réussi !');
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

export function openEditModal(rowData, currentTable, updateTable) {
    fetchSelectOptions().then(options => {
        const modal = document.createElement('div');
        modal.className = 'modal';

        const form = document.createElement('form');
        form.className = 'modal-form';

        form.appendChild(createTitle(`Modifier ${currentTable}`));

        const ignore = ['IdCapteur', 'IdCarte', 'DateMiseEnService', 'Actions'];

        Object.entries(rowData).forEach(([key, val]) => {
            if (ignore.includes(key)) return;

            const label = document.createElement('label');
            label.setAttribute('for', key);
            label.textContent = key;

            let input;
            if (['EtatComposant', 'GrandeurCapt', 'Unite'].includes(key)) {
                input = createSelect(key, options[key], val);
            } else {
                input = document.createElement('input');
                input.type = 'text';
                input.name = key;
                input.value = val;
                input.required = true;
            }

            input.name = key;
            input.id = key;

            form.appendChild(label);
            form.appendChild(input);
        });

        if (currentTable === 'capteur') {
            form.appendChild(createCarteSelect(options.Cartes, rowData.IdCarte));
        }

        const btnContainer = createButtonContainer('Enregistrer', () => modal.remove());
        form.appendChild(btnContainer);
        modal.appendChild(form);
        document.body.appendChild(modal);
        modal.style.display = 'flex';

        form.addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('table', currentTable);

            const idKey = currentTable === 'capteur' ? 'IdCapteur' : 'IdCarte';
            formData.append('id', rowData[idKey]);

            const res = await fetch('updateRow.php', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();
            if (result.success) {
                alert("Modification réussie !");
                modal.remove();
                updateTable(currentTable);
            } else {
                alert("Erreur : " + result.error);
            }
        });
    });
}

// Helpers
function createTitle(text) {
    const title = document.createElement('h2');
    title.textContent = text;
    return title;
}

function getLabelText(key) {
    return {
        EtatComposant: 'État du composant',
        GrandeurCapt: 'Grandeur du capteur',
        Unite: 'Unité'
    }[key] || key;
}

function createSelect(name, options, selected = '') {
    const select = document.createElement('select');
    options.forEach(val => {
        const opt = document.createElement('option');
        opt.value = val;
        opt.textContent = val;
        if (val == selected) opt.selected = true;
        select.appendChild(opt);
    });
    return select;
}

function createCarteSelect(cartes, selected = '') {
    const label = document.createElement('label');
    label.textContent = 'Carte associée';
    label.setAttribute('for', 'IdCarte');

    const select = document.createElement('select');
    select.name = 'IdCarte';
    select.id = 'IdCarte';

    cartes.forEach(carte => {
        const opt = document.createElement('option');
        opt.value = carte.IdCarte;
        opt.textContent = carte.NomCarte || `Carte #${carte.IdCarte}`;
        if (carte.IdCarte == selected) opt.selected = true;
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