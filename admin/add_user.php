<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/db.php';
checkAuth();
checkAdmin();

// Обработка добавления пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $login = trim($_POST['login']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    try {
        $stmt = $db->prepare("INSERT INTO users (login, password, is_admin) VALUES (?, ?, ?)");
        $stmt->execute([$login, $password, $is_admin]);
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Пользователь успешно добавлен!'];
    } catch (PDOException $e) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Ошибка: ' . htmlspecialchars($e->getMessage())];
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Обработка удаления пользователя
// Обработка удаления пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    try {
        // Начинаем транзакцию
        $db->beginTransaction();
        
        // Сначала удаляем связанные записи (если есть)
        $db->prepare("DELETE FROM user_pc_access WHERE user_id = ?")->execute([$user_id]);
        
        // Затем удаляем самого пользователя
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Проверяем, был ли удален пользователь
        if ($stmt->rowCount() > 0) {
            $db->commit();
            $_SESSION['success_message'] = 'Пользователь успешно удален!';
        } else {
            $db->rollBack();
            $_SESSION['error_message'] = 'Пользователь не найден или не был удален';
        }
        
        // Перенаправляем, чтобы избежать повторной отправки формы
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
        
    } catch (PDOException $e) {
        $db->rollBack();
        $_SESSION['error_message'] = 'Ошибка при удалении: ' . htmlspecialchars($e->getMessage());
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Вывод сообщений об ошибках/успехе
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['error_message'].'</div>';
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">'.$_SESSION['success_message'].'</div>';
    unset($_SESSION['success_message']);
}

// Обработка изменения пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = password_hash(trim($_POST['new_password']), PASSWORD_BCRYPT);
    
    try {
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $user_id]);
        echo '<div class="alert alert-success">Пароль успешно изменен!</div>';
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Ошибка: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Получаем список пользователей
$users = $db->query("SELECT id, login, is_admin FROM users ORDER BY login")->fetchAll();
?>

<div class="container">
    <div class="card">
        <h2>Добавить пользователя</h2>
        
        <form method="POST">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group form-check">
                <input type="checkbox" name="is_admin" id="is_admin" class="form-check-input">
                <label for="is_admin" class="form-check-label">Администратор</label>
            </div>
            
            <button type="submit" class="btn btn-primary">Создать пользователя</button>
            <a href="/admin" class="btn btn-secondary">Назад</a>
        </form>

        <!-- Таблица существующих пользователей -->
        <div class="mt-5">
            <h3>Список пользователей</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Логин</th>
                            <th>Роль</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['login']) ?></td>
                            <td><?= $user['is_admin'] ? 'Администратор' : 'Пользователь' ?></td>
                            <td>
                                <!-- Форма для смены пароля -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <div class="input-group mb-2">
                                        <input type="password" name="new_password" class="form-control form-control-sm" placeholder="Новый пароль" required>
                                        <div class="input-group-append">
                                            <button type="submit" name="change_password" class="btn btn-sm btn-warning">
                                                Сменить пароль
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <!-- Форма для удаления -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                                        Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>