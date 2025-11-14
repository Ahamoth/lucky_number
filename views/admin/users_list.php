<?php
require_once '../../config/config.php';
require_once '../../controllers/AdminController.php';

$admin_controller = new AdminController();
$users = $admin_controller->getUsersList(1, 20);
?>
<div class="content-block">
    <div class="table-header">
        <h3>👥 Управление пользователями</h3>
        <div class="search-box">
            <input type="text" id="userSearch" placeholder="Поиск пользователя...">
            <button class="btn-secondary">🔍</button>
        </div>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Telegram</th>
                <th>Имя</th>
                <th>Баланс</th>
                <th>Игр</th>
                <th>Регистрация</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $user): ?>
            <tr>
                <td>#<?= $user['id'] ?></td>
                <td>
                    <?php if($user['username']): ?>
                        <a href="https://t.me/<?= $user['username'] ?>" target="_blank">
                            @<?= $user['username'] ?>
                        </a>
                    <?php else: ?>
                        <span class="text-muted">нет username</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></td>
                <td><strong><?= number_format($user['balance'], 2) ?> руб.</strong></td>
                <td><?= $user['games_played'] ?? 0 ?></td>
                <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-small" onclick="viewUser(<?= $user['id'] ?>)">👁️</button>
                        <button class="btn-small btn-warning" onclick="editBalance(<?= $user['id'] ?>, '<?= $user['first_name'] ?>')">💰</button>
                        <button class="btn-small btn-info" onclick="sendMessage(<?= $user['id'] ?>)">✉️</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="table-footer">
        <div class="pagination">
            <button class="btn-secondary">← Назад</button>
            <span>Страница 1</span>
            <button class="btn-secondary">Вперед →</button>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования баланса -->
<div id="balanceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>💰 Изменение баланса</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="balanceForm">
                <input type="hidden" id="balanceUserId">
                <div class="form-group">
                    <label class="form-label">Пользователь</label>
                    <input type="text" id="balanceUserName" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Тип операции</label>
                    <select id="balanceOperation" class="form-control">
                        <option value="add">Пополнение</option>
                        <option value="subtract">Списание</option>
                        <option value="set">Установить значение</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Сумма (руб.)</label>
                    <input type="number" id="balanceAmount" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Причина</label>
                    <textarea id="balanceReason" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn-primary">Применить</button>
            </form>
        </div>
    </div>
</div>

<script>
function viewUser(userId) {
    window.open('user_details.php?id=' + userId, '_blank');
}

function editBalance(userId, userName) {
    document.getElementById('balanceUserId').value = userId;
    document.getElementById('balanceUserName').value = userName;
    document.getElementById('balanceModal').style.display = 'block';
}

function sendMessage(userId) {
    const message = prompt('Введите сообщение для пользователя:');
    if (message) {
        fetch('ajax/admin_ajax.php?action=send_message', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                user_id: userId,
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Сообщение отправлено!');
            } else {
                alert('Ошибка: ' + data.error);
            }
        });
    }
}

// Обработка формы баланса
document.getElementById('balanceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const userId = document.getElementById('balanceUserId').value;
    const operation = document.getElementById('balanceOperation').value;
    const amount = parseFloat(document.getElementById('balanceAmount').value);
    const reason = document.getElementById('balanceReason').value;
    
    fetch('ajax/admin_ajax.php?action=update_balance', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            user_id: userId,
            operation: operation,
            amount: amount,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Баланс обновлен! Новый баланс: ' + data.new_balance + ' руб.');
            document.getElementById('balanceModal').style.display = 'none';
            location.reload();
        } else {
            alert('Ошибка: ' + data.error);
        }
    });
});

// Закрытие модального окна
document.querySelector('#balanceModal .close').addEventListener('click', function() {
    document.getElementById('balanceModal').style.display = 'none';
});
</script>