<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByTgId($tg_id) {
        try {
            $sql = "SELECT * FROM users WHERE tg_id = ?";
            return $this->db->fetch($sql, [$tg_id]);
        } catch (Exception $e) {
            error_log("User getByTgId error: " . $e->getMessage());
            return null;
        }
    }

    public function getById($user_id) {
        try {
            $sql = "SELECT * FROM users WHERE id = ?";
            return $this->db->fetch($sql, [$user_id]);
        } catch (Exception $e) {
            error_log("User getById error: " . $e->getMessage());
            return null;
        }
    }

    public function create($user_data) {
        try {
            $sql = "INSERT INTO users (tg_id, username, first_name, balance, created_at) 
                    VALUES (?, ?, ?, 0.00, NOW())";
            $this->db->query($sql, [
                $user_data['id'],
                $user_data['username'] ?? '',
                $user_data['first_name']
            ]);

            return $this->getByTgId($user_data['id']);
        } catch (Exception $e) {
            error_log("User create error: " . $e->getMessage());
            return null;
        }
    }

    public function updateBalance($user_id, $amount) {
        try {
            $sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $this->db->query($sql, [$amount, $user_id]);
            return true;
        } catch (Exception $e) {
            error_log("Update balance error: " . $e->getMessage());
            return false;
        }
    }

    public function getStats($user_id) {
        try {
            // Получаем реальную статистику из БД
            $sql = "SELECT 
                    COUNT(gp.id) as total_games,
                    SUM(CASE WHEN gp.is_winner = 1 THEN 1 ELSE 0 END) as wins,
                    COALESCE(SUM(gp.prize_amount), 0) as total_winnings
                    FROM game_participants gp
                    WHERE gp.user_id = ?";
            
            $stats = $this->db->fetch($sql, [$user_id]);
            
            return [
                'total_games' => $stats['total_games'] ?? 0,
                'wins' => $stats['wins'] ?? 0,
                'total_winnings' => $stats['total_winnings'] ?? 0
            ];
        } catch (Exception $e) {
            error_log("Get stats error: " . $e->getMessage());
            return [
                'total_games' => 0,
                'wins' => 0,
                'total_winnings' => 0
            ];
        }
    }

    public function getGameHistory($user_id) {
        try {
            $sql = "SELECT gp.*, g.ticket_price, g.created_at as game_date 
                    FROM game_participants gp 
                    JOIN games g ON gp.game_id = g.id 
                    WHERE gp.user_id = ? 
                    ORDER BY gp.joined_at DESC 
                    LIMIT 10";
            return $this->db->fetchAll($sql, [$user_id]);
        } catch (Exception $e) {
            error_log("Get game history error: " . $e->getMessage());
            return [];
        }
    }
}
?>