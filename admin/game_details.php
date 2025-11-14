<?php
require_once '../config/config.php';
require_once '../core/Auth.php';
require_once '../models/Game.php';
require_once '../models/User.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$game_id = $_GET['id'] ?? null;
if (!$game_id) {
    header('Location: games.php');
    exit;
}

$game_model = new Game();
$user_model = new User();

$game = $game_model->getById($game_id);
$players = $game_model->getGamePlayers($game_id);

if (!$game) {
    header('Location: games.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали игры #<?= $game_id ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>🎮 Детали игры #<?= $game_id ?></h1>
                <div class="admin-actions">
                    <a href="games.php" class="btn-secondary">← Назад</a>
                    <a href="?action=logout" class="btn-logout">🚪 Выйти</a>
                </div>
            </div>

            <div class="content-grid">
                <div class="content-block">
                    <h3>📊 Информация об игре</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Статус:</label>
                            <span class="badge badge-<?= 
                                $game['status'] == 'waiting' ? 'warning' : 
                                ($game['status'] == 'active' ? 'info' : 'success') 
                            ?>">
                                <?= $game['status'] ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Игроков:</label>
                            <span><?= $game['players_count'] ?>/<?= MAX_PLAYERS ?></span>
                        </div>
                        <div class="info-item">
                            <label>Призовой фонд:</label>
                            <span><?= number_format($game['prize_pool'], 2) ?> руб.</span>
                        </div>
                        <div class="info-item">
                            <label>Создана:</label>
                            <span><?= date('d.m.Y H:i', strtotime($game['created_at'])) ?></span>
                        </div>
                        <?php if ($game['finished_at']): ?>
                        <div class="info-item">
                            <label>Завершена:</label>
                            <span><?= date('d.m.Y H:i', strtotime($game['finished_at'])) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($game['winner_numbers']): ?>
                        <div class="info-item">
                            <label>Победители:</label>
                            <span><?= implode(', ', json_decode($game['winner_numbers'], true)) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="content-block">
                    <h3>👥 Участники игры</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Игрок</th>
                                <th>Номер</th>
                                <th>Статус</th>
                                <th>Выигрыш</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($players as $player): ?>
                            <tr>
                                <td>
                                    <?php if($player['username']): ?>
                                        @<?= $player['username'] ?>
                                    <?php else: ?>
                                        ID: <?= $player['tg_id'] ?>
                                    <?php endif; ?>
                                </td>
                                <td>#<?= $player['ticket_number'] ?></td>
                                <td>
                                    <?php if($player['is_winner']): ?>
                                        <span class="badge badge-success">Победитель</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Участник</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($player['is_winner']): ?>
                                        <strong>+<?= number_format($player['prize_amount'], 2) ?> руб.</strong>
                                    <?php else: ?>
                                        -<?= TICKET_PRICE ?> руб.
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($game['status'] == 'waiting'): ?>
            <div class="content-block">
                <h3>⚡ Действия</h3>
                <div class="action-buttons">
                    <button onclick="startGame(<?= $game_id ?>)" class="btn-success">
                        ▶️ Запустить игру
                    </button>
                    <button onclick="addTestPlayer(<?= $game_id ?>)" class="btn-secondary">
                        🤖 Добавить тестового игрока
                    </button>
                    <button onclick="deleteGame(<?= $game_id ?>)" class="btn-danger">
                        🗑️ Удалить игру
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function startGame(gameId) {
        if (confirm('Запустить игру?')) {
            fetch('ajax/admin_ajax.php?action=start_game&game_id=' + gameId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Игра запущена! Победители: ' + data.winners.join(', '));
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.error);
                }
            });
        }
    }

    function addTestPlayer(gameId) {
        fetch('ajax/admin_ajax.php?action=add_test_player&game_id=' + gameId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Тестовый игрок добавлен!');
                location.reload();
            } else {
                alert('Ошибка: ' + data.error);
            }
        });
    }

    function deleteGame(gameId) {
        if (confirm('Удалить игру? Это действие нельзя отменить.')) {
            fetch('ajax/admin_ajax.php?action=delete_game&game_id=' + gameId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Игра удалена!');
                    location.href = 'games.php';
                } else {
                    alert('Ошибка: ' + data.error);
                }
            });
        }
    }
    </script>
</body>
</html>