import { openEditModal, openAddModal, fetchSelectOptions } from '../js/modal.js';

// Lancer la vérification des capteurs & cartes inactifs dès chargement
window.addEventListener('DOMContentLoaded', () => {
    fetch('checkCapteursInactifs.php')
        .then(r => r.text())
        .then(txt => console.log('[Capteurs vérifiés] ' + txt))
        .catch(err => console.error('[Erreur capteurs]', err));

    fetch('checkCartesInactives.php')
        .then(r => r.text())
        .then(txt => console.log('[Cartes vérifiées] ' + txt))
        .catch(err => console.error('[Erreur cartes]', err));
});

// Interface dynamique
document.addEventListener('DOMContentLoaded', function () {
    const buttons = {
        serre: 'selectSerre',
        chapelle: 'selectChapelle',
        carte: 'selectCarte',
        capteur: 'selectCapteur',
        utilisateur: 'selectUtilisateur'
    };

    const searchInput = document.getElementById('searchInput');
    const tableHeader = document.querySelector('.tabParametrage thead tr');
    const tableBody = document.querySelector('.tabParametrage tbody');
    let currentTable = 'capteur';

    // Gérer les boutons de sélection de table
    Object.entries(buttons).forEach(([table, id]) => {
        document.getElementById(id).addEventListener('click', () => updateTable(table));
    });

    // Filtrage par texte
    searchInput.addEventListener('input', () => {
        const term = searchInput.value.toLowerCase();
        document.querySelectorAll('.divTableau tbody tr').forEach(row => {
            const visible = Array.from(row.cells).some(cell =>
                cell.textContent.toLowerCase().includes(term)
            );
            row.style.display = visible ? '' : 'none';
        });
    });

    // Met à jour l’affichage d’une table
    async function updateTable(tableName) {
        currentTable = tableName;

        // Activer le bon bouton
        Object.entries(buttons).forEach(([key, id]) => {
            document.getElementById(id).classList.toggle('active', key === tableName);
        });

        try {
            const res = await fetch('getTableData.php?table=' + tableName);
            const { columns, rows } = await res.json();

            tableHeader.innerHTML = '';
            tableBody.innerHTML = '';

            columns.forEach(col => {
                const th = document.createElement('th');
                th.textContent = col;
                th.classList.add(col.toLowerCase());
                if (col.toLowerCase() === 'commentaire') th.classList.add('commentaire');

                // Bouton Ajouter
                if (col === 'Actions' && ['carte', 'capteur'].includes(tableName)) {
                    const btn = document.createElement('img');
                    btn.src = '../img/plus.svg';
                    btn.alt = 'Ajouter';
                    btn.title = 'Ajouter';
                    btn.className = 'addImage';
                    btn.style.cursor = 'pointer';
                    btn.addEventListener('click', () => openAddModal(currentTable, updateTable));
                    th.appendChild(btn);
                }

                tableHeader.appendChild(th);
            });

            // Lignes
            rows.forEach(row => {
                const tr = document.createElement('tr');

                columns.forEach(col => {
                    const td = document.createElement('td');
                    td.classList.add(col.toLowerCase());

                    if (col === 'Actions') {
                        const idKey = Object.keys(row).find(k => k.toLowerCase().includes('id') || k === 'DevEui');
                        const id = row[idKey];

                        const rowSansActions = { ...row };
                        delete rowSansActions.Actions;

                        // Modifier
                        const modifyBtn = createActionBtn('../img/modifier.svg', 'Modifier', () =>
                            openEditModal(rowSansActions, currentTable, updateTable)
                        );

                        // Supprimer
                        const deleteBtn = createActionBtn('../img/moins.svg', 'Supprimer', () => {
                            if (confirm(`Supprimer ce ${currentTable} ?`)) deleteRow(currentTable, id);
                        });
                        deleteBtn.classList.add('deleteImage');
                        deleteBtn.style.marginLeft = '5px';

                        td.appendChild(modifyBtn);
                        td.appendChild(deleteBtn);
                    } else {
                        td.textContent = row[col];
                    }

                    tr.appendChild(td);
                });

                tableBody.appendChild(tr);
            });

            searchInput.dispatchEvent(new Event('input')); // Rafraîchit filtre
        } catch (err) {
            console.error('Erreur updateTable :', err);
        }
    }

    async function deleteRow(table, id) {
        try {
            const res = await fetch('deleteRow.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `table=${table}&id=${id}`
            });
            const result = await res.json();
            if (result.success) {
                alert(`${table} supprimé`);
                updateTable(currentTable);
            } else {
                alert('Erreur : ' + result.error);
            }
        } catch (e) {
            console.error('Erreur suppression :', e);
            alert('Erreur lors de la suppression');
        }
    }

    function createActionBtn(src, alt, onClick) {
        const img = document.createElement('img');
        img.src = src;
        img.alt = alt;
        img.title = alt;
        img.className = alt.toLowerCase() + 'Image';
        img.style.cursor = 'pointer';
        img.style.transition = 'transform 0.2s ease';
        img.addEventListener('mouseenter', () => img.style.transform = 'scale(1.3)');
        img.addEventListener('mouseleave', () => img.style.transform = 'scale(1)');
        img.addEventListener('click', onClick);
        return img;
    }

    updateTable(currentTable); // Chargement initial
});