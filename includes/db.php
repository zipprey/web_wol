<?php
$host = 'localhost';
$dbname = 'wol_db';
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    echo "Подключение успешно!"; // Добавьте эту строку
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>