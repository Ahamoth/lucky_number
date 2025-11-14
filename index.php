<?php
// index.php - стартовая страница для проверки
require_once 'config/config.php';

echo "<h1>🎰 Счастливый Номер - Панель управления</h1>";
echo "<p>Бот: " . BOT_USERNAME . "</p>";
echo "<p>База данных: " . DB_NAME . "</p>";

echo "<h2>🔧 Инструменты:</h2>";
echo "<ul>";
echo "<li><a href='check_webhook.php'>Проверить вебхук</a></li>";
echo "<li><a href='bot_commands.php'>Установить команды бота</a></li>";
echo "<li><a href='admin/'>Админ-панель</a></li>";
echo "</ul>";

// Проверка соединения с БД
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    echo "<p style='color: green;'>✅ База данных подключена</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Ошибка БД: " . $e->getMessage() . "</p>";
}
?>