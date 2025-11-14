<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добро пожаловать - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        .welcome-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .welcome-content {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .welcome-content h1 {
            color: #2D3748;
            margin-bottom: 10px;
        }
        
        .welcome-content p {
            color: #718096;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .features {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin: 30px 0;
        }
        
        .feature {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #F7FAFC;
            border-radius: 10px;
            text-align: left;
        }
        
        .feature-icon {
            font-size: 1.5rem;
        }
        
        .telegram-btn {
            display: inline-block;
            background: #0088CC;
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .telegram-btn:hover {
            background: #0077B5;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-content">
            <div class="welcome-icon">🎰</div>
            <h1>Добро пожаловать в <?= SITE_NAME ?>!</h1>
            <p>Мгновенные лотереи с реальными выигрышами в реальном времени</p>
            
            <div class="features">
                <div class="feature">
                    <span class="feature-icon">⚡</span>
                    <div>
                        <strong>Мгновенные игры</strong>
                        <div>Быстрые раунды по 5-10 игроков</div>
                    </div>
                </div>
                <div class="feature">
                    <span class="feature-icon">💰</span>
                    <div>
                        <strong>Реальные выигрыши</strong>
                        <div>Мгновенное зачисление на баланс</div>
                    </div>
                </div>
                <div class="feature">
                    <span class="feature-icon">🎮</span>
                    <div>
                        <strong>Простота игры</strong>
                        <div>Получи номер и жди результата</div>
                    </div>
                </div>
                <div class="feature">
                    <span class="feature-icon">🔒</span>
                    <div>
                        <strong>Безопасность</strong>
                        <div>Проверяемая честность игры</div>
                    </div>
                </div>
            </div>
            
            <p>Для начала игры откройте приложение через Telegram бота</p>
            
            <a href="https://t.me/<?= BOT_USERNAME ?>" class="telegram-btn">
                📲 Открыть в Telegram
            </a>
            
            <div style="margin-top: 20px; font-size: 0.9rem; color: #A0AEC0;">
                Уже есть аккаунт? Откройте бота и нажмите "Играть"
            </div>
        </div>
    </div>
</body>
</html>