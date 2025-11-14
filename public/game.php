<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Game.php';

$auth = new Auth();

// Обработка данных Telegram Web App
if (isset($_GET['tg_init_data'])) {
    $user = processTelegramInitData($_GET['tg_init_data']);
} elseif (isset($_POST['initData'])) {
    $user = processTelegramInitData($_POST['initData']);
} else {
    $user = $auth->getCurrentUser();
}

if (!$user) {
    header('Location: index.php');
    exit;
}

$game_model = new Game();
$active_game = $game_model->getActiveGame();

if (!$active_game) {
    echo "Нет активных игр";
    exit;
}

$participants = $game_model->getGameParticipants($active_game['id']);
$user_joined = false;
$user_ticket = null;

foreach ($participants as $participant) {
    if ($participant['user_id'] == $user['id']) {
        $user_joined = true;
        $user_ticket = $participant['ticket_number'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎰 Игра - Счастливый Номер</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
            overflow-x: hidden;
        }
        
        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .game-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .prize-fund {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            color: #ffd700;
        }
        
        .players-count {
            text-align: center;
            margin: 10px 0;
            font-size: 18px;
        }
        
        .wheel-container {
            position: relative;
            width: 300px;
            height: 300px;
            margin: 20px auto;
        }
        
        .wheel {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(
                #ff6b6b, #ffa726, #ffee58, #66bb6a, 
                #42a5f5, #5c6bc0, #ab47bc, #ff6b6b
            );
            position: relative;
            transition: transform 5s cubic-bezier(0.2, 0.8, 0.2, 1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .wheel-spinning {
            animation: spin 5s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(1080deg); }
        }
        
        .wheel-number {
            position: absolute;
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #333;
            transform-origin: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .participants {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        
        .participant {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            font-size: 12px;
        }
        
        .participant-number {
            font-size: 18px;
            font-weight: bold;
            color: #ffd700;
        }
        
        .btn {
            background: linear-gradient(45deg, #ff6b6b, #ffa726);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin: 10px 0;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            background: #666;
            cursor: not-allowed;
        }
        
        .btn-join {
            background: linear-gradient(45deg, #66bb6a, #4caf50);
        }
        
        .ticket-display {
            text-align: center;
            font-size: 48px;
            font-weight: bold;
            color: #ffd700;
            margin: 20px 0;
            text-shadow: 0 2px 10px rgba(255, 215, 0, 0.5);
        }
        
        .winner-animation {
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .status-message {
            text-align: center;
            margin: 10px 0;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎰 Счастливый Номер</h1>
            <p>Привет, <?php echo htmlspecialchars($user['first_name']); ?>! 💎 <?php echo $user['balance']; ?> руб.</p>
        </div>
        
        <div class="game-card">
            <div class="prize-fund">
                💰 Призовой фонд: <?php echo $active_game['prize_fund']; ?> руб.
            </div>
            
            <div class="players-count">
                👥 Игроков: <?php echo $active_game['players_count']; ?>/<?php echo MAX_PLAYERS; ?>
            </div>
            
            <?php if ($user_joined): ?>
                <div class="ticket-display winner-animation">
                    Ваш номер: <?php echo $user_ticket; ?>
                </div>
                <div class="status-message">
                    ✅ Вы участвуете в игре! Ожидайте розыгрыша...
                </div>
            <?php else: ?>
                <button class="btn btn-join" onclick="joinGame()">
                    🎮 Войти в игру за <?php echo TICKET_PRICE; ?> руб.
                </button>
            <?php endif; ?>
            
            <div class="wheel-container">
                <div class="wheel" id="wheel">
                    <!-- Номера будут добавлены через JavaScript -->
                </div>
            </div>
            
            <div class="participants" id="participants">
                <?php foreach ($participants as $participant): ?>
                    <div class="participant">
                        <div class="participant-number"><?php echo $participant['ticket_number']; ?></div>
                        <div><?php echo htmlspecialchars($participant['first_name']); ?></div>
                    </div>
                <?php endforeach; ?>
                
                <?php for ($i = count($participants); $i < MAX_PLAYERS; $i++): ?>
                    <div class="participant" style="background: rgba(255,255,255,0.1);">
                        <div class="participant-number">?</div>
                        <div>Свободно</div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <button class="btn" onclick="goBack()">← Назад</button>
    </div>

    <script>
        const gameId = <?php echo $active_game ? $active_game['id'] : 0; ?>;
        const userJoined = <?php echo $user_joined ? 'true' : 'false'; ?>;
        
        // Создаем колесо с номерами
        function createWheel() {
            const wheel = document.getElementById('wheel');
            const numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            
            numbers.forEach((number, index) => {
                const angle = (index / numbers.length) * 360;
                const numberElement = document.createElement('div');
                numberElement.className = 'wheel-number';
                numberElement.textContent = number;
                numberElement.style.transform = `rotate(${angle}deg) translate(130px) rotate(-${angle}deg)`;
                wheel.appendChild(numberElement);
            });
        }
        
        // Участие в игре
        async function joinGame() {
            try {
                const response = await fetch(`../api/game.php?action=join_game`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `game_id=${gameId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(`🎉 Вы успешно присоединились! Ваш номер: ${result.ticket_number}`);
                    location.reload();
                } else {
                    alert(`❌ Ошибка: ${result.error}`);
                }
            } catch (error) {
                alert('❌ Ошибка соединения');
            }
        }
        
        function goBack() {
            window.location.href = 'index.php';
        }
        
        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            createWheel();
            
            if (typeof Telegram !== "undefined") {
                Telegram.WebApp.ready();
                Telegram.WebApp.expand();
            }
        });
    </script>
</body>
</html>