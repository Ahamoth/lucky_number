<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Payment.php';

// Создаем папку logs если нет
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

// Логируем входящий запрос
file_put_contents(__DIR__ . '/../logs/ton_webhook.log', date('Y-m-d H:i:s') . " - " . file_get_contents('php://input') . "\n", FILE_APPEND);

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['transaction'])) {
    http_response_code(400);
    die('Invalid request');
}

$transaction = $input['transaction'];
$payment_model = new Payment();

// Ищем платеж по комментарию или сумме
if (isset($transaction['comment']) && !empty($transaction['comment'])) {
    $invoice_id = $transaction['comment'];
} else {
    // Если нет комментария, ищем по сумме и адресу
    $invoice_id = $payment_model->findInvoiceByAmount(
        $transaction['value'] / 1000000000, // Конвертируем нанотоны в TON
        $transaction['to']
    );
}

if ($invoice_id) {
    $payment_model->confirmPayment($invoice_id, $transaction['hash']);
    http_response_code(200);
    echo 'OK';
} else {
    http_response_code(404);
    echo 'Invoice not found';
}
?>