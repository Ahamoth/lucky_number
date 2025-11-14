<?php
require_once '../../config/config.php';
require_once '../../controllers/AdminController.php';

$admin_controller = new AdminController();
$games = $admin_controller->getGamesList(1, 20);
?>
<div class="content-block">
    <div class="table-header">
        <h3>🎮 Управление играми</h3>
        <button class="btn-primary" onclick="createGame()">+ Создать игру</button>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Статус</th>
                <th>Игроков</th>
                <th>Призовой фонд</th>
                <th>Создана</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($games as $game): ?>
            <tr>
                <td>#<?= $game['id'] ?></td>
                <td>
                    <span class="badge badge-<?= 
                        $game['status'] == 'waiting' ? 'warning' : 
                        ($game['status'] == 'active' ? 'info' : 'success') 
                    ?>">
                        <?= $game['status'] ?>
                    </span>
                </td>
                <td><?= $game['players_count'] ?></td>
                <td><?= number_format($game['prize_pool'], 2) ?> руб.</td>
                <td><?= date('d.m.Y H:i', strtotime($game['created_at'])) ?></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-small" onclick="viewGame(<?= $game['id'] ?>)">👁️</button>
                        <?php if($game['status'] == 'waiting'): ?>
                            <button class="btn-small btn-success" onclick="startGame(<?= $game['id'] ?>)">▶️</button>
                        <?php endif; ?>
                        <button class="btn-small btn-danger" onclick="deleteGame(<?= $game['id'] ?>)">🗑️</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="table-footer">
        <div class="pagination">
            <button class="btn-secondary">← Назад</button>
            <span>Страница 1</span>
            <button class="btn-secondary">Вперед →</button>
        </div>
    </div>
</div>

<script>
function createGame() {
    if (confirm('Создать новую игру?')) {
        fetch('ajax/admin_ajax.php?action=create_game', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Игра создана!');
                location.reload();
            } else {
                alert('Ошибка: ' + data.error);
            }
        });
    }
}

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

function viewGame(gameId) {
    window.open('game_details.php?id=' + gameId, '_blank');
}

function deleteGame(gameId) {
    if (confirm('Удалить игру? Это действие нельзя отменить.')) {
        fetch('ajax/admin_ajax.php?action=delete_game&game_id=' + gameId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Игра удалена!');
                location.reload();
            } else {
                alert('Ошибка: ' + data.error);
            }
        });
    }
}
</script>