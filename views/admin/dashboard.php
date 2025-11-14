<?php
require_once '../../config/config.php';
require_once '../../core/Auth.php';
require_once '../../controllers/AdminController.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$admin_controller = new AdminController();
$stats = $admin_controller->getDashboardStats();
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
        <?php include 'sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>📊 Дашборд</h1>
                <div class="admin-actions">
                    <span class="admin-welcome">Привет, Админ!</span>
                    <div class="header-stats">
                        <span class="online-count">👥 Онлайн: <strong id="onlineUsers">0</strong></span>
                        <span class="server-time">🕐 <?= date('H:i') ?></span>
                    </div>
                    <a href="?action=logout" class="btn-logout">🚪 Выйти</a>
                </div>
            </div>
            
            <!-- Статистика -->
            <div class="stats-grid admin">
                <div class="stat-card admin">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <div class="stat-number"><?= $stats['total_users'] ?></div>
                        <div class="stat-label">Всего пользователей</div>
                    </div>
                </div>
                <div class="stat-card admin">
                    <div class="stat-icon">🎮</div>
                    <div class="stat-info">
                        <div class="stat-number"><?= $stats['total_games'] ?></div>
                        <div class="stat-label">Всего игр</div>
                    </div>
                </div>
                <div class="stat-card admin">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <div class="stat-number"><?= number_format($stats['total_deposits'], 0, ',', ' ') ?> руб.</div>
                        <div class="stat-label">Общие депозиты</div>
                    </div>
                </div>
                <div class="stat-card admin">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-info">
                        <div class="stat-number"><?= number_format($stats['total_prizes'], 0, ',', ' ') ?> руб.</div>
                        <div class="stat-label">Выплачено призов</div>
                    </div>
                </div>
                <div class="stat-card admin">
                    <div class="stat-icon">💵</div>
                    <div class="stat-info">
                        <div class="stat-number"><?= number_format($stats['today_profit'], 0, ',', ' ') ?> руб.</div>
                        <div class="stat-label">Прибыль сегодня</div>
                    </div>
                </div>
                <div class="stat-card admin">
                    <div class="stat-icon">⚡</div>
                    <div class="stat-info">
                        <div class="stat-number"><?= $stats['active_games'] ?></div>
                        <div class="stat-label">Активных игр</div>
                    </div>
                </div>
            </div>
            
            <!-- Быстрые действия -->
            <div class="quick-actions">
                <h2>⚡ Быстрые действия</h2>
                <div class="action-buttons">
                    <button class="btn-action" onclick="createGame()">
                        🎮 Создать игру
                    </button>
                    <button class="btn-action" onclick="showUserSearch()">
                        👤 Найти пользователя
                    </button>
                    <button class="btn-action" onclick="showStats()">
                        📈 Подробная статистика
                    </button>
                    <button class="btn-action" onclick="exportData()">
                        📊 Экспорт данных
                    </button>
                </div>
            </div>
            
            <!-- Графики и таблицы -->
            <div class="admin-content-grid">
                <div class="content-block">
                    <h3>📈 Активность за последние 7 дней</h3>
                    <div id="activityChart" class="chart-container">
                        <canvas id="activityCanvas"></canvas>
                    </div>
                </div>
                
                <div class="content-block">
                    <h3>🎮 Последние игры</h3>
                    <div class="recent-games">
                        <?php
                        $recent_games = $admin_controller->getGamesList(1, 5);
                        foreach($recent_games as $game): 
                        ?>
                        <div class="recent-game">
                            <div class="game-id">#<?= $game['id'] ?></div>
                            <div class="game-info">
                                <div class="game-players">👥 <?= $game['players_count'] ?> игроков</div>
                                <div class="game-prize">💰 <?= $game['prize_pool'] ?> руб.</div>
                            </div>
                            <div class="game-status <?= $game['status'] ?>">
                                <?= $game['status'] ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../public/assets/js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Инициализация графика
    const ctx = document.getElementById('activityCanvas').getContext('2d');
    const activityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
            datasets: [{
                label: 'Игры',
                data: [12, 19, 8, 15, 12, 25, 18],
                borderColor: '#4CAF50',
                tension: 0.1
            }, {
                label: 'Депозиты',
                data: [8, 12, 6, 10, 15, 20, 14],
                borderColor: '#2196F3',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
    
    // Обновление онлайн пользователей
    function updateOnlineUsers() {
        fetch('ajax/admin_ajax.php?action=online_users')
            .then(response => response.json())
            .then(data => {
                document.getElementById('onlineUsers').textContent = data.count;
            });
    }
    
    setInterval(updateOnlineUsers, 30000);
    updateOnlineUsers();
    </script>
</body>
</html>