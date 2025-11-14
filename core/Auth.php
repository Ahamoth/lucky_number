<?php
class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Добавляем недостающий метод
    public function isLoggedIn() {
        // Проверяем авторизацию через сессию или токен
        session_start();
        return isset($_SESSION['user_id']) || isset($_GET['tg_auth']);
    }

    // Добавляем метод для получения пользователя
    public function getCurrentUser() {
        session_start();
        
        // Если есть ID пользователя в сессии
        if (isset($_SESSION['user_id'])) {
            $user_model = new User();
            return $user_model->getByTgId($_SESSION['user_id']);
        }
        
        // Если есть данные Telegram Web App
        if (isset($_GET['tg_auth'])) {
            $auth_data = json_decode(base64_decode($_GET['tg_auth']), true);
            if ($this->verifyTelegramAuth($auth_data)) {
                $user_model = new User();
                $user = $user_model->getByTgId($auth_data['id']);
                if (!$user) {
                    $user = $user_model->create($auth_data);
                }
                $_SESSION['user_id'] = $user['tg_id'];
                return $user;
            }
        }
        
        return null;
    }

    public function validateToken($token) {
        return !empty($token);
    }

    public function getUserByToken($token) {
        if (!$this->validateToken($token)) {
            return null;
        }

        // Для демо возвращаем тестового пользователя
        return [
            'id' => 1,
            'tg_id' => 123456789,
            'username' => 'test_user',
            'first_name' => 'Test',
            'balance' => 100.00
        ];
    }

    public function generateToken($userData) {
        return md5($userData['tg_id'] . time());
    }

    public function verifyTelegramAuth($auth_data) {
        return isset($auth_data['id']) && isset($auth_data['first_name']);
    }

    // Добавляем метод для выхода
    public function logout() {
        session_start();
        session_destroy();
        return true;
    }
}
?>