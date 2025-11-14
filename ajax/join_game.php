<?php
require_once '../config/config.php';
require_once '../core/Auth.php';
require_once '../controllers/ApiController.php';

session_start();
header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_data']['id'];
$game_id = $input['game_id'] ?? null;

$api_controller = new ApiController();
$result = $api_controller->joinGame($user_id, $game_id);

echo json_encode($result);
?>