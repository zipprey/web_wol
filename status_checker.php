<?php
require_once 'includes/db.php';

// Получаем все компьютеры из БД
$pcs = $db->query("SELECT id, ip FROM pcs")->fetchAll(PDO::FETCH_ASSOC);

// Начинаем транзакцию для пакетного обновления
$db->beginTransaction();

try {
    foreach ($pcs as $pc) {
        // Определяем команду ping для текущей ОС
        $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' 
            ? "ping -n 1 -w 1000 " . escapeshellarg($pc['ip'])  // Windows: 1 сек таймаут
            : "ping -c 1 -W 1 " . escapeshellarg($pc['ip']);    // Linux: 1 сек таймаут
        
        // Выполняем ping и определяем статус
        exec($command, $output, $result);
        $is_online = ($result === 0) ? 1 : 0; // 1 - онлайн, 0 - оффлайн
        
        // Готовим запрос на обновление
        $stmt = $db->prepare("UPDATE pcs SET is_online = ? WHERE id = ?");
        $stmt->execute([$is_online, $pc['id']]);
    }
    
    // Подтверждаем все изменения
    $db->commit();
    
} catch (Exception $e) {
    // Откатываем при ошибке
    $db->rollBack();
    die("Ошибка: " . $e->getMessage());
}
?>