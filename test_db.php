<?php
require_once 'config/config.php';
require_once 'core/Database.php';

echo "<h1>Тест подключения к БД</h1>";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Проверяем таблицы
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p style='color: green;'>✅ База данных подключена</p>";
    echo "<h3>Таблицы в БД:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>{$table}</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
}
?>