<?php
require_once '../config/config.php';
require_once '../controllers/GameController.php';

header('Content-Type: application/json');

$game_controller = new GameController();
$games = $game_controller->getActiveGames();

echo json_encode($games);
?>