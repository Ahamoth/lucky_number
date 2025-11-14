<?php
class AdminController {
    private $user_model;
    private $game_model;
    private $payment_model;
    
    public function __construct() {
        $this->user_model = new User();
        $this->game_model = new Game();
        $this->payment_model = new Payment();
    }
    
    public function getDashboardStats() {
        return [
            'total_users' => $this->user_model->getTotalUsers(),
            'total_games' => $this->game_model->getTotalGames(),
            'total_deposits' => $this->payment_model->getTotalDeposits(),
            'total_prizes' => $this->game_model->getTotalPrizes(),
            'today_profit' => $this->payment_model->getTodayProfit(),
            'active_games' => count($this->game_model->getActiveGames())
        ];
    }
    
    public function getUsersList($page = 1, $per_page = 20) {
        $offset = ($page - 1) * $per_page;
        
        return $this->user_model->getAllUsers($offset, $per_page);
    }
    
    public function getGamesList($page = 1, $per_page = 20) {
        $offset = ($page - 1) * $per_page;
        
        return $this->game_model->getAllGames($offset, $per_page);
    }
    
    public function getPaymentsList($page = 1, $per_page = 20) {
        $offset = ($page - 1) * $per_page;
        
        return $this->payment_model->getAllPayments($offset, $per_page);
    }
    
    public function updateUserBalance($user_id, $amount, $reason) {
        $user = $this->user_model->getById($user_id);
        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }
        
        $this->user_model->updateBalance($user_id, $amount, 'admin_adjustment');
        
        // Логируем действие
        $this->logAdminAction("Изменение баланса пользователя {$user_id}: {$amount} руб. Причина: {$reason}");
        
        return ['success' => true, 'new_balance' => $user['balance'] + $amount];
    }
    
    private function logAdminAction($action) {
        $log_file = __DIR__ . '/../logs/admin_actions.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        file_put_contents($log_file, "[{$timestamp}] [{$ip}] {$action}\n", FILE_APPEND);
    }
}
?>