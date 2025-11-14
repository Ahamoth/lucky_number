<?php
require_once '../config/config.php';
require_once '../core/Auth.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Обработка сохранения настроек
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = $_POST['settings'] ?? [];
    
    // Здесь будет логика сохранения настроек в БД или файл
    file_put_contents('../logs/settings.log', 
        date('Y-m-d H:i:s') . " - Settings updated: " . json_encode($settings) . "\n", 
        FILE_APPEND
    );
    
    $success_message = 'Настройки успешно сохранены!';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>⚙️ Настройки системы</h1>
                <div class="admin-actions">
                    <a href="?action=logout" class="btn-logout">🚪 Выйти</a>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <?= $success_message ?>
            </div>
            <?php endif; ?>

            <div class="content-block">
                <form method="POST" class="admin-form">
                    <h3>🎮 Настройки игры</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Цена билета (руб.)</label>
                        <input type="number" name="settings[ticket_price]" class="form-control" 
                               value="<?= TICKET_PRICE ?>" min="1" max="1000" step="1">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Минимальное количество игроков</label>
                        <input type="number" name="settings[min_players]" class="form-control" 
                               value="<?= MIN_PLAYERS ?>" min="2" max="10">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Максимальное количество игроков</label>
                        <input type="number" name="settings[max_players]" class="form-control" 
                               value="<?= MAX_PLAYERS ?>" min="3" max="20">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Количество победителей</label>
                        <input type="number" name="settings[winners_count]" class="form-control" 
                               value="<?= WINNERS_COUNT ?>" min="1" max="5">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Процент призового фонда</label>
                        <input type="number" name="settings[prize_fund_percent]" class="form-control" 
                               value="<?= PRIZE_FUND_PERCENT ?>" min="50" max="95" step="1">
                        <small>Остальные <?= 100 - PRIZE_FUND_PERCENT ?>% - комиссия системы</small>
                    </div>

                    <h3>💰 Настройки платежей</h3>
                    
                    <div class="form-group">
                        <label class="form-label">TON Wallet Address</label>
                        <input type="text" name="settings[ton_wallet]" class="form-control" 
                               value="<?= TON_WALLET ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Минимальный депозит (руб.)</label>
                        <input type="number" name="settings[min_deposit]" class="form-control" 
                               value="10" min="1" max="100">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Максимальный депозит (руб.)</label>
                        <input type="number" name="settings[max_deposit]" class="form-control" 
                               value="10000" min="100" max="100000">
                    </div>

                    <h3>🔧 Системные настройки</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Телеграм бот токен</label>
                        <input type="password" name="settings[bot_token]" class="form-control" 
                               value="********">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">URL вебхука</label>
                        <input type="text" name="settings[webhook_url]" class="form-control" 
                               value="<?= WEBHOOK_URL ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Домен сайта</label>
                        <input type="text" name="settings[site_url]" class="form-control" 
                               value="<?= SITE_URL ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">💾 Сохранить настройки</button>
                        <button type="button" class="btn-secondary" onclick="resetSettings()">🔄 Сбросить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function resetSettings() {
        if (confirm('Сбросить все настройки к значениям по умолчанию?')) {
            document.querySelector('form').reset();
        }
    }
    </script>
</body>
</html>