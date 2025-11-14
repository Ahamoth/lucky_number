<?php
require_once '../config/config.php';
require_once '../core/Auth.php';
require_once '../controllers/AdminController.php';
require_once '../models/Transaction.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$transaction_model = new Transaction();
$transactions = $transaction_model->getDailyStats();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Транзакции - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>📈 Статистика транзакций</h1>
                <div class="admin-actions">
                    <span class="admin-welcome">Период: 30 дней</span>
                    <a href="?action=export_stats" class="btn-secondary">📊 Экспорт</a>
                    <a href="?action=logout" class="btn-logout">🚪 Выйти</a>
                </div>
            </div>

            <div class="content-block">
                <h3>📊 Ежедневная статистика</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Депозиты</th>
                            <th>Выплаты</th>
                            <th>Ставки</th>
                            <th>Прибыль</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $stat): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($stat['date'])) ?></td>
                            <td><?= number_format($stat['total_deposits'], 2) ?> руб.</td>
                            <td><?= number_format($stat['total_prizes'], 2) ?> руб.</td>
                            <td><?= number_format($stat['total_bets'], 2) ?> руб.</td>
                            <td><strong><?= number_format($stat['total_deposits'] - $stat['total_prizes'], 2) ?> руб.</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>