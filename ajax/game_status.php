<?php
require_once '../config/config.php';
require_once '../core/Auth.php';
require_once '../controllers/GameController.php';

session_start();
header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$game_id = $_GET['game_id'] ?? null;
if (!$game_id) {
    echo json_encode(['error' => 'ID игры не указан']);
    exit;
}

$game_controller = new GameController();
$result = $game_controller->getGameStatus($game_id);

echo json_encode($result);
?>