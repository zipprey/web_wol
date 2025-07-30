<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/db.php';
checkAuth();
checkAdmin();

header('X-Content-Type-Options: nosniff');
header('Content-Type: text/html; charset=utf-8');

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Массовое добавление/удаление
    if (isset($_POST['bulk_action'], $_POST['user_id'], $_POST['pc_ids'])) {
        if ($_POST['bulk_action'] === 'add_all') {
            foreach ($_POST['pc_ids'] as $pc_id) {
                $db->prepare("INSERT IGNORE INTO user_pc_access (user_id, pc_id) VALUES (?, ?)")
                   ->execute([$_POST['user_id'], $pc_id]);
            }
        }
        elseif ($_POST['bulk_action'] === 'remove_all') {
            $placeholders = rtrim(str_repeat('?,', count($_POST['pc_ids'])), ',');
            $db->prepare("DELETE FROM user_pc_access WHERE user_id = ? AND pc_id IN ($placeholders)")
               ->execute(array_merge([$_POST['user_id']], $_POST['pc_ids']));
        }
    }
}

// Получаем данные
$users = $db->query("SELECT * FROM users ORDER BY login")->fetchAll();
$pcs = $db->query("SELECT * FROM pcs ORDER BY name")->fetchAll();

// Выбранный пользователь
$selected_user = $_POST['user_id'] ?? $users[0]['id'];

// Получаем текущие права доступа
$access = $db->query("SELECT pc_id FROM user_pc_access WHERE user_id = $selected_user")
            ->fetchAll(PDO::FETCH_COLUMN);
$assigned_pcs = array_filter($pcs, fn($pc) => in_array($pc['id'], $access));
$available_pcs = array_filter($pcs, fn($pc) => !in_array($pc['id'], $access));
?>

<div class="container-fluid full-width-container">
    <div class="card border-0">
        <div class="card-header full-width-header bg-primary text-white">
            <h2 class="mb-0">Управление правами доступа</h2>
        </div>
        
        <div class="card-body p-0">
            <form method="post" id="mainForm">
                <div class="card-footer bg-light p-2">
                    <button type="submit" name="bulk_action" value="add_all" class="btn btn-sm btn-outline-success">
                        Добавить выбранные
                    </button>
                    <button type="submit" name="bulk_action" value="remove_all" class="btn btn-sm btn-outline-danger ms-2">
                        Удалить выбранные
                    </button>
                </div>
                <input type="hidden" name="user_id" value="<?= $selected_user ?>">
                
                <div class="table-row-container">
                    <!-- Таблица пользователей -->
                    <div class="table-wrapper users-table">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th colspan="2">Пользователи</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr class="<?= $selected_user == $user['id'] ? 'table-active' : '' ?>" 
                                        onclick="selectUser(<?= $user['id'] ?>)">
                                        <td class="text-truncate" title="<?= htmlspecialchars($user['login']) ?>">
                                            <?= htmlspecialchars($user['login']) ?>
                                        </td>
                                        <td><?= $user['is_admin'] ? 'Админ' : 'Польз.' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Таблица назначенных компьютеров -->
                    <div class="table-wrapper assigned-pcs">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="40"><input type="checkbox" id="selectAssigned" onclick="toggleCheckboxes('assigned')"></th>
                                        <th>Компьютер</th>
                                        <th>IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($assigned_pcs)): ?>
                                    <tr><td colspan="3" class="text-center text-muted">Нет назначенных компьютеров</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($assigned_pcs as $pc): ?>
                                    <tr>
                                        <td><input type="checkbox" name="pc_ids[]" value="<?= $pc['id'] ?>" class="assigned-checkbox"></td>
                                        <td class="text-truncate" title="<?= htmlspecialchars($pc['name']) ?>"><?= htmlspecialchars($pc['name']) ?></td>
                                        <td class="text-truncate"><?= $pc['ip'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Таблица доступных компьютеров -->
                    <div class="table-wrapper available-pcs">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="40"><input type="checkbox" id="selectAvailable" onclick="toggleCheckboxes('available')"></th>
                                        <th>Компьютер</th>
                                        <th>IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($available_pcs)): ?>
                                    <tr><td colspan="3" class="text-center text-muted">Нет доступных компьютеров</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($available_pcs as $pc): ?>
                                    <tr>
                                        <td><input type="checkbox" name="pc_ids[]" value="<?= $pc['id'] ?>" class="available-checkbox"></td>
                                        <td class="text-truncate" title="<?= htmlspecialchars($pc['name']) ?>"><?= htmlspecialchars($pc['name']) ?></td>
                                        <td class="text-truncate"><?= $pc['ip'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function selectUser(userId) {
    document.querySelector('input[name="user_id"]').value = userId;
    document.getElementById('mainForm').submit();
}

function toggleCheckboxes(type) {
    const checkboxes = document.querySelectorAll(`.${type}-checkbox`);
    const masterCheckbox = document.getElementById(`select${type.charAt(0).toUpperCase() + type.slice(1)}`);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = masterCheckbox.checked;
    });
}
</script>

<style>
.container-fluid {
    padding: 0;
    margin: 0;
    max-width: 100%;
}

.table-row-container {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 0;
}

.table-wrapper {
    flex: 1 0 33%;
    min-width: 300px;
    overflow: hidden;
    border-right: 1px solid #dee2e6;
}

.table-wrapper:last-child {
    border-right: none;
}

.table-responsive {
    overflow-x: auto;
    height: calc(100vh - 200px);
}

.table {
    table-layout: fixed;
    width: 100%;
    margin-bottom: 0;
}

.table th, .table td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 0.5rem;
}

.text-truncate {
    max-width: 150px;
    vertical-align: middle;
}

.table-hover tr {
    cursor: pointer;
}

.table-active {
    background-color: #e9f7fe;
}

.card {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
}

.card-header {
    padding: 0.75rem 1.25rem;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.card-body {
    padding: 0;
}

.card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

input[type="checkbox"] {
    transform: scale(1.2);
    cursor: pointer;
    margin-left: 5px;
}
</style>

<?php require_once '../includes/footer.php'; ?>