<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    session_unset();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    $stmt = $db->prepare("SELECT * FROM users WHERE login = ? LIMIT 1");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['is_admin'] = $user['is_admin'];
        header("Location: /wol/panel");
        exit();
    } else {
        $error = "Неверный логин или пароль!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Авторизация</title>
    <link rel="stylesheet" href="includes/styles.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 50px;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 20px;">Авторизация</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <input type="text" name="login" class="form-control" placeholder="Логин" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Пароль" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Войти</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php require_once 'includes/footer.php'; ?>