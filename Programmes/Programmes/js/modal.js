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
        console.error('Erreur chargement options select', e);
        return { EtatComposant: [], GrandeurCapt: [], Cartes: [], Unite: [] };
    }
}

export async function openAddModal(currentTable, updateTable) {
    const modal = document.getElementById('modalOverlay'); // <- correspond à la div à afficher
    const form = document.getElementById('modalDynamicForm');
    const title = document.getElementById('modalTitle');
    const fieldsContainer = document.getElementById('modalFieldsContainer');
    const submitBtn = document.getElementById('modalSubmitBtn');

    // Préparation
    title.textContent = `Ajouter un ${currentTable}`;
    fieldsContainer.innerHTML = '';
    submitBtn.textContent = 'Ajouter';

    const res = await fetch('getTableData.php?table=' + currentTable);
    const data = await res.json();
    const options = await fetchSelectOptions();

    const columns = data.columns.filter(c =>
        c !== 'Actions' && c !== 'IdCapteur' && c !== 'DateMiseEnService'
    );

    columns.forEach(col => {
        const label = document.createElement('label');
        label.textContent = getLabel(col);
        label.setAttribute('for', col);

        let input;

        if (col === 'EtatComposant') {
            input = createSelect(col, options.EtatComposant);
        } else if (col === 'GrandeurCapt') {
            input = createSelect(col, options.GrandeurCapt);
        } else if (col === 'Unite') {
            input = createSelect(col, options.Unite);
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.name = col;
            input.id = col;
        }

        fieldsContainer.appendChild(label);
        fieldsContainer.appendChild(input);
    });

    // Carte liée pour capteur
    if (currentTable === 'capteur') {
        const labelCarte = document.createElement('label');
        labelCarte.textContent = 'Carte associée';
        labelCarte.setAttribute('for', 'DevEui');

        const select = createSelect('DevEui', options.Cartes.map(c => ({
            value: c.DevEui,
            label: c.NomCarte || `Carte #${c.DevEui}`
        })));

        fieldsContainer.appendChild(labelCarte);
        fieldsContainer.appendChild(select);
    }

    modal.style.display = 'flex';

    // Fermer la modale si on clique en dehors du formulaire
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Gère soumission
    form.onsubmit = async e => {
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
            modal.style.display = 'none';
            updateTable(currentTable);
        } else {
            alert('Erreur : ' + result.error);
        }
    };

    // Bouton annuler
    document.getElementById('modalCancelBtn').onclick = () => {
        modal.style.display = 'none';
    };
}

export async function openEditModal(rowData, currentTable, updateTable) {
    const modal = document.getElementById('modalOverlay'); // <- correspond à la div à afficher
    const form = document.getElementById('modalDynamicForm');
    const title = document.getElementById('modalTitle');
    const fieldsContainer = document.getElementById('modalFieldsContainer');
    const submitBtn = document.getElementById('modalSubmitBtn');

    title.textContent = `Modifier ${currentTable}`;
    fieldsContainer.innerHTML = '';
    submitBtn.textContent = 'Enregistrer';

    const ignore = ['IdCapteur', 'DateMiseEnService', 'Actions'];
    const options = await fetchSelectOptions();

    Object.entries(rowData).forEach(([key, val]) => {
        if (ignore.includes(key)) return;

        const label = document.createElement('label');
        label.textContent = getLabel(key);
        label.setAttribute('for', key);

        let input;
        if (key === 'EtatComposant') {
            input = createSelect(key, options.EtatComposant, val);
        } else if (key === 'GrandeurCapt') {
            input = createSelect(key, options.GrandeurCapt, val);
        } else if (key === 'Unite') {
            input = createSelect(key, options.Unite, val);
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.name = key;
            input.id = key;
            input.value = val;
        }

        fieldsContainer.appendChild(label);
        fieldsContainer.appendChild(input);
    });

    // Sélecteur carte
    if (currentTable === 'capteur') {
        const label = document.createElement('label');
        label.textContent = 'Carte associée';
        label.setAttribute('for', 'DevEui');

        const select = createSelect('DevEui', options.Cartes.map(c => ({
            value: c.DevEui,
            label: c.NomCarte || `Carte #${c.DevEui}`
        })), rowData.DevEui);

        fieldsContainer.appendChild(label);
        fieldsContainer.appendChild(select);
    }

    modal.style.display = 'flex';

    // Fermer la modale si on clique en dehors du formulaire
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    form.onsubmit = async e => {
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
            modal.style.display = 'none';
            updateTable(currentTable);
        } else {
            alert('Erreur : ' + result.error);
        }
    };

    document.getElementById('modalCancelBtn').onclick = () => {
        modal.style.display = 'none';
    };
}

// Helpers
function getLabel(col) {
    return {
        EtatComposant: 'État du composant',
        GrandeurCapt: 'Grandeur',
        Unite: 'Unité'
    }[col] || col;
}

function createSelect(name, list, selected = '') {
    const select = document.createElement('select');
    select.name = name;
    select.id = name;

    list.forEach(opt => {
        let val = opt.value ?? opt;
        let text = opt.label ?? opt;

        const option = document.createElement('option');
        option.value = val;
        option.textContent = text;
        if (val == selected) option.selected = true;
        select.appendChild(option);
    });

    return select;
}