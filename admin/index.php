<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
checkAuth();
checkAdmin();
?>

<div class="container">
    <div class="card">
        <h2>Администрирование</h2>
        
        <div class="admin-menu">
            <a href="/admin/computers" class="admin-card">
                <div class="admin-card-icon">💻</div>
                <h3>Управление компьютерами</h3>
                <p>Добавление и редактирование ПК</p>
            </a>
            
            <a href="/admin/users" class="admin-card">
                <div class="admin-card-icon">👥</div>
                <h3>Управление пользователями</h3>
                <p>Настройка прав доступа</p>
            </a>
            
            <a href="/admin/add-user" class="admin-card">
                <div class="admin-card-icon">➕</div>
                <h3>Добавить пользователя</h3>
                <p>Создание новых учетных записей</p>
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>