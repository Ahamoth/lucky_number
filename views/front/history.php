<?php
require_once '../../config/config.php';
require_once '../../core/Auth.php';
require_once '../../models/User.php';
require_once '../../models/Game.php';

session_start();
$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user = $auth->getUser();
$user_model = new User();
$game_model = new Game();

$game_history = $user_model->getGameHistory($user['id'], 50);
?>
<?php include '../templates/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>📊 История игр</h1>
        <p>Ваши последние 50 игр</p>
    </div>

    <div class="history-filters">
        <button class="filter-btn active" data-filter="all">Все игры</button>
        <button class="filter-btn" data-filter="win">Победы</button>
        <button class="filter-btn" data-filter="lose">Поражения</button>
    </div>

    <div class="games-history">
        <?php if (empty($game_history)): ?>
            <div class="empty-state">
                <div class="empty-icon">🎮</div>
                <h3>Игр пока нет</h3>
                <p>Сыграйте свою первую игру!</p>
                <a href="index.php" class="btn-primary">Начать играть</a>
            </div>
        <?php else: ?>
            <?php foreach($game_history as $game): ?>
            <div class="history-item <?= $game['is_winner'] ? 'winner' : 'loser' ?>" data-result="<?= $game['is_winner'] ? 'win' : 'lose' ?>">
                <div class="game-result-icon">
                    <?= $game['is_winner'] ? '🏆' : '💫' ?>
                </div>
                <div class="game-info">
                    <div class="game-date">
                        <?= date('d.m.Y в H:i', strtotime($game['created_at'])) ?>
                    </div>
                    <div class="game-details">
                        Номер билета: <strong>#<?= $game['ticket_number'] ?></strong>
                    </div>
                </div>
                <div class="game-outcome">
                    <?php if ($game['is_winner']): ?>
                        <div class="prize-amount">+<?= $game['prize_amount'] ?> руб.</div>
                        <div class="outcome-label">Победа! 🎉</div>
                    <?php else: ?>
                        <div class="prize-amount">-<?= TICKET_PRICE ?> руб.</div>
                        <div class="outcome-label">Участие</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Фильтрация истории
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Активная кнопка
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        const filter = btn.getAttribute('data-filter');
        const items = document.querySelectorAll('.history-item');
        
        items.forEach(item => {
            if (filter === 'all' || item.getAttribute('data-result') === filter) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>

<?php include '../templates/footer.php'; ?>