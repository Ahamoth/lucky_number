<?php
class Router {
    private $routes = [];
    
    public function add($route, $callback) {
        $this->routes[$route] = $callback;
    }
    
    public function dispatch($url) {
        foreach ($this->routes as $route => $callback) {
            if ($url === $route) {
                return call_user_func($callback);
            }
        }
        
        http_response_code(404);
        echo "Страница не найдена";
    }
}
?>