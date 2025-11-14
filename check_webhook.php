<?php
require_once 'config/config.php';

echo "<h1>🔧 Проверка настроек бота</h1>";

// Проверка бота
$me_url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getMe";
$me = json_decode(file_get_contents($me_url), true);

echo "<h2>🤖 Информация о боте:</h2>";
echo "<pre>";
print_r($me);
echo "</pre>";

// Проверка вебхука
$webhook_url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getWebhookInfo";
$webhook_info = json_decode(file_get_contents($webhook_url), true);

echo "<h2>🌐 Информация о вебхуке:</h2>";
echo "<pre>";
print_r($webhook_info);
echo "</pre>";

// Проверка БД
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    echo "<p style='color: green;'>✅ База данных подключена</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Ошибка БД: " . $e->getMessage() . "</p>";
}
?>