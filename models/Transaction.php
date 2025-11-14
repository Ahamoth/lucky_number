<?php
class Transaction {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($user_id, $type, $amount, $description = '') {
        return $this->db->query(
            "INSERT INTO transactions (user_id, type, amount, description, created_at) 
             VALUES (?, ?, ?, ?, NOW())",
            [$user_id, $type, $amount, $description]
        );
    }
    
    public function getUserTransactions($user_id, $limit = 20) {
        return $this->db->fetchAll(
            "SELECT * FROM transactions 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$user_id, $limit]
        );
    }
    
    public function getDailyStats() {
        return $this->db->fetchAll(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_count,
                SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END) as total_deposits,
                SUM(CASE WHEN type = 'game_win' THEN amount ELSE 0 END) as total_prizes,
                SUM(CASE WHEN type = 'game_bet' THEN amount ELSE 0 END) as total_bets
             FROM transactions 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date DESC"
        );
    }
}
?>