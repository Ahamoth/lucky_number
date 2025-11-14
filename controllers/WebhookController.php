<?php
class WebhookController {
    private $payment_controller;
    
    public function __construct() {
        $this->payment_controller = new PaymentController();
    }
    
    public function handleTelegramWebhook($update) {
        try {
            if (isset($update['message'])) {
                $this->processMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->processCallback($update['callback_query']);
            }
        } catch (Exception $e) {
            error_log("Webhook error: " . $e->getMessage());
        }
    }
    
    private function processMessage($message) {
        $chat_id = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $user = $message['from'];
        
        $auth = new Auth();
        $auth->login($user);
        
        switch ($text) {
            case '/start':
                $this->sendWelcomeMessage($chat_id, $user);
                break;
            case '/balance':
                $this->sendBalance($chat_id, $user);
                break;
            case '/game':
                $this->sendGameLink($chat_id);
                break;
            default:
                $this->sendHelpMessage($chat_id);
        }
    }
    
    private function sendWelcomeMessage($chat_id, $user) {
        $user_model = new User();
        $user_data = $user_model->getByTgId($user['id']);
        
        $message = "🎰 Добро пожаловать в <b>Счастливый Номер</b>, {$user['first_name']}!\n\n";
        $message .= "💎 Ваш баланс: <b>{$user_data['balance']} руб.</b>\n\n";
        $message .= "🎮 <b>Как играть:</b>\n";
        $message .= "• Входите в игру за " . TICKET_PRICE . " руб.\n";
        $message .= "• Получаете случайный номер\n";
        $message .= "• 3 победителя получают призы!\n\n";
        $message .= "👇 Нажмите кнопку ниже чтобы начать!";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🎮 Играть сейчас', 
                        'web_app' => ['url' => TELEGRAM_WEBAPP_URL]
                    ]
                ],
                [
                    [
                        'text' => '💰 Пополнить баланс', 
                        'callback_data' => 'deposit'
                    ],
                    [
                        'text' => '📊 Статистика', 
                        'callback_data' => 'stats'
                    ]
                ]
            ]
        ];
        
        $this->sendTelegramMessage($chat_id, $message, $keyboard);
    }
    
    private function sendTelegramMessage($chat_id, $text, $keyboard = null) {
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        
        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }
        
        file_get_contents(
            "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage?" . 
            http_build_query($data)
        );
    }
}
?>