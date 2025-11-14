<?php
/**
 * Ежедневная очистка системы
 * Добавьте в cron: 0 3 * * * /usr/bin/php /path/to/cron/daily_cleanup.php
 */

require_once '../config/config.php';
require_once '../core/Database.php';

try {
    $db = Database::getInstance();
    
    // Логируем запуск
    file_put_contents('../logs/cron.log', date('Y-m-d H:i:s') . " - Daily cleanup started\n", FILE_APPEND);
    
    // 1. Очистка старых неактивных игр (старше 24 часов)
    $db->query(
        "DELETE FROM games WHERE status = 'waiting' AND created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)"
    );
    $inactive_games = $db->query("SELECT ROW_COUNT()")->fetchColumn();
    
    // 2. Архивирование старых транзакций (старше 30 дней)
    $db->query(
        "CREATE TABLE IF NOT EXISTS transactions_archive LIKE transactions"
    );
    $db->query(
        "INSERT INTO transactions_archive 
         SELECT * FROM transactions 
         WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
    $db->query(
        "DELETE FROM transactions WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
    );
    $archived_transactions = $db->query("SELECT ROW_COUNT()")->fetchColumn();
    
    // 3. Очистка просроченных платежей
    $db->query(
        "UPDATE payments SET status = 'expired' 
         WHERE status = 'pending' AND expires_at < NOW()"
    );
    $expired_payments = $db->query("SELECT ROW_COUNT()")->fetchColumn();
    
    // 4. Обновление ежедневной статистики
    $db->query(
        "INSERT INTO daily_stats (date, total_users, new_users, total_games, total_deposits, total_prizes, total_profit)
         SELECT 
            CURDATE(),
            (SELECT COUNT(*) FROM users),
            (SELECT COUNT(*) FROM users WHERE created_at >= CURDATE()),
            (SELECT COUNT(*) FROM games WHERE created_at >= CURDATE()),
            (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'deposit' AND created_at >= CURDATE()),
            (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'game_win' AND created_at >= CURDATE()),
            (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'deposit' AND created_at >= CURDATE()) -
            (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type = 'game_win' AND created_at >= CURDATE())
         ON DUPLICATE KEY UPDATE 
            total_users = VALUES(total_users),
            new_users = VALUES(new_users),
            total_games = VALUES(total_games),
            total_deposits = VALUES(total_deposits),
            total_prizes = VALUES(total_prizes),
            total_profit = VALUES(total_profit),
            updated_at = NOW()"
    );
    
    // Логируем результаты
    file_put_contents('../logs/cron.log', 
        date('Y-m-d H:i:s') . " - Cleanup completed: " .
        "Inactive games: $inactive_games, " .
        "Archived transactions: $archived_transactions, " .
        "Expired payments: $expired_payments\n", 
        FILE_APPEND
    );
    
    echo "Daily cleanup completed successfully\n";
    
} catch (Exception $e) {
    file_put_contents('../logs/cron.log', 
        date('Y-m-d H:i:s') . " - Cleanup error: " . $e->getMessage() . "\n", 
        FILE_APPEND
    );
    echo "Error: " . $e->getMessage() . "\n";
}
?>