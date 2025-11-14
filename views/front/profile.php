<?php
require_once '../../config/config.php';
require_once '../../core/Auth.php';
require_once '../../models/User.php';
require_once '../../models/Transaction.php';

session_start();
$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user = $auth->getUser();
$user_model = new User();
$transaction_model = new Transaction();

$user_stats = $user_model->getStats($user['id']);
$transactions = $transaction_model->getUserTransactions($user['id']);
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <div class="profile-header">
        <div class="profile-avatar">
            <div class="avatar-placeholder">
                <?= substr($user['first_name'], 0, 1) ?>
            </div>
        </div>
        <div class="profile-info">
            <h1><?= htmlspecialchars($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></h1>
            <p class="profile-username">@<?= htmlspecialchars($user['username'] ?? 'anonymous') ?></p>
            <p class="profile-id">ID: <?= $user['tg_id'] ?></p>
        </div>
    </div>

    <div class="stats-grid compact">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <div class="stat-value"><?= $user['balance'] ?> руб.</div>
                <div class="stat-label">Текущий баланс</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎮</div>
            <div class="stat-info">
                <div class="stat-value"><?= $user_stats['total_games'] ?? 0 ?></div>
                <div class="stat-label">Всего игр</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div class="stat-info">
                <div class="stat-value"><?= $user_stats['wins'] ?? 0 ?></div>
                <div class="stat-label">Побед</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-info">
                <div class="stat-value"><?= round(($user_stats['wins'] / max($user_stats['total_games'], 1)) * 100, 1) ?>%</div>
                <div class="stat-label">Процент побед</div>
            </div>
        </div>
    </div>

    <div class="profile-section">
        <h2>💸 История транзакций</h2>
        <div class="transactions-list">
            <?php if (empty($transactions)): ?>
                <div class="empty-state">
                    <p>Транзакций пока нет</p>
                </div>
            <?php else: ?>
                <?php foreach($transactions as $transaction): ?>
                <div class="transaction-item <?= $transaction['type'] ?>">
                    <div class="transaction-icon">
                        <?php switch($transaction['type']):
                            case 'deposit': ?>💳<?php break;
                            case 'game_win': ?>🏆<?php break;
                            case 'game_bet': ?>🎫<?php break;
                            default: ?>💰<?php endswitch; ?>
                    </div>
                    <div class="transaction-details">
                        <div class="transaction-type">
                            <?php switch($transaction['type']):
                                case 'deposit': ?>Пополнение<?php break;
                                case 'game_win': ?>Выигрыш<?php break;
                                case 'game_bet': ?>Ставка в игре<?php break;
                                default: echo $transaction['type']; endswitch; ?>
                        </div>
                        <div class="transaction-date">
                            <?= date('d.m.Y H:i', strtotime($transaction['created_at'])) ?>
                        </div>
                    </div>
                    <div class="transaction-amount <?= $transaction['type'] === 'game_bet' ? 'negative' : 'positive' ?>">
                        <?= $transaction['type'] === 'game_bet' ? '-' : '+' ?><?= $transaction['amount'] ?> руб.
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="profile-section">
        <h2>⚙️ Настройки</h2>
        <div class="settings-list">
            <div class="setting-item">
                <span>Уведомления</span>
                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="setting-item">
                <span>Звуковые эффекты</span>
                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
            </div>
            <button class="btn-secondary" onclick="clearHistory()">Очистить историю</button>
        </div>
    </div>
</div>

<?php include '../templates/modal.php'; ?>
<?php include '../templates/footer.php'; ?>