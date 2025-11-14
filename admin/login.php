<?php
require_once '../config/config.php';
require_once '../core/Auth.php';

session_start();

$auth = new Auth();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->adminLogin($username, $password)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверные учетные данные';
    }
}

// Если уже авторизован, перенаправляем
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админку - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>🔐 Админ панель</h1>
                <p><?= SITE_NAME ?></p>
            </div>
            
            <?php if ($error): ?>
            <div style="background: #FED7D7; color: #C53030; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="admin-form">
                <div class="form-group">
                    <label class="form-label">Имя пользователя</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%;">Войти</button>
            </form>
        </div>
    </div>
</body>
</html>