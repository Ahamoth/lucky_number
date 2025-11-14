<?php
require_once '../config/config.php';
require_once '../core/Auth.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Чтение логов
$log_files = [
    'system' => '../logs/system.log',
    'telegram' => '../logs/telegram_webhook.log',
    'ton' => '../logs/ton_webhook.log',
    'admin' => '../logs/admin_actions.log',
    'errors' => '../logs/errors.log'
];

$selected_log = $_GET['log'] ?? 'system';
$log_content = '';

if (isset($log_files[$selected_log]) {
    $log_file = $log_files[$selected_log];
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        // Ограничиваем вывод последними 1000 строк
        $lines = explode("\n", $log_content);
        $lines = array_slice($lines, -1000);
        $log_content = implode("\n", $lines);
    } else {
        $log_content = "Лог файл не найден: " . basename($log_file);
    }
}

// Очистка логов
if (isset($_POST['clear_logs'])) {
    foreach ($log_files as $file) {
        if (file_exists($file)) {
            file_put_contents($file, '');
        }
    }
    header('Location: logs.php?log=' . $selected_log);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Логи системы - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <style>
        .log-content {
            background: #1a202c;
            color: #cbd5e0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            max-height: 600px;
            overflow-y: auto;
            white-space: pre-wrap;
            margin-top: 20px;
        }
        .log-line {
            margin-bottom: 2px;
        }
        .log-error { color: #fc8181; }
        .log-warning { color: #faf089; }
        .log-info { color: #90cdf4; }
        .log-success { color: #68d391; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="admin-main">
            <div class="admin-header">
                <h1>📋 Логи системы</h1>
                <div class="admin-actions">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="clear_logs" class="btn-danger" 
                                onclick="return confirm('Очистить все логи?')">
                            🗑️ Очистить логи
                        </button>
                    </form>
                    <a href="?action=logout" class="btn-logout">🚪 Выйти</a>
                </div>
            </div>

            <div class="content-block">
                <div class="log-tabs">
                    <?php foreach ($log_files as $key => $file): ?>
                    <a href="?log=<?= $key ?>" 
                       class="log-tab <?= $selected_log == $key ? 'active' : '' ?>">
                        <?= ucfirst($key) ?>
                        <?php if (file_exists($file)): ?>
                        <small>(<?= round(filesize($file) / 1024, 1) ?> KB)</small>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <div class="log-controls">
                    <button onclick="refreshLog()" class="btn-secondary">🔄 Обновить</button>
                    <button onclick="downloadLog()" class="btn-secondary">📥 Скачать</button>
                    <button onclick="clearLog()" class="btn-danger">🗑️ Очистить этот лог</button>
                </div>

                <div class="log-content" id="logContent">
                    <?= htmlspecialchars($log_content) ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function refreshLog() {
        location.reload();
    }

    function downloadLog() {
        const logContent = document.getElementById('logContent').textContent;
        const blob = new Blob([logContent], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = '<?= $selected_log ?>_log_<?= date('Y-m-d') ?>.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function clearLog() {
        if (confirm('Очистить этот лог файл?')) {
            fetch('ajax/admin_ajax.php?action=clear_log&log=<?= $selected_log ?>')
                .then(() => location.reload());
        }
    }

    // Авто-скролл к низу логов
    document.addEventListener('DOMContentLoaded', function() {
        const logContent = document.getElementById('logContent');
        logContent.scrollTop = logContent.scrollHeight;
    });
    </script>
</body>
</html>