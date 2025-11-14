<?php
class ApiController {
    private $user_model;
    private $game_model;
    private $payment_model;
    
    public function __construct() {
        $this->user_model = new User();
        $this->game_model = new Game();
        $this->payment_model = new Payment();
    }
    
    public function joinGame($user_id) {
        // Проверяем баланс
        $user = $this->user_model->getById($user_id);
        if ($user['balance'] < TICKET_PRICE) {
            return ['success' => false, 'error' => 'Недостаточно средств'];
        }
        
        // Ищем активную игру
        $active_games = $this->game_model->getActiveGames();
        $game = null;
        
        foreach ($active_games as $active_game) {
            if ($active_game['players_count'] < MAX_PLAYERS) {
                $game = $active_game;
                break;
            }
        }
        
        if (!$game) {
            $game_id = $this->game_model->create();
            $game = $this->game_model->getById($game_id);
        }
        
        // Генерируем номер билета
        $ticket_number = rand(1, 10);
        
        // Добавляем игрока
        $this->game_model->addPlayer($game['id'], $user_id, $ticket_number);
        
        // Списываем средства
        $this->user_model->updateBalance($user_id, -TICKET_PRICE, TRANSACTION_GAME_BET);
        
        // Обновляем призовой фонд
        $this->db->query(
            "UPDATE games SET prize_pool = prize_pool + ? WHERE id = ?",
            [TICKET_PRICE * (PRIZE_FUND_PERCENT / 100), $game['id']]
        );
        
        // Проверяем, можно ли начинать игру
        $updated_game = $this->game_model->getById($game['id']);
        if ($updated_game['players_count'] >= MIN_PLAYERS) {
            $winners = $this->game_model->startGame($game['id']);
            return [
                'success' => true, 
                'game_id' => $game['id'],
                'started' => true,
                'winners' => $winners
            ];
        }
        
        return [
            'success' => true, 
            'game_id' => $game['id'],
            'started' => false
        ];
    }
    
    public function createInvoice($user_id, $amount) {
        if ($amount < 10 || $amount > 10000) {
            return ['success' => false, 'error' => 'Неверная сумма'];
        }
        
        return $this->payment_model->createInvoice($user_id, $amount);
    }
    
    public function checkPayment($invoice_id) {
        return ['paid' => $this->payment_model->checkPayment($invoice_id)];
    }
    
    public function getUserStats($user_id) {
        return $this->user_model->getStats($user_id);
    }
}
?>