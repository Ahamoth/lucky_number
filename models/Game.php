<?php
class Game {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Создание новой игры
    public function createGame() {
        try {
            $sql = "INSERT INTO games (status, ticket_price, players_count, prize_fund, created_at) 
                    VALUES ('waiting', ?, 0, 0, NOW())";
            $this->db->query($sql, [TICKET_PRICE]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Create game error: " . $e->getMessage());
            return null;
        }
    }

    // Получение активной игры
    public function getActiveGame() {
        try {
            $sql = "SELECT * FROM games WHERE status = 'waiting' OR status = 'active' 
                    ORDER BY created_at DESC LIMIT 1";
            $game = $this->db->fetch($sql);
            
            // Если нет активной игры, создаем новую
            if (!$game) {
                $game_id = $this->createGame();
                if ($game_id) {
                    return $this->getGameById($game_id);
                }
            }
            
            return $game;
        } catch (Exception $e) {
            error_log("Get active game error: " . $e->getMessage());
            return null;
        }
    }

    // Участие в игре
    public function joinGame($user_id, $game_id) {
        try {
            // Проверяем баланс пользователя
            $user_model = new User();
            $user = $user_model->getById($user_id);
            
            if ($user['balance'] < TICKET_PRICE) {
                return ['success' => false, 'error' => 'Недостаточно средств'];
            }

            // Проверяем, не участвует ли уже пользователь
            $sql_check = "SELECT id FROM game_participants WHERE game_id = ? AND user_id = ?";
            $existing = $this->db->fetch($sql_check, [$game_id, $user_id]);
            
            if ($existing) {
                return ['success' => false, 'error' => 'Вы уже участвуете в этой игре'];
            }

            // Получаем доступный номер
            $ticket_number = $this->getAvailableTicketNumber($game_id);
            
            if (!$ticket_number) {
                return ['success' => false, 'error' => 'Нет свободных номеров'];
            }

            // Списываем средства
            $user_model->updateBalance($user_id, -TICKET_PRICE);

            // Добавляем участника
            $sql = "INSERT INTO game_participants (game_id, user_id, ticket_number, joined_at) 
                    VALUES (?, ?, ?, NOW())";
            $this->db->query($sql, [$game_id, $user_id, $ticket_number]);

            // Обновляем счетчик игроков и призовой фонд
            $this->updateGameStats($game_id);

            // Проверяем, можно ли начинать игру
            $this->checkGameStart($game_id);

            return [
                'success' => true, 
                'ticket_number' => $ticket_number,
                'game_id' => $game_id
            ];

        } catch (Exception $e) {
            error_log("Join game error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка сервера'];
        }
    }

    // Получение доступного номера
    private function getAvailableTicketNumber($game_id) {
        $used_numbers = $this->db->fetchAll(
            "SELECT ticket_number FROM game_participants WHERE game_id = ?", 
            [$game_id]
        );
        
        $used_numbers = array_column($used_numbers, 'ticket_number');
        $available_numbers = array_diff(range(1, 10), $used_numbers);
        
        return !empty($available_numbers) ? array_shift($available_numbers) : null;
    }

    // Обновление статистики игры
    private function updateGameStats($game_id) {
        $sql = "UPDATE games SET 
                players_count = (SELECT COUNT(*) FROM game_participants WHERE game_id = ?),
                prize_fund = (SELECT COUNT(*) FROM game_participants WHERE game_id = ?) * ticket_price * (PRIZE_FUND_PERCENT / 100)
                WHERE id = ?";
        $this->db->query($sql, [$game_id, $game_id, $game_id]);
    }

    // Проверка старта игры
    private function checkGameStart($game_id) {
        $game = $this->getGameById($game_id);
        
        if ($game['players_count'] >= MIN_PLAYERS && $game['status'] == 'waiting') {
            // Начинаем игру
            $this->startGame($game_id);
        }
    }

    // Запуск игры
    public function startGame($game_id) {
        try {
            // Обновляем статус игры
            $this->db->query(
                "UPDATE games SET status = 'active', started_at = NOW() WHERE id = ?", 
                [$game_id]
            );

            // Ждем 5 секунд и определяем победителей
            $this->scheduleWinnerSelection($game_id);

            return true;
        } catch (Exception $e) {
            error_log("Start game error: " . $e->getMessage());
            return false;
        }
    }

    // Планирование выбора победителей
    private function scheduleWinnerSelection($game_id) {
        // В реальном приложении используйте очереди (Redis, RabbitMQ)
        // Здесь симуляция через sleep в фоновом режиме
        $this->selectWinners($game_id);
    }

    // Выбор победителей
    public function selectWinners($game_id) {
        try {
            sleep(5); // Имитация ожидания 5 секунд

            $participants = $this->db->fetchAll(
                "SELECT * FROM game_participants WHERE game_id = ? ORDER BY RAND()",
                [$game_id]
            );

            $winners_count = min(WINNERS_COUNT, count($participants));
            $winners = array_slice($participants, 0, $winners_count);

            // Распределение призов
            $prize_fund = $this->db->fetch(
                "SELECT prize_fund FROM games WHERE id = ?", 
                [$game_id]
            )['prize_fund'];

            $user_model = new User();
            $total_distribution = array_sum($GLOBALS['prize_distribution']);

            foreach ($winners as $index => $winner) {
                $place = $index + 1;
                $prize_percent = $GLOBALS['prize_distribution'][$place] ?? 0;
                $prize_amount = ($prize_fund * $prize_percent) / $total_distribution;

                // Начисляем выигрыш
                $user_model->updateBalance($winner['user_id'], $prize_amount);

                // Отмечаем победителя
                $this->db->query(
                    "UPDATE game_participants SET is_winner = TRUE, prize_amount = ? WHERE id = ?",
                    [$prize_amount, $winner['id']]
                );
            }

            // Завершаем игру
            $this->db->query(
                "UPDATE games SET status = 'finished', finished_at = NOW() WHERE id = ?",
                [$game_id]
            );

            return $winners;

        } catch (Exception $e) {
            error_log("Select winners error: " . $e->getMessage());
            return [];
        }
    }

    // Получение информации об игре
    public function getGameById($game_id) {
        try {
            return $this->db->fetch("SELECT * FROM games WHERE id = ?", [$game_id]);
        } catch (Exception $e) {
            error_log("Get game by id error: " . $e->getMessage());
            return null;
        }
    }

    // Получение участников игры
    public function getGameParticipants($game_id) {
        try {
            $sql = "SELECT gp.*, u.first_name, u.username 
                    FROM game_participants gp 
                    JOIN users u ON gp.user_id = u.id 
                    WHERE gp.game_id = ? 
                    ORDER BY gp.joined_at";
            return $this->db->fetchAll($sql, [$game_id]);
        } catch (Exception $e) {
            error_log("Get game participants error: " . $e->getMessage());
            return [];
        }
    }

    // Получение истории игр пользователя
    public function getUserGameHistory($user_id) {
        try {
            $sql = "SELECT gp.*, g.ticket_price, g.created_at as game_date 
                    FROM game_participants gp 
                    JOIN games g ON gp.game_id = g.id 
                    WHERE gp.user_id = ? 
                    ORDER BY gp.joined_at DESC 
                    LIMIT 10";
            return $this->db->fetchAll($sql, [$user_id]);
        } catch (Exception $e) {
            error_log("Get user game history error: " . $e->getMessage());
            return [];
        }
    }
}
?>