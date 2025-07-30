<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/db.php';
checkAuth();
checkAdmin();

// Показываем уведомления
if (isset($_GET['success'])) {
    echo '<div class="alert alert-success">Компьютер успешно обновлен!</div>';
}
if (isset($_GET['deleted'])) {
    echo '<div class="alert alert-success">Компьютер успешно удален!</div>';
}
if (isset($_GET['error'])) {
    echo '<div class="alert alert-danger">Ошибка: компьютер не найден</div>';
}

// Обработка добавления нового компьютера
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_pc'])) {
    $stmt = $db->prepare("INSERT INTO pcs (name, ip, mac) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['ip'], $_POST['mac']]);
    echo '<div class="alert alert-success">Компьютер успешно добавлен!</div>';
}
?>

<div class="container">
    <div class="card">
        <h2>Управление компьютерами</h2>
        
        <!-- Форма добавления компьютера -->
        <div class="mb-4">
            <h4>Добавить новый компьютер</h4>
            <form method="POST" class="form-grid">
                <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Имя ПК" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="ip" class="form-control" placeholder="IP-адрес" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="mac" class="form-control" placeholder="MAC-адрес" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="add_pc" class="btn btn-primary1">
                        Добавить компьютер
                    </button>
                </div>
            </form>
        </div>

        <!-- Список компьютеров -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>IP</th>
                        <th>MAC</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pcs = $db->query("SELECT * FROM pcs ORDER BY name")->fetchAll();
                    foreach ($pcs as $pc): ?>
                    <tr>
                        <td><?= $pc['id'] ?></td>
                        <td><?= htmlspecialchars($pc['name']) ?></td>
                        <td><?= $pc['ip'] ?></td>
                        <td><?= $pc['mac'] ?></td>
                        <td>
                            <a href="/admin/edit_pc/<?= $pc['id'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                            <a href="/admin/delete_pc/<?= $pc['id'] ?>" class="btn btn-sm btn-danger">Удалить</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>