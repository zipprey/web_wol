<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/db.php';
checkAuth();
checkAdmin();

// Получаем ID компьютера
$pc_id = $_GET['id'] ?? 0;
$pc = $db->query("SELECT * FROM pcs WHERE id = $pc_id")->fetch();

if (!$pc) {
    header("Location: /admin/computers/");
    exit;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $ip = trim($_POST['ip']);
    $mac = trim($_POST['mac']);

    $stmt = $db->prepare("UPDATE pcs SET name = ?, ip = ?, mac = ? WHERE id = ?");
    $stmt->execute([$name, $ip, $mac, $pc_id]);
    
    header("Location: /admin/computers/?success=1");
    exit;
}
?>

<div class="container">
    <div class="card">
        <h2>Редактирование компьютера</h2>
        
        <form method="POST" class="form-grid">
            <div class="form-group">
                <label>Имя ПК</label>
                <input type="text" name="name" class="form-control" 
                       value="<?= htmlspecialchars($pc['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>IP-адрес</label>
                <input type="text" name="ip" class="form-control" 
                       value="<?= htmlspecialchars($pc['ip']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>MAC-адрес</label>
                <input type="text" name="mac" class="form-control" 
                       value="<?= htmlspecialchars($pc['mac']) ?>" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="/admin/computers/" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>