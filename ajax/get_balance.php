<?php
require_once '../config/config.php';
require_once '../core/Auth.php';

session_start();
header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$user = $auth->getUser();
echo json_encode([
    'success' => true,
    'balance' => $user['balance']
]);
?>