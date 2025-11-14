<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? SITE_NAME ?></title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <h1>🎰 <?= SITE_NAME ?></h1>
            </div>
            <nav class="main-nav">
                <a href="index.php" class="nav-link">🎮 Играть</a>
                <a href="profile.php" class="nav-link">👤 Профиль</a>
                <a href="history.php" class="nav-link">📊 История</a>
            </nav>
            <div class="user-balance">
                💰 <span id="headerBalance"><?= $_SESSION['user_data']['balance'] ?? 0 ?></span> руб.
            </div>
        </div>
    </header>
    <main class="main-content">