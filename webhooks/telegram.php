<?php
require_once __DIR__ . '/../core/autoload.php';
// Исправленные пути для вашей структуры
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/User.php';

// Создаем папку logs если нет
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

// Логируем входящий запрос
file_put_contents(__DIR__ . '/../logs/telegram_webhook.log', 
    date('Y-m-d H:i:s') . " - " . file_get_contents('php://input') . "\n", 
    FILE_APPEND
);

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    exit;
}

// Обрабатываем сообщение
processUpdate($input);

http_response_code(200);
echo 'OK';

function processUpdate($update) {
    if (isset($update['message'])) {
        processMessage($update['message']);
    } elseif (isset($update['callback_query'])) {
        processCallbackQuery($update['callback_query']);
    } elseif (isset($update['web_app_data'])) {
        processWebAppData($update['web_app_data']);
    }
}

function processMessage($message) {
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $user = $message['from'];
    
    // Регистрируем/получаем пользователя
    $user_model = new User();
    $db_user = $user_model->getByTgId($user['id']);
    if (!$db_user) {
        $db_user = $user_model->create($user);
    }
    
    // Логируем обработку
    file_put_contents(__DIR__ . '/../logs/telegram_webhook.log', 
        date('Y-m-d H:i:s') . " - Processing: {$text} from {$user['id']}\n", 
        FILE_APPEND
    );
    
    switch ($text) {
        case '/start':
            sendWelcomeMessage($chat_id, $user, $db_user);
            break;
            
        case '/balance':
            sendBalanceMessage($chat_id, $db_user);
            break;
            
        case '/game':
            sendGameLink($chat_id);
            break;
            
        case '/history':
            sendHistoryMessage($chat_id, $db_user);
            break;
            
        case '/profile':
            sendProfileMessage($chat_id, $db_user);
            break;
            
        case '/support':
            sendSupportMessage($chat_id);
            break;
            
        default:
            if (strpos($text, '/deposit') === 0) {
                sendDepositInfo($chat_id);
            } else {
                sendHelpMessage($chat_id);
            }
    }
}

function sendWelcomeMessage($chat_id, $user, $db_user) {
    $message = "🎰 <b>Добро пожаловать в Счастливый Номер, {$user['first_name']}!</b>\n\n";
    $message .= "💎 <b>Ваш баланс:</b> {$db_user['balance']} руб.\n\n";
    $message .= "🎮 <b>Как играть:</b>\n";
    $message .= "• Входите в игру за " . TICKET_PRICE . " руб.\n";
    $message .= "• Получаете случайный номер от 1 до 10\n";
    $message .= "• 3 победителя получают денежные призы!\n";
    $message .= "• Выигрыши мгновенно начисляются на баланс\n\n";
    $message .= "👇 <b>Нажмите кнопку ниже чтобы начать игру!</b>";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                [
                    'text' => '🎮 Играть сейчас', 
                    'web_app' => [
                        'url' => TELEGRAM_WEBAPP_URL . '?tg_user=' . $user['id']
                    ]
                ]
            ],
            [
                [
                    'text' => '💰 Пополнить баланс', 
                    'callback_data' => 'deposit'
                ],
                [
                    'text' => '📊 Моя статистика', 
                    'callback_data' => 'stats'
                ]
            ]
        ]
    ];
    
    sendTelegramMessage($chat_id, $message, $keyboard);
}

function sendBalanceMessage($chat_id, $user) {
    $message = "💎 <b>Ваш баланс:</b> {$user['balance']} руб.\n\n";
    
    $stats = (new User())->getStats($user['id']);
    if ($stats) {
        $message .= "📊 <b>Статистика:</b>\n";
        $message .= "• Игр сыграно: {$stats['total_games']}\n";
        $message .= "• Побед: {$stats['wins']}\n";
        $message .= "• Выиграно всего: {$stats['total_winnings']} руб.\n";
        $win_rate = $stats['total_games'] > 0 ? round(($stats['wins'] / $stats['total_games']) * 100, 1) : 0;
        $message .= "• Процент побед: {$win_rate}%\n";
    }
    
    $keyboard = [
        'inline_keyboard' => [
            [
                [
                    'text' => '💰 Пополнить баланс', 
                    'callback_data' => 'deposit'
                ]
            ],
            [
                [
                    'text' => '🎮 К игре', 
                    'web_app' => ['url' => TELEGRAM_WEBAPP_URL]
                ]
            ]
        ]
    ];
    
    sendTelegramMessage($chat_id, $message, $keyboard);
}

