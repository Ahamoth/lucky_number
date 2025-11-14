<?php
// core/autoload.php - Автозагрузка классов
spl_autoload_register(function ($class_name) {
    // Преобразуем Namespace\Class в путь к файлу
    $class_name = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
    
    // Возможные пути к файлам классов
    $paths = [
        __DIR__ . DIRECTORY_SEPARATOR . $class_name . '.php',
        __DIR__ . '/../models/' . $class_name . '.php',
        __DIR__ . '/../core/' . $class_name . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
?>