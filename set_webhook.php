<?php
require_once 'config/config.php';

$webhook_url = TELEGRAM_WEBHOOK_URL;

echo "<h1>🌐 Настройка вебхука</h1>";
echo "<p>URL: {$webhook_url}</p>";

$url = "https://api.telegram.org/bot" . BOT_TOKEN . "/setWebhook";
$data = ['url' => $webhook_url];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
curl_close($ch);

echo "<pre>";
print_r(json_decode($result, true));
echo "</pre>";
?>