function sendGameLink($chat_id) {
    $message = "🎮 <b>Готовы к игре?</b>\n\n";
    $message .= "Нажмите кнопку ниже чтобы открыть игровое меню и присоединиться к текущим играм!";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                [
                    'text' => '🎮 Открыть игру', 
                    'web_app' => ['url' => TELEGRAM_WEBAPP_URL]
                ]
            ]
        ]
    ];
    
    sendTelegramMessage($chat_id, $message, $keyboard);
}

function sendHistoryMessage($chat_id, $user) {
    $user_model = new User();
    $history = $user_model->getGameHistory($user['id']);
    
    $message = "📊 <b>История ваших игр</b>\n\n";
    
    if (empty($history)) {
        $message .= "Вы еще не участвовали в играх.\n";
        $message .= "Начните свою первую игру прямо сейчас!";
    } else {
        $message .= "Последние 5 игр:\n\n";
        foreach (array_slice($history, 0, 5) as $game) {
            $status = $game['is_winner'] ? "🏆 Выигрыш: {$game['prize_amount']} руб." : "❌ Не повезло";
            $message .= "🎰 Игра #{$game['game_id']} - {$status}\n";
        }
    }
    
    $keyboard = [
        'inline_keyboard' => [
            [
                [
                    'text' => '🎮 Играть сейчас', 
                    'web_app' => ['url' => TELEGRAM_WEBAPP_URL]
                ]
            ]
        ]
    ];
    
    sendTelegramMessage($chat_id, $message, $keyboard);
}

function sendProfileMessage($chat_id, $user) {
    $user_model = new User();
    $stats = $user_model->getStats($user['id']);
    
    $message = "👤 <b>Ваш профиль</b>\n\n";
    $message .= "🆔 ID: {$user['tg_id']}\n";
    $message .= "👤 Имя: {$user['first_name']}\n";
    if (!empty($user['username'])) {
        $message .= "📱 @{$user['username']}\n";
    }
    $message .= "💎 Баланс: {$user['balance']} руб.\n\n";
    
    if ($stats) {
        $message .= "📊 <b>Статистика игр:</b>\n";
        $message .= "• Всего игр: {$stats['total_games']}\n";
        $message .= "• Побед: {$stats['wins']}\n";
        $message .= "• Выиграно: {$stats['total_winnings']} руб.\n";
        $win_rate = $stats['total_games'] > 0 ? round(($stats['wins'] / $stats['total_games']) * 100, 1) : 0;
        $message .= "• Процент побед: {$win_rate}%\n";
    }
    
    $keyboard = [
        'inline_keyboard' => [
            [
                [
                    'text' => '🎮 Играть', 
                    'web_app' => ['url' => TELEGRAM_WEBAPP_URL]
                ],
                [
                    'text' => '💰 Пополнить', 
                    'callback_data' => 'deposit'
                ]
            ]
        ]
    ];
    
    sendTelegramMessage($chat_id, $message, $keyboard);
}

function sendSupportMessage($chat_id) {
    $message = "🆘 <b>Поддержка</b>\n\n";
    $message .= "Если у вас возникли проблемы с игрой, оплатой или у вас есть вопросы:\n\n";
    $message .= "📧 <b>Email:</b> support@luckynumber.ru\n";
    $message .= "⏰ <b>Время работы:</b> 24/7\n\n";
    $message .= "Мы отвечаем в течение 15 минут!\n\n";
    $message .= "⚠️ <b>Перед обращением:</b>\n";
    $message .= "• Проверьте баланс (/balance)\n";
    $message .= "• Посмотрите историю игр (/history)\n";
    $message .= "• Убедитесь что платеж прошел";
    
    sendTelegramMessage($chat_id, $message);
}

function sendDepositInfo($chat_id) {
    $message = "💰 <b>Пополнение баланса</b>\n\n";
    $message .= "Для пополнения баланса откройте веб-приложение и выберите удобный способ оплаты.\n\n";
    $message .= "💎 <b>Доступные методы:</b>\n";
    $message .= "• TON (The Open Network) - мгновенное зачисление\n";
    $message .= "• Другие методы (в разработке)\n\n";
    $message .= "Минимальная сумма пополнения: 10 руб.\n";
    $message .= "Максимальная сумма: 10,000 руб.";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                [
                    'text' => '💰 Пополнить баланс', 
                    'web_app' => ['url' => TELEGRAM_WEBAPP_URL . '?page=deposit']
                ]
            ]
        ]
    ];
    
    sendTelegramMessage($chat_id, $message, $keyboard);
}

function sendHelpMessage($chat_id) {
    $message = "🤖 <b>Счастливый Номер - команды бота</b>\n\n";
    $message .= "<b>Основные команды:</b>\n";
    $message .= "/start - начать работу с ботом\n";
    $message .= "/game - быстрый старт игры\n";
    $message .= "/balance - узнать баланс и статистику\n";
    $message .= "/history - история игр\n";
    $message .= "/profile - профиль игрока\n";
    $message .= "/support - связь с поддержкой\n\n";
    $message .= "🎮 <b>Чтобы начать играть:</b>\n";
    $message .= "1. Нажмите '🎮 Играть сейчас'\n";
    $message .= "2. Пополните баланс если нужно\n";
    $message .= "3. Выберите активную игру\n";
    $message .= "4. Получите номер и ждите розыгрыша!\n\n";
    $message .= "💰 <b>Выигрыши</b> мгновенно начисляются на баланс!";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                [
                    'text' => '🎮 Начать игру', 
                    'web_app' => ['url' => TELEGRAM_WEBAPP_URL]
                ]
            ]
        ]
    ];
    
    sendTelegramMessage($chat_id, $message, $keyboard);
}
// Добавьте обработку команды /stars
case '/stars':
    sendStarsInfo($chat_id);
    break;

function sendStarsInfo($chat_id) {
    $message = "⭐ <b>Telegram Stars</b>\n\n";
    $message .= "💎 <b>Курс:</b> 7 Stars = 10 рублей\n\n";
    $message .= "🎯 <b>Как пополнить:</b>\n";
    $message .= "1. Нажмите кнопку ниже\n";
    $message .= "2. Выберите сумму\n";
    $message .= "3. Оплатите через Telegram\n";
    $message .= "4. Баланс пополнится мгновенно!\n\n";
    $message .= "⚡ <b>Мгновенное зачисление!</b>";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                [
                    'text' => '⭐ Пополнить баланс', 
                    'web_app' => ['url' => TELEGRAM_WEBAPP_URL . 'stars_deposit.php']
                ]
            ]
        ]
    ];
    
    sendTelegramMessage($chat_id, $message, $keyboard);
}
function processCallbackQuery($callback_query) {
    $chat_id = $callback_query['message']['chat']['id'];
    $data = $callback_query['data'];
    $user = $callback_query['from'];
    
    // Логируем callback
    file_put_contents(__DIR__ . '/../logs/telegram_webhook.log', 
        date('Y-m-d H:i:s') . " - Callback: {$data} from {$user['id']}\n", 
        FILE_APPEND
    );
    
    switch ($data) {
        case 'deposit':
            sendDepositInfo($chat_id);
            break;
            
        case 'stats':
            $user_model = new User();
            $db_user = $user_model->getByTgId($user['id']);
            sendBalanceMessage($chat_id, $db_user);
            break;
            
        case 'game':
            sendGameLink($chat_id);
            break;
    }
    
    // Ответ на callback query
    answerCallbackQuery($callback_query['id']);
}

function sendTelegramMessage($chat_id, $text, $keyboard = null) {
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    
    if ($keyboard) {
        $data['reply_markup'] = json_encode($keyboard);
    }
    
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    // Логируем отправку
    file_put_contents(__DIR__ . '/../logs/telegram_webhook.log', 
        date('Y-m-d H:i:s') . " - Sent to {$chat_id}: " . substr($text, 0, 50) . "...\n", 
        FILE_APPEND
    );
    
    return $result;
}

function answerCallbackQuery($callback_query_id) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/answerCallbackQuery";
    $data = [
        'callback_query_id' => $callback_query_id
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}
?>