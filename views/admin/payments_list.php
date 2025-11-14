<?php
require_once '../../config/config.php';
require_once '../../controllers/AdminController.php';

$admin_controller = new AdminController();
$payments = $admin_controller->getPaymentsList(1, 20);
?>
<div class="content-block">
    <div class="table-header">
        <h3>💰 История платежей</h3>
        <div class="filter-buttons">
            <select id="paymentFilter" onchange="filterPayments()">
                <option value="all">Все статусы</option>
                <option value="pending">Ожидание</option>
                <option value="completed">Завершены</option>
                <option value="failed">Ошибки</option>
            </select>
        </div>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Инвойс</th>
                <th>Пользователь</th>
                <th>Сумма</th>
                <th>TON</th>
                <th>Статус</th>
                <th>Создан</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($payments as $payment): ?>
            <tr data-status="<?= $payment['status'] ?>">
                <td><code><?= substr($payment['invoice_id'], 0, 8) ?>...</code></td>
                <td>
                    <?php if($payment['username']): ?>
                        @<?= $payment['username'] ?>
                    <?php else: ?>
                        ID: <?= $payment['user_id'] ?>
                    <?php endif; ?>
                </td>
                <td><?= number_format($payment['amount'], 2) ?> руб.</td>
                <td><?= number_format($payment['ton_amount'], 4) ?> TON</td>
                <td>
                    <span class="badge badge-<?= 
                        $payment['status'] == 'completed' ? 'success' : 
                        ($payment['status'] == 'pending' ? 'warning' : 'danger') 
                    ?>">
                        <?= $payment['status'] ?>
                    </span>
                </td>
                <td><?= date('d.m.Y H:i', strtotime($payment['created_at'])) ?></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-small" onclick="viewPayment('<?= $payment['invoice_id'] ?>')">👁️</button>
                        <?php if($payment['status'] == 'pending'): ?>
                            <button class="btn-small btn-success" onclick="checkPayment('<?= $payment['invoice_id'] ?>')">🔄</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="table-footer">
        <div class="summary">
            <strong>Всего: <?= count($payments) ?> платежей</strong>
        </div>
        <div class="pagination">
            <button class="btn-secondary">← Назад</button>
            <span>Страница 1</span>
            <button class="btn-secondary">Вперед →</button>
        </div>
    </div>
</div>

<script>
function filterPayments() {
    const filter = document.getElementById('paymentFilter').value;
    const rows = document.querySelectorAll('tr[data-status]');
    
    rows.forEach(row => {
        if (filter === 'all' || row.getAttribute('data-status') === filter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function viewPayment(invoiceId) {
    alert('Детали платежа: ' + invoiceId);
    // Здесь можно открыть модальное окно с детальной информацией
}

function checkPayment(invoiceId) {
    fetch('ajax/admin_ajax.php?action=check_payment&invoice_id=' + invoiceId)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Статус платежа обновлен!');
            location.reload();
        } else {
            alert('Платеж еще не подтвержден в сети');
        }
    });
}
</script>