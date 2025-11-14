<?php
require_once '../config/config.php';
require_once '../core/Auth.php';
require_once '../controllers/AdminController.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$admin_controller = new AdminController();
$users = $admin_controller->getUsersList(1, 50);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>👥 Управление пользователями</h1>
                <div class="admin-actions">
                    <span class="admin-welcome">Всего пользователей: <?= count($users) ?></span>
                    <a href="?action=export_users" class="btn-secondary">📊 Экспорт</a>
                    <a href="?action=logout" class="btn-logout">🚪 Выйти</a>
                </div>
            </div>

            <?php include '../views/admin/users_list.php'; ?>
        </div>
    </div>
</body>
</html>