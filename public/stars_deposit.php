<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/StarsPayment.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

if (!$user) {
    header('Location: index.php');
    exit;
}

$stars_payment = new StarsPayment();

// Обработка выбора суммы
if (isset($_POST['stars_amount'])) {
    $stars_amount = intval($_POST['stars_amount']);
    $result = $stars_payment->createInvoice($user['id'], $stars_amount);
    
    if ($result['success']) {
        // Здесь будет интеграция с Telegram Stars API
        $invoice_url = "https://t.me/" . BOT_USERNAME . "?start=stars_" . $result['invoice_id'];
        header("Location: " . $invoice_url);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⭐ Пополнение Stars</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: white;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
        }
        .card {
            background: white;
            color: #333;
            padding: 20px;
            border-radius: 20px;
            margin: 10px 0;
            text-align: center;
        }
        .btn {
            background: linear-gradient(45deg, #FFD700, #FFA500);
            color: #333;
            padding: 15px;
            border: none;
            border-radius: 10px;
            margin: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: calc(50% - 20px);
            display: inline-block;
        }
        .stars-amount {
            font-size: 24px;
            font-weight: bold;
            color: #FFD700;
        }
        .rub-amount {
            font-size: 16px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>⭐ Пополнение баланса</h2>
            <p>Ваш баланс: <strong><?php echo $user['balance']; ?> руб.</strong></p>
            <p>Выберите сумму для пополнения:</p>
            
            <form method="POST">
                <button type="submit" name="stars_amount" value="7" class="btn">
                    <div class="stars-amount">7 ⭐</div>
                    <div class="rub-amount">≈ 10 руб.</div>
                </button>
                
                <button type="submit" name="stars_amount" value="35" class="btn">
                    <div class="stars-amount">35 ⭐</div>
                    <div class="rub-amount">≈ 50 руб.</div>
                </button>
                
                <button type="submit" name="stars_amount" value="70" class="btn">
                    <div class="stars-amount">70 ⭐</div>
                    <div class="rub-amount">≈ 100 руб.</div>
                </button>
                
                <button type="submit" name="stars_amount" value="350" class="btn">
                    <div class="stars-amount">350 ⭐</div>
                    <div class="rub-amount">≈ 500 руб.</div>
                </button>
            </form>
            
            <p style="margin-top: 20px; font-size: 12px; color: #666;">
                💡 7 Stars = 10 рублей<br>
                ⚡ Мгновенное зачисление
            </p>
            
            <button onclick="goBack()" style="background: #666; color: white; padding: 10px 20px; border: none; border-radius: 10px; margin-top: 20px;">
                ← Назад
            </button>
        </div>
    </div>
    
    <script>
        function goBack() {
            window.location.href = 'index.php';
        }
        
        if (typeof Telegram !== "undefined") {
            Telegram.WebApp.ready();
            Telegram.WebApp.expand();
        }
    </script>
</body>
</html>