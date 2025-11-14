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
$amount = $input['amount'] ?? 0;
$user_id = $_SESSION['user_data']['id'];

$api_controller = new ApiController();
$result = $api_controller->createInvoice($user_id, $amount);

echo json_encode($result);
?>