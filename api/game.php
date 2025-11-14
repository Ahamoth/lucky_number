<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';
require_once '../models/User.php';
require_once '../models/Game.php';

header('Content-Type: application/json');

$auth = new Auth();
$user = $auth->getCurrentUser();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_active_game':
        getActiveGame($user);
        break;
    case 'join_game':
        joinGame($user);
        break;
    case 'get_game_status':
        getGameStatus($user);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function getActiveGame($user) {
    $game_model = new Game();
    $game = $game_model->getActiveGame();
    
    if ($game) {
        $participants = $game_model->getGameParticipants($game['id']);
        echo json_encode([
            'success' => true,
            'game' => $game,
            'participants' => $participants,
            'user_joined' => isUserJoined($participants, $user['id'])
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No active game']);
    }
}

function joinGame($user) {
    $game_id = $_POST['game_id'] ?? 0;
    
    if (!$game_id) {
        echo json_encode(['success' => false, 'error' => 'Game ID required']);
        return;
    }

    $game_model = new Game();
    $result = $game_model->joinGame($user['id'], $game_id);
    
    echo json_encode($result);
}

function getGameStatus($user) {
    $game_id = $_GET['game_id'] ?? 0;
    
    if (!$game_id) {
        echo json_encode(['success' => false, 'error' => 'Game ID required']);
        return;
    }

    $game_model = new Game();
    $game = $game_model->getGameById($game_id);
    $participants = $game_model->getGameParticipants($game_id);
    
    echo json_encode([
        'success' => true,
        'game' => $game,
        'participants' => $participants
    ]);
}

function isUserJoined($participants, $user_id) {
    foreach ($participants as $participant) {
        if ($participant['user_id'] == $user_id) {
            return $participant;
        }
    }
    return false;
}
?>