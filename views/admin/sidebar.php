<nav class="admin-sidebar">
    <div class="sidebar-header">
        <h2>🎰 <?= SITE_NAME ?></h2>
        <p>Админ панель</p>
    </div>
    
    <div class="sidebar-nav">
        <a href="index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span>
            <span>Дашборд</span>
        </a>
        
        <a href="games.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'games.php' ? 'active' : '' ?>">
            <span class="nav-icon">🎮</span>
            <span>Игры</span>
        </a>
        
        <a href="users.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
            <span class="nav-icon">👥</span>
            <span>Пользователи</span>
        </a>
        
        <a href="payments.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : '' ?>">
            <span class="nav-icon">💰</span>
            <span>Платежи</span>
        </a>
        
        <a href="transactions.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : '' ?>">
            <span class="nav-icon">📈</span>
            <span>Транзакции</span>
        </a>
        
        <a href="settings.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
            <span class="nav-icon">⚙️</span>
            <span>Настройки</span>
        </a>
        
        <a href="logs.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : '' ?>">
            <span class="nav-icon">📋</span>
            <span>Логи</span>
        </a>
    </div>
    
    <div class="sidebar-footer">
        <div class="server-info">
            <small>Сервер: <?= $_SERVER['SERVER_NAME'] ?></small><br>
            <small>Версия: 1.0.0</small>
        </div>
    </div>
</nav>