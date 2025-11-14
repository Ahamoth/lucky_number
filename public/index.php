<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Game.php';

// Инициализируем классы
$auth = new Auth();
$user_model = new User();
$game_model = new Game();

// Обработка данных Telegram Web App
if (isset($_GET['tg_init_data'])) {
    $user = processTelegramInitData($_GET['tg_init_data']);
} elseif (isset($_POST['initData'])) {
    $user = processTelegramInitData($_POST['initData']);
} else {
    $user = $auth->getCurrentUser();
}

// Если пользователь не найден, показываем страницу авторизации
if (!$user) {
    showAuthPage();
    exit;
}

// Получаем активную игру и статистику
$active_game = $game_model->getActiveGame();
$user_stats = $user_model->getStats($user['id']);
$game_history = $user_model->getGameHistory($user['id']);

// Показываем главную страницу
showMainPage($user, $active_game, $user_stats);

function processTelegramInitData($initData) {
    // Парсим данные из Telegram Web App
    parse_str($initData, $data);
    
    if (isset($data['user'])) {
        $tg_user = json_decode(urldecode($data['user']), true);
        
        if ($tg_user && isset($tg_user['id'])) {
            $user_model = new User();
            $user = $user_model->getByTgId($tg_user['id']);
            
            if (!$user) {
                $user = $user_model->create($tg_user);
            }
            
            // Сохраняем в сессию
            session_start();
            $_SESSION['user_id'] = $user['tg_id'];
            $_SESSION['user_data'] = $user;
            
            return $user;
        }
    }
    
    return null;
}

function showAuthPage() {
    echo '
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>🎰 Счастливый Номер</title>
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                text-align: center; 
                padding: 50px; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container { 
                max-width: 400px; 
                margin: 0 auto; 
            }
            .btn { 
                background: white; 
                color: #667eea; 
                padding: 15px 30px; 
                text-decoration: none; 
                border-radius: 10px; 
                display: inline-block; 
                margin: 20px 0; 
                font-size: 18px;
                font-weight: bold;
                border: none;
                cursor: pointer;
            }
            .telegram-btn {
                background: #0088cc;
                color: white;
                padding: 15px 30px;
                border-radius: 10px;
                text-decoration: none;
                display: inline-block;
                margin: 10px 0;
                font-size: 16px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🎰 Счастливый Номер</h1>
            <p>Для игры необходимо авторизоваться через Telegram</p>
            
            <div id="auth-buttons">
                <a href="https://t.me/' . BOT_USERNAME . '" class="telegram-btn" target="_blank">
                    📲 Открыть в Telegram
                </a>
                <p style="margin: 20px 0;">или</p>
                <button class="btn" onclick="authorizeInWebApp()">
                    🔐 Войти в веб-приложении
                </button>
            </div>
            
            <div id="webapp-auth" style="display: none;">
                <p>Нажмите кнопку ниже для авторизации:</p>
                <button class="btn" onclick="sendAuthData()">
                    ✅ Подтвердить авторизацию
                </button>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px;">
                Используйте бота: @' . BOT_USERNAME . '
            </p>
        </div>
        
        <script>
            // Проверяем, открыто ли в Telegram Web App
            if (typeof Telegram !== "undefined") {
                Telegram.WebApp.ready();
                Telegram.WebApp.expand();
                
                // Если есть данные авторизации, показываем веб-приложение версию
                if (Telegram.WebApp.initData) {
                    document.getElementById("auth-buttons").style.display = "none";
                    document.getElementById("webapp-auth").style.display = "block";
                }
            }
            
            function authorizeInWebApp() {
                if (typeof Telegram !== "undefined" && Telegram.WebApp.initData) {
                    document.getElementById("auth-buttons").style.display = "none";
                    document.getElementById("webapp-auth").style.display = "block";
                } else {
                    alert("Откройте приложение через Telegram бота для авторизации");
                }
            }
            
            function sendAuthData() {
                if (typeof Telegram !== "undefined" && Telegram.WebApp.initData) {
                    const initData = Telegram.WebApp.initData;
                    
                    // Создаем форму для отправки данных
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = window.location.href;
                    
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "initData";
                    input.value = initData;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                } else {
                    alert("Данные авторизации не найдены");
                }
            }
            
            // Автоматическая авторизация при загрузке в Web App
            if (typeof Telegram !== "undefined" && Telegram.WebApp.initData) {
                // Пробуем авторизоваться автоматически
                const initData = Telegram.WebApp.initData;
                window.location.href = "?tg_init_data=" + encodeURIComponent(initData);
            }
        </script>
    </body>
    </html>';
}

function showMainPage($user, $active_game, $user_stats) {
    $total_games = $user_stats['total_games'] ?? 0;
    $wins = $user_stats['wins'] ?? 0;
    $total_winnings = $user_stats['total_winnings'] ?? 0;
    $win_rate = $total_games > 0 ? round(($wins / $total_games) * 100, 1) : 0;
    
    echo '
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>🎰 Счастливый Номер</title>
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body { 
                font-family: "Arial", sans-serif; 
                margin: 0; 
                padding: 20px; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                color: #333;
            }
            
            .container { 
                max-width: 400px; 
                margin: 0 auto; 
            }
            
            .card { 
                background: white; 
                padding: 20px; 
                border-radius: 20px; 
                margin: 10px 0; 
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                text-align: center;
            }
            
            .balance { 
                font-size: 32px; 
                color: #27ae60; 
                font-weight: bold;
                margin: 10px 0;
            }
            
            .btn { 
                background: linear-gradient(45deg, #0088cc, #00c6ff);
                color: white; 
                padding: 18px; 
                text-align: center; 
                border-radius: 15px; 
                display: block; 
                text-decoration: none; 
                margin: 15px 0;
                font-size: 18px;
                font-weight: bold;
                border: none;
                cursor: pointer;
                transition: transform 0.2s;
                width: 100%;
            }
            
            .btn:hover {
                transform: translateY(-2px);
            }
            
            .btn-game {
                background: linear-gradient(45deg, #ff6b6b, #ffa726);
                font-size: 20px;
                padding: 20px;
            }
            
            .btn-success {
                background: linear-gradient(45deg, #66bb6a, #4caf50);
            }
            
            .user-info { 
                background: linear-gradient(45deg, #667eea, #764ba2);
                color: white;
            }
            
            .user-info h2 {
                margin: 0;
                color: white;
            }
            
            .game-stats {
                display: flex;
                justify-content: space-around;
                margin: 15px 0;
            }
            
            .stat-item {
                text-align: center;
            }
            
            .stat-value {
                font-size: 24px;
                font-weight: bold;
                color: #0088cc;
            }
            
            .game-info {
                background: linear-gradient(45deg, #ffd700, #ffa726);
                color: #333;
                font-weight: bold;
            }
            
            .prize-fund {
                font-size: 24px;
                color: #e74c3c;
                font-weight: bold;
                margin: 10px 0;
            }
            
            .players-count {
                font-size: 18px;
                margin: 10px 0;
            }
            
            .winner-badge {
                background: #ffd700;
                color: #333;
                padding: 5px 10px;
                border-radius: 10px;
                font-weight: bold;
                margin: 5px;
                display: inline-block;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card user-info">
                <h2>👋 Привет, ' . htmlspecialchars($user['first_name']) . '!</h2>
                <div class="balance">💎 ' . number_format($user['balance'], 2) . ' руб.</div>
            </div>';
            
            // Блок активной игры
            if ($active_game) {
                $participants = [];
                $game_model = new Game();
                $participants = $game_model->getGameParticipants($active_game['id']);
                $user_joined = false;
                
                foreach ($participants as $participant) {
                    if ($participant['user_id'] == $user['id']) {
                        $user_joined = true;
                        break;
                    }
                }
                
                echo '
                <div class="card game-info">
                    <h3>🎮 Активная игра</h3>
                    <div class="prize-fund">💰 ' . number_format($active_game['prize_fund'], 2) . ' руб.</div>
                    <div class="players-count">👥 ' . $active_game['players_count'] . '/' . MAX_PLAYERS . ' игроков</div>';
                    
                    if ($user_joined) {
                        echo '<div class="winner-badge">✅ Вы участвуете в игре!</div>';
                    }
                    
                    echo '
                    <button class="btn btn-game" onclick="openGame()">
                        ' . ($user_joined ? '🎯 Смотреть игру' : '🎮 Присоединиться за ' . TICKET_PRICE . ' руб.') . '
                    </button>
                </div>';
            } else {
                echo '
                <div class="card">
                    <h3>🎮 Нет активных игр</h3>
                    <p>Новая игра начнется скоро!</p>
                    <button class="btn" onclick="openGame()">🔄 Проверить игры</button>
                </div>';
            }
            
            echo '
            <div class="card">
                <h3>📊 Моя статистика</h3>
                <div class="game-stats">
                    <div class="stat-item">
                        <div class="stat-value">' . $total_games . '</div>
                        <div>Игр</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">' . $wins . '</div>
                        <div>Побед</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">' . $win_rate . '%</div>
                        <div>Успех</div>
                    </div>
                </div>
                <div style="margin-top: 10px;">
                    <strong>💰 Выиграно: ' . number_format($total_winnings, 2) . ' руб.</strong>
                </div>
            </div>
            
            <div class="card">
                <h3>⚙️ Управление</h3>
                <button class="btn btn-success" onclick="showDeposit()">💳 Пополнить баланс</button>
                <button class="btn" onclick="showHistory()">📈 История игр</button>
                <button class="btn" onclick="showProfile()">👤 Профиль</button>
                <button class="btn" onclick="showSupport()">🆘 Поддержка</button>
            </div>
        </div>

        <script>
            // Инициализация Telegram Web App
            if (typeof Telegram !== "undefined") {
                Telegram.WebApp.ready();
                Telegram.WebApp.expand();
                Telegram.WebApp.setHeaderColor("#667eea");
                Telegram.WebApp.setBackgroundColor("#667eea");
            }

            function openGame() {
                window.location.href = "game.php";
            }

            function showDeposit() {
                alert("💳 Пополнение баланса - скоро будет доступно!");
            }

            function showHistory() {
                window.location.href = "?page=history";
            }

            function showProfile() {
                window.location.href = "?page=profile";
            }
            
            function showSupport() {
                alert("🆘 Поддержка: @' . BOT_USERNAME . '\\nПишите нам в Telegram!");
            }
        </script>
    </body>
    </html>';
}

// Обработка разных страниц
if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'deposit':
            showDepositPage($user);
            break;
        case 'history':
            showHistoryPage($user);
            break;
        case 'profile':
            showProfilePage($user);
            break;
    }
}

function showDepositPage($user) {
    echo '
    <div class="container">
        <div class="card">
            <h2>💰 Пополнение баланса</h2>
            <p>Ваш баланс: <strong>' . number_format($user['balance'], 2) . ' руб.</strong></p>
            
            <h3>Выберите способ оплаты:</h3>
            <button class="btn" onclick="showTONPayment()">💎 TON Crystal</button>
            <button class="btn" onclick="showCardPayment()">💳 Банковская карта</button>
            <button class="btn" onclick="showCryptoPayment()">₿ Криптовалюты</button>
            
            <p style="margin-top: 20px; text-align: center;">
                <button class="btn" onclick="goBack()">← Назад</button>
            </p>
        </div>
    </div>
    
    <script>
        function showTONPayment() {
            alert("💎 Оплата TON - скоро будет доступна!");
        }
        
        function showCardPayment() {
            alert("💳 Оплата картой - скоро будет доступна!");
        }
        
        function showCryptoPayment() {
            alert("₿ Криптовалюты - скоро будут доступны!");
        }
        
        function goBack() {
            window.location.href = "./";
        }
    </script>';
}

function showHistoryPage($user) {
    $user_model = new User();
    $game_history = $user_model->getGameHistory($user['id']);
    
    echo '
    <div class="container">
        <div class="card">
            <h2>📊 История игр</h2>';
    
    if (empty($game_history)) {
        echo '<p>Вы еще не участвовали в играх.</p>
              <p>Присоединяйтесь к активной игре чтобы начать!</p>';
    } else {
        echo '<div style="max-height: 400px; overflow-y: auto;">';
        foreach ($game_history as $game) {
            $status = $game['is_winner'] ? 
                '🏆 <span style="color: #27ae60;">Выигрыш: ' . number_format($game['prize_amount'], 2) . ' руб.</span>' : 
                '❌ <span style="color: #e74c3c;">Не повезло</span>';
            
            echo '<div style="padding: 15px; border-bottom: 1px solid #eee; text-align: left;">
                    <div><strong>Игра #' . $game['game_id'] . '</strong></div>
                    <div>Номер: ' . $game['ticket_number'] . '</div>
                    <div>Ставка: ' . number_format($game['ticket_price'], 2) . ' руб.</div>
                    <div>Результат: ' . $status . '</div>
                    <div style="color: #666; font-size: 12px;">' . $game['game_date'] . '</div>
                  </div>';
        }
        echo '</div>';
    }
    
    echo '<p style="margin-top: 20px; text-align: center;">
            <button class="btn" onclick="goBack()">← Назад</button>
          </p>
        </div>
    </div>
    
    <script>
        function goBack() {
            window.location.href = "./";
        }
    </script>';
}

function showProfilePage($user) {
    $user_model = new User();
    $user_stats = $user_model->getStats($user['id']);
    
    $total_games = $user_stats['total_games'] ?? 0;
    $wins = $user_stats['wins'] ?? 0;
    $total_winnings = $user_stats['total_winnings'] ?? 0;
    $win_rate = $total_games > 0 ? round(($wins / $total_games) * 100, 1) : 0;
    
    echo '
    <div class="container">
        <div class="card">
            <h2>👤 Мой профиль</h2>
            
            <div style="text-align: left; margin: 20px 0;">
                <p><strong>🆔 ID:</strong> ' . $user['tg_id'] . '</p>
                <p><strong>👤 Имя:</strong> ' . htmlspecialchars($user['first_name']) . '</p>';
    
    if (!empty($user['username'])) {
        echo '<p><strong>📱 Username:</strong> @' . htmlspecialchars($user['username']) . '</p>';
    }
    
    echo '<p><strong>💎 Баланс:</strong> ' . number_format($user['balance'], 2) . ' руб.</p>
            </div>
            
            <h3>📊 Статистика игр:</h3>
            <div style="text-align: left; margin: 15px 0;">
                <p>🎮 Всего игр: ' . $total_games . '</p>
                <p>🏆 Побед: ' . $wins . '</p>
                <p>📈 Процент побед: ' . $win_rate . '%</p>
                <p>💰 Выиграно всего: ' . number_format($total_winnings, 2) . ' руб.</p>
            </div>
            
            <p style="margin-top: 20px; text-align: center;">
                <button class="btn" onclick="goBack()">← Назад</button>
            </p>
        </div>
    </div>
    
    <script>
        function goBack() {
            window.location.href = "./";
        }
    </script>';
}
?>