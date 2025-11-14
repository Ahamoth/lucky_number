<?php
class GameController {
    private $game_model;
    
    public function __construct() {
        $this->game_model = new Game();
    }
    
    public function getActiveGames() {
        return $this->game_model->getActiveGames();
    }
    
    public function getGameStatus($game_id) {
        $game = $this->game_model->getById($game_id);
        if (!$game) {
            return ['error' => 'Игра не найдена'];
        }
        
        $players = $this->game_model->getGamePlayers($game_id);
        
        return [
            'game' => $game,
            'players' => $players,
            'status' => $game['status']
        ];
    }
    
    public function getRecentWinners($limit = 10) {
        return $this->game_model->getRecentWinners($limit);
    }
}
?>