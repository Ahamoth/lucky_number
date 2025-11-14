<?php
// Коды ошибок
define('ERROR_INVALID_REQUEST', 1001);
define('ERROR_INSUFFICIENT_FUNDS', 1002);
define('ERROR_GAME_FULL', 1003);
define('ERROR_USER_NOT_FOUND', 1004);
define('ERROR_PAYMENT_FAILED', 1005);

// Статусы игр
define('GAME_STATUS_WAITING', 'waiting');
define('GAME_STATUS_ACTIVE', 'active');
define('GAME_STATUS_FINISHED', 'finished');

// Типы транзакций
define('TRANSACTION_DEPOSIT', 'deposit');
define('TRANSACTION_WITHDRAWAL', 'withdrawal');
define('TRANSACTION_GAME_WIN', 'game_win');
define('TRANSACTION_GAME_BET', 'game_bet');

// Статусы платежей
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_COMPLETED', 'completed');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_EXPIRED', 'expired');
?>