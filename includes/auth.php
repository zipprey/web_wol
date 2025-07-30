<?php
session_start();

// Проверка авторизации
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /wol/login.php");
        exit;
    }
}

// Проверка прав администратора
function checkAdmin() {
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        die("Доступ запрещён!");
    }
}
?>
