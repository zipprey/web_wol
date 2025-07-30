<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
checkAuth();
checkAdmin();

// Получаем ID компьютера
$pc_id = $_GET['id'] ?? 0;

// Проверяем существование компьютера
$pc = $db->query("SELECT * FROM pcs WHERE id = $pc_id")->fetch();

if ($pc) {
    // Удаляем сначала связи в user_pc_access
    $db->prepare("DELETE FROM user_pc_access WHERE pc_id = ?")->execute([$pc_id]);
    
    // Затем удаляем сам компьютер
    $db->prepare("DELETE FROM pcs WHERE id = ?")->execute([$pc_id]);
    
    // Перенаправляем с сообщением об успехе
    header("Location: /admin/computers/?deleted=1");
} else {
    // Если компьютер не найден
    header("Location: /admin/computers/?error=1");
}
exit;