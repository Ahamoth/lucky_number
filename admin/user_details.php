<?php
require_once '../config/config.php';
require_once '../core/Auth.php';
require_once '../models/User.php';
require_once '../models/Game.php';
require_once '../models/Transaction.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header('Location: users.php');
    exit;
}

$user_model = new User();
$game_model = new Game();
$transaction_model = new Transaction();

$user = $user_model->getById($user_id);
$user_stats = $user_model->getStats($user_id);
$game_history = $user_model->getGameHistory($user_id, 20);
$transactions = $transaction_model->getUserTransactions($user_id, 20);

if (!$user) {
    header('Location: users.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>👤 Профиль пользователя</h1>
                <div class="admin-actions">
                    <a href="users.php" class="btn-secondary">← Назад</a>
                    <a href="?action=logout" class="btn-logout">🚪 Выйти</a>
                </div>
            </div>

            <div class="content-grid">
                <div class="content-block">
                    <h3>📊 Основная информация</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>ID:</label>
                            <span>#<?= $user['id'] ?></span>
                        </div>
                        <div class="info-item">
                            <label>Telegram ID:</label>
                            <span><?= $user['tg_id'] ?></span>
                        </div>
                        <div class="info-item">
                            <label>Username:</label>
                            <span><?= $user['username'] ? '@' . $user['username'] : 'не указан' ?></span>
                        </div>
                        <div class="info-item">
                            <label>Имя:</label>
                            <span><?= htmlspecialchars($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Баланс:</label>
                            <span><strong><?= number_format($user['balance'], 2) ?> руб.</strong></span>
                        </div>
                        <div class="info-item">
                            <label>Регистрация:</label>
                            <span><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></span>
                        </div>
                        <div class="info-item">
                            <label>Язык:</label>
                            <span><?= strtoupper($user['language_code'] ?? 'ru') ?></span>
                        </div>
                    </div>
                </div>

                <div class="content-block">
                    <h3>📈 Статистика</h3>
                    <div class="stats-grid compact">
                        <div class="stat-card admin">
                            <div class="stat-number"><?= $user_stats['total_games'] ?? 0 ?></div>
                            <div class="stat-label">Всего игр</div>
                        </div>
                        <div class="stat-card admin">
                            <div class="stat-number"><?= $user_stats['wins'] ?? 0 ?></div>
                            <div class="stat-label">Побед</div>
                        </div>
                        <div class="stat-card admin">
                            <div class="stat-number"><?= round(($user_stats['wins'] / max($user_stats['total_games'], 1)) * 100, 1) ?>%</div>
                            <div class="stat-label">Процент побед</div>
                        </div>
                        <div class="stat-card admin">
                            <div class="stat-number"><?= number_format($user_stats['total_winnings'] ?? 0, 2) ?> руб.</div>
                            <div class="stat-label">Выиграно всего</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-grid">
                <div class="content-block">
                    <h3>🎮 История игр</h3>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID игры</th>
                                    <th>Дата</th>
                                    <th>Номер</th>
                                    <th>Результат</th>
                                    <th>Выигрыш</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($game_history as $game): ?>
                                <tr>
                                    <td>#<?= $game['id'] ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($game['created_at'])) ?></td>
                                    <td>#<?= $game['ticket_number'] ?></td>
                                    <td>
                                        <?php if($game['is_winner']): ?>
                                            <span class="badge badge-success">Победа</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Участие</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($game['is_winner']): ?>
                                            <strong>+<?= number_format($game['prize_amount'], 2) ?> руб.</strong>
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

                <div class="content-block">
                    <h3>💰 История транзакций</h3>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Тип</th>
                                    <th>Сумма</th>
                                    <th>Описание</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($transactions as $transaction): ?>
                                <tr>
                                    <td><?= date('d.m.Y H:i', strtotime($transaction['created_at'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?= 
                                            $transaction['type'] == 'deposit' ? 'success' : 
                                            ($transaction['type'] == 'game_win' ? 'info' : 'warning')
                                        ?>">
                                            <?= $transaction['type'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if(in_array($transaction['type'], ['deposit', 'game_win'])): ?>
                                            <span style="color: #38A169;">+<?= number_format($transaction['amount'], 2) ?> руб.</span>
                                        <?php else: ?>
                                            <span style="color: #E53E3E;">-<?= number_format($transaction['amount'], 2) ?> руб.</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $transaction['description'] ?? '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="content-block">
                <h3>⚡ Быстрые действия</h3>
                <div class="action-buttons">
                    <button onclick="editBalance(<?= $user['id'] ?>, '<?= $user['first_name'] ?>')" 
                            class="btn-warning">💰 Изменить баланс</button>
                    <button onclick="sendMessage(<?= $user['id'] ?>)" 
                            class="btn-info">✉️ Отправить сообщение</button>
                    <button onclick="loginAsUser(<?= $user['id'] ?>)" 
                            class="btn-secondary">🔐 Войти как пользователь</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function editBalance(userId, userName) {
        const amount = prompt('Введите сумму для изменения баланса (+ для пополнения, - для списания):');
        if (amount !== null) {
            const reason = prompt('Укажите причину изменения:');
            if (reason !== null) {
                fetch('ajax/admin_ajax.php?action=update_balance', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        user_id: userId,
                        operation: amount >= 0 ? 'add' : 'subtract',
                        amount: Math.abs(parseFloat(amount)),
                        reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Баланс обновлен! Новый баланс: ' + data.new_balance + ' руб.');
                        location.reload();
                    } else {
                        alert('Ошибка: ' + data.error);
                    }
                });
            }
        }
    }

    function sendMessage(userId) {
        const message = prompt('Введите сообщение для пользователя:');
        if (message) {
            fetch('ajax/admin_ajax.php?action=send_message', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    user_id: userId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Сообщение отправлено!');
                } else {
                    alert('Ошибка: ' + data.error);
                }
            });
        }
    }

    function loginAsUser(userId) {
        if (confirm('Войти в систему как этот пользователь?')) {
            // Здесь будет реализация входа от имени пользователя
            alert('Функция в разработке');
        }
    }
    </script>
</body>
</html>