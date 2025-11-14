<?php
require_once 'config/config.php';

function setBotCommands() {
    $commands = [
        [
            'command' => 'start',
            'description' => '🎰 Начать игру'
        ],
        [
            'command' => 'balance', 
            'description' => '💰 Мой баланс'
        ],
        [
            'command' => 'game',
            'description' => '🎮 Быстрый старт'
        ],
        [
            'command' => 'history',
            'description' => '📊 История игр'
        ],
        [
            'command' => 'profile',
            'description' => '👤 Мой профиль'
        ],
        [
            'command' => 'support',
            'description' => '🆘 Поддержка'
        ]
    ];

    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/setMyCommands";
    $data = [
        'commands' => json_encode($commands),
        'scope' => json_encode(['type' => 'all_private_chats'])
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($result, true);
}

$result = setBotCommands();
echo "<pre>";
print_r($result);
echo "</pre>";
?>