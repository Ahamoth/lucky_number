<?php
// Базовые настройки
define('SITE_NAME', 'Счастливый Номер');
define('SITE_URL', 'https://slinkier-dominic-uninnocently.ngrok-free.dev/tg_lucky_number');

// Настройки БД для XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'tg_lucky_number');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Настройки игры
define('MIN_PLAYERS', 3);
define('MAX_PLAYERS', 10);
define('WINNERS_COUNT', 3);
define('TICKET_PRICE', 10);
define('PRIZE_FUND_PERCENT', 85);

// Telegram настройки
define('BOT_TOKEN', '8305094727:AAGq9uFFErPzDbEfhmwtm1zDYJ1--NZSXCc');
define('BOT_USERNAME', 'YourLuckyNumberBot');
define('TELEGRAM_WEBHOOK_URL', SITE_URL . '/webhooks/telegram.php');
define('TELEGRAM_WEBAPP_URL', SITE_URL . '/public/');
define('TELEGRAM_STARS_PRICE', 7); // 7 stars = 10 руб.
define('MIN_DEPOSIT_STARS', 7); // Минимальное пополнение

// Безопасность
define('ENCRYPTION_KEY', 'd4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9');
define('JWT_SECRET', 'b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f6a7b8c9');

// Админка
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', password_hash('start', PASSWORD_DEFAULT));

// Призовое распределение
$GLOBALS['prize_distribution'] = [
    1 => 40,
    2 => 30, 
    3 => 30
];

// Включить отображение ошибок для разработки
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>