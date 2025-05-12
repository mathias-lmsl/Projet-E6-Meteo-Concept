<?php
require_once 'connectDB.php';

$tableName = $_GET['table'] ?? 'capteur';
$pdo = $bdd;

$tablesAutorisees = ['capteur', 'carte', 'serre', 'chapelle', 'utilisateur'];
if (!in_array($tableName, $tablesAutorisees)) {
    http_response_code(400);
    echo "Table non autorisée.";
    exit;
}

$query = $pdo->query("SELECT * FROM $tableName");
$rows = $query->fetchAll(PDO::FETCH_ASSOC);
$columns = array_keys($rows[0] ?? []);
$columns[] = 'Actions';

ob_start();
?>

<table class="tabParametrage">
    <thead>
        <tr>
            <?php foreach ($columns as $col): ?>
                <th class="<?= strtolower($col) ?>">
                    <?= htmlspecialchars($col) ?>
                    <?php if ($col === 'Actions' && in_array($tableName, ['carte', 'capteur'])): ?>
                        <img src="../img/plus.svg" alt="Ajouter" class="addImage"
                             title="Ajouter" style="cursor:pointer; margin-left:4px"
                             onclick="openAddModal()">
                    <?php endif; ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php foreach ($columns as $col): ?>
                    <td class="<?= strtolower($col) ?>">
                        <?php if ($col === 'Actions'): ?>
                            <?php
                                $idKey = array_keys($row)[0]; // ex: IdCapteur, IdCarte
                                $id = $row[$idKey];
                                $rowJSON = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                            ?>
                            <img src="../img/modifier.svg" alt="Modifier" class="modifyImage"
                                 data-id="<?= $id ?>" data-row="<?= $rowJSON ?>"
                                 onclick="openEditModal(JSON.parse(this.getAttribute('data-row')))"
                                 style="cursor:pointer">
                            <img src="../img/moins.svg" alt="Supprimer" class="deleteImage"
                                 data-id="<?= $id ?>" style="cursor:pointer"
                                 onclick="if(confirm('Confirmer suppression ?')) deleteRow('<?= $tableName ?>', <?= $id ?>)">
                        <?php else: ?>
                            <?= htmlspecialchars($row[$col] ?? '') ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
echo ob_get_clean();
