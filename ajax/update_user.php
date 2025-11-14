<?php
require_once '../config/config.php';
require_once '../core/Auth.php';

session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$tg_user = $input['tg_user'] ?? null;

if (!$tg_user) {
    echo json_encode(['success' => false, 'error' => 'Данные пользователя не получены']);
    exit;
}

$auth = new Auth();
$user = $auth->login($tg_user);

echo json_encode([
    'success' => true,
    'user' => $user
]);
?>