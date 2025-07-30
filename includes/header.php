<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?? 'WoL Админ-панель' ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/wol/includes/styles.css">
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <h1>Админ-панель</h1>
            <div class="nav-links">
                <a href="/wol/panel">Главная</a>
                <a href="/wol/admin/">Админка</a>
                <a href="/wol/logout">Выйти</a>
            </div>
        </div>
    </header>
    <main class="container">