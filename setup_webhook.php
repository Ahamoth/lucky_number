<?php
require_once 'config/config.php';

echo "<h1>🌐 Настройка Telegram вебхука</h1>";

// Проверяем бота
$me_url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getMe";
$me = json_decode(file_get_contents($me_url), true);

if (!$me || !$me['ok']) {
    die("<p style='color: red;'>❌ Ошибка: Неверный BOT_TOKEN или бот не доступен</p>");
}

echo "<p>✅ Бот: @" . $me['result']['username'] . " (" . $me['result']['first_name'] . ")</p>";

// Устанавливаем вебхук
$webhook_url = TELEGRAM_WEBHOOK_URL;
echo "<p>Webhook URL: <strong>" . $webhook_url . "</strong></p>";

$url = "https://api.telegram.org/bot" . BOT_TOKEN . "/setWebhook";
$data = ['url' => $webhook_url];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
curl_close($ch);

$response = json_decode($result, true);

echo "<h2>Результат:</h2>";
echo "<pre>";
print_r($response);
echo "</pre>";

if ($response['ok']) {
    echo "<p style='color: green; font-size: 20px;'>✅ Вебхук успешно установлен!</p>";
    echo "<p><strong>Теперь проверьте бота в Telegram!</strong></p>";
    
    // Показываем информацию о вебхуке
    $info_url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getWebhookInfo";
    $webhook_info = json_decode(file_get_contents($info_url), true);
    
    echo "<h2>Информация о вебхуке:</h2>";
    echo "<pre>";
    print_r($webhook_info);
    echo "</pre>";
    
    echo "<h3>🎯 Что делать дальше:</h3>";
    echo "<ol>";
    echo "<li>Откройте Telegram</li>";
    echo "<li>Найдите @YourLuckyNumberBot</li>";
    echo "<li>Нажмите /start</li>";
    echo "<li>Бот должен ответить приветствием!</li>";
    echo "</ol>";
} else {
    echo "<p style='color: red;'>❌ Ошибка: " . $response['description'] . "</p>";
}
?>