<?php
// Файл установки
require_once 'config/config.php';

// Проверяем, не установлена ли уже система
if (file_exists('config/installed.lock')) {
    die('Система уже установлена. Для переустановки удалите файл config/installed.lock');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? 'lucky_number';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    
    $bot_token = $_POST['bot_token'] ?? '';
    $bot_username = $_POST['bot_username'] ?? '';
    
    $admin_username = $_POST['admin_username'] ?? 'admin';
    $admin_password = $_POST['admin_password'] ?? '';
    
    // Проверяем подключение к БД
    try {
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Создаем базу данных если не существует
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");
        
        // Выполняем SQL скрипт
        $sql_script = file_get_contents('database_schema.sql');
        $pdo->exec($sql_script);
        
        // Обновляем конфиг файл
        $config_content = file_get_contents('config/config.php');
        $config_content = str_replace([
            "define('DB_HOST', 'localhost');",
            "define('DB_NAME', 'lucky_number');", 
            "define('DB_USER', 'username');",
            "define('DB_PASS', 'password');",
            "define('BOT_TOKEN', 'YOUR_BOT_TOKEN');",
            "define('BOT_USERNAME', 'YOUR_BOT_USERNAME');",
            "define('ADMIN_PASSWORD_HASH', password_hash('admin123', PASSWORD_DEFAULT));"
        ], [
            "define('DB_HOST', '$db_host');",
            "define('DB_NAME', '$db_name');",
            "define('DB_USER', '$db_user');", 
            "define('DB_PASS', '$db_pass');",
            "define('BOT_TOKEN', '$bot_token');",
            "define('BOT_USERNAME', '$bot_username');",
            "define('ADMIN_PASSWORD_HASH', password_hash('$admin_password', PASSWORD_DEFAULT));"
        ], $config_content);
        
        file_put_contents('config/config.php', $config_content);
        
        // Создаем файл блокировки
        file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
        
        $success = true;
        
    } catch (PDOException $e) {
        $errors[] = "Ошибка базы данных: " . $e->getMessage();
    } catch (Exception $e) {
        $errors[] = "Ошибка: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="public/assets/css/style.css">
    <style>
        .install-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .install-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .install-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .install-header h1 {
            color: #2D3748;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4A5568;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #FED7D7;
            color: #C53030;
            border: 1px solid #FEB2B2;
        }
        
        .alert-success {
            background: #C6F6D5;
            color: #276749;
            border: 1px solid #9AE6B4;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-box">
            <div class="install-header">
                <h1>🎰 Установка <?= SITE_NAME ?></h1>
                <p>Заполните форму для настройки системы</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <?php foreach($errors as $error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ Установка завершена успешно!<br><br>
                    <strong>Следующие шаги:</strong><br>
                    1. Удалите файл install.php для безопасности<br>
                    2. Настройте вебхук для Telegram бота<br>
                    3. Проверьте работу системы<br><br>
                    
                    <a href="admin/login.php" class="btn-primary">Перейти в админку</a>
                    <a href="public/index.php" class="btn-secondary">На сайт</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <h3>📊 Настройки базы данных</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Хост БД</label>
                            <input type="text" name="db_host" class="form-control" value="localhost" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Имя базы данных</label>
                            <input type="text" name="db_name" class="form-control" value="lucky_number" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Пользователь БД</label>
                            <input type="text" name="db_user" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Пароль БД</label>
                            <input type="password" name="db_pass" class="form-control">
                        </div>
                    </div>
                    
                    <h3>🤖 Настройки Telegram бота</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Токен бота</label>
                        <input type="text" name="bot_token" class="form-control" required>
                        <small>Получите у @BotFather</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Username бота</label>
                        <input type="text" name="bot_username" class="form-control" required>
                        <small>Без @, например: MyLuckyNumberBot</small>
                    </div>
                    
                    <h3>🔐 Настройки админки</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Логин администратора</label>
                            <input type="text" name="admin_username" class="form-control" value="admin" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Пароль администратора</label>
                            <input type="password" name="admin_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">
                            🚀 Начать установку
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>