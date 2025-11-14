<?php
require_once '../config/config.php';
require_once '../core/Auth.php';
require_once '../controllers/AdminController.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
    exit;
}

$action = $_GET['action'] ?? '';
$admin_controller = new AdminController();

switch ($action) {
    case 'create_game':
        $game_model = new Game();
        $game_id = $game_model->create();
        echo json_encode(['success' => true, 'game_id' => $game_id]);
        break;
        
    case 'start_game':
        $game_id = $_GET['game_id'] ?? null;
        if (!$game_id) {
            echo json_encode(['success' => false, 'error' => 'ID игры не указан']);
            exit;
        }
        
        $game_model = new Game();
        $winners = $game_model->startGame($game_id);
        echo json_encode(['success' => true, 'winners' => $winners]);
        break;
        
    case 'update_balance':
        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id'] ?? null;
        $operation = $input['operation'] ?? '';
        $amount = $input['amount'] ?? 0;
        $reason = $input['reason'] ?? '';
        
        if (!$user_id || !$operation || !$amount) {
            echo json_encode(['success' => false, 'error' => 'Неверные параметры']);
            exit;
        }
        
        $user_model = new User();
        $user = $user_model->getById($user_id);
        
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Пользователь не найден']);
            exit;
        }
        
        switch ($operation) {
            case 'add':
                $new_balance = $user_model->updateBalance($user_id, $amount, 'admin_deposit');
                break;
            case 'subtract':
                $new_balance = $user_model->updateBalance($user_id, -$amount, 'admin_withdrawal');
                break;
            case 'set':
                $difference = $amount - $user['balance'];
                $new_balance = $user_model->updateBalance($user_id, $difference, 'admin_adjustment');
                break;
            default:
                echo json_encode(['success' => false, 'error' => 'Неизвестная операция']);
                exit;
        }
        
        // Логируем действие
        file_put_contents('../logs/admin_actions.log', 
            date('Y-m-d H:i:s') . " - Admin adjusted balance for user {$user_id}: {$operation} {$amount} руб. Reason: {$reason}\n", 
            FILE_APPEND
        );
        
        echo json_encode(['success' => true, 'new_balance' => $new_balance]);
        break;
        
    case 'online_users':
        // Простая реализация подсчета онлайн пользователей
        $online_count = rand(5, 50); // Заглушка
        echo json_encode(['count' => $online_count]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Неизвестное действие']);
}
?>