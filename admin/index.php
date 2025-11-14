<?php
require_once '../config/config.php';
require_once '../core/Auth.php';
require_once '../models/User.php';
require_once '../models/Game.php';
require_once '../models/Payment.php';

session_start();

// Проверка авторизации админа
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$user_model = new User();
$game_model = new Game();
$payment_model = new Payment();

// Статистика для дашборда
$stats = [
    'total_users' => $user_model->getTotalUsers(),
    'total_games' => $game_model->getTotalGames(),
    'total_deposits' => $payment_model->getTotalDeposits(),
    'total_prizes' => $game_model->getTotalPrizes(),
    'today_profit' => $payment_model->getTodayProfit()
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../views/admin/sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>Дашборд</h1>
                <div class="admin-actions">
                    <span>Привет, Админ!</span>
                    <a href="?action=logout" class="btn-logout">Выйти</a>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card admin">
                    <h3>👥 Пользователи</h3>
                    <div class="stat-number"><?= $stats['total_users'] ?></div>
                </div>
                <div class="stat-card admin">
                    <h3>🎮 Игры</h3>
                    <div class="stat-number"><?= $stats['total_games'] ?></div>
                </div>
                <div class="stat-card admin">
                    <h3>💰 Депозиты</h3>
                    <div class="stat-number"><?= $stats['total_deposits'] ?> руб.</div>
                </div>
                <div class="stat-card admin">
                    <h3>🏆 Выплаты</h3>
                    <div class="stat-number"><?= $stats['total_prizes'] ?> руб.</div>
                </div>
                <div class="stat-card admin">
                    <h3>💵 Прибыль сегодня</h3>
                    <div class="stat-number"><?= $stats['today_profit'] ?> руб.</div>
                </div>
            </div>
            
            <div class="admin-content">
                <div class="tabs">
                    <button class="tab-btn active" data-tab="games">Последние игры</button>
                    <button class="tab-btn" data-tab="users">Новые пользователи</button>
                    <button class="tab-btn" data-tab="payments">Платежи</button>
                </div>
                
                <div id="games-tab" class="tab-content active">
                    <?php include '../views/admin/games_list.php'; ?>
                </div>
                
                <div id="users-tab" class="tab-content">
                    <?php include '../views/admin/users_list.php'; ?>
                </div>
                
                <div id="payments-tab" class="tab-content">
                    <?php include '../views/admin/payments_list.php'; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../public/assets/js/admin.js"></script>
</body>
</html>