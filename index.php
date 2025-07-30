<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
checkAuth();

// Получаем доступные компьютеры
$stmt = $db->prepare("
    SELECT p.* FROM pcs p
    JOIN user_pc_access a ON p.id = a.pc_id
    WHERE a.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$pcs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>WoL - Панель управления</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <h1>Wake-on-LAN</h1>
            <div class="nav-links">
                <?php if ($_SESSION['is_admin']): ?>
                    <a href="admin/">Админ-панель</a>
                <?php endif; ?>
                <a href="logout.php">Выйти</a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <h2>Доступные компьютеры</h2>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>IP</th>
                        <th>Статус</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pcs as $pc): ?>
                    <tr>
                        <td><?= htmlspecialchars($pc['name']) ?></td>
                        <td><?= $pc['ip'] ?></td>
                        <td class="<?= $pc['is_online'] ? 'status-online' : 'status-offline' ?>">
                            <?= $pc['is_online'] ? '✅ Online' : '🔴 Offline' ?>
                        </td>
                        <td>
                            <?php if (!$pc['is_online']): ?>
                            <button class="btn btn-primary" onclick="wakePC('<?= $pc['mac'] ?>')">
                                Включить
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function wakePC(mac) {
            fetch(`wake.php?mac=${mac}`)
                .then(response => response.text())
                .then(msg => {
                    alert(msg);
                    location.reload(); // Обновляем страницу после включения
                });
        }
    </script>
</body>
</html>
<?php require_once 'includes/footer.php'; ?